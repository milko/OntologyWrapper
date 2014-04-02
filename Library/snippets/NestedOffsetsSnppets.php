
	 
	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * Reset a value at a given offset
	 *
	 * This method is the equivalent of {@link offsetUnset()} for nested offsets, the method
	 * will traverse the current value until it reaches the requested offset and it will
	 * delete it, if any offset is not matched, or if any intermediate offset is not an
	 * array, the method will do nothing.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: This parameter expects the nested offset string, when
	 *		recursing, it will be passed the nested offset path array.
	 *	<li><b>$theRootOffset</b>: This parameter is only used during recursion.
	 *	<li><b>$theCurrentValue</b>: This parameter is only used during recursion.
	 * </ul>
	 *
	 * The method will take care of deleting the the object's offset. If any intermediate
	 * offset holds an empry array, the method will delete it.
	 *
	 * It is assumed that all offsets except the last one must be arrays, if that is not
	 * the case, the method will return <tt>NULL</tt>.
	 *
	 * The provided offset must be a valid nested offset, which is a sequence of numerics
	 * separated by a period; this check must have been performed by the caller.
	 *
	 * @param string				$theOffset			Offset.
	 * @param string				$theRootOffset		Receives root offset.
	 * @param array					$theCurrentValue	Current level value.
	 *
	 * @access public
	 *
	 * @uses nestedOffsetUnset()
	 */
	public function nestedOffsetUnset( $theOffset, &$theRootOffset, &$theCurrentValue )
	{
		//
		// Handle root offset.
		// Note that we know the offset to have at least two levels.
		//
		if( $theCurrentValue === NULL)
		{
			//
			// Convert offset.
			//
			$theOffset = explode( '.', $theOffset );
			
			//
			// Get offset value.
			//
			$theCurrentValue = parent::offsetGet( array_shift( $theOffset ) );
		
		} // Root offset.
		
		//
		// Only handle arrays.
		//
		if( is_array( $theCurrentValue ) )
		{
		
		} // Offset is array.
		
		//
		// Check value.
		//
		if( ! is_array( $theCurrentValue ) )
			return FALSE;															// ==>
	
		//
		// Get current offset.
		//
		$offset = array_shift( $theOffset );
	
		//
		// Check offset.
		//
		if( ! array_key_exists( $offset, $theCurrentValue ) )
			return FALSE;															// ==>
	
		//
		// Handle leaf offset.
		//
		if( ! count( $theOffset ) )
			return TRUE;															// ==>
		
		return $this->nestedOffsetExists( $theOffset, $theCurrentValue[ $offset ] );		// ==>
	
	} // nestedOffsetUnset.
