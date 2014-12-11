<?php

/**
 * {@link Encoder} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link Encoder} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 11/12/2014
 */

/*=======================================================================================
 *																						*
 *									test_Encoder.php									*
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
$pubkey = '/Library/WebServer/Library/OntologyWrapper/Library/test/public_key.pem';
$privkey = '/Library/WebServer/Library/OntologyWrapper/Library/test/private_key.pem';
 
//
// Test class.
//
try
{
	//
	// Get private and public keys.
	//
	$thePubKey = file_get_contents( $pubkey );
	$thePrivKey = file_get_contents( $privkey );
	
	//
	// Test pub/priv encode.
	//
	echo( '<h4>Test pub/priv encode</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$string = \'This is a test string.\';<br />' );
	$string = 'This is a test string.';
	echo( '$encoder = new OntologyWrapper\\Encoder();<br />' );
	$encoder = new OntologyWrapper\Encoder();
	echo( '$encoded = $encoder->publicEncode( $string, $thePubKey );<br />' );
	$encoded = $encoder->publicEncode( $string, $thePubKey );
	echo( '$decoded = $encoder->privateDecode( $encoded, $thePrivKey );<br />' );
	$decoded = $encoder->privateDecode( $encoded, $thePrivKey );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( "String: $string<br />" );
	echo( "Encoded: $encoded<br />" );
	echo( "Decoded: $decoded<br />" );
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
