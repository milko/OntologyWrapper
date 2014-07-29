<?php
include( 'convex_hull.php' );

$points = Array();
for( $i = 0; $i < 100000; $i++ )
	$points[] = array( rand( 0, 1000000 ), rand( 0, 1000000 ) );

$hull = new ConvexHull( $points );

var_dump( $hull->getHullPoints() );
