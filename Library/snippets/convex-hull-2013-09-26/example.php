<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
***************************************************************/
require_once("convex-hull.php");

// example 1
$chull=new convexhull();
$chull->main();
$pic=new visualize("/tmp/",$chull);
$pic->genimage();

//example2
$set=array();
$tree=array(172,31,238,106,233,397,118,206,58,28,268,382,10,380,342,26,67,371,380,14,382,200,24,200,194,190,10,88,276);
for ($i=0,$end=count($tree);$i<$end;$i+=2)
{
    $set[]=array($tree[$i],$tree[$i+1]);    
}
$chull=new convexhull();
$chull->main($set);
$pic=new visualize("/tmp/",$chull);
$pic->genimage(); 

//example3
$file = fopen("star.dat", "r");
$set=array();
while (!feof($file))
{
    list($x,$y)=explode(" ",rtrim(fgets($file)));
    $set[] = array(round($x),round($y));  
}
fclose($file);
$chull->main($set,500,400);
 
$pic=new visualize("/tmp/",$chull);
$pic->genimage();

//example4
$file = fopen("strange.dat", "r");
$set=array();
while (!feof($file))
{
    list($x,$y)=explode(" ",rtrim(fgets($file)));
    $set[] = array(round($x),round($y));  
}
fclose($file);
$chull=new convexhull();
$chull->main($set,500,500);
$pic=new visualize("/tmp/",$chull);
$pic->genimage();

//example5
$mapPadding  = 100;
$mapWidth    = 500;
$mapHeight   = 500;
$mapLonLeft  =1000;
$mapLatBottom=1000;
$mapLonRight =   0;
$mapLatTop   =   0;
$set=array();
$geocoord = array ("8.6544487,50.1005233",
                   "8.7839489,50.0907496",
                   "8.1004734,50.2002273",
                   "8.4117234,50.0951493",
                   "8.3508367,49.4765982",
                   "9.1828630,48.7827027",
                   "9.1686483,48.7686426",
                   "9.2118466,48.7829101",
                   "8.9670738,48.9456327");

foreach ($geocoord as $key => $arr)
{
    list($lon,$lat) = explode(",",$arr);
    $mapLonLeft = min($mapLonLeft,$lon);
    $mapLonRight = max($mapLonRight,$lon);
    $mapLatBottom = min($mapLatBottom,$lat);
    $mapLatTop = max($mapLatTop,$lat);
    $set[]=array($lon,$lat);
}

$mapLonDelta = $mapLonRight-$mapLonLeft;
$mapLatDelta = $mapLatTop-$mapLatBottom;
$mapLatTopY=$mapLatTop*(M_PI/180);
$worldMapWidth=(($mapWidth/$mapLonDelta)*360)/(2*M_PI);
$LatBottomSin=min(max(sin($mapLatBottom*(M_PI/180)),-0.9999),0.9999);
$mapOffsetY=$worldMapWidth/2 * log((1+$LatBottomSin)/(1-$LatBottomSin));
$LatTopSin=min(max(sin($mapLatTop*(M_PI/180)),-0.9999),0.9999);
$mapOffsetTopY=$worldMapWidth/2 * log((1+$LatTopSin)/(1-$LatTopSin));
$mapHeightD=$mapOffsetTopY-$mapOffsetY;
$mapRatioH=$mapHeight/$mapHeightD;
$newWidth=$mapWidth*($mapHeightD/$mapHeight);
$mapRatioW=$mapWidth/$newWidth;

foreach ($set as $key => $arr)
{
    list($lon,$lat) = $arr;
    $tx = ($lon - $mapLonLeft) * ($newWidth/$mapLonDelta)*$mapRatioW;
    $f = sin($lat*M_PI/180);
    $ty = ($mapHeightD-(($worldMapWidth/2 * log((1+$f)/(1-$f)))-$mapOffsetY));
}

$chull=new convexhull();
$chull->main($set,$mapWidth,$mapHeightD);
 
$pic=new visualize("/tmp/",$chull);
$pic->genimage();
?>