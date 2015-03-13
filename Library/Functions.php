<?php

/**
 * Functions.php
 *
 * This file contains common function definitions.
 */

/**
 * Convex hull class.
 *
 * This file contains the definition of the convex hull class.
 */
require_once( kPATH_CLASSES_ROOT."/quickhull/convex_hull.php" );



/*=======================================================================================
 *																						*
 *									DIRECTORY UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DeleteFileDir																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete a file or directory</h4>
	 *
	 * If provided with a file path this function will delete it; if provided with a
	 * directory path, it will recursively remove its contents and delete it also.
	 *
	 * @param string				$thePath			Directory path.
	 *
	 * @return string				JSON string.
	 */
	function DeleteFileDir( $thePath )
	{
		//
		// Handle file.
		//
		if( is_file( $thePath ) )
			@unlink( $thePath );
		
		//
		// Handle directory.
		//
		elseif( is_dir( $thePath ) )
		{
			//
			// Get directory iterator.
			//
			$iter = new DirectoryIterator( $thePath );
			foreach( $iter as $file )
			{
				if( ! $file->isDot() )
					DeleteFileDir( $file->getRealPath() );
			}
			
			//
			// Remove directory.
			//
			@rmdir( $thePath );
		
		} // Provided directory.
	
	} // DeleteFileDir.



/*=======================================================================================
 *																						*
 *									JSON INTERFACE										*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	JsonEncode																		*
	 *==================================================================================*/

	/**
	 * <h4>Return JSON encoded data</h4>
	 *
	 * This function will return the provided array or object into a JSON encoded string.
	 *
	 * @param mixed					$theData			PHP data.
	 *
	 * @return string				JSON string.
	 *
	 * @uses JsonError()
	 */
	function JsonEncode( $theData )
	{
		//
		// Encode json.
		//
		$json = @json_encode( $theData );
		
		//
		// Handle errors.
		//
		JsonError( TRUE );
		
		return $json;																// ==>
	
	} // JsonEncode.

	 
	/*===================================================================================
	 *	JsonDecode																		*
	 *==================================================================================*/

	/**
	 * <h4>Return JSON decoded data</h4>
	 *
	 * This function will convert the provided JSON string into a PHP structure.
	 *
	 * @param string				$theData			JSON string.
	 *
	 * @return mixed				PHP data.
	 *
	 * @uses JsonError()
	 */
	function JsonDecode( $theData )
	{
		//
		// Decode JSON.
		//
		$decoded = @json_decode( $theData, TRUE );
		
		//
		// Handle errors.
		//
		JsonError( FALSE );
		
		return $decoded;															// ==>
	
	} // JsonDecode.

	 
	/*===================================================================================
	 *	JsonError																		*
	 *==================================================================================*/

	/**
	 * <h4>Return JSON errors</h4>
	 *
	 * This method will raise an exception according to the last JSON error
	 *
	 * @param boolean				$doEncode			<tt>TRUE</tt> for <i>encode</i>,
	 *													<tt>FALSE</tt> for <i>decode</i>.
	 *
	 * @throws Exception
	 *
	 * @see JSON_ERROR_DEPTH JSON_ERROR_STATE_MISMATCH
	 * @see JSON_ERROR_CTRL_CHAR JSON_ERROR_SYNTAX JSON_ERROR_UTF8
	 */
	function JsonError( $doEncode )
	{
		//
		// Init local storage.
		//
		$sense = ( $doEncode )? 'encode' : 'decode';
		
		//
		// Handle errors.
		//
		switch( json_last_error() )
		{
			case JSON_ERROR_DEPTH:
				throw new Exception
					( "JSON $sense error: maximum stack depth exceeded" );		// !@! ==>

			case JSON_ERROR_STATE_MISMATCH:
				throw new Exception
					( "JSON $sense error: invalid or malformed JSON" );			// !@! ==>

			case JSON_ERROR_CTRL_CHAR:
				throw new Exception
					( "JSON $sense error: unexpected control character found" );// !@! ==>

			case JSON_ERROR_SYNTAX:
				throw new Exception
					( "JSON $sense error: syntax error, malformed JSON" );		// !@! ==>

			case JSON_ERROR_UTF8:
				throw new Exception
					( "JSON $sense error: malformed UTF-8 characters, "
					 ."possibly incorrectly encoded" );							// !@! ==>
		}
	
	} // JsonError.



/*=======================================================================================
 *																						*
 *										PO INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	PO2Array																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert a PO file into an array</h4>
	 *
	 * This function will parse the provided PO file and return its contents as an array in
	 * which the element's key represents the english string and the element's value the
	 * translated string.
	 *
	 * If any error occurs, the function will raise an exception; if the file is empty, the
	 * function will return <tt>NULL</tt>.
	 *
	 * @param string				$theFile			File path.
	 *
	 * @return array				Parsed key/value array.
	 *
	 * @uses JsonError()
	 */
	function PO2Array( $theFile )
	{
		//
		// Read file.
		//
		$file = file_get_contents( $theFile );
		if( $file !== FALSE )
		{
			//
			// Match english strings in file.
			//
			$count = preg_match_all( '/msgid ("(.*)"\n)+/', $file, $match );
			if( $count === FALSE )
				throw new Exception
						( "Error parsing the file [$theFile]",
						  kERROR_STATE );										// !@! ==>
			
			//
			// Normalise matches.
			//
			$match = $match[ 0 ];
			
			//
			// Normalise english strings.
			//
			$keys = Array();
			while( ($line = array_shift( $match )) !== NULL )
			{
				//
				// Get strings.
				//
				$count = preg_match_all( '/"(.*)"/', $line, $strings );
				if( $count === FALSE )
					throw new Exception
							( "Error parsing the file [$theFile]",
							  kERROR_STATE );									// !@! ==>
				
				//
				// Merge strings.
				//
				$strings = $strings[ 1 ];
				if( count( $strings ) > 1 )
				{
					$tmp = '';
					foreach( $strings as $item )
						$tmp .= $item;
					$keys[] = $tmp;
				}
				else
					$keys[] = $strings[ 0 ];
			}
			
			//
			// Match translated strings in file.
			//
			$count = preg_match_all( '/msgstr ("(.*)"\n)+/', $file, $match );
			if( $count === FALSE )
				throw new Exception
						( "Error parsing the file [$theFile]",
						  kERROR_STATE );										// !@! ==>
			
			//
			// Normalise matches.
			//
			$match = $match[ 0 ];
			
			//
			// Normalise english strings.
			//
			$values = Array();
			while( ($line = array_shift( $match )) !== NULL )
			{
				//
				// Get strings.
				//
				$count = preg_match_all( '/"(.*)"/', $line, $strings );
				if( $count === FALSE )
					throw new Exception
							( "Error parsing the file [$theFile]",
							  kERROR_STATE );									// !@! ==>
				
				//
				// Merge strings.
				//
				$strings = $strings[ 1 ];
				if( count( $strings ) > 1 )
				{
					$tmp = '';
					foreach( $strings as $item )
						$tmp .= $item;
					$values[] = $tmp;
				}
				else
					$values[] = $strings[ 0 ];
			}
			
			//
			// Combine array.
			//
			$matches = array_combine( $keys, $values );
			
			//
			// Get rid of header.
			//
			array_shift( $matches );
			
			return $matches;														// ==>
		
		} // Read the file.
		
		throw new Exception
				( "Unable to read the file [$theFile]",
				  kERROR_STATE );												// !@! ==>
	
	} // PO2Array.



/*=======================================================================================
 *																						*
 *									STRING UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	UTF82XML																		*
	 *==================================================================================*/

	/**
	 * <h4>Filter unicode characters</h4>
	 *
	 * This function will filter invalid UTF8 characters for XML, it will skip all UTF8
	 * characters that are not allowed in XML.
	 *
	 * The function will only filter arguments that are strings, other types are returned
	 * as provided.
	 *
	 * If the provided string is empty, the function will return <tt>NULL</tt>.
	 *
	 * @param string				$theString			String to filter.
	 *
	 * @return string				Filtered string.
	 */
	function UTF82XML( $theString )
	{
		//
		// Check string
		//
		if( is_string( $theString ) )
		{
			//
			// Check if empty.
			//
			if( strlen( $theString ) )
			{
				//
				// INIT LOCAL STORAGE.
				//
				$string = '';
				$length = strlen( $theString );
				
				//
				// SCAN STRING.
				//
				for( $i = 0; $i < $length; $i++ )
				{
					//
					// Filter invalid unicode characters.
					//
					$current = ord( substr( $theString, $i, 1 ) );
					if( ($current == 0x9)
					 || ($current == 0xA)
					 || ($current == 0xD)
					 || ( ($current >= 0x20)
					   && ($current <= 0xD7FF) )
					 || ( ($current >= 0xE000)
					   && ($current <= 0xFFFD) )
					 || ( ($current >= 0x10000)
					   && ($current <= 0x10FFFF) ) )
						$string .= chr( $current );
	
				} // Scanning string.
				
				return $string;														// ==>
			
			} // Not empty.
			
			return NULL;															// ==>
		
		} // Is a string.
		
		return $theString;															// ==>
	
	} // UTF82XML.

	 
	/*===================================================================================
	 *	DisplayDate																		*
	 *==================================================================================*/

	/**
	 * Return display date
	 *
	 * This function will parse the provided date expected in <tt>YYYYMMDDhhmmss</tt> format
	 * and return a string that can be used to display the date.
	 *
	 * The provided date must have at least the year and full time if day and month are
	 * provided.
	 *
	 * @param string				$theDate			YYYYMMDDhhmmss date.
	 *
	 * @return string				Display date.
	 */
	function DisplayDate( $theDate )
	{
		//
		// Validate type.
		//
		if( ctype_digit( $theDate ) )
		{
			//
			// Init local storage.
			//
			$yea = $mon = NULL;
			
			//
			// Parse by length.
			//
			switch( strlen( $theDate ) )
			{
				case 14:
					if( checkdate( substr( $theDate, 4, 2 ),
								   substr( $theDate, 6, 2 ),
								   substr( $theDate, 0, 4 ) ) )
					{
						$date = new DateTime( substr( $theDate, 0, 4 ).'-'
											 .substr( $theDate, 4, 2 ).'-'
											 .substr( $theDate, 6, 2 ).' '
											 .substr( $theDate, 8, 2 ).':'
											 .substr( $theDate, 10, 2 ).':'
											 .substr( $theDate, 12, 2 ) );
						return $date->format( 'D M j, Y G:i:s' );						// ==>
					}
					else
						return $theDate;											// ==>
					
				case 8:
					if( checkdate( substr( $theDate, 4, 2 ),
								   substr( $theDate, 6, 2 ),
								   substr( $theDate, 0, 4 ) ) )
					{
						$date = new DateTime( substr( $theDate, 0, 4 ).'-'
											 .substr( $theDate, 4, 2 ).'-'
											 .substr( $theDate, 6, 2 ) );
						return $date->format( 'D M j, Y' );							// ==>
					}
					else
						return $theDate;											// ==>
					
				case 6:
					if( checkdate( substr( $theDate, 4, 2 ),
								   1,
								   substr( $theDate, 0, 4 ) ) )
						$mon
							= DateTime::createFromFormat(
								'!m', (int) substr( $theDate, 4, 2 ) )
								->format( 'M' );
					else
						return $theDate;											// ==>
				case 4:
					$yea = substr( $theDate, 0, 4 );
					return "$mon $yea";												// ==>
					break;
				
				default:
					return $theDate;												// ==>
					break;
			
			} // Parse by length.
		
		} // Numeric string.
		
		return $theDate;															// ==>
	
	} // DisplayDate.



/*=======================================================================================
 *																						*
 *									GEOMETRIC UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Centroid																		*
	 *==================================================================================*/

	/**
	 * <h4>Calculate the centroid of a set of points</h4>
	 *
	 * This function will return the centroid of a set of points. It expects an array of
	 * X/Y coordinates and will return a X/Y coordinate as an array.
	 *
	 * @param array					$thePoints			Array of points.
	 * @return array				The centroid as a X/Y coordinate.
	 */
	function Centroid( $thePoints )
	{
		//
		// Init local storage
		//
		$x = $y = 0;
		
		//
		// Iterate points.
		//
		foreach( $thePoints as $point )
		{
			$x += $point[ 0 ];
			$y += $point[ 1 ];
		}
		
		return array( $x / count( $thePoints ), $y / count( $thePoints ) );			// ==>
	
	} // Centroid.

	 
	/*===================================================================================
	 *	Polygon																			*
	 *==================================================================================*/

	/**
	 * <h4>Calculate the polygon of a set of points</h4>
	 *
	 * This function will return the polygon surrounding a set of points. It expects an
	 * array of X/Y coordinates and will return an X/Y coordinates array representing the
	 * polygon enclosing the provided points.
	 *
	 * The first and last points of the returned array will be the same.
	 *
	 * @param array					$thePoints			Array of points.
	 * @return array				The polygon as a X/Y coordinates.
	 */
	function Polygon( $thePoints )
	{
		//
		// Calculate convex hull.
		//
		$hull = new ConvexHull( $thePoints );
		
		//
		// Get polygon.
		//
		$poly = $hull->getHullPoints();
		
		//
		// Set first and last points.
		//
		if( ($poly[ 0 ][ 0 ] != $poly[ count( $poly ) - 1 ][ 0 ])
		 || ($poly[ 0 ][ 1 ] != $poly[ count( $poly ) - 1 ][ 1 ]) )
			$poly[] = $poly[ 0 ];
		
		return $poly;																// ==>
	
	} // Polygon.



/*=======================================================================================
 *																						*
 *									GENERIC UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CollectArrayKeys																*
	 *==================================================================================*/

	/**
	 * <h4>Collect array keys</h4>
	 *
	 * This function will traverse the array provided in the first parameter and return in
	 * the second parameter the list of all the found array keys.
	 *
	 * @param reference			   &$theArray			Array to traverse.
	 * @param reference			   &$theKeys			Resulting keys.
	 */
	function CollectArrayKeys( &$theArray, &$theKeys )
	{
		//
		// Init keys
		//
		if( ! is_array( $theKeys ) )
			$theKeys = Array();
		
		//
		// Handle array.
		//
		if( is_array( $theArray ) )
		{
			//
			// Iterate array.
			//
			$keys = array_keys( $theArray );
			foreach( $keys as $key )
			{
				//
				// Add key.
				//
				$theKeys[] = $key;
				
				//
				// Recurse.
				//
				if( is_array( $theArray[ $key ] ) )
					CollectArrayKeys( $theArray[ $key ], $theKeys );
			
			} // Iterating array.
		
		} // Provided an array.
	
	} // CollectArrayKeys.

	 
	/*===================================================================================
	 *	CollectOffsetValues																*
	 *==================================================================================*/

	/**
	 * <h4>Collect array values</h4>
	 *
	 * This function will parse the provided offset and return the pointed value or values.
	 *
	 * The function expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theObject</tt>: Reference to the object containing the offset as an array.
	 *	<li><b>$theOffset</tt>: The offset, <b><i>provide a copy of the object, since this
	 *		parameter will be overwritten</i></b>.
	 * </ul>
	 *
	 * The method will return the reference to the value.
	 *
	 * @param reference			   &$theObject			Offset container.
	 * @param reference			   &$theOffset			Offset (provide a copy).
	 *
	 * @return reference			Reference to the offset value.
	 */
	function & CollectOffsetValues( &$theObject, &$theOffset )
	{
		//
		// Explode offset.
		//
		if( ! is_array( $theOffset ) )
			$theOffset = explode( '.', $theOffset );
		
		//
		// Get current offset value.
		//
		$offset = array_shift( $theOffset );
		
		//
		// Found value.
		//
		if( ! count( $theOffset ) )
			return $theObject[ $offset ];											// ==>
		
		return CollectOffsetValues( $theObject[ $offset ], $theOffset );			// ==>
		
	} // CollectOffsetValues.

	 
	/*===================================================================================
	 *	ParseCoordinate																	*
	 *==================================================================================*/

	/**
	 * Parse coordinate
	 *
	 * This function will parse the provided coordinate which should be in the
	 * <tt>DDD°MM.MMMM'SS.SSSS"H</tt> format where <tt>D</tt> stands for degrees,
	 * <tt>M</tt> for minutes, <tt>S</tt> for seconds and <tt>H</tt> for hemisphere.
	 *
	 * The function will return an array indexed by <tt>D</tt>, <tt>M</tt>, <tt>S</tt> and
	 * <tt>H</tt>; if the no coordinate pattern is recognised, the function will return an
	 * empty array.
	 *
	 * The provided coordinate requires the degrees and the hemisphere, other elements are
	 * optional. The elements will be cast to integer or double, if the value contains a
	 * decimal point.
	 *
	 * This fumction will not validate the coordinate, this is the responsibility of the
	 * caller.
	 *
	 * @param string				$theCoordinate		Coordinate.
	 *
	 * @return array				Parsed coordinate elements.
	 */
	function ParseCoordinate( $theCoordinate )
	{
		//
		// Init local storage.
		//
		$result = Array();
		
		//
		// Parse coordinate.
		//
		if( preg_match( '/^(\d+)\°([\d\.]*)[\']{0,1}([\d\.]*)[\"]{0,1}([nNsSeEwW])/',
						$theCoordinate,
						$items ) )
		{
			//
			// Set degrees.
			//
			$result[ 'D' ] = (int) $items[ 1 ];
			
			//
			// Normalise elements.
			//
			$elements = Array();
			foreach( $items as $item )
			{
				if( strlen( $item = trim( $item ) ) )
					$elements[] = $item;
			}
			
			//
			// Parse by size.
			//
			switch( count( $elements ) )
			{
				case 3:
					$result[ 'H' ] = strtoupper( $elements[ 2 ] );
					break;
				
				case 4:
					if( is_numeric( $elements[ 2 ] ) )
						$result[ 'M' ] = ( strpos( $elements[ 2 ], '.' ) !== FALSE )
									   ? (double) $elements[ 2 ]
									   : (int) $elements[ 2 ];
					else
						return Array();												// ==>
					$result[ 'H' ] = strtoupper( $elements[ 3 ] );
					break;
				
				case 5:
					if( is_numeric( $elements[ 2 ] ) )
						$result[ 'M' ] = ( strpos( $elements[ 2 ], '.' ) !== FALSE )
									   ? (double) $elements[ 2 ]
									   : (int) $elements[ 2 ];
					else
						return Array();												// ==>
					if( is_numeric( $elements[ 3 ] ) )
						$result[ 'S' ] = ( strpos( $elements[ 3 ], '.' ) !== FALSE )
									   ? (double) $elements[ 3 ]
									   : (int) $elements[ 3 ];
					else
						return Array();												// ==>
					$result[ 'H' ] = strtoupper( $elements[ 4 ] );
					break;
				
				default:
					return Array();													// ==>
			}
		
		} // Parsed coordinate.
		
		return $result;																// ==>
		
	} // ParseCoordinate.

	 
	/*===================================================================================
	 *	ParseGeometry																	*
	 *==================================================================================*/

	/**
	 * Parse geometry
	 *
	 * This function will parse the provided geometry as a list of linear ring coordinate
	 * arrays.
	 *
	 * The colon (<tt>:</tt>) token divides the rings, the semicolon (<tt>;</tt>) token divides
	 * the points and the comma (<tt>,</tt>) token divides the coordinates.
	 *
	 * The method will return an array structured as follows:
	 *
	 * <ul>
	 *	<li><tt>rings</tt>: The array of rings.
	 *	  <ul>
	 *		<li><tt>coordinates</tt>: The array of coordinates.
	 *		  <ul>
	 *			<li><tt>point</tt>: The longitude/latitude pair.
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * If the provided coordinate has one ring and one coordinate, the point may have three
	 * elements, where the third element represents a circle radius in meters.
	 *
	 * In all other cases, if the provided string does not respect this structure, the
	 * method will return <tt>FALSE</tt>.
	 *
	 * @param string				$theCoordinate		Geometry string.
	 *
	 * @return array				Parsed geometry or <tt>FALSE</tt> on errors.
	 */
	function ParseGeometry( $theCoordinate )
	{
		//
		// Init local storage.
		//
		$radius = FALSE;
		$geometry = Array();
		
		//
		// Collect rings.
		//
		$rings = explode( ':', $theCoordinate );
		foreach( $rings as $ring )
		{
			//
			// Trim ring.
			//
			$ring = trim( $ring );
			if( strlen( $ring ) )
			{
				//
				// Allocate ring.
				//
				$index_ring = count( $geometry );
				$geometry[ $index_ring ] = Array();
				$ref_ring = & $geometry[ $index_ring ];
				
				//
				// Collect points.
				//
				$points = explode( ';', $ring );
				foreach( $points as $point )
				{
					//
					// Trim point.
					//
					$point = trim( $point );
					if( strlen( $point ) )
					{
						//
						// Allocate point.
						//
						$index_point = count( $ref_ring );
						$ref_ring[ $index_point ] = Array();
						$ref_point = & $ref_ring[ $index_point ];
				
						//
						// Collect coordinates.
						//
						$coordinates = explode( ';', $point );
						foreach( $coordinates as $coordinate )
						{
							//
							// Trim coordinate.
							//
							$coordinate = trim( $coordinate );
							if( strlen( $coordinate ) )
							{
								//
								// Collect longitude and latitude.
								//
								$items = Array();
								
								//
								// Trim items.
								//
								foreach( explode( ',', $coordinate ) as $item )
								{
									//
									// Trim element.
									//
									$item = trim( $item );
									if( strlen( $item ) )
										$items[] = $item;
								
								} // Iterating coordinate elements.
								
								//
								// Handle point.
								//
								if( count( $items ) == 2 )
								{
									//
									// Check elements.
									//
									foreach( $items as $item )
									{
										if( ! is_numeric( $item ) )
											return FALSE;							// ==>
									}
									
									//
									// Cast elements.
									//
									$items[ 0 ] = (double) $items[ 0 ];
									$items[ 1 ] = (double) $items[ 1 ];
									
									//
									// Set element.
									//
									$ref_point = $items;
								
								} // Found point.
								
								//
								// Handle circle.
								//
								elseif( count( $items ) == 3 )
								{
									//
									// No two circle elements.
									//
									if( $radius )
										return FALSE;								// ==>
									
									//
									// Set flag.
									//
									$radius = TRUE;
									
									//
									// Check elements.
									//
									foreach( $items as $item )
									{
										if( ! is_numeric( $item ) )
											return FALSE;							// ==>
									}
									
									//
									// Cast elements.
									//
									$items[ 0 ] = (double) $items[ 0 ];
									$items[ 1 ] = (double) $items[ 1 ];
									$items[ 2 ] = (int) $items[ 2 ];
									
									//
									// Set element.
									//
									$ref_point = $items;
								
								} // Found radius.
								
								//
								// Invalid point.
								//
								else
									return FALSE;									// ==>
							
							} // Coordinate not empty.
				
						} // Iterating coordinates.
			
					} // Point not empty.
				
				} // Iterating points.
			
			} // Ring not empty.
			
			//
			// Check radius.
			//
			if( $radius
			 && (count( $geometry[ $index_ring ] ) > 1) )
				return FALSE;														// ==>
		
		} // Iterating rings.
		
		//
		// Handle empty geometry.
		//
		if( ! count( $geometry ) )
			return FALSE;															// ==>
		
		return $geometry;															// ==>
		
	} // ParseGeometry.

	 
	/*===================================================================================
	 *	SetAsCDATA																		*
	 *==================================================================================*/

	/**
	 * Set CDATA element
	 *
	 * This function will set the provided XML element with the provided value as a CDATA
	 * section.
	 *
	 * @param SimpleXMLElement		$theElement			XML element.
	 * @param mixed					$theValue			Value to set.
	 */
	function SetAsCDATA( SimpleXMLElement $theElement, $theValue )
	{
		//
		// Import element in DOM.
		//
		$node = dom_import_simplexml( $theElement );
		
		//
		// Get ownerDocument.
		//
		$owner = $node->ownerDocument;
		
		//
		// Set CDATA section.
		//
		$node->appendChild( $owner->createCDATASection( (string) $theValue ) );
		
	} // SetAsCDATA.

	 
	/*===================================================================================
	 *	CheckIntegerValue																*
	 *==================================================================================*/

	/**
	 * Validate integer value
	 *
	 * This function will ensure that the provided value can be cast to an integer, it will
	 * return the following values:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: The value can be cast to an integer.
	 *	<li><tt>FALSE</tt>: The value cannot be cast to an integer.
	 *	<li><tt>NULL</tt>: The value is empty.
	 * </ul>
	 *
	 * The value will be converted to a string and trimmed, if it is empty, the method will
	 * return <tt>NULL</tt> without modifying the provided value.
	 *
	 * If the value is numeric, the method will cast the provided value to an integer and
	 * return <tt>TRUE</tt>.
	 *
	 * If the value is not numeric, the method will return <tt>FALSE</tt> without modifying
	 * the provided value.
	 *
	 * @param mixed				   &$theValue			Value.
	 *
	 * @return mixed				<tt>TRUE</tt> correct value.
	 */
	function CheckIntegerValue( &$theValue )
	{
		//
		// Check integer.
		//
		if( is_int( $theValue ) )
			return TRUE;															// ==>
		
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $theValue ) )
			return NULL;															// ==>
		
		//
		// Check if numeric.
		//
		if( ! is_numeric( $value ) )
			return FALSE;															// ==>
		
		//
		// Cast value.
		//
		$theValue = (int) $value;
		
		return TRUE;																// ==>

	} // CheckIntegerValue.

	 
	/*===================================================================================
	 *	CheckFloatValue																	*
	 *==================================================================================*/

	/**
	 * Validate float value
	 *
	 * This function will ensure that the provided value can be cast to an double, it will
	 * return the following values:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: The value can be cast to an double.
	 *	<li><tt>FALSE</tt>: The value cannot be cast to an double.
	 *	<li><tt>NULL</tt>: The value is empty.
	 * </ul>
	 *
	 * The value will be converted to a string and trimmed, if it is empty, the method will
	 * return <tt>NULL</tt> without modifying the provided value.
	 *
	 * If the value is numeric, the method will cast the provided value to a double and
	 * return <tt>TRUE</tt>.
	 *
	 * If the value is not numeric, the method will return <tt>FALSE</tt> without modifying
	 * the provided value.
	 *
	 * @param mixed				   &$theValue			Value.
	 *
	 * @return mixed				<tt>TRUE</tt> correct value.
	 */
	function CheckFloatValue( &$theValue )
	{
		//
		// Check double.
		//
		if( is_float( $theValue )
		 || is_double( $theValue ) )
			return TRUE;															// ==>
		
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $theValue ) )
			return NULL;															// ==>
		
		//
		// Check if numeric.
		//
		if( ! is_numeric( $value ) )
			return FALSE;															// ==>
		
		//
		// Cast value.
		//
		$theValue = (double) $value;
		
		return TRUE;																// ==>

	} // CheckFloatValue.

	 
	/*===================================================================================
	 *	CheckBooleanValue																*
	 *==================================================================================*/

	/**
	 * Validate boolean value
	 *
	 * This function will ensure that the provided value can be interpreted as a boolean, it
	 * will return the following values:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: The value can be considered a boolean.
	 *	<li><tt>FALSE</tt>: The value cannot be considered a boolean.
	 *	<li><tt>NULL</tt>: The value is empty.
	 * </ul>
	 *
	 * The value will be converted to a string and trimmed, if it is empty, the method will
	 * return <tt>NULL</tt> without modifying the provided value.
	 *
	 * The value will be checked for the following types:
	 *
	 * <ul>
	 *	<li><tt>y</tt>: <tt>TRUE</tt>.
	 *	<li><tt>n</tt>: <tt>FALSE</tt>.
	 *	<li><tt>yes</tt>: <tt>TRUE</tt>.
	 *	<li><tt>no</tt>: <tt>FALSE</tt>.
	 *	<li><tt>true</tt>: <tt>TRUE</tt>.
	 *	<li><tt>false</tt>: <tt>FALSE</tt>.
	 *	<li><tt>1</tt>: <tt>TRUE</tt>.
	 *	<li><tt>0</tt>: <tt>FALSE</tt>.
	 * </ul>
	 *
	 * If the value is not among the above choices, the method will return <tt>FALSE</tt>
	 * without modifying the provided value.
	 *
	 * @param mixed				   &$theValue			Value.
	 *
	 * @return mixed				<tt>TRUE</tt> correct value.
	 */
	function CheckBooleanValue( &$theValue )
	{
		//
		// Check boolean.
		//
		if( is_bool( $theValue ) )
			return TRUE;															// ==>
		
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $value ) )
			return NULL;															// ==>
		
		//
		// Cast value.
		//
		switch( strtolower( $value ) )
		{
			case '1':
			case 'y':
			case 'yes':
			case 'true':
				$theValue = TRUE;
				return TRUE;														// ==>
		
			case '0':
			case 'n':
			case 'no':
			case 'false':
				$theValue = FALSE;
				return TRUE;														// ==>
			
			default:
				return FALSE;														// ==>
		
		} // Parsing value.

	} // CheckBooleanValue.

	 
	/*===================================================================================
	 *	CheckArrayValue																	*
	 *==================================================================================*/

	/**
	 * Validate array value
	 *
	 * This function will convert the provided string to a {@link kTYPE_ARRAY} type value.
	 *
	 * Such values are key/value pairs, in order to split elements, the function expects a
	 * parameter that represents the the separator tokens: the first token will separate
	 * elements, the second token will separate the key from the value.
	 *
	 * If no token is provided, the function will return <tt>FALSE</tt> with the value
	 * untouched.
	 *
	 * This function will return the following values:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: The value was split into an array.
	 *	<li><tt>FALSE</tt>: Missing tokens, value untouched.
	 *	<li><tt>NULL</tt>: The value is empty, value untouched.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 * @param string				$theTokens			Tokens.
	 *
	 * @return mixed				<tt>TRUE</tt> split values.
	 */
	function CheckArrayValue( &$theValue, $theTokens = NULL )
	{
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $theValue ) )
			return NULL;															// ==>
		
		//
		// Get tokens count.
		//
		$count = strlen( $theTokens );
		if( ! $count )
			return FALSE;															// ==>
		
		//
		// Split elements.
		//
		$result = Array();
		$elements = explode( substr( $theTokens, 0, 1 ), $value );
		foreach( $elements as $element )
		{
			//
			// Slip empty elements.
			//
			if( ! strlen( $element = trim( $element ) ) )
				continue;													// =>
			
			//
			// Handle no key.
			//
			if( $count == 1 )
				$result[] = $element;
			
			//
			// Split key/value.
			//
			else
			{
				//
				// Split.
				//
				if( CheckArrayValue( $element, substr( $theTokens, 1, 1 ) ) )
				{
					//
					// Set value.
					//
					if( count( $element ) == 1 )
						$result[] = $element[ 0 ];
					
					//
					// Set key.
					//
					elseif( count( $element ) == 2 )
						$result[ $element[ 0 ] ] = $element[ 1 ];
					
					//
					// Handle mess.
					//
					else
					{
						//
						// Set key.
						//
						$key = $element[ 0 ];
						
						//
						// Reconstitute value.
						//
						array_shift( $element );
						$value = implode( substr( $theTokens, 1, 1 ), $element );
						
						//
						// Set element.
						//
						$result[ $key ] = $value;
					
					} // More than two elements.
				
				} // Was split.
			
			} // Split key/value.
		
		} // Iterating elements.
		
		//
		// Check if empty.
		//
		if( ! count( $result ) )
			return NULL;															// ==>
		
		//
		// Cast to array.
		//
		$theValue = $result;
		
		return TRUE;																// ==>

	} // CheckArrayValue.

	 
	/*===================================================================================
	 *	CheckShapeValue																	*
	 *==================================================================================*/

	/**
	 * Validate shape value
	 *
	 * This function will convert the provided string to a shape value, by default a shape
	 * is provided as a string of the form <tt>type</tt>=geometry where the equal
	 * (<tt>=</tt>) sign separates the shape type from the geometry, the semicolon
	 * (<tt>;</tt>) separates longitude/latitude pairs, the comma (<tt>,</tt>) separates the
	 * longitude from the latitude and the colon (<tt>:</tt>) separates the eventual linear
	 * ring coordinate arrays.
	 *
	 * These are the valid shape types:
	 *
	 * <ul>
	 *	<tt>Point</tt>: A point <tt>Point=lon,lat</tt>.
	 *	<tt>Circle</tt>: A circle <tt>Circle=lon,lat,radius</tt>.
	 *	<tt>MultiPoint</tt>: A collection of points <tt>MultiPoint=lon,lat;lon,lat...</tt>.
	 *	<tt>LineString</tt>: A collection of lines <tt>LineString=lon,lat;lon,lat...</tt>,
	 *		in this case there must be at least two pairs of coordinates.
	 *	<tt>Polygon</tt>: A polygon <tt>Polygon=lon,lat;lon,lat:lon,lat;lon,lat...</tt>,
	 *		where the colon (<tt>:</tt>) separates the linear ring coordinate arrays: the
	 *		first coordinate array represents the exterior ring, the other eventual elements
	 *		the interior rings or holes.
	 * </ul>
	 *
	 * This function will return the following values:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: The shape is correct and was set.
	 *	<li><tt>NULL</tt>: The value is empty, value untouched.
	 *	<li><tt>kTYPE_ERROR_CODE_NO_SHAPE_TYPE</tt>: Missing shape type.
	 *	<li><tt>kTYPE_ERROR_CODE_BAD_SHAPE_TYPE</tt>: Invalid or unsupported shape type.
	 *	<li><tt>kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY</tt>: Invalid shape geometry.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 *
	 * @return mixed				<tt>TRUE</tt> set value.
	 */
	function CheckShapeValue( &$theValue )
	{
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $theValue ) )
			return NULL;															// ==>
		
		//
		// Get type.
		//
		$items = explode( '=', $theValue );
		if( count( $items ) == 2 )
		{
			//
			// Save by type.
			//
			$type = trim( $items[ 0 ] );
			
			//
			// Handle point.
			//
			if( $type == 'Point' )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Check ring.
					//
					if( count( $geometry ) == 1 )
					{
						//
						// Check points.
						//
						if( count( $geometry[ 0 ] ) == 1 )
						{
							//
							// Check coordinates.
							//
							if( count( $geometry[ 0 ][ 0 ] ) == 2 )
							{
								//
								// Set shape.
								//
								$theValue
									= array( kTAG_TYPE => $type,
											 kTAG_GEOMETRY => $geometry[ 0 ][ 0 ] );
								
								return TRUE;										// ==>
							
							} // Two coordinates.
						
						} // One point.
					
					} // One ring.
				
				} // Correct geometry.
			
			} // Point.
			
			//
			// Handle circle.
			//
			elseif( $type == 'Circle' )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Check ring.
					//
					if( count( $geometry ) == 1 )
					{
						//
						// Check points.
						//
						if( count( $geometry[ 0 ] ) == 1 )
						{
							//
							// Check coordinates.
							//
							if( count( $geometry[ 0 ][ 0 ] ) == 3 )
							{
								//
								// Set shape.
								//
								$theValue
									= array( kTAG_TYPE => $type,
											 kTAG_RADIUS => $geometry[ 0 ][ 0 ][ 2 ],
											 kTAG_GEOMETRY
											 	=> array( $geometry[ 0 ][ 0 ][ 0 ],
											 			  $geometry[ 0 ][ 0 ][ 1 ] ) );
								
								return TRUE;										// ==>
							
							} // Two coordinates.
						
						} // One point.
					
					} // One ring.
				
				} // Correct geometry.
			
			} // Circle.
			
			//
			// Handle multipoint.
			//
			elseif( ($type == 'MultiPoint')
				 || ($type == 'LineString') )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Check ring.
					//
					if( count( $geometry ) == 1 )
					{
						//
						// Check points.
						//
						if( count( $geometry[ 0 ] ) > 1 )
						{
							//
							// Set shape.
							//
							$theValue
								= array( kTAG_TYPE => $type,
										 kTAG_GEOMETRY => $geometry[ 0 ] );
							
							return TRUE;											// ==>
						
						} // One point.
					
					} // One ring.
				
				} // Correct geometry.
			
			} // MultiPoint or LineString.
			
			//
			// Handle polygon.
			//
			elseif( $type == 'Polygon' )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Set shape.
					//
					$theValue
						= array( kTAG_TYPE => $type,
								 kTAG_GEOMETRY => $geometry );
					
					return TRUE;													// ==>
				
				} // Correct geometry.
			
			} // Polygon.
			
			return kTYPE_ERROR_CODE_BAD_SHAPE_TYPE;									// ==>
		
		} // Has type.
		
		//
		// Handle missing type.
		//
		else
			return kTYPE_ERROR_CODE_NO_SHAPE_TYPE;									// ==>
		
		return kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY;									// ==>

	} // CheckShapeValue.

	 
	/*===================================================================================
	 *	CheckDateValue																	*
	 *==================================================================================*/

	/**
	 * Validate shape value
	 *
	 * This function will convert the provided string to a date value, the method will
	 * return the following values:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: The date is correct and was set.
	 *	<li><tt>NULL</tt>: The date is empty, value untouched.
	 *	<li><tt>kTYPE_ERROR_CODE_BAD_DATE_FORMAT</tt>: Bad date format.
	 *	<li><tt>kTYPE_ERROR_CODE_BAD_DATE</tt>: Bad date value.
	 *	<li><tt>kTYPE_ERROR_CODE_DUBIOUS_YEAR</tt>: Dubious year.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 *
	 * @return mixed				<tt>TRUE</tt> set value.
	 */
	function CheckDateValue( &$theValue )
	{
		//
		// Cast date.
		//
		$date = $theValue = (string) $theValue;
		
		//
		// Handle non-standard format.
		//
		if( ! ctype_digit( $theValue ) )
		{
			//
			// Check - separator.
			//
			if( strpos( $date, '-' ) === FALSE )
			{
				//
				// Check / separator.
				//
				if( strpos( $date, '/' ) === FALSE )
				{
					//
					// Check space separator.
					//
					if( strpos( $date, ' ' ) === FALSE )
						return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;					// ==>
					else
						$items = explode( ' ', $date );
				
				} // No slash separator.
				
				else
					$items = explode( '/', $date );
			
			} // No dash separator.
			
			else
				$items = explode( '-', $date );
			
			//
			// Normalise elements.
			//
			$elements = Array();
			foreach( $items as $item )
			{
				if( strlen( $item = trim( $item ) ) )
					$elements[] = $item;
			}
			
			//
			// Check format.
			//
			if( (! count( $elements ))										// No elements,
			 || ( (strlen( $elements[ 0 ] ) != 4)							// or no start y
			   && (strlen( $elements[ count( $elements ) - 1 ] ) != 4) ) )	// and no end y.
				return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;							// ==>
			
			//
			// Init date.
			//
			$date = '';
			
			//
			// Check YYYYMMDD.
			//
			if( strlen( $elements[ 0 ] ) == 4 )
			{
				foreach( $elements as $element )
					$date .= $element;
			}
			
			//
			// Check DDMMYYYY.
			//
			else
			{
				for( $i = count( $elements ) - 1; $i >= 0; $i-- )
					$date .= $elements[ $i ];
			}
		
		} // Non-standard format.
		
		//
		// Check date content.
		//
		if( ! ctype_digit( $date ) )
			return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;								// ==>
		
		//
		// Check full date.
		//
		if( strlen( $date ) == 8 )
		{
			$y = (int) substr( $date, 0, 4 );
			$m = (int) substr( $date, 4, 2 );
			$d = (int) substr( $date, 6, 2 );
		}
	
		//
		// Month.
		//
		elseif( strlen( $date ) == 6 )
		{
			$y = (int) substr( $date, 0, 4 );
			$m = (int) substr( $date, 4, 2 );
			$d = 1;
		}
	
		//
		// Year.
		//
		elseif( strlen( $date ) == 4 )
		{
			$y = (int) substr( $date, 0, 4 );
			$m = 1;
			$d = 1;
		}
		
		//
		// Bad format.
		//
		else
			return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;								// ==>
		
		//
		// Check date.
		//
		if( ! checkdate( $m, $d, $y ) )
			return kTYPE_ERROR_CODE_BAD_DATE;										// ==>
		
		//
		// Check year.
		//
		if( ($y < 1900)
		 || ($y > (int) date( "Y" )) )
			return kTYPE_ERROR_CODE_DUBIOUS_YEAR;									// ==>
		
		//
		// Set date.
		//
		$theValue = $date;
		
		return TRUE;																// ==>

	} // CheckDateValue.

	 
	/*===================================================================================
	 *	checkStringCombinations															*
	 *==================================================================================*/

	/**
	 * Check string combinations
	 *
	 * This function will return all combinations of the provided prefix, string and suffix.
	 *
	 * If there are no combinations, the function will return <tt>NULL</tt>.
	 *
	 * @param string				$theString			String.
	 * @param array					$thePrefix			String prefixes.
	 * @param array					$theSuffix			String suffixes.
	 *
	 * @return array				Combinations.
	 */
	function checkStringCombinations( $theString, $thePrefix = NULL, $theSuffix = NULL )
	{
		//
		// Normalise string.
		//
		$theString = trim( $theString );
		
		//
		// Handle no prefix or suffix.
		//
		if( ($thePrefix === NULL)
		 && ($theSuffix === NULL) )
		{
			//
			// Handle no combinations.
			//
			if( ! strlen( $theString ) )
				return Array();														// ==>
			
			return array( $theString );												// ==>
		
		} // Has no prefix nor suffix.
		
		//
		// Cast prefix and suffix.
		//
		if( ($thePrefix !== NULL)
		 && (! is_array( $thePrefix )) )
			$thePrefix = array( (string) $thePrefix );
		if( ($theSuffix !== NULL)
		 && (! is_array( $theSuffix )) )
			$theSuffix = array( (string) $theSuffix );
		
		//
		// Init local storage.
		//
		$combinations = Array();
		
		//
		// Iterate prefixes.
		//
		if( is_array( $thePrefix ) )
		{
			//
			// Iterate prefixes.
			//
			foreach( $thePrefix as $prefix )
			{
				//
				// Handle suffixes.
				//
				if( is_array( $theSuffix ) )
				{
					//
					// Iterate suffixes.
					//
					foreach( $theSuffix as $suffix )
						$combinations[] = $prefix.$theString.$suffix;
				
				} // Has suffixes.
				
				//
				// Handle no suffixes.
				//
				else
					$combinations[] = $prefix.$theString;
			
			} // Iterating prefixes.
		
		} // Has prefixes.
		
		//
		// Iterate suffixes.
		//
		else
		{
			//
			// Iterate suffixes.
			//
			foreach( $theSuffix as $suffix )
				$combinations[] = $theString.$suffix;
		
		} // Has suffixes.
		
		return $combinations;														// ==>

	} // checkStringCombinations.


?>
