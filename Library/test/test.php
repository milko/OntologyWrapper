<?php

/*	
	//
	// Connect.
	//
	$m = new MongoClient();
	$d = $m->selectDB( 'TEST' );
	$c = $d->selectCollection( '_edges' );
	
	//
	// Set criteria.
	//
	$criteria
		= array(
			'$or' => array(
				array(
					'16' => 42 ),
				array(
					'18' => 42 ) ),
			'17' => ":relationship:predicate:SUBCLASS-OF" );
	
	//
	// Show.
	//
	var_dump( $criteria );
	
	//
	// Query.
	//
	$rs = $c->find( $criteria, array( '_id' ) );
	
	//
	// Show.
	//
	var_dump( $rs->count() );
*/
	
/******************************************************************************/
	
/*
	//
	// Test nested array objects.
	//
	
	//
	// Define functions.
	//
	function level1( & $object, $value )
	{
		level2( $object[ 'new' ], $value );
	}
	function level2( & $object, $value )
	{
		level3( $object[ 'new' ], $value );
	}
	function level3( & $object, $value )
	{
		level4( $object[ 'new' ], $value );
	}
	function level4( & $object, $value )
	{
		$object[ 'PIPPO' ] = $value;
	}
	
	//
	// Allocate objects.
	//
	$a = new ArrayObject( array( 'a1' => 1, 'a2' => 2, 'a3' => array( 'uno', 'due', 'tre' ) ) );
	$b = new ArrayObject( array( 'b1' => 11, 'b2' => 22, 'b3' => 33 ) );
	$c = new ArrayObject( array( 'c1' => 111, 'c2' => 222, 'c3' => 333 ) );
	
	//
	// Nest objects.
	//
	$b[ 'new' ] = $c;
	$a[ 'new' ] = $b;
	
	//
	// Display object.
	//
	var_dump( $a );
	
	//
	// Get nested element.
	//
	echo( '$a[ "new" ][ "new" ][ "c2" ];<br />' );
	var_dump( $a[ "new" ][ "new" ][ "c2" ] );
	
	//
	// Set nested element.
	//
	echo( '$a[ "new" ][ "new" ][ "c2" ] = "CHANGED";<br />' );
	$a[ "new" ][ "new" ][ "c2" ] = "CHANGED";
	var_dump( $a );
	
	//
	// Set nested element with functions.
	//
	echo( 'level1( $a, "WITH FUNCTION" );<br />' );
	level1( $a, "WITH FUNCTION" );
	var_dump( $a );
	
	//
	// Set nested element with index.
	//
	echo( '$a[ "new" ][ "new" ][ "new" ][ "PIPPO" ] = "AGAIN";<br />' );
	$a[ "new" ][ "new" ][ "new" ][ "PIPPO" ] = "AGAIN";
	var_dump( $a );
/*
	
/******************************************************************************/
	
	//
	// Test nested offsets match.
	//
	
	//
	// Set pattern.
	//
	$pat = '/^\d+(\.\d+)+/';
	
	//
	// Test.
	//
	echo( 'preg_match( $pat, "1" );<br />' );
	var_dump( preg_match( $pat, "1" ) );

	echo( 'preg_match( $pat, "1.22" );<br />' );
	var_dump( preg_match( $pat, "1.22" ) );

	echo( 'preg_match( $pat, "1.22.333" );<br />' );
	var_dump( preg_match( $pat, "1.22.333" ) );

	echo( 'preg_match( $pat, "1." );<br />' );
	var_dump( preg_match( $pat, "1." ) );

	echo( 'preg_match( $pat, ".1" );<br />' );
	var_dump( preg_match( $pat, ".1" ) );

	echo( 'preg_match( $pat, "1..11" );<br />' );
	var_dump( preg_match( $pat, "1..11" ) );

?>