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
 *								DATA VALIDATION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CheckIntegerValue																*
	 *==================================================================================*/

	/**
	 * Validate integer value
	 *
	 * This function will ensure that the provided value can be cast to an integer, If that
	 * is the case, the function will cast the provided value and return <tt>TRUE</tt>.
	 *
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * If the value cannot be cast to an integer, the function will do the following:
	 *
	 * <ul>
	 *	<li>Set the <tt>$theErrorType</tt> reference to {@link kTYPE_ERROR_INVALID_VALUE}.
	 *	<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *	<li>Return {@link kTYPE_ERROR_CODE_BAD_NUMBER}.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 * @param mixed				   &$theErrorType		Receives error type.
	 * @param mixed				   &$theErrorMessage	Receives error message.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckIntegerValue( &$theValue, &$theErrorType, &$theErrorMessage )
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
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid integer value.';
			return kTYPE_ERROR_CODE_BAD_NUMBER;										// ==>
		}
		
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
	 * This function will ensure that the provided value can be cast to a double, If that
	 * is the case, the function will cast the provided value and return <tt>TRUE</tt>.
	 *
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * If the value cannot be cast to a float, the function will do the following:
	 *
	 * <ul>
	 *	<li>Set the <tt>$theErrorType</tt> reference to {@link kTYPE_ERROR_INVALID_VALUE}.
	 *	<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *	<li>Return {@link kTYPE_ERROR_CODE_BAD_NUMBER}.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 * @param mixed				   &$theErrorType		Receives error type.
	 * @param mixed				   &$theErrorMessage	Receives error message.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckFloatValue( &$theValue, &$theErrorType, &$theErrorMessage )
	{
		//
		// Check float.
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
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid floating point value.';
			return kTYPE_ERROR_CODE_BAD_NUMBER;										// ==>
		}
		
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
	 * This function will ensure that the provided value can be interpreted as a boolean, if
	 * that is the case, the function will cast the provided value and return <tt>TRUE</tt>.
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
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * If the value is not among the above choices, the function will do the following:
	 *
	 * <ul>
	 *	<li>Set the <tt>$theErrorType</tt> reference to {@link kTYPE_ERROR_INVALID_VALUE}.
	 *	<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *	<li>Return {@link kTYPE_ERROR_CODE_BAD_BOOLEAN}.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 * @param mixed				   &$theErrorType		Receives error type.
	 * @param mixed				   &$theErrorMessage	Receives error message.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckBooleanValue( &$theValue, &$theErrorType, &$theErrorMessage )
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
		
		} // Parsing value.
		
		$theErrorType = kTYPE_ERROR_INVALID_VALUE;
		$theErrorMessage = 'Invalid boolean value.';
		return kTYPE_ERROR_CODE_BAD_BOOLEAN;										// ==>

	} // CheckBooleanValue.

	 
	/*===================================================================================
	 *	CheckArrayValue																	*
	 *==================================================================================*/

	/**
	 * Validate array value
	 *
	 * This function will convert the provided string to a {@link kTYPE_ARRAY} type value,
	 * if the operation was successful, the function will return <tt>TRUE</tt>.
	 *
	 * {@link kTYPE_ARRAY} instances are a list of key/value pairs, the function expects
	 * a parameter containing at most two tokens which will be used to parse the provided
	 * string value and extract the array elements.
	 *
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * The function will not return any error codes, it is assumed that the tokens are
	 * either one or two: the first token will be used to split the array elements, the
	 * second to split the elements into key/value pairs.
	 *
	 * @param string			   &$theValue			Value.
	 * @param string				$theTokens			Separator tokens.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckArrayValue( &$theValue, $theTokens )
	{
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $theValue ) )
			return NULL;															// ==>
		
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
			if( strlen( $theTokens ) == 1 )
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
	 * This function will convert the provided string to a shape value. By default a shape
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
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * If the value cannot be cast to a shape, the function will do the following:
	 *
	 * <ul>
	 *	<li>Missing shape type:
	 *	  <ul>
	 *		<li>Set the <tt>$theErrorType</tt> reference to
	 *			{@link kTYPE_ERROR_INVALID_VALUE}.
	 *		<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *		<li>Return {@link kTYPE_ERROR_CODE_NO_SHAPE_TYPE}.
	 *	  </ul>
	 *	<li>Invalid or unsupported shape type:
	 *	  <ul>
	 *		<li>Set the <tt>$theErrorType</tt> reference to
	 *			{@link kTYPE_ERROR_INVALID_VALUE}.
	 *		<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *		<li>Return {@link kTYPE_ERROR_CODE_BAD_SHAPE_TYPE}.
	 *	  </ul>
	 *	<li>Invalid shape geometry:
	 *	  <ul>
	 *		<li>Set the <tt>$theErrorType</tt> reference to
	 *			{@link kTYPE_ERROR_INVALID_VALUE}.
	 *		<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *		<li>Return {@link kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY}.
	 *	  </ul>
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 * @param mixed				   &$theErrorType		Receives error type.
	 * @param mixed				   &$theErrorMessage	Receives error message.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckShapeValue( &$theValue, &$theErrorType, &$theErrorMessage )
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
			
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid or unsupported shape type.';
			return kTYPE_ERROR_CODE_BAD_SHAPE_TYPE;									// ==>
		
		} // Has type.
		
		//
		// Handle missing type.
		//
		else
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Missing shape type.';
			return kTYPE_ERROR_CODE_NO_SHAPE_TYPE;									// ==>
		}
		
		$theErrorType = kTYPE_ERROR_INVALID_VALUE;
		$theErrorMessage = 'Invalid shape geometry.';
		return kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY;									// ==>

	} // CheckShapeValue.

	 
	/*===================================================================================
	 *	CheckLinkValue																	*
	 *==================================================================================*/

	/**
	 * Validate link value
	 *
	 * This function will ensure that the provided URL value is active, if that is the case,
	 * the function will return <tt>TRUE</tt>.
	 *
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * If the link cannot be reached, the function will do the following:
	 *
	 * <ul>
	 *	<li>Set the <tt>$theErrorType</tt> reference to {@link kTYPE_ERROR_INVALID_VALUE}.
	 *	<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *	<li>Return {@link kTYPE_ERROR_CODE_BAD_LINK}.
	 * </ul>
	 *
	 * @param mixed				   &$theValue			Value.
	 * @param mixed				   &$theErrorType		Receives error type.
	 * @param mixed				   &$theErrorMessage	Receives error message.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckLinkValue( &$theValue, &$theErrorType, &$theErrorMessage )
	{
		//
		// Trim value.
		//
		$value = trim( $theValue );
		if( ! strlen( $theValue ) )
			return NULL;															// ==>
		
		//
		// Check link.
		//
/* @@@ MILKO
		if( @get_headers( $value ) === FALSE )
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid or inactive link.';
			return kTYPE_ERROR_CODE_BAD_LINK;										// ==>
		}
*/
		
		return TRUE;																// ==>

	} // CheckLinkValue.

	 
	/*===================================================================================
	 *	CheckDateValue																	*
	 *==================================================================================*/

	/**
	 * Validate shape value
	 *
	 * This function will convert the provided string to a date value, if that is the case,
	 * the function will cast the provided value and return <tt>TRUE</tt>
	 *
	 * If the provided value is an empty string or <tt>NULL</tt>, the function will return
	 * <tt>NULL</tt>.
	 *
	 * If the value cannot be converted to a date, the function will do the following:
	 *
	 * <ul>
	 *	<li>Bad date format:
	 *	  <ul>
	 *		<li>Set the <tt>$theErrorType</tt> reference to
	 *			{@link kTYPE_ERROR_INVALID_VALUE}.
	 *		<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *		<li>Return {@link kTYPE_ERROR_CODE_BAD_DATE_FORMAT}.
	 *	  </ul>
	 *	<li>Bad date value:
	 *	  <ul>
	 *		<li>Set the <tt>$theErrorType</tt> reference to
	 *			{@link kTYPE_ERROR_INVALID_VALUE}.
	 *		<li>Set the <tt>$theErrorMessage</tt> reference to the error message.
	 *		<li>Return {@link kTYPE_ERROR_CODE_BAD_DATE}.
	 *	  </ul>
	 *	<li>Dubious year:
	 *	  <ul>
	 *		<li>Set the <tt>$theErrorType</tt> reference to
	 *			{@link kTYPE_WARNING_DUBIOUS_VALUE}.
	 *		<li>Set the <tt>$theErrorMessage</tt> reference to the warning message.
	 *		<li>Return {@link kTYPE_ERROR_CODE_DUBIOUS_YEAR}.
	 *	  </ul>
	 * </ul>
	 *
	 * @param string			   &$theValue			Value.
	 * @param mixed				   &$theErrorType		Receives error type.
	 * @param mixed				   &$theErrorMessage	Receives error message.
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>NULL</tt> or error code.
	 */
	function CheckDateValue( &$theValue, &$theErrorType, &$theErrorMessage )
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
					{
						$theErrorType = kTYPE_ERROR_INVALID_VALUE;
						$theErrorMessage = 'Invalid date format.';
						return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;					// ==>
					}
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
			{
				$theErrorType = kTYPE_ERROR_INVALID_VALUE;
				$theErrorMessage = 'Invalid date format.';
				return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;							// ==>
			}
			
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
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid date format.';
			return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;								// ==>
		}
		
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
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid date format.';
			return kTYPE_ERROR_CODE_BAD_DATE_FORMAT;								// ==>
		}
		
		//
		// Check date.
		//
		if( ! checkdate( $m, $d, $y ) )
		{
			$theErrorType = kTYPE_ERROR_INVALID_VALUE;
			$theErrorMessage = 'Invalid date value.';
			return kTYPE_ERROR_CODE_BAD_DATE;										// ==>
		}
		
		//
		// Check year.
		//
		if( ($y < 1900)
		 || ($y > (int) date( "Y" )) )
		{
			$theErrorType = kTYPE_WARNING_DUBIOUS_VALUE;
			$theErrorMessage = 'Double check if year is correct.';
			return kTYPE_ERROR_CODE_DUBIOUS_YEAR;									// ==>
		}
		
		//
		// Set date.
		//
		$theValue = $date;
		
		return TRUE;																// ==>

	} // CheckDateValue.

	 
	/*===================================================================================
	 *	CheckStringCombinations															*
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
	function CheckStringCombinations( $theString, $thePrefix = NULL, $theSuffix = NULL )
	{
		//
		// Normalise string.
		//
		$theString = trim( $theString );
		
		//
		// Normalise prefix.
		//
		if( $thePrefix !== NULL )
		{
			if( ! is_array( $thePrefix ) )
				$thePrefix = ( ! strlen( $thePrefix = trim( $thePrefix ) ) )
						   ? NULL
						   : array( $thePrefix );
		}
		
		//
		// Normalise suffix.
		//
		if( $theSuffix !== NULL )
		{
			if( ! is_array( $theSuffix ) )
				$theSuffix = ( ! strlen( $theSuffix = trim( $theSuffix ) ) )
						   ? NULL
						   : array( $theSuffix );
		}
		
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

	} // CheckStringCombinations.



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
	 *	SetLanguageString																*
	 *==================================================================================*/

	/**
	 * Set a language string entry
	 *
	 * This function can be used to add an entry to a language string property, type
	 * {@link kTYPE_LANGUAGE_STRING}, the function expects the destination container, the
	 * language code and the string.
	 *
	 * @param array				   &$theContainer		Language string container.
	 * @param string				$theLanguage		Language code.
	 * @param string				$theString			String.
	 *
	 * @return boolean				<tt>TRUE</tt> added, <tt>FALSE</tt> updated.
	 */
	function SetLanguageString( &$theContainer, $theLanguage, $theString )
	{
		//
		// Init container
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();

		//
		// Trim parameters
		//
		$theString = trim( $theString );
		$theLanguage = trim( $theLanguage );
		
		//
		// Skip empty string.
		//
		if( strlen( $theString ) )
		{
			//
			// Handle language.
			//
			if( strlen( $theLanguage ) )
			{
				//
				// Locate language.
				//
				foreach( $theContainer as $key => $value )
				{
					if( array_key_exists( kTAG_LANGUAGE, $value )
					 && ($value[ kTAG_LANGUAGE ] == $theLanguage) )
					{
						$theContainer[ $key ][ kTAG_TEXT ] = $theString;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_LANGUAGE => $theLanguage,
										 kTAG_TEXT => $theString );
				
				return TRUE;														// ==>
			
			} // Has language.
			
			//
			// Handle no language.
			//
			else
			{
				//
				// Locate no language.
				//
				foreach( $theContainer as $key => $value )
				{
					if( ! array_key_exists( kTAG_LANGUAGE, $value ) )
					{
						$theContainer[ $key ][ kTAG_TEXT ] = $theString;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_TEXT => $theString );
				
				return TRUE;														// ==>
			
			} // No language.
		
		} // Not an empty string.

	} // SetLanguageString.

	 
	/*===================================================================================
	 *	SetLanguageStrings																*
	 *==================================================================================*/

	/**
	 * Set a language strings entry
	 *
	 * This function can be used to add an entry to a language strings property, type
	 * {@link kTYPE_LANGUAGE_STRINGS}, the function expects the destination container, the
	 * language code and the strings.
	 *
	 * @param array				   &$theContainer		Language string container.
	 * @param string				$theLanguage		Language code.
	 * @param array					$theStrings			Strings.
	 *
	 * @return boolean				<tt>TRUE</tt> added, <tt>FALSE</tt> updated.
	 */
	function SetLanguageStrings( &$theContainer, $theLanguage, $theStrings )
	{
		//
		// Init container
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();

		//
		// Trim parameters
		//
		$theLanguage = trim( $theLanguage );
		
		//
		// Skip empty strings.
		//
		if( count( $theStrings ) )
		{
			//
			// Trim strings.
			//
			$strings = Array();
			foreach( $theStrings as $string )
			{
				if( strlen( $tmp = trim( $string ) ) )
					$strings[] = $tmp;
			}
			
			//
			// Normalise strings.
			//
			$theStrings = array_values( array_unique( $strings ) );
			
			//
			// Handle language.
			//
			if( strlen( $theLanguage ) )
			{
				//
				// Locate language.
				//
				foreach( $theContainer as $key => $value )
				{
					//
					// Match language.
					//
					if( array_key_exists( kTAG_LANGUAGE, $value )
					 && ($value[ kTAG_LANGUAGE ] == $theLanguage) )
					{
						//
						// Iterate strings.
						//
						foreach( $theStrings as $string )
						{
							if( ! in_array( $string, $theContainer[ $key ][ kTAG_TEXT ] ) )
								$theContainer[ $key ][ kTAG_TEXT ][]
									= $string;
						}

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_LANGUAGE => $theLanguage,
										 kTAG_TEXT => $theStrings );
				
				return TRUE;														// ==>
			
			} // Has language.
			
			//
			// Handle no language.
			//
			else
			{
				//
				// Locate no language.
				//
				foreach( $theContainer as $key => $value )
				{
					//
					// Match no language.
					//
					if( ! array_key_exists( kTAG_LANGUAGE, $value ) )
					{
						//
						// Iterate strings.
						//
						foreach( $theStrings as $string )
						{
							if( ! in_array( $string, $theContainer[ $key ][ kTAG_TEXT ] ) )
								$theContainer[ $key ][ kTAG_TEXT ][]
									= $string;
						}

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_TEXT => $theStrings );
				
				return TRUE;														// ==>
			
			} // No language.
		
		} // Not an empty string.

	} // SetLanguageStrings.

	 
	/*===================================================================================
	 *	SetTypedList																	*
	 *==================================================================================*/

	/**
	 * Set a typed list entry
	 *
	 * This function can be used to add an entry to a typed list property, type
	 * {@link kTYPE_TYPED_LIST}, the function expects the destination container, the value
	 * tag, the language code and the string.
	 *
	 * @param array				   &$theContainer		Language string container.
	 * @param string				$theTag				Value tag.
	 * @param string				$theType			Language code.
	 * @param string				$theValue			String.
	 *
	 * @return boolean				<tt>TRUE</tt> added, <tt>FALSE</tt> updated.
	 */
	function SetTypedList( &$theContainer, $theTag, $theType, $theValue )
	{
		//
		// Init container
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();

		//
		// Trim parameters
		//
		$theValue = trim( $theValue );
		$theType = trim( $theType );
		
		//
		// Skip empty value.
		//
		if( strlen( $theValue ) )
		{
			//
			// Handle type.
			//
			if( strlen( $theType ) )
			{
				//
				// Locate type.
				//
				foreach( $theContainer as $key => $value )
				{
					if( array_key_exists( kTAG_TYPE, $value )
					 && ($value[ kTAG_TYPE ] == $theType) )
					{
						$theContainer[ $key ][ $theTag ] = $theValue;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_TYPE => $theType,
										 $theTag => $theValue );
				
				return TRUE;														// ==>
			
			} // Has type.
			
			//
			// Handle no type.
			//
			else
			{
				//
				// Locate no type.
				//
				foreach( $theContainer as $key => $value )
				{
					if( ! array_key_exists( kTAG_TYPE, $value ) )
					{
						$theContainer[ $key ][ $theTag ] = $theValue;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( $theTag => $theValue );
				
				return TRUE;														// ==>
			
			} // No type.
		
		} // Not an empty value.

	} // SetTypedList.

	 
	/*===================================================================================
	 *	GetLocalTransformations															*
	 *==================================================================================*/

	/**
	 * Retrieve local transformations
	 *
	 * This function expects a template node object and will return in the provided
	 * reference parameters the eventual collection name, prefix and suffix strings related
	 * to the transformations of the node's current tag.
	 *
	 * @param OntologyWrapper\Node $theNode			Template node object.
	 * @param string			   &$theCollection		Receives collection name.
	 * @param string			   &$thePrefix			Receives prefix string.
	 * @param string			   &$theSuffix			Receives suffix string.
	 */
	function GetLocalTransformations( OntologyWrapper\Node $theNode,
														  &$theCollection,
														  &$thePrefix,
														  &$theSuffix )
	{
		//
		// Init parameters.
		//
		$theCollection = $thePrefix = $theSuffix = NULL;
		
		//
		// Handle transformations.
		//
		if( $theNode->offsetExists( kTAG_TRANSFORM ) )
		{
			//
			// Iterate transformation records.
			//
			foreach( $theNode->offsetGet( kTAG_TRANSFORM ) as $record )
			{
				//
				// Select the one without tag reference.
				//
				if( ! array_key_exists( kTAG_TAG, $record ) )
				{
					//
					// Set collection name.
					//
					if( array_key_exists( kTAG_CONN_COLL, $record ) )
						$theCollection = $record[ kTAG_CONN_COLL ];
				
					//
					// Set prefix strings.
					//
					if( array_key_exists( kTAG_PREFIX, $record ) )
						$thePrefix = $record[ kTAG_PREFIX ];
				
					//
					// Set suffix strings.
					//
					if( array_key_exists( kTAG_SUFFIX, $record ) )
						$theSuffix = $record[ kTAG_SUFFIX ];
				
				} // Matched local transformations
			
			} // Iterating transformations.
		
		} // Has transformations.

	} // GetLocalTransformations.

	 
	/*===================================================================================
	 *	GetExternalTransformations														*
	 *==================================================================================*/

	/**
	 * Retrieve external transformations
	 *
	 * This function expects a template node object and will return the list of
	 * transformations to be applied to external tags.
	 *
	 * @param OntologyWrapper\Node $theNode			Template node object.
	 *
	 * @return array				List of external transformations.
	 */
	function GetExternalTransformations( OntologyWrapper\Node $theNode )
	{
		//
		// Init local storage.
		//
		$trans = Array();
		
		//
		// Handle transformations.
		//
		if( $theNode->offsetExists( kTAG_TRANSFORM ) )
		{
			//
			// Iterate transformation records.
			//
			foreach( $theNode->offsetGet( kTAG_TRANSFORM ) as $record )
			{
				//
				// Select the one with tag reference.
				//
				if( array_key_exists( kTAG_TAG, $record ) )
					$trans[] = $record;
			
			} // Iterating transformations.
		
		} // Has transformations.
		
		return $trans;																// ==>

	} // GetExternalTransformations.

	 
	/*===================================================================================
	 *	SetLocalTransformations															*
	 *==================================================================================*/

	/**
	 * Set current transformations
	 *
	 * This function will prefix the provided string with the provided prefix and append
	 * the provided suffix to the provided string returning the transformed value.
	 *
	 * The transformation will only be applied if the provided prefix and the suffix have
	 * a single element.
	 *
	 * The container may also be provided as an array, in which case the transformation
	 * will be applied to each of its elements.
	 *
	 * @param string				$theContainer		Strings container.
	 * @param array					$thePrefix			Prefix strings list.
	 * @param array					$theSuffix			Suffix strings list.
	 *
	 * @return mixed				Transformed container.
	 */
	function SetLocalTransformations( $theContainer, $thePrefix, $theSuffix )
	{
		//
		// Handle array.
		//
		if( is_array( $theContainer ) )
		{
			foreach( array_keys( $theContainer ) as $key )
				$theContainer[ $key ]
					= SetLocalTransformations(
						$theContainer[ $key ], $thePrefix, $theSuffix );
		}
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Ensure prefix and/or suffix are scalars.
			//
			if( ( ($thePrefix !== NULL)
			   && (count( $thePrefix ) == 1) )
			 || ( ($theSuffix !== NULL)
			   && (count( $theSuffix ) == 1) ) )
			{
				//
				// Trim string.
				//
				$theContainer = triem( $theContainer );
				
				//
				// Handle prefix.
				//
				if( $thePrefix !== NULL )
					$theContainer = $thePrefix[ 0 ].$theContainer;
			
				//
				// Handle suffix.
				//
				if( $theSuffix !== NULL )
					$theContainer = $theContainer.$theSuffix[ 0 ];
			}
		}
		
		return $theContainer;														// ==>

	} // SetLocalTransformations.

	 
	/*===================================================================================
	 *	UpdateProcessCounter															*
	 *==================================================================================*/

	/**
	 * Update session or transaction counter
	 *
	 * This function can be used to update the counter of the provided {@link SessionObject}
	 * instance, it will check whether the delay interval is longer than the
	 * {@link kSTANDARDS_PROGRESS_TIME} value and if the provided increment is greater than
	 * zero, in that case the function will update the object.
	 *
	 * The function expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTimestamp</b>: This parameter will hold the start time stamp.
	 *	<li><b>$theIncrement</b>: This parameter will hold the increment.
	 *	<li><b>$theCounter</b>: This parameter holds the counter offset.
	 *	<li><b>$theObject</b>: This parameter holds the object.
	 *	<li><b>$theTotal</b>: This parameter holds the total count, will be used for the
	 *		progress.
	 * </ul>
	 *
	 * This is the workflow:
	 *
	 * <ul>
	 *	<li>Call the function with the three first parameters, it is not important what
	 *		value they hold, the function will initialise them.
	 *	<li>As you perform operations, step increment the counter outside of this function.
	 *	<li>When you reach the point in which you intend to update the object, call
	 *		this function with the timestamp, increment and object, if you know the
	 *		total number of processed items pass it in the last parameter, this will ensure
	 *		that the progress will automatically be updated in the object.
	 *	<li>After the end of the loop call this function with all its parameters, excluding
	 *		the total, if not required for progress, and add to the list of parameters
	 *		<tt>TRUE</tt>, to flush residual counts and ensure that the counter is correct.
	 * </ul>
	 *
	 * If the counter was updated, the method will return <tt>TRUE</tt>; if you provide an
	 * object that is not derived from {@link SessionObject} the function will do nothing
	 * and return <tt>FALSE</tt>; if you provide a counter that is not supported, the
	 * function will do nothing and return <tt>FALSE</tt>; if the function did not update
	 * the counter, the method will return <tt>NULL</tt>.
	 *
	 * @param float				   &$theTimestamp		Start timestamp.
	 * @param int				   &$theIncrement		Number of processed items.
	 * @param string				$theCounter			Counter offset.
	 * @param Transaction			$theObject			Session or transaction object.
	 * @param int					$theTotal			Total number of items to process.
	 * @param boolean				$doUpdate			<tt>TRUE</tt> to force update..
	 *
	 * @return mixed				<tt>TRUE</tt>, <tt>FALSE</tt> or <tt>NULL</tt>.
	 */
	function UpdateProcessCounter( &$theTimestamp, &$theIncrement,
									$theCounter = NULL,
									$theObject = NULL,
									$theTotal = NULL,
									$doUpdate = FALSE )
	{
		//
		// Initialise counters.
		//
		if( $theObject === NULL )
		{
			$theTimestamp = microtime( TRUE );
			$theIncrement = 0;
			
			return NULL;															// ==>
		}
	
		//
		// Check object.
		//
		if( $theObject instanceof OntologyWrapper\SessionObject )
		{
			//
			// Update object.
			//
			if( $doUpdate
			 || ( ((microtime( TRUE ) - $theTimestamp) > kSTANDARDS_PROGRESS_TIME)
			   && ($theIncrement > 0) ) )
			{
				//
				// Parse counter.
				//
				switch( $theCounter )
				{
					case kTAG_COUNTER_PROCESSED:
						$theObject->processed( $theIncrement, $theTotal );
						break;
						
					case kTAG_COUNTER_VALIDATED:
						$theObject->validated( $theIncrement, $theTotal );
						break;
						
					case kTAG_COUNTER_REJECTED:
						$theObject->rejected( $theIncrement, $theTotal );
						break;
						
					case kTAG_COUNTER_SKIPPED:
						$theObject->skipped( $theIncrement, $theTotal );
						break;
								
					default:
						return FALSE;												// ==>
				}
				
				//
				// Reset timer and increment.
				//
				if( ! $doUpdate )
				{
					$theTimestamp = microtime( TRUE );
					$theIncrement = 0;
				}
			}
			
			return TRUE;															// ==>
		
		} // Provided the correct object.
		
		return FALSE;																// ==>
		
	} // UpdateProcessCounter.


?>
