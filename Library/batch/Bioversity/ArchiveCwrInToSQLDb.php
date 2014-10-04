<?php

/**
 * SQL CWR inventory archive procedure.
 *
 * This file contains routines to load CWR inventory from an SQL database and archive it as
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
 *								ArchiveCwrInToSQLDb.php									*
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
	// cwr_in
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
$limit = 20;
$page = 4;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Inventory';

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
// Init base query.
//
$base_query = <<<EOT
SELECT DISTINCT
	`HASH`,
	`:inventory:NICODE`,
	`:inventory:INSTCODE`,
	`cwr:in:NIENUMB`,
	`:taxon:epithet`
FROM
	`$table`
ORDER BY
	`HASH` ASC

EOT;

//
// Inform.
//
echo( "\n==> Loading CWR inventories into $table.\n" );

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
	// Iterate hashes.
	//
	$pages = $page;
	echo( "  • Exporting\n" );
	$query = $base_query;
	if( $last !== NULL )
		$query .= " WHERE `HASH` > $last";
	$query .= "LIMIT $start,$limit";
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
			// Load identifier.
			//
			loadIdentifier( $object, $data, $wrapper, $dc_in );
			
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
					   .'0x'.bin2hex( (string) $record[ 'HASH' ] ).', '
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
			$query .= " WHERE `HASH` > $last";
		$query .= "LIMIT $start,$limit";
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
	 * Load identifier data.
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
	function loadIdentifier( $theObject, $theData, $theWrapper, $theDatabase )
	{
		/***********************************************************************
		 * Set unit identification properties.
		 **********************************************************************/
		
		//
		// Set authority.
		//
		$theObject->offsetSet( kTAG_AUTHORITY, $theData[ ':inventory:INSTCODE' ] );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( kTAG_COLLECTION, $theData[ ':taxon:epithet' ] );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ ':inventory:NICODE' ].'-001' );
		
		//
		// Set version.
		//
		$theObject->offsetSet( kTAG_VERSION, '2014' );
				
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet(
			':inventory:dataset',
			'United Kingdom crop wild relatives inventory' );
		
		//
		// Set inventory code.
		//
		$theObject->offsetSet( ':inventory:code', $theData[ ':inventory:NICODE' ] );
		
		//
		// Set inventory administrative unit.
		//
		$theObject->offsetSet( ':inventory:admin',
							   "iso:3166:1:alpha-3:".$theData[ ':inventory:NICODE' ] );
		
		//
		// Set inventory institute.
		//
		$theObject->offsetSet(
			':inventory:institute',
			kDOMAIN_ORGANISATION
		   .'://http://fao.org/wiews:'
		   .$theData[ ':inventory:INSTCODE' ]
		   .kTOKEN_END_TAG );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set inventory institute code.
		//
		$theObject->offsetSet( 'cwr:INSTCODE', $theData[ ':inventory:INSTCODE' ] );
		
		//
		// Set familia.
		//
		if( array_key_exists( ':taxon:familia', $theData ) )
			$theObject->offsetSet( ':taxon:familia',
								   $theData[ ':taxon:familia' ] );
		
		//
		// Set genus.
		//
		if( array_key_exists( ':taxon:genus', $theData ) )
			$theObject->offsetSet( ':taxon:genus',
								   $theData[ ':taxon:genus' ] );
		
		//
		// Set species.
		//
		if( array_key_exists( ':taxon:species', $theData ) )
			$theObject->offsetSet( ':taxon:species',
								   $theData[ ':taxon:species' ] );
		
		//
		// Set species authority.
		//
		if( array_key_exists( ':taxon:species:author', $theData ) )
			$theObject->offsetSet( ':taxon:species:author',
								   $theData[ ':taxon:species:author' ] );
		
		//
		// Set infraspecific epithet.
		//
		if( array_key_exists( ':taxon:infraspecies', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies',
								   $theData[ ':taxon:infraspecies' ] );
		
		//
		// Set infraspecific authority.
		//
		if( array_key_exists( ':taxon:infraspecies:author', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies:author',
								   $theData[ ':taxon:infraspecies:author' ] );
		
		//
		// Set scientific name.
		//
		if( array_key_exists( ':taxon:epithet', $theData ) )
			$theObject->offsetSet( ':taxon:epithet',
								   $theData[ ':taxon:epithet' ] );
		
		//
		// Handle unit data.
		//
		loadUnit( $theObject, $theData[ 'HASH' ], $theWrapper, $theDatabase );
		
	} // loadIdentifier.
	

	/**
	 * Load unit data.
	 *
	 * This function will load the unit data identified by the <b>$theHash</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the target species data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theHash			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadUnit( $theObject, $theHash, $theWrapper, $theDatabase )
	{
		//
		// Iterate unit data.
		//
		$query = "SELECT DISTINCT "
				."`cwr:in:CRITPRIORI`, "
				."`cwr:TAXREF`, "
				."`__LANG`, "
				."`:taxon:names`, "
				."`cwr:GENEPOOL`, "
				."`cwr:GENEPOOLREF`, "
				."`cwr:TAXONGROUP`, "
				."`cwr:REFTAXONGROUP` "
				."FROM `cwr_in` "
				."WHERE( `HASH` = '$theHash' )";
		$all = $theDatabase->GetAll( $query );
		
		//
		// Clean records.
		//
		$records = Array();
		foreach( $all as $record )
		{
			$data = Array();
			foreach( $record as $key => $value )
			{
				if( strlen( $value = trim( $value ) ) )
					$data[ $key ] = $value;
			}
			if( count( $data ) )
				$records[] = $data;
		}
		
		//
		// Set vernacular names.
		//
		$list = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( ':taxon:names', $record ) )
			{
				if( strlen( $item = trim( $record[ ':taxon:names' ] ) ) )
				{
					if( ! in_array( $item, $list ) )
						$list[] = $item;
				}
			}
		}
		if( count( $list ) )
			$theObject->offsetSet( ':taxon:names',
								   array( array( kTAG_LANGUAGE => 'en',
												 kTAG_TEXT => $list ) ) );
		
		//
		// Set priority criteria.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:in:CRITPRIORI', $record ) )
			{
				foreach( explode( ';', $record[ 'cwr:in:CRITPRIORI' ] ) as $item )
				{
					if( strlen( $item = trim( $item ) ) )
					{
						$item = "cwr:in:CRITPRIORI:$item";
						if( ! in_array( $item, $property ) )
							$property[] = $item;
					}
				}
			}
		}
		if( count( $property ) )
			$theObject->offsetSet( 'cwr:in:CRITPRIORI', $property );
		
		//
		// Set taxon reference.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:TAXREF', $record ) )
			{
				if( strlen( $item = trim( $record[ 'cwr:TAXREF' ] ) ) )
				{
					if( ! in_array( $item, $property ) )
						$property[] = $item;
				}
			}
		}
		if( count( $property ) )
			$theObject->offsetSet( ':taxon:reference', $property );
		
		//
		// Set genepool.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:GENEPOOL', $record ) )
			{
				foreach( explode( ';', $record[ 'cwr:GENEPOOL' ] ) as $item )
				{
					if( strlen( $item = trim( $item ) ) )
					{
						if( ! in_array( $item, $property ) )
							$property[] = $item;
					}
				}
			}
		}
		if( count( $property ) )
			$theObject->offsetSet( ':taxon:genepool', $property );
		
		//
		// Set genepool reference.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:GENEPOOLREF', $record ) )
			{
				if( strlen( $item = trim( $record[ 'cwr:GENEPOOLREF' ] ) ) )
				{
					if( ! in_array( $item, $property ) )
						$property[] = $item;
				}
			}
		}
		if( count( $property ) )
			$theObject->offsetSet( ':taxon:genepool-ref', $property );
		
		//
		// Set taxon group.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:TAXONGROUP', $record ) )
			{
				foreach( explode( ';', $record[ 'cwr:TAXONGROUP' ] ) as $item )
				{
					if( strlen( $item = trim( $item ) ) )
					{
						if( ! in_array( $item, $property ) )
							$property[] = $item;
					}
				}
			}
		}
		if( count( $property ) )
			$theObject->offsetSet( ':taxon:group', $property );
		
		//
		// Set taxon group reference.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:REFTAXONGROUP', $record ) )
			{
				if( strlen( $item = trim( $record[ 'cwr:REFTAXONGROUP' ] ) ) )
				{
					if( ! in_array( $item, $property ) )
						$property[] = $item;
				}
			}
		}
		if( count( $property ) )
			$theObject->offsetSet( ':taxon:group-ref', $property );
		
		//
		// Handle distribution.
		//
		loadDistribution( $theObject, $theHash, $theWrapper, $theDatabase );
	
		//
		// Handle crossability.
		//
		loadCrossability( $theObject, $theHash, $theWrapper, $theDatabase );
		
		//
		// Handle threats.
		//
		loadThreats( $theObject, $theHash, $theWrapper, $theDatabase );
	
	} // loadUnit.
	

	/**
	 * Load distribution data.
	 *
	 * This function will load the unit data identified by the <b>$theHash</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the target species data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theHash			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadDistribution( $theObject, $theHash, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$struct = Array();
		
		//
		// Iterate crossability data.
		//
		$query = "SELECT DISTINCT "
				."`cwr:in:DISTCOUNTRYCODE`, "
				."`cwr:TAXONSTATUS` "
				."FROM `cwr_in` "
				."WHERE( `HASH` = '$theHash' )";
		$records = $theDatabase->GetAll( $query );
		foreach( $records as $record )
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
			// Set distribution region.
			//
			if( array_key_exists( 'cwr:in:DISTCOUNTRYCODE', $data ) )
			{
				//
				// Init loop storage.
				//
				$dist = Array();
				$tag_dist = getTag( ':location:admin' );
				$tag_dist_reg = getTag( ':location:region' );
				$tag_dist_occur = getTag( ':taxon:occurrence-status' );
				
				//
				// Set region code.
				//
				$code = "iso:3166:2:".$data[ 'cwr:in:DISTCOUNTRYCODE' ];
				$dist[ $tag_dist ] = $code;
				
				//
				// Set region name.
				//
				$region = new OntologyWrapper\Term( $theWrapper, $code );
				$dist[ $tag_dist_reg ]
					= OntologyWrapper\OntologyObject::SelectLanguageString(
						$region[ kTAG_LABEL ], 'en' );
		
				//
				// Set taxon occurrence status.
				//
				if( array_key_exists( 'cwr:TAXONSTATUS', $record ) )
				{
					switch( trim( $record[ 'cwr:TAXONSTATUS' ] ) )
					{
						case '1':
							$dist[ $tag_dist_occur ] = array( ':taxon:occurrence-status:100' );
							break;
						case '5':
							$dist[ $tag_dist_occur ] = array( ':taxon:occurrence-status:400' );
							break;
					}
				}
				
				//
				// Set element.
				//
				if( count( $dist ) )
					$struct[] = $dist;
			
			} // Has distribution.
		
		} // Iterating distribution data.
		
		//
		// Set structure.
		//
		if( count( $struct ) )
			$theObject->offsetSet( ':taxon:distribution', $struct );

	} // loadDistribution.
	

	/**
	 * Load crossability data.
	 *
	 * This function will load the unit data identified by the <b>$theHash</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the target species data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theHash			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCrossability( $theObject, $theHash, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$struct = Array();
		
		//
		// Iterate crossability data.
		//
		$query = "SELECT DISTINCT `cwr:LISTSPCROSS` "
				."FROM `cwr_in` "
				."WHERE( `HASH` = '$theHash' )";
		$records = $theDatabase->GetAll( $query );
		foreach( $records as $record )
		{
			//
			// Init loop storage.
			//
			$properties = Array();
			
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
			// Set list of species crosses.
			//
			if( array_key_exists( 'cwr:LISTSPCROSS', $data ) )
			{
				//
				// Collect species.
				//
				$list = Array();
				foreach( explode( ';', $data[ 'cwr:LISTSPCROSS' ] ) as $species )
				{
					if( strlen( $species = trim( $species ) ) )
						$list[] = $species;
				}
				
				//
				// Handle species.
				//
				if( count( $list ) )
				{
					//
					// Set structure label.
					//
					$properties[ kTAG_STRUCT_LABEL ] = implode( ', ', $list );
				
					//
					// Set list of species crosses.
					//
					$properties[ getTag( ':taxon:cross:species' ) ] = $list;
				
				} // Found species.
				
			} // Has species crosses.
			
			//
			// Add to struct.
			//
			if( count( $properties ) )
				$struct[] = $properties;
		
		} // Iterating crossability data.
		
		//
		// Set structure.
		//
		if( count( $struct ) )
			$theObject->offsetSet( ':taxon:cross', $struct );

	} // loadCrossability.
	

	/**
	 * Load threat data.
	 *
	 * This function will load the unit data identified by the <b>$theHash</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the target species data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theHash			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadThreats( $theObject, $theHash, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$struct = $properties = Array();
		
		//
		// Iterate crossability data.
		//
		$query = <<<EOT
SELECT DISTINCT
	`cwr:ASSLEVEL`,
	`cwr:in:COUNTRYCODEASS`,
	`iucn:category`,
	`iucn:criteria`,
	`cwr:YEARREDLISTASS`,
	`cwr:URLPUBREDLISTASS`,
	`cwr:REFREDLISTASS`,
	`cwr:in:COUNTRYCODE`,
	`cwr:in:NUNITCODE`,
	`cwr:in:NUNITDESCR`,
	`cwr:in:NUNITAUTHOR`,
	`iucn:threat`
FROM
	`cwr_in`
WHERE( `HASH` = '$theHash' )
EOT;
		$all = $theDatabase->GetAll( $query );
		
		//
		// Clean records.
		//
		$records = Array();
		foreach( $all as $record )
		{
			$data = Array();
			foreach( $record as $key => $value )
			{
				if( strlen( $value = trim( $value ) ) )
					$data[ $key ] = $value;
			}
			if( count( $data ) )
				$records[] = $data;
		}
		
		//
		// Set structure label.
		//
		$cou = $year = $ass = $code = NULL;
		foreach( $records as $record )
		{
			if( ($cou === NULL)
			 && array_key_exists( 'cwr:in:COUNTRYCODEASS', $record ) )
				$cou = $record[ 'cwr:in:COUNTRYCODEASS' ];
			if( ($year === NULL)
			 && array_key_exists( 'cwr:YEARREDLISTASS', $record ) )
				$year = $record[ 'cwr:YEARREDLISTASS' ];
			if( ($ass === NULL)
			 && array_key_exists( 'cwr:ASSLEVEL', $record ) )
				$ass = $record[ 'cwr:ASSLEVEL' ];
			if( ($code === NULL)
			 && array_key_exists( 'cwr:in:NUNITCODE', $record ) )
				$code = $record[ 'cwr:in:NUNITCODE' ];
			elseif( ($code === NULL)
				 && array_key_exists( 'iucn:criteria', $record ) )
				$code = $record[ 'iucn:criteria' ];
		}
		$label = Array();
		if( $cou !== NULL )
			$label[] = $cou;
		if( $year !== NULL )
			$label[] = $year;
		if( $ass !== NULL )
			$label[] = $ass;
		if( $code !== NULL )
			$label[] = $code;
		if( count( $label ) )
			$properties[ kTAG_STRUCT_LABEL ] = implode( '/', $label );
	
		//
		// Set threat assessment level.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:ASSLEVEL', $record ) )
			{
				$properties[ getTag( ':taxon:threat:assessment' ) ]
					= ':taxon:threat:assessment:'.$record[ 'cwr:ASSLEVEL' ];
				break;
			}
		}
	
		//
		// Set threat country.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:in:COUNTRYCODEASS', $record ) )
			{
				$properties[ getTag( ':taxon:threat:country' ) ]
					= 'iso:3166:1:alpha-3:'.$record[ 'cwr:in:COUNTRYCODEASS' ];
				break;
			}
		}
	
		//
		// Set iucn category.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'iucn:category', $record ) )
			{
				$item = 'iucn:category:'.$record[ 'iucn:category' ];
				if( ! in_array( $item, $property ) )
					$property[] = $item;
			}
		}
		if( count( $property ) )
			$properties[ getTag( 'iucn:category' ) ]
				= $property;
	
		//
		// Set iucn criteria citation.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'iucn:criteria', $record ) )
			{
				$properties[ getTag( 'iucn:criteria-citation' ) ]
					= $record[ 'iucn:criteria' ];
				break;
			}
		}
	
		//
		// Set red list assessment year.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:YEARREDLISTASS', $record ) )
			{
				$properties[ getTag( ':taxon:threat:assessment:year' ) ]
					= $record[ 'cwr:YEARREDLISTASS' ];
				break;
			}
		}
	
		//
		// Set red list assessment URL.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:URLPUBREDLISTASS', $record ) )
			{
				$properties[ getTag( ':taxon:threat:assessment:url' ) ]
					= $record[ 'cwr:URLPUBREDLISTASS' ];
				break;
			}
		}
	
		//
		// Set red list assessment references.
		//
		$property = Array();
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:REFREDLISTASS', $record ) )
			{
				if( ! in_array( $item = $record[ 'cwr:REFREDLISTASS' ], $property ) )
					$property[] = $item;
			}
		}
	
		//
		// Set national unit code.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:in:NUNITCODE', $record ) )
			{
				$properties[ getTag( ':taxon:threat:national:ucode' ) ]
					= $record[ 'cwr:in:NUNITCODE' ];
				break;
			}
		}
	
		//
		// Set national unit description.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:in:NUNITDESCR', $record ) )
			{
				$properties[ getTag( ':taxon:threat:national:udescr' ) ]
					= $record[ 'cwr:in:NUNITDESCR' ];
				break;
			}
		}
	
		//
		// Set national unit authority.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'cwr:in:NUNITAUTHOR', $record ) )
			{
				$properties[ getTag( ':taxon:threat:national:uauth' ) ]
					= $record[ 'cwr:in:NUNITAUTHOR' ];
				break;
			}
		}
	
		//
		// Set national threat code.
		//
		foreach( $records as $record )
		{
			if( array_key_exists( 'iucn:threat', $record ) )
			{
				$properties[ getTag( ':taxon:threat:national' ) ]
					= $record[ 'iucn:threat' ];
				break;
			}
		}
		
		//
		// Add to object.
		//
		if( count( $properties ) )
			$theObject->offsetSet( ':taxon:threat', array( $properties ) );

	} // loadThreats.
	

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
	 * Get enum.
	 *
	 * This function will return the label of the provided enumeration.
	 *
	 * @param string				$theEnum			Enumeration.
	 * @return string				Term label.
	 */
	function getEnum( $theEnum )
	{
		global $wrapper;
		
		$term = new OntologyWrapper\Term( $wrapper, $theEnum );
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$term[ kTAG_LABEL ], 'en' );										// ==>

	} // getEnum.

?>
