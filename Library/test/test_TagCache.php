<?php

/**
 * {@link TagCache} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link TagCache} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/01/2014
 */

/*=======================================================================================
 *																						*
 *									test_TagCache.php									*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// Style includes.
//
require_once( 'styles.inc.php' );

//
// Session offsets.
//
require_once( kPATH_DEFINITIONS_ROOT.'/session.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT.'/Tags.inc.php' );


/*=======================================================================================
 *	RUNTIME SETTINGS																	*
 *======================================================================================*/
 
//
// Debug switches.
//
define( 'kDEBUG_PARENT', TRUE );


/*=======================================================================================
 *	CLASS SETTINGS																		*
 *======================================================================================*/
 
//
// Cast current class.
//
class MyClass extends OntologyWrapper\TagCache{}


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

session_start();
 
//
// Test class.
//
try
{
	//
	// Instantiate main tag cache.
	//
	if( ! array_key_exists( kSESSION_DDICT, $_SESSION ) )
		$_SESSION[ kSESSION_DDICT ]
			= new OntologyWrapper\TagCache(
				kSESSION_DDICT,
				array( array( 'localhost', 11211 ) ) );
	
	//
	// Test parent class.
	// No parents.
	//
	if( kDEBUG_PARENT )
	{
	}
	
	//
	// Test object.
	//
	echo( '<h4>Test instantiate object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( kSESSION_DDICT, array( array( "localhost", 11211 ) ) );'.kSTYLE_HEAD_POS );
	$test = new MyClass( kSESSION_DDICT, array( array( "localhost", 11211 ) ) );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->stats() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Set and retrieve identifier.
	//
	echo( '<h4>Set and retrieve identifier<br /><i>should return -1</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->setTagId( "TEST", -1 );'.kSTYLE_HEAD_POS );
	$test->setTagId( "TEST", -1 );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$match = $test->getTagId( "TEST" );'.kSTYLE_HEAD_POS );
	$match = $test->getTagId( "TEST" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $match ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Delete identifier.
	//
	echo( '<h4>Delete identifier<br /><i>should not find -1</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->delTagId( "TEST" );'.kSTYLE_HEAD_POS );
	$test->delTagId( "TEST" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$match = $test->getTagId( "TEST" );'.kSTYLE_HEAD_POS );
	$match = $test->getTagId( "TEST" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $match );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Set and retrieve object.
	//
	echo( '<h4>Set and retrieve object<br /><i>should return the array</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->setTagObject( -1, array( 1, 2, 3 ) );'.kSTYLE_HEAD_POS );
	$test->setTagObject( -1, array( 1, 2, 3 ) );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$match = $test->getTagObject( -1 );'.kSTYLE_HEAD_POS );
	$match = $test->getTagObject( -1 );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $match );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Delete object.
	//
	echo( '<h4>Delete object<br /><i>should not find key -1</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->delTagObject( -1 );'.kSTYLE_HEAD_POS );
	$test->delTagObject( -1 );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$match = $test->getTagObject( -1 );'.kSTYLE_HEAD_POS );
	$match = $test->getTagObject( -1 );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $match );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get global identifier.
	//
	echo( '<h4>Get global identifier<br /><i>should return <tt>:definition</tt></i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$match = $test->getTagGID( kTAG_DEFINITION );'.kSTYLE_HEAD_POS );
	$match = $test->getTagGID( kTAG_DEFINITION );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $match );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get current keys.
	//
	echo( '<h4>Get current keys</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$match = $test->Connection()->getAllKeys();'.kSTYLE_HEAD_POS );
	$match = $test->Connection()->getAllKeys();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $match );
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
	echo( '<pre>' );
	echo( (string) $error );
	echo( '</pre>' );
}

echo( "\nDone!\n" );

?>
