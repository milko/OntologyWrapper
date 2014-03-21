	/*===================================================================================
	 *	modifyAdd																		*
	 *==================================================================================*/

	/**
	 * Add offsets to object
	 *
	 * This method can be used to add the properties contained in the current object to an
	 * object residing in the persistent store, the offsets contained in the current object
	 * will be added or will replace the offsets contained in the stored object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdentifier</b>: The object native identifier.
	 *	<li><b>$theWrapper</b>: The persistent store.
	 * </ul>
	 *
	 * The current object <em>must only contain the offsets to be added</em>, the method
	 * will take into consideration all the offsets contained in the object.
	 *
	 * In this method we perform the following steps:
	 *
	 * <ul>
	 *	<li>We load the original object, if it not found, the method will raise an exception.
	 *	<li>We select all new tags and add them to the {@link .
	 *	<li>We decrement eventual reference counts of the replaced offsets.
	 *	<li>We add the replaced offsets to the stored object.
	 *	<li>We add the new offsets to the stored object.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The method will return the number of elements affected by the operation (1 or 0).
	 *
	 * @param mixed					$theIdentifier		Original object native identifier.
	 * @param Wrapper				$theWrapper			Persistent store.
	 *
	 * @access public
	 * return integer				Number of objects affected.
	 *
	 * @throws Exception
	 */
	public function modifyAdd( $theIdentifier, $theWrapper = NULL )
	{
		//
		// Init local storage.
		//
		$class = get_class( $this );
		
		//
		// Resolve wrapper.
		//
		$this->resolveWrapper( $theWrapper );
		
		//
		// Validate, collect tags, offsets and references.
		//
		$this_tags = $this_refs = Array();
		$this->preCommitTraverse( $this_tags, $this_refs );
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Load original object.
		//
		$saved = $collection->matchOne( array( kTAG_NID => $theIdentifier ),
										kQUERY_ASSERT | kQUERY_OBJECT );
		
		//
		// Save old tags and references.
		//
		$old_tags = $saved->offsetGet( kTAG_OBJECT_OFFSETS );
		$old_refs = $saved->offsetGet( kTAG_OBJECT_REFERENCES );
		
		//
		// Add and replace properties.
		//
		foreach( $this as $key => $value )
			$saved->offsetSet( $key, $value );
		
		//
		// Collect tags, offsets and references.
		//
		$cur_tags = $cur_refs = Array();
		$saved->preCommitTraverse( $cur_tags, $cur_refs, FALSE );
		
		//
		// Replace tags list.
		//
		$collection->replaceOffsets(
			$theIdentifier,
			array( kTAG_OBJECT_TAGS => array_keys( $cur_tags ) ) );
	
		//
		// Replace tag offsets.
		//
		$tmp = Array();
		foreach( $cur_tags as $key => $value )
			$tmp[ $key ] = $value[ kTAG_OBJECT_OFFSETS ];
		$collection->replaceOffsets(
			$theIdentifier,
			array( kTAG_OBJECT_OFFSETS => $tmp ) );
		
		//
		// Replace object references.
		//
		$collection->replaceOffsets(
			$theIdentifier,
			array( kTAG_OBJECT_REFERENCES => $cur_refs ) );
		
		//
		// Select new tags and references.
		//
		$tags = array_diff_key( $this_tags, $old_tags );
		$refs = array_diff_key( $this_refs, $old_refs );
			
		//
		// Update metadata.
		//
		$this->postCommit( $tags, $refs );
		
		//
		// Select removed tags and references.
		//
		$tags = array_diff_key( $old_tags, $cur_tags );
		$refs = array_diff_key( $old_refs, $cur_refs );
			
		//
		// Update metadata.
		//
		$this->postDelete( $tags, $refs );
		
		//
		// Select common tags.
		//
		$tags = array_intersect_key( $this_tags, $old_tags );
		
		//
		// Filter new offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $info[ kTAG_OBJECT_OFFSETS ],
							   $old_tags[ $tag ][ kTAG_OBJECT_OFFSETS ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Add new offsets.
		//
		if( count( $set ) )
			$this->postCommitTagOffsets( $set );
		
		//
		// Filter deleted offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $old_tags[ $tag ][ kTAG_OBJECT_OFFSETS ],
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
		
		return $collection->replaceOffsets( $theIdentifier, $tmp->getArrayCopy() );	// ==>
	
	} // modifyAdd.
