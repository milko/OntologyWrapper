<?php

/**
 * Data initialisation procedure (test version).
 *
 * This file contains routines to initialise the data.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 28/05/2014
 */

/*=======================================================================================
 *																						*
 *										Test.php										*
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
	$meta = $wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Drop metadata.
	//
//	$meta->drop();
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$units = $wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Drop units.
	//
//	$units->drop();
	
	//
	// Set entities.
	//
	echo( "  • Setting entities.\n" );
	$entities = $wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Drop entities.
	//
//	$entities->drop();
	
	//
	// Check graph database.
	//
	if( kGRAPH_DO )
	{
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
//		echo( "  • Resetting graph.\n" );
//		$graph->drop( kGRAPH_DIR.'*', kGRAPH_SERVICE );
	
	} // Use graph database.
	
	//
	// Reset ontology.
	//
//	$wrapper->resetOntology( TRUE );
	
	//
	// Load ISO Standards.
	//
//	$wrapper->initISOStandards( TRUE );
	
	//
	// Load WBI Standards.
	//
//	$wrapper->initWBIStandards( TRUE );
	
	//
	// Load standards.
	//
//	$wrapper->loadStandards( TRUE );
//	$wrapper->loadFCUStandards( TRUE );
	
	//
	// Load units.
	//
//	$wrapper->resetUnits( TRUE );
	
	//
	// Load entities.
	//
//	$wrapper->resetEntities( TRUE );
	
/*
	//
	// Get units collection.
	//
	$collection = $units->collection( UnitObject::kSEQ_NAME, TRUE );
	
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
*/
	//
	// Load FAO institutes.
	//
	echo( "  • Loading FAO Institutes.\n" );
	$ok = OntologyWrapper\FAOInstitute::Maintain( $wrapper );
	var_dump( $ok );
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
