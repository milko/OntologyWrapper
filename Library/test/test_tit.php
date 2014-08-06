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
 *	@version	1.00 13/01/2014
 */

/*=======================================================================================
 *																						*
 *									test_tit.php										*
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
	$d = $m->selectDB( 'PGRDG' );
	$c = $d->selectCollection( '_units' );
	
	//
	// Match.
	//
	$match = array( '$and' => array(
		array( '47' => 173 ),
		array( '47' => 163 ),
		array( '47' => 255 ) ) );
	
	//
	// Project.
	//
	$project = array(
		'163' => '$242.163',
		'255' => '$242.255',
		'7' => '$7' );
	
	$pipeline
		= array(
			array( '$match' => $match ),
			array( '$project' => $project ),
			array( '$unwind' => '$255' ),
			array( '$unwind' => '$255' ),
			array( '$unwind' => '$163' ),
			
			array( '$group' => array(
				'_id' => array(
					'163' => '$163',
					'255' => '$255',
					'7' => '$7' ),
				'count' => array( '$sum' => 1 ) ) ),
/*			
			array( '$group' => array(
				'_id' => array(
					'id' => '$id',
					'7' => '$7',
					'255' => '$163' ),
				'count' => array( '$sum' => 1 ) ) ),
*/
			array( '$sort' => array( '_id.163' => 1,
									 '_id.255' => 1,
									 '_id.7' => 1 ) )
		);
				
			
var_dump( $pipeline );
	$rs = $c->aggregate( $pipeline, Array() );
var_dump( $rs );
exit;
		   
	
	//
	// Unwind.
	//
	$unwind = array( '$241.163' );
	
	//
	// Group.
	//
	$group = array( '_id' => '$_id', '163' => array( '$push' => '$241.163' ) );
	
	//
	// Pipeline.
	//
	$pipeline = array( array( '$match' => $match ),
					   array( '$project' => $project ) );
	
	//
	// Execite.
	//
	$rs = $c->aggregate( $pipeline );
var_dump( $rs );
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
