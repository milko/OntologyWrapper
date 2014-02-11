<?php

/**
 * {@link Connection} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link Connection} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/01/2014
 */

/*=======================================================================================
 *																						*
 *									test_Connection.php									*
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


/*=======================================================================================
 *	RUNTIME SETTINGS																	*
 *======================================================================================*/
 
//
// Debug switches.
//
define( 'kDEBUG_PARENT', TRUE );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/
 
//
// Test class.
//
try
{
	//
	// Test parent class.
	//
	if( kDEBUG_PARENT )
	{
	}

	//
	// Get memcached.
	//
	echo( '<h4>Get memcached</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = OntologyWrapper\Connection::NewConnection( "memcached://localhost:11211" );'.kSTYLE_HEAD_POS );
	$test = OntologyWrapper\Connection::NewConnection( "memcached://localhost:11211" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test unsupported.
	//
	echo( '<h4>Test unsupported<br /><i>should raise an exception</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = OntologyWrapper\Connection::NewConnection( "pippo://localhost:11211" );'.kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	try
	{
		$test = OntologyWrapper\Connection::NewConnection( "pippo://localhost:11211" );
	}
	catch( Exception $error )
	{
		echo( '<pre>' );
		echo( (string) $error );
		echo( '</pre>' );
	}
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( (string) $error );
}

echo( "\nDone!\n" );

?>
