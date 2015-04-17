#!/usr/bin/php
<?php

require_once "Drinkiit.php";

$meals = array();
$meals["soda"] = 21;
$meals["sandwich"] = 20;
$meals["paninisaumon"] =  17;
$meals["paninizza"] = 18;
$meals["paninipoulet"] = 19;

if(count($argv) != 5){
  echo "Usage : ".$argv[0]." email pass meal comment".PHP_EOL;
  die();
}

$session = new Drinkiit($argv[1],$argv[2]);
if(! $session->getToken()){
  echo "Error : ".$session->getLastError().PHP_EOL;
  die();
}

if( ! array_key_exists($argv[3],$meals)){
  echo "Error : unknown meal : ".$argv[3].PHP_EOL;
  die();
}

if( ! $session->order($meals[$argv[3]],1,$argv[4])){
  echo "Error : ".$session->getLastError().PHP_EOL;
  die();
}

echo "SUCCESS !".PHP_EOL;
?>
