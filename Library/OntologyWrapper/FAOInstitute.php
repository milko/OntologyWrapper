<?php

/**
 * FAOInstitute.php
 *
 * This file contains the definition of the {@link FAOInstitute} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Institution;

/*=======================================================================================
 *																						*
 *									FAOInstitute.php									*
 *																						*
 *======================================================================================*/

/**
 * FAO institute
 *
 * This class wraps the {@link Institution} class around a FAO/WIEWS institute, it
 * implements the necessary methods for managing a copy of the dataset in the current
 * database.
 *
 * The current object is populated as follows:
 *
 * <ul>
 *	<li><tt>INSTCODE</tt>: The institute code is copied to the {@link kTAG_IDENTIFIER}
 *		offset, the <tt>http://fao.org<tt> constant will be set in the
 *		{@link kTAG_AUTHORITY} offset and the <tt>wiews</tt> constant will be set in the
 *		{@link kTAG_COLLECTION} offset.
 *	<li><tt>ACRONYM</tt>: The acronym is set into the {@link kTAG_ENTITY_ACRONYM} offset.
 *	<li><tt>ECPACRONYM</tt>: The ecpgr acronym is set into the {@link kTAG_ENTITY_ACRONYM}
 *		offset.
 *	<li><tt>FULL_NAME</tt>: The full name is set into the {@link kTAG_NAME} offset.
 *	<li><tt>TYPE</tt>:The provided value is first parsed and normalised into a standard
 *		enumerated set, then it is placed in the {@link kTAG_ENTITY_TYPE} offset.
 *	<li><tt>PGR_ACTIVITY</tt>: This value, if positive, will set the <tt>100</tt>
 *		enumeration in the {@link kTAG_ENTITY_KIND}.
 *	<li><tt>MAINTCOLL</tt>: This value, if positive, will set the <tt>200</tt>
 *		enumeration in the {@link kTAG_ENTITY_KIND}.
 *	<li><tt>STREET_POB</tt>: This value is aggregated into the {@link kTAG_ENTITY_MAIL}
 *		offset omitting the type.
 *	<li><tt>CITY_STATE</tt>: This value is aggregated into the {@link kTAG_ENTITY_MAIL}
 *		offset omitting the type.
 *	<li><tt>ZIP_CODE</tt>: This value is aggregated into the {@link kTAG_ENTITY_MAIL}
 *		offset omitting the type.
 *	<li><tt>PHONE</tt>: This value is set into the {@link kTAG_ENTITY_PHONE} offset omitting
 *		the type.
 *	<li><tt>FAX</tt>: This value is set into the {@link kTAG_ENTITY_FAX} offset omitting the
 *		type.
 *	<li><tt>EMAIL</tt>: This value is set into the {@link kTAG_ENTITY_EMAIL} offset omitting
 *		the type.
 *	<li><tt>URL</tt>: This value is set into the {@link kTAG_ENTITY_LINK} offset omitting
 *		the type.
 *	<li><tt>LATITUDE</tt>: This value is ignored until it can be decoded.
 *	<li><tt>LONGITUDE</tt>: This value is ignored until it can be decoded.
 *	<li><tt>ALTITUDE</tt>: This value is ignored until it can be decoded.
 *	<li><tt>UPDATED_ON</tt>: This value is set into the {@link kTAG_VERSION} attribute.
 *	<li><tt>V_INSTCODE</tt>: This value is set into the {@link kTAG_ENTITY_VALID} offset,
 *		after being encoded for this database.
 * </ul>
 *
 * The {@link import()} method takes care of populating the object from a FAO/WIEWS record,
 * rather than populating the record with the member accessor methods, you should use this
 * one instead.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 08/03/2014
 */
class FAOInstitute extends Institution
{
	/**
	 * <b>FAO/WIEWS offsets</b>
	 *
	 * This data member holds the FAO/WIEW record offsets.
	 *
	 * @var array
	 */
	 static $sImportOffsets
	 	= array( 'INSTCODE', 'ACRONYM', 'ECPACRONYM', 'FULL_NAME', 'TYPE', 'PGR_ACTIVITY',
				 'MAINTCOLL', 'STREET_POB', 'CITY_STATE', 'ZIP_CODE', 'PHONE', 'FAX',
				 'EMAIL', 'URL', 'LATITUDE', 'LONGITUDE', 'ALTITUDE',
				 'UPDATED_ON', 'V_INSTCODE' );
		


/*=======================================================================================
 *																						*
 *								STATIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	InstituteCode																	*
	 *==================================================================================*/

	/**
	 * <h4>Translate from and to institute code</h4>
	 *
	 * This method will convert a FAO institute code into an entity native identifier and
	 * vice-versa.
	 *
	 * @param string				$theIdentifier		WIEWS institute code or native ID.
	 *
	 * @static
	 * @return string				Translated identifier.
	 */
	static function InstituteCode( $theIdentifier )
	{
		//
		// Init local storage.
		//
		$prefix =  ( static::kDEFAULT_DOMAIN.kTOKEN_DOMAIN_SEPARATOR	// Domain.
					.'http://fao.org'.kTOKEN_INDEX_SEPARATOR			// Authority.
					.'wiews'.kTOKEN_NAMESPACE_SEPARATOR );				// Collection.
		
		//
		// Handle entity identifier.
		//
		if( substr( $theIdentifier, strlen( $theIdentifier ) - 1, 1 ) == kTOKEN_END_TAG )
			return substr( $theIdentifier,
						   strlen( $prefix ) - 1,
						   strlen( $theIdentifier ) - strlen( $prefix ) - 1 );		// ==>
		
		//
		// Handle FAO institute code.
		//
		else
			return ( $prefix.$theIdentifier.kTOKEN_END_TAG );						// ==>
		
	} // InstituteCode.

	 
	/*===================================================================================
	 *	Resolve																			*
	 *==================================================================================*/

	/**
	 * <h4>Resolve FAO institutes</h4>
	 *
	 * This method will return the FAO institute object corresponding to the provided WIEWS
	 * code, if the institute was not found, the method will return <tt>NULL</tt>.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param string				$theIdentifier		WIEWS institute code.
	 *
	 * @static
	 * @return FAOInstitute			Resolved object or <tt>NULL</tt>.
	 */
	static function Resolve( Wrapper $theWrapper, $theIdentifier )
	{
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		//
		// Build identifier.
		//
		$identifier = static::InstituteCode( $theIdentifier );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_NID => $identifier );
		
		return $collection->matchOne( $criteria );									// ==>
		
	} // Resolve.
		


/*=======================================================================================
 *																						*
 *								STATIC MAINTENANCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Maintain																		*
	 *==================================================================================*/

	/**
	 * <h4>Update FAO institutes</h4>
	 *
	 * This method will load the current FAO institutes from WIEWS and update the provided
	 * database or container entries.
	 *
	 * The method will return an array with the following structure:
	 *
	 * <ul>
	 *	<li><tt>new</tt>: Number of inserted records.
	 *	<li><tt>updated</tt>: Number of updated records.
	 *	<li><tt>processed</tt>: Number of processed records.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 *
	 * @static
	 * @return array				Operation statistics.
	 */
	static function Maintain( Wrapper $theWrapper )
	{
		//
		// Init local storage.
		//
		$prefix = __class__;
		$zp = $fp = $name_zip = $ok = $eol = NULL;
		$stats = array( 'new' => 0, 'updated' => 0, 'processed' => 0 );
		
		//
		// TRY BLOCK.
		//
		try
		{
			//
			// Init local storage.
			//
			$name_zip = tempnam( "/tmp", "$prefix"."ZIP" );
			
			//
			// Load FAO institutes.
			//
			if( file_put_contents( $name_zip,
								   file_get_contents( kFAO_INSTITUTES_URL ) ) !== FALSE )
			{
				//
				// Open zip file.
				//
				$zp = zip_open( $name_zip );
				if( $zp )
				{
					//
					// Unzip data.
					//
					if( $data = zip_read( $zp ) )
					{
						//
						// Save unzipped data.
						//
						$name_txt = tempnam( "/tmp", "$prefix"."TXT" );
						if( $ok = file_put_contents(
									$name_txt,
									zip_entry_read(
										$data, zip_entry_filesize( $data ) ) ) )
						{
							//
							// Cleanup.
							//
							zip_entry_close( $data );
							zip_close( $zp );
							$zp = NULL;
							
							//
							// Handle Mac EOL.
							//
							$eol = ini_set( 'auto_detect_line_endings', 1 );
							
							//
							// Open file.
							//
							$fp = fopen( $name_txt, 'r' );
							if( $fp )
							{
								//
								// Cycle file.
								//
								while( ($data = fgetcsv( $fp, 4096, ',', '"' )) !== FALSE )
								{
									//
									// Increment processed.
									//
									$stats[ 'processed' ]++;
									
									//
									// Load new record.
									//
									$new = new static( $theWrapper );
									$new->import( $data );
									
									//
									// Try loading existing.
									//
									$old = static::Resolve( $theWrapper,
															$data[ 'INSTCODE' ] );
									
									//
									// Handle existing.
									//
									if( $old )
									{
										//
										// Check version.
										//
										if( $old->offsetGet( kTAG_VERSION )
											!= $new->offsetGet( kTAG_VERSION ) )
										{
											//
											// Delete old.
											//
											$old->Delete( $theWrapper );
											
											//
											// Insert new.
											//
											$new->commit( $theWrapper );
										
											//
											// Increment updated.
											//
											$stats[ 'updated' ]++;
										
										} // New version.
									
									} // Exists.
									
									//
									// Handle new.
									//
									else
									{
										//
										// Insert new.
										//
										$new->commit( $theWrapper );
									
										//
										// Increment updated.
										//
										$stats[ 'new' ]++;
									
									} // New record.
								
								} // Iterating file.
			
								return $stats;										// ==>
							
							} // Opened unzipped file.
		
							else
								throw new CException(
									"Unable to load FAO institutes: "
								   ."unable to open unzipped data",
									kERROR_STATE,
									kSTATUS_ERROR,
									array( 'File' => $name_txt ) );				// !@! ==>
						
						} // Saved unzipped data.
		
						else
							throw new CException(
								"Unable to load FAO institutes: "
							   ."unable to save unzipped data",
								kERROR_STATE,
								kSTATUS_ERROR,
								array( 'File' => $name_txt ) );					// !@! ==>
					
					} // Unzipped data.
		
					else
						throw new CException( "Unable to load FAO institutes: "
											 ."unable to unzip data",
											  kERROR_STATE,
											  kSTATUS_ERROR );					// !@! ==>
				
				} // Opened zip file.
		
				else
					throw new CException( "Unable to load FAO institutes: "
										 ."unable to open zip file",
										   kERROR_STATE,
										   kSTATUS_ERROR,
										   array( 'File' => $name_zip ) );		// !@! ==>
		
			} // Loaded institutes from FAO.
		
			else
				throw new CException( "Unable to load FAO institutes: "
									 ."unable to access URL",
									  kERROR_STATE,
									  kSTATUS_ERROR,
									  array( 'URL' => kFAO_INSTITUTES_URL ) );	// !@! ==>
		}
		
		//
		// FINAL BLOCK.
		//
		finally
		{
			if( $zp )
			{
				fclose( $zp );
				unlink( $name_zip );
			}
			
			if( $fp )
				fclose( $fp );
			
			if( $ok )
				unlink( $name_txt );
			
			if( $eol !== NULL )
				ini_set( 'auto_detect_line_endings', $eol );
		}
		
	} // Maintain.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * In this class we overload this method to set the default domain, authority and
	 * collection, if not yet set.
	 *
	 * Once we do this, we call the parent method.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses isInited()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Check domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
	
		//
		// Check suthority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
			$this->offsetSet( kTAG_AUTHORITY, 'http://fao.org' );
	
		//
		// Check collection.
		//
		if( ! $this->offsetExists( kTAG_COLLECTION ) )
			$this->offsetSet( kTAG_COLLECTION, 'wiews' );
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

		

/*=======================================================================================
 *																						*
 *								PROTECTED IMPORT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	import																			*
	 *==================================================================================*/

	/**
	 * <h4>Import institute</h4>
	 *
	 * This method will import an institute from the provided array which is expected to
	 * be an array containing a FAO/WIEWS record.
	 *
	 * The provided record is expected to be the array of values, the value offsets are set
	 * by this method.
	 *
	 * @param array					$theRecord			FAO/WIEWS record.
	 *
	 * @access protected
	 */
	protected function import( $theRecord )
	{
		//
		// Check record.
		//
		if( ! is_array( $theRecord ) )
			throw new Exception( "Invalid record type: expecting an array." );	// !@! ==>
		
		//
		// Normalise and clean.
		//
		foreach( $theRecord as $key => $value )
		{
			//
			// Clean.
			//
			$value = trim( $value );
			if( (! strlen( $value ))
			 || ($value == 'null') )
				$value = NULL;
			
			//
			// Set.
			//
			$theRecord[ $key ] = $value;
		
		} // Cleaning data.
		
		//
		// Add offsets.
		//
		$theRecord = array_combine( static::$sImportOffsets, $theRecord );
		
		//
		// Fix valid code.
		//
		if( array_key_exists( 'V_INSTCODE', $theRecord )
		 && strlen( $theRecord[ 'V_INSTCODE' ] )
		 && ($theRecord[ 'V_INSTCODE' ] == $theRecord[ 'INSTCODE' ]) )
			$theRecord[ 'V_INSTCODE' ] = NULL;
		
		//
		// Fix date.
		//
		if( array_key_exists( 'UPDATED_ON', $theRecord )
		 && strlen( $theRecord[ 'UPDATED_ON' ] ) )
			$theRecord[ 'UPDATED_ON' ]
				= substr( $theRecord[ 'UPDATED_ON' ], 6, 4 )
				 .substr( $theRecord[ 'UPDATED_ON' ], 3, 2 )
				 .substr( $theRecord[ 'UPDATED_ON' ], 0, 2 );
			
		//
		// Import record.
		//
		foreach( $theRecord as $key => $value )
			$this->importProperty( $key, $value );
		
		//
		// Set nationality.
		//
		$this->offsetSet( kTAG_ENTITY_COUNTRY, $this->offsetGet( kTAG_COLLECTION ) );
		
		//
		// Handle address.
		//
		$address = Array();
		if( array_key_exists( 'STREET_POB', $theRecord ) )
		{
			if( strlen( $tmp = trim( $theRecord[ 'STREET_POB' ] ) ) )
				$address[] = $theRecord[ 'STREET_POB' ];
		}
		$city = '';
		if( array_key_exists( 'ZIP_CODE', $theRecord ) )
		{
			if( strlen( $tmp = trim( $theRecord[ 'ZIP_CODE' ] ) ) )
				$city .= ($theRecord[ 'ZIP_CODE' ].' ');
		}
		if( array_key_exists( 'CITY_STATE', $theRecord ) )
		{
			if( strlen( $tmp = trim( $theRecord[ 'CITY_STATE' ] ) ) )
				$city .= ($theRecord[ 'CITY_STATE' ].' ');
		}
		if( strlen( $city ) )
			$address[] = $city;
		$address[] = $this->Collection();
		$this->EntityMail( NULL, implode( "\n", $address ) );

	} // import.

	 
	/*===================================================================================
	 *	importProperty																	*
	 *==================================================================================*/

	/**
	 * <h4>Import offset</h4>
	 *
	 * This method will import the provided offset, it expects the entry to be a FAO/WIEWS
	 * institute element.
	 *
	 * @param string				$theOffset			Data offset.
	 * @param string				$theValue			Data value.
	 *
	 * @access protected
	 */
	protected function importProperty( $theOffset, $theValue )
	{
		//
		// Skip empty values.
		//
		if( strlen( $theValue ) )
		{
			//
			// Parse by offset.
			//
			switch( $theOffset )
			{
				//
				// Institute code.
				//
				case 'INSTCODE':
					$this->offsetSet( kTAG_IDENTIFIER, substr( $theValue, 3 ) );
					$this->offsetSet( kTAG_COLLECTION, substr( $theValue, 0, 3 ) );
					break;
			
				//
				// Acronyms.
				//
				case 'ACRONYM':
				case 'ECPACRONYM':
					$this->EntityAcronym( $theValue, TRUE );
					break;
			
				//
				// Name.
				//
				case 'FULL_NAME':
					$this->offsetSet( kTAG_NAME, $theValue );
					break;
			
				//
				// Type.
				//
				case 'TYPE':
					if( count( $tmp = $this->importType( $theValue ) ) )
						$this->offsetSet( kTAG_ENTITY_TYPE, $tmp );
					break;
			
				//
				// PGR activity.
				//
				case 'PGR_ACTIVITY':
					if( $theValue == 'Y' )
						$this->EntityKind( '100', TRUE );
					break;
			
				//
				// Maintains collection.
				//
				case 'MAINTCOLL':
					if( $theValue == 'Y' )
						$this->EntityKind( '200', TRUE );
					break;
			
				//
				// Phone.
				//
				case 'PHONE':
					$this->EntityPhone( NULL, $theValue );
					break;
			
				//
				// Fax.
				//
				case 'FAX':
					$this->EntityFax( NULL, $theValue );
					break;
			
				//
				// Email.
				//
				case 'EMAIL':
					$this->EntityEmail( NULL, $theValue );
					break;
			
				//
				// URL.
				//
				case 'URL':
					$this->EntityLink( NULL, $theValue );
					break;
			
				//
				// Version.
				//
				case 'UPDATED_ON':
					$this->offsetSet( kTAG_VERSION, $theValue );
					break;
			
				//
				// Valid.
				//
				case 'V_INSTCODE':
					$this->offsetSet( kTAG_ENTITY_VALID,
									  static::InstituteCode( $theValue ) );
					break;
			
			} // Parsed offset.
		
		} // Not empty.
	
	} // importProperty.

	 
	/*===================================================================================
	 *	importType																		*
	 *==================================================================================*/

	/**
	 * <h4>Import type</h4>
	 *
	 * This method will convert the FAO/WIEWS type into the local entity type enumeration.
	 *
	 * The method expects the FAO/WIEWS value and will return the local set; it also expects
	 * the value not to be empty.
	 *
	 * @param string				$theValue			Data value.
	 *
	 * @access protected
	 * @return array				Converted value.
	 */
	protected function importType( $theValue )
	{
		//
		// Init local storage.
		//
		$type = Array();
		
		//
		// Parse value.
		//
		$list = explode( '/', $theValue );
		foreach( $list as $element )
		{
			//
			// Parse type.
			//
			switch( strtoupper( trim( $element ) ) )
			{
				case 'AUT':
					$type[ '260' ] = '260';
					break;

				case 'EEC':
					$type[ '252' ] = '252';
					break;

				case 'PRI':
				case 'PRIV':
					$type[ '280' ] = '280';
					break;

				case 'REG':
					$type[ '240' ] = '240';
					break;

				case 'IND':
					$type[ '100' ] = '100';
					break;

				case 'INT':
					$type[ '250' ] = '250';
					break;

				case 'NGO':
					$type[ '220' ] = '220';
					break;

				case 'PREFECTURAL':
					$type[ '230' ] = '230';
					break;

				case 'UN':
					$type[ '255' ] = '255';
					break;

				case 'WB':
					$type[ '254' ] = '254';
					break;

				case 'WWF':
					$type[ '253' ] = '253';
					break;

				case 'CGIAR':
					$type[ '251' ] = '251';
					break;

				case 'GOV':
				case 'FRA':
				case 'FED':
				case 'BEN':
				case 'CIV':
				case 'GAB':
				case 'MDG':
				case 'MUS':
				case 'PYF':
				case 'REU':
				case 'VTU':
				case 'SWZ':
				case 'USA':
					$type[ '210' ] = '210';
					break;
			
			} // Parsed type.
		
		} // Iterating types.
		
		return array_values( $type );												// ==>
	
	} // importType.

	 

} // class FAOInstitute.


?>
