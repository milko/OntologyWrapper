<?php

/**
 * {@link OntologyObject} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link OntologyObject} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/01/2014
 */

/*=======================================================================================
 *																						*
 *								test_OntologyObject.php									*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );


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
		//
		// Instantiate empty object.
		//
		echo( "\nInstantiate empty object\n" );
		echo( '$test = new OntologyWrapper\OntologyObject();'."\n" );
		$test = new OntologyWrapper\OntologyObject();
		var_dump( $test );
	
		//
		// Set offset.
		//
		echo( "\nSet offset (should add the value)\n" );
		echo( '$test["test"] = "TEST1";'."\n" );
		$test["test"] = "TEST1";
		var_dump( $test );
	
		//
		// Set NULL offset.
		//
		echo( "\nSet NULL offset (should add the value under offset 0)\n" );
		echo( '$test[NULL] = "TEST2";'."\n" );
		$test[NULL] = "TEST2";
		var_dump( $test );
	
		//
		// Set NULL value.
		//
		echo( "\nSet NULL value (should delete 'test' offset)\n" );
		echo( '$test["test"] = NULL;'."\n" );
		$test["test"] = NULL;
		var_dump( $test );
	
		//
		// Set NULL offset and value.
		//
		echo( "\nSet NULL offset and value (should throw a warning)\n" );
		echo( '$test[NULL] = NULL;'."\n" );
		$test[NULL] = NULL;
		var_dump( $test );
	
		//
		// Get offset 0.
		//
		echo( "\nGet offset 0 (should return 'TEST2')\n" );
		echo( '$test[0];'."\n" );
		var_dump( $test[0] );
	
		//
		// Delete offset 0.
		//
		echo( "\nDelete offset 0 (should delete 'TEST2')\n" );
		echo( '$test->offsetUnset( 0 );'."\n" );
		$test->offsetUnset( 0 );
		var_dump( $test );
	
		//
		// Delete offset with NULL.
		//
		echo( "\nDelete offset with NULL (should delete ['test'] => 'TEST')\n" );
		echo( '$test["test"] = "TEST";'."\n" );
		$test["test"] = "TEST";
		var_dump( $test );
		echo( '$test["test"] = NULL;'."\n" );
		$test["test"] = NULL;
		var_dump( $test );
	
		//
		// Test getArrayCopy.
		//
		echo( "\nTest getArrayCopy (should return arrays)\n" );
		echo( '$test1 = new OntologyWrapper\OntologyObject( array( "test" => "TEST" ) );'."\n" );
		$test1 = new OntologyWrapper\OntologyObject( array( 'test' => 'TEST' ) );
		echo( '$test2 = new OntologyWrapper\OntologyObject( array( "test2" => "TEST2" ) );'."\n" );
		$test2 = new OntologyWrapper\OntologyObject( array( 'test2' => 'TEST2' ) );
		echo( '$test = new OntologyWrapper\OntologyObject( array( "one" => $test1, "two" => $test2, new ArrayObject( array( 1, 2, 3 ) ) ) );'."\n" );
		$test = new OntologyWrapper\OntologyObject( array( "one" => $test1, "two" => $test2, new ArrayObject( array( 1, 2, 3 ) ) ) );
		echo( "Object:\n" );
		var_dump( $test );
		echo( "Array copy:\n" );
		var_dump( $test->getArrayCopy() );
	}
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
