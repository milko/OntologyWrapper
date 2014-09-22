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
// Global includes.
//
require_once( 'includes.inc.php' );

//
// Style includes.
//
require_once( 'styles.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );
 
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
	$t = $d->selectCollection( '_tags' );
	$u = $d->selectCollection( '_units' );
	
	//
	// Count genus.
	//
	$list = Array();
	$query = array( "_id" => ":taxon:genus" );
	$tag = $t->findOne( $query );
	$offsets = $tag[ kTAG_UNIT_OFFSETS ];
	foreach( $offsets as $offset )
	{
		$data = $u->distinct( $offset );
		$list = array_merge( $list, $data );
	}
	$list = array_unique( $list );
	echo( "Genus: ".count( $list )."\n" );
	
	//
	// Count species.
	//
	$list = Array();
	$query = array( "_id" => ":taxon:epithet" );
	$tag = $t->findOne( $query );
	$offsets = $tag[ kTAG_UNIT_OFFSETS ];
	foreach( $offsets as $offset )
	{
		$data = $u->distinct( $offset );
		$list = array_merge( $list, $data );
	}
	$list = array_unique( $list );
	echo( "Species: ".count( $list )."\n" );
	
	//
	// Count countries.
	//
	$list = Array();
	$query = array( "_id" => ":location:country" );
	$tag = $t->findOne( $query );
	$offsets = $tag[ kTAG_UNIT_OFFSETS ];
	foreach( $offsets as $offset )
	{
		$data = $u->distinct( $offset );
		$list = array_merge( $list, $data );
	}
	$list = array_unique( $list );
	echo( "Countries: ".count( $list )."\n" );
	
	//
	// Count coordinates.
	//
	$list = Array();
	$query = array( "57" => array( '$exists' => TRUE ) );
	$rs = $u->find( $query );
	echo( "Coordinates: ".$rs->count()."\n" );
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
