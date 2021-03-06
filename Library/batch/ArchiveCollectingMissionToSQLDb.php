<?php

/**
 * SQL collecting mission archive procedure.
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
 *							ArchiveCollectingMissionToSQLDb.php							*
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
	// cmdb_collecting
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
$page = 3;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\CollectingMission';

//
// Init base query.
//
$base_query = "SELECT * from `cmdb_collecting`";

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
echo( "\n==> Loading collecting missions into $table.\n" );

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
		$query .= " WHERE `:collecting:event:identifier` > $last";
	$query .= " ORDER BY `:collecting:event:identifier` LIMIT $start,$limit";
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
					   .'0x'.bin2hex( (string) $record[ ':collecting:event:identifier' ] ).', '
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
			$query .= " WHERE `:collecting:event:identifier` > $last";
		$query .= " ORDER BY `:collecting:event:identifier` LIMIT $start,$limit";
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
		$theObject->offsetSet( ':unit:authority', 'ITA406' );
		
		//
		// Set collection.
		//
		if( $theData[ ':collecting:mission:identifier' ]
			!= $theData[ ':collecting:event:identifier' ] )
			$theObject->offsetSet( ':unit:collection',
								   $theData[ ':collecting:mission:identifier' ] );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( ':unit:identifier',
							   $theData[ ':collecting:event:identifier' ] );
				
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
		// Set mission.
		//
		if( array_key_exists( ':collecting:mission:identifier', $theData ) )
		{
			//
			// Set mission.
			//
			$theObject->offsetSet( ':mission',
								   OntologyWrapper\Mission::kDEFAULT_DOMAIN
								  .'://'
								  .'ITA406/'
								  .$theData[ ':collecting:mission:identifier' ]
								  .kTOKEN_END_TAG );
			
			//
			// Set code.
			//
			$theObject->offsetSet( ':mission:identifier',
								   $theData[ ':collecting:mission:identifier' ] );
		}
		
		//
		// Set collecting mission identifier.
		//
		if( array_key_exists( ':collecting:event:identifier', $theData ) )
			$theObject->offsetSet( ':mission:collecting:identifier',
								   $theData[ ':collecting:event:identifier' ] );
		
		//
		// Set collecting mission start.
		//
		if( array_key_exists( ':collecting:event:start', $theData ) )
			$theObject->offsetSet( ':mission:collecting:start',
								   $theData[ ':collecting:event:start' ] );
		
		//
		// Set collecting mission end.
		//
		if( array_key_exists( ':collecting:event:end', $theData ) )
			$theObject->offsetSet( ':mission:collecting:end',
								   $theData[ ':collecting:event:end' ] );
		
		//
		// Set region name.
		//
		if( array_key_exists( 'Region_name', $theData ) )
			$theObject->offsetSet( ':location:region',
								   $theData[ 'Region_name' ] );
		
		//
		// Set location admin.
		//
		if( array_key_exists( 'REGION', $theData ) )
			$theObject->offsetSet( ':location:admin',
								   $theData[ 'REGION' ] );
		
		//
		// Load collecting mission taxa.
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
		
		//
		// Load collectors.
		//
		$sub = Array();
		loadCollectors( $sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':collecting:entities', $sub );
		
		//
		// Load material transfers.
		//
		$sub = Array();
		loadTransfers( $sub,
					   $theData,
					   $theWrapper,
					   $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':germplasm:mt', $sub );
		
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
		$id = $theUnit[ ':collecting:event:identifier' ];
		$query = "SELECT DISTINCT "
				."`:taxon:genus`, "
				."`:taxon:sectio`, "
				."`:taxon:species`, "
				."`:taxon:epithet` "
				."FROM `cmdb_collecting_taxa` "
				."WHERE `:collecting:event:identifier` = "
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
	 * Load collectors.
	 *
	 * This function will load the mission collectors related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCollectors( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		global $wrapper;
		
		//
		// Iterate taxa data.
		//
		$id = $theUnit[ ':collecting:event:identifier' ];
		$query = "SELECT * FROM `cmdb_collectors` "
				."WHERE `:collecting:event:identifier` = "
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
			// Handle institute.
			//
			if( array_key_exists( 'INSTCODE', $data ) )
			{
				//
				// Get institute.
				//
				$query = "SELECT * FROM `cmdb_institutes` "
						."WHERE `INSTCODE` = "
						.'0x'.bin2hex( $data[ 'INSTCODE' ] );
				$tmp = $theDatabase->GetRow( $query );
				$instrec = Array();
				foreach( $tmp as $key => $value )
				{
					//
					// Normalise value.
					//
					if( strlen( trim( $value ) ) )
						$instrec[ $key ] = trim( $value );
			
				} // Scanning record.
			
				//
				// Determine institute identifier.
				//
				$institute_id = ( array_key_exists( 'FAOCODE', $instrec ) )
							  ? (kDOMAIN_ORGANISATION
								.'://http://fao.org/wiews:'
								.strtoupper( $instrec[ 'FAOCODE' ] )
								.kTOKEN_END_TAG)
							  : NULL;
			
				//
				// Determine institute object.
				//
				$institute = ( $institute_id !== NULL )
						   ? new OntologyWrapper\FAOInstitute(
						   			$wrapper, $institute_id, FALSE )
						   : NULL;
			
				//
				// Check institute object.
				//
				if( $institute !== NULL )
				{
					if( ! $institute->committed() )
						$institute = $institute_id = NULL;
				}
			
			} // Has institute.
			
			//
			// Clear institute data.
			//
			else
			{
				$instrec = Array();
				$institute = $institute_id = NULL;
			
			} // No institute.
			
			//
			// Set entity name.
			//
			$name = Array();
			if( array_key_exists( ':entity:fname', $data )
			 || array_key_exists( ':entity:lname', $data ) )
			{
				$tmp = Array();
				if( array_key_exists( ':entity:fname', $data ) )
					$tmp[] = $data[ ':entity:fname' ];
				if( array_key_exists( ':entity:lname', $data ) )
					$tmp[] = $data[ ':entity:lname' ];
				if( count( $tmp ) )
					$name[] = implode( ' ', $tmp );
				
				if( array_key_exists( 'ACRONYM', $instrec ) )
					$name[] = "(".$instrec[ 'ACRONYM' ].")";
				elseif( array_key_exists( 'ECPACRONYM', $instrec ) )
					$name[] = "(".$instrec[ 'ECPACRONYM' ].")";
				elseif( array_key_exists( 'NAME_NAT', $instrec ) )
					$name[] = "(".$instrec[ 'NAME_NAT' ].")";
				elseif( array_key_exists( 'NAME_ENG', $instrec ) )
					$name[] = "(".$instrec[ 'NAME_ENG' ].")";
				elseif( $institute !== NULL )
					$name[] = "(".$institute->offsetGet( ':name' ).")";
			}
			elseif( array_key_exists( 'NAME_NAT', $instrec ) )
				$name[] = $instrec[ 'NAME_NAT' ];
			elseif( array_key_exists( 'NAME_ENG', $instrec ) )
				$name[] = $instrec[ 'NAME_ENG' ];
			elseif( $institute !== NULL )
				$name[] = $institute->offsetGet( ':name' );
			elseif( array_key_exists( 'ACRONYM', $instrec ) )
				$name[] = $instrec[ 'ACRONYM' ];
			elseif( array_key_exists( 'ECPACRONYM', $instrec ) )
				$name[] = $instrec[ 'ECPACRONYM' ];
			
			//
			// Set name.
			//
			if( count( $name ) )
				$sub[ kTAG_NAME ]
					= implode( ' ', $name );
			else
				$sub[ kTAG_NAME ]
					= 'unknown';
			
			//
			// Set entity first name.
			//
			if( array_key_exists( ':entity:fname', $data ) )
				$sub[ getTag( ':entity:fname' ) ]
					= $data[ ':entity:fname' ];
			
			//
			// Set entity last name.
			//
			if( array_key_exists( ':entity:lname', $data ) )
				$sub[ getTag( ':entity:lname' ) ]
					= $data[ ':entity:lname' ];
			
			//
			// Set collecting institute code.
			//
			if( array_key_exists( 'FAOCODE', $instrec ) )
				$sub[ getTag( 'mcpd:COLLCODE' ) ]
					= $instrec[ 'FAOCODE' ];
			
			//
			// Set collecting institute name.
			//
			if( array_key_exists( 'NAME_NAT', $instrec ) )
				$sub[ getTag( 'mcpd:COLLDESCR' ) ]
					= $instrec[ 'NAME_NAT' ];
			elseif( array_key_exists( 'NAME_ENG', $instrec ) )
				$sub[ getTag( 'mcpd:COLLDESCR' ) ]
					= $instrec[ 'NAME_ENG' ];
		
			//
			// Set entity type.
			//
			if( array_key_exists( 'ORGTYPE', $instrec ) )
				$sub[ getTag( ':type:entity' ) ]
					= explode( ';', $instrec[ 'ORGTYPE' ] );
		
			//
			// Set entity kind.
			//
			if( array_key_exists( 'ORGKIND', $instrec ) )
				$sub[ getTag( ':kind:entity' ) ]
					= explode( ';', $instrec[ 'ORGKIND' ] );
			
			//
			// Set institute.
			//
			if( $institute_id !== NULL )
				$sub[ getTag( ':inventory:institute' ) ]
					= $institute_id;
			
			//
			// Set institute details.
			//
			else
			{
				//
				// Set entity acronym.
				//
				$tmp = Array();
				if( array_key_exists( 'ACRONYM', $instrec ) )
					$tmp[] = $instrec[ 'ACRONYM' ];
				if( array_key_exists( 'ECPACRONYM', $instrec ) )
					$tmp[] = $instrec[ 'ECPACRONYM' ];
				if( count( $tmp ) )
					$sub[ getTag( ':entity:acronym' ) ]
						= $tmp;
			
				//
				// Set URL.
				//
				if( array_key_exists( 'URL', $instrec ) )
					$sub[ getTag( ':entity:url' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'URL' ] ) );
			
				//
				// Set nationality.
				//
				$country_name = $country_code = NULL;
				if( array_key_exists( 'CTY', $instrec ) )
				{
					$country_code = $instrec[ 'CTY' ];
					$country_name = getCountry( $country_code );
					$sub[ getTag( ':entity:nationality' ) ] = $country_code;
				}
		
				//
				// Set address.
				//
				$address = Array();
				if( array_key_exists( 'STREET_POB', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'STREET_POB' ] ) ) )
						$address[] = $record[ 'STREET_POB' ];
				}
				$city = '';
				if( array_key_exists( 'ZIP_CODE', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'ZIP_CODE' ] ) ) )
						$city .= ($record[ 'ZIP_CODE' ].' ');
				}
				if( array_key_exists( 'CITY_STATE', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'CITY_STATE' ] ) ) )
						$city .= ($record[ 'CITY_STATE' ].' ');
				}
				if( strlen( $city ) )
					$address[] = $city;
				if( $country_name !== NULL )
					$address[] = $country_name;
				if( count( $address ) )
					$sub[ getTag( ':entity:mail' ) ]
						= array( array( kTAG_TEXT => implode( "\n", $address ) ) );
			
				//
				// Set e-mail.
				//
				if( array_key_exists( 'EMA', $instrec ) )
					$sub[ getTag( ':entity:email' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'EMA' ] ) );
			
				//
				// Set telephone.
				//
				if( array_key_exists( 'TLF', $instrec ) )
					$sub[ getTag( ':entity:phone' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'TLF' ] ) );
			
				//
				// Set telefax.
				//
				if( array_key_exists( 'FAX', $instrec ) )
					$sub[ getTag( ':entity:fax' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'FAX' ] ) );
			
				//
				// Set telex.
				//
				if( array_key_exists( 'TLX', $instrec ) )
					$sub[ getTag( ':entity:tlx' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'TLX' ] ) );
		
				//
				// Set elevation.
				//
				if( array_key_exists( 'ALT', $instrec ) )
					$sub[ getTag( ':location:site:elevation' ) ]
						= $instrec[ 'ALT' ];
		
				//
				// Set latitude.
				//
				if( array_key_exists( 'LAT', $instrec ) )
					$sub[ getTag( ':location:site:latitude:provided' ) ]
						= $instrec[ 'LAT' ];
		
				//
				// Set longitude.
				//
				if( array_key_exists( 'LONG_', $instrec ) )
					$sub[ getTag( ':location:site:longitude:provided' ) ]
						= $instrec[ 'LONG_' ];
		
				//
				// Set version.
				//
				if( array_key_exists( 'UPDATED', $instrec ) )
					$sub[ getTag( ':unit:version' ) ]
						= $instrec[ 'UPDATED' ];
			
			} // Not an institute or not known institite.

			//
			// Set remarks.
			//
			if( array_key_exists( 'REMARKS', $instrec ) )
				$sub[ getTag( ':notes' ) ]
					= array( $instrec[ 'REMARKS' ] );
			
			//
			// Set element.
			//
			if( count( $sub ) )
				$theContainer[] = $sub;
		
		} // Iterating records.

	} // loadCollectors.
	

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
		global $wrapper;
		
		//
		// Iterate taxa data.
		//
		$id = $theUnit[ ':collecting:event:identifier' ];
		$query = "SELECT * FROM `cmdb_distribution` "
				."WHERE `:collecting:event:identifier` = "
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
			// Get institute.
			//
			$query = "SELECT * FROM `cmdb_institutes` "
					."WHERE `INSTCODE` = "
					.'0x'.bin2hex( $data[ 'INSTCODE' ] );
			$tmp = $theDatabase->GetRow( $query );
			$instrec = Array();
			foreach( $tmp as $key => $value )
			{
				//
				// Normalise value.
				//
				if( strlen( trim( $value ) ) )
					$instrec[ $key ] = trim( $value );
			
			} // Scanning record.
			
			//
			// Determine institute identifier.
			//
			$institute_id = ( array_key_exists( 'FAOCODE', $instrec ) )
						  ? (kDOMAIN_ORGANISATION
							.'://http://fao.org/wiews:'
							.strtoupper( $instrec[ 'FAOCODE' ] )
							.kTOKEN_END_TAG)
						  : NULL;
			
			//
			// Determine institute object.
			//
			$institute = ( $institute_id !== NULL )
					   ? new OntologyWrapper\FAOInstitute( $wrapper, $institute_id, FALSE )
					   : NULL;
			
			//
			// Check institute object.
			//
			if( $institute !== NULL )
			{
				if( ! $institute->committed() )
					$institute = $institute_id = NULL;
			}
			
			//
			// Set organisation name.
			//
			$name = Array();
			if( array_key_exists( 'NAME_NAT', $instrec ) )
				$name[] = $instrec[ 'NAME_NAT' ];
			elseif( array_key_exists( 'NAME_ENG', $instrec ) )
				$name[] = $instrec[ 'NAME_ENG' ];
			elseif( $institute !== NULL )
				$name[] = $institute->offsetGet( ':name' );
			elseif( array_key_exists( 'ACRONYM', $instrec ) )
				$name[] = $instrec[ 'ACRONYM' ];
			elseif( array_key_exists( 'ECPACRONYM', $instrec ) )
				$name[] = $instrec[ 'ECPACRONYM' ];
			
			//
			// Set name.
			//
			if( count( $name ) )
				$sub[ kTAG_NAME ]
					= implode( ' ', $name );
			else
				$sub[ kTAG_NAME ]
					= 'unknown';
			
			//
			// Set taxon.
			//
			if( array_key_exists( 'TAXON', $data ) )
				$sub[ getTag( ':taxon:epithet' ) ]
					= $data[ 'TAXON' ];
			
			//
			// Set samples intended.
			//
			if( array_key_exists( 'SamplesIntended', $data ) )
				$sub[ getTag( ':germplasm:mt:samples-intended' ) ]
					= $data[ 'SamplesIntended' ];
			
			//
			// Set samples verified.
			//
			if( array_key_exists( 'SamplesVerified', $data ) )
				$sub[ getTag( ':germplasm:mt:samples-verified' ) ]
					= $data[ 'SamplesVerified' ];
			
			//
			// Set samples received.
			//
			if( array_key_exists( 'SamplesReceived', $data ) )
				$sub[ getTag( ':germplasm:mt:samples-received' ) ]
					= $data[ 'SamplesReceived' ];
			
			//
			// Set institute identifier.
			//
			if( array_key_exists( 'FAOCODE', $instrec ) )
				$sub[ kTAG_ENTITY_IDENT ]
					= $instrec[ 'FAOCODE' ];
		
			//
			// Set entity type.
			//
			if( array_key_exists( 'ORGTYPE', $instrec ) )
				$sub[ getTag( ':type:entity' ) ]
					= explode( ';', $instrec[ 'ORGTYPE' ] );
		
			//
			// Set entity kind.
			//
			if( array_key_exists( 'ORGKIND', $instrec ) )
				$sub[ getTag( ':kind:entity' ) ]
					= explode( ';', $instrec[ 'ORGKIND' ] );
			
			//
			// Set institute.
			//
			if( $institute !== NULL )
				$sub[ getTag( ':inventory:institute' ) ]
					= $institute_id;
			
			//
			// Set institute details.
			//
			else
			{
				//
				// Set entity acronym.
				//
				$tmp = Array();
				if( array_key_exists( 'ACRONYM', $instrec ) )
					$tmp[] = $instrec[ 'ACRONYM' ];
				if( array_key_exists( 'ECPACRONYM', $instrec ) )
					$tmp[] = $instrec[ 'ECPACRONYM' ];
				if( count( $tmp ) )
					$sub[ getTag( ':entity:acronym' ) ]
						= $tmp;
			
				//
				// Set URL.
				//
				if( array_key_exists( 'URL', $instrec ) )
					$sub[ getTag( ':entity:url' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'URL' ] ) );
			
				//
				// Set nationality.
				//
				$country_name = $country_code = NULL;
				if( array_key_exists( 'CTY', $instrec ) )
				{
					$country_code = $instrec[ 'CTY' ];
					$country_name = getCountry( $country_code );
					$sub[ getTag( ':entity:nationality' ) ] = $country_code;
				}
		
				//
				// Set address.
				//
				$address = Array();
				if( array_key_exists( 'STREET_POB', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'STREET_POB' ] ) ) )
						$address[] = $record[ 'STREET_POB' ];
				}
				$city = '';
				if( array_key_exists( 'ZIP_CODE', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'ZIP_CODE' ] ) ) )
						$city .= ($record[ 'ZIP_CODE' ].' ');
				}
				if( array_key_exists( 'CITY_STATE', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'CITY_STATE' ] ) ) )
						$city .= ($record[ 'CITY_STATE' ].' ');
				}
				if( strlen( $city ) )
					$address[] = $city;
				if( $country_name !== NULL )
					$address[] = $country_name;
				if( count( $address ) )
					$sub[ getTag( ':entity:mail' ) ]
						= array( array( kTAG_TEXT => implode( "\n", $address ) ) );
			
				//
				// Set e-mail.
				//
				if( array_key_exists( 'EMA', $instrec ) )
					$sub[ getTag( ':entity:email' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'EMA' ] ) );
			
				//
				// Set telephone.
				//
				if( array_key_exists( 'TLF', $instrec ) )
					$sub[ getTag( ':entity:phone' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'TLF' ] ) );
			
				//
				// Set telefax.
				//
				if( array_key_exists( 'FAX', $instrec ) )
					$sub[ getTag( ':entity:fax' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'FAX' ] ) );
			
				//
				// Set telex.
				//
				if( array_key_exists( 'TLX', $instrec ) )
					$sub[ getTag( ':entity:tlx' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'TLX' ] ) );
		
				//
				// Set elevation.
				//
				if( array_key_exists( 'ALT', $instrec ) )
					$sub[ getTag( ':location:site:elevation' ) ]
						= $instrec[ 'ALT' ];
		
				//
				// Set latitude.
				//
				if( array_key_exists( 'LAT', $instrec ) )
					$sub[ getTag( ':location:site:latitude:provided' ) ]
						= $instrec[ 'LAT' ];
		
				//
				// Set longitude.
				//
				if( array_key_exists( 'LONG_', $instrec ) )
					$sub[ getTag( ':location:site:longitude:provided' ) ]
						= $instrec[ 'LONG_' ];
		
				//
				// Set version.
				//
				if( array_key_exists( 'UPDATED', $instrec ) )
					$sub[ getTag( ':unit:version' ) ]
						= $instrec[ 'UPDATED' ];
			
			} // Not an institute or not known institite.
	
			//
			// Set notes.
			//
			if( array_key_exists( 'Comments', $data ) )
				$sub[ getTag( ':notes' ) ]
					= array( $data[ 'Comments' ] );
		
			//
			// Set label.
			//
			$tmp = Array();
			if( array_key_exists( kTAG_NAME, $sub ) )
				$tmp[] = $sub[ kTAG_NAME ];
			if( array_key_exists( getTag( ':taxon:epithet' ), $sub ) )
				$tmp[] = '('.$sub[ getTag( ':taxon:epithet' ) ].')';
			$sub[ kTAG_STRUCT_LABEL ] = implode( ' ', $tmp );
			
			//
			// Set element.
			//
			if( count( $sub ) )
				$theContainer[] = $sub;
		
		} // Iterating records.

	} // loadTransfers.
	

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
				$region[ getTag( ':label' ) ], 'en' );										// ==>

	} // getRegion.
	

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
				$country[ getTag( ':label' ) ], 'en' );										// ==>

	} // getCountry.
	

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
