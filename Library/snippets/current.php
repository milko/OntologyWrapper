
	 
	/*===================================================================================
	 *	deleteTagOffset																	*
	 *==================================================================================*/

	/**
	 * Delete tag offsets
	 *
	 * The duty of this method is to delete the provided offset paths from the provided
	 * list, 
	 *
	 * This method is called for each element of the tags list parameter described in the
	 * {@link preDelete()} method, its duty is to remove all offsets featured in the list
	 * from tag objects.
	 *
	 * This method will be invoked <em>after running the {@link traverseFilterOffsets()}
	 * method</em>, which means that the iterated list will only hold those offsets which
	 * are not referenced any more by objects of the current class.
	 *
	 * The method will iterate the list and remove from the relative tag object set all the
	 * offsets featured in the current list element.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter points to the current element of the tags
	 *		list.
	 *	<li><b>$theTagCollection</b>: The collection of tag objects.
	 *	<li><b>$theCollection</b>: The collection of the current object.
	 * </ul>
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param CollectionObject		$theCollection		Tag object collection.
	 * @param string				$theOffset			Tag object set offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 */
	protected function removeTagOffsets( \Iterator		   $theIterator,
											  CollectionObject $theCollection,
															   $theOffset )
	{
		//
		// Remove from set.
		//
		$theCollection->updateSet(
			(int) $theIterator->key(),
			kTAG_ID_SEQUENCE,
			array( $theOffset => $theIterator->current()[ kTAG_OBJECT_OFFSETS ] ),
			FALSE );
		
		return TRUE;																// ==>
	
	} // removeTagOffsets.

	 
	/*===================================================================================
	 *	updateTags																		*
	 *==================================================================================*/

	/**
	 * Update tags
	 *
	 * The duty of this method is to update the tag objects reference counts and offset
	 * paths relative to the current object, this method can be used both when tags are
	 * added or when tags are deleted.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This array reference must be structured as the
	 *		{@link kTAG_OBJECT_OFFSETS} property, if it is not an array, the method will
	 *		exit.
	 *	<li><b>$doAdd</b>: If this boolean parameter is <tt>TRUE</tt>, it is assumed the
	 *		provided list of tags and offsets comes from an object that was added to the
	 *		collection; if the value is <tt>FALSE</tt>, it is assumed that the list of
	 *		offsets comes from an object that was deleted or is a set of object properties
	 *		that were deleted from an object. In this last case, the object must have been
	 *		deleted or updated <em>before</em> this method is called.
	 * </ul> 
	 *
	 * This method is called by the {@link updateTags()} method, it will update the
	 * tag object offsets property set relative to the base class of the current object.
	 *
	 * This method will first select the set of offsets to be removed, then, for each of
	 * these offsets, it will check if there are other objects featuring them: if that is
	 * the case, the method will do nothing, if the offset cannot be found in any other
	 * object, the method will remove it from the relative tag's set.
	 *
	 * <em>This method is fundamental for referential integrity, it ensures all tag objects
	 * hold the set of offset paths in which the specific tag is used as a data container,
	 * this information will be used to optimise queries. If you need to overload this
	 * method, be aware that you should mirror changes in the commit and modification
	 * workflows, so that referential integrity is maintained.</em>
	 *
	 * This method expects the {@link dictionary()} set.
	 *
	 * The provided parameter must have the same structure as the
	 * {@link kTAG_OBJECT_OFFSETS} property.
	 *
	 * @param array					$theTags			Object tag offset paths.
	 * @param boolean				$doAdd				<tt>TRUE</tt> means new tags.
	 *
	 * @access protected
	 *
	 * @uses ResolveOffsetsTag()
	 */
	protected function updateTags( &$theTags )
	{
		//
		// Check tag offsets.
		//
		if( is_array( $theTags )
		 && count( $theTags ) )
		{
			//
			// Resolve collection.
			//
			$collection
				= Tag::ResolveCollection(
					Tag::ResolveDatabase( $this->mDictionary ) );
	
			//
			// Resolve tag property.
			//
			$offset = static::ResolveOffsetsTag( static::kSEQ_NAME );
	
			//
			// Handle add.
			//
			if( $doAdd )
			{
				//
				// Update reference count.
				//
				$this->updateReferenceCount(
					Tag::kSEQ_NAME,				// Tags collection.
					array_keys( $theTags ),		// Tags identifiers.
					kTAG_ID_SEQUENCE,			// Identifiers offset.
					1 );						// Reference count.
			
				//
				// Update tag offsets.
				//
				foreach( $tags as $tag => $offsets )
					$collection->updateSet(
						(int) $tag,									// Tag identifier.
						kTAG_ID_SEQUENCE,							// Identifier offset.
						array( $offset => $offsets ),				// Offsets set.
						TRUE );										// Add to set.
			
			} // Added tags.
			
			//
			// Handle delete.
			//
			else
			{
				//
				// Filter existing tags.
				//
				$offsets = $theTags;
				$tags = $this->filterExistingOffsets( $collection, $offsets );
				
				//
				// Remove missing tags.
				//
				if( count( $tags ) )
					$this->deleteTagOffsets( $tags, $offset );
	
				//
				// Remove missing tag offsets.
				//
				if( count( $offsets ) )
					$this->deleteTagOffset( $offsets, $offset );
			
			} // Deleted tags.
		
		} // Has tag offsets.
	
	} // updateTags.
