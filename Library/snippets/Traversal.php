
	 
	/*===================================================================================
	 *	traverseObject																	*
	 *==================================================================================*/

	/**
	 * Validate object
	 *
	 * The duty of this method is to traverse the current object structure and perform the
	 * following main actions:
	 *
	 * <ul>
	 *	<li><em>Collect information</em>: The method will collect all tags, tag offsets and
	 *		object references.
	 *	<li><em>Validate properties</em>: The method will check if the parsed properties are
	 *		correct, this will be performed if the <tt>$doValidate</tt> flag is set.
	 *	<li><em>Commit referenced objects</em>: If the property is an object reference and
	 *		the property is an object, the method will commit it if not already committed
	 *		and replace the object with its reference. This will be performed if the
	 *		<tt>$doValidate</tt> flag is set.
	 *	<li><em>Cast properties</em>: The method will cast the property values to the
	 *		correct data type, this will be performed if the <tt>$doValidate</tt> flag is set.
	 * </ul>
	 *
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 */
	protected function traverseObject( $doValidate = TRUE )
	{
		//
		// Init local storage.
		//
		$path = $tags = $refs = Array();
		
		//
		// Get object array copy.
		//
		$object = $this->getArrayCopy();
		
		//
		// Iterate properties.
		//
		$this->traverseProperties( $object, $path, $tags, $refs, $doValidate );
	
	} // traverseObject.

	 
	/*===================================================================================
	 *	traverseProperties																*
	 *==================================================================================*/

	/**
	 * Traverse property
	 *
	 * The duty of this method is to parse the provided property list and perform the
	 * following actions:
	 *
	 * <ul>
	 *	<li><em>Collect information</em>: The method will collect the tag and its offset, if
	 *		the current property is a leaf offset.
	 *	<li><em>Validate properties</em>: The method will check if the provided list or
	 *		structure is an array, or validate the provided property if it is a scalar.
	 *	<li><em>Cast properties</em>: The method will cast the property value if it is a
	 *		scalar.
	 * </ul>
	 *
	 * @param array					$theProperties		Property list.
	 * @param array					$thePath			Receives the offset path.
	 * @param array					$theTags			Receives the tag information.
	 * @param array					$theRefs			Receives the object references.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 */
	protected function traverseProperties( &$theProperties,
										   &$thePath, &$theTags, &$theRefs,
											$doValidate )
	{
		//
		// Iterate properties.
		//
		$tags = array_keys( $theProperties );
		foreach( $tags as $tag )
		{
			//
			// Push offset to path.
			//
			$thePath[] = $tag;
		
			//
			// Compute offset.
			//
			$offset = implode( '.', $thePath );
		
			//
			// Reference property.
			//
			$ref = & $theProperties[ $tag ];
			
			//
			// Copy tag information.
			//
			if( array_key_exists( $tag, $theTags ) )
			{
				//
				// Copy type and kind.
				//
				$type = $theTags[ $tag ][ kTAG_DATA_TYPE ];
				$kind = $theTags[ $tag ][ kTAG_DATA_KIND ];
				
				//
				// Update offset path.
				//
				if( ! in_array( $offset, $theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] ) )
					$theTags[ $tag ][ kTAG_OBJECT_OFFSETS ][] = $offset;
		
			} // Already parsed.
		
			//
			// Collect tag information.
			//
			else
			{
				//
				// Get info.
				//
				$this->getOffsetTypes( $tag, $type, $kind );
				
				//
				// Set type and kind.
				//
				$theTags[ $tag ][ kTAG_DATA_TYPE ] = $type;
				$theTags[ $tag ][ kTAG_DATA_KIND ] = $kind;
				
				//
				// Set offset path.
				//
				$theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] = array( $offset );
			
			} // New tag.
			
			//
			// Handle structures.
			//
			if( in_array( kTYPE_STRUCT, $type ) )
			{
				//
				// Verify structure.
				//
				if( $doVerify
				 && (! is_array( $ref )) )
					throw new \Exception(
						"Invalid structure value in [$offset]: "
					   ."the value is not an array." );							// !@! ==>
				
				//
				// Handle list.
				//
				if( in_array( kTYPE_LIST, $kind ) )
				{
					//
					// Iterate list.
					//
					$keys = array_keys( $ref );
					foreach( $keys as $key )
						$this->traverseProperties(
							$ref[ $key ], $thePath, $theTags, $theRefs, $doValidate );
				
				} // Structures list.
				
				//
				// Handle scalar structure.
				//
				else
					$this->traverseProperties(
						$ref, $thePath, $theTags, $theRefs, $doValidate );
			
			} // Structure.
			
			//
			// Handle lists.
			//
			elseif( in_array( kTYPE_LIST, $kind ) )
			{
				//
				// Verify list.
				//
				if( $doVerify
				 && (! is_array( $ref )) )
					throw new \Exception(
						"Invalid list value in [$offset]: "
					   ."the value is not an array." );							// !@! ==>
				
				//
				// Iterate list.
				//
				$keys = array_keys( $ref );
				foreach( $keys as $key )
					$this->traverseScalar(
						$ref[ $key ], $type, $theRefs, $offset, $doValidate );
			
			} // List.
			
			//
			// Handle scalars.
			//
			else
				$this->traverseScalar( $ref, $type, $theRefs, $offset, $doValidate );
		
			//
			// Pop offset from path.
			//
			array_pop( $thePath );
	
		} // Iterating properties.
		
	} // traverseProperties.

	 
	/*===================================================================================
	 *	traverseScalar																	*
	 *==================================================================================*/

	/**
	 * Traverse scalar
	 *
	 * The duty of this method is to parse the provided scalar property and perform the
	 * following actions:
	 *
	 * <ul>
	 *	<li><em>Validate reference</em>: If the provided property is an object reference,
	 *		the method will commit it, if it is a non committed object and the .
	 *	<li><em>Validate properties</em>: The method will check if the provided list or
	 *		structure is an array, or validate the provided property if it is a scalar.
	 *	<li><em>Cast properties</em>: The method will cast the property value if it is a
	 *		scalar.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param array					$theTypes			Data types.
	 * @param array					$theRefs			Receives the object references.
	 * @param string				$theOffset			Offset.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 */
	protected function traverseScalar( &$theProperty,
									   &$theType, &$theRefs,
									    $theOffset, $doValidate )
	{
		//
		// Get reference class.
		//
		$class = $this->getReferenceTypeClass( $theType );
		
		//
		// Validate.
		//
		if( $doValidate )
		{
			//
			// Validate reference.
			//
			if( $class !== NULL )
				$this->validateReference( $theProperty, $class, $theOffset );
			
			//
			// Validate scalar.
			//
			else
				$this->validateProperty( $theProperty, $theType, $theOffset );
		
		} // Validate.
		
		//
		// Collect references.
		//
		if( $class !== NULL )
		{
			//
			// Update references.
			//
			if( ! array_key_exists( $class, $theRefs ) )
				$theRefs[ $class ] = array( $theProperty );
			elseif( ! in_array( $theRefs[ $class ], $theProperty ) )
				$theRefs[ $class ][] = $theProperty;
		
		} // Reference.
		
	} // traverseScalar.

	 
	/*===================================================================================
	 *	propertyValidate																*
	 *==================================================================================*/

	/**
	 * Validate property
	 *
	 * The duty of this method is to validate the provided property, the method will perform
	 * the following actions:
	 *
	 * <ul>
	 *	<li><em>List or structure</em>: The method will assert if the property is an array.
	 *	<li><em>Scalar object reference</em>: The method will check the provided reference.
	 *	<li><em>Cast properties</em>: The method will cast the property value if it is a
	 *		scalar.
	 * </ul>
	 *
	 * @param string				$tag			Property offset.
	 * @param array					$theProperty		Property reference.
	 * @param array					$thePath			Receives the offset path.
	 * @param array					$theTags			Receives the tag information.
	 * @param array					$theRefs			Receives the object references.
	 *
	 * @access protected
	 */
	protected final function propertyValidate( $tag, &$theProperty,
														   &$thePath,
														   &$theTags,
														   &$theRefs )
	{
		//
		// Push offset to path.
		//
		$thePath[] = $tag;
		
		//
		// Compute offset.
		//
		$offset = implode( '.', $thePath );
		
		//
		// Copy tag information.
		//
		if( array_key_exists( $tag, $theTags ) )
		{
			$type = $theTags[ $tag ][ kTAG_DATA_TYPE ];
			$kind = $theTags[ $tag ][ kTAG_DATA_KIND ];
		
		} // Already parsed.
		
		//
		// Collect tag information.
		//
		else
			$this->getOffsetTypes( $tag, $type, $kind );
		
		//
		// Handle scalars.
		//
		if( (! in_array( kTYPE_LIST, $kind ))
		 && (! in_array( kTYPE_STRUCT, $type )) )
		{
			//
			// Add tag info.
			//
			if( ! array_key_exists( $tag, $theTags ) )
			{
				//
				// Set type and kind.
				//
				$theTags[ $tag ][ kTAG_DATA_TYPE ] = $type;
				$theTags[ $tag ][ kTAG_DATA_KIND ] = $kind;
			
				//
				// Add offset.
				//
				$theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] = array( $offset );
			
			} // New tag.
			
			//
			// Add offset path.
			//
			elseif( ! in_array( $offset, $theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] ) )
				$theTags[ $tag ][ kTAG_OBJECT_OFFSETS ][] = $offset;
			
			//
			// Handle object references.
			//
			if( count( array_intersect( $type, static::GetReferenceTypes() ) ) )
			{
			
			} // Is a reference.
		
		} // Scalar property.
		
		//
		// Pop offset from path.
		//
		array_pop( $thePath );
	
	} // propertyValidate.
