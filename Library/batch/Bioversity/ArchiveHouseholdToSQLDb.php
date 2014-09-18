<?php

/**
 * SQL household assessment archive procedure.
 *
 * This file contains routines to load household assessments from an SQL database and
 * archive it as XML in the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 17/09/2014
 */

/*=======================================================================================
 *																						*
 *								ArchiveHouseholdToSQLDb.php								*
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
define( 'kDO_CLIMATE', FALSE );


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
	// abdh
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
$limit = 10;
$page = 5;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Household';

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
echo( "\n==> Loading Household Assessments into $table.\n" );

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
	// Import.
	//
	$pages = $page;
	echo( "  • Exporting\n" );
	$query = "SELECT * FROM `abdh_household` ";
	if( $last !== NULL )
		$query .= "WHERE( `ID` > $last ) ";
	$query .= "ORDER BY `ID_HOUSEHOLD` LIMIT $start,$limit";
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
	/*
			$xml = $object->export( 'xml' );
			$insert = ( $last === NULL )
					? "INSERT INTO `$table`( "
					: "REPLACE INTO `$table`( ";
			$insert .= ("`id`, `class`, `xml` ) VALUES( "
					   .'0x'.bin2hex( (string) $record[ 'ID' ] ).', '
					   .'0x'.bin2hex( get_class( $object ) ).', '
					   .'0x'.bin2hex( $xml->asXML() ).' )');
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
		$query = "SELECT * FROM `abdh_household` ";
		if( $last !== NULL )
			$query .= "WHERE( `ID` > $last ) ";
		$query .= "ORDER BY `ID_HOUSEHOLD` LIMIT $start,$limit";
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
		// Set dataset.
		//
		$theObject->offsetSet( getTag( ':inventory:dataset' ), 'ABDH' );
		
		//
		// Set version.
		//
		$theObject->offsetSet( 'abdh:REF-YEAR', '2012' );
		
		//
		// Set household ID.
		//
		if( array_key_exists( 'ID_HOUSEHOLD', $theData ) )
			$theObject->offsetSet( 'abdh:ID_HOUSEHOLD', $theData[ 'ID_HOUSEHOLD' ] );
		
		//
		// Set geographic data.
		//
		$theObject->offsetSet( ':location:country', 'iso:3166:1:alpha-3:IND' );
		$theObject->offsetSet( ':location:admin', 'iso:3166:2:IN-RJ' );
		
		//
		// Set state.
		//
		if( array_key_exists( 'STATE', $theData ) )
		{
			$theObject->offsetSet( 'abdh:STATE', $theData[ 'STATE' ] );
			$theObject->offsetSet( ':location:admin-1', $theData[ 'STATE' ] );
		}
		
		//
		// Set district.
		//
		if( array_key_exists( 'DISTRICT', $theData ) )
		{
			$theObject->offsetSet( 'abdh:DISTRICT', $theData[ 'DISTRICT' ] );
			$theObject->offsetSet( ':location:admin-2', $theData[ 'DISTRICT' ] );
		}
		
		//
		// Set blocks.
		//
		if( array_key_exists( 'BLOCKS', $theData ) )
		{
			$theObject->offsetSet( 'abdh:BLOCKS', $theData[ 'BLOCKS' ] );
			$theObject->offsetSet( ':location:admin-3', $theData[ 'BLOCKS' ] );
		}
		
		//
		// Set village.
		//
		if( array_key_exists( 'VILLAGE', $theData ) )
		{
			$theObject->offsetSet( 'abdh:VILLAGE', $theData[ 'VILLAGE' ] );
			$theObject->offsetSet( ':location:locality', $theData[ 'VILLAGE' ] );
		}
		
		//
		// Set pin.
		//
		if( array_key_exists( 'PIN', $theData ) )
			$theObject->offsetSet( 'abdh:PIN', $theData[ 'PIN' ] );
		
		//
		// Set landscape.
		//
		if( array_key_exists( 'LANDSCAPE', $theData ) )
			$theObject->offsetSet( 'abdh:LANDSCAPE',
								   'abdh:LANDSCAPE:'.$theData[ 'LANDSCAPE' ] );
		
		//
		// Load respondents data.
		//
		$sub = Array();
		loadRespondent(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( 'abdh:interview', $sub );
		
		//
		// Init species.
		//
		$tmp = Array();
		
		//
		// Load annual species data.
		//
		$sub = Array();
		loadSpeciesAnnual( $sub,
						   $theData,
						   $theWrapper,
						   $theDatabase );
		if( count( $sub ) )
		{
			foreach( $sub as $element )
				$tmp[] = $element;
		}
		
		//
		// Load perennial species data.
		//
		$sub = Array();
		loadSpeciesPerennial( $sub,
							  $theData,
							  $theWrapper,
							  $theDatabase );
		if( count( $sub ) )
		{
			foreach( $sub as $element )
				$tmp[] = $element;
		}
		
		//
		// Load wild and semi-wild species data.
		//
		$sub = Array();
		loadSpeciesWild( $sub,
						 $theData,
						 $theWrapper,
						 $theDatabase );
		if( count( $sub ) )
		{
			foreach( $sub as $element )
				$tmp[] = $element;
		}
		
		//
		// Load animal species data.
		//
		$sub = Array();
		loadSpeciesAnimal( $sub,
						   $theData,
						   $theWrapper,
						   $theDatabase );
		if( count( $sub ) )
		{
			foreach( $sub as $element )
				$tmp[] = $element;
		}
		
		//
		// Load species.
		//
		if( count( $tmp ) )
			$theObject->offsetSet( 'abdh:species', $tmp );
		
		//
		// Load socio-economic data.
		//
		$sub = Array();
		loadEconomy( $sub,
					 $theData,
					 $theWrapper,
					 $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( 'abdh:economy', $sub );

	} // loadUnit.
	

	/**
	 * Load respondent data.
	 *
	 * This function will load the responsents data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadRespondent( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		$identifier = $theUnit[ 'ID_HOUSEHOLD' ];
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `abdh_respondent` "
									."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
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
				// Set enumerator identifier.
				//
				if( array_key_exists( 'ID_ENUMERATOR', $data ) )
					$sub[ getTag( 'abdh:ID_ENUMERATOR' ) ] = $data[ 'ID_ENUMERATOR' ];
			
				//
				// Set enumerator.
				//
				if( array_key_exists( 'ENUMERATOR', $data ) )
					$sub[ getTag( 'abdh:ENUMERATOR' ) ] = $data[ 'ENUMERATOR' ];
			
				//
				// Set household head name.
				//
				if( array_key_exists( 'NOM_HHH', $data ) )
					$sub[ getTag( 'abdh:NOM_HHH' ) ] = $data[ 'NOM_HHH' ];
			
				//
				// Set household head gender.
				//
				setEnum( $sub, $data, 'GENDER_HHH', 'abdh:GENDER_HHH' );
			
				//
				// Set household head education years.
				//
				if( array_key_exists( 'EDUC_HHH', $data ) )
					$sub[ getTag( 'abdh:EDUC_HHH' ) ]
						= (int) $data[ 'EDUC_HHH' ];
			
				//
				// Set household head education notes.
				//
				if( array_key_exists( 'EDUC_HHH_NOTES', $data ) )
					$sub[ getTag( 'abdh:EDUC_HHH_NOTES' ) ]
						= $data[ 'EDUC_HHH_NOTES' ];
			
				//
				// Set household head age.
				//
				if( array_key_exists( 'AGE_HHH', $data ) )
					$sub[ getTag( 'abdh:AGE_HHH' ) ]
						= (int) $data[ 'AGE_HHH' ];
			
				//
				// Set household head marital status.
				//
				setEnum( $sub, $data, 'MARIT_STAT', 'abdh:MARIT_STAT' );
			
				//
				// Set household head spouse status.
				//
				setEnum( $sub, $data,
						 'SPOUSE_STAT', 'abdh:SPOUSE_STAT',
						 array( '1', '2', '3' ) );
			
				//
				// Set household head spouse education level.
				//
				if( array_key_exists( 'SPOUSE_EDUC', $data ) )
					$sub[ getTag( 'abdh:SPOUSE_EDUC' ) ]
						= (int) $data[ 'SPOUSE_EDUC' ];
			
				//
				// Set household head spouse education notes.
				//
				if( array_key_exists( 'SPOUSE_EDUC_NOTES', $data ) )
					$sub[ getTag( 'abdh:SPOUSE_EDUC_NOTES' ) ]
						= $data[ 'SPOUSE_EDUC_NOTES' ];
			
				//
				// Set respondent relation to head.
				//
				setEnum( $sub, $data,
						 'REL_RESP_HHH', 'abdh:REL_RESP_HHH' );
			
				//
				// Set date.
				//
				if( array_key_exists( 'DATE_INT', $data ) )
					$sub[ getTag( 'abdh:DATE_INT' ) ]
						= substr( $data[ 'DATE_INT' ], 0, 4 )
						 .substr( $data[ 'DATE_INT' ], 5, 2 )
						 .substr( $data[ 'DATE_INT' ], 8, 2 );
			
				//
				// Set latitude.
				//
				if( array_key_exists( 'LAT', $data ) )
				{
					$sub[ getTag( ':location:site:latitude:provided' ) ]
						= $data[ 'LAT' ];
					if( array_key_exists( 'LATITUDE', $data ) )
						$sub[ getTag( ':location:site:latitude' ) ]
							= (double) $data[ 'LATITUDE' ];
					if( array_key_exists( 'LAT_DEG', $data ) )
						$sub[ getTag( ':location:site:latitude:deg' ) ]
							= (int) $data[ 'LAT_DEG' ];
					if( array_key_exists( 'LAT_MIN', $data ) )
						$sub[ getTag( ':location:site:latitude:min' ) ]
							= (int) $data[ 'LAT_MIN' ];
					if( array_key_exists( 'LAT_SEC', $data ) )
						$sub[ getTag( ':location:site:latitude:sec' ) ]
							= (double) $data[ 'LAT_SEC' ];
					if( array_key_exists( 'LAT_HEM', $data ) )
						$sub[ getTag( ':location:site:latitude:hem' ) ]
							= $data[ 'LAT_HEM' ];
				}
			
				//
				// Set longitude.
				//
				if( array_key_exists( 'LONG', $data ) )
				{
					$sub[ getTag( ':location:site:longitude:provided' ) ]
						= $data[ 'LONG' ];
					if( array_key_exists( 'LONGITUDE', $data ) )
						$sub[ getTag( ':location:site:longitude' ) ]
							= (double) $data[ 'LONGITUDE' ];
					if( array_key_exists( 'LONG_DEG', $data ) )
						$sub[ getTag( ':location:site:longitude:deg' ) ]
							= (int) $data[ 'LONG_DEG' ];
					if( array_key_exists( 'LONG_MIN', $data ) )
						$sub[ getTag( ':location:site:longitude:min' ) ]
							= (int) $data[ 'LONG_MIN' ];
					if( array_key_exists( 'LONG_SEC', $data ) )
						$sub[ getTag( ':location:site:longitude:sec' ) ]
							= (double) $data[ 'LONG_SEC' ];
					if( array_key_exists( 'LONG_HEM', $data ) )
						$sub[ getTag( ':location:site:longitude:hem' ) ]
							= $data[ 'LONG_HEM' ];
				}
			
				//
				// Set elevation.
				//
				if( array_key_exists( 'ELEV', $data ) )
					$sub[ getTag( ':location:site:elevation' ) ]
						= (int) $data[ 'ELEV' ];
		
				//
				// Load record.
				//
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
			$rs = $theDatabase->execute( "SELECT * FROM `abdh_respondent` "
										."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadRespondent.
	

	/**
	 * Load annual species data.
	 *
	 * This function will load the annual species data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Each species record will be loaded as an element of the provided container, this
	 * means that all different types of species will be treated at the same level.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSpeciesAnnual( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		$identifier = $theUnit[ 'ID_HOUSEHOLD' ];
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `abdh_annual_plants` "
									."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
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
				// Set species category.
				//
				$sub[ getTag( 'abdh:SPECIES_CAT' ) ]
					= 'abdh:SPECIES_CAT:1';
			
				//
				// Set year.
				//
				if( array_key_exists( 'YEAR', $data ) )
					$sub[ getTag( 'abdh:YEAR' ) ] = (int) $data[ 'YEAR' ];
				
				//
				// Set species sequential number.
				//
				if( array_key_exists( 'NUM_SPECIES', $data ) )
					$sub[ getTag( 'abdh:NUM_SPECIES' ) ] = $data[ 'NUM_SPECIES' ];
				
				//
				// Init species vernacular names.
				//
				$tmp = Array();
				
				//
				// Set species local name.
				//
				if( array_key_exists( 'NAME_LOC', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_LOC' ) ] = $data[ 'NAME_LOC' ];
					$tmp[] = array( kTAG_TEXT => array( $data[ 'NAME_LOC' ] ) );
				}
				
				//
				// Set species english name.
				//
				if( array_key_exists( 'NAME_ENG', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_ENG' ) ] = $data[ 'NAME_ENG' ];
					$tmp[] = array( kTAG_LANGUAGE => 'en',
									kTAG_TEXT => array( $data[ 'NAME_ENG' ] ) );
				}
				
				//
				// Set taxon names.
				//
				if( count( $tmp ) )
					$sub[ getTag( ':taxon:names' ) ] = $tmp;
				
				//
				// Set scientific name.
				//
				if( array_key_exists( 'NAME_SCIENT', $data ) )
				{
					$taxon = $data[ 'NAME_SCIENT' ];
					
					$sub[ getTag( ':taxon:epithet' ) ] = $taxon;
					
					$pos = strpos( $taxon, ' ' );
					if( $pos !== FALSE )
					{
						$genus = substr( $taxon, 0, $pos );
						$species = substr( $taxon, $pos + 1 );
					}
					else
					{
						$genus = $taxon;
						$species = NULL;
					}
				
					//
					// Set genus and species.
					//
					if( strlen( $genus ) )
						$sub[ getTag( ':taxon:genus' ) ] = $genus;
					if( strlen( $species ) )
						$sub[ getTag( ':taxon:species' ) ] = $species;
				}
				
				//
				// Set where was species grown.
				//
				setEnumSet( $sub, $data,
							'Q2.1a', 'abdh:Q1a',
							Array(),
							array( '0' ) );
				
				//
				// Set which season species grown.
				//
				setEnumSet( $sub, $data,
							'Q2.2a', 'abdh:Q2.2a',
							Array(),
							array( '0' ) );
				
				//
				// Set where was species grown.
				//
				setEnumSet( $sub, $data,
							'Q2.3a', 'abdh:Q2a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.3b', 'abdh:Q2a',
							Array(),
							array( '0' ) );
				// No data for abdh:Q2b.
				
				//
				// Cropping practice.
				//
				setEnum( $sub, $data,
						 'Q2.4a', 'abdh:Q2.4a',
						 Array(),
						 array( '0' ) );
				
				//
				// Cropping area.
				//
				if( array_key_exists( 'Q2.4b', $data ) )
					$sub[ getTag( 'abdh:Q2.4b' ) ]
						= (int) $data[ 'Q2.4b' ];
				
				//
				// Objectives of species production.
				//
				setEnum( $sub, $data,
						 'Q2.5', 'abdh:Q3',
						 array( '1', '2', '3' ) );
				
				//
				// Contribution to consumption.
				//
				setEnum( $sub, $data,
						 'Q2.6', 'abdh:Q2.6' );
				
				//
				// Contribution to income.
				//
				setEnum( $sub, $data,
						 'Q2.7', 'abdh:Q2.7' );
				
				//
				// Plant parts used.
				//
				setEnumSet( $sub, $data,
							'Q2.8a', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.8b', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.8c', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.8d', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.8e', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				// No data for abdh:Q4b.
				
				//
				// Plant specific used.
				//
				setEnumSet( $sub, $data,
							'Q2.9a', 'abdh:Q5a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.9b', 'abdh:Q5a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.9c', 'abdh:Q5a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.9d', 'abdh:Q5a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.9e', 'abdh:Q5a',
							Array(),
							array( '0' ) );
				// No data for abdh:Q5b.
				
				//
				// Source of seed.
				//
				setEnum( $sub, $data,
						 'Q2.10', 'abdh:Q2.10',
						 array('1', '2' ) );
				
				//
				// Seed obtained by who.
				//
				setEnumSet( $sub, $data,
							'Q2.11a', 'abdh:Q2.11a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.11b', 'abdh:Q2.11a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.11c', 'abdh:Q2.11a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.11d', 'abdh:Q2.11a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.11e', 'abdh:Q2.11a',
							Array(),
							array( '0' ) );
				// No data for abdh:11b.
				
				//
				// Source of seed outside of farm.
				//
				setEnum( $sub, $data,
						 'Q2.12', 'abdh:Q2.12',
						 Array(),
						 array( '0', '5' ) );
				
				//
				// Seed transactions.
				//
				setEnumSet( $sub, $data,
							'Q2.13a', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.13b', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.13c', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.13d', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q2.13e', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				// No data for abdh:Q6b.
				
				//
				// Seed to other farmers.
				//
				setEnum( $sub, $data,
						 'Q2.14', 'abdh:Q2.14',
						 array('0', '1' ) );
				
				//
				// Seed renewal.
				//
				setEnum( $sub, $data,
						 'Q2.15a', 'abdh:Q2.15a',
						 Array(),
						 array( '0' ) );
				// No data for abdh:Q2.15b.
				
				//
				// Varieties planted.
				//
				if( array_key_exists( 'Q2.16', $data ) )
					$sub[ getTag( 'abdh:Q2.16' ) ]
						= (int) $data[ 'Q2.16' ];
				
				//
				// Varieties desi.
				//
				if( array_key_exists( 'Q2.17', $data ) )
					$sub[ getTag( 'abdh:Q2.17' ) ]
						= (int) $data[ 'Q2.17' ];
				
				//
				// Varieties hybrid.
				//
				if( array_key_exists( 'Q2.18', $data ) )
					$sub[ getTag( 'abdh:Q2.18' ) ]
						= (int) $data[ 'Q2.18' ];
				
				//
				// Want other varieties.
				//
				setEnum( $sub, $data,
						 'Q2.19', 'abdh:Q2.19',
						 array('0', '1' ) );
				
				//
				// If yes what types?
				//
				setEnum( $sub, $data,
						 'Q2.20', 'abdh:Q2.20',
						 array( '1', '2', '3' ) );
				
				//
				// Who takes care of the species?
				//
				setEnumSet( $sub, $data,
							'Q2.21a', 'abdh:Q7a',
							Array(),
							array( '0' ) );
				if( array_key_exists( 'Q2.21b', $data ) )
					$sub[ getTag( 'abdh:Q7b' ) ]
						= array( $data[ 'Q2.21b' ] );
				
				//
				// Who takes decisions about seed planted?
				//
				setEnumSet( $sub, $data,
							'Q2.22a', 'abdh:Q2.22a',
							Array(),
							array( '0' ) );
				if( array_key_exists( 'Q2.22b', $data ) )
					$sub[ getTag( 'abdh:Q2.22b' ) ]
						= array( $data[ 'Q2.22b' ] );
				
				//
				// Who takes decisions about management?
				//
				setEnumSet( $sub, $data,
							'Q2.23a', 'abdh:Q8a',
							Array(),
							array( '0' ) );
				if( array_key_exists( 'Q2.23b', $data ) )
					$sub[ getTag( 'abdh:Q8b' ) ]
						= array( $data[ 'Q2.23b' ] );
				
				//
				// Who takes decisions about consumption?
				//
				setEnumSet( $sub, $data,
							'Q2.24a', 'abdh:Q2.24a',
							Array(),
							array( '0' ) );
				if( array_key_exists( 'Q2.24b', $data ) )
					$sub[ getTag( 'abdh:Q2.24b' ) ]
						= array( $data[ 'Q2.24b' ] );
				
				//
				// Who takes decisions about marketing?
				//
				setEnumSet( $sub, $data,
							'Q2.25a', 'abdh:Q2.25a',
							Array(),
							array( '0' ) );
				if( array_key_exists( 'Q2.25b', $data ) )
					$sub[ getTag( 'abdh:Q2.25b' ) ]
						= array( $data[ 'Q2.25b' ] );
		
				//
				// Load record.
				//
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
			$rs = $theDatabase->execute( "SELECT * FROM `abdh_annual_plants` "
										."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadSpeciesAnnual.
	

	/**
	 * Load perennial species data.
	 *
	 * This function will load the perennial species data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Each species record will be loaded as an element of the provided container, this
	 * means that all different types of species will be treated at the same level.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSpeciesPerennial( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		$identifier = $theUnit[ 'ID_HOUSEHOLD' ];
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `abdh_perennial_plants` "
									."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
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
				// Set species category.
				//
				$sub[ getTag( 'abdh:SPECIES_CAT' ) ]
					= 'abdh:SPECIES_CAT:2';
			
				//
				// Set year.
				//
				if( array_key_exists( 'YEAR', $data ) )
					$sub[ getTag( 'abdh:YEAR' ) ] = (int) $data[ 'YEAR' ];
				
				//
				// Set species sequential number.
				//
				if( array_key_exists( 'NUM_SPECIES', $data ) )
					$sub[ getTag( 'abdh:NUM_SPECIES' ) ] = $data[ 'NUM_SPECIES' ];
				
				//
				// Init species vernacular names.
				//
				$tmp = Array();
				
				//
				// Set species local name.
				//
				if( array_key_exists( 'NAME_LOC', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_LOC' ) ] = $data[ 'NAME_LOC' ];
					$tmp[] = array( kTAG_TEXT => array( $data[ 'NAME_LOC' ] ) );
				}
				
				//
				// Set species english name.
				//
				if( array_key_exists( 'NAME_ENG', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_ENG' ) ] = $data[ 'NAME_ENG' ];
					$tmp[] = array( kTAG_LANGUAGE => 'en',
									kTAG_TEXT => array( $data[ 'NAME_ENG' ] ) );
				}
				
				//
				// Set taxon names.
				//
				if( count( $tmp ) )
					$sub[ getTag( ':taxon:names' ) ] = $tmp;
				
				//
				// Set scientific name.
				//
				if( array_key_exists( 'NAME_SCIENT', $data ) )
				{
					$taxon = $data[ 'NAME_SCIENT' ];
					
					$sub[ getTag( ':taxon:epithet' ) ] = $taxon;
					
					$pos = strpos( $taxon, ' ' );
					if( $pos !== FALSE )
					{
						$genus = substr( $taxon, 0, $pos );
						$species = substr( $taxon, $pos + 1 );
					}
					else
					{
						$genus = $taxon;
						$species = NULL;
					}
				
					//
					// Set genus and species.
					//
					if( strlen( $genus ) )
						$sub[ getTag( ':taxon:genus' ) ] = $genus;
					if( strlen( $species ) )
						$sub[ getTag( ':taxon:species' ) ] = $species;
				}
				
				//
				// Set where was species grown.
				//
				setEnumSet( $sub, $data,
							'Q3.1a', 'abdh:Q1a',
							Array(),
							array( '0' ) );
				
				//
				// Set grown water conditions.
				//
				setEnumSet( $sub, $data,
							'Q3.2a', 'abdh:Q2a',
							Array(),
							array( '0' ) );
				
				//
				// Set harvested?
				//
				setEnum( $sub, $data,
						 'Q3.3', 'abdh:Q3.3' );
				
				//
				// Objectives of species production.
				//
				setEnum( $sub, $data,
						 'Q3.4', 'abdh:Q3',
						 array( '1', '2', '3' ) );
				
				//
				// Plant parts used.
				//
				setEnumSet( $sub, $data,
							'Q3.5a', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.5b', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.5c', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.5d', 'abdh:Q4a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.5e', 'abdh:Q4a',
							array( '8', '9' ) );
				if( array_key_exists( 'Q3.5e', $data )
				 && ( ($data[ 'Q3.5e' ] == 'Gum')
				   || ($data[ 'Q3.5e' ] == 'PEELU') ) )
					$sub[ getTag( 'abdh:Q4b' ) ]
						= array( $data[ 'Q3.5e' ] );
				
				//
				// Plant specific uses.
				//
				setEnumSet( $sub, $data,
							'Q3.6a', 'abdh:Q5a',
							Array(),
							array( '0', '10' ) );
				setEnumSet( $sub, $data,
							'Q3.6b', 'abdh:Q5a',
							Array(),
							array( '0', '10' ) );
				setEnumSet( $sub, $data,
							'Q3.6c', 'abdh:Q5a',
							Array(),
							array( '0', '10' ) );
				setEnumSet( $sub, $data,
							'Q3.6d', 'abdh:Q5a',
							Array(),
							array( '0', '10' ) );
				setEnumSet( $sub, $data,
							'Q3.6e', 'abdh:Q5a',
							Array(),
							array( '0', '10' ) );
				// No data for abdh:Q5b.
				
				//
				// Trees/grasses area.
				//
				if( array_key_exists( 'Q3.7', $data ) )
					$sub[ getTag( 'abdh:Q3.7' ) ]
						= (int) $data[ 'Q3.7' ];
				
				//
				// Varieties grown.
				//
				if( array_key_exists( 'Q3.8', $data ) )
					$sub[ getTag( 'abdh:Q3.8' ) ]
						= (int) $data[ 'Q3.8' ];
				
				//
				// Local varieties count.
				//
				if( array_key_exists( 'Q3.9', $data ) )
					$sub[ getTag( 'abdh:Q3.9' ) ]
						= (int) $data[ 'Q3.9' ];
				
				//
				// Improved varieties count.
				//
				if( array_key_exists( 'Q3.10', $data ) )
					$sub[ getTag( 'abdh:Q3.10' ) ]
						= (int) $data[ 'Q3.10' ];
				
				//
				// How was variety obtained?
				//
				if( array_key_exists( 'Q3.11', $data ) )
					$sub[ getTag( 'abdh:Q3.11' ) ]
						= array( $data[ 'Q3.11' ] );
				
				//
				// Seed transactions.
				//
				setEnumSet( $sub, $data,
							'Q3.12a', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.12b', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.12c', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.12d', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				setEnumSet( $sub, $data,
							'Q3.12e', 'abdh:Q6a',
							Array(),
							array( '0' ) );
				// No data for abdh:Q6b.
				
				//
				// Planting material to other farmers.
				//
				setEnum( $sub, $data,
						 'Q3.13', 'abdh:Q3.13',
						 array('0', '1' ) );
				
				//
				// Who takes care of the species?
				//
				setEnumSet( $sub, $data,
							'Q3.14a', 'abdh:Q7a',
							Array(),
							array( '0' ) );
				// No data for Q2.14b.
				
				//
				// Who takes decisions about field management?
				//
				setEnumSet( $sub, $data,
							'Q3.15a', 'abdh:Q8a',
							Array(),
							array( '0' ) );
				// No data for Q2.15b.
				
				//
				// Who takes decisions about harvesting?
				//
				setEnumSet( $sub, $data,
							'Q3.16a', 'abdh:Q9a',
							Array(),
							array( '0' ) );
				// No data for Q2.16b.
				
				//
				// Who takes decisions about uses?
				//
				setEnumSet( $sub, $data,
							'Q3.17a', 'abdh:Q10a',
							Array(),
							array( '0' ) );
				// No data for Q2.17b.
				
				//
				// Who takes decisions about sale?
				//
				setEnumSet( $sub, $data,
							'Q3.18a', 'abdh:Q11a',
							Array(),
							array( '0' ) );
				// No data for Q2.18b.
		
				//
				// Load record.
				//
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
			$rs = $theDatabase->execute( "SELECT * FROM `abdh_perennial_plants` "
										."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadSpeciesPerennial.
	

	/**
	 * Load wild species data.
	 *
	 * This function will load the wild species data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Each species record will be loaded as an element of the provided container, this
	 * means that all different types of species will be treated at the same level.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSpeciesWild( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		$identifier = $theUnit[ 'ID_HOUSEHOLD' ];
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `abdh_wild_plants` "
									."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
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
				// Set species category.
				//
				$sub[ getTag( 'abdh:SPECIES_CAT' ) ]
					= 'abdh:SPECIES_CAT:3';
			
				//
				// Set year.
				//
				if( array_key_exists( 'YEAR', $data ) )
					$sub[ getTag( 'abdh:YEAR' ) ] = (int) $data[ 'YEAR' ];
				
				//
				// Set species sequential number.
				//
				if( array_key_exists( 'NUM_SPECIES', $data ) )
					$sub[ getTag( 'abdh:NUM_SPECIES' ) ] = $data[ 'NUM_SPECIES' ];
				
				//
				// Init species vernacular names.
				//
				$tmp = Array();
				
				//
				// Set species local name.
				//
				if( array_key_exists( 'NAME_LOC', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_LOC' ) ] = $data[ 'NAME_LOC' ];
					$tmp[] = array( kTAG_TEXT => array( $data[ 'NAME_LOC' ] ) );
				}
				
				//
				// Set species english name.
				//
				if( array_key_exists( 'NAME_ENG', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_ENG' ) ] = $data[ 'NAME_ENG' ];
					$tmp[] = array( kTAG_LANGUAGE => 'en',
									kTAG_TEXT => array( $data[ 'NAME_ENG' ] ) );
				}
				
				//
				// Set taxon names.
				//
				if( count( $tmp ) )
					$sub[ getTag( ':taxon:names' ) ] = $tmp;
				
				//
				// Set scientific name.
				//
				if( array_key_exists( 'NAME_SCIENT', $data ) )
				{
					$taxon = $data[ 'NAME_SCIENT' ];
					
					$sub[ getTag( ':taxon:epithet' ) ] = $taxon;
					
					$pos = strpos( $taxon, ' ' );
					if( $pos !== FALSE )
					{
						$genus = substr( $taxon, 0, $pos );
						$species = substr( $taxon, $pos + 1 );
					}
					else
					{
						$genus = $taxon;
						$species = NULL;
					}
				
					//
					// Set genus and species.
					//
					if( strlen( $genus ) )
						$sub[ getTag( ':taxon:genus' ) ] = $genus;
					if( strlen( $species ) )
						$sub[ getTag( ':taxon:species' ) ] = $species;
				}
				
				//
				// Set crop group.
				//
				setEnumSet( $sub, $data,
							'Q4.1', 'abdh:Q4.1' );
				
				//
				// Objectives of species production.
				//
				setEnum( $sub, $data,
						 'Q4.2a', 'abdh:Q3',
						 array( '1', '2', '3' ) );
				// No data for Q4.2b.
				// No data for Q4.2c.
		
				//
				// Load record.
				//
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
			$rs = $theDatabase->execute( "SELECT * FROM `abdh_wild_plants` "
										."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadSpeciesWild.
	

	/**
	 * Load animal species data.
	 *
	 * This function will load the animal species data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Each species record will be loaded as an element of the provided container, this
	 * means that all different types of species will be treated at the same level.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSpeciesAnimal( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		$identifier = $theUnit[ 'ID_HOUSEHOLD' ];
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `abdh_animals` "
									."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
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
				// Set species category.
				//
				$sub[ getTag( 'abdh:SPECIES_CAT' ) ]
					= 'abdh:SPECIES_CAT:4';
			
				//
				// Set year.
				//
				if( array_key_exists( 'YEAR', $data ) )
					$sub[ getTag( 'abdh:YEAR' ) ] = (int) $data[ 'YEAR' ];
				
				//
				// Set species sequential number.
				//
				if( array_key_exists( 'NUM_SPECIES', $data ) )
					$sub[ getTag( 'abdh:NUM_SPECIES' ) ] = $data[ 'NUM_SPECIES' ];
				
				//
				// Init species vernacular names.
				//
				$tmp = Array();
				
				//
				// Set species local name.
				//
				if( array_key_exists( 'NAME_LOC', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_LOC' ) ] = $data[ 'NAME_LOC' ];
					$tmp[] = array( kTAG_TEXT => array( $data[ 'NAME_LOC' ] ) );
				}
				
				//
				// Set species english name.
				//
				if( array_key_exists( 'NAME_ENG', $data ) )
				{
					$sub[ getTag( 'abdh:NAME_ENG' ) ] = $data[ 'NAME_ENG' ];
					$tmp[] = array( kTAG_LANGUAGE => 'en',
									kTAG_TEXT => array( $data[ 'NAME_ENG' ] ) );
				}
				
				//
				// Set taxon names.
				//
				if( count( $tmp ) )
					$sub[ getTag( ':taxon:names' ) ] = $tmp;
				
				//
				// Set scientific name.
				//
				if( array_key_exists( 'NAME_SCIENT', $data ) )
				{
					$taxon = $data[ 'NAME_SCIENT' ];
					
					$sub[ getTag( ':taxon:epithet' ) ] = $taxon;
					
					$pos = strpos( $taxon, ' ' );
					if( $pos !== FALSE )
					{
						$genus = substr( $taxon, 0, $pos );
						$species = substr( $taxon, $pos + 1 );
					}
					else
					{
						$genus = $taxon;
						$species = NULL;
					}
				
					//
					// Set genus and species.
					//
					if( strlen( $genus ) )
						$sub[ getTag( ':taxon:genus' ) ] = $genus;
					if( strlen( $species ) )
						$sub[ getTag( ':taxon:species' ) ] = $species;
				}
				
				//
				// Number of animals.
				//
				if( array_key_exists( 'Q5.1', $data ) )
					$sub[ getTag( 'abdh:Q5.1' ) ]
						= (int) $data[ 'Q5.1' ];
				
				//
				// Products derived.
				//
				if( array_key_exists( 'Q5.2a', $data ) )
				{
					$value = $data[ 'Q5.2a' ];
					setEnumSet( $sub, $data,
								'Q5.2a', 'abdh:Q5.2a',
								Array(),
								array( '0', 'AUNDA' ) );
					if( $value == 'AUNDA' )
					{
						$tag = getTag( 'abdh:Q5.2b' );
						if( ! array_key_exists( $tag, $sub ) )
							$sub[ $tag ] = Array();
						if( ! in_array( $value, $sub[ $tag ] ) )
							$sub[ $tag ][] = $value;
					}
				}
				if( array_key_exists( 'Q5.2b', $data ) )
				{
					$value = $data[ 'Q5.2b' ];
					setEnumSet( $sub, $data,
								'Q5.2b', 'abdh:Q5.2a',
								Array(),
								array( '0', 'EGG' ) );
					if( $value == 'EGG' )
					{
						$tag = getTag( 'abdh:Q5.2b' );
						if( ! array_key_exists( $tag, $sub ) )
							$sub[ $tag ] = Array();
						if( ! in_array( $value, $sub[ $tag ] ) )
							$sub[ $tag ][] = $value;
					}
				}
				if( array_key_exists( 'Q5.2c', $data ) )
				{
					$value = $data[ 'Q5.2c' ];
					setEnumSet( $sub, $data,
								'Q5.2c', 'abdh:Q5.2a',
								Array(),
								array( '0' ) );
				}
				// No data for Q5.2d.
				// No data for Q5.2e.
				
				//
				// Objectives of species production.
				//
				setEnumSet( $sub, $data,
							'Q5.3a', 'abdh:Q5.3a',
							array( '1', '2', '3', '4' ) );
				setEnumSet( $sub, $data,
							'Q5.3b', 'abdh:Q5.3a',
							array( '1', '2', '3', '4' ) );
				
				//
				// Number of breeds.
				//
				if( array_key_exists( 'Q5.4', $data ) )
					$sub[ getTag( 'abdh:Q5.4' ) ]
						= (int) $data[ 'Q5.4' ];
				
				//
				// Number of local breeds.
				//
				if( array_key_exists( 'Q5.5', $data ) )
					$sub[ getTag( 'abdh:Q5.5' ) ]
						= (int) $data[ 'Q5.5' ];
				
				//
				// Number of improved breeds.
				//
				if( array_key_exists( 'Q5.6', $data ) )
					$sub[ getTag( 'abdh:Q5.6' ) ]
						= (int) $data[ 'Q5.6' ];
				
				//
				// Number of local and improved breeds.
				//
				if( array_key_exists( 'Q5.7', $data ) )
					$sub[ getTag( 'abdh:Q5.7' ) ]
						= (int) $data[ 'Q5.7' ];
				
				//
				// Number of mixture breeds.
				//
				if( array_key_exists( 'Q5.8', $data ) )
					$sub[ getTag( 'abdh:Q5.8' ) ]
						= (int) $data[ 'Q5.8' ];
				
				//
				// Number of purchased.
				//
				if( array_key_exists( 'Q5.9', $data ) )
					$sub[ getTag( 'abdh:Q5.9' ) ]
						= (int) $data[ 'Q5.9' ];
				
				//
				// Number of sold.
				//
				if( array_key_exists( 'Q5.10', $data ) )
					$sub[ getTag( 'abdh:Q5.10' ) ]
						= (int) $data[ 'Q5.10' ];
				
				//
				// Sold to whom.
				//
				if( array_key_exists( 'Q5.11', $data ) )
				{
					if( ($data[ 'Q5.11' ] != '0')
					 && ($data[ 'Q5.11' ] != '1') )
						$sub[ getTag( 'abdh:Q5.11' ) ]
							= array( $data[ 'Q5.11' ] );
				}
				
				//
				// Sold where.
				//
				if( array_key_exists( 'Q5.12', $data ) )
				{
					if( ($data[ 'Q5.12' ] != '0')
					 && ($data[ 'Q5.12' ] != '1')
					 && ($data[ 'Q5.12' ] != '6') )
						$sub[ getTag( 'abdh:Q5.12' ) ]
							= array( $data[ 'Q5.12' ] );
				}
				
				//
				// Participation in fairs.
				//
				setEnum( $sub, $data,
						 'Q5.13', 'abdh:Q5.13',
						 array( '0', '1' ) );
				
				//
				// Who takes care of the species?
				//
				setEnumSet( $sub, $data,
							'Q5.14a', 'abdh:Q7a',
							Array(),
							array( '0' ) );
				// No data for Q2.14b.
				
				//
				// Who takes decisions about field management?
				//
				setEnumSet( $sub, $data,
							'Q5.15a', 'abdh:Q8a' );
				// No data for Q2.15b.
				
				//
				// Who takes decisions about harvesting?
				//
				setEnumSet( $sub, $data,
							'Q5.16a', 'abdh:Q9a',
							Array(),
							array( '0' ) );
				// No data for Q2.16b.
				
				//
				// Who takes decisions about uses?
				//
				setEnumSet( $sub, $data,
							'Q5.17a', 'abdh:Q10a',
							Array(),
							array( '0' ) );
				// No data for Q2.17b.
				
				//
				// Who takes decisions about sale?
				//
				setEnumSet( $sub, $data,
							'Q5.18a', 'abdh:Q11a',
							Array(),
							array( '0' ) );
				// No data for Q2.18b.
		
				//
				// Load record.
				//
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
			$rs = $theDatabase->execute( "SELECT * FROM `abdh_animals` "
										."WHERE( `ID_HOUSEHOLD` = '$identifier' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadSpeciesAnimal.
	

	/**
	 * Load economic data.
	 *
	 * This function will load the economic data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadEconomy( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$identifier = $theUnit[ 'ID_HOUSEHOLD' ];
		
		//
		// Select respondents.
		//
		$record = $theDatabase->GetRow( "SELECT * FROM `abdh_economic` "
									   ."WHERE( `ID_HOUSEHOLD` = '$identifier' )" );
		if( count( $record ) )
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
			if( count( $data ) )
			{
				//
				// Load family size data.
				//
				$sub = Array();
				loadEconomyFamily( $sub,
								   $data,
								   $theWrapper,
								   $theDatabase );
				if( count( $sub ) )
					$theContainer[ getTag( 'abdh:economy:family' ) ]
						= $sub;
				
				//
				// Load owned land data.
				//
				$sub = Array();
				loadEconomyLand( $sub,
								 $data,
								 $theWrapper,
								 $theDatabase );
				if( count( $sub ) )
					$theContainer[ getTag( 'abdh:economy:land' ) ]
						= $sub;
				
				//
				// Load other owned land data.
				//
				$sub = Array();
				loadEconomyOtherLand( $sub,
									  $data,
									  $theWrapper,
									  $theDatabase );
				if( count( $sub ) )
					$theContainer[ getTag( 'abdh:economy:land-other' ) ]
						= $sub;
				
				//
				// Load water resources data.
				//
				$sub = Array();
				loadEconomyWater( $sub,
								  $data,
								  $theWrapper,
								  $theDatabase );
				if( count( $sub ) )
					$theContainer[ getTag( 'abdh:economy:water' ) ]
						= $sub;
				
				//
				// Load housing data.
				//
				$sub = Array();
				loadEconomyHousing( $sub,
									$data,
									$theWrapper,
									$theDatabase );
				if( count( $sub ) )
					$theContainer[ getTag( 'abdh:economy:housing' ) ]
						= $sub;
			}
		}

	} // loadEconomy.
	

	/**
	 * Load family size data.
	 *
	 * This function will load the family size data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * Note that the container is a non-list structure and the provided record holds the
	 * actual data.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadEconomyFamily( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set male members less than 6 months old.
		//
		if( array_key_exists( 'M_6MONTHS', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_6MONTHS' ) ]
				= (int) $theUnit[ 'M_6MONTHS' ];

		//
		// Set male members between 5 and 59 months old.
		//
		if( array_key_exists( 'M_6_59MONTHS', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_6_59MONTHS' ) ]
				= (int) $theUnit[ 'M_6_59MONTHS' ];

		//
		// Set male members between 5 and 6 years old.
		//
		if( array_key_exists( 'M_5_6YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_5_6YEARS' ) ]
				= (int) $theUnit[ 'M_5_6YEARS' ];

		//
		// Set male members between 7 and 9 years old.
		//
		if( array_key_exists( 'M_7_9YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_7_9YEARS' ) ]
				= (int) $theUnit[ 'M_7_9YEARS' ];

		//
		// Set male members between 10 and 15 years old.
		//
		if( array_key_exists( 'M_10_15YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_10_15YEARS' ) ]
				= (int) $theUnit[ 'M_10_15YEARS' ];

		//
		// Set male members between 16 and 60 years old.
		//
		if( array_key_exists( 'M_16_60YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_16_60YEARS' ) ]
				= (int) $theUnit[ 'M_16_60YEARS' ];

		//
		// Set male members more than 60 years old.
		//
		if( array_key_exists( 'M_60MORE', $theUnit ) )
			$theContainer[ getTag( 'abdh:M_60MORE' ) ]
				= (int) $theUnit[ 'M_60MORE' ];

		//
		// Set female members less than 6 months old.
		//
		if( array_key_exists( 'F_6MONTHS', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_6MONTHS' ) ]
				= (int) $theUnit[ 'F_6MONTHS' ];

		//
		// Set female members between 5 and 59 months old.
		//
		if( array_key_exists( 'F_6_59MONTHS', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_6_59MONTHS' ) ]
				= (int) $theUnit[ 'F_6_59MONTHS' ];

		//
		// Set female members between 5 and 6 years old.
		//
		if( array_key_exists( 'F_5_6YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_5_6YEARS' ) ]
				= (int) $theUnit[ 'F_5_6YEARS' ];

		//
		// Set female members between 7 and 9 years old.
		//
		if( array_key_exists( 'F_7_9YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_7_9YEARS' ) ]
				= (int) $theUnit[ 'F_7_9YEARS' ];

		//
		// Set female members between 10 and 15 years old.
		//
		if( array_key_exists( 'F_10_15YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_10_15YEARS' ) ]
				= (int) $theUnit[ 'F_10_15YEARS' ];

		//
		// Set female members between 16 and 60 years old.
		//
		if( array_key_exists( 'F_16_60YEARS', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_16_60YEARS' ) ]
				= (int) $theUnit[ 'F_16_60YEARS' ];

		//
		// Set female members more than 60 years old.
		//
		if( array_key_exists( 'F_60MORE', $theUnit ) )
			$theContainer[ getTag( 'abdh:F_60MORE' ) ]
				= (int) $theUnit[ 'F_60MORE' ];

	} // loadEconomyFamily.
	

	/**
	 * Load owned land data.
	 *
	 * This function will load the owned land data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * Note that the container is a non-list structure and the provided record holds the
	 * actual data.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadEconomyLand( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set husband owned land.
		//
		if( array_key_exists( 'HUSB_OWN', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_OWN' ) ]
				= (double) $theUnit[ 'HUSB_OWN' ];

		//
		// Set husband land shared in.
		//
		if( array_key_exists( 'HUSB_SH_IN', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_SH_IN' ) ]
				= (double) $theUnit[ 'HUSB_SH_IN' ];

		//
		// Set husband land shared out.
		//
		if( array_key_exists( 'HUSB_SH_OUT', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_SH_OUT' ) ]
				= (double) $theUnit[ 'HUSB_SH_OUT' ];

		//
		// Set husband land rented in.
		//
		if( array_key_exists( 'HUSB_RENT_IN', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_RENT_IN' ) ]
				= (double) $theUnit[ 'HUSB_RENT_IN' ];

		//
		// Set husband land rented out.
		//
		if( array_key_exists( 'HUSB_RENT_OUT', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_RENT_OUT' ) ]
				= (double) $theUnit[ 'HUSB_RENT_OUT' ];

		//
		// Set husband total land cultivated.
		//
		if( array_key_exists( 'HUSB_TOT_CULT', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_TOT_CULT' ) ]
				= (double) $theUnit[ 'HUSB_TOT_CULT' ];

		//
		// Set husband total land cultivated (ha.).
		//
		if( array_key_exists( 'HUSB_TOT_CULT1', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_TOT_CULT1' ) ]
				= (double) $theUnit[ 'HUSB_TOT_CULT1' ];

		//
		// Set wife owned land.
		//
		if( array_key_exists( 'SPO_OWN', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_OWN' ) ]
				= (double) $theUnit[ 'SPO_OWN' ];

		//
		// Set wife land shared in.
		//
		if( array_key_exists( 'SPO_SH_IN', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_SH_IN' ) ]
				= (double) $theUnit[ 'SPO_SH_IN' ];

		//
		// Set wife land shared out.
		//
		if( array_key_exists( 'SPO_SH_OUT', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_SH_OUT' ) ]
				= (double) $theUnit[ 'SPO_SH_OUT' ];

		//
		// Set wife land rented in.
		//
		if( array_key_exists( 'SPO_RENT_IN', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_RENT_IN' ) ]
				= (double) $theUnit[ 'SPO_RENT_IN' ];

		//
		// Set wife land rented out.
		//
		if( array_key_exists( 'SPO_RENT_OUT', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_RENT_OUT' ) ]
				= (double) $theUnit[ 'SPO_RENT_OUT' ];

		//
		// Set wife total land cultivated.
		//
		if( array_key_exists( 'SPO_TOT_CULT', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_TOT_CULT' ) ]
				= (double) $theUnit[ 'SPO_TOT_CULT' ];

		//
		// Set wife total land cultivated (ha.).
		//
		if( array_key_exists( 'SPO_TOT_CULT1', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_TOT_CULT1' ) ]
				= (double) $theUnit[ 'SPO_TOT_CULT1' ];

		//
		// Set joint owned land.
		//
		if( array_key_exists( 'JOINT_OWN', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_OWN' ) ]
				= (double) $theUnit[ 'JOINT_OWN' ];

		//
		// Set joint land shared in.
		//
		if( array_key_exists( 'JOINT_SH_IN', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_SH_IN' ) ]
				= (double) $theUnit[ 'JOINT_SH_IN' ];

		//
		// Set joint land shared out.
		//
		if( array_key_exists( 'JOINT_SH_OUT', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_SH_OUT' ) ]
				= (double) $theUnit[ 'JOINT_SH_OUT' ];

		//
		// Set joint land rented in.
		//
		if( array_key_exists( 'JOINT_RENT_IN', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_RENT_IN' ) ]
				= (double) $theUnit[ 'JOINT_RENT_IN' ];

		//
		// Set joint land rented out.
		//
		if( array_key_exists( 'JOINT_RENT_OUT', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_RENT_OUT' ) ]
				= (double) $theUnit[ 'JOINT_RENT_OUT' ];

		//
		// Set joint total land cultivated.
		//
		if( array_key_exists( 'JOINT_TOT_CULT', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_TOT_CULT' ) ]
				= (double) $theUnit[ 'JOINT_TOT_CULT' ];

	} // loadEconomyLand.
	

	/**
	 * Load other owned land data.
	 *
	 * This function will load the other owned land data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Note that the container is a non-list structure and the provided record holds the
	 * actual data.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadEconomyOtherLand( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set husband fallow land.
		//
		if( array_key_exists( 'HUSB_FAL', $theUnit ) )
		{
			switch( $value = $theUnit[ 'HUSB_FAL' ] )
			{
				case '10 BEEGA':
					$theContainer[ getTag( 'abdh:HUSB_FAL' ) ]
						= (double) 10;
					break;
				case '90 BEEGA':
					$theContainer[ getTag( 'abdh:HUSB_FAL' ) ]
						= (double) 90;
					break;
				default:
					$theContainer[ getTag( 'abdh:HUSB_FAL' ) ]
						= (double) $value;
					break;
			}
		}

		//
		// Set husband waste land.
		//
		if( array_key_exists( 'HUSB_WASTE', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_WASTE' ) ]
				= (double) $theUnit[ 'HUSB_WASTE' ];

		//
		// Set husband fallow land.
		//
		if( array_key_exists( 'HUSB_GRAZING', $theUnit ) )
		{
			switch( $value = $theUnit[ 'HUSB_GRAZING' ] )
			{
				case '5 BEEGA':
					$theContainer[ getTag( 'abdh:HUSB_GRAZING' ) ]
						= (double) 5;
					break;
				default:
					$theContainer[ getTag( 'abdh:HUSB_GRAZING' ) ]
						= (double) $value;
					break;
			}
		}

		//
		// Set husband tree land.
		//
		if( array_key_exists( 'HUSB_TREE', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_TREE' ) ]
				= (double) $theUnit[ 'HUSB_TREE' ];

		//
		// Set husband other land.
		//
		if( array_key_exists( 'HUSB_OTHERS', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_OTHERS' ) ]
				= (double) $theUnit[ 'HUSB_OTHERS' ];

		//
		// Set husband total uncultivated land.
		//
		if( array_key_exists( 'HUSB_TOT_UNCULT', $theUnit ) )
			$theContainer[ getTag( 'abdh:HUSB_TOT_UNCULT' ) ]
				= (double) $theUnit[ 'HUSB_TOT_UNCULT' ];

		//
		// Set wife fallow land.
		//
		if( array_key_exists( 'SPO_FAL', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_FAL' ) ]
				= (double) $theUnit[ 'SPO_FAL' ];

		//
		// Set wife waste land.
		//
		if( array_key_exists( 'SPO_WASTE', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_WASTE' ) ]
				= (double) $theUnit[ 'SPO_WASTE' ];

		//
		// Set wife fallow land.
		//
		if( array_key_exists( 'SPO_GRAZING', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_GRAZING' ) ]
				= (double) $theUnit[ 'SPO_GRAZING' ];

		//
		// Set wife tree land.
		//
		if( array_key_exists( 'SPO_TREE', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_TREE' ) ]
				= (double) $theUnit[ 'SPO_TREE' ];

		//
		// Set wife other land.
		//
		if( array_key_exists( 'SPO_OTHERS', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_OTHERS' ) ]
				= (double) $theUnit[ 'SPO_OTHERS' ];

		//
		// Set wife total uncultivated land.
		//
		if( array_key_exists( 'SPO_TOT_UNCULT', $theUnit ) )
			$theContainer[ getTag( 'abdh:SPO_TOT_UNCULT' ) ]
				= (double) $theUnit[ 'SPO_TOT_UNCULT' ];

		//
		// Set joint fallow land.
		//
		if( array_key_exists( 'JOINT_FAL', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_FAL' ) ]
				= (double) $theUnit[ 'JOINT_FAL' ];

		//
		// Set joint waste land.
		//
		if( array_key_exists( 'JOINT_WASTE', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_WASTE' ) ]
				= (double) $theUnit[ 'JOINT_WASTE' ];

		//
		// Set joint fallow land.
		//
		if( array_key_exists( 'JOINT_GRAZING', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_GRAZING' ) ]
				= (double) $theUnit[ 'JOINT_GRAZING' ];

		//
		// Set joint tree land.
		//
		if( array_key_exists( 'JOINT_TREE', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_TREE' ) ]
				= (double) $theUnit[ 'JOINT_TREE' ];

		//
		// Set joint other land.
		//
		if( array_key_exists( 'JOINT_OTHERS', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_OTHERS' ) ]
				= (double) $theUnit[ 'JOINT_OTHERS' ];

		//
		// Set joint total uncultivated land.
		//
		if( array_key_exists( 'JOINT_TOT_UNCULT', $theUnit ) )
			$theContainer[ getTag( 'abdh:JOINT_TOT_UNCULT' ) ]
				= (double) $theUnit[ 'JOINT_TOT_UNCULT' ];

	} // loadEconomyOtherLand.
	

	/**
	 * Load water resources data.
	 *
	 * This function will load the water resources data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Note that the container is a non-list structure and the provided record holds the
	 * actual data.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadEconomyWater( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set number of household owned canals.
		//
		if( array_key_exists( 'CAN_PRIV', $theUnit ) )
			$theContainer[ getTag( 'abdh:CAN_PRIV' ) ]
				= (int) $theUnit[ 'CAN_PRIV' ];

		//
		// Set number of communally owned canals.
		//
		if( array_key_exists( 'CAN_COM', $theUnit ) )
			$theContainer[ getTag( 'abdh:CAN_COM' ) ]
				= (int) $theUnit[ 'CAN_COM' ];

		//
		// Set canals owner.
		//
		setEnum( $sub, $theUnit,
				 'CAN_WHO', 'abdh:CAN_WHO',
				 array( '1', '2', '3' ) );

		//
		// Set number of household owned open wells.
		//
		if( array_key_exists( 'OWELL_PRIV', $theUnit ) )
			$theContainer[ getTag( 'abdh:OWELL_PRIV' ) ]
				= (int) $theUnit[ 'OWELL_PRIV' ];

		//
		// Set number of communally owned open wells.
		//
		if( array_key_exists( 'OWELL_COM', $theUnit ) )
			$theContainer[ getTag( 'abdh:OWELL_COM' ) ]
				= (int) $theUnit[ 'OWELL_COM' ];

		//
		// Set open wells owner.
		//
		setEnum( $sub, $theUnit,
				 'OWELL_WHO', 'abdh:OWELL_WHO',
				 array( '1', '2', '3' ) );

		//
		// Set number of household owned bore wells.
		//
		if( array_key_exists( 'BWELL_PRIV', $theUnit ) )
			$theContainer[ getTag( 'abdh:BWELL_PRIV' ) ]
				= (int) $theUnit[ 'BWELL_PRIV' ];

		//
		// Set number of communally owned bore wells.
		//
		if( array_key_exists( 'BWELL_COM', $theUnit ) )
			$theContainer[ getTag( 'abdh:BWELL_COM' ) ]
				= (int) $theUnit[ 'BWELL_COM' ];

		//
		// Set bore wells owner.
		//
		setEnum( $sub, $theUnit,
				 'BWELL_WHO', 'abdh:BWELL_WHO',
				 array( '1', '2', '3' ) );

		//
		// Set number of household owned khadin.
		//
		if( array_key_exists( 'KHADIN_PRIV', $theUnit ) )
			$theContainer[ getTag( 'abdh:KHADIN_PRIV' ) ]
				= (int) $theUnit[ 'KHADIN_PRIV' ];

		//
		// Set number of communally owned khadin.
		//
		if( array_key_exists( 'KHADIN_COM', $theUnit ) )
			$theContainer[ getTag( 'abdh:KHADIN_COM' ) ]
				= (int) $theUnit[ 'KHADIN_COM' ];

		//
		// Set khadin owner.
		//
		setEnum( $sub, $theUnit,
				 'KHADIN_WHO', 'abdh:KHADIN_WHO',
				 array( '1', '2', '3' ) );

		//
		// Set number of household owned naadi.
		//
		if( array_key_exists( 'TAANKA_PRIV', $theUnit ) )
			$theContainer[ getTag( 'abdh:TAANKA_PRIV' ) ]
				= (int) $theUnit[ 'TAANKA_PRIV' ];

		//
		// Set number of communally owned naadi.
		//
		if( array_key_exists( 'TAANKA_COM', $theUnit ) )
			$theContainer[ getTag( 'abdh:TAANKA_COM' ) ]
				= (int) $theUnit[ 'TAANKA_COM' ];

		//
		// Set naadi owner.
		//
		setEnum( $sub, $theUnit,
				 'TAANKA_WHO', 'abdh:TAANKA_WHO',
				 array( '1', '2', '3' ) );
		
		// No mdata for GLR.

	} // loadEconomyWater.
	

	/**
	 * Load housing data.
	 *
	 * This function will load the housing data related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * Note that the container is a non-list structure and the provided record holds the
	 * actual data.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadEconomyHousing( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Set main residence floor.
		//
		setEnumSet( $sub, $theUnit,
					'Q6.1a', 'abdh:Q6.1a',
					Array(),
					array( '0' ) );
		setEnumSet( $sub, $theUnit,
					'Q6.1b', 'abdh:Q6.1a',
					Array(),
					array( '0' ) );
		setEnumSet( $sub, $theUnit,
					'Q6.1c', 'abdh:Q6.1a',
					Array(),
					array( '0' ) );
		// No data for abdh:Q6.1b.

		//
		// Set main residence wall.
		//
		setEnumSet( $sub, $theUnit,
					'Q6.2a', 'abdh:Q6.2a',
					Array(),
					array( '0' ) );
		setEnumSet( $sub, $theUnit,
					'Q6.2b', 'abdh:Q6.2a',
					Array(),
					array( '0' ) );

		//
		// Set main residence roof.
		//
		setEnumSet( $sub, $theUnit,
					'Q6.3a', 'abdh:Q6.3a',
					Array(),
					array( '0' ) );
		if( array_key_exists( 'Q6.3b', $theUnit ) )
		{
			$tag = getTag( 'abdh:Q6.3b' )
			switch( $value = $theUnit[ 'Q6.3b' ] )
			{
				case 'R,C;C':
					if( ! array_key_exists( $tag, $theContainer ) )
						$theContainer[ $tag ] = Array();
					if( ! array_key_exists( 'R,C', $theContainer[ $tag ] ) )
						$theContainer[ $tag ][] = 'R,C';
					if( ! array_key_exists( 'C', $theContainer[ $tag ] ) )
						$theContainer[ $tag ][] = 'C';
					break;
				case 'STONE':
					if( ! array_key_exists( $tag, $theContainer ) )
						$theContainer[ $tag ] = Array();
					if( ! array_key_exists( 'STONE', $theContainer[ $tag ] ) )
						$theContainer[ $tag ][] = 'STONE';
					break;
				default:
					setEnumSet( $sub, $theUnit,
								'Q6.3b', 'abdh:Q6.3a',
								Array(),
								array( '0' ) );
					break;
			}
		}

		//
		// Set electricity.
		//
		setEnum( $sub, $theUnit,
				 'Q6.4', 'abdh:Q6.4',
				 Array(),
				 array( '-1' ) );

		//
		// Set electricity source.
		//
		if( array_key_exists( 'Q6.5a', $theUnit ) )
		{
			$tag = getTag( 'abdh:Q6.5b' )
			switch( $value = $theUnit[ 'Q6.5a' ] )
			{
				case 'POWER COMPANY':
					if( ! array_key_exists( $tag, $theContainer ) )
						$theContainer[ $tag ] = Array();
					if( ! array_key_exists( 'POWER COMPANY', $theContainer[ $tag ] ) )
						$theContainer[ $tag ][] = 'POWER COMPANY';
					break;
				default:
					setEnumSet( $sub, $theUnit,
								'Q6.5a', 'abdh:Q6.5a',
								Array(),
								array( '0' ) );
					break;
			}
		}
		// No data for Q6.5b.

	} // loadEconomyHousing.
	

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
	

	/**
	 * Set enum.
	 *
	 * This function will set an enumeration according to the provided parameters.
	 *
	 * @param array					$theContainer		Destination container reference.
	 * @param array					$theRecord			Database record.
	 * @param string				$theField			Database field name.
	 * @param string				$theTag				Tag native identifier.
	 * @param array					$theSelections		Values to be considered.
	 * @param array					$theExceptions		Values to be skipped.
	 * @return int					Serial identifier.
	 */
	function setEnum( &$theContainer, $theRecord, $theField, $theTag,
									  $theSelections = Array(), $theExceptions = Array() )
	{
		//
		// Check field.
		//
		if( array_key_exists( $theField, $theRecord ) )
		{
			//
			// Skip excluded.
			//
			if( ! in_array( $theRecord[ $theField ], $theExceptions ) )
			{
				//
				// Select included.
				//
				if( (! count( $theSelections ))
				 || in_array( $theRecord[ $theField ], $theSelections ) )
					$theContainer[ getTag( $theTag ) ]
						= "$theTag:".$theRecord[ $theField ];
			}
		}

	} // setEnum.
	

	/**
	 * Set enum set.
	 *
	 * This function will set an enumerated set according to the provided parameters.
	 *
	 * @param array					$theContainer		Destination container reference.
	 * @param array					$theRecord			Database record.
	 * @param string				$theField			Database field name.
	 * @param string				$theTag				Tag native identifier.
	 * @param array					$theSelections		Values to be considered.
	 * @param array					$theExceptions		Values to be skipped.
	 * @return int					Serial identifier.
	 */
	function setEnumSet( &$theContainer, $theRecord, $theField, $theTag,
										 $theSelections = Array(),
										 $theExceptions = Array() )
	{
		//
		// Check field.
		//
		if( array_key_exists( $theField, $theRecord ) )
		{
			//
			// Skip excluded.
			//
			if( ! in_array( $theRecord[ $theField ], $theExceptions ) )
			{
				//
				// Select included.
				//
				if( (! count( $theSelections ))
				 || in_array( $theRecord[ $theField ], $theSelections ) )
				{
					//
					// Get tag serial.
					//
					$tag = getTag( $theTag );
					
					//
					// Init enumerated set container.
					//
					if( ! array_key_exists( $tag, $theContainer ) )
						$theContainer[ $tag ] = Array();
					
					//
					// Reference enumerated set container.
					//
					$ref = & $theContainer[ $tag ];
					
					//
					// Set enumerated value.
					//
					$value = "$theTag:".$theRecord[ $theField ];
					
					//
					// Set element.
					//
					if( ! in_array( $value, $ref ) )
						$ref[] = $value;
						
				}
			}
		}

	} // setEnumSet.

?>
