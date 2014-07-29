<?php

require_once("Cluster.php");

$points = array(
                array("location" => array(-81.14, 26.01)),
                array("location" => array(-82.44, 22.35)),
                array("location" => array(-80.24, 24.45)),
                array("location" => array(-81.05, 24.34)),
                array("location" => array(-82.54, 22.54)),
                array("location" => array(-84.74, 23.75)),
                array("location" => array(-81.55, 23.34)),
                array("location" => array(-81.32, 25.65))
);

$cluster = new Cluster;
$clusterPoint = $cluster->createCluster($points, 4000, 11, 0);

echo "<pre>";
var_dump($clusterPoint);

?>