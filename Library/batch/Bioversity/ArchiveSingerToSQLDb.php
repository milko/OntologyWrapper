<?php

/**
 * SQL SINGER archive procedure.
 *
 * This file contains routines to load SINGER from an SQL database and archive it as XML
 * in the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 03/09/2014
 */

/*=======================================================================================
 *																						*
 *								ArchiveSingerToSQLDb.php								*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// Local includes.
//
require_once( 'local.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Predicate definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Predicates.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

//
// Functions.
//
require_once( kPATH_LIBRARY_ROOT."/Functions.php" );

/**
 * ADODB library.
 *
 * This include file contains the ADODB library definitions.
 */
require_once( "/Library/WebServer/Library/adodb/adodb.inc.php" );

/**
 * ADODB iterators.
 *
 * This include file contains the ADODB library iterators.
 */
require_once( "/Library/WebServer/Library/adodb/adodb-iterator.inc.php" );

/**
 * ADODB exceptions.
 *
 * This include file contains the ADODB library exceptions.
 */
require_once( "/Library/WebServer/Library/adodb/adodb-exceptions.inc.php" );

/**
 * Settings.
 */
define( 'kDO_CLIMATE', TRUE );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 5 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// MySQLi://user:pass@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist
				."<Output SQL database DSN> "
	// singer
				."<Output SQL database table> "
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// neo4j://localhost:7474 or ""
				."[graph DSN]"
	// "'last identifier'"
				."[last ID (including quotes if string)]\n" );						// ==>

//
// Init local storage.
//
$start = 0;
$limit = 1000;
$page = 50;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Accession';

//
// Load arguments.
//
$db_in = $argv[ 1 ];
$db_out = $argv[ 2 ];
$table = $argv[ 3 ];
$mongo = $argv[ 4 ];
$graph = ( ($argc > 5) && strlen( $argv[ 5 ] ) ) ? $argv[ 5 ] : NULL;
$last = ( $argc > 6 ) ? $argv[ 6 ] : NULL;

//
// Inform.
//
echo( "\n==> Loading SINGER into $table.\n" );

//
// Try.
//
try
{
	//
	// Inform.
	//
	echo( "  • Creating wrapper.\n" );
	
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Inform.
	//
	echo( "  • Creating database.\n" );
	
	//
	// Instantiate database.
	//
	$mongo
		= new OntologyWrapper\MongoDatabase(
			"$mongo?connect=1" );
	
	//
	// Set metadata.
	//
	echo( "  • Setting metadata.\n" );
	$wrapper->Metadata( $mongo );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$wrapper->Units( $mongo );
	
	//
	// Set entities.
	//
	echo( "  • Setting entities.\n" );
	$wrapper->Entities( $mongo );
	
	//
	// Check graph database.
	//
	if( $graph !== NULL )
	{
		//
		// Set graph database.
		//
		echo( "  • Setting graph.\n" );
		$wrapper->Graph(
			new OntologyWrapper\Neo4jGraph(
				$graph ) );
	
	} // Use graph database.
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper ) );
	
	//
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db_in\n" );
	$dc_in = NewADOConnection( $db_in );
	$dc_in->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_in->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Connect to output database.
	//
	echo( "  • Connecting to output SQL\n" );
	echo( "    - $db_out\n" );
	$dc_out = NewADOConnection( $db_out );
	$dc_out->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_out->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Clearing output.
	//
	if( $last === NULL )
	{
		$rs = $dc_out->Execute( "TRUNCATE TABLE `$table`" );
		$rs->Close();
	}
	
	//
	// Import.
	//
	$pages = $page;
	echo( "  • Exporting\n" );
	$query = "SELECT * FROM `singer_acc` ";
	if( $last !== NULL )
		$query .= "WHERE( `ID` > $last ) ";
	$query .= "ORDER BY `ID` LIMIT $start,$limit";
	$rs = $dc_in->execute( $query );
	while( $rs->RecordCount() )
	{
		//
		// Iterate page.
		//
		foreach( $rs as $record )
		{
			//
			// Scan record.
			//
			$data = Array();
			foreach( $record as $key => $value )
			{
				//
				// Normalise value.
				//
				if( strlen( trim( $value ) ) )
					$data[ $key ] = trim( $value );
			
			} // Scanning record.
			
			//
			// Skip empty records.
			//
			if( ! count( $data ) )
				continue;													// =>
		
			//
			// Instantiate object.
			//
			$object = new $class( $wrapper );
			
			//
			// Load unit.
			//
			loadUnit( $object, $data, $wrapper, $dc_in );
			
			//
			// Load climate.
			//
			if( kDO_CLIMATE )
				$object->setClimateData();
			
			//
			// Validate object.
			//
			$object->validate();
			
			//
			// Save record.
			//
			$xml = $object->export( 'xml' );
			$insert = "INSERT INTO `$table`( ";
			$insert .= ("`id`, `class`, `xml` ) VALUES( "
					   .$record[ 'ID' ].', '
					   .'0x'.bin2hex( get_class( $object ) ).', '
					   .'0x'.bin2hex( $xml->asXML() ).' )');
			$dc_out->Execute( $insert );
			
		} // Iterating page.
		
		//
		// Close recordset.
		//
		$rs->Close();
		$rs = NULL;
			
		//
		// Inform.
		//
		if( ! $pages-- )
		{
			echo( $start + $limit );
			$pages = $page;
		}
		else
			echo( '.' );
		
		//
		// Read next.
		//
		$start += $limit;
		$query = "SELECT * FROM `singer_acc` ";
		if( $last !== NULL )
			$query .= "WHERE( `ID` > $last ) ";
		$query .= "ORDER BY `ID` LIMIT $start,$limit";
		$rs = $dc_in->execute( $query );
	
	} // Records left.

	echo( "\nDone!\n" );

} // TRY BLOCK.

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );

} // CATCH BLOCK.

//
// FINAL BLOCK.
//
finally
{
	if( $rs instanceof ADORecordSet )
		$rs->Close();
	if( $dc_in instanceof ADOConnection )
		$dc_in->Close();
	if( $dc_out instanceof ADOConnection )
		$dc_out->Close();

} // FINALLY BLOCK.


/*=======================================================================================
 *	FUNCTIONS																			*
 *======================================================================================*/

	/**
	 * Load unit data.
	 *
	 * This function will load the unit data provided in the <b>$theData</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the target species data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theData			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadUnit( $theObject, $theData, $theWrapper, $theDatabase )
	{
		/***********************************************************************
		 * Set unit identification properties.
		 **********************************************************************/
		
		//
		// Set authority.
		//
		$theObject->offsetSet( kTAG_AUTHORITY, $theData[ 'HoldingInstituteFAOCode' ] );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( kTAG_COLLECTION, $theData[ 'HoldingCollectionCode' ] );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ 'AccessionNumber' ] );
		
		//
		// Set version.
		//
		if( array_key_exists( 'Stamp', $theData ) )
			$theObject->offsetSet( kTAG_VERSION,
								   substr( $theData[ 'Stamp' ], 0, 4 )
								  .substr( $theData[ 'Stamp' ], 5, 2 )
								  .substr( $theData[ 'Stamp' ], 8, 2 ) );
				
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet(
			':inventory:dataset',
			'System-wide Information Network for Genetic Resources' );
		
		//
		// Set inventory institute.
		//
		$theObject->offsetSet(
			':inventory:institute',
			kDOMAIN_ORGANISATION
		   .'://http://fao.org/wiews:'
		   .$theData[ 'HoldingInstituteFAOCode' ]
		   .kTOKEN_END_TAG );
		
		//
		// Set Genesys ID.
		//
		if( array_key_exists( 'AlisID', $theData ) )
			$theObject->offsetSet( ':inventory:GENESYS',
								   (string) $theData[ 'AlisID' ] );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set germplasm identifier.
		//
		$theObject->offsetSet(
			':germplasm:identifier',
			$theData[ 'HoldingInstituteFAOCode' ].kTOKEN_INDEX_SEPARATOR
		   .$theData[ 'HoldingCollectionCode' ].kTOKEN_NAMESPACE_SEPARATOR
		   .$theData[ 'AccessionNumber' ] );
		
		//
		// Set holding institute code.
		//
		$theObject->offsetSet( 'mcpd:INSTCODE',
								$theData[ 'HoldingInstituteFAOCode' ] );
		
		//
		// Set accession number.
		//
		$theObject->offsetSet( 'mcpd:ACCENUMB',
								$theData[ 'AccessionNumber' ] );
		
		//
		// Set accession name.
		//
		if( array_key_exists( 'AccessionNames', $theData ) )
		{
			$tmp = Array();
			foreach( explode( ',', $theData[ 'AccessionNames' ] ) as $item )
			{
				$item = trim( $item );
				if( strlen( $item ) )
				{
					if( ! in_array( $item, $tmp ) )
						$tmp[] = $item;
				}
			}
			if( count( $tmp ) )
				$theObject->offsetSet( 'mcpd:ACCENAME', $tmp );
		}
		
		//
		// Set taxon rank.
		//
		if( array_key_exists( 'TaxonRank', $theData ) )
		{
			switch( $theData[ 'TaxonRank' ] )
			{
				case 'convarietas':
					$tmp = ':taxon:convarietas';
					break;
				
				case 'forma':
					$tmp = ':taxon:forma';
					break;
				
				case 'genus':
					$tmp = ':taxon:genus';
					break;
				
				case 'genus':
					$tmp = ':taxon:genus';
					break;
				
				case 'group':
					$tmp = ':taxon:group-rank';
					break;
				
				case 'species':
					$tmp = ':taxon:species';
					break;
				
				case 'subspecies':
					$tmp = ':taxon:subspecies';
					break;
				
				case 'varietas':
					$tmp = ':taxon:varietas';
					break;
				
				default:
					$tmp = NULL;
					break;
			}
			
			if( $tmp !== NULL )
				$theObject->offsetSet( ':taxon:rank', $tmp );
		}
		
		//
		// Set taxon genus.
		//
		if( array_key_exists( 'Genus', $theData ) )
			$theObject->offsetSet( ':taxon:genus',
								   $theData[ 'Genus' ] );
		
		//
		// Set taxon species.
		//
		if( array_key_exists( 'Species', $theData ) )
			$theObject->offsetSet( ':taxon:species',
								   $theData[ 'Species' ] );
		
		//
		// Set taxon species authority.
		//
		if( array_key_exists( 'SpeciesAuthority', $theData ) )
			$theObject->offsetSet( ':taxon:species:author',
								   $theData[ 'SpeciesAuthority' ] );
		
		//
		// Set taxon infraspecific epithet.
		//
		if( array_key_exists( 'InfraspecificEpithet', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies',
								   $theData[ 'InfraspecificEpithet' ] );
		
		//
		// Set taxon infraspecific authority.
		//
		if( array_key_exists( 'InfraspecificAuthority', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies:author',
								   $theData[ 'InfraspecificAuthority' ] );
		
		//
		// Set taxon epithet.
		//
		if( array_key_exists( 'ScientificName', $theData ) )
			$theObject->offsetSet( ':taxon:epithet',
								   $theData[ 'ScientificName' ] );
		
		//
		// Set taxon reference.
		//
		if( array_key_exists( 'TaxonReference', $theData ) )
		{
			//
			// Set taxon reference.
			//
			$theObject->offsetSet(
				':taxon:reference',
				array( 'http://www.ars-grin.gov/cgi-bin/npgs/html/index.pl' ) );
			
			//
			// Set taxon URL.
			//
			$theObject->offsetSet( ':taxon:url',
								   $theData[ 'TaxonReference' ] );
		}
		
		//
		// Set vernacular names.
		//
		if( array_key_exists( 'CropNames', $theData ) )
		{
			$tmp = Array();
			foreach( explode( ';', $theData[ 'CropNames' ] ) as $item )
			{
				$item = trim( $item );
				if( strlen( $item ) )
				{
					if( ! in_array( $item, $tmp ) )
						$tmp[] = $item;
				}
			}
			if( count( $tmp ) )
				$theObject->offsetSet(
					':taxon:names',
					array(
						array( kTAG_TEXT => $tmp ) ) );
		}
		
		//
		// Set crop.
		//
		if( array_key_exists( 'CropCode', $theData ) )
			$theObject->offsetSet( ':taxon:crop',
									':taxon:crop:'.$theData[ 'CropCode' ] );
		
		//
		// Set annex-1.
		//
		if( array_key_exists( 'Annex1', $theData ) )
		{
			if( $theData[ 'Annex1' ] != '900' )
				$theObject->offsetSet( ':taxon:annex-1',
									   ':taxon:annex-1:'.$theData[ 'Annex1' ] );
		}
		
		//
		// Set remarks.
		//
		if( array_key_exists( 'AccessionRemarks', $theData ) )
			$theObject->offsetSet( 'mcpd:REMARKS',
								   $theData[ 'AccessionRemarks' ] );
		
		//
		// Load collecting event.
		//
		$sub = Array();
		loadCollecting(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':domain:accession:collecting', $sub );

		//
		// Load breeding event.
		//
		$sub = Array();
		loadBreeding(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':domain:accession:breeding', $sub );
		
		//
		// Load source.
		//
		$sub = Array();
		loadSource(		$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':domain:accession:source', $sub );
		
		//
		// Load management.
		//
		$sub = Array();
		loadManagement(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':domain:accession:management', $sub );
		
		//
		// Load status.
		//
		$sub = Array();
		loadStatus(	$sub,
					$theData,
					$theWrapper,
					$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':domain:accession:status', $sub );
		
		//
		// Load transfers.
		//
		$sub = Array();
		loadTransfers(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':germplasm:mt', $sub );

	} // loadUnit.
	

	/**
	 * Load collecting event.
	 *
	 * This function will load the collecting data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCollecting( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set collecting date.
		//
		if( array_key_exists( 'CollectingDate', $theUnit ) )
			$theContainer[ getTag( 'mcpd:COLLDATE' ) ]
				= $theUnit[ 'CollectingDate' ];
								
		//
		// Set collecting number.
		//
		if( array_key_exists( 'CollectingNumber', $theUnit ) )
			$theContainer[ getTag( 'mcpd:COLLNUMB' ) ]
				= $theUnit[ 'CollectingNumber' ];
								
		//
		// Set country.
		//
		if( array_key_exists( 'CollectingSiteCountryCode', $theUnit ) )
		{
			if( $tmp
					= OntologyWrapper\Term::ResolveCountryCode(
							$theWrapper, $theUnit[ 'CollectingSiteCountryCode' ] ) )
				$theContainer[ getTag( ':location:country' ) ] = $tmp;
		}
								
		//
		// Set admins.
		//
		if( array_key_exists( 'CollectingSiteAdmin1', $theUnit ) )
			$theContainer[ getTag( ':location:admin-1' ) ]
				= $theUnit[ 'CollectingSiteAdmin1' ];
		if( array_key_exists( 'CollectingSiteAdmin2', $theUnit ) )
			$theContainer[ getTag( ':location:admin-2' ) ]
				= $theUnit[ 'CollectingSiteAdmin2' ];
		if( array_key_exists( 'CollectingSiteAdmin3', $theUnit ) )
			$theContainer[ getTag( ':location:admin-3' ) ]
				= $theUnit[ 'CollectingSiteAdmin3' ];
								
		//
		// Set locality.
		//
		if( array_key_exists( 'CollectingSiteLocation', $theUnit ) )
			$theContainer[ getTag( ':location:locality' ) ]
				= $theUnit[ 'CollectingSiteLocation' ];
								
		//
		// Set elevation.
		//
		if( array_key_exists( 'CollectingSiteElevation', $theUnit ) )
			$theContainer[ getTag( ':location:site:elevation' ) ]
				= $theUnit[ 'CollectingSiteElevation' ];
								
		//
		// Set latitude.
		//
		if( array_key_exists( 'CollectingSiteLatitude', $theUnit ) )
			$theContainer[ getTag( ':location:site:latitude' ) ]
				= $theUnit[ 'CollectingSiteLatitude' ];
								
		//
		// Set latitude provided.
		//
		if( array_key_exists( 'CollectingSiteLatitudeProvided', $theUnit ) )
			$theContainer[ getTag( ':location:site:latitude:provided' ) ]
				= $theUnit[ 'CollectingSiteLatitudeProvided' ];
								
		//
		// Set latitude precision.
		//
		if( array_key_exists( 'CollectingSiteLatitudePrecision', $theUnit ) )
			$theContainer[ getTag( ':location:site:latitude:error' ) ]
				= $theUnit[ 'CollectingSiteLatitudePrecision' ];
								
		//
		// Set longitude.
		//
		if( array_key_exists( 'CollectingSiteLongitude', $theUnit ) )
			$theContainer[ getTag( ':location:site:longitude' ) ]
				= $theUnit[ 'CollectingSiteLongitude' ];
								
		//
		// Set longitude provided.
		//
		if( array_key_exists( 'CollectingSiteLongitudeProvided', $theUnit ) )
			$theContainer[ getTag( ':location:site:longitude:provided' ) ]
				= $theUnit[ 'CollectingSiteLongitudeProvided' ];
								
		//
		// Set longitude precision.
		//
		if( array_key_exists( 'CollectingSiteLongitudePrecision', $theUnit ) )
			$theContainer[ getTag( ':location:site:longitude:error' ) ]
				= $theUnit[ 'CollectingSiteLongitudePrecision' ];
								
		//
		// Set georeference date.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceDate', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-date' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceDate' ];
								
		//
		// Set georeference contact.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceContact', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-contact' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceContact' ];
								
		//
		// Set georeference source.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceSource', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-source' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceSource' ];
								
		//
		// Set georeference latitude.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceLatitude', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-latitude' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceLatitude' ];
								
		//
		// Set georeference longitude.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceLongitude', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-longitude' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceLongitude' ];
								
		//
		// Set georeference error.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceError', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-error' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceError' ];
								
		//
		// Set georeference old.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceOld', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-old' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceOld' ];
		
		//
		// Set collecting site error.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceError', $theUnit ) )
			$theContainer[ getTag( ':location:site:error' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceError' ];
		elseif( array_key_exists( 'CollectingSiteLatitudePrecision', $theUnit ) )
			$theContainer[ getTag( ':location:site:error' ) ]
				= $theUnit[ 'CollectingSiteLatitudePrecision' ];
								
		//
		// Set georeference notes.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceNotes', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-notes' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceNotes' ];
		
		//
		// Load collecting entities.
		//
		$sub = Array();
		loadCollectors(	$sub,
						$theUnit,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theContainer[ getTag( ':collecting:entities' ) ]
				= $sub;

	} // loadCollecting.
	

	/**
	 * Load collecting entities.
	 *
	 * This function will load the collector's data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCollectors( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		
		//
		// Select collectors.
		//
		$query = "SELECT * FROM `singer_collectors` "
				."WHERE( `AccessionID` = "
				.'0x'.bin2hex( $theUnit[ 'AccessionID' ] )." ) "
				."LIMIT $start,$limit";
		$rs = $theDatabase->execute( $query );
		while( $rs->RecordCount() )
		{
			//
			// Iterate page.
			//
			foreach( $rs as $record )
			{
				//
				// Scan record.
				//
				$data = Array();
				foreach( $record as $key => $value )
				{
					//
					// Normalise value.
					//
					if( strlen( trim( $value ) ) )
						$data[ $key ] = trim( $value );
			
				} // Scanning record.
			
				//
				// Skip empty records.
				//
				if( ! count( $data ) )
					continue;													// =>
				
				//
				// Init sub.
				//
				$sub = Array();
		
				//
				// Set COLLCODE.
				//
				if( array_key_exists( 'CooperatorInstituteFAOCode', $data ) )
					$sub[ getTag( 'mcpd:COLLCODE' ) ]
						= $data[ 'CooperatorInstituteFAOCode' ];
		
				//
				// Set COLLDESCR.
				//
				if( array_key_exists( 'CooperatorInstituteName', $data ) )
				{
					if( ! array_key_exists( 'CooperatorInstituteFAOCode', $data ) )
						$sub[ getTag( 'mcpd:COLLDESCR' ) ]
							= $data[ 'CooperatorInstituteName' ];
				}
		
				//
				// Set :inventory:INSTCODE.
				//
				if( array_key_exists( 'CooperatorInstituteFAOCode', $data ) )
					$sub[ getTag( ':inventory:institute' ) ]
						= kDOMAIN_ORGANISATION
						 .'://http://fao.org/wiews:'
						 .$data[ 'CooperatorInstituteFAOCode' ]
						 .kTOKEN_END_TAG;
		
				//
				// Set :entity:identifier.
				//
				if( array_key_exists( 'CooperatorCode', $data ) )
					$sub[ kTAG_ENTITY_IDENT ]
						= $data[ 'CooperatorCode' ];
		
				//
				// Set :name.
				//
				if( array_key_exists( 'CooperatorName', $data ) )
					$sub[ kTAG_NAME ]
						= $data[ 'CooperatorName' ];
				elseif( array_key_exists( 'CooperatorLocalCode', $data ) )
					$sub[ kTAG_NAME ]
						= $data[ 'CooperatorLocalCode' ];
		
				//
				// Set :type:entity.
				//
				if( array_key_exists( 'CooperatorType', $data )
				 || array_key_exists( 'CooperatorEntityType', $data ) )
				{
					$tmp = Array();
					if( array_key_exists( 'CooperatorType', $data )
					 && ($data[ 'CooperatorType' ] != '999') )
						$tmp[] = ':type:entity:'.$data[ 'CooperatorType' ];
					if( array_key_exists( 'CooperatorEntityType', $data )
					 && ($data[ 'CooperatorEntityType' ] != '999')
					 && (! in_array( ':type:entity:'.$data[ 'CooperatorEntityType' ],
					 				 $tmp )) )
						$tmp[] = ':type:entity:'.$data[ 'CooperatorEntityType' ];
					if( count( $tmp ) )
						$sub[ kTAG_ENTITY_TYPE ] = $tmp;
				}
		
				//
				// Set :entity:mail.
				//
				if( array_key_exists( 'CooperatorAddress', $data ) )
					$sub[ kTAG_ENTITY_MAIL ][]
						= array( kTAG_TEXT => $data[ 'CooperatorAddress' ] );
		
				//
				// Set :entity:email.
				//
				if( array_key_exists( 'CooperatorEmail', $data ) )
					$sub[ kTAG_ENTITY_EMAIL ][]
						= array( kTAG_TEXT => $data[ 'CooperatorEmail' ] );
								
				//
				// Set country.
				//
				if( array_key_exists( 'CooperatorCountryCode', $theUnit ) )
				{
					if( $tmp
							= OntologyWrapper\Term::ResolveCountryCode(
									$theWrapper, $data[ 'CooperatorCountryCode' ] ) )
						$sub[ kTAG_ENTITY_NATIONALITY ] = $tmp;
				}
		
				//
				// Load record.
				//
				if( count( $sub ) )
					$theContainer[] = $sub;
			
			} // Iterating page.
		
			//
			// Close recordset.
			//
			$rs->Close();
			$rs = NULL;
		
			//
			// Read next.
			//
			$start += $limit;
			$rs = $theDatabase->execute( "SELECT * FROM `singer_collectors` "
										."WHERE( `AccessionID` = "
										.'0x'.bin2hex( $theUnit[ 'AccessionID' ] )." ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadCollectors.
	

	/**
	 * Load breeding event.
	 *
	 * This function will load the breeding data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadBreeding( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set ancestors.
		//
		if( array_key_exists( 'AncestralData', $theUnit ) )
			$theContainer[ getTag( 'mcpd:ANCEST' ) ]
				= $theUnit[ 'AncestralData' ];
		
		//
		// Set country.
		//
		if( array_key_exists( 'BreedingSiteCountryCode', $theUnit ) )
		{
			if( $tmp
					= OntologyWrapper\Term::ResolveCountryCode(
							$theWrapper, $theUnit[ 'BreedingSiteCountryCode' ] ) )
				$theContainer[ getTag( ':location:country' ) ] = $tmp;
		}

	} // loadBreeding.
	

	/**
	 * Load management information.
	 *
	 * This function will load the management data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadManagement( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set acquisition date.
		//
		if( array_key_exists( 'AcquisitionDate', $theUnit ) )
			$theContainer[ getTag( 'mcpd:ACQDATE' ) ]
				= $theUnit[ 'AcquisitionDate' ];
								
		//
		// Set last regeneration.
		//
		if( array_key_exists( 'LastRegenerationDate', $theUnit ) )
			$theContainer[ getTag( ':germplasm:last-gen' ) ]
				= $theUnit[ 'LastRegenerationDate' ];
								
		//
		// Set storage.
		//
		if( array_key_exists( 'GermplasmStorageTypes', $theUnit ) )
		{
			$tmp = Array();
			foreach( explode( ',', $theUnit[ 'GermplasmStorageTypes' ] ) as $item )
			{
				$item = trim( $item );
				if( strlen( $item ) )
				{
					if( $item != '99' )
						$tmp[] = "mcpd:STORAGE:$item";
				}
			}
			if( count( $tmp ) )
				$theContainer[ getTag( 'mcpd:STORAGE' ) ]
					= $tmp;
		}
								
		//
		// Set storage notes.
		//
		if( array_key_exists( 'GermplasmStorageTypeNotes', $theUnit ) )
			$theContainer[ getTag( 'mcpd:STORAGE:NOTES' ) ]
				= $theUnit[ 'GermplasmStorageTypeNotes' ];
		
		//
		// Set other accession identifiers.
		//
		if( array_key_exists( 'OtherAccessionIdentification', $theUnit ) )
			$theContainer[ getTag( 'mcpd:OTHERNUMB' ) ]
				= $theUnit[ 'OtherAccessionIdentification' ];
		
		//
		// Set sample AVAILABLE.
		//
		if( array_key_exists( 'Available', $theUnit ) )
			$theContainer[ getTag( 'mcpd:AVAILABLE' ) ]
				= ( $theUnit[ 'Available' ] )
				? 'mcpd:AVAILABLE:1'
				: 'mcpd:AVAILABLE:0';
								
		//
		// Set safety duplicates.
		//
		if( array_key_exists( 'SafetyDuplicateInstitutesFAOCodes', $theUnit )
		 || array_key_exists( 'SafetyDuplicateInstitutesNames', $theUnit ) )
		{
			$list = Array();
			if( array_key_exists( 'SafetyDuplicateInstitutesFAOCodes', $theUnit ) )
			{
				foreach( explode( ',', $theUnit[ 'SafetyDuplicateInstitutesFAOCodes' ] )
							as $item )
				{
					$tmp = Array();
					$item = trim( $item );
					if( strlen( $item ) )
					{
						$tmp[ getTag( ':struct-label' ) ] = $item;
						$tmp[ getTag( 'mcpd:DUPLSITE' ) ] = $item;
						$tmp[ getTag( ':inventory:DUPLSITE' ) ]
							= kDOMAIN_ORGANISATION
							 .'://http://fao.org/wiews:'
							 .$item
							 .kTOKEN_END_TAG;
						$list[] = $tmp;
					}
				}
			}
			else
			{
				$tmp = Array();
				$item = trim( $theUnit[ 'SafetyDuplicateInstitutesNames' ] );
				if( strlen( $item ) )
				{
					$tmp[ getTag( ':struct-label' ) ] = $item;
					$tmp[ getTag( 'mcpd:DUPLDESCR' ) ] = $item;
					$list[] = $tmp;
				}
			}
			if( count( $list ) )
				$theContainer[ getTag( ':germplasm:safety' ) ]
					= $list;
		}

	} // loadManagement.
	

	/**
	 * Load source information.
	 *
	 * This function will load the source data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSource( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set source code.
		//
		if( array_key_exists( 'AcquisitionSourceCode', $theUnit )
		 && ($theUnit[ 'AcquisitionSourceCode' ] != '99') )
			$theContainer[ getTag( 'mcpd:COLLSRC' ) ]
				= 'mcpd:COLLSRC:'.$theUnit[ 'AcquisitionSourceCode' ];
								
		//
		// Set status code.
		//
		if( array_key_exists( 'BiologicalStatusCode', $theUnit )
		 && ($theUnit[ 'BiologicalStatusCode' ] != '999') )
			$theContainer[ getTag( 'mcpd:SAMPSTAT' ) ]
				= 'mcpd:SAMPSTAT:'.$theUnit[ 'BiologicalStatusCode' ];
		
		//
		// Set donor.
		//
		if( array_key_exists( 'DonorCode', $theUnit ) )
		{
			//
			// Select donors.
			//
			$query = "SELECT * FROM `singer_donors` "
					."WHERE( `CooperatorCode` = "
					.'0x'.bin2hex( $theUnit[ 'DonorCode' ] )." )";
			$tmp = $theDatabase->GetRow( $query );
			
			//
			// Scan record.
			//
			$data = Array();
			foreach( $tmp as $key => $value )
			{
				//
				// Normalise value.
				//
				if( strlen( trim( $value ) ) )
					$data[ $key ] = trim( $value );
		
			} // Scanning record.
		
			//
			// Skip empty records.
			//
			if( count( $data ) )
			{
				//
				// Set DONORCODE.
				//
				if( array_key_exists( 'CooperatorInstituteFAOCode', $data ) )
				{
					//
					// :inventory:INSTCODE
					//
					$theContainer[ getTag( ':inventory:institute' ) ]
						= kDOMAIN_ORGANISATION
						 .'://http://fao.org/wiews:'
						 .$data[ 'CooperatorInstituteFAOCode' ]
						 .kTOKEN_END_TAG;
					 
					//
					// mcpd:DONORCODE
					//
					$theContainer[ getTag( 'mcpd:DONORCODE' ) ]
						= $data[ 'CooperatorInstituteFAOCode' ];
				}
				else
				{
					//
					// mcpd:DONORDESCR
					//
					if( array_key_exists( 'CooperatorInstituteName', $theUnit ) )
						$theContainer[ getTag( 'mcpd:DONORDESCR' ) ]
							= $theUnit[ 'CooperatorInstituteName' ];
	
					//
					// Set :name.
					//
					if( array_key_exists( 'CooperatorName', $data ) )
						$theContainer[ getTag( ':name' ) ]
							= $data[ 'CooperatorName' ];
	
					//
					// Set :type:entity.
					//
					if( array_key_exists( 'CooperatorType', $data )
					 || array_key_exists( 'CooperatorEntityType', $data ) )
					{
						$tmp = Array();
						if( array_key_exists( 'CooperatorType', $data )
						 && ($data[ 'CooperatorType' ] != '999') )
							$tmp[] = ':type:entity:'.$data[ 'CooperatorType' ];
						if( array_key_exists( 'CooperatorEntityType', $data )
						 && ($data[ 'CooperatorEntityType' ] != '999')
						 && (! in_array( ':type:entity:'.$data[ 'CooperatorEntityType' ],
										 $tmp )) )
							$tmp[] = ':type:entity:'.$data[ 'CooperatorEntityType' ];
						if( count( $tmp ) )
							$theContainer[ getTag( ':type:entity' ) ] = $tmp;
					}
	
					//
					// Set :entity:mail.
					//
					if( array_key_exists( 'CooperatorAddress', $data ) )
						$theContainer[ getTag( ':entity:mail' ) ][]
							= array( kTAG_TEXT => $data[ 'CooperatorAddress' ] );
	
					//
					// Set :entity:email.
					//
					if( array_key_exists( 'CooperatorEmail', $data ) )
						$theContainer[ getTag( ':entity:email' ) ][]
							= array( kTAG_TEXT => $data[ 'CooperatorEmail' ] );
							
					//
					// Set country.
					//
					if( array_key_exists( 'CooperatorCountryCode', $theUnit ) )
					{
						if( $tmp
								= OntologyWrapper\Term::ResolveCountryCode(
										$theWrapper, $data[ 'CooperatorCountryCode' ] ) )
							$theContainer[ getTag( ':entity:nationality' ) ] = $tmp;
					}
				}
			}
		}
								
		//
		// Set donor accession number.
		//
		if( array_key_exists( 'DonorAccessionNumber', $theUnit ) )
			$theContainer[ getTag( 'mcpd:DONORNUMB' ) ]
				= $theUnit[ 'DonorAccessionNumber' ];

	} // loadSource.
	

	/**
	 * Load accession status.
	 *
	 * This function will load the accession status related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadStatus( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set MLSSTAT.
		//
		if( array_key_exists( 'InTrust', $theUnit ) )
			$theContainer[ getTag( 'mcpd:MLSSTAT' ) ]
				= ( $theUnit[ 'InTrust' ] )
				? 'mcpd:MLSSTAT:1'
				: 'mcpd:MLSSTAT:0';

	} // loadStatus.
	

	/**
	 * Load material transfers.
	 *
	 * This function will load the material transfers related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadTransfers( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		
		//
		// Select transfers.
		//
		$query = "SELECT * FROM `singer_trans` "
				."WHERE( `AccessionID` = "
				.'0x'.bin2hex( $theUnit[ 'AccessionID' ] )." ) "
				."ORDER BY `TransferDate` ASC "
				."LIMIT $start,$limit";
		$rs = $theDatabase->execute( $query );
		while( $rs->RecordCount() )
		{
			//
			// Iterate page.
			//
			foreach( $rs as $record )
			{
				//
				// Scan record.
				//
				$data = Array();
				foreach( $record as $key => $value )
				{
					//
					// Normalise value.
					//
					if( strlen( trim( $value ) ) )
						$data[ $key ] = trim( $value );
			
				} // Scanning record.
			
				//
				// Skip empty records.
				//
				if( ! count( $data ) )
					continue;													// =>
				
				//
				// Init sub.
				//
				$sub = Array();
		
				//
				// Set :germplasm:mt:date.
				//
				if( array_key_exists( 'TransferDate', $data ) )
					$sub[ getTag( ':germplasm:mt:date' ) ]
						= $data[ 'TransferDate' ];
		
				//
				// Set :germplasm:mt:smta.
				//
				if( array_key_exists( 'TransferSMTA', $data )
				 && ($data[ 'TransferSMTA' ] != '99') )
					$sub[ getTag( ':germplasm:mt:smta' ) ]
						= ':germplasm:mt:smta:'.$data[ 'TransferSMTA' ];
		
				//
				// Set :germplasm:mt:smta:notes.
				//
				if( array_key_exists( 'TransferSMTANotes', $data ) )
					$sub[ getTag( ':germplasm:mt:smta:notes' ) ]
						= $data[ 'TransferSMTANotes' ];
		
				//
				// Set :germplasm:mt:samples-intended.
				//
				if( array_key_exists( 'Samples', $data ) )
					$sub[ getTag( ':germplasm:mt:samples-intended' ) ]
						= $data[ 'Samples' ];
		
				//
				// Set :entity:identifier.
				//
				if( array_key_exists( 'CooperatorCode', $data ) )
					$sub[ getTag( ':entity:identifier' ) ]
						= $data[ 'CooperatorCode' ];
		
				//
				// Set :inventory:INSTCODE.
				//
				if( array_key_exists( 'CooperatorInstituteFAOCode', $data ) )
					$sub[ getTag( ':inventory:institute' ) ]
						= kDOMAIN_ORGANISATION
						 .'://http://fao.org/wiews:'
						 .$data[ 'CooperatorInstituteFAOCode' ]
						 .kTOKEN_END_TAG;
		
				//
				// Set :name.
				//
				if( array_key_exists( 'CooperatorName', $data ) )
					$sub[ kTAG_NAME ]
						= $data[ 'CooperatorName' ];
				elseif( array_key_exists( 'CooperatorLocalCode', $data ) )
					$sub[ kTAG_NAME ]
						= $data[ 'CooperatorCode' ];
		
				//
				// Set :type:entity.
				//
				if( array_key_exists( 'CooperatorType', $data )
				 || array_key_exists( 'CooperatorEntityType', $data ) )
				{
					$tmp = Array();
					if( array_key_exists( 'CooperatorType', $data )
					 && ($data[ 'CooperatorType' ] != '999') )
						$tmp[] = ':type:entity:'.$data[ 'CooperatorType' ];
					if( array_key_exists( 'CooperatorEntityType', $data )
					 && ($data[ 'CooperatorEntityType' ] != '999')
					 && (! in_array( ':type:entity:'.$data[ 'CooperatorEntityType' ],
					 				 $tmp )) )
						$tmp[] = ':type:entity:'.$data[ 'CooperatorEntityType' ];
					if( count( $tmp ) )
						$sub[ getTag( ':type:entity' ) ] = $tmp;
				}
		
				//
				// Set :entity:mail.
				//
				if( array_key_exists( 'CooperatorAddress', $data ) )
					$sub[ getTag( ':entity:mail' ) ][]
						= array( kTAG_TEXT => $data[ 'CooperatorAddress' ] );
		
				//
				// Set :entity:email.
				//
				if( array_key_exists( 'CooperatorEmail', $data ) )
					$sub[ getTag( ':entity:email' ) ][]
						= array( kTAG_TEXT => $data[ 'CooperatorEmail' ] );
								
				//
				// Set country.
				//
				if( array_key_exists( 'CooperatorCountryCode', $theUnit ) )
				{
					if( $tmp
							= OntologyWrapper\Term::ResolveCountryCode(
									$theWrapper, $data[ 'CooperatorCountryCode' ] ) )
						$sub[ getTag( ':entity:nationality' ) ] = $tmp;
				}
		
				//
				// Load record.
				//
				if( count( $sub ) )
					$theContainer[] = $sub;
			
			} // Iterating page.
		
			//
			// Close recordset.
			//
			$rs->Close();
			$rs = NULL;
		
			//
			// Read next.
			//
			$start += $limit;
			$query = "SELECT * FROM `singer_trans` "
					."WHERE( `AccessionID` = "
					.'0x'.bin2hex( $theUnit[ 'AccessionID' ] )." ) "
					."ORDER BY `TransferDate` ASC "
					."LIMIT $start,$limit";
			$rs = $theDatabase->execute( $query );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadTransfers.
	

	/**
	 * Get tag.
	 *
	 * This function will return the tag serial number provided its native identifier, if
	 * the tag fails to resolve, the method will raise an exception.
	 *
	 * @param string				$theIdentifier		Native identifier.
	 * @return int					Serial identifier.
	 */
	function getTag( $theIdentifier )
	{
		global $wrapper;
		
		return $wrapper->getSerial( $theIdentifier, TRUE );							// ==>

	} // getTag.

?>
