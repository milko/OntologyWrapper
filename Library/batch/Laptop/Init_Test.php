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
 *									Init_Test.php										*
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
			"mongodb://localhost:27017/TEST?connect=1" ) );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$units = $wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	
	//
	// Set entities.
	//
	echo( "  • Setting entities.\n" );
	$entities = $wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	
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
	// Inform.
	//
	echo( "  • Loading test properties.\n" );
	
	//
	// Load XML schema files.
	//
	$file = kPATH_STANDARDS_ROOT.'/test/Namespaces.xml';
	echo( "    - $file\n" );
	$wrapper->loadXMLFile( $file );
	
	$file = kPATH_STANDARDS_ROOT.'/test/Attributes.xml';
	echo( "    - $file\n" );
	$wrapper->loadXMLFile( $file );
	
	$file = kPATH_STANDARDS_ROOT.'/test/Types.xml';
	echo( "    - $file\n" );
	$wrapper->loadXMLFile( $file );
	
	$file = kPATH_STANDARDS_ROOT.'/test/Tags.xml';
	echo( "    - $file\n" );
	$wrapper->loadXMLFile( $file );
	
	//
	// Load XML data files.
	//
	$file = kPATH_STANDARDS_ROOT.'/test/Data.xml';
	echo( "    - $file\n" );
	$wrapper->loadXMLFile( $file );
	
	//
	// Get units collection.
	//
	$collection = $units->collection( OntologyWrapper\UnitObject::kSEQ_NAME, TRUE );
	
	//
	// Set test indexes.
	//
	$collection->createIndex(
		array( $wrapper->getSerial( ':test:feature2', TRUE ) => 1 ),
		array( "name" => "TEST_INDEX_1" ) );
	
	$collection->createIndex(
		array( $wrapper->getSerial( ':test:feature2/:predicate:SCALE-OF/:test:scale1', TRUE ) => 1 ),
		array( "name" => "TEST_INDEX_2" ) );
	
	$collection->createIndex(
		array( $wrapper->getSerial( ':test:feature2/:predicate:SCALE-OF/:test:scale2', TRUE ) => 1 ),
		array( "name" => "TEST_INDEX_3" ) );
	
	$collection->createIndex(
		array( $wrapper->getSerial( ':test:feature2/:predicate:SCALE-OF/:test:scale3', TRUE ) => 1 ),
		array( "name" => "TEST_INDEX_4" ) );
	
	$collection->createIndex(
		array( $wrapper->getSerial( ':test:feature5', TRUE ) => 1 ),
		array( "name" => "TEST_INDEX_5" ) );
	
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
	echo( "\n\nTRACE:\n" );
	print_r( $error->getTrace() );
}

echo( "\nDone!\n" );

?>
