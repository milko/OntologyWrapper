<?php

require_once("Cluster.php");

$points = Array();
for( $i = 0; $i < 100000; $i++ )
	$points[] = array( "location" => array( rand( -180, 180 ), rand( -90, 90 ) ) );

$cluster = new Cluster;
$clusterPoint = $cluster->createCluster($points, 4000, 11, 0);

echo "<pre>";
var_dump($clusterPoint);

?>