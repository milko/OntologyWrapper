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
						$elements ) )
		{
			//
			// Set degrees.
			//
			$result[ 'D' ] = (int) $elements[ 1 ];
			
			//
			// Parse by size.
			//
			switch( count( $elements ) )
			{
				case 3:
					$result[ 'H' ] = $elements[ 2 ];
					break;
				
				case 4:
					if( is_numeric( $elements[ 2 ] ) )
						$result[ 'M' ] = ( strpos( '.', $elements[ 2 ] ) !== FALSE )
									   ? (double) $elements[ 2 ]
									   : (int) $elements[ 2 ];
					else
						return Array();												// ==>
					$result[ 'H' ] = $elements[ 3 ];
					break;
				
				case 5:
					if( is_numeric( $elements[ 2 ] ) )
						$result[ 'M' ] = ( strpos( '.', $elements[ 2 ] ) !== FALSE )
									   ? (double) $elements[ 2 ]
									   : (int) $elements[ 2 ];
					else
						return Array();												// ==>
					if( is_numeric( $elements[ 3 ] ) )
						$result[ 'M' ] = ( strpos( '.', $elements[ 3 ] ) !== FALSE )
									   ? (double) $elements[ 3 ]
									   : (int) $elements[ 3 ];
					else
						return Array();												// ==>
					$result[ 'H' ] = $elements[ 4 ];
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


?>
