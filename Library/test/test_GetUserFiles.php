<?php

/**
 * Template upload test.
 *
 * This file contains routines to test and demonstrate the behaviour of a template upload
 * service.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 09/03/2015
 */

/*=======================================================================================
 *																						*
 *								test_GetUserFiles.php									*
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
// Set service URL.
//
//$base_url = 'http://mauricio.grinfo.private/Service.php';
$base_url = 'http://localhost/gateway/Service.php';

//
// Init local storage.
//
$pub_key_path = '/Library/WebServer/Private/gateway/pub.pem';
$pub_key = file_get_contents( $pub_key_path );
$priv_key_path = '/Library/WebServer/Private/gateway/priv.pem';
$priv_key = file_get_contents( $priv_key_path );
$ext_pub_key_path = '/Library/WebServer/Private/gateway/ext_pub.pem';
$ext_pub_key = file_get_contents( $ext_pub_key_path );
$ext_priv_key_path = '/Library/WebServer/Private/gateway/ext_priv.pem';
$ext_priv_key = file_get_contents( $ext_priv_key_path );
$encoder = new OntologyWrapper\Encoder();

//
// Test class.
//
try
{
	//
	// Check user files.
	//
	echo( '<h4>Check user files</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
	//	kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_REQUEST_USER => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A'
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GET_USER_FILES;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	if( array_key_exists( kAPI_STATUS_CRYPTED, $result[ kAPI_RESPONSE_STATUS ] )
	 && $result[ kAPI_RESPONSE_STATUS ][ kAPI_STATUS_CRYPTED ] )
	{
		$encoded = $result[ kAPI_RESPONSE_RESULTS ];
		$decoded = $encoder->privateDecode( $encoded, $ext_priv_key );
		$decoded = json_decode( $decoded, TRUE );
		$result[ kAPI_RESPONSE_RESULTS ] = $decoded;
	}
	var_dump( $result );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get file ID.
	//
	$id = $result[ kAPI_RESPONSE_RESULTS ]
				 [ 0 ]
				 [ kTAG_NID ]
				 [ kAPI_PARAM_RESPONSE_FRMT_VALUE ];
	
	//
	// Save user file.
	//
	echo( '<h4>Save user file</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
	//	kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_REQUEST_USER => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		kAPI_PARAM_ID => $id,
		kAPI_PARAM_FILE_PATH => "/tmp/PIPPO.XXX"
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_SAVE_USER_FILE;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	if( array_key_exists( kAPI_STATUS_CRYPTED, $result[ kAPI_RESPONSE_STATUS ] )
	 && $result[ kAPI_RESPONSE_STATUS ][ kAPI_STATUS_CRYPTED ] )
	{
		$encoded = $result[ kAPI_RESPONSE_RESULTS ];
		$decoded = $encoder->privateDecode( $encoded, $ext_priv_key );
		$decoded = json_decode( $decoded, TRUE );
		$result[ kAPI_RESPONSE_RESULTS ] = $decoded;
	}
	var_dump( $result );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( '<pre>'.$error->xdebug_message.'</pre>' );
}

echo( "\nDone!\n" );

?>
