
	 
	/*===================================================================================
	 *	modifyObject																	*
	 *==================================================================================*/

	/**
	 * Modify object
	 *
	 * This method will modify the current object by either adding, replacing or deleting
	 * the provided offsets. The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: The offsets to be added, replaced or deleted. This parameter
	 *		is structured according to the requested operation:
	 *	 <ul>
	 *		<li><em>Add or replace</tt>: This occurs if the last parameter is <tt>TRUE</tt>.
	 *			The parameter should be an array indexed by tag sequence number and value
	 *			representing the offset value. All offsets will be added at the root level.
	 *		<li><em>Remove</tt>: This occurs if the last parameter is <tt>FALSE</tt>.
	 *			The parameter should be an array listing all the offsets to be removed at
	 *			the root level.
	 *	 </ul>
	 *	<li><b>$doSet</b>: <tt>TRUE</tt> means add or replace offsets, <tt>FALSE</tt> means
	 *		remove offsets.
	 * </ul>
	 *
	 * This method will call the {@link modifyObjectAdd()} method if setting and the
	 * {@link modifyObjectDel()} method if removing.
	 *
	 * The method will return the number of elements affected by the operation (1 or 0).
	 *
	 * <em>Note that this method is called by the static {@link Modify()} method, the
	 * current object was just loaded from the persistent store.</em>
	 *
	 * @param mixed					$theOffsets			Offsets to be modified.
	 * @param boolean				$doSet				<tt>TRUE</tt> means add or replace.
	 *
	 * @access protected
	 * return integer				Number of objects affected.
	 *
	 * @throws Exception
	 */
	protected function modifyObject( $theOffsets, $doSet )
	{
		//
		// Do it only if the object is committed and clean.
		//
		if( $this->isCommitted()
		 && (! $this->isDirty()) )
		{
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary ) );
		
			//
			// Set offsets.
			//
			if( $doSet )
				return $this->modifyObjectAdd( $collection, $theOffsets );			// ==>
		
			return $this->modifyObjectDel( $collection, $theOffsets );				// ==>
		
		} // Clean and committed.
		
		throw new \Exception(
			"Cannot modify object: "
		   ."the object is not committed or was modified." );					// !@! ==>
	
	} // modifyObject.

	 
	/*===================================================================================
	 *	modifyObjectAdd																	*
	 *==================================================================================*/

	/**
	 * Add offsets to object
	 *
	 * This method can be used to add the provided properties to the current object, the
	 * provided offsets will be added or will replace the offsets contained in the current
	 * object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The object's persistent store.
	 *	<li><b>$theOffsets</b>: The offsets to be added or replaced, this parameter should
	 *		be an array of properties in which the element key is the offset and the value
	 *		is the offset value.
	 * </ul>
	 *
	 * In this method we perform the following steps:
	 *
	 * <ul>
	 *	<li>We update the {@link kTAG_OBJECT_TAGS} property.
	 *	<li>We update the {@link kTAG_OBJECT_OFFSETS} property.
	 *	<li>We update the {@link kTAG_OBJECT_REFERENCES} property.
	 *	<li>We update the metadata of new tags and references.
	 *	<li>We update the metadata of removed tags, offsets and references.
	 *	<li>We update the metadata of new offsets of common collections.
	 *	<li>We update the metadata of removed offsets of common collections.
	 *	<li>We replace the offsets in the stored object.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The method will return the modified object.
	 *
	 * @param CollectionObject		$theCollection		Object collection.
	 * @param mixed					$theOffsets			Modification properties.
	 *
	 * @access protected
	 * return integer				Number of objects affected.
	 */
	protected function modifyObjectAdd( CollectionObject $theCollection, $theOffsets )
	{
		//
		// Init local storage.
		//
		$id = $this->offsetGet( kTAG_NID );
		
		//
		// Instantiate modifications as object.
		//
		if( $theOffsets instanceof self )
			$mods = $theOffsets;
		elseif( is_array( $theOffsets ) )
		{
			$class = get_class( $this );
			$mods = new $class( $this->mDictionary, $theOffsets );
		}
		else
			throw new \Exception(
				"Cannot modify offsets: "
			   ."invalid modifications parameter type." );						// !@! ==>
		
		//
		// Validate, collect tags, offsets and references.
		//
		$mods_tags = $mods_refs = Array();
		$mods->preCommitTraverse( $mods_tags, $mods_refs );
		
		//
		// Save old tags and references.
		//
		$old_tags = $this->offsetGet( kTAG_OBJECT_OFFSETS );
		$old_refs = $this->offsetGet( kTAG_OBJECT_REFERENCES );
		
		//
		// Add and replace properties.
		//
		foreach( $mods as $key => $value )
			$this->offsetSet( $key, $value );
		
		//
		// Collect tags, offsets and references.
		//
		$cur_tags = $cur_refs = Array();
		$this->preCommitTraverse( $cur_tags, $cur_refs, FALSE );
		
		//
		// Replace tags list.
		//
		$this->offsetSet( kTAG_OBJECT_TAGS, array_keys( $cur_tags ) );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_TAGS => array_keys( $cur_tags ) ) );
	
		//
		// Replace tag offsets.
		//
		$tmp = Array();
		foreach( $cur_tags as $key => $value )
			$tmp[ $key ] = $value[ kTAG_OBJECT_OFFSETS ];
		$this->offsetSet( kTAG_OBJECT_OFFSETS, $tmp );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_OFFSETS => $tmp ) );
		
		//
		// Replace object references.
		//
		$this->offsetSet( kTAG_OBJECT_REFERENCES, $cur_refs );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_REFERENCES => $cur_refs ) );
		
		//
		// Select new tags and references.
		//
		$tags = array_diff_key( $mods_tags, $old_tags );
		$refs = $mods->compareObjectOffsets( $mods_refs, $old_refs, TRUE );
			
		//
		// Update metadata.
		//
		$mods->postCommit( $tags, $refs );
		
		//
		// Select removed tags and references.
		//
		$tags = array_diff_key( $old_tags, $cur_tags );
		$refs = $mods->compareObjectOffsets( $old_refs, $cur_refs, TRUE );
			
		//
		// Update metadata.
		//
		$mods->postDelete( $tags, $refs );
		
		//
		// Select common tags.
		//
		$tags = array_intersect_key( $mods_tags, $old_tags );
		
		//
		// Filter new offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $info[ kTAG_OBJECT_OFFSETS ],
							   $old_tags[ $tag ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Add new offsets.
		//
		if( count( $set ) )
			$mods->postCommitTagOffsets( $set );
		
		//
		// Filter deleted offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $old_tags[ $tag ],
							   $info[ kTAG_OBJECT_OFFSETS ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Delete old offsets.
		//
		if( count( $set ) )
			$mods->postDeleteTagOffsets( $set );
		
		return $theCollection->replaceOffsets( $id, $mods->getArrayCopy() );		// ==>
	
	} // modifyObjectAdd.

	 
	/*===================================================================================
	 *	modifyObjectDel																	*
	 *==================================================================================*/

	/**
	 * Delete offsets from object
	 *
	 * This method can be used to delete the provided properties from the current object,
	 * the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The object's persistent store.
	 *	<li><b>$theOffsets</b>: The offsets to be added or replaced, this parameter should
	 *		be an array of tag sequence numbers referencing the offsets to be removed from
	 *		the root of the object.
	 * </ul>
	 *
	 * In this method we perform the following steps:
	 *
	 * <ul>
	 *	<li>We update the {@link kTAG_OBJECT_TAGS} property.
	 *	<li>We update the {@link kTAG_OBJECT_OFFSETS} property.
	 *	<li>We update the {@link kTAG_OBJECT_REFERENCES} property.
	 *	<li>We update the metadata of removed tags, offsets and references.
	 *	<li>We update the metadata of removed offsets of common collections.
	 *	<li>We remove the offsets from the stored object.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The method will return the number of elements affected by the operation (1 or 0).
	 *
	 * @param CollectionObject		$theCollection		Object collection.
	 * @param array					$theOffsets			Modification properties.
	 *
	 * @access public
	 * return integer				Number of objects affected.
	 *
	 * @throws Exception
	 */
	public function modifyObjectDel( CollectionObject $theCollection, $theOffsets )
	{
		//
		// Init local storage.
		//
		$id = $this->offsetGet( kTAG_NID );
		
		//
		// Collect tags, offsets and references.
		//
		$old_tags = $old_refs = Array();
		$this->preCommitTraverse( $old_tags, $old_refs, FALSE );
		
		//
		// Remove properties.
		//
		foreach( $theOffsets as $offset )
			$this->offsetUnset( $offset );
		
		//
		// Collect tags, offsets and references.
		//
		$cur_tags = $cur_refs = Array();
		$this->preCommitTraverse( $cur_tags, $cur_refs, FALSE );
		
		//
		// Replace tags list.
		//
		$this->offsetSet( kTAG_OBJECT_TAGS, array_keys( $cur_tags ) );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_TAGS => array_keys( $cur_tags ) ) );
	
		//
		// Replace tag offsets.
		//
		$tmp = Array();
		foreach( $cur_tags as $key => $value )
			$tmp[ $key ] = $value[ kTAG_OBJECT_OFFSETS ];
		$this->offsetSet( kTAG_OBJECT_OFFSETS, $tmp );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_OFFSETS => $tmp ) );
		
		//
		// Replace object references.
		//
		$this->offsetSet( kTAG_OBJECT_REFERENCES, $cur_refs );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_REFERENCES => $cur_refs ) );
		
		//
		// Select removed tags and references.
		//
		$tags = array_diff_key( $old_tags, $cur_tags );
		$refs = $this->compareObjectOffsets( $old_refs, $cur_refs, TRUE );
			
		//
		// Update metadata.
		//
		$this->postDelete( $tags, $refs );
		
		//
		// Select common tags.
		//
		$tags = array_intersect_key( $cur_tags, $old_tags );
		
		//
		// Filter deleted offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $old_tags[ $tag ],
							   $info[ kTAG_OBJECT_OFFSETS ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Delete old offsets.
		//
		if( count( $set ) )
			$this->postDeleteTagOffsets( $set );
		
		return $theCollection->deleteOffsets( $id, $theOffsets );					// ==>
	
	} // modifyObjectDel.
