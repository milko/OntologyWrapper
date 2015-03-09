<?php

/**
 * Template load batch.
 *
 * This file contains routines to validate a template.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Batch
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 09/03/2015
 */

/*=======================================================================================
 *																						*
 *								Batch_LoadTemplate.php									*
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
if( $argc < 3 )
	exit( "Usage: <script.php> <session ID> <template path>\n" );					// ==>
$session_id = $argv[ 1 ];
$template_path = $argv[ 2 ];


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
// Instantiate session.
//
$session = new OntologyWrapper\Session( $wrapper, $session_id );
if( $session->committed() )
{
	//
	// Instantiate upload.
	//
	$upload = new OntologyWrapper\SessionUpload( $session, $template_path );
	
	//
	// Execute upload.
	//
	$upload->execute();

} // Found session.


?>
