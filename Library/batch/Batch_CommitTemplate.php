<?php

/**
 * Commit upload batch.
 *
 * This file contains routines to commit a template.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Batch
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 23/03/2015
 */

/*=======================================================================================
 *																						*
 *								Batch_CommitTemplate.php								*
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
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: <script.php> <session ID>\n" );									// ==>
$session_id = $argv[ 1 ];


/*=======================================================================================
 *	INIT																				*
 *======================================================================================*/
 
//
// Instantiate data dictionary.
//
$wrapper
	= new OntologyWrapper\Wrapper(
		kSESSION_DDICT,
		array( array( kSTANDARDS_DDICT_HOST,
					  kSTANDARDS_DDICT_PORT ) ) );

//
// Set metadata.
//
$meta = $wrapper->metadata(
	new OntologyWrapper\MongoDatabase(
		kSTANDARDS_METADATA_DB ) );

//
// Set units.
//
$units = $wrapper->units(
	new OntologyWrapper\MongoDatabase(
		kSTANDARDS_UNITS_DB ) );

//
// Set users.
//
$users = $wrapper->users(
	new OntologyWrapper\MongoDatabase(
		kSTANDARDS_ENTITIES_DB ) );

//
// Check graph database.
//
if( kGRAPH_DO )
{
	//
	// Set graph database.
	//
	$graph = $wrapper->graph(
		new OntologyWrapper\Neo4jGraph(
			kSTANDARDS_GRAPH_DB ) );

} // Use graph database.

//
// Load data dictionary.
//
if( ! $wrapper->dictionaryFilled() )
	$wrapper->loadTagCache();


/*=======================================================================================
 *	INSTANTIATE																			*
 *======================================================================================*/
 
//
// Write to log file.
//
if( kDEBUG_FLAG )
	file_put_contents(
		kPATH_BATCHES_ROOT."/log/$session_id.log",
		"Update batch start: ".date( "r" )."\n",
		FILE_APPEND );

//
// Instantiate session.
//
$session = new OntologyWrapper\Session( $wrapper, $session_id );
if( $session->committed() )
{
	//
	// Reset maximum execution time.
	//
	$max_exe = ini_set( 'max_execution_time', 0 );

	//
	// Instantiate update.
	//
	$update = new OntologyWrapper\SessionUpdate( $session );
	
	//
	// Execute upload.
	//
	$update->execute();

	//
	// Re-set maximum execution time.
	//
	ini_set( 'max_execution_time', $max_exe );

} // Found session.

elseif( kDEBUG_FLAG )
 	file_put_contents(
		kPATH_BATCHES_ROOT."/log/$session_id.log",
		"  ==> Session [$session_id] not found.\n",
		FILE_APPEND );

//
// Write to log file.
//
if( kDEBUG_FLAG )
	file_put_contents(
		kPATH_BATCHES_ROOT."/log/$session_id.log",
		"Batch end: ".date( "r" )."\n",
		FILE_APPEND );


?>
