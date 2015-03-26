<?php

/**
 * Template upload batch.
 *
 * This file contains routines to validate a template, it will launch a child process that
 * will perform the validation and, at exit, ensure the lock file is deleted.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Batch
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 24/03/2015
 */

/*=======================================================================================
 *																						*
 *									Batch_Upload.php									*
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


/*=======================================================================================
 *	ENVIRONMENT SETTINGS																*
 *======================================================================================*/

set_time_limit( 0 );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 4 )
	exit( "Usage: <script.php> <user ID> <session ID> <template path>\n" );			// ==>
$user_id = $argv[ 1 ];
$session_id = $argv[ 2 ];
$template_path = $argv[ 3 ];


/*=======================================================================================
 *	INIT																				*
 *======================================================================================*/
 
//
// Save lock file name.
//
$lock_file = OntologyWrapper\SessionBatch::LockFilePath( $user_id );
				
//
// Set process path.
//
$script = kPATH_BATCHES_ROOT.'/Batch_LoadTemplate.php';

//
// Handle debug log.
//
if( kDEBUG_FLAG )
	$log = "'".kPATH_BATCHES_ROOT."/log/$session_id.batch'";
else
	$log = '/dev/null';

//
// SAVE PHP BINARY.
//
$php = kPHP_BINARY;


/*=======================================================================================
 *	LAUNCH																				*
 *======================================================================================*/
 
//
// Launch child process.
//
exec( "$php -f $script '$session_id' '$template_path' > '$log'" );


/*=======================================================================================
 *	EXIT																				*
 *======================================================================================*/
 
//
// Check lock file.
//
if( file_exists( $lock_file ) )
	unlink( $lock_file );


?>
