<?php
//-- array polygon
/*
$polygon = array(array(50,50),
				 array(75,25),
				 array(105,55),
				 array(125,75),
				 array(100,100),
				 array(75,125),
				 array(50,100),
				 array(25,75));
*/
$polygon = array(array(-5, 5),
				 array(5, 5),
				 array(5, -5),
				 array(-5, -5),
				 array(-5, 5));
$er = polyCenter($polygon);
var_dump($er);

function polyCenter($polygon){
  $x=$y=0;
  $n = count($polygon);
  for($i=0;$i<$n;$i++){
    $x+=$polygon[$i][0];
    $y+=$polygon[$i][1];
  }
/*
  $x = round($x/$n); 
  $y = round($y/$n);
*/
  $x = $x/$n; 
  $y = $y/$n;
  return $x.":".$y;
}
?>