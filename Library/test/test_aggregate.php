<?php

/**
 * {@link Service} generic test suite.
 *
 * This file contains generic routines to test and demonstrate the behaviour of the
 * {@link Service} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/08/2014
 */

/*=======================================================================================
 *																						*
 *									test_aggregate.php									*
 *																						*
 *======================================================================================*/
 
//
// Test class.
//
try
{
	//
	// Connect.
	//
	$m = new MongoClient( 'mongodb://localhost:27017' );
	$d = $m->selectDB( 'testbed' );
	$c = $d->selectCollection( 'A' );
	
	//
	// Project.
	//
	$project1 = array(
		'fld' => '$elements.fld',
		'knd' => '$elements.knd',
		'type' => '$type',
		'shape' => array(
			'$cond' => array(
				 'if' => '$shape',
				 'then' => 1,
				 'else' => 0 ) ) );

	//
	// Group 1.
	//
	$group1 = array(
		'_id' => array(
			'id' => '$_id',
			'fld' => '$fld',
			'knd' => '$knd',
			'type' => '$type' ),
		'shape' => array( '$sum' => '$shape' ) );
	
	//
	// Project.
	//
	$project2 = array(
		'_id' => '$_id',
		'shape' => array(
			'$cond' => array(
				 'if' => '$shape',
				 'then' => 1,
				 'else' => 0 ) ) );
			
	//
	// Group 2.
	//
	$group2 = array(
		'_id' => array(
			'fld' => '$_id.fld',
			'knd' => '$_id.knd',
			'type' => '$_id.type' ),
		'count' => array( '$sum' => 1 ),
		'points' => array( '$sum' => '$shape' ) );
			
	//
	// Sort.
	//
	$sort = array(
		'_id.fld' => 1,
		'_id.knd' => 1,
		'_id.type' => 1 );
			
	$pipeline
		= array(
			array( '$project' => $project1 ),
			array( '$unwind' => '$knd' ),
			array( '$unwind' => '$knd' ),
			array( '$unwind' => '$fld' ),
			array( '$unwind' => '$fld' ),
			array( '$group' => $group1 ),
			array( '$project' => $project2 ),
			array( '$group' => $group2 ),
			array( '$sort' => $sort )	
		);
			
var_dump( $pipeline );
	$rs = $c->aggregate( $pipeline );
var_dump( $rs );
exit;
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
