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
 *									test_tat.php										*
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
	$d = $m->selectDB( 'BIOVERSITY' );
	$c = $d->selectCollection( '_units' );
	
	//
	// Aggregate.
	//
	$p = array( array( '$match' => array( '$text' => array( '$search' => 'pinus' ) ) ),
				array( '$sort' => array( 'score' => array( '$meta' => "textScore" ) ) ),
				array( '$project' => array( 'score' => array( '$meta' => "textScore" ) ) ) );
	$r = $c->aggregateCursor($p, array( 'allowDiskUse' => TRUE ) );
	
	echo( 'aggregateCursor();<pre>' );
	var_dump( gettype( $r ) );
	
	$i = 3;
	foreach( $r as $rec )
	{
		if( ! $i-- )
			break;
		var_dump( $rec );
	}
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
