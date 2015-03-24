<?php

/**
 * {@link SessionUpload} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link SessionUpload} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/02/2015
 */

/*=======================================================================================
 *																						*
 *								test_SessionUpdate.php									*
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
// Style includes.
//
require_once( 'styles.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

//
// Operators.
//
require_once( kPATH_DEFINITIONS_ROOT."/Operators.inc.php" );

//
// API.
//
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// Managed offsets query.
//
// { "@7" : { "$in" : [ "@2a", "@60", "@19", "@2b", "@68" ] } }

//
// Init local storage.
//
$file = "/Library/WebServer/Library/OntologyWrapper/Library/test/test_checklist.small.xlsx";
$user = ":domain:individual://ITA406/pgrdiversity.bioversityinternational.org:7C4D3533C21C608B39E8EAB256B4AFB771FA534A;";
$fingerprint = "7C4D3533C21C608B39E8EAB256B4AFB771FA534A";
	
//
// Test class.
//
try
{
	//
	// Reset maximum execution time.
	//
	$max_exe = ini_set( 'max_execution_time', 0 );
	
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
	$meta = $wrapper->metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Instantiate upload session.
	//
	echo( '<h4>Instantiate upload session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$upload_session = new OntologyWrapper\\Session( $wrapper )<br />;' );
	$upload_session = new OntologyWrapper\Session( $wrapper );
	echo( '$upload_session[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPLOAD;<br />' );
	$upload_session[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPLOAD;
	echo( '$upload_session[ kTAG_USER ] = $user;<br />' );
	$upload_session[ kTAG_USER ] = $user;
	echo( '$upload_session[ kTAG_ENTITY_PGP_FINGERPRINT ] = $fingerprint;<br />' );
	$upload_session[ kTAG_ENTITY_PGP_FINGERPRINT ] = $fingerprint;
	echo( '$upload_id = $upload_session->commit();' );
	$upload_id = $upload_session->commit();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'ID' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $upload_id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $upload_session->getName( kSTANDARDS_LANGUAGE );' );
	$data = $upload_session->getName( kSTANDARDS_LANGUAGE );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Instantiate new upload.
	//
	echo( '<h4>Instantiate new upload</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$workflow = new OntologyWrapper\\SessionUpload( $upload_session, $file );' );
	$workflow = new OntologyWrapper\SessionUpload( $upload_session, $file );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$ok = $workflow->execute();' );
	$ok = $workflow->execute();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $ok );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Instantiate update session.
	//
	echo( '<h4>Instantiate update session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$update_session = new OntologyWrapper\\Session( $wrapper )<br />;' );
	$update_session = new OntologyWrapper\Session( $wrapper );
	echo( '$update_session[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPDATE;<br />' );
	$update_session[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPDATE;
	echo( '$update_session[ kTAG_USER ] = $user;<br />' );
	$update_session[ kTAG_USER ] = $user;
	echo( '$update_session[ kTAG_ENTITY_PGP_FINGERPRINT ] = $fingerprint;<br />' );
	$update_session[ kTAG_ENTITY_PGP_FINGERPRINT ] = $fingerprint;
	echo( '$update_id = $update_session->commit();<br />' );
	$update_id = $update_session->commit();
	echo( '$update_session->offsetSet( kTAG_SESSION, $upload_id );' );
	$update_session->offsetSet( kTAG_SESSION, $upload_id );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'ID' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $update_id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $update_session->getName( kSTANDARDS_LANGUAGE );' );
	$data = $update_session->getName( kSTANDARDS_LANGUAGE );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Instantiate new update.
	//
	echo( '<h4>Instantiate new update</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$workflow = new OntologyWrapper\\SessionUpdate( $update_session );' );
	$workflow = new OntologyWrapper\SessionUpdate( $update_session );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$ok = $workflow->execute();' );
	$ok = $workflow->execute();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $ok );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Re-set maximum execution time.
	//
	ini_set( 'max_execution_time', $max_exe );

}

//
// Catch exceptions.
//
catch( \Exception $error )
{

	//
	// Re-set maximum execution time.
	//
	ini_set( 'max_execution_time', $max_exe );
	
	echo( '<pre>'.$error->xdebug_message.'</pre>' );
}

echo( "\nDone!\n" );

?>
