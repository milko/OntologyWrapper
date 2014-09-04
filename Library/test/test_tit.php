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
	$d = $m->selectDB( 'scrap' );
	$c = $d->selectCollection( 'planets' );
	$c->drop();
	
	//
	// Load records.
	//
	$c->insert(array("name" => "Mercury", "color" => "blue", "desc" => "Mercury is the smallest and closest to the Sun"));
	$c->insert(array("name" => "Venus", "color" => "green", "desc" => "Venus is the second planet from the Sun, orbiting it every 224.7 Earth days."));
	$c->insert(array("name" => "Earth", "color" => "blue", "desc" => "Earth is the densest of the eight planets in the Solar System."));
	$c->insert(array("name" => "Mars", "color" => "red", "desc" => "Mars is named after the Roman god of war."));
	
	//
	// index.
	//
	$c->ensureIndex(array('desc' => 'text'));
	
	//
	// Search.
	//
	$r = $d->command(array("text" => "planets", 'search' => "sun" ));
	
	echo( 'db.command();<pre>' );
	var_dump( gettype( $r ) );
	print_r($r);
	echo( '</pre>' );
	
	//
	// Aggregate.
	//
	$p = array( array( '$match' => array( '$text' => array( '$search' => 'sun' ) ) ),
				array( '$sort' => array( 'score' => array( '$meta' => "textScore" ) ) ),
				array( '$project' => array( 'score' => array( '$meta' => "textScore" ) ) ) );
	$r = $c->aggregate($p);
	
	echo( 'aggregate();<pre>' );
	var_dump( gettype( $r ) );
	var_dump( $p );
	print_r($r);
	echo( '</pre>' );
	
	//
	// Database command aggregate.
	//
	$p = array( array( '$match' => array( '$text' => array( '$search' => 'sun' ) ) ),
				array( '$sort' => array( 'score' => array( '$meta' => "textScore" ) ) ) );
	$r = $d->command( array( "aggregate" => "planets",
						   	 'pipeline' => $p ),
					   array( 'allowDiskUse' => TRUE ) );
	
	echo( 'db.command(aggregate);<pre>' );
	var_dump( gettype( $r ) );
	var_dump( $p );
	print_r($r);
	echo( '</pre>' );
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
