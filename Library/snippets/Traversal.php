
	

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT PARSING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseObject																		*
	 *==================================================================================*/

	/**
	 * Parse object
	 *
	 * The duty of this method is to traverse the current object structure, collect tag,
	 * offset and reference information and eventually validate the object properties.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter will receive the list of tags used in the
	 *		current object, the parameter is a reference to an array indexed by tag sequence
	 *		number holding the following elements:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key will contain
	 *			the tag data type.
	 *		<li><tt>{@link kTAG_DATA_KIND}</tt>: The item indexed by this key will contain
	 *			the tag data kinds.
	 *		<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: The item indexed by this key will
	 *			collect all the possible offsets at any depth level in which the current tag
	 *			holds a scalar value (not a structure).
	 *		 </ul>
	 *	<li><b>$theRefs</b>: This parameter will receive the list of all object references
	 *		held by the object, the parameter is an array reference in which the key is the
	 *		collection name and the value is a list of native identifiers of the referenced
	 *		objects held by the collection.
	 *	<li><b>$doValidate</b>: If this parameter is <tt>TRUE</tt>, the object's properties
	 *		will be validated and cast to their correct type.
	 * </ul>
	 *
	 * @param array					$theTags			Receives tag information.
	 * @param array					$theRefs			Receives references information.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 *
	 * @uses parseStructure()
	 */
	protected function parseObject( &$theTags, &$theRefs, $doValidate = TRUE )
	{
		//
		// Init local storage.
		//
		$path = $theTags = $theRefs = Array();
		
		//
		// Get object array copy.
		//
		$object = $this->getArrayCopy();
		
		//
		// Iterate properties.
		//
		$this->parseStructure(
			$object, $path, $theTags, $theRefs, $doValidate );
		
		//
		// Update object.
		//
		if( $doValidate )
			$this->exchangeArray( $object );
	
	} // parseObject.

	 
	/*===================================================================================
	 *	parseStructure																	*
	 *==================================================================================*/

	/**
	 * Parse property
	 *
	 * This method will parse the provided structure collecting tag, offset and object
	 * reference information in the provided reference parameters, the method will perform
	 * the following actions:
	 *
	 * <ul>
	 *	<li><b>Collect tag information</b>: The method will collect all tags referenced by
	 *		the leaf offsets of the provided structure and for each tag it will collect the
	 *		data type, data kind and offset path.
	 *	<li><b>Collect object references</b>: The method will collect all object references
	 *		contained in the provided structure, these references will be grouped by
	 *		collection.
	 *	<li><em>Validate properties</em>: The method will validate all properties of the
	 *		provided structure, if the last parameter is <tt>TRUE</tt>.
	 *	<li><em>Cast properties</em>: The method will cast all properties of the provided
	 *		structure to the expected data type, if the last parameter is <tt>TRUE</tt>.
	 * </ul>
	 *
	 * The above actions will only be applied to offsets not belonging to the list of
	 * internal offsets, {@link InternalOffsets()}, and the method will recursively be
	 * applied to all nested structures.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theStructure</b>: This parameter is a reference to an array containing the
	 *		structure.
	 *	<li><b>$thePath</b>: This parameter is a reference to an array representing the path
	 *		of offsets pointing to the provided structure.
	 *	<li><b>$theTags</b>: This parameter is a reference to an array which will receive
	 *		tag information related to the provided structure's offsets, the array is
	 *		structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The element key represents the tag sequence numbers referenced
	 *			by the offset. Each element is an array structured as follows:
	 *		 <ul>
	 +			<li>{@link kTAG_DATA_TYPE}</tt>: The item holding this key will contain the
	 *				tag data type.
	 +			<li>{@link kTAG_DATA_KIND}</tt>: The item holding this key will contain the
	 *				tag data kind; if the tag has no data kind, this item will be an empty
	 *				array.
	 +			<li>{@link kTAG_OBJECT_OFFSETS}</tt>: The item holding this key will
	 *				contain the list of offset paths in which the current tag is referenced
	 *				as a leaf offset (an offset holding a value, not a structure).
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter is a reference to an array which will receive
	 *		the list of object references held by the structure, the array is structured as
	 *		follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The element key represents a collection name.
	 *		<li><tt>value</tt>: The element value is the list of references to objects
	 *			belonging to the collection.
	 *	 </ul>
	 *	<li><b>$doValidate</b>: This boolean flag indicates whether the method should
	 *		validate and cast the structure elements.
	 * </ul>
	 *
	 * @param array					$theStructure		Structure.
	 * @param array					$thePath			Receives the offset path.
	 * @param array					$theTags			Receives the tag information.
	 * @param array					$theRefs			Receives the object references.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 *
	 * @uses InternalOffsets()
	 * @uses OffsetTypes()
	 * @uses parseProperty()
	 * @uses loadTagInformation()
	 * @uses loadReferenceInformation()
	 */
	protected function parseStructure( &$theStructure, &$thePath, &$theTags, &$theRefs,
										$doValidate )
	{
		//
		// Iterate properties.
		//
		$tags = array_keys( $theStructure );
		foreach( $tags as $tag )
		{
			//
			// Skip internal offsets.
			//
			if( ! in_array( $tag, static::InternalOffsets() ) )
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
				$property_ref = & $theStructure[ $tag ];
			
				//
				// Copy type and kind.
				//
				if( array_key_exists( $tag, $theTags ) )
				{
					//
					// Copy type and kind.
					//
					$type = $theTags[ $tag ][ kTAG_DATA_TYPE ];
					$kind = $theTags[ $tag ][ kTAG_DATA_KIND ];
	
				} // Already parsed.
	
				//
				// Determine type and kind.
				//
				else
					static::OffsetTypes(
						$this->mDictionary, $tag, $type, $kind, TRUE );
				
				//
				// Handle lists.
				//
				if( in_array( kTYPE_LIST, $kind ) )
				{
					//
					// Verify list.
					//
					if( ! is_array( $property_ref ) )
						throw new \Exception(
							"Invalid list in [$offset]: "
						   ."the value is not an array." );						// !@! ==>
					
					//
					// Iterate list elements.
					//
					$keys = array_keys( $property_ref );
					foreach( $keys as $key )
					{
						//
						// Reference element.
						//
						$element_ref = & $property_ref[ $key ];
						
						//
						// Handle structures.
						//
						if( $type == kTYPE_STRUCT )
						{
							//
							// Verify structure.
							//
							if( ! is_array( $element_ref ) )
								throw new \Exception(
									"Invalid structure in [$offset]: "
								   ."the value is not an array." );				// !@! ==>
				
							//
							// Parse structure.
							//
							$this->parseStructure(
								$element_ref,
								$thePath, $theTags, $theRefs, $doValidate );
						
						} // Structure.
						
						//
						// Handle scalars.
						//
						else
						{
							//
							// Parse property.
							//
							$class
								= $this->parseProperty(
									$element_ref, $type, $offset, $doValidate );
						
							//
							// Load tag information.
							//
							$this->loadTagInformation(
								$theTags, $kind, $type, $offset, $tag );
				
							//
							// Load reference information.
							//
							$this->loadReferenceInformation(
								$element_ref, $theRefs, $class, $type, $offset );
			
						} // Scalar.
					
					} // Iterating list elements.
				
				} // List.
				
				//
				// Handle structure.
				//
				elseif( $type == kTYPE_STRUCT )
				{
					//
					// Verify structure.
					//
					if( ! is_array( $property_ref ) )
						throw new \Exception(
							"Invalid structure value in [$offset]: "
						   ."the value is not an array." );						// !@! ==>
			
					//
					// Traverse structure properties.
					//
					$this->parseStructure(
						$property_ref,
						$thePath, $theTags, $theRefs, $doValidate );
				
				} // Structure.
				
				//
				// Handle scalar.
				//
				else
				{
					//
					// Parse property.
					//
					$class
						= $this->parseProperty(
							$property_ref, $type, $offset, $doValidate );
				
					//
					// Load tag information.
					//
					$this->loadTagInformation(
						$theTags, $kind, $type, $offset, $tag );
				
					//
					// Load reference information.
					//
					$this->loadReferenceInformation(
						$property_ref, $theRefs, $class, $type, $offset );
	
				} // Scalar.
		
				//
				// Pop offset from path.
				//
				array_pop( $thePath );
			
			} // Not an internal offset.
	
		} // Iterating properties.
		
	} // parseStructure.

	 
	/*===================================================================================
	 *	parseProperty																	*
	 *==================================================================================*/

	/**
	 * Parse property
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
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theProperty</b>: The property to parse, a scalar property is expected.
	 *	<li><b>$theType</b>: The property data type.
	 *	<li><b>$thePath</b>: The property offset path.
	 *	<li><b>$doValidate</b>: This boolean flag indicates whether the method should
	 *		validate and cast the structure elements.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 * @return string				Eventual property class name.
	 *
	 * @uses validateProperty()
	 * @uses getReferenceTypeClass()
	 * @uses parseReference()
	 * @uses validateReference()
	 * @uses castProperty()
	 */
	protected function parseProperty( &$theProperty,
									   $theType, $thePath, $doValidate )
	{
		//
		// Validate scalar.
		//
		if( $doValidate )
			$this->validateProperty(
				$theProperty, $theType, $thePath );
		
		//
		// Get reference class.
		//
		$class = $this->getReferenceTypeClass( $theType );
		
		//
		// Parse object reference.
		//
		if( $class !== NULL )
			$this->parseReference( $theProperty, $class, $thePath );
		
		//
		// Validate.
		//
		if( $doValidate )
		{
			//
			// Validate reference.
			//
			if( $class !== NULL )
				$this->validateReference(
					$theProperty, $theType, $class, $thePath );
			
			//
			// Cast value.
			//
			$this->castProperty(
				$theProperty, $theType, $thePath );
		
		} // Validate.
		
		return $class;																// ==>
		
	} // parseProperty.

	 
	/*===================================================================================
	 *	parseReference																	*
	 *==================================================================================*/

	/**
	 * Parse reference
	 *
	 * The duty of this method is to parse the provided reference expressed as an object,
	 * the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check class</em>: If the provided property is an object and is not an
	 *		instance of the provided class, the method will raise an exception.
	 *	<li><em>Commit object</em>: If the provided property is an uncommitted object, the
	 *		method will commit it.
	 *	<li><em>Check object identifier</em>: If the provided property is an object which
	 *		lacks its native identifier, the method will raise an exception.
	 *	<li><em>Use object native identifier</em>: The method will replace the object with
	 *		its native identifier.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theClass			Object class name.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 */
	protected function parseReference( &$theProperty, $theClass, $thePath )
	{
		//
		// Handle objects.
		//
		if( is_object( $theProperty ) )
		{
			//
			// Verify class.
			//
			if( ! ($theProperty instanceof $theClass) )
				throw new \Exception(
					"Invalid object reference in [$thePath]: "
				   ."incorrect class [$theClass]." );							// !@! ==>
	
			//
			// Commit object.
			//
			if( ! $theProperty->isCommitted() )
				$id = $theProperty->commit( $this->mDictionary );
	
			//
			// Get identifier.
			//
			elseif( ! $theProperty->offsetExists( kTAG_NID ) )
				throw new \Exception(
					"Invalid object in [$thePath]: "
				   ."missing native identifier." );								// !@! ==>
	
			//
			// Set identifier.
			//
			$theProperty = $theProperty[ kTAG_NID ];

		} // Property is an object.
		
	} // parseReference.

	 
	/*===================================================================================
	 *	validateProperty																*
	 *==================================================================================*/

	/**
	 * Validate property
	 *
	 * The duty of this method is to validate the provided scalar property. In this class
	 * we check whether the structure of the property is correct, we assert the following
	 * properties:
	 *
	 * <ul>
	 *	<li>We check whether structured data types are arrays.
	 *	<li>We check the contents of shapes.
	 *	<li>We assert that all other data types are not arrays.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 */
	protected final function validateProperty( &$theProperty,
												$theType, $thePath )
	{
		//
		// Validate property.
		//
		switch( $theType )
		{
			case kTYPE_SET:
			case kTYPE_ARRAY:
			case kTYPE_TYPED_LIST:
			case kTYPE_LANGUAGE_STRINGS:
				if( ! is_array( $theProperty ) )
					throw new \Exception(
						"Invalid offset value in [$thePath]: "
					   ."the value is not an array." );							// !@! ==>
				
				break;
			
			case kTYPE_SHAPE:
				if( ! is_array( $theProperty ) )
					throw new \Exception(
						"Invalid offset value in [$thePath]: "
					   ."the value is not an array." );							// !@! ==>
				
				if( (! array_key_exists( kTAG_SHAPE_TYPE, $theProperty ))
				 || (! array_key_exists( kTAG_SHAPE_GEOMETRY, $theProperty ))
				 || (! is_array( $theProperty[ kTAG_SHAPE_GEOMETRY ] )) )
					throw new \Exception(
						"Invalid offset value in [$thePath]: "
					   ."invalid shape geometry." );							// !@! ==>
				
				break;
			
			default:
				if( is_array( $theProperty ) )
					throw new \Exception(
						"Invalid offset value in [$thePath]: "
					   ."array not expected." );								// !@! ==>
				
				break;
		
		} // Parsed data type.
	
	} // validateProperty.

	 
	/*===================================================================================
	 *	validateReference																*
	 *==================================================================================*/

	/**
	 * Validate reference
	 *
	 * The duty of this method is to validate the provided object reference, the method will
	 * perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Cast reference</em>: The method will cast the object references.
	 *	<li><em>Assert reference</em>: The method will resolve the references.
	 * </ul>
	 *
	 * This method expects an object reference, not an object, the latter case must have
	 * been handled by the {@link parseReference()} method.
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param string				$theClass			Object class name.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 */
	protected final function validateReference( &$theProperty,
												 $theType, $theClass, $thePath )
	{
		//
		// Cast identifier.
		//
		switch( $theType )
		{
			case kTYPE_REF_TAG:
			case kTYPE_ENUM:
			case kTYPE_REF_TERM:
			case kTYPE_REF_EDGE:
			case kTYPE_REF_ENTITY:
			case kTYPE_REF_UNIT:
				$theProperty = (string) $theProperty;
				break;

			case kTYPE_REF_NODE:
				$theProperty = (int) $theProperty;
				break;

			case kTYPE_SET:
				foreach( $theProperty as $key => $val )
					$theProperty[ $key ] = (string) $val;
				break;
	
			case kTYPE_REF_SELF:
				switch( $theClass::kSEQ_NAME )
				{
					case Tag::kSEQ_NAME:
					case Term::kSEQ_NAME:
					case Edge::kSEQ_NAME:
					case UnitObject::kSEQ_NAME:
					case EntityObject::kSEQ_NAME:
						$theProperty = (string) $theProperty;
						break;
					case Node::kSEQ_NAME:
						$theProperty = (int) $theProperty;
						break;
				}
				break;

		} // Parsed type.

		//
		// Resolve collection.
		//
		$collection
			= $theClass::ResolveCollection(
				$theClass::ResolveDatabase( $this->mDictionary ) );

		//
		// Handle references list.
		//
		if( is_array( $theProperty ) )
		{
			//
			// Iterate list.
			//
			foreach( $theProperty as $val )
			{
				//
				// Assert reference.
				//
				if( ! $collection->matchOne( array( kTAG_NID => $val ), kQUERY_COUNT ) )
					throw new \Exception(
						"Unresolved reference in [$thePath]: "
					   ."($val)." );											// !@! ==>
			
			} // Iterating references list.
		
		} // List of references.
		
		//
		// Assert reference.
		//
		elseif( ! $collection->matchOne( array( kTAG_NID => $theProperty ), kQUERY_COUNT ) )
			throw new \Exception(
				"Unresolved reference in [$thePath]: "
			   ."($theProperty)." );											// !@! ==>
		
	} // validateReference.

	 
	/*===================================================================================
	 *	castProperty																	*
	 *==================================================================================*/

	/**
	 * Cast scalar
	 *
	 * The duty of this method is to cast the provided scalar property to the provided data
	 * type.
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @uses parseStructure()
	 * @uses castShapeGeometry()
	 */
	protected final function castProperty( &$theProperty,
											$theType, $thePath )
	{
		//
		// Cast property.
		//
		switch( $theType )
		{
			//
			// Strings.
			//
			case kTYPE_STRING:
			case kTYPE_ENUM:
			case kTYPE_URL:
			case kTYPE_REF_TAG:
			case kTYPE_REF_TERM:
			case kTYPE_REF_EDGE:
			case kTYPE_REF_UNIT:
			case kTYPE_REF_ENTITY:
				$theProperty = (string) $theProperty;
				break;
			
			//
			// Integers.
			//
			case kTYPE_INT:
			case kTYPE_REF_NODE:
				$theProperty = (int) $theProperty;
				break;
	
			//
			// Floats.
			//
			case kTYPE_FLOAT:
				$theProperty = (double) $theProperty;
				break;
	
			//
			// Enumerated sets.
			//
			case kTYPE_SET:
				//
				// Iterate set.
				//
				$idxs = array_keys( $theProperty );
				foreach( $idxs as $idx )
					$theProperty[ $idx ] = (string) $theProperty[ $idx ];
				break;
	
			//
			// Language strings.
			//
			case kTYPE_TYPED_LIST:
			case kTYPE_LANGUAGE_STRINGS:
				//
				// Init loop storage.
				//
				$tags = Array();
				$path = explode( '.', $thePath );
				//
				// Iterate elements.
				//
				$idxs = array_keys( $theProperty );
				foreach( $idxs as $idx )
				{
					//
					// Check format.
					//
					if( ! is_array( $theProperty[ $idx ] ) )
						throw new \Exception(
							"Invalid offset value element in [$thePath]: "
						   ."the value is not an array." );						// !@! ==>
					//
					// Traverse element.
					//
					$ref = $theProperty[ $idx ];
					$this->parseStructure(
						$theProperty[ $idx ], $path, $tags, $theRefs, TRUE );
				}
				break;
	
			//
			// Shapes.
			//
			case kTYPE_SHAPE:
				//
				// Cast geometry.
				//
				$this->castShapeGeometry( $theProperty );
				break;
	
		} // Parsed type.
	
	} // castProperty.

	 
	/*===================================================================================
	 *	loadTagInformation																*
	 *==================================================================================*/

	/**
	 * Load tag information
	 *
	 * The duty of this method is to load the provided tag information into the provided
	 * array reference, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter will receive the the tag information, it is a
	 *		reference to an array structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The element key represents the tag sequence number, the value
	 *			is an array structured as follows:
	 *		 <ul>
	 +			<li>{@link kTAG_DATA_TYPE}</tt>: The item holding this key will contain the
	 *				tag data type.
	 +			<li>{@link kTAG_DATA_KIND}</tt>: The item holding this key will contain the
	 *				tag data kind; if the tag has no data kind, this item will be an empty
	 *				array.
	 +			<li>{@link kTAG_OBJECT_OFFSETS}</tt>: The item holding this key will
	 *				contain the list of offset paths in which the current tag is referenced
	 *				as a leaf offset (an offset holding a value, not a structure).
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theKind</b>: This parameter holds the tag data kind, if missing, it will be
	 *		an empty array.
	 *	<li><b>$theType</b>: This parameter holds the tag data type, if missing, it will be
	 *		<tt>NULL</tt>.
	 *	<li><b>$thePath</b>: This parameter holds the offset path.
	 *	<li><b>$theTag</b>: This parameter holds the tag sequence number.
	 * </ul>
	 *
	 * @param array					$theTags			Receives tag information.
	 * @param array					$theKind			Data kind.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 * @param string				$theTag				Tag sequence number.
	 *
	 * @access protected
	 */
	public function loadTagInformation( &$theTags, &$theKind, $theType, $thePath, $theTag )
	{
		//
		// Copy tag information.
		//
		if( array_key_exists( $theTag, $theTags ) )
		{
			//
			// Update offset path.
			//
			if( ! in_array( $thePath, $theTags[ $theTag ][ kTAG_OBJECT_OFFSETS ] ) )
				$theTags[ $theTag ][ kTAG_OBJECT_OFFSETS ][] = $thePath;

		} // Already parsed.

		//
		// Collect tag information.
		//
		else
		{
			//
			// Set type and kind.
			//
			$theTags[ $theTag ][ kTAG_DATA_TYPE ] = $theType;
			$theTags[ $theTag ][ kTAG_DATA_KIND ] = $theKind;
	
			//
			// Set offset path.
			//
			$theTags[ $theTag ][ kTAG_OBJECT_OFFSETS ] = array( $thePath );

		} // New tag.
	
	} // loadTagInformation.

	 
	/*===================================================================================
	 *	loadReferenceInformation														*
	 *==================================================================================*/

	/**
	 * Load reference information
	 *
	 * The duty of this method is to load the provided array reference with the eventual
	 * object references, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theProperty</b>: This parameter represents the current property.
	 *	<li><b>$theRefs</b>: This parameter is a reference to an array which will receive
	 *		the list of object references held by the structure, the array is structured as
	 *		follows:
	 *	<li><b>$theType</b>: This parameter holds the tag data type, if missing, it will be
	 *		<tt>NULL</tt>.
	 *	<li><b>$thePath</b>: This parameter holds the offset path.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param array					$theRefs			Receives object references.
	 * @param string				$theClass			Class name.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @uses getReferenceTypeClass()
	 */
	public function loadReferenceInformation( &$theProperty, &$theRefs,
											   $theClass, $theType, $thePath )
	{
		//
		// Get reference class.
		//
		if( $theClass !== NULL )
		{
			//
			// Handle references list.
			//
			if( is_array( $theProperty ) )
			{
				//
				// Iterate list.
				//
				foreach( $theProperty as $reference )
				{
					//
					// Update references.
					//
					if( ! array_key_exists( $theClass, $theRefs ) )
						$theRefs[ $theClass ] = array( $reference );
					elseif( ! in_array( $theRefs[ $theClass ], $theProperty ) )
						$theRefs[ $theClass ][] = $reference;
			
				} // Iterating list.
		
			} // List of references.
		
			//
			// Scalar reference.
			//
			else
			{
				//
				// Update references.
				//
				if( ! array_key_exists( $theClass, $theRefs ) )
					$theRefs[ $theClass ] = array( $theProperty );
				elseif( ! in_array( $theRefs[ $theClass ], $theProperty ) )
					$theRefs[ $theClass ][] = $theProperty;
		
			} // Scalar reference.
		
		} // Is an object reference.
		
	} // loadReferenceInformation.






	 
	/*===================================================================================
	 *	traverseFilterOffsets															*
	 *==================================================================================*/

	/**
	 * Remove offsets from tags list
	 *
	 * This method is called for each element of the tags list parameter described in the
	 * {@link preDelete()} method, its duty is to check whether objects stored in the
	 * current object's collection still have the offsets of the current object, if that
	 * is not the case, these offsets will be remoived from the current iterator element:
	 * after this process the iterated list will only feature the offsets to be removed from
	 * the relative tag objects.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter points to the current element of the tags
	 *		list.
	 *	<li><b>$theCollection</b>: The collection of the current object.
	 * </ul>
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param CollectionObject		$theCollection		Current object collection.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 */
	protected function traverseFilterOffsets( \Iterator		   $theIterator,
											  CollectionObject $theCollection )
	{
		//
		// Init local storage.
		//
		$tag = (int) $theIterator->key();
		$info = $theIterator->current();
		$ref = & $info[ kTAG_OBJECT_OFFSETS ];
		$base_criteria = array( kTAG_ID_SEQUENCE => $tag );

		//
		// Iterate offsets.
		//
		foreach( $ref as $key => $offset )
		{
			//
			// Check offset.
			//
			$criteria = $base_criteria;
			$criteria[ kTAG_OBJECT_OFFSETS.".$tag" ] = $offset;
			if( $theCollection->matchAll( $criteria, kQUERY_ARRAY ) )
				unset( $ref[ $key ] );
		
		} // Iterating offsets.
		
		//
		// Handle empty list.
		//
		if( ! count( $ref ) )
			$theIterator->offsetUnset( $theIterator->key() );
		
		//
		// Update list.
		//
		else
		{
			//
			// Normalise list.
			//
			$ref = array_values( $ref );
			
			//
			// Update list.
			//
			$theIterator->offsetSet( $theIterator->key(), $info );
		
		} // Offsets left.
		
		return TRUE;																// ==>
	
	} // traverseFilterOffsets.

	 
	/*===================================================================================
	 *	traverseRemoveOffsets															*
	 *==================================================================================*/

	/**
	 * Remove offsets from tag objects
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
	protected function traverseRemoveOffsets( \Iterator		   $theIterator,
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
	
	} // traverseRemoveOffsets.
