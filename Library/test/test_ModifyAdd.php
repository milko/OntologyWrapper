<?php

/**
 * {@link PersistentObject::modifyAdd()} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link PersistentObject} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 11/03/2014
 */

/*=======================================================================================
 *																						*
 *									test_ModifyAdd.php									*
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
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


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
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			'TEST',
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Set databases.
	//
	$meta = $wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );

	//
	// Modify object.
	//
	echo( '<h4>Modify object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\Term( $wrapper );<br />' );
	$test = new OntologyWrapper\Term( $wrapper );
	echo( '$test[ kTAG_SYNONYM ] = array( "pippo" );<br />' );
	$test[ kTAG_SYNONYM ] = array( "pippo" );
	echo( '$test[ "WBI:VERSION" ] = "July 2012";<br />' );
	$test[ "WBI:VERSION" ] = "July 2012";
	echo( '$test[ "WBI:GROUP" ] = array( ... );<br />' );
	$test[ "WBI:GROUP" ] = array(
		"WBI:LENDING:IBRD",
		"WBI:INCOME:ARB",
		"WBI:INCOME:IBD",
		"WBI:INCOME:IBB",
		"WBI:INCOME:IBT",
		"WBI:INCOME:LMY" );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$ok = $test->modifyAdd( "iso:3166:1:alpha-3:DZA" );'.kSTYLE_HEAD_POS );
	$ok = $test->modifyAdd( "iso:3166:1:alpha-3:DZA" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $ok );
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
	echo( $error->xdebug_message );
}

echo( "\nDone!\n" );

?>
