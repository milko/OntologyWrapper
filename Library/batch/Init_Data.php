<?php

/**
 * Data initialisation procedure.
 *
 * This file contains routines to initialise data.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2014
 */

/*=======================================================================================
 *																						*
 *									Init_Data.php										*
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
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$units = $wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
	//
	// Set users.
	//
	echo( "  • Setting users.\n" );
	$users = $wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/PGRDG?connect=1" ) );
	
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
	// Reset units.
	//
	$wrapper->openConnections();
	$wrapper->resetUnits( TRUE );
	
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
}

echo( "\nDone!\n" );

?>
