<?php

	//
	// Test hashed serial identifiers.
	//
	
	require_once( "includes.inc.php" );
	require_once( "local.inc.php" );
	require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );
	
	//
	// Connect.
	//
	$m = new MongoClient( 'mongodb://192.168.181.191:27017' );
	$d = $m->selectDB( 'BIOVERSITY' );
	$c = $d->selectCollection( '_units' );
	
	//
	// Select records.
	//
	$rs = $c->find( array( '@9' => ':domain:accession' ) );
	
	//
	// Limit record.
	//
	$rs->limit( 1 );
	
	//
	// Sort records.
	//
	$rs->sort( array('@3e' => -1 ) );
	
	//
	// Get first element.
	//
	foreach( $rs as $record )
		print_r( $record );
	
?>