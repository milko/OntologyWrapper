	
	
	/*===================================================================================
	 *	setWorksheetProperties															*
	 *==================================================================================*/

	/**
	 * Set worksheets properties
	 *
	 * This method will traverse the provided list of child worksheets loading the provided
	 * object's properties.
	 *
	 * @param mixed					$theObject			Object or array.
	 * @param array					$theWorksheets		List of child worksheets.
	 * @param mixed					$theParentKey		Parent worksheet key value.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function setWorksheetProperties( $theObject, $theWorksheets, $theParentKey )
	{
		//
		// Iterate worksheets.
		//
		foreach( $theWorksheets as $worksheet_info )
		{
			//
			// Init local storage.
			//
			$container = Array();
			$worksheet = $this->mIterator->getList()[ $worksheet_info[ 'N' ] ][ 'W' ];
			$index = $this->mIterator->getList()[ $worksheet_info[ 'N' ] ][ 'F' ];
			$struct_tag
				= $this->mParser->getTag(
					$this->mParser->getNode(
						$this->mParser->getWorksheets()[ $worksheet ][ 'node' ] )
							->offsetGet( kTAG_TAG ) );
			$is_list = ( $struct_tag->offsetExists( kTAG_DATA_KIND )
					  && in_array( kTYPE_LIST, $struct_tag->offsetGet( kTAG_DATA_KIND ) ) );
			
			//
			// Select worksheet records.
			//
			$records
				= $this->mCollections[ $this->getCollectionName( $worksheet ) ]
					->matchAll( array( $index => $theParentKey ),
								kQUERY_ARRAY );
			
			//
			// Load worksheet records.
			//
			if( $records->count() )
			{
				//
				// Iterate records.
				//
				foreach( $records as $record )
				{
					//
					// Reference container.
					//
					if( $is_list )
					{
						$container[] = Array();
						$reference = & $container[ count( $container ) - 1 ];
					}
					else
						$reference = & $container;
					
					//
					// Set object properties.
					//
					if( $this->setObjectProperties( $reference, $root, $record ) )
						return FALSE;												// ==>
					
					//
					// Handle related worksheets.
					//
					if( array_key_exists( 'C', $theWorksheets ) )
					{
						//
						// Traverse worksheet structure.
						//
						if( ! $this->setWorksheetProperties(
								$reference,
								$theWorksheets[ 'C' ],
								@@@ MILKO - find worksheet index @@@
							return FALSE;											// ==>
			
					} // Has related worksheets.
		
				} // Iterating records.
	
				//
				// Update object.
				//
				$object[ $tag->offsetGet( kTAG_ID_HASH ) ] = $container;
			
			} // Has records.
		
		} // Iterating worksheets.
		
		return TRUE;																// ==>

	} // setWorksheetProperties.
