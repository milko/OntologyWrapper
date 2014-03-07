<?php

/**
 * {@link MongoIterator} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link MongoIterator} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */

/*=======================================================================================
 *																						*
 *									test_MongoIterator.php								*
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
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


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
class MyClass extends OntologyWrapper\MongoIterator{}


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/
 
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
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$meta->drop();
	$wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	
	//
	// Reset ontology.
	//
	$wrapper->resetOntology();
	
	//
	// Test parent class.
	//
	if( kDEBUG_PARENT )
	{
		echo( "<h3>Parent class test</h3>" );
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );

	//
	// Query all terms.
	//
	echo( '<h4>Query all terms</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$database = OntologyWrapper\Term::ResolveDatabase( $wrapper, TRUE, TRUE );<br/>' );
	$database = OntologyWrapper\Term::ResolveDatabase( $wrapper, TRUE, TRUE );
	echo( '$collection = $database->Collection( OntologyWrapper\Term::kSEQ_NAME, TRUE );<br/>' );
	$collection = $database->Collection( OntologyWrapper\Term::kSEQ_NAME, TRUE );
	echo( '$test = $collection->matchAll();' );
	$test = $collection->matchAll();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Check count.
	//
	echo( '<h4>Query all terms</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$x = $test->count();' );
	$x = $test->count();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$x = $test->affectedCount();' );
	$x = $test->affectedCount();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Set limits.
	//
	echo( '<h4>Set limits</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$x = $test->limit( 3 );' );
	$x = $test->limit( 3 );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$x = $test->count();' );
	$x = $test->count();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$x = $test->affectedCount();' );
	$x = $test->affectedCount();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Iterate objects.
	//
	echo( '<h4>Iterate objects</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$x = iterator_to_array( $test );' );
	$x = iterator_to_array( $test );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Iterate arrays.
	//
	echo( '<h4>Iterate arrays</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test->resultType( kQUERY_ARRAY );<br/>' );
	$test->resultType( kQUERY_ARRAY );
	echo( '$test->rewind();<br/>' );
	$test->rewind();
	echo( '$x = iterator_to_array( $test );' );
	$x = iterator_to_array( $test );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Iterate identifiers.
	//
	echo( '<h4>Iterate identifiers</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test->resultType( kQUERY_NID );<br/>' );
	$test->resultType( kQUERY_NID );
	echo( '$test->rewind();<br/>' );
	$test->rewind();
	echo( '$x = iterator_to_array( $test );' );
	$x = iterator_to_array( $test );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Get keys.
	//
	echo( '<h4>Get keys</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test->rewind();<br/>' );
	$test->rewind();
	echo( '$x = $test->keys();' );
	$x = $test->keys();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Skip 10.
	//
	echo( '<h4>Skip 10</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = $collection->matchAll();<br/>' );
	$test = $collection->matchAll();
	echo( '$test->skip( 10 );<br/>' );
	$test->skip( 10 );
	echo( '$test->limit( 5 );<br/>' );
	$test->limit( 5 );
	echo( '$x = $test->keys();' );
	$x = $test->keys();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Sort.
	//
	echo( '<h4>Sort</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = $collection->matchAll();<br/>' );
	$test = $collection->matchAll();
	echo( '$test->sort( array( kTAG_NID => -1 ) );<br/>' );
	$test->sort( array( kTAG_NID => -1 ) );
	echo( '$test->limit( 5 );<br/>' );
	$test->limit( 5 );
	echo( '$x = $test->keys();' );
	$x = $test->keys();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Fields.
	//
	echo( '<h4>Fields</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = $collection->matchAll( Array(), kQUERY_ARRAY );<br/>' );
	$test = $collection->matchAll( Array(), kQUERY_ARRAY );
	echo( '$test->fields( array( kTAG_ID_LOCAL => TRUE ) );<br/>' );
	$test->fields( array( kTAG_ID_LOCAL => TRUE ) );
	echo( '$test->limit( 5 );<br/>' );
	$test->limit( 5 );
	echo( '$x = iterator_to_array( $test );' );
	$x = iterator_to_array( $test );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $x );
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
