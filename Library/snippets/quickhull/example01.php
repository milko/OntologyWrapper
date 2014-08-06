<?php
include( 'convex_hull.php' );

$points = Array();
for( $i = 0; $i < 10000; $i++ )
	$points[] = array( rand( -180, 180 ), rand( -90, 90 ) );

$hull = new ConvexHull( $points );

var_dump( $hull->getHullPoints() );
