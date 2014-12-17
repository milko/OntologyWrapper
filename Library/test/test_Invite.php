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
 *									test_Invite.php										*
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
// Init local storage.
//
$pub_key_path = '/Library/WebServer/Private/pgrdg/pub.pem';
$pub_key = file_get_contents( $pub_key_path );
$priv_key_path = '/Library/WebServer/Private/pgrdg/priv.pem';
$priv_key = file_get_contents( $priv_key_path );
$base_url = 'http://localhost/services/Bioversity/Service.php';

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
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();

	//
	// Reset users.
	//
	$collection = $wrapper->resolveCollection( OntologyWrapper\User::kSEQ_NAME );
	
	//
	// Delete existing users.
	//
	foreach(
		$wrapper->resolveCollection(
			OntologyWrapper\User::kSEQ_NAME )
				->matchAll( Array(), kQUERY_NID )
			as $id )
		OntologyWrapper\User::Delete( $wrapper, $id );
	
	//
	// Reset user sequences.
	//
	OntologyWrapper\User::ResolveDatabase( $wrapper, TRUE )
		->setSequenceNumber( OntologyWrapper\User::kSEQ_NAME, 1 );
	
	//
	// Load administrator.
	//
	$wrapper->loadXMLFile( kPATH_LIBRARY_ROOT."/settings/Admin.xml" );
	
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
	echo( '$encoder = new OntologyWrapper\\Encoder( );<br />' );
	$encoder = new OntologyWrapper\Encoder();
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
		kAPI_REQUEST_USER => ':domain:individual://ITA406/pgrdiversity.bioversityinternational.org:milko;',
		kAPI_PARAM_OBJECT => array
		(
			kTAG_ENTITY_EMAIL => 'info@skofic.net',
			kTAG_NAME => 'Test User',
			kTAG_ROLES => array( kTYPE_ROLE_UPLOAD, kTYPE_ROLE_EDIT ),
			kTAG_ENTITY_PGP_KEY => $user_pub_key,
			kTAG_ENTITY_PGP_FINGERPRINT => $user_finger,
		)
	);
	$encoder = new OntologyWrapper\Encoder();
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

/*
	//
	// Try matchUnits statistics.
	//
	echo( '<h4>Try matchUnits statistics</h4>' );
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
		kAPI_PAGING_LIMIT => 3,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_CRITERIA => Array(),
		kAPI_PARAM_DOMAIN => kDOMAIN_HH_ASSESSMENT,
		kAPI_PARAM_STAT => 'abdh-species-07',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_STAT
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_UNITS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
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
*/

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
