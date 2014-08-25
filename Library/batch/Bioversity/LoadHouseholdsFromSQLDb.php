<?php

/**
 * Household load procedure.
 *
 * This file contains routines to load household assessments from an SQL database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 25/08/2014
 */

/*=======================================================================================
 *																						*
 *								LoadHouseholdsFromSQLDb.php								*
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


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 3 )
	exit( "Usage: "
		 ."script.php "
		 ."[SQL database DSN] "		// MySQLi://WEB-SERVICES:webservicereader@192.168.181.190/mauricio?socket=/var/mysql/mysql.sock&persist
		 ."[mongo database DSN] "	// mongodb://localhost:27017/BIOVERSITY
		 ."[graph DSN].\n" );		// neo4j://localhost:7474						// ==>

//
// Init local storage.
//
$start = 0;
$limit = 100;
$db = $rs = NULL;

//
// Load arguments.
//
$database = $argv[ 1 ];
$mongo = $argv[ 2 ];
$graph = ( $argc > 3 ) ? $argv[ 3 ] : NULL;

//
// Inform.
//
echo( "\n==> Loading household assessments.\n" );

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
	// Connect to database.
	//
	echo( "  • Connecting to SQL\n" );
	echo( "    - $database\n" );
	$db = NewADOConnection( $database );
	$db->Execute( "SET CHARACTER SET 'utf8'" );
	$db->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Import.
	//
	echo( "  • Importing\n" );
	$rs = $db->execute( "SELECT * FROM `Household_Information` limit $start,$limit" );
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
			$object = new OntologyWrapper\Household( $wrapper );
			
			//
			// Load household.
			//
			loadHousehold( $object, $data, $wrapper, $db );
print_r( $object->getArrayCopy() );
			
			//
			// Save record.
			//
	//		$object->commit();
			
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
		$rs = $db->execute( "SELECT * FROM `Household_Information` limit $start,$limit" );
	
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
	if( $db instanceof ADOConnection )
		$db->Close();

} // FINALLY BLOCK.


/*=======================================================================================
 *	FUNCTIONS																			*
 *======================================================================================*/

	/**
	 * Load household data.
	 *
	 * This function will load the household data provided in the <b>$theData</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the other sub-structure data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theData			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadHousehold( $theObject, $theData, $theWrapper, $theDatabase )
	{
		//
		// Set household ID.
		//
		if( array_key_exists( 'ID_HOUSEHOLD', $theData ) )
			$theObject->offsetSet( 'abdh:ID_HOUSEHOLD', $theData[ 'ID_HOUSEHOLD' ] );
		
		//
		// Set version.
		//
		if( array_key_exists( ':unit:version', $theData ) )
			$theObject->offsetSet( ':unit:version', $theData[ ':unit:version' ] );
		
		//
		// Set geographic data.
		//
		if( array_key_exists( ':location:country', $theData ) )
			$theObject->offsetSet( ':location:country', $theData[ ':location:country' ] );
		if( array_key_exists( ':location:admin', $theData ) )
			$theObject->offsetSet( ':location:admin', $theData[ ':location:admin' ] );
		
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
		loadRespondent( $sub,
						$theObject->offsetGet( 'abdh:ID_HOUSEHOLD' ),
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( 'abdh:interview', $sub );
		
		//
		// Init species records.
		//
		$sub = Array();
		
		//
		// Load annual species data.
		//
		loadSpeciesAnnual( $sub,
						   $theObject->offsetGet( 'abdh:ID_HOUSEHOLD' ),
						   $theWrapper,
						   $theDatabase );
		
		//
		// Set species records.
		//
		if( count( $sub ) )
			$theObject->offsetSet( 'abdh:species', $sub );

	} // loadHousehold.
	

	/**
	 * Load respondent data.
	 *
	 * This function will load the respondent data identifier by the household identifier
	 * provided in the <b>$theHousehold</b> parameter into the container provided in the
	 * <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param string				$theHousehold		Household identifier.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadRespondent( &$theContainer, $theHousehold, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `Respondent_Information` "
									."WHERE( `ID_HOUSEHOLD` = '$theHousehold' ) "
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
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:ID_ENUMERATOR' );
					$sub[ $tag ] = $data[ 'ID_ENUMERATOR' ];
				}
			
				//
				// Set enumerator.
				//
				if( array_key_exists( 'ENUMERATOR', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:ENUMERATOR' );
					$sub[ $tag ] = $data[ 'ENUMERATOR' ];
				}
			
				//
				// Set household head name.
				//
				if( array_key_exists( 'NOM_HHH', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:NOM_HHH' );
					$sub[ $tag ] = $data[ 'NOM_HHH' ];
				}
			
				//
				// Set household head gender.
				//
				if( array_key_exists( 'GENDER_HHH', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:GENDER_HHH' );
					$sub[ $tag ] = 'abdh:GENDER_HHH:'.$data[ 'GENDER_HHH' ];
				}
			
				//
				// Set household head education years.
				//
				if( array_key_exists( 'EDUC_HHH', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:EDUC_HHH' );
					$sub[ $tag ] = (int) $data[ 'EDUC_HHH' ];
				}
			
				//
				// Set household head education notes.
				//
				if( array_key_exists( 'EDUC_HHH_NOTES', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:EDUC_HHH_NOTES' );
					$sub[ $tag ] = $data[ 'EDUC_HHH_NOTES' ];
				}
			
				//
				// Set household head age.
				//
				if( array_key_exists( 'AGE_HHH', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:AGE_HHH' );
					$sub[ $tag ] = (int) $data[ 'AGE_HHH' ];
				}
			
				//
				// Set household head marital status.
				//
				if( array_key_exists( 'MARIT_STAT', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:MARIT_STAT' );
					$sub[ $tag ] = 'abdh:MARIT_STAT:'.$data[ 'MARIT_STAT' ];
				}
			
				//
				// Set household head spouse status.
				//
				if( array_key_exists( 'SPOUSE_STAT', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:SPOUSE_STAT' );
					$sub[ $tag ] = 'abdh:SPOUSE_STAT:'.$data[ 'SPOUSE_STAT' ];
				}
			
				//
				// Set household head spouse education level.
				//
				if( array_key_exists( 'SPOUSE_EDUC', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:SPOUSE_EDUC' );
					$sub[ $tag ] = (int) $data[ 'SPOUSE_EDUC' ];
				}
			
				//
				// Set household head spouse education notes.
				//
				if( array_key_exists( 'SPOUSE_EDUC_NOTES', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:SPOUSE_EDUC_NOTES' );
					$sub[ $tag ] = $data[ 'SPOUSE_EDUC_NOTES' ];
				}
			
				//
				// Set respondent relation to head.
				//
				if( array_key_exists( 'REL_RESP_HHH', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:REL_RESP_HHH' );
					$sub[ $tag ] = 'abdh:REL_RESP_HHH:'.$data[ 'REL_RESP_HHH' ];
				}
			
				//
				// Set date.
				//
				if( array_key_exists( 'DATE_INT', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:DATE_INT' );
					$sub[ $tag ] = substr( $data[ 'DATE_INT' ], 0, 4 )
								  .substr( $data[ 'DATE_INT' ], 5, 2 )
								  .substr( $data[ 'DATE_INT' ], 8, 2 );
				}
			
				//
				// Set latitude.
				//
				if( array_key_exists( 'LAT', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:LAT' );
					$sub[ $tag ] = $data[ 'LAT' ];
					if( array_key_exists( 'LATITUDE', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:latitude' );
						$sub[ $tag ] = (double) $data[ 'LATITUDE' ];
					}
					if( array_key_exists( 'LAT_DEG', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:latitude:deg' );
						$sub[ $tag ] = (int) $data[ 'LAT_DEG' ];
					}
					if( array_key_exists( 'LAT_MIN', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:latitude:min' );
						$sub[ $tag ] = (int) $data[ 'LAT_MIN' ];
					}
					if( array_key_exists( 'LAT_SEC', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:latitude:sec' );
						$sub[ $tag ] = (double) $data[ 'LAT_SEC' ];
					}
					if( array_key_exists( 'LAT_HEM', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:latitude:hem' );
						$sub[ $tag ] = $data[ 'LAT_HEM' ];
					}
				}
			
				//
				// Set longitude.
				//
				if( array_key_exists( 'LONG', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:LONG' );
					$sub[ $tag ] = $data[ 'LONG' ];
					if( array_key_exists( 'LONGITUDE', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:longitude' );
						$sub[ $tag ] = (double) $data[ 'LONGITUDE' ];
					}
					if( array_key_exists( 'LONG_DEG', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:longitude:deg' );
						$sub[ $tag ] = (int) $data[ 'LONG_DEG' ];
					}
					if( array_key_exists( 'LONG_MIN', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:longitude:min' );
						$sub[ $tag ] = (int) $data[ 'LONG_MIN' ];
					}
					if( array_key_exists( 'LONG_SEC', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:longitude:sec' );
						$sub[ $tag ] = (double) $data[ 'LONG_SEC' ];
					}
					if( array_key_exists( 'LONG_HEM', $data ) )
					{
						$tag = (string) $theWrapper->getSerial( ':location:longitude:hem' );
						$sub[ $tag ] = $data[ 'LONG_HEM' ];
					}
				}
			
				//
				// Set elevation.
				//
				if( array_key_exists( 'ELEV', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:ELEV' );
					$sub[ $tag ] = (int) $data[ 'ELEV' ];
					$tag = (string) $theWrapper->getSerial( ':location:elevation' );
					$sub[ $tag ] = (int) $data[ 'ELEV' ];
				}
		
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
			$rs = $theDatabase->execute( "SELECT * FROM `Respondent_Information` "
										."WHERE( `ID_HOUSEHOLD` = '$theHousehold' ) "
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
	 * This function will load the annual species data identifier by the household
	 * identifier provided in the <b>$theHousehold</b> parameter into the container provided
	 * in the <b>$theContainer</b> parameter.
	 *
	 * Each species record will be loaded as an element of the provided container, this
	 * means that all different types of species will be treated at the same level.
	 *
	 * @param array					$theContainer		Container.
	 * @param string				$theHousehold		Household identifier.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSpeciesAnnual( &$theContainer, $theHousehold, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `Annual_Plants` "
									."WHERE( `ID_HOUSEHOLD` = '$theHousehold' ) "
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
				$tag = (string) $theWrapper->getSerial( 'abdh:SPECIES_CAT' );
				$sub[ $tag ] = 'SPECIES_CAT:1';
			
				//
				// Set year.
				//
				if( array_key_exists( 'YEAR', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:YEAR' );
					$sub[ $tag ] = (int) $data[ 'YEAR' ];
				}
				
				//
				// Set species sequential number.
				//
				if( array_key_exists( 'NUM_SPECIES', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:NUM_SPECIES' );
					$sub[ $tag ] = $data[ 'NUM_SPECIES' ];
				}
				
				//
				// Init species vernacular names.
				//
				$tmp = Array();
				
				//
				// Set species local name.
				//
				if( array_key_exists( 'NAME_LOC', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:NAME_LOC' );
					$sub[ $tag ] = $data[ 'NAME_LOC' ];
					$tmp[] = array( kTAG_TEXT => array( $data[ 'NAME_LOC' ] ) );
				}
				
				//
				// Set species english name.
				//
				if( array_key_exists( 'NAME_ENG', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'abdh:NAME_ENG' );
					$sub[ $tag ] = $data[ 'NAME_ENG' ];
					$tmp[] = array( kTAG_LANGUAGE => 'en',
									kTAG_TEXT => array( $data[ 'NAME_ENG' ] ) );
				}
				
				//
				// Set taxon names.
				//
				if( count( $tmp ) )
				{
					$tag = (string) $theWrapper->getSerial( ':taxon:names' );
					$sub[ $tag ] = $tmp;
				}
				
				//
				// Set scientific name.
				//
				if( array_key_exists( 'NAME_SCIENT', $data ) )
				{
					$taxon = $data[ 'NAME_SCIENT' ];
					
					$tag = (string) $theWrapper->getSerial( ':taxon:epithet' );
					$sub[ $tag ] = $taxon;
					
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
					{
						$tag = (string) $theWrapper->getSerial( ':taxon:genus' );
						$sub[ $tag ] = $genus;
					}
					if( strlen( $species ) )
					{
						$tag = (string) $theWrapper->getSerial( ':taxon:species' );
						$sub[ $tag ] = $species;
					}
				}
		
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
			$rs = $theDatabase->execute( "SELECT * FROM `Annual_Plants` "
										."WHERE( `ID_HOUSEHOLD` = '$theHousehold' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadSpeciesAnnual.

?>
