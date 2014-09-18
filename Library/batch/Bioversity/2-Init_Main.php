<?php

/**
 * Main data initialisation procedure.
 *
 * This file contains routines to initialise main data, which includes standards metadata
 * and organisation units.
 *
 * <em>Note that this implies that you call InitBase.php before</em>.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2014
 */

/*=======================================================================================
 *																						*
 *									Init_Main.php										*
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
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$units = $wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
	//
	// Set entities.
	//
	echo( "  • Setting entities.\n" );
	$entities = $wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
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
	
	} // Use graph database.
	
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
	// Load ABDH standards.
	//
	$wrapper->loadABDHStandards( TRUE );
	
	//
	// Load collections.
	//
	$wrapper->loadCollections( TRUE );
	
	//
	// Reset units.
	//
	$wrapper->resetUnits( TRUE );
	
	//
	// Reset entities.
	//
	$wrapper->resetEntities( TRUE );
	
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
