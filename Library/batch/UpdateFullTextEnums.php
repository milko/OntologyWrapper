<?php

/**
 * MCPD (CWR) load procedure.
 *
 * This file contains routines to update the objects of the database, this script will scan
 * all objects that do not feature the {@link kTAG_ENUM_FULL_TEXT} property, load them and
 * update them.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 30/07/2014
 */

/*=======================================================================================
 *																						*
 *								UpdateFullTextEnums.php									*
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


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: "
		 ."script.php "
		 ."[mongo database DSN] "	// mongodb://localhost:27017/PGRDG
		 ."[graph DSN].\n" );		// neo4j://localhost:7474						// ==>

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$graph = ( $argc > 2 ) ? $argv[ 2 ] : NULL;

//
// Init local storage.
//
$limit = 10000;
 
//
// Inform.
//
echo( "\n==> Updating full-text enumerated values for full-text search.\n" );

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
			OntologyWrapper\UnitObject::ResolveDatabase( $wrapper ) );
	
	//
	// Read.
	//
	echo( "  • Scanning...\n" );
	$criteria = array( kTAG_ENUM_FULL_TEXT => array( '$exists' => FALSE ) );
	$rs = $collection->matchAll( $criteria, kQUERY_OBJECT );
	
	//
	// Iterate.
	//
	while( $rs->count() )
	{
		//
		// Inform.
		//
		echo( "    ".$rs->count()." records.\n" );
		
		//
		// Set limit.
		//
		$rs->limit( $limit );
		
		//
		// Iterate recordset.
		//
		foreach( $rs as $object )
			$object->commit( $wrapper );
		
		//
		// Read.
		//
		$rs = $collection->matchAll( $criteria, kQUERY_OBJECT );
	
	} // Iterated.

	echo( "\nDone!\n" );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );
}

//
// FINAL BLOCK.
//
finally
{
}

?>
