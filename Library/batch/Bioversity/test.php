<?php

/**
 * Build units indexes.
 *
 * This file contains a script to build unit indexes.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 02/10/2014
 */

/*=======================================================================================
 *																						*
 *										test.php										*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// local includes.
//
require_once( 'local.inc.php' );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// MAIN.
//
try
{
	//
	// Get arguments.
	//
	$database = 'mongodb://localhost:27017/BIOVERSITY';
	$tag = kTAG_OBJECT_OFFSETS;
	$background = TRUE;
	
	//
	// Parse database.
	//
	$parts = parse_url( $database );
	
	//
	// Inform.
	//
	echo( "\n"
		 ."Tag:              $tag\n"
		 ."Host:             ".$parts[ 'host' ]."\n"
		 ."Port:             ".$parts[ 'port' ]."\n"
		 ."Database:         ".substr( $parts[ 'path' ], 1 )."\n"
		 ."Background build: "
		 .( ( $background ) ? "Yes" : "No" )
		 ."\n" );
	
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
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase( $database ) );
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper ) );
	
	//
	// Create index.
	//
	$collection->createIndex( array( kTAG_OBJECT_OFFSETS => 1 ),
							  array( "name" => "OFFSETS" ) );
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
