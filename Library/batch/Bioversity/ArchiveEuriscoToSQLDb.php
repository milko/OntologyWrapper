<?php

/**
 * SQL EURISCO archive procedure.
 *
 * This file contains routines to load EURISCO from an SQL database and archive it as XML
 * in the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/09/2014
 */

/*=======================================================================================
 *																						*
 *								ArchiveEuriscoToSQLDb.php								*
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
	// MySQLi://user:pass@localhost/eurisco_itw?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// MySQLi://user:pass@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist
				."<Output SQL database DSN> "
	// eurisco
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
// Init base query.
//
$base_query = <<<EOT
select
	`eurisco_itw`.`accessions`.`ID` AS `ID`,
	`eurisco_itw`.`accessions`.`InventoryCode` AS `NICODE`,
	`eurisco_itw`.`accessions`.`HoldingInstituteCode` AS `INSTCODE`,
	`eurisco_itw`.`taxa`.`Genus` AS `COLLECTION`,
	`eurisco_itw`.`accessions`.`AccessionNumber` AS `ACCENUMB`,
	`eurisco_itw`.`accessions`.`CollectingNumber` AS `COLLNUMB`,
	`eurisco_itw`.`accessions`.`CollectingInstituteCode` AS `COLLCODE`,
	`eurisco_itw`.`taxa`.`Genus` AS `GENUS`,
	`eurisco_itw`.`taxa`.`Species` AS `SPECIES`,
	`eurisco_itw`.`taxa`.`SpeciesAuthor` AS `SPAUTHOR`,
	`eurisco_itw`.`taxa`.`InfraspeciesEpithet` AS `SUBTAXA`,
	`eurisco_itw`.`taxa`.`InfraspeciesAuthor` AS `SUBTAUTHOR`,
	`eurisco_itw`.`accessions`.`CropNames` AS `CROPNAME`,
	`eurisco_itw`.`accessions`.`AccessionNames` AS `ACCENAME`,
	`eurisco_itw`.`accessions`.`AcquisitionDate` AS `ACQDATE`,
	`ancillary`.`code_iso_3166`.`ISO3` AS `ORIGCTY`,
	`eurisco_itw`.`accessions`.`CollectingLocality` AS `COLLSITE`,
	`eurisco_itw`.`accessions`.`ProvidedLatitude` AS `LATITUDE`,
	`eurisco_itw`.`accessions`.`CollectingLatitude` AS `LATITUDED`,
	`eurisco_itw`.`accessions`.`ProvidedLongitude` AS `LONGITUDE`,
	`eurisco_itw`.`accessions`.`CollectingLongitude` AS `LONGITUDED`,
	IF( `eurisco_itw`.`accessions`.`CollectingLatitudeError` > 0,
		`eurisco_itw`.`accessions`.`CollectingLatitudeError` * 111034,
		NULL ) AS `ERROR`,
	`eurisco_itw`.`accessions`.`CollectingElevation` AS `ELEVATION`,
	`eurisco_itw`.`accessions`.`CollectingDate` AS `COLLDATE`,
	`BREEDERS`.`InstituteCode` AS `BREDCODE`,
	`eurisco_itw`.`accessions`.`BiologicalStatus` AS `SAMPSTAT`,
	`eurisco_itw`.`accessions`.`Ancestors` AS `ANCEST`,
	`eurisco_itw`.`accessions`.`AcquisitionSource` AS `COLLSRC`,
	`DONORS`.`InstituteCode` AS `DONORCODE`,
	`DONORS`.`Number` AS `DONORNUMB`,
	`eurisco_itw`.`accessions`.`OTHERNUMB` AS `OTHERNUMB`,
	`eurisco_itw`.`accessions`.`DUPLSITE` AS `DUPLSITE`,
	group_concat(`eurisco_itw`.`accession_storage`.`Storage` separator ',') AS `STORAGE`,
	`eurisco_itw`.`accessions`.`REMARKS` AS `REMARKS`,
	`eurisco_itw`.`accessions`.`Collectors` AS `COLLDESCR`,
	`BREEDERS`.`Name` AS `BREDDESCR`,
	`DONORS`.`Name` AS `DONORDESCR`,
	`eurisco_itw`.`accessions`.`DUPLDESCR` AS `DUPLDESCR`,
	`eurisco_itw`.`accessions`.`URL` AS `ACCEURL`,
	`eurisco_itw`.`accessions`.`MLSSTAT` AS `MLSSTAT`,
	`eurisco_itw`.`accessions`.`AEGISSTAT` AS `AEGISSTAT`,
	`eurisco_itw`.`accessions`.`DateOfLastChange` AS `DateOfLastChange`
from
	(((((`eurisco_itw`.`accessions` left join `eurisco_itw`.`holdings` `BREEDERS` on((`BREEDERS`.`ID` = `eurisco_itw`.`accessions`.`BreederID`))) left join `eurisco_itw`.`holdings` `DONORS` on((`DONORS`.`ID` = `eurisco_itw`.`accessions`.`DonorID`))) left join `eurisco_itw`.`taxa` on((`eurisco_itw`.`taxa`.`ID` = `eurisco_itw`.`accessions`.`TaxonID`))) left join `ancillary`.`code_iso_3166` on((`ancillary`.`code_iso_3166`.`Code` = `eurisco_itw`.`accessions`.`CountryOrigin`))) left join `eurisco_itw`.`accession_storage` on((`eurisco_itw`.`accession_storage`.`AccessionID` = `eurisco_itw`.`accessions`.`ID`)))
group by
	`eurisco_itw`.`accessions`.`ID`
EOT;

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
echo( "\n==> Loading EURISCO into $table.\n" );

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
	$query = $base_query;
	if( $last !== NULL )
		$query .= " WHERE `ID` > $last";
	$query .= " ORDER BY `eurisco_itw`.`accessions`.`ID` LIMIT $start,$limit";
	$rs = $dc_in->Execute( $query );
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
		$query = $base_query;
		if( $last !== NULL )
			$query .= " WHERE `ID` > $last";
		$query .= " ORDER BY `eurisco_itw`.`accessions`.`ID` LIMIT $start,$limit";
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
		// Set taxon.
		//
		$tmp = $theData[ 'GENUS' ];
		if( array_key_exists( 'SPECIES', $theData ) )
			$tmp .= (' '.$theData[ 'SPECIES' ]);
		if( array_key_exists( 'SUBTAXA', $theData ) )
			$tmp .= (' '.$theData[ 'SUBTAXA' ]);
		$theData[ ':taxon:epithet' ] = $tmp;
		
		/***********************************************************************
		 * Set unit identification properties.
		 **********************************************************************/
		
		//
		// Set authority.
		//
		$theObject->offsetSet( kTAG_AUTHORITY, $theData[ 'INSTCODE' ] );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( kTAG_COLLECTION, $theData[ 'GENUS' ] );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ 'ACCENUMB' ] );
		
		//
		// Set version.
		//
		if( array_key_exists( 'DateOfLastChange', $theData ) )
			$theObject->offsetSet( kTAG_VERSION,
								   substr( $theData[ 'DateOfLastChange' ], 0, 4 )
								  .substr( $theData[ 'DateOfLastChange' ], 5, 2 )
								  .substr( $theData[ 'DateOfLastChange' ], 8, 2 ) );
				
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet( ':inventory:dataset',
			'European network of ex situ National Inventories (EURISCO)' );
		
		//
		// Set National inventory code.
		//
		$theObject->offsetSet( ':inventory:code', $theData[ 'NICODE' ] );
		
		//
		// Set inventory administrative code.
		//
		if( $theData[ 'NICODE' ] != 'NGB' )
			$theObject->offsetSet( ':inventory:admin',
								   "iso:3166:1:alpha-3:".$theData[ 'NICODE' ] );
		else
			$theObject->offsetSet( ':inventory:admin',
								   "iso:3166:1:alpha-3:"
								  .substr( $theData[ 'INSTCODE' ], 0, 3 ) );
		
		//
		// Set inventory institute.
		//
		$theObject->offsetSet(
			':inventory:institute',
			kDOMAIN_ORGANISATION
		   .'://http://fao.org/wiews:'
		   .strtoupper( $theData[ 'INSTCODE' ] )
		   .kTOKEN_END_TAG );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set germplasm identifier.
		//
		$theObject->offsetSet(
			':germplasm:identifier',
			$theData[ 'INSTCODE' ].kTOKEN_INDEX_SEPARATOR
		   .$theData[ 'COLLECTION' ].kTOKEN_NAMESPACE_SEPARATOR
		   .$theData[ 'ACCENUMB' ] );
		
		//
		// Set holding institute code.
		//
		$theObject->offsetSet( 'mcpd:INSTCODE',
								$theData[ 'INSTCODE' ] );
		
		//
		// Set accession number.
		//
		$theObject->offsetSet( 'mcpd:ACCENUMB',
								$theData[ 'ACCENUMB' ] );
		
		//
		// Set accession name.
		//
		if( array_key_exists( 'ACCENAME', $theData ) )
		{
			$tmp = Array();
			foreach( explode( ',', $theData[ 'ACCENAME' ] ) as $item )
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
		// Set taxon genus.
		//
		if( array_key_exists( 'GENUS', $theData ) )
			$theObject->offsetSet( ':taxon:genus',
								   $theData[ 'GENUS' ] );
		
		//
		// Set taxon species.
		//
		if( array_key_exists( 'SPECIES', $theData ) )
			$theObject->offsetSet( ':taxon:species',
								   $theData[ 'SPECIES' ] );
		
		//
		// Set taxon species authority.
		//
		if( array_key_exists( 'SPAUTHOR', $theData ) )
			$theObject->offsetSet( ':taxon:species:author',
								   $theData[ 'SPAUTHOR' ] );
		
		//
		// Set taxon infraspecific epithet.
		//
		if( array_key_exists( 'SUBTAXA', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies',
								   $theData[ 'SUBTAXA' ] );
		
		//
		// Set taxon infraspecific authority.
		//
		if( array_key_exists( 'SUBTAUTHOR', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies:author',
								   $theData[ 'SUBTAUTHOR' ] );
		
		//
		// Set species name.
		//
		if( array_key_exists( 'GENUS', $theData )
		 && array_key_exists( 'SPECIES', $theData ) )
			$theObject->offsetSet(
				':taxon:species:name',
				implode( ' ', array( $theData[ 'GENUS' ],
									 $theData[ 'SPECIES' ] ) ) );
		
		//
		// Set taxon epithet.
		//
		$tmp = $theData[ 'GENUS' ];
		if( array_key_exists( 'SPECIES', $theData ) )
			$tmp .= (' '.$theData[ 'SPECIES' ]);
		if( array_key_exists( 'SUBTAXA', $theData ) )
			$tmp .= (' '.$theData[ 'SUBTAXA' ]);
		if( strlen( $tmp ) )
			$theObject->offsetSet( ':taxon:epithet', $tmp );
		
		//
		// Set vernacular names.
		//
		if( array_key_exists( 'CROPNAME', $theData ) )
		{
			$tmp = Array();
			foreach( explode( ';', $theData[ 'CROPNAME' ] ) as $item )
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
		if( array_key_exists( 'CROP', $theData ) )
			$theObject->offsetSet( ':taxon:crop',
									':taxon:crop:'.$theData[ 'CROP' ] );
		
		//
		// Set remarks.
		//
		if( array_key_exists( 'REMARKS', $theData ) )
			$theObject->offsetSet( 'mcpd:REMARKS',
								   $theData[ 'REMARKS' ] );
		
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
		// Load germplasm neighbourhood.
		//
		$sub = Array();
		loadNeighbourhood( $sub,
						   $theData,
						   $theWrapper,
						   $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':germplasm:neighbourhood', $sub );

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
		if( array_key_exists( 'COLLDATE', $theUnit ) )
			$theContainer[ getTag( 'mcpd:COLLDATE' ) ]
				= $theUnit[ 'COLLDATE' ];
								
		//
		// Set collecting number.
		//
		if( array_key_exists( 'COLLNUMB', $theUnit ) )
			$theContainer[ getTag( 'mcpd:COLLNUMB' ) ]
				= $theUnit[ 'COLLNUMB' ];
								
		//
		// Set country.
		//
		if( array_key_exists( 'ORIGCTY', $theUnit ) )
		{
			if( $tmp
					= OntologyWrapper\Term::ResolveCountryCode(
							$theWrapper, $theUnit[ 'ORIGCTY' ] ) )
				$theContainer[ getTag( ':location:country' ) ] = $tmp;
		}
								
		//
		// Set locality.
		//
		if( array_key_exists( 'COLLSITE', $theUnit ) )
			$theContainer[ getTag( ':location:locality' ) ]
				= $theUnit[ 'COLLSITE' ];
								
		//
		// Set elevation.
		//
		if( array_key_exists( 'ELEVATION', $theUnit ) )
			$theContainer[ getTag( ':location:site:elevation' ) ]
				= $theUnit[ 'ELEVATION' ];
								
		//
		// Set latitude.
		//
		if( array_key_exists( 'LATITUDED', $theUnit ) )
			$theContainer[ getTag( ':location:site:latitude' ) ]
				= $theUnit[ 'LATITUDED' ];
								
		//
		// Set latitude provided.
		//
		if( array_key_exists( 'LATITUDE', $theUnit ) )
			$theContainer[ getTag( ':location:site:latitude:provided' ) ]
				= $theUnit[ 'LATITUDE' ];
								
		//
		// Set longitude.
		//
		if( array_key_exists( 'LONGITUDED', $theUnit ) )
			$theContainer[ getTag( ':location:site:longitude' ) ]
				= $theUnit[ 'LONGITUDED' ];
								
		//
		// Set longitude provided.
		//
		if( array_key_exists( 'LONGITUDE', $theUnit ) )
			$theContainer[ getTag( ':location:site:longitude:provided' ) ]
				= $theUnit[ 'LONGITUDE' ];
		
		//
		// Set collecting site error.
		//
		if( array_key_exists( 'ERROR', $theUnit ) )
			$theContainer[ getTag( ':location:site:error' ) ]
				= $theUnit[ 'ERROR' ];
		
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
		// Init sub.
		//
		$sub = Array();

		//
		// Set COLLCODE.
		//
		if( array_key_exists( 'COLLCODE', $theUnit ) )
			$sub[ getTag( 'mcpd:COLLCODE' ) ]
				= $theUnit[ 'COLLCODE' ];

		//
		// Set COLLDESCR.
		//
		if( array_key_exists( 'COLLDESCR', $theUnit ) )
		{
			$sub[ getTag( 'mcpd:COLLDESCR' ) ]
				= $theUnit[ 'COLLDESCR' ];
		}

		//
		// Set :inventory:INSTCODE.
		//
		if( array_key_exists( 'COLLCODE', $theUnit ) )
			$sub[ getTag( ':inventory:institute' ) ]
				= kDOMAIN_ORGANISATION
				 .'://http://fao.org/wiews:'
				 .strtoupper( $theUnit[ 'COLLCODE' ] )
				 .kTOKEN_END_TAG;

		//
		// Set :name.
		//
		if( array_key_exists( 'COLLDESCR', $theUnit ) )
			$sub[ getTag( ':name' ) ]
				= $theUnit[ 'COLLDESCR' ];
		elseif( array_key_exists( 'COLLCODE', $theUnit ) )
			$sub[ getTag( ':name' ) ]
				= $theUnit[ 'COLLCODE' ];

		//
		// Load record.
		//
		if( count( $sub ) )
			$theContainer[] = $sub;

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
		if( array_key_exists( 'ANCEST', $theUnit ) )
			$theContainer[ getTag( 'mcpd:ANCEST' ) ]
				= $theUnit[ 'ANCEST' ];
		
		//
		// Load breeding entities.
		//
		$sub = Array();
		loadBreeders( $sub,
					  $theUnit,
					  $theWrapper,
					  $theDatabase );
		if( count( $sub ) )
			$theContainer[ getTag( ':breeding:entities' ) ]
				= $sub;

	} // loadBreeding.
	

	/**
	 * Load breeding entities.
	 *
	 * This function will load the breeder's data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadBreeders( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init sub.
		//
		$sub = Array();

		//
		// Set BREDCODE.
		//
		if( array_key_exists( 'BREDCODE', $theUnit ) )
			$sub[ getTag( 'mcpd:BREDCODE' ) ]
				= $theUnit[ 'BREDCODE' ];

		//
		// Set BREDDESCR.
		//
		if( array_key_exists( 'BREDDESCR', $theUnit ) )
		{
			$sub[ getTag( 'mcpd:BREDDESCR' ) ]
				= $theUnit[ 'BREDDESCR' ];
		}

		//
		// Set :inventory:INSTCODE.
		//
		if( array_key_exists( 'BREDCODE', $theUnit ) )
			$sub[ getTag( ':inventory:institute' ) ]
				= kDOMAIN_ORGANISATION
				 .'://http://fao.org/wiews:'
				 .strtoupper( $theUnit[ 'BREDCODE' ] )
				 .kTOKEN_END_TAG;

		//
		// Set :name.
		//
		if( array_key_exists( 'BREDDESCR', $theUnit ) )
			$sub[ getTag( ':name' ) ]
				= $theUnit[ 'BREDDESCR' ];
		elseif( array_key_exists( 'BREDCODE', $theUnit ) )
			$sub[ getTag( ':name' ) ]
				= $theUnit[ 'BREDCODE' ];

		//
		// Load record.
		//
		if( count( $sub ) )
			$theContainer[] = $sub;

	} // loadBreeders.
	

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
		if( array_key_exists( 'ACQDATE', $theUnit ) )
			$theContainer[ getTag( 'mcpd:ACQDATE' ) ]
				= $theUnit[ 'ACQDATE' ];
								
		//
		// Set storage.
		//
		if( array_key_exists( 'STORAGE', $theUnit ) )
		{
			$tmp = Array();
			foreach( explode( ',', $theUnit[ 'STORAGE' ] ) as $item )
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
		// Set safety duplicates.
		//
		if( array_key_exists( 'DUPLSITE', $theUnit )
		 || array_key_exists( 'DUPLDESCR', $theUnit ) )
		{
			$list = Array();
			if( array_key_exists( 'DUPLSITE', $theUnit ) )
			{
				foreach( explode( ',', $theUnit[ 'DUPLSITE' ] )
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
							 .strtoupper( $item )
							 .kTOKEN_END_TAG;
						$list[] = $tmp;
					}
				}
			}
			else
			{
				$tmp = Array();
				$item = trim( $theUnit[ 'DUPLDESCR' ] );
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
		
		//
		// Set other accession identifiers.
		//
		if( array_key_exists( 'OTHERNUMB', $theUnit ) )
			$theContainer[ getTag( 'mcpd:OTHERNUMB' ) ]
				= $theUnit[ 'OTHERNUMB' ];
		
		//
		// Set taxon AVAILABLE.
		//
		if( array_key_exists( 'AVAILABLE', $theUnit ) )
		{
			if( $theUnit[ 'AVAILABLE' ] == '1' )
				$theContainer[ getTag( 'mcpd:AVAILABLE' ) ]
					= 'mcpd:AVAILABLE:1';
			if( $theUnit[ 'AVAILABLE' ] == '0' )
				$theContainer[ getTag( 'mcpd:AVAILABLE' ) ]
					= 'mcpd:AVAILABLE:0';
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
		if( array_key_exists( 'COLLSRC', $theUnit )
		 && ($theUnit[ 'COLLSRC' ] != '99') )
			$theContainer[ getTag( 'mcpd:COLLSRC' ) ]
				= 'mcpd:COLLSRC:'.$theUnit[ 'COLLSRC' ];
								
		//
		// Set status code.
		//
		if( array_key_exists( 'SAMPSTAT', $theUnit )
		 && ($theUnit[ 'SAMPSTAT' ] != '999') )
			$theContainer[ getTag( 'mcpd:SAMPSTAT' ) ]
				= 'mcpd:SAMPSTAT:'.$theUnit[ 'SAMPSTAT' ];
		 
		//
		// Set DONORCODE.
		//
		if( array_key_exists( 'DONORCODE', $theUnit ) )
		{
			$theContainer[ getTag( 'mcpd:DONORCODE' ) ]
				= $theUnit[ 'DONORCODE' ];
			
			$theContainer[ getTag( ':inventory:institute' ) ]
				= kDOMAIN_ORGANISATION
				 .'://http://fao.org/wiews:'
				 .strtoupper( $theUnit[ 'DONORCODE' ] )
				 .kTOKEN_END_TAG;
		}
		
		//
		// Set DONORDESCR.
		//
		elseif( array_key_exists( 'DONORDESCR', $theUnit ) )
			$theContainer[ getTag( 'mcpd:DONORDESCR' ) ]
				= $theUnit[ 'DONORDESCR' ];
								
		//
		// Set donor accession number.
		//
		if( array_key_exists( 'DONORNUMB', $theUnit ) )
			$theContainer[ getTag( 'mcpd:DONORNUMB' ) ]
				= $theUnit[ 'DONORNUMB' ];

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
		// Set annex-1.
		//
		if( array_key_exists( 'ANNEX1', $theUnit ) )
		{
			if( $theUnit[ 'ANNEX1' ] != '900' )
				$theContainer[ getTag( ':taxon:annex-1' ) ]
					= ':taxon:annex-1:'.$theUnit[ 'ANNEX1' ];
		}
		
		//
		// Set taxon MLSSTAT.
		//
		if( array_key_exists( 'MLSSTAT', $theUnit ) )
		{
			if( $theUnit[ 'MLSSTAT' ] == '1' )
				$theContainer[ getTag( 'mcpd:MLSSTAT' ) ]
					= 'mcpd:MLSSTAT:1';
			if( $theUnit[ 'MLSSTAT' ] == '0' )
				$theContainer[ getTag( 'mcpd:MLSSTAT' ) ]
					= 'mcpd:MLSSTAT:0';
		}
		
		//
		// Set taxon AEGISSTAT.
		//
		if( array_key_exists( 'AEGISSTAT', $theUnit ) )
		{
			if( $theUnit[ 'AEGISSTAT' ] == '1' )
				$theContainer[ getTag( 'mcpd:AEGISSTAT' ) ]
					= 'mcpd:AEGISSTAT:1';
			if( $theUnit[ 'AEGISSTAT' ] == '0' )
				$theContainer[ getTag( 'mcpd:AEGISSTAT' ) ]
					= 'mcpd:AEGISSTAT:0';
		}

	} // loadStatus.
	

	/**
	 * Load germplasm neighbourhood.
	 *
	 * This function will load the accession germplasm neighbourhood related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadNeighbourhood( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		global $wrapper;
		
		//
		// Check other identification.
		//
		if( array_key_exists( 'OTHERNUMB', $theUnit ) )
		{
			//
			// Iterate elements.
			//
			foreach( explode( ';', $theUnit[ 'OTHERNUMB' ] ) as $element )
			{
				//
				// Init loop storage.
				//
				$sub = Array();
				$element = trim( $element );
				
				//
				// Parse identifier.
				//
				$items = explode( ':', $element );
				
				//
				// Set institute code.
				//
				$instcode = ( strlen( trim( $items[ 0 ] ) ) )
						  ? trim( $items[ 0 ] )
						  : NULL;
				
				//
				// Set identifier.
				//
				if( count( $items ) > 1 )
					$accenumb = ( strlen( trim( $items[ 1 ] ) ) )
							  ? trim( $items[ 1 ] )
							  : NULL;
				else
					$accenumb = NULL;
				
				//
				// Set germplasm identifier.
				//
				$sub[ getTag( ':germplasm:identifier' ) ]
					= ( $instcode !== NULL )
					? $element
					: $accenumb;
				
				//
				// Set institute.
				//
				if( $instcode !== NULL )
				{
					//
					// Set institute code.
					//
					$sub[ getTag( 'mcpd:INSTCODE' ) ]
						= $instcode;
				
					//
					// Set reference.
					//
					$reference
						= kDOMAIN_ORGANISATION
						 .'://http://fao.org/wiews:'
						 .strtoupper( $instcode )
						 .kTOKEN_END_TAG;
					
					//
					// Check institute.
					//
					$tmp = new OntologyWrapper\FAOInstitute( $wrapper, $reference, FALSE );
					if( $tmp->committed() )
					{
						//
						// Set institute.
						//
						$sub[ getTag( ':inventory:institute' ) ]
							= $reference;
					}
				
					//
					// Set accession number.
					//
					if( $accenumb !== NULL )
						$sub[ getTag( 'mcpd:ACCENUMB' ) ]
							= $accenumb;
				}
				
				//
				// Set element.
				//
				if( count( $sub ) )
					$theContainer[]
						= $sub;
			}
		}

	} // loadNeighbourhood.
	

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
