<?php

/**
 * SQL mission archive procedure.
 *
 * This file contains routines to load missions from an SQL database and archive it as
 * XML the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/09/2014
 */

/*=======================================================================================
 *																						*
 *								ArchiveMissionToSQLDb.php								*
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
	// cmdb_mission
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
$page = 2;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Mission';

//
// Init base query.
//
$base_query = "SELECT * from `cmdb_mission`";

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
echo( "\n==> Loading missions into $table.\n" );

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
	echo( "  • Setting users.\n" );
	$wrapper->Users( $mongo );
	
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
		$query .= " WHERE `:collecting:mission:identifier` > $last";
	$query .= " ORDER BY `:collecting:mission:identifier` LIMIT $start,$limit";
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
			// Validate object.
			//
			$object->validate();
			
			//
			// Save record.
			//
			$xml = $object->export( 'xml' );
			$insert = ( $last === NULL )
					? "INSERT INTO `$table`( "
					: "REPLACE INTO `$table`( ";
			$insert .= ("`id`, `class`, `xml` ) VALUES( "
					   .'0x'.bin2hex( (string) $record[ ':collecting:mission:identifier' ] ).', '
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
			$query .= " WHERE `:collecting:mission:identifier` > $last";
		$query .= " ORDER BY `:collecting:mission:identifier` LIMIT $start,$limit";
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
		$theObject->offsetSet( kTAG_AUTHORITY,
							   'ITA406' );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER,
							   $theData[ ':collecting:mission:identifier' ] );
				
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet(
			':inventory:dataset',
			'Germplasm Collecting Missions Database' );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set mission identifier.
		//
		if( array_key_exists( ':collecting:mission:identifier', $theData ) )
			$theObject->offsetSet( ':mission:identifier',
								   $theData[ ':collecting:mission:identifier' ] );
		
		//
		// Set mission title.
		//
		if( array_key_exists( 'Title', $theData ) )
			$theObject->offsetSet( ':name',
								   $theData[ 'Title' ] );
		
		//
		// Set mission start.
		//
		if( array_key_exists( ':collecting:mission:start', $theData ) )
			$theObject->offsetSet( ':mission:start',
								   $theData[ ':collecting:mission:start' ] );
		
		//
		// Set mission end.
		//
		if( array_key_exists( ':collecting:mission:end', $theData ) )
			$theObject->offsetSet( ':mission:end',
								   $theData[ ':collecting:mission:end' ] );
		
		//
		// Set mission documents.
		//
		$theObject->offsetSet( ':mission:documents',
							   'http://www.central-repository.cgiar.org/index.php?'
							  .'id=2391&'
							  .'user_alfsearch_pi1[search1][submit]=Search&'
							  .'user_alfsearch_pi1[search1][missionID]='
							  .$theData[ ':collecting:mission:identifier' ] );
		
		//
		// Load mission taxa.
		//
	/*
		$sub = Array();
		loadTaxa( $sub,
				  $theData,
				  $theWrapper,
				  $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':mission:taxa', $sub );
	*/
		
	} // loadUnit.
	

	/**
	 * Load mission taxa.
	 *
	 * This function will load the mission taxa related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadTaxa( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Iterate taxa data.
		//
		$id = $theUnit[ ':collecting:mission:identifier' ];
		$query = "SELECT DISTINCT "
				."`:taxon:genus`, "
				."`:taxon:sectio`, "
				."`:taxon:species`, "
				."`:taxon:epithet` "
				."FROM `cmdb_mission_taxa` "
				."WHERE `:collecting:mission:identifier` = "
				.'0x'.bin2hex( $id );
		$records = $theDatabase->GetAll( $query );
		foreach( $records as $record )
		{
			//
			// Init local storage.
			//
			$sub = $data = Array();
			
			//
			// Scan record.
			//
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
			// Set genus.
			//
			if( array_key_exists( ':taxon:genus', $data ) )
				$sub[ getTag( ':taxon:genus' ) ]
					= $data[ ':taxon:genus' ];
			
			//
			// Set section.
			//
			if( array_key_exists( ':taxon:sectio', $data ) )
				$sub[ getTag( ':taxon:sectio' ) ]
					= $data[ ':taxon:sectio' ];
			
			//
			// Set species.
			//
			if( array_key_exists( ':taxon:species', $data ) )
				$sub[ getTag( ':taxon:species' ) ]
					= $data[ ':taxon:species' ];
			
			//
			// Set species name.
			//
			if( array_key_exists( ':taxon:genus', $data )
			 && array_key_exists( ':taxon:species', $data ) )
				$sub[ getTag( ':taxon:species:name' ) ]
					= $implode( ' ', array( $data[ ':taxon:genus' ],
											$data[ ':taxon:species' ] ) );
			
			//
			// Set epithet.
			//
			if( array_key_exists( ':taxon:epithet', $data ) )
				$sub[ getTag( ':taxon:epithet' ) ]
					= $data[ ':taxon:epithet' ];
			
			//
			// Set element.
			//
			if( count( $sub ) )
				$theContainer[] = $sub;
		}

	} // loadTaxa.
	

	/**
	 * Get country.
	 *
	 * This function will return the country name and its code in the provided parameter
	 * that should contain its code.
	 *
	 * @param string				$theIdentifier		Country code, receives full code.
	 * @return string				Country name.
	 */
	function getCountry( &$theIdentifier )
	{
		global $wrapper;
		
		//
		// Get country code.
		//
		$code = OntologyWrapper\Term::ResolveCountryCode( $wrapper, $theIdentifier );
		if( ! $code )
			throw new Exception( "Unknown country [$theIdentifier]." );			// !@! ==>
		
		//
		// Set country code.
		//
		$theIdentifier = $code;
		
		//
		// Get country name.
		//
		$country = new OntologyWrapper\Term( $wrapper, $code );
		
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$country[ kTAG_LABEL ], 'en' );										// ==>

	} // getCountry.
	

	/**
	 * Get region.
	 *
	 * This function will return the region name referenced by the provided parameter
	 * that should contain its code.
	 *
	 * @param string				$theIdentifier		Region code.
	 * @return string				Region name.
	 */
	function getRegion( $theIdentifier )
	{
		global $wrapper;
		
		//
		// Get region name.
		//
		$region = new OntologyWrapper\Term( $wrapper, $theIdentifier );
		
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$region[ kTAG_LABEL ], 'en' );										// ==>

	} // getRegion.
	

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
