<?php

/**
 * XML file parser.
 *
 * This file contains the script to load a single XML file from terminal arguments.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 04/06/2014
 */

/*=======================================================================================
 *																						*
 *									LoadXMLFile.php										*
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
if( $argc < 3 )
	exit( "Usage: script.php [XML file path] [database DSN] [graph DSN].\n" );		// ==>

//
// Load arguments.
//
$file = $argv[ 1 ];
$database = $argv[ 2 ];
$graph = ( $argc > 3 ) ? $argv[ 3 ] : NULL;
 
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
	// Inform.
	//
	echo( "  • Creating database.\n" );
	
	//
	// Instantiate database.
	//
	$database
		= new OntologyWrapper\MongoDatabase(
			"$database?connect=1" );
	
	//
	// Set metadata.
	//
	echo( "  • Setting metadata.\n" );
	$wrapper->Metadata( $database );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$wrapper->Units( $database );
	
	//
	// Set entities.
	//
	echo( "  • Setting entities.\n" );
	$wrapper->Entities( $database );
	
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
	// Load default XML files.
	//
	echo( "  • Loading file\n" );
	echo( "    - $file\n" );
	$wrapper->loadXMLFile( $file );
		
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
