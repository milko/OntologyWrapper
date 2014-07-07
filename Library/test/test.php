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
			'17' => ":predicate:SUBCLASS-OF" );
	
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
	
/*
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
*/	

/******************************************************************************/
	
/*
	//
	// Test numeric index arrays.
	//
	
	echo( 'array( "test" => 1, 22 => 2 ):<br />' );
	$array = array( "test" => 1, 22 => 2 );
	var_dump( $array );
	
	echo( 'array( "test" => 1, "22" => 2 ):<br />' );
	$array = array( "test" => 1, "22" => 2 );
	var_dump( $array );
	
	$x = (string) "22";
	echo( 'array( "test" => 1, $x => 2 ):<br />' );
	$array = array( "test" => 1, $x => 2 );
	var_dump( $array );
	
	echo( '$object = new StdClass();<br />' );
	$object = new StdClass();
	echo( '$object->_id = 1;<br />' );
	$object->_id = 1;
	echo( '$object->22 = 2;<br />' );
	$object->22 = 2;
	var_dump( $object );
*/

/******************************************************************************/

/*	
	//
	// Connect.
	//
	$m = new MongoClient();
	$d = $m->selectDB( 'TEST' );
	$c = $d->selectCollection( 'bubu' );
	$c->drop();
	
	//
	// Insert object.
	//
	$c->insert( array( "_id" => 1, "22" => "twentytwo", "33" => "Thirtythree" ) );
	
	//
	// It works.
	//
	
	//
	// Set index.
	//
	$c->createIndex( array( "22" => 1 ) );
	
	//
	// Find object.
	//
	$fields = new ArrayObject();
	$fields[ '22' ] = TRUE;
	$x = $c->find( array( "_id" => 1, "22" => "twentytwo" ), $fields );
var_dump( iterator_to_array( $x ) );
	
	//
	// Find object.
	//
	$fields = array( 22 => 1 );
	$fields = new ArrayObject( $fields );
	$x = $c->find( array( "_id" => 1, "22" => "twentytwo" ), $fields );
var_dump( iterator_to_array( $x ) );
	
	//
	// Find object.
	//
	$fields = array( "22" => 1 );
	$x = $c->find( array( "_id" => 1, "22" => "twentytwo" ), $fields );
var_dump( iterator_to_array( $x ) );
	
	//
	// This posts the error:
	// "MongoException: field names must be strings"
	//
*/

/******************************************************************************/
	
/*
	//
	// Connect.
	//
	$m = new MongoClient();
	$d = $m->selectDB( 'USERS' );
	$c = $d->selectCollection( 'CUser' );
	
	//
	// Find object.
	//
	$criteria = array( '$and' => array( array( '46' => 'admin' ), array( '52' => 'TIP' ) ) );
var_dump( $criteria );
	$x = $c->findOne( $criteria );
var_dump( $x );
*/

/******************************************************************************/
	
/*
	//
	// Test geo.
	//
	
	//
	// Connect.
	//
	$m = new MongoClient( 'mongodb://mongo1.grinfo.private:27017' );
	$d = $m->selectDB( 'GEO' );
	$c = $d->selectCollection( 'LAYERS-30' );
	
	//
	// Test near.
	//
	$criteria = array
	(
		'geoNear' => 'LAYERS-30',
		'near' => array
		(
			'type' => 'Point',
			'coordinates' => array( 7.456, 46.302 )
		),
		'spherical' => TRUE,
		'maxDistance' => 200000,
		'query' => array( 'elev' => array( '$gte' => 2000, '$lte' => 3000 ) )
	);
	$rs = $d->command( $criteria, array( 'socketTimeoutMS' => 60000 ) );
	var_dump( $rs );
*/
	
/******************************************************************************/
	
/*
	//
	// Test map server clustering.
	//
	
	//
	// Init local storage.
	//
	$lonmin = -180;
	$latnmin = -90;
	$lonmax = 180;
	$latnmax = 90;
	$delta = ( $lonmax - $lonmin ) / 16;
	
	//
	// Connect.
	//
	$m = new MongoClient( 'mongodb://192.168.181.1:27017' );
	$d = $m->selectDB( 'DATA' );
	$c = $d->selectCollection( ':_units' );
	
	//
	// QUery stages.
	//
	$match = array
	(
//		"9" => "FRA144",
		
		"57.75" => array
		(
			'$geoWithin' => array
			(
				'$box' => array
				(
					array( $lonmin, $latnmin ),
					array( $lonmax, $latnmax )
				)
			)
		)
	);

	
	//
	// Test get points in pane.
	//
	$rs = $c->find
	(
		array
		(
			"9" => "FRA144",
			
			"57.75" => array
			(
				'$geoWithin' => array
				(
					'$box' => array
					(
						array( $lonmin, $latnmin ),
						array( $lonmax, $latnmax )
					)
				)
			)
		)
	);
	echo( $rs->count() );
*/
	
/******************************************************************************/

/*	
	//
	// Test array keys.
	//
	
	//
	// Init local storage.
	//
	$array = [ "-1" => "meno uno", "1" => "uno", "3" => "tre" ];
	
	//
	// View keys.
	//
	var_dump( array_keys( $array ) );
	
	//
	// Test intersect.
	//
	var_dump( array_intersect( array_keys( $array ), array_keys( $array ) ) );
	
	echo( '<hr>' );
	
	//
	// Init local storage.
	//
	$array = [ "-1" => "meno uno", "1.2" => "uno", "3.3" => "tre" ];
	
	//
	// View keys.
	//
	var_dump( array_keys( $array ) );
	
	//
	// Test intersect.
	//
	var_dump( array_intersect( array_keys( $array ), array_keys( $array ) ) );
*/
	
/******************************************************************************/

/*
	//
	// Test json mongo query.
	//
	
	//
	// Set json query.
	//
	$query = '{ "$and" : [ { "$or" : [ { "47" : 165 }, { "47" : 29 } ] }, { "$or" : [ { "$and" : [ { "47" : 73 }, { "$or" : [ { "73" : ":kind:entity:100" }, { "72" : ":type:entity:100" } ] } ] }, { "$and" : [ { "47" : 81 }, { "$or" : [ { "81" : "iso:3166:1:alpha-3:ALB" }, { "165" : "iso:3166:1:alpha-3:ITA" } ] } ] } ] } ] }';
	
	//
	// Show info.
	//
	$message = <<<EOT
<ul>
	<li>The root clause in an <b><tt>\$and</tt></b> clause array.
	<li>Each cluster is an element of the root <b><tt>\$and</tt></b> clause array.
	<li>For each cluster:
	 <ul>
	 	<li>Create an array element at the root level and reference it <em>REF</em>.
	 	<li>If the cluster has one element:
	 	 <ul>
	 	 	<li>If the cluster element has a match value:
	 	 	 <ul>
	 	 	 	<li>If the cluster element is indexed:
	 	 	 	 <ul>
	 	 	 	 	<li>If the cluster element has more than one offset:
	 	 	 	 	 <ul>
	 	 	 	 	 	<li>Create an <tt>\$or</tt> clause in <em>REF</em>.
	 	 	 	 	 	<li>For each offset:
	 	 	 	 	 	 <ul>
	 	 	 	 	 	 	<li>Create an array element in the <tt>\$or</tt> clause.
	 	 	 	 	 	 	<li>Load the offset clause in this element.
	 	 	 	 	 	 </ul>
	 	 	 	 	 </ul>
	 	 	 	 	<li>If the cluster element has one offset:
	 	 	 	 	 <ul>
	 	 	 	 	 	<li>Create an array element in the <em>REF</em> clause.
	 	 	 	 	 	<li>Load the offset clause in the element.
	 	 	 	 	 </ul>
	 	 	 	 </ul>
	 	 	 	<li>If the cluster element is not indexed:
	 	 	 	 <ul>
	 	 	 	 	<li>Create an array element in the <em>REF</em> clause.
	 	 	 	 	<li>Add the <tt>kTAG_TAGS</tt> clause in the element.
	 	 	 	 	<li>If the cluster element has more than one offset:
	 	 	 	 	 <ul>
	 	 	 	 	 	<li>Create an <tt>\$or</tt> clause in <em>REF</em>.
	 	 	 	 	 	<li>For each offset:
	 	 	 	 	 	 <ul>
	 	 	 	 	 	 	<li>Create an array element in the <tt>\$or</tt> clause.
	 	 	 	 	 	 	<li>Load the offset clause in this element.
	 	 	 	 	 	 </ul>
	 	 	 	 	 </ul>
	 	 	 	 	<li>If the cluster element has one offset:
	 	 	 	 	 <ul>
	 	 	 	 	 	<li>Load the offset clause in <em>REF</em>.
	 	 	 	 	 </ul>
	 	 	 	 </ul>
	 	 	 </ul>
	 	 	<li>If the cluster element has no match value:
	 	 	 <ul>
	 	 	 	<li>Add the <tt>kTAG_TAGS</tt> clause in the em>REF</em>.
	 	 	 </ul>
	 	 </ul>
	 	 
	 	<li>If the cluster has more than one element:
	 	 <ul>
	 	 	<li>Create an <tt>\$or</tt> clause in the <em>REF</em>, alias with <em>REF</em>.
	 	 	<li>For each cluster element:
	 	 	 <ul>
				<li>If the cluster element has a match value:
				 <ul>
					<li>If the cluster element is indexed:
					 <ul>
						<li>If the cluster element has more than one offset:
						 <ul>
							<li>For each offset:
							 <ul>
								<li>Create an array element in the <em>REF</em>.
								<li>Load the offset clause in this element.
							 </ul>
						 </ul>
						<li>If the cluster element has one offset:
						 <ul>
							<li>Create an array element in the <em>REF</em>.
							<li>Load the offset clause in this element.
						 </ul>
					 </ul>
					<li>If the cluster element is not indexed:
					 <ul>
						<li>Create an <tt>\$and</tt> clause.
						<li>Load the <tt>\$and</tt> clause with the <tt>kTAG_TAGS</tt> clause.
						<li>If the cluster element has more than one offset:
						 <ul>
							<li>Create an <tt>\$or</tt> clause in the above <tt>\$and</tt> clause.
							<li>For each offset:
							 <ul>
								<li>Create an array element in the <tt>\$or</tt> clause.
								<li>Load the offset clause in this element.
							 </ul>
						 </ul>
						<li>If the cluster element has one offset:
						 <ul>
							<li>Load the offset clause in the above <tt>\$and</tt> clause.
						 </ul>
					 </ul>
				 </ul>
				<li>If the cluster element has no match value:
				 <ul>
					<li>Add the <tt>kTAG_TAGS</tt> clause in the em>REF</em>.
				 </ul>
	 	 	 </ul>
	 	 </ul>
	 </ul>
</ul>
<i>Note that the root <b><tt>kTAG_TAGS</tt></b> clauses are to be added as separate elements: the <b><tt>\$and</tt></b> clause is faster than the <b><tt>\$all</tt></b> clause.</i>
EOT;
	echo( $message );
		 
	
	//
	// View PHP.
	//
	echo( '<pre>' );
	print_r( json_decode( $query, TRUE ) );
	echo( '</pre>' );

/*

if( clusters many )
{
	create AND;
	if( has value )
	{
		if( indexed )
		{
			if( many offsets )
			{
			}
			else
			{
			}
		}
		else
		{
			if( many offsets )
			{
			}
			else
			{
			}
		}
	}
	else
	{
		if( indexed )
		{
			if( many offsets )
			{
			}
			else
			{
			}
		}
		else
		{
			if( many offsets )
			{
			}
			else
			{
			}
		}
	}
}
else
{
	if( (! has value)
	 || (! indexed) )
		add tag match;
	
	if( (has value)
	 && (many offsets) )
		add OR;
	
	add criteria;
}

================================================================================

if( count( $clusters ) > 1 )
	add AND;

foreach( $clusters as $cluster )
{
	//
	// Cluster has values.
	//
	if( cluster[ values ] )
	{
		if( count( cluster[ criteria ] ) > 1 )
		{
			foreach( cluster[ criteria ] as $tag => $clause )
			{
				if( $clause !== NULL )
				{
					if( $clause[ indexed ] )
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
					else
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
				}
				else
				{
					if( $clause[ indexed ] )
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
					else
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
				}
			}
		}
		else
		{
			foreach( cluster[ criteria ] as $tag => $clause )
			{
				if( $clause !== NULL )
				{
					if( $clause[ indexed ] )
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
					else
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
				}
				else
				{
					if( $clause[ indexed ] )
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
					else
					{
						if( count( $clause[ offsets ] > 1 )
						{
						}
						else
						{
						}
					}
				}
			}
		}
	}
	
	//
	// Cluster has no values.
	//
	else
	{
		foreach( cluster[ criteria ] as $tag => $clause )
		{
			if( count( cluster[ criteria ] ) > 1 )
				Add tag match to '$in';
			else
				Add tag match equals;
		}
	}
}

*/
	
/******************************************************************************/

	//
	// Test aggregation framework.
	//

	//
	// Connect.
	//
	$m = new MongoClient();
	$d = $m->selectDB( 'TEST' );
	$c = $d->selectCollection( '_units' );
	
	//
	// Init local storage.
	//
	$pipeline = Array();
	
	//
	// Set match.
	//
	$pipeline[]
		= array(
			'$match' => array(
				'173' => 'iso:3166:1:alpha-3:AUT' ) );
	
	//
	// Set project.
	//
	$pipeline[]
		= array(
			'$project' => array(
				'7' => 1,
				'173' => 1,
				'57' => array(
					'$cond' => array(
						'if' => '$57.type',
						'then' => 1,
						'else' => 0 ) ) ) );
	
	//
	// Set group.
	//
	$pipeline[]
		= array(
			'$group' => array(
				'_id' => array(
					'173' => '$173',
					'7' => '$7' ),
					'count' => array(
						'$sum' => 1 ),
					'markers' => array(
						'$sum' => '$57' ) ) );
	
	//
	// Show.
	//
	echo( '<h4>Pipeline:</h4>' );
	var_dump( $pipeline );
	
	//
	// Query.
	//
	$rs = $c->aggregateCursor( $pipeline );
	
	//
	// Show.
	//
	echo( '<h4>Results:</h4>' );
	var_dump( iterator_to_array( $rs ) );
	
/******************************************************************************/

/*

//
// Decode URL.
//

var_dump( urldecode( 'op=matchUnits&lang=en&param=%7B%22limit%22:50,%22skipped%22:0,%22log-request%22:%22true%22,%22criteria%22:%7B%22:location:country%22:%7B%22input-type%22:%22input-enum%22,%22term%22:%5B%22%22%5D%7D%7D,%22result-domain%22:%22:domain:accession%22,%22result-data%22:%22record%22%7D' ) );

*/

	
?>