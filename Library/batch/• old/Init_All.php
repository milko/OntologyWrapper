<?php

/**
 * Data initialisation procedure.
 *
 * This file contains routines to initialise all data.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2014
 */

/*=======================================================================================
 *																						*
 *									Init_All.php										*
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
// Try.
//
try
{
	//
	// Inform.
	//
	echo( "\n==> Connecting.\n" );
	echo( "  • Creating wrapper.\n" );
	
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Set metadata.
	//
	echo( "  • Setting metadata.\n" );
	$meta = $wrapper->metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Drop metadata.
	//
	$meta->drop();
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$units = $wrapper->units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Drop units.
	//
	$units->drop();
	
	//
	// Set users.
	//
	echo( "  • Setting users.\n" );
	$users = $wrapper->users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Drop users.
	//
	$users->drop();
	
	//
	// Check graph database.
	//
	if( kGRAPH_DO )
	{
		//
		// Set graph database.
		//
		echo( "  • Setting graph.\n" );
		$graph = $wrapper->graph(
			new OntologyWrapper\Neo4jGraph(
				"neo4j://localhost:7474" ) );
	
		//
		// Drop graph database.
		//
		echo( "  • Resetting graph.\n" );
		$graph->drop( kGRAPH_DIR.'*', kGRAPH_SERVICE );
	
	} // Use graph database.
	
	//
	// Reset ontology.
	//
	$wrapper->resetOntology( TRUE );
	
	//
	// Load ISO Standards.
	//
	$wrapper->loadISOStandards( TRUE );
	
	//
	// Load WBI Standards.
	//
	$wrapper->loadWBIStandards( TRUE );
	
	//
	// Load IUCN Standards.
	//
	$wrapper->loadIUCNStandards( TRUE );
	
	//
	// Load standards.
	//
	$wrapper->loadStandards( TRUE );
	
	//
	// Load MCPD standards.
	//
	$wrapper->loadMCPDStandards( TRUE );
	
	//
	// Load FCU standards.
	//
	$wrapper->loadFCUStandards( TRUE );
	
	//
	// Load CWR standards.
	//
	$wrapper->loadCWRStandards( TRUE );
	
	//
	// Reset units.
	//
	$wrapper->resetUnits( TRUE );
	
	//
	// Reset users.
	//
	$wrapper->resetUsers( TRUE );
	
	//
	// Get units collection.
	//
	$collection = $units->collection( OntologyWrapper\UnitObject::kSEQ_NAME, TRUE );
	
	//
	// Set country index.
	//
	$collection->createIndex(
		array( $wrapper->getSerial( ':location:country', TRUE ) => 1 ),
		array( "name" => "COUNTRY" ) );
	
	//
	// Set administrative unit index.
	//
	$collection->createIndex(
		array( $wrapper->getSerial( ':location:admin', TRUE ) => 1 ),
		array( "name" => "ADMIN" ) );
	
	//
	// Reset dictionary.
	//
	$wrapper->loadTagCache();
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );
}

echo( "\nDone!\n" );

?>
