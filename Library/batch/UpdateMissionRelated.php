<?php

/**
 * SQL archive load procedure.
 *
 * This file contains routines to load ovjects from the XML SQL archive.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/08/2014
 */

/*=======================================================================================
 *																						*
 *								UpdateMissionRelated.php								*
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
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

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
if( $argc < 2 )
	exit( "Usage: <script.php> "
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// neo4j://localhost:7474 or ""
				."[graph DSN]\n" );													// ==>

//
// Init local storage.
//
$start = 0;
$limit = 100;
$query = array( kTAG_DOMAIN => kDOMAIN_MISSION );

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$graph = ( ($argc > 2) && strlen( $argv[ 2 ] ) ) ? $argv[ 2 ] : NULL;

//
// Inform.
//
echo( "\n==> Updating collecting missions in missions.\n" );

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
	echo( "  • Creating databases.\n" );
	
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
				$wrapper, TRUE ) );
	
	//
	// Update.
	//
	echo( "  • Updating\n" );
	$rs = $collection->matchAll( $query, kQUERY_OBJECT );
	if( $start )
		$rs->skip( $start );
	$rs->limit( $limit );
	while( $rs->count() )
	{
		//
		// Iterate page.
		//
		foreach( $rs as $object )
			$object->commit( NULL, kFLAG_OPT_REL_MANY );
		
		//
		// Inform.
		//
		echo( '.' );
		
		//
		// Read next.
		//
		$start += $limit;
		$rs = $collection->matchAll( $query, kQUERY_OBJECT );
		if( $start )
			$rs->skip( $start );
		$rs->limit( $limit );
	
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

?>
