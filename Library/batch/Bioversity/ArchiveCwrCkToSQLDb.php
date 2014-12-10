<?php

/**
 * SQL CWR checklist archive procedure.
 *
 * This file contains routines to load CWR checklist from an SQL database and archive it as
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
 *								ArchiveCwrCkToSQLDb.php									*
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
	// cwr_ck
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
$page = 10;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Checklist';

//
// Init base query.
//
$base_query = "SELECT * from `cwr_ck`";

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
echo( "\n==> Loading CWR checklists into $table.\n" );

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
	// Normalise output table.
	//
	if( $last === NULL )
		$rs = $dc_out->Execute( "TRUNCATE TABLE `$table`" );
	else
	{
		$dc_out->Execute( "DELETE FROM `$table` WHERE `id` > $last" );
	}
	$rs->Close();
	
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
			// Clean record.
			//
			$data = Array();
			foreach( $record as $key => $value )
			{
				//
				// Normalise value.
				//
				if( strlen( $tmp = trim( $value ) ) )
					$data[ $key ] = $tmp;
			
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
					   .$record[ 'id' ].', '
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
		 * Compile taxon.
		 **********************************************************************/
		
		//
		// Set scientific name.
		//
		$taxon = Array();
		if( array_key_exists( 'CK:GENUS', $theData ) )
			$taxon[] = $theData[ 'CK:GENUS' ];
		if( array_key_exists( 'CK:SPECIES', $theData ) )
			$taxon[] = $theData[ 'CK:SPECIES' ];
		if( array_key_exists( 'CK:SUBTAXON', $theData ) )
			$taxon[] = $theData[ 'CK:SUBTAXON' ];
		$taxon = ( count( $taxon ) )
			   ? implode( ' ', $taxon )
			   : NULL;
		
		/***********************************************************************
		 * Set unit identification properties.
		 **********************************************************************/
		
		//
		// Set authority.
		//
		if( array_key_exists( 'CK:INSTCODE', $theData ) )
			$theObject->offsetSet( ':unit:authority', $theData[ 'CK:INSTCODE' ] );
		else
			$theObject->offsetSet( ':unit:authority', $theData[ 'CK:CWRCODE' ] );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( ':unit:collection', $taxon );
		
		//
		// Set identifier.
		//
		$tmp = Array();
		$tmp[] = $theData[ 'CK:CWRCODE' ];
		if( array_key_exists( 'CK:NUMB', $theData ) )
			$tmp[] = $theData[ 'CK:NUMB' ];
		if( array_key_exists( 'CK:TYPE', $theData ) )
			$tmp[] = $theData[ 'CK:TYPE' ];
		if( count( $tmp ) )
			$theObject->offsetSet( ':unit:identifier', implode( '-', $tmp ) );
		
		//
		// Set version.
		//
		if( array_key_exists( 'Version', $theData ) )
			$theObject->offsetSet( ':unit:version', $theData[ 'Version' ] );
		
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet( ':inventory:dataset', $theData[ 'Dataset' ] );
		
		//
		// Set inventory code.
		//
		$theObject->offsetSet( ':inventory:code', $theData[ 'CK:CWRCODE' ] );
		
		//
		// Set inventory administrative unit.
		//
		$value = $theData[ 'CK:CWRCODE' ];
		if( strlen( $value ) == 3 )
			$theObject->offsetSet( ':inventory:admin', "iso:3166:1:alpha-3:$value" );
		else
			$theObject->offsetSet( ':inventory:admin', "iso:3166:2:$value" );
		
		//
		// Set inventory institute.
		//
		if( array_key_exists( 'CK:INSTCODE', $theData ) )
			$theObject->offsetSet(
				':inventory:institute',
				kDOMAIN_ORGANISATION
			   .'://http://fao.org/wiews:'
			   .$theData[ 'CK:INSTCODE' ]
			   .kTOKEN_END_TAG );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set checklist code.
		//
		$theObject->offsetSet( 'cwr:ck:CWRCODE', $theData[ 'CK:CWRCODE' ] );
		
		//
		// Set checklist number.
		//
		if( array_key_exists( 'CK:NUMB', $theData ) )
			$theObject->offsetSet( 'cwr:ck:NUMB', $theData[ 'CK:NUMB' ] );
		
		//
		// Set checklist institute.
		//
		if( array_key_exists( 'CK:INSTCODE', $theData ) )
			$theObject->offsetSet( 'cwr:INSTCODE', $theData[ 'CK:INSTCODE' ] );
		
		//
		// Set checklist type.
		//
		if( array_key_exists( 'CK:TYPE', $theData ) )
			$theObject->offsetSet( 'cwr:ck:TYPE',
								   'cwr:ck:TYPE:'.$theData[ 'CK:TYPE' ] );
		
		//
		// Set checklist references.
		//
		if( array_key_exists( 'CK:REF', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:REF' ], '§' ) ) )
				$theObject->offsetSet( 'cwr:REF', $tmp );
		}
		
		//
		// Set checklist URLs.
		//
		if( array_key_exists( 'CK:URL', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:REF' ], '§' ) ) )
				$theObject->offsetSet( 'cwr:URL', $tmp );
		}
		
		//
		// Set checklist priority.
		//
		if( array_key_exists( 'CK:CRITPRIORI', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:CRITPRIORI' ], ';' ) ) )
			{
				$list = Array();
				foreach( $tmp as $item )
					$list[] = "cwr:in:CRITPRIORI:$item";
				$theObject->offsetSet( 'cwr:in:CRITPRIORI', $list );
			}
		}
		
		//
		// Set taxon ID.
		//
		// CK:TAXONID
		
		//
		// Set regnum.
		//
		if( array_key_exists( 'CK:KINGDOM', $theData ) )
			$theObject->offsetSet( ':taxon:regnum', $theData[ 'CK:KINGDOM' ] );
		//
		// Set phylum.
		//
		if( array_key_exists( 'CK:PHYLUM/DIVISION', $theData ) )
			$theObject->offsetSet( ':taxon:phylum', $theData[ 'CK:PHYLUM/DIVISION' ] );
		//
		// Set classis.
		//
		if( array_key_exists( 'CK:CLASS', $theData ) )
			$theObject->offsetSet( ':taxon:classis', $theData[ 'CK:CLASS' ] );
		//
		// Set ordo.
		//
		if( array_key_exists( 'CK:ORDER', $theData ) )
			$theObject->offsetSet( ':taxon:ordo', $theData[ 'CK:ORDER' ] );
		//
		// Set familia.
		//
		if( array_key_exists( 'CK:FAMILY', $theData ) )
			$theObject->offsetSet( ':taxon:familia', $theData[ 'CK:FAMILY' ] );
		//
		// Set subfamilia.
		//
		if( array_key_exists( 'CK:SUBFAMILY', $theData ) )
			$theObject->offsetSet( ':taxon:subfamilia', $theData[ 'CK:SUBFAMILY' ] );
		//
		// Set tribus.
		//
		if( array_key_exists( 'CK:TRIBE', $theData ) )
			$theObject->offsetSet( ':taxon:tribus', $theData[ 'CK:TRIBE' ] );
		//
		// Set subtribus.
		//
		if( array_key_exists( 'CK:SUBTRIBE', $theData ) )
			$theObject->offsetSet( ':taxon:subtribus', $theData[ 'CK:SUBTRIBE' ] );
		//
		// Set genus.
		//
		if( array_key_exists( 'CK:GENUS', $theData ) )
			$theObject->offsetSet( ':taxon:genus', $theData[ 'CK:GENUS' ] );
		//
		// Set species.
		//
		if( array_key_exists( 'CK:SPECIES', $theData ) )
			$theObject->offsetSet( ':taxon:species', $theData[ 'CK:SPECIES' ] );
		//
		// Set species:author.
		//
		if( array_key_exists( 'CK:SPAUTHOR', $theData ) )
			$theObject->offsetSet( ':taxon:species:author', $theData[ 'CK:SPAUTHOR' ] );
		//
		// Set infraspecies.
		//
		if( array_key_exists( 'CK:SUBTAXON', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies',
								   $theData[ 'CK:SUBTAXON' ] );
		//
		// Set infraspecies:author.
		//
		if( array_key_exists( 'CK:SUBTAUTHOR', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies:author',
								   $theData[ 'CK:SUBTAUTHOR' ] );
		
		//
		// Set sample species name.
		//
		if( array_key_exists( 'CK:GENUS', $theData )
		 && array_key_exists( 'CK:SPECIES', $theData ) )
			$theObject->offsetSet(
				':taxon:species:name',
				implode( ' ', array( $theData[ 'CK:GENUS' ],
									 $theData[ 'CK:SPECIES' ] ) ) );
		
		//
		// Set epithet.
		//
		if( $taxon !== NULL )
			$theObject->offsetSet( ':taxon:epithet', $taxon );
		
		//
		// Set taxon reference.
		//
		if( array_key_exists( 'CK:TAXREF', $theData ) )
			$theObject->offsetSet( ':taxon:reference',
								   array( $theData[ 'CK:TAXREF' ] ) );
		
		//
		// Set taxon reference ID.
		//
		// CK:TAXREFID
		
		//
		// Set taxon synonyms.
		//
		// CK:SYNONYMS
		
		//
		// Set taxon synonyms reference.
		//
		// CK:SYNREF
		
		//
		// Set vernacular names.
		//
		if( array_key_exists( 'CK:COMMONTAXONNAME', $theData ) )
		{
			$tmp = setLangStrings( $theData[ 'CK:COMMONTAXONNAME' ] );
			if( $tmp !== NULL )
				$theObject->offsetSet( ':taxon:names', array( $tmp ) );
		}
		
		//
		// Set endemism.
		//
		if( array_key_exists( 'CK:ENDEMISM', $theData ) )
		{
			switch( $tmp = trim( $theData[ 'CK:ENDEMISM' ] ) )
			{
				case '0':
					$sub[ getTag( 'cwr:ENDEMISM' ) ] = 'cwr:ENDEMISM:0';
					break;
				case '1':
					$sub[ getTag( 'cwr:ENDEMISM' ) ] = 'cwr:ENDEMISM:1';
					break;
			}
		}
		
		//
		// Set chromosome number.
		//
		if( array_key_exists( 'CK:CHROMOSNUMB', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:CHROMOSNUMB' ], ';' ) ) )
				$theObject->offsetSet( ':taxon:chromosome-number', $tmp );
		}
		
		//
		// Set gene pool.
		//
		if( array_key_exists( 'CK:GENEPOOL', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:GENEPOOL' ], ';' ) ) )
				$theObject->offsetSet( ':taxon:genepool', $tmp );
		}
		
		//
		// Set gene pool reference.
		//
		if( array_key_exists( 'CK:GENEPOOLREF', $theData ) )
			$theObject->offsetSet( ':taxon:genepool-ref',
								   $theData[ 'CK:GENEPOOLREF' ] );
		
		//
		// Set taxon group.
		//
		if( array_key_exists( 'CK:TAXONGROUP', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:TAXONGROUP' ], ';' ) ) )
				$theObject->offsetSet( ':taxon:group', $tmp );
		}
		
		//
		// Set taxon group reference.
		//
		if( array_key_exists( 'CK:REFTAXONGROUP', $theData ) )
			$theObject->offsetSet( ':taxon:group-ref',
								   $theData[ 'CK:REFTAXONGROUP' ] );
		
		//
		// Set taxon designation use.
		//
		if( array_key_exists( 'CK:USEOFTAXON', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:USEOFTAXON' ], ';' ) ) )
				$theObject->offsetSet( ':taxon:designation:use', $tmp );
		}
		
		//
		// Load threat data.
		//
		$sub = Array();
		loadThreat(	$sub,
					$theData,
					$theWrapper,
					$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':taxon:threat', $sub );
		
		//
		// Set checklist remarks.
		//
		if( array_key_exists( 'REMARKS', $theData ) )
			$theObject->offsetSet( 'cwr:REMARKS', $theData[ 'REMARKS' ] );

	} // loadUnit.
	

	/**
	 * Load distribution data.
	 *
	 * This function will load the threat data related to the provided <b>$theData</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theData			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadDistribution( &$theContainer, $theData, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$sub = Array();
		
		//
		// Set taxon status
		//
		if( array_key_exists( 'CK:TAXONSTATUS', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:TAXONSTATUS' ], ';' ) ) )
			{
				$value = Array();
				foreach( $tmp as $item )
				{
					switch( $theData[ 'CK:TAXONSTATUS' ] )
					{
						case '1':
							$value[] = ":taxon:occurrence-status:100";
							break;
						case '2':
							$value[] = ":taxon:occurrence-status:130";
							break;
						case '3':
							$value[] = ":taxon:occurrence-status:200";
							break;
						case '4':
							$value[] = ":taxon:occurrence-status:300";
							break;
						case '5':
							$value[] = ":taxon:occurrence-status:400";
							break;
						case '6':
							$value[] = ":taxon:occurrence-status:490";
							break;
					}
				}
		
				if( count( $value ) )
					$sub[ getTag( ':taxon:occurrence-status' ) ]
						= $value;
			}
		}
		
		//
		// Set taxon economic value.
		//
		if( array_key_exists( 'CK:ECOVALUE', $theData ) )
			$sub[ getTag( ':taxon:ecovalue' ) ]
				= array( $theData[ 'CK:ECOVALUE' ] );
		
		//
		// Set taxon economic value reference.
		//
		if( array_key_exists( 'CK:ECOVALUEREF', $theData ) )
			$sub[ getTag( ':taxon:ecovalue-ref' ) ]
				= array( $theData[ 'CK:ECOVALUEREF' ] );
		
		//
		// Set taxon occurrence notes.
		//
		if( array_key_exists( ':taxon:occurrence-notes', $theData ) )
			$sub[ getTag( ':taxon:occurrence-notes' ) ]
				= $theData[ ':taxon:occurrence-notes' ];
		
		//
		// Handle fixed data.
		//
		if( count( $sub ) )
		{
			//
			// Set country and administrative unit.
			//
			$value = $theData[ 'CK:CWRCODE' ];
			if( strlen( $value ) == 3 )
			{
				$sub[ getTag( ':location:country' ) ] = "iso:3166:1:alpha-3:$value";
				$sub[ getTag( ':inventory:admin' ) ] = "iso:3166:1:alpha-3:$value";
			}
			else
			{
				$sub[ getTag( ':location:country' ) ] = "iso:3166:1:alpha-3:GBR";
				$sub[ getTag( ':inventory:admin' ) ] = "iso:3166:2:$value";
			}
		
			//
			// Set structure label.
			//
			$sub[ kTAG_STRUCT_LABEL ] = $value;
		}
		
		//
		// Load sub-structure.
		//
		if( count( $sub ) )
			$theContainer[] = $sub;

	} // loadDistribution.
	

	/**
	 * Load threat data.
	 *
	 * This function will load the threat data related to the provided <b>$theData</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theData			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadThreat( &$theContainer, $theData, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$sub = Array();
		
		//
		// Set assessment level.
		//
		if( array_key_exists( 'CK:ASSLEVEL', $theData ) )
			$sub[ getTag( ':taxon:threat:assessment' ) ]
				= ':taxon:threat:assessment:'.$theData[ 'CK:ASSLEVEL' ];
		
		//
		// Set assessment region.
		//
		if( array_key_exists( 'CK:REGIONASS', $theData ) )
			$sub[ getTag( ':location:region' ) ]
				= $theData[ 'CK:REGIONASS' ];
		
		//
		// Set iucn category
		//
		if( array_key_exists( 'CK:IUCNCAT', $theData ) )
		{
			if( count( $list = setList( $theData[ 'CK:IUCNCAT' ], ';' ) ) )
			{
				$value = Array();
				foreach( $list as $element )
				{
					switch( $element )
					{
						case '1':
							$element = 'EX';
							break;
						case '2':
							$element = 'EW';
							break;
						case '3':
							$element = 'CR';
							break;
						case '4':
							$element = 'EN';
							break;
						case '5':
							$element = 'VU';
							break;
						case '6':
							$element = 'NT';
							break;
						case '7':
							$element = 'LC';
							break;
						case '8':
							$element = 'DD';
							break;
						case '9':
							$element = 'NE';
							break;
						case '10':
							$element = NULL;
							break;
					}
					if( $element !== NULL )
						$value[] = "iucn:category:$element";
				}
				if( count( $value ) )
					$sub[ getTag( 'iucn:category' ) ]
						= $value;
			}
		}
		
		//
		// Set IUCN criteria citation.
		//
		if( array_key_exists( 'CK:IUCNCRIT', $theData ) )
			$sub[ getTag( 'iucn:criteria-citation' ) ]
			 = $theData[ 'CK:IUCNCRIT' ];
		
		//
		// Set IUCN threat class.
		//
		if( array_key_exists( 'CK:IUCNTHREATCLSS', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:IUCNTHREATCLSS' ], ';' ) ) )
			{
				$list = Array();
				foreach( $tmp as $item )
					$list[] = "iucn:threat:$item";
				$sub[ getTag( 'iucn:threat' ) ]
				 = $list;
			}
		}
		
		//
		// Set IUCN occurrence period.
		//
		if( array_key_exists( 'CK:OCCURTHREAT', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:OCCURTHREAT' ], ';' ) ) )
			{
				$list = Array();
				foreach( $tmp as $item )
					$list[] = ":taxon:threat:period:$item";
				$sub[ getTag( ':taxon:threat:period' ) ]
				 = $list;
			}
		}
		
		//
		// Set other red list category.
		//
		if( array_key_exists( 'CK:REDLISTCAT', $theData ) )
			$sub[ getTag( ':taxon:threat:other-red-list-criteria' ) ]
			 = $theData[ 'CK:REDLISTCAT' ];
		
		//
		// Set red list URL.
		//
		if( array_key_exists( 'CK:URLPUBREDLISTASS', $theData ) )
			$sub[ getTag( ':taxon:threat:assessment:url' ) ]
			 = $theData[ 'CK:URLPUBREDLISTASS' ];
		
		//
		// Set red list references.
		//
		if( array_key_exists( 'CK:REFREDLISTASS', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'CK:REFREDLISTASS' ], ';' ) ) )
				$sub[ getTag( ':taxon:threat:assessment:ref' ) ]
					= $tmp;
		}
		
		//
		// Set structure label.
		//
		if( array_key_exists( 'CK:REGIONASS', $theData ) )
			$sub[ kTAG_STRUCT_LABEL ] = $theData[ 'CK:REGIONASS' ];
		elseif( array_key_exists( 'CK:IUCNCRIT', $theData ) )
			$sub[ kTAG_STRUCT_LABEL ] = $theData[ 'CK:IUCNCRIT' ];
		elseif( array_key_exists( 'CK:REDLISTCAT', $theData ) )
			$sub[ kTAG_STRUCT_LABEL ] = $theData[ 'CK:REDLISTCAT' ];
		elseif( count( $sub ) )
			$sub[ kTAG_STRUCT_LABEL ] = 'threat';

		//
		// Load sub-structure.
		//
		if( count( $sub ) )
			$theContainer[] = $sub;

	} // loadThreat.
	

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
	

	/**
	 * Set language strings.
	 *
	 * This function will return a language strings record.
	 *
	 * @param string				$theString			Strings.
	 * @return array				Language strings or an empty array.
	 */
	function setLangStrings( $theString )
	{
		//
		// Init local storage.
		//
		$list = Array();
		$lang = $name = NULL;
		
		//
		// Split language.
		//
		$item = setList( $theString, '@' );
		
		//
		// Handle no language.
		//
		if( count( $item ) == 1 )
			$names = setList( $item[ 0 ], ';' );
		
		//
		// Handle language.
		//
		elseif( count( $item ) == 2 )
		{
			$lang = $item[ 0 ];
			$names = setList( $item[ 1 ], ';' );
		}
		
		//
		// Return property.
		//
		if( count( $names ) )
		{
			//
			// Handle language.
			//
			if( $lang !== NULL )
				$list[ kTAG_LANGUAGE ] = $lang;
			
			//
			// Set names.
			//
			$list[ kTAG_TEXT ] = $names;
		}
			
		return $list;																// ==>

	} // setLangStrings.
	

	/**
	 * Set list.
	 *
	 * This function will return a trimmed list.
	 *
	 * @param string				$theString			Strings.
	 * @param string				$theDivider			List divider.
	 * @return array				Trimmed list or an empty array.
	 */
	function setList( $theString, $theDivider )
	{
		//
		// Init local storage.
		//
		$list = Array();
		
		//
		// Split string.
		//
		foreach( explode( $theDivider, $theString ) as $item )
		{
			$item = trim( $item );
			if( strlen( $item ) )
				$list[] = $item;
		}
		
		return $list;																// ==>

	} // setList.

?>
