<?php

	require_once( "ClusterBis.php" );

	//
	// Connect to database.
	//
	$m = new MongoClient();
	$d = $m->selectDB( "PGRDG" );
	$c = $d->selectCollection( '_units' );
	
	//
	// Select all shapes.
	//
	$criteria = array( "47" => array( '$in' => array( 189, 195 ) ) );
	$fields = new ArrayObject( array( "_id" => FALSE, "189" => TRUE, "195" => TRUE ) );
	$rs = $c->find( $criteria, $fields );
	
	echo( '==> Found '.$rs->count()." coordinates.\n" );
	
	//
	// Cluster points.
	//
	$cluster = new ClusterBis();
	$clusterPoints
		= $cluster->createCluster(
			array( iterator_to_array( $rs ) ),
			4000,
			11,
			0 );
	
	echo( '==> Clustered '.count( $clusterPoints )." points.\n\n" );
	
	//
	// Display points.
	//
/*
	echo( "<pre>" );
	print_r( $clusterPoints );
	echo( '</pre>' );
*/

?>