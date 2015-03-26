<?php

/**
 * Template update batch.
 *
 * This file contains routines to commit a template, it will launch a child process that
 * will perform the commit and, at exit, ensure the lock file is deleted.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Batch
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 24/03/2015
 */

/*=======================================================================================
 *																						*
 *									Batch_Update.php									*
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
if( $argc < 2 )
	exit( "Usage: <script.php> <user ID> <session ID>\n" );							// ==>
$user_id = $argv[ 1 ];
$session_id = $argv[ 2 ];


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
$script = kPATH_BATCHES_ROOT.'/Batch_CommitTemplate.php';

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
exec( "$php -f $script '$session_id' > '$log'" );


/*=======================================================================================
 *	EXIT																				*
 *======================================================================================*/
 
//
// Check lock file.
//
if( file_exists( $lock_file ) )
	unlink( $lock_file );


?>
