<?php

/**
 * Base data initialisation procedure.
 *
 * This file contains routines to initialise base data, which includes the default and major
 * standards.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2014
 */

/*=======================================================================================
 *																						*
 *									Init_Base.php										*
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
	echo( "  • Connecting metadata database.\n" );
	$meta = $wrapper->metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
	//
	// Drop metadata.
	//
	echo( "  • Resetting metadata database.\n" );
	$meta->drop();
	
	//
	// Set units.
	//
	echo( "  • Connecting units database.\n" );
	$units = $wrapper->units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
	//
	// Drop units.
	//
	echo( "  • Resetting units database.\n" );
	$units->drop();
	
	//
	// Set users.
	//
	echo( "  • Connecting users database.\n" );
	$users = $wrapper->users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
	//
	// Drop users.
	//
	echo( "  • Resetting users database.\n" );
	$users->drop();
	
	//
	// Check graph database.
	//
	if( kGRAPH_DO )
	{
		//
		// Set graph database.
		//
		echo( "  • Connecting graph database.\n" );
		$graph = $wrapper->graph(
			new OntologyWrapper\Neo4jGraph(
				"neo4j://localhost:7474" ) );
	
		//
		// Drop graph database.
		//
		echo( "  • Resetting graph database.\n" );
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
	// Load NaturalServe Standards.
	//
	$wrapper->loadNatServeStandards( TRUE );
	
	//
	// Load FAO Standards.
	//
	$wrapper->loadFAOStandards( TRUE );
	
	//
	// Load EEC Standards.
	//
	$wrapper->loadEECStandards( TRUE );
	
	//
	// Load GENS Standards.
	//
	$wrapper->loadGENSStandards( TRUE );
	
	//
	// Load GLOBCOV Standards.
	//
	$wrapper->loadGLOBCOVStandards( TRUE );
	
	//
	// Load HWSD Standards.
	//
	$wrapper->loadHWSDStandards( TRUE );
	
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
