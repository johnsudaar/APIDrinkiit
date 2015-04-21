#!/usr/bin/php
<?php

require_once "Drinkiit.php";

//Drinkiit::configure("http://localhost:3000/api/");

if(count($argv) != 5){
  echo "Usage : ".$argv[0]." email pass meal comment".PHP_EOL;
  die();
}

$session = new Drinkiit($argv[1],$argv[2]);
if(! $session->getToken()){
  echo "Error : ".$session->getLastError().PHP_EOL;
  die();
}

if(! $user = $session->getUserInfo()){
  echo "Error : ".$session->getLastError().PHP_EOL;
  die();
}

echo "Bonjour ".$user->name." ".$user->surname.". Il vous reste ".$user->credit."€".PHP_EOL;

if(($commandes = $session->getOrders(Drinkiit::$ORDERS_PENDING)) === false){
  echo "Error : ".$session->getLastError().PHP_EOL;
  die();
}

echo "Vous avez ".count($commandes)." commandes en attente : ".PHP_EOL;
foreach($commandes as $commande){
  echo "   - ".$commande["quantity"]."x".$commande["name"]."(".$commande["comment"].") : ".$commande["price"].PHP_EOL;
}

$meals = $session->getMeals();

if( ! array_key_exists($argv[3],$meals)){
  echo "Error : unknown meal : ".$argv[3].PHP_EOL;
  die();
}

if( ! $session->order($meals[$argv[3]]["id"],1,$argv[4])){
  echo "Error : ".$session->getLastError().PHP_EOL;
  die();
}

echo "SUCCESS !".PHP_EOL;
?>
