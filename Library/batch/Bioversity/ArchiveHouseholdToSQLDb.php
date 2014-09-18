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
$limit = 1000;
$page = 50;
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
		// Load annual species data.
		//
		$sub = Array();
		loadSpeciesAnnual(	$sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( 'abdh:species', $sub );

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
				if( array_key_exists( 'GENDER_HHH', $data ) )
					$sub[ getTag( 'abdh:GENDER_HHH' ) ]
						= 'abdh:GENDER_HHH:'.$data[ 'GENDER_HHH' ];
			
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
				if( array_key_exists( 'MARIT_STAT', $data ) )
					$sub[ getTag( 'abdh:MARIT_STAT' ) ]
						= 'abdh:MARIT_STAT:'.$data[ 'MARIT_STAT' ];
			
				//
				// Set household head spouse status.
				//
				if( array_key_exists( 'SPOUSE_STAT', $data )
				 && ( ($data[ 'SPOUSE_STAT' ] == '1')
				   || ($data[ 'SPOUSE_STAT' ] == '2')
				   || ($data[ 'SPOUSE_STAT' ] == '3') ) )
					$sub[ getTag( 'abdh:SPOUSE_STAT' ) ]
						= 'abdh:SPOUSE_STAT:'.$data[ 'SPOUSE_STAT' ];
			
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
				if( array_key_exists( 'REL_RESP_HHH', $data ) )
					$sub[ getTag( 'abdh:REL_RESP_HHH' ) ]
						= 'abdh:REL_RESP_HHH:'.$data[ 'REL_RESP_HHH' ];
			
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
				if( array_key_exists( 'Q2.1a', $data )
				 && ($data[ 'Q2.1a' ] != '0') )
				{
					$sub[ getTag( 'abdh:Q1a' ) ]
						= array( 'abdh:Q1a:'.$data[ 'Q2.1a' ] );
					// No data for abdh:Q1b.
				}
				
				//
				// Set which season species grown.
				//
				if( array_key_exists( 'Q2.2a', $data )
				 && ($data[ 'Q2.2a' ] != '0') )
				{
					$sub[ getTag( 'abdh:Q2.2a' ) ]
						= array( 'abdh:Q2.2a:'.$data[ 'Q2.2a' ] );
					// No data for abdh:Q2.2b.
				}
				
				//
				// Set where was species grown.
				//
				$tmp = Array();
				if( array_key_exists( 'Q2.3a', $data )
				 && ($data[ 'Q2.3a' ] != '0') )
				{
					$val = 'abdh:Q2a:'.$data[ 'Q2.3a' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.3b', $data )
				 && ($data[ 'Q2.3b' ] != '0') )
				{
					$val = 'abdh:Q2a:'.$data[ 'Q2.3b' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( count( $tmp ) )
					$sub[ getTag( 'abdh:Q2a' ) ] = $tmp;
				// No data for abdh:Q2b.
				
				//
				// Cropping practice.
				//
				if( array_key_exists( 'Q2.4a', $data )
				 && ($data[ 'Q2.4a' ] != '0') )
					$sub[ getTag( 'abdh:Q2.4a' ) ]
						= 'abdh:Q2.4a:'.$data[ 'Q2.4a' ];
				
				//
				// Cropping area.
				//
				if( array_key_exists( 'Q2.4b', $data ) )
					$sub[ getTag( 'abdh:Q2.4b' ) ]
						= (int) $data[ 'Q2.4b' ];
				
				//
				// Objectives of species production.
				//
				if( array_key_exists( 'Q2.5', $data )
				 && ( ($data[ 'Q2.5' ] == '1')
				   || ($data[ 'Q2.5' ] == '2')
				   || ($data[ 'Q2.5' ] == '3') ) )
					$sub[ getTag( 'abdh:Q3' ) ]
						= 'abdh:Q3:'.$data[ 'Q2.5' ];
				
				//
				// Contribution to consumption.
				//
				if( array_key_exists( 'Q2.6', $data ) )
					$sub[ getTag( 'abdh:Q2.6' ) ]
						= 'abdh:Q2.6:'.$data[ 'Q2.6' ];
				
				//
				// Contribution to income.
				//
				if( array_key_exists( 'Q2.7', $data ) )
					$sub[ getTag( 'abdh:Q2.7' ) ]
						= 'abdh:Q2.7:'.$data[ 'Q2.7' ];
				
				//
				// Plant parts used.
				//
				$tmp = Array();
				if( array_key_exists( 'Q2.8a', $data )
				 && ($data[ 'Q2.8a' ] != '0') )
				{
					$val = 'abdh:Q4a:'.$data[ 'Q2.8a' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.8b', $data )
				 && ($data[ 'Q2.8b' ] != '0') )
				{
					$val = 'abdh:Q4a:'.$data[ 'Q2.8b' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.8c', $data )
				 && ($data[ 'Q2.8c' ] != '0') )
				{
					$val = 'abdh:Q4a:'.$data[ 'Q2.8c' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.8d', $data )
				 && ($data[ 'Q2.8d' ] != '0') )
				{
					$val = 'abdh:Q4a:'.$data[ 'Q2.8d' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.8e', $data )
				 && ($data[ 'Q2.8e' ] != '0') )
				{
					$val = 'abdh:Q4a:'.$data[ 'Q2.8e' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( count( $tmp ) )
					$sub[ getTag( 'abdh:Q4a' ) ] = $tmp;
				// No data for abdh:Q4b.
				
				//
				// Plant specific used.
				//
				$tmp = Array();
				if( array_key_exists( 'Q2.9a', $data )
				 && ($data[ 'Q2.9a' ] != '0') )
				{
					$val = 'abdh:Q5a:'.$data[ 'Q2.9a' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.9b', $data )
				 && ($data[ 'Q2.9b' ] != '0') )
				{
					$val = 'abdh:Q5a:'.$data[ 'Q2.9b' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.9c', $data )
				 && ($data[ 'Q2.9c' ] != '0') )
				{
					$val = 'abdh:Q5a:'.$data[ 'Q2.9c' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.9d', $data )
				 && ($data[ 'Q2.9d' ] != '0') )
				{
					$val = 'abdh:Q5a:'.$data[ 'Q2.9d' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.9e', $data )
				 && ($data[ 'Q2.9e' ] != '0') )
				{
					$val = 'abdh:Q5a:'.$data[ 'Q2.9e' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( count( $tmp ) )
					$sub[ getTag( 'abdh:Q5a' ) ] = $tmp;
				// No data for abdh:Q4b.
				
				//
				// Source of seed.
				//
				if( array_key_exists( 'Q2.10', $data )
				 && ( ($data[ 'Q2.10' ] == '1')
				   || ($data[ 'Q2.10' ] == '2') ) )
					$sub[ getTag( 'abdh:Q2.10' ) ]
						= 'abdh:Q2.10:'.$data[ 'Q2.10' ];
				
				//
				// Seed obtained by who.
				//
				$tmp = Array();
				if( array_key_exists( 'Q2.11a', $data )
				 && ($data[ 'Q2.11a' ] != '0') )
				{
					$val = 'abdh:Q2.11a:'.$data[ 'Q2.11a' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.11b', $data ) )
				{
					$val = 'abdh:Q2.11a:'.$data[ 'Q2.11b' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.11c', $data ) )
				{
					$val = 'abdh:Q2.11a:'.$data[ 'Q2.11c' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.11d', $data ) )
				{
					$val = 'abdh:Q2.11a:'.$data[ 'Q2.11d' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.11e', $data ) )
				{
					$val = 'abdh:Q2.11a:'.$data[ 'Q2.11e' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( count( $tmp ) )
					$sub[ getTag( 'abdh:Q2.11a' ) ] = $tmp;
				// No data for abdh:11b.
				
				//
				// Source of seed outside of farm.
				//
				if( array_key_exists( 'Q2.12', $data )
				 && ($data[ 'Q2.12' ] != '0')
				 && ($data[ 'Q2.12' ] != '5') )
					$sub[ getTag( 'abdh:Q2.12' ) ]
						= 'abdh:Q2.12:'.$data[ 'Q2.12' ];
				
				//
				// Seed transactions.
				//
				$tmp = Array();
				if( array_key_exists( 'Q2.13a', $data )
				 && ($data[ 'Q2.13a' ] != '0') )
				{
					$val = 'abdh:Q6a:'.$data[ 'Q2.13a' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.13b', $data ) )
				{
					$val = 'abdh:Q6a:'.$data[ 'Q2.13b' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.13c', $data ) )
				{
					$val = 'abdh:Q6a:'.$data[ 'Q2.13c' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.13d', $data ) )
				{
					$val = 'abdh:Q6a:'.$data[ 'Q2.13d' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( array_key_exists( 'Q2.13e', $data ) )
				{
					$val = 'abdh:Q6a:'.$data[ 'Q2.13e' ];
					if( ! in_array( $val, $tmp ) )
						$tmp[] = $val;
				}
				if( count( $tmp ) )
					$sub[ getTag( 'abdh:Q6a' ) ] = $tmp;
				// No data for abdh:Q6b.
				
				//
				// Seed to other farmers.
				//
				if( array_key_exists( 'Q2.14', $data )
				 && ( ($data[ 'Q2.14' ] == '0')
				   || ($data[ 'Q2.14' ] == '1') ) )
					$sub[ getTag( 'abdh:Q2.14' ) ]
						= 'abdh:Q2.14:'.$data[ 'Q2.14' ];
				
				//
				// Seed renewal.
				//
				if( array_key_exists( 'Q2.15a', $data )
				 && ($data[ 'Q2.15a' ] != '0') )
					$sub[ getTag( 'abdh:Q2.15a' ) ]
						= 'abdh:Q2.15a:'.$data[ 'Q2.15a' ];
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
				if( array_key_exists( 'Q2.19', $data )
				 && ( ($data[ 'Q2.19' ] == '0')
				   || ($data[ 'Q2.19' ] == '1') ) )
					$sub[ getTag( 'abdh:Q2.19' ) ]
						= 'abdh:Q2.19:'.$data[ 'Q2.19' ];
				
				//
				// If yes what types?
				//
				if( array_key_exists( 'Q2.20', $data )
				 && ( ($data[ 'Q2.20' ] == '1')
				   || ($data[ 'Q2.20' ] == '2')
				   || ($data[ 'Q2.20' ] == '3') ) )
					$sub[ getTag( 'abdh:Q2.20' ) ]
						= 'abdh:Q2.20:'.$data[ 'Q2.20' ];
				
				//
				// Who takes care of the species?
				//
				if( array_key_exists( 'Q2.21a', $data )
				 && ($data[ 'Q2.21a' ] != '0') )
					$sub[ getTag( 'abdh:Q7a' ) ]
						= array( 'abdh:Q7a:'.$data[ 'Q2.21a' ] );
				if( array_key_exists( 'Q2.21b', $data ) )
					$sub[ getTag( 'abdh:Q7b' ) ]
						= array( $data[ 'Q2.21b' ] );
				
				//
				// Who takes decisions about seed planted?
				//
				if( array_key_exists( 'Q2.22a', $data )
				 && ($data[ 'Q2.22a' ] != '0') )
					$sub[ getTag( 'abdh:Q2.22a' ) ]
						= array( 'abdh:Q2.22a:'.$data[ 'Q2.22a' ] );
				if( array_key_exists( 'Q2.22b', $data ) )
					$sub[ getTag( 'abdh:Q2.22b' ) ]
						= array( $data[ 'Q2.22b' ] );
				
				//
				// Who takes decisions about field management?
				//
				if( array_key_exists( 'Q2.23a', $data )
				 && ($data[ 'Q2.23a' ] != '0') )
					$sub[ getTag( 'abdh:Q8a' ) ]
						= array( 'abdh:Q8a:'.$data[ 'Q2.23a' ] );
				if( array_key_exists( 'Q2.23b', $data ) )
					$sub[ getTag( 'abdh:Q8b' ) ]
						= array( $data[ 'Q2.23b' ] );
				
				//
				// Who takes decisions about consumption?
				//
				if( array_key_exists( 'Q2.24a', $data )
				 && ($data[ 'Q2.24a' ] != '0') )
					$sub[ getTag( 'abdh:Q2.24a' ) ]
						= array( 'abdh:Q2.24a:'.$data[ 'Q2.24a' ] );
				if( array_key_exists( 'Q2.24b', $data ) )
					$sub[ getTag( 'abdh:Q2.24b' ) ]
						= array( $data[ 'Q2.24b' ] );
				
				//
				// Who takes decisions about marketing?
				//
				if( array_key_exists( 'Q2.25a', $data )
				 && ($data[ 'Q2.25a' ] != '0') )
					$sub[ getTag( 'abdh:Q2.25a' ) ]
						= array( 'abdh:Q2.25a:'.$data[ 'Q2.25a' ] );
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
