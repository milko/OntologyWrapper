<?php

/**
 * Template upload test.
 *
 * This file contains routines to test and demonstrate the behaviour of a template upload
 * service.,
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 09/03/2015
 */

/*=======================================================================================
 *																						*
 *								test_GroupTransactions.php								*
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
// Types.
//
require_once( kPATH_DEFINITIONS_ROOT."/Types.inc.php" );

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
	// Show status list.
	//
	echo( '<h4>Show status list</h4>' );
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
		kAPI_PARAM_GROUP_TRANS => array( kTAG_TRANSACTION_STATUS => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS' => array( 'kTAG_TRANSACTION_STATUS' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	
	//
	// Show messages by error type.
	//
	echo( '<h4>Show messages by error type</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_MESSAGE,
					  kTAG_TRANSACTION_MESSAGE => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_MESSAGE',
					  'kTAG_TRANSACTION_MESSAGE' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	
	//
	// Show errors by worksheet.
	//
	echo( '<h4>Show errors by worksheet</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Threats" worksheet errors by property.
	//
	echo( '<h4>Show "CK_Threats" worksheet errors by property</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Threats',
					  kTAG_TRANSACTION_ALIAS => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Threats" worksheet errors by column.
	//
	echo( '<h4>Show "CK_Threats" worksheet errors by column</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Threats',
					  kTAG_TRANSACTION_FIELD => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_FIELD' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Threats" worksheet errors by "ASSESSMENT_LEVEL" property and value.
	//
	echo( '<h4>Show "CK_Threats" worksheet errors by "ASSESSMENT_LEVEL" property and value</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Threats',
					  kTAG_TRANSACTION_ALIAS => 'ASSESSMENT_LEVEL',
					  kTAG_TRANSACTION_VALUE => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => 'ASSESSMENT_LEVEL',
					  'kTAG_TRANSACTION_VALUE' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Threats" worksheet errors by "ASSESSMENT_LEVEL" property, "4" value and error message.
	//
	echo( '<h4>Show "CK_Threats" worksheet errors by "ASSESSMENT_LEVEL" property, "4" value and error message</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Threats',
					  kTAG_TRANSACTION_ALIAS => 'ASSESSMENT_LEVEL',
					  kTAG_TRANSACTION_VALUE => '4',
					  kTAG_TRANSACTION_MESSAGE => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => 'ASSESSMENT_LEVEL',
					  'kTAG_TRANSACTION_VALUE' => '4',
					  'kTAG_TRANSACTION_MESSAGE' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Threats" worksheet errors by "ASSESSMENT_LEVEL" property, "4" value, "Invalid code." error message and row.
	//
	echo( '<h4>Show "CK_Threats" worksheet errors by "ASSESSMENT_LEVEL" property, "4" value, "Invalid code." error message and row</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Threats',
					  kTAG_TRANSACTION_ALIAS => 'ASSESSMENT_LEVEL',
					  kTAG_TRANSACTION_VALUE => '4',
					  kTAG_TRANSACTION_MESSAGE => 'Invalid code.',
					  kTAG_TRANSACTION_RECORD => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => 'ASSESSMENT_LEVEL',
					  'kTAG_TRANSACTION_VALUE' => '4',
					  'kTAG_TRANSACTION_MESSAGE' => 'Invalid code.',
					  'kTAG_TRANSACTION_RECORD' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	
	//
	// Show "CK_Identification" worksheet errors by property.
	//
	echo( '<h4>Show "CK_Identification" worksheet errors by property</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Identification',
					  kTAG_TRANSACTION_ALIAS => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Identification" worksheet errors with the empty property by error type.
	//
	echo( '<h4>Show "CK_Identification" worksheet errors with the empty property by error type</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Identification',
					  kTAG_TRANSACTION_ALIAS => '',
					  kTAG_ERROR_TYPE => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => '',
					  'kTAG_ERROR_TYPE' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
	// Show "CK_Identification" worksheet errors with the empty property by error type and value.
	//
	echo( '<h4>Show "CK_Identification" worksheet errors with the empty property by and value value</h4>' );
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
		kAPI_PARAM_GROUP_TRANS
			=> array( kTAG_TRANSACTION_STATUS => kTYPE_STATUS_ERROR,
					  kTAG_TRANSACTION_COLLECTION => 'CK_Identification',
					  kTAG_TRANSACTION_ALIAS => '',
					  kTAG_ERROR_TYPE => 'Duplicate record',
					  kTAG_TRANSACTION_VALUE => NULL ),
		kAPI_PAGING_SKIP => 0,
		kAPI_PAGING_LIMIT => 100
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GROUP_TRANSACTIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param_disp = array
	(
		'kAPI_PARAM_LOG_TRACE' => TRUE,
		'kAPI_REQUEST_USER' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		'kAPI_PARAM_GROUP_TRANS'
			=> array( 'kTAG_TRANSACTION_STATUS' => 'kTYPE_STATUS_ERROR',
					  'kTAG_TRANSACTION_COLLECTION' => 'CK_Threats',
					  'kTAG_TRANSACTION_ALIAS' => '',
					  'kTAG_ERROR_TYPE' => 'Duplicate record',
					  'kTAG_TRANSACTION_VALUE' => NULL ),
		'kAPI_PAGING_SKIP' => 0,
		'kAPI_PAGING_LIMIT' => 100
	);
	var_dump( $param_disp );
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
