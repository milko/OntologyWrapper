<?php

/**
 * User invitation test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of a user invitation,
 * the script will:
 *
 * <ul>
 *	<li>Create an invitation.
 *	<li>Create the invited user.
 * </ul>
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 17/12/2014
 */

/*=======================================================================================
 *																						*
 *										test_Users.php									*
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
//$base_db = 'mongodb://mauricio.grinfo.private:27017/MAURICIO?connect=1';
$base_db = 'mongodb://localhost:27017/BIOVERSITY?connect=1';
//$base_url = 'http://mauricio.grinfo.private/Service.php';
$base_url = 'http://localhost/services/Bioversity/Service.php';

//
// Init local storage.
//
$pub_key_path = '/Library/WebServer/Private/pgrdg/pub.pem';
$pub_key = file_get_contents( $pub_key_path );
$priv_key_path = '/Library/WebServer/Private/pgrdg/priv.pem';
$priv_key = file_get_contents( $priv_key_path );
$ext_pub_key_path = '/Library/WebServer/Private/pgrdg/ext_pub.pem';
$ext_pub_key = file_get_contents( $ext_pub_key_path );
$ext_priv_key_path = '/Library/WebServer/Private/pgrdg/ext_priv.pem';
$ext_priv_key = file_get_contents( $ext_priv_key_path );
$encoder = new OntologyWrapper\Encoder();

//
// Test class.
//
try
{
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
		new OntologyWrapper\MongoDatabase(
			$base_db ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			$base_db ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			$base_db ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Login.
	//
	echo( '<h4>Login</h4>' );
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_ID => array( 'milko', 'b1b3773a05c0ed0176787a4f1574ff0075f7521e' ),
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GET_USER;
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
	
	//
	// Get managed user.
	//
	echo( '<h4>Get managed user</h4>' );
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_REQUEST_USER => 'E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2',
		kAPI_PARAM_ID => 'E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GET_USER;
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
	
	//
	// Get public user.
	//
	echo( '<h4>Get public user</h4>' );
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_REQUEST_USER => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A',
		kAPI_PARAM_ID => 'E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GET_USER;
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
exit;
	
	//
	// Login.
	//
	echo( '<h4>Login</h4>' );
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_ID => array( 'milko', 'qwerty' ),
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_GET_USER;
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
exit;
	
	//
	// Select administrator's ID.
	//
	$admin_id = key( $result[ kAPI_RESPONSE_RESULTS ] );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'User:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( $admin_id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Create user cert9ificates.
	//
	echo( '<h4>Create user cert9ificates</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$encoder->generateKeys( $user_pub_key, $user_priv_key );<br />' );
	$encoder->generateKeys( $user_pub_key, $user_priv_key );
	echo( '$user_finger = md5( $user_pub_key );<br />' );
	$user_finger = md5( $user_pub_key );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( "Public: $user_pub_key" );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( "Private: $user_priv_key" );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( "Finger: $user_finger" );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Invite user.
	//
	echo( '<h4>Invite user</h4>' );
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_REQUEST_USER => $admin_id,
		kAPI_PARAM_OBJECT => array
		(
			kTAG_AUTHORITY => 'ITA406',
			kTAG_COLLECTION => 'pgrdiversity.bioversityinternational.org',
			kTAG_ENTITY_EMAIL => 'skofic@gmail.com',
			kTAG_NAME => 'Milko Skofic',
			kTAG_ROLES => array( kTYPE_ROLE_UPLOAD, kTYPE_ROLE_EDIT ),
			kTAG_ENTITY_PGP_KEY => $user_pub_key,
			kTAG_ENTITY_PGP_FINGERPRINT => $user_finger,
		)
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_INVITE_USER;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Retrieve invitation.
	//
	echo( '<h4>Retrieve invitation</h4>' );
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT,
		kAPI_PARAM_ID => $user_finger
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_USER_INVITE;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	if( $result[ kAPI_RESPONSE_STATUS ][ kAPI_STATUS_CRYPTED ] )
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
	// Build user record.
	//
	echo( '<h4>Build user record</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Record:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$invitation = Array();
	foreach( $result[ kAPI_RESPONSE_RESULTS ] as $key => $value )
		$invitation[ $key ]
			= ( array_key_exists( kAPI_PARAM_RESPONSE_FRMT_VALUE, $value ) )
			? $value[ kAPI_PARAM_RESPONSE_FRMT_VALUE ]
			: $value[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $invitation );;
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Add user.
	//
	echo( '<h4>Add user</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Build:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$invitation[ kTAG_ENTITY_FNAME ] = "Test";<br />' );
	$invitation[ kTAG_ENTITY_FNAME ] = "Test";
	echo( '$invitation[ kTAG_ENTITY_LNAME ] = "User";<br />' );
	$invitation[ kTAG_ENTITY_LNAME ] = "User";
	echo( '$invitation[ kTAG_CONN_CODE ] = "user";<br />' );
	$invitation[ kTAG_CONN_CODE ] = "user";
	echo( '$invitation[ kTAG_CONN_PASS ] = "password";<br />' );
	$invitation[ kTAG_CONN_PASS ] = "password";
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( 'User:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '<pre>' ); print_r( $invitation ); echo( '</pre>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT,
		kAPI_PARAM_OBJECT => $invitation
	);
	$encoded = $encoder->publicEncode( json_encode( $param ), $pub_key );
	$request = "$base_url?op=".kAPI_OP_ADD_USER;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( $encoded ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	if( $result[ kAPI_RESPONSE_STATUS ][ kAPI_STATUS_CRYPTED ] )
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
