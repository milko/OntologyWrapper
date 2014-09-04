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
define( 'kDO_CLIMATE',	FALSE );


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
$limit = 100;
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
	$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$mWrapper ) );
	
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
	// Import.
	//
	echo( "  • Exporting\n" );
	$query = "SELECT * FROM `singer_acc` ";
	if( $last !== NULL )
		$query .= "WHERE( `AccessionID` > $last ) ";
	$query .= "ORDER BY `AccessionID` LIMIT $start,$limit";
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
if( $object->offsetExists( ':domain:accession:collecting' ) )
{
print_r( $object->getArrayCopy() );
}
			$xml = $object->export( 'xml' );
/*
			$insert = "REPLACE INTO `$table`( "
					 ."`id`, `class`, `xml` ) VALUES( "
					 .'0x'.bin2hex( (string) $record[ 'UnitID' ] ).', '
					 .'0x'.bin2hex( get_class( $object ) ).', '
					 .'0x'.bin2hex( $xml->asXML() ).' )';
			$dc_out->Execute( $insert );
*/
			
		} // Iterating page.
		
		//
		// Close recordset.
		//
		$rs->Close();
		$rs = NULL;
			
		//
		// Inform.
		//
		echo( '.' );
		
		//
		// Read next.
		//
		$start += $limit;
		$query = "SELECT * FROM `singer_acc` ";
		if( $last !== NULL )
			$query .= "WHERE( `AccessionID` > $last ) ";
		$query .= "ORDER BY `AccessionID` LIMIT $start,$limit";
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
		//
		// Set accession ID.
		//
		$theObject->offsetSet(
			':germplasm:accession-id',
			$theData[ 'HoldingInstituteFAOCode' ].kTOKEN_INDEX_SEPARATOR
		   .$theData[ 'HoldingCollectionCode' ].kTOKEN_NAMESPACE_SEPARATOR
		   .$theData[ 'AccessionNumber' ] );
		
		//
		// Set dataset.
		//
		$theObject->offsetSet( ':inventory:dataset', 'SINGER' );
		
		//
		// Set holding institute.
		//
		$theObject->offsetSet(
			':inventory:INSTCODE',
			kDOMAIN_ORGANISATION
		   .'://http://fao.org/wiews:'
		   .$theData[ 'HoldingInstituteFAOCode' ]
		   .kTOKEN_END_TAG );
		
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
			$theObject->offsetSet( 'mcpd:ACCENAME',
									explode( ';', $theData[ 'AccessionNames' ] ) );
		
		//
		// Set other accession identifiers.
		//
		if( array_key_exists( 'OtherAccessionIdentification', $theData ) )
			$theObject->offsetSet( 'mcpd:OTHERNUMB',
									explode( ';',
											 $theData[ 'OtherAccessionIdentification' ] ) );
		
		
		//
		// Set taxon reference.
		//
		if( array_key_exists( 'TaxonReference', $theData ) )
			$theObject->offsetSet( ':taxon:reference',
								   'http://www.ars-grin.gov/cgi-bin/npgs/html/index.pl' );
		//
		// Set taxon URL.
		//
		if( array_key_exists( 'TaxonReference', $theData ) )
			$theObject->offsetSet( ':taxon:url',
								   $theData[ 'TaxonReference' ] );
		
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
		// Set vernacular names.
		//
		if( array_key_exists( 'CropNames', $theData ) )
		{
			$theObject->offsetSet( ':taxon:names',
								   array( kTAG_TEXT =>
								   	explode( ';', $theData[ 'CropNames' ] ) ) );
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
			$theObject->offsetSet( ':taxon:annex-1',
									':taxon:annex-1:'.$theData[ 'Annex1' ] );
		
		//
		// Set ancestors.
		//
		if( array_key_exists( 'AncestralData', $theData ) )
			$theObject->offsetSet( 'mcpd:ANCEST',
								   $theData[ 'AncestralData' ] );
		
		//
		// Set taxon MLSSTAT.
		//
		if( array_key_exists( 'InTrust', $theData ) )
			$theObject->offsetSet( 'mcpd:MLSSTAT',
								   ( ( $theData[ 'InTrust' ] )
									 ? 'mcpd:MLSSTAT:1'
									 : 'mcpd:MLSSTAT:0' ) );
		
		//
		// Set sample AVAILABLE.
		//
		if( array_key_exists( 'Available', $theData ) )
			$theObject->offsetSet( 'mcpd:AVAILABLE',
								   ( ( $theData[ 'Available' ] )
									 ? 'mcpd:AVAILABLE:1'
									 : 'mcpd:AVAILABLE:0' ) );
		
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
		if( count( $sub )
		 && array_key_exists( ':collecting:entities', $sub ) )
			$theObject->offsetSet( ':domain:accession:collecting', $sub );
/*		
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
		// Load transfers.
		//
		$sub = Array();
		loadTransfers(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':germplasm:mt', $sub );
*/
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
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		
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
		// Set georeference notes.
		//
		if( array_key_exists( 'CollectingSiteGeoreferenceNotes', $theUnit ) )
			$theContainer[ getTag( ':location:site:georeference-notes' ) ]
				= $theUnit[ 'CollectingSiteGeoreferenceNotes' ];
		
		//
		// Select collectors.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `singer_collectors` "
									."WHERE( `AccessionID` = "
									.'0x'.bin2hex( $theUnit[ 'AccessionID' ] )." ) "
									."LIMIT $start,$limit" );
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
				// Set :entity:identifier.
				//
				if( array_key_exists( 'CooperatorCode', $data ) )
					$sub[ getTag( ':entity:identifier' ) ]
						= $data[ 'CooperatorCode' ];
		
				//
				// Set :inventory:INSTCODE.
				//
				if( array_key_exists( 'CooperatorInstituteFAOCode', $data ) )
					$sub[ ':inventory:INSTCODE' ]
						= kDOMAIN_ORGANISATION
						 .'://http://fao.org/wiews:'
						 .$data[ 'CooperatorInstituteFAOCode' ];
		
				//
				// Set :name.
				//
				if( array_key_exists( 'CooperatorName', $data ) )
					$sub[ getTag( ':name' ) ]
						= $data[ 'CooperatorName' ];
		
				//
				// Set :type:entity.
				//
				if( array_key_exists( 'CooperatorType', $data )
				 || array_key_exists( 'CooperatorEntityType', $data ) )
				{
					$tmp = Array();
					if( array_key_exists( 'CooperatorType', $data ) )
						$tmp[] = ':type:entity:'.$data[ 'CooperatorType' ];
					if( array_key_exists( 'CooperatorEntityType', $data )
					 && (! in_array( ':type:entity:'.$data[ 'CooperatorEntityType' ],
					 				 $tmp )) )
						$tmp[] = ':type:entity:'.$data[ 'CooperatorEntityType' ];
					if( count( $tmp ) )
						$sub[ ':type:entity' ] = $tmp;
				}
		
				//
				// Set :entity:mail.
				//
				if( array_key_exists( 'CooperatorAddress', $data ) )
					$sub[ getTag( ':entity:mail' ) ]
						= $data[ 'CooperatorAddress' ];
		
				//
				// Set :entity:email.
				//
				if( array_key_exists( 'CooperatorEmail', $data ) )
					$sub[ getTag( ':entity:email' ) ]
						= $data[ 'CooperatorEmail' ];
								
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
					$theContainer[ getTag( ':collecting:entities' ) ] = $sub;
			
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

	} // loadCollecting.
	

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
