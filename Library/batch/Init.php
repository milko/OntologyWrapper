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
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Set databases.
	//
	$meta = $wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	$meta->drop();
	$entities = $wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	$entities->drop();
	$units = $wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	$units->drop();
	
	//
	// Set graph database.
	//
	$graph = $wrapper->Graph(
		new OntologyWrapper\Neo4jGraph(
			"neo4j://localhost:7474" ) );
//	$graph->drop( '/Volumes/Data/Neo4j/*' );
	
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
	// Load entities.
	//
	$wrapper->initEntities( TRUE );
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
