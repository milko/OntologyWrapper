<?php

/**
 * Test unit initialisation.
 *
 * This file contains a script to create the test unit object.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Batch
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/01/2015
 */

/*=======================================================================================
 *																						*
 *									LoadTestUnit.php									*
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
// Session includes.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: "
		 ."script.php "
		 ."[mongo database DSN] "	// mongodb://localhost:27017/BIOVERSITY
		 ."[graph DSN].\n" );		// neo4j://localhost:7474						// ==>

//
// Load arguments.
//
$database = $argv[ 1 ];
$graph = ( $argc > 2 ) ? $argv[ 2 ] : NULL;
 
//
// Test class.
//
try
{
	//
	// Instantiate wrapper.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( kSTANDARDS_DDICT_HOST, kSTANDARDS_DDICT_PORT ) ) );

	//
	// Set databases.
	//
	$wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			"$database?connect=1" ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			"$database?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"$database?connect=1" ) );
	
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
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Delete object.
	//
	$ok = OntologyWrapper\UnitObject::Delete(
			$wrapper,
			':domain:unit://Authority/Collection:Identifier/Version;' );
	if( $ok )
		echo( "  • Deleted [$ok].\n" );
	
	//
	// Instantiate object.
	//
	$object = new OntologyWrapper\TestUnit( $wrapper );
	
	//
	// Set identifiers.
	//
	$object[ kTAG_AUTHORITY ] = 'Authority';
	$object[ kTAG_COLLECTION ] = 'Collection';
	$object[ kTAG_IDENTIFIER ] = 'Identifier';
	$object[ kTAG_VERSION ] = 'Version';
	
	//
	// Set string property.
	//
	$object[ kTAG_ID_LOCAL ] = 'String';
	$object[ kTAG_SYNONYM ] = array( 's1', 's2', 's3' );
	
	//
	// Set integer property.
	//
	$object[ kTAG_ID_SEQUENCE ] = 1;
	$object[ ':environment:ghf' ] = array( 1, 2, 3 );
	
	//
	// Set float property.
	//
	$object[ kTAG_MIN_VAL ] = 1.23;
	
	//
	// Set boolean property.
	//
	$object[ ':location:site:coordinates-restricted' ] = TRUE;
	$object[ 'lr:EXSECURE' ] = FALSE;
	
	//
	// Set language string.
	//
	$object[ kTAG_LABEL ] = array( array( kTAG_LANGUAGE => 'it',
										  kTAG_TEXT => 'Italiano' ),
								   array( kTAG_LANGUAGE => 'en',
										  kTAG_TEXT => 'English' ),
								   array( kTAG_LANGUAGE => 'fr',
										  kTAG_TEXT => 'French' ) );
	
	//
	// Commit object.
	//
	$object->commit();

	echo( "\nDone!\n" );

}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( '<pre>'.$error->xdebug_message.'</pre>' );
}

?>
