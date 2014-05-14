<?php

/**
 * Data initialisation procedure.
 *
 * This file contains routines to initialise the data.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2014
 */

/*=======================================================================================
 *																						*
 *										Init.php										*
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
	// Set databases.
	//
	echo( "  • Setting metadata.\n" );
	$meta = $wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	$meta->drop();
	echo( "  • Setting entities.\n" );
	$entities = $wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	$entities->drop();
	echo( "  • Setting units.\n" );
	$units = $wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	$units->drop();
	
	//
	// Set graph database.
	//
	echo( "  • Setting graph.\n" );
	$graph = $wrapper->Graph(
		new OntologyWrapper\Neo4jGraph(
			"neo4j://localhost:7474" ) );
	
	//
	// Drop graph database.
	//
	echo( "  • Resetting graph.\n" );
	$graph->drop( kGRAPH_DIR.'*', kGRAPH_SERVICE );
	
	//
	// Reset ontology.
	//
	$wrapper->resetOntology( TRUE );
	
	//
	// Load ISO Standards.
	//
	$wrapper->initISOStandards( TRUE );
	
	//
	// Load WBI Standards.
	//
	$wrapper->initWBIStandards( TRUE );
	
	//
	// Load standards.
	//
	$wrapper->loadStandards( TRUE );
	
	//
	// Load entities.
	//
	$wrapper->resetEntities( TRUE );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
}

echo( "\nDone!\n" );

?>
