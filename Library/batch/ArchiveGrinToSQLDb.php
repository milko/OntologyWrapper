<?php

/**
 * SQL GRIN archive procedure.
 *
 * This file contains routines to load GRIN from an SQL database and archive it as XML
 * in the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/09/2014
 */

/*=======================================================================================
 *																						*
 *								ArchiveGrinToSQLDb.php									*
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
	// grin
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
$base_query = "SELECT * from `grin_acc`";

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
echo( "\n==> Loading GRIN into $table.\n" );

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
	$wrapper->metadata( $mongo );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$wrapper->units( $mongo );
	
	//
	// Set entities.
	//
	echo( "  • Setting users.\n" );
	$wrapper->users( $mongo );
	
	//
	// Check graph database.
	//
	if( $graph !== NULL )
	{
		//
		// Set graph database.
		//
		echo( "  • Setting graph.\n" );
		$wrapper->graph(
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
	$query .= " ORDER BY `ID` LIMIT $start,$limit";
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
					   .'0x'.bin2hex( (string) $record[ 'ID' ] ).', '
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
		$query .= " ORDER BY `ID` LIMIT $start,$limit";
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
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ 'ACCENUMB' ] );
		
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
			'National Plant Germplasm System (GRIN)' );
		
		//
		// Set National inventory code.
		//
		$theObject->offsetSet( ':inventory:code', 'USA' );
		
		//
		// Set inventory administrative unit.
		//
		$theObject->offsetSet( ':inventory:admin', 'iso:3166:1:alpha-3:USA' );
		
		//
		// Set Genesys ID.
		//
		if( array_key_exists( 'ALIS_ID', $theData ) )
			$theObject->offsetSet( ':inventory:GENESYS',
								   (string) $theData[ 'ALIS_ID' ] );
		
		//
		// Set holding institute.
		//
		$theObject->offsetSet(
			':inventory:institute',
			kDOMAIN_ORGANISATION
		   .'://http://fao.org/wiews:'
		   .$theData[ 'INSTCODE' ]
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
			$theObject->offsetSet( 'mcpd:ACCENAME',
								   array( $theData[ 'ACCENAME' ] ) );
		
		//
		// Set taxon family.
		//
		if( array_key_exists( 'FAMILY', $theData ) )
			$theObject->offsetSet( ':taxon:familia',
								   $theData[ 'FAMILY' ] );
		
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
		if( array_key_exists( 'TAXON', $theData ) )
			$theObject->offsetSet( ':taxon:epithet',
								   $theData[ 'TAXON' ] );
		
		//
		// Set taxon valid name.
		//
		if( array_key_exists( 'ValidName', $theData ) )
			$theObject->offsetSet( ':taxon:valid',
								   $theData[ 'ValidName' ] );
		
		//
		// Set taxon reference.
		//
		if( array_key_exists( 'TAXREF', $theData ) )
			$theObject->offsetSet(
				':taxon:reference',
				array( 'http://www.ars-grin.gov/cgi-bin/npgs/html/index.pl' ) );
		
		//
		// Set taxon URL.
		//
		if( array_key_exists( 'TAXREF', $theData ) )
			$theObject->offsetSet( ':taxon:url',
								   $theData[ 'TAXREF' ] );
		
		//
		// Set vernacular names.
		//
		if( array_key_exists( 'CROPNAME', $theData ) )
		{
			$name = html_entity_decode( $theData[ 'CROPNAME' ],
										ENT_COMPAT | ENT_HTML401,
										'UTF-8' );
			$tmp = Array();
			foreach( explode( ';', $name ) as $item )
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
		// Set uses.
		//
		if( array_key_exists( 'DesignationUse', $theData ) )
			$theObject->offsetSet( ':taxon:designation:use',
								   explode( '§', $theData[ 'DesignationUse' ] ) );
		
		//
		// Set crop.
		//
		if( array_key_exists( 'CROP', $theData ) )
			$theObject->offsetSet( ':taxon:crop',
									':taxon:crop:'.$theData[ 'CROP' ] );
		
		//
		// Set annex-1.
		//
		if( array_key_exists( 'ANNEX1', $theData ) )
		{
			if( $theData[ 'ANNEX1' ] != '900' )
				$theObject->offsetSet( ':taxon:annex-1',
									   ':taxon:annex-1:'.$theData[ 'ANNEX1' ] );
		}
		
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
			switch( $theUnit[ 'ORIGCTY' ] )
			{
				case '001':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:002";
					break;
				case '002':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:ARB";
					break;
				case '003':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:142";
					break;
				case '004':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:AMI";
					break;
				case '006':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:BWI";
					break;
				case '007':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:013";
					break;
				case '008':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:151";
					break;
				case '009':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:150";
					break;
				case '012':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:FEA";
					break;
				case '014':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:IDC";
					break;
				case '015':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:KOR";
					break;
				case '017':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:AMD";
					break;
				case '018':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:MES";
					break;
				case '019':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:NGN";
					break;
				case '020':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:015";
					break;
				case '023':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:APL";
					break;
				case '024':
					$theContainer[ getTag( ':location:country' ) ] = "iso:3166:3:alpha-3:RHO";
					break;
				case '025':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:RUR";
					break;
				case '026':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:SOM";
					break;
				case '027':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:005";
					break;
				case '029':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:035";
					break;
				case '030':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:039";
					break;
				case '031':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:TUR";
					break;
				case '034':
					$theContainer[ getTag( ':location:country' ) ] = "iso:3166:1:alpha-3:NGA";
					break;
				case '036':
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:location:145";
					break;
				case '037':
					$theContainer[ getTag( ':location:country' ) ] = "iso:3166:3:alpha-3:SUN";
					$theContainer[ getTag( ':location:admin' ) ] = "iso:3166:2:RU-MOS";
					break;
				
				default:
					if( $tmp
							= OntologyWrapper\Term::ResolveCountryCode(
									$theWrapper, $theUnit[ 'ORIGCTY' ] ) )
						$theContainer[ getTag( ':location:country' ) ] = $tmp;
					break;
			}
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
				 .$theUnit[ 'COLLCODE' ]
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
				 .$theUnit[ 'BREDCODE' ]
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
							 .$item
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
			switch( $theUnit[ 'AVAILABLE' ] )
			{
				case '0':
				case '1':
					$theContainer[ getTag( 'mcpd:AVAILABLE' ) ]
						= 'mcpd:AVAILABLE:'.$theUnit[ 'AVAILABLE' ];
					break;
			}
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
				 .$theUnit[ 'DONORCODE' ]
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
		// Set taxon MLSSTAT.
		//
		if( array_key_exists( 'MLSSTAT', $theUnit ) )
		{
			switch( $theUnit[ 'MLSSTAT' ] )
			{
				case '0':
				case '1':
					$theContainer[ getTag( 'mcpd:MLSSTAT' ) ]
						= 'mcpd:MLSSTAT:'.$theUnit[ 'MLSSTAT' ];
					break;
			}
		}
		
		//
		// Set taxon AEGISSTAT.
		//
		if( array_key_exists( 'AEGISSTAT', $theUnit ) )
		{
			switch( $theUnit[ 'AEGISSTAT' ] )
			{
				case '0':
				case '1':
					$theContainer[ getTag( 'mcpd:AEGISSTAT' ) ]
						= 'mcpd:AEGISSTAT:'.$theUnit[ 'AEGISSTAT' ];
					break;
			}
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
					// Set accession number.
					//
					if( $accenumb !== NULL )
						$sub[ getTag( 'mcpd:ACCENUMB' ) ]
							= $accenumb;
					
					//
					// Set reference.
					//
					$institute
						= kDOMAIN_ORGANISATION
						 .'://http://fao.org/wiews:'
						 .$instcode
						 .kTOKEN_END_TAG;
					$inst = new OntologyWrapper\FAOInstitute( $wrapper, $institute, FALSE );
					if( $inst->committed() )
						$sub[ getTag( ':inventory:institute' ) ]
							= $institute;
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
