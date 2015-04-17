#!/bin/bash

username=""
password=""

cmdpath="/home/johnsudaar/Documents/drinkiit/TestAPI/commander.php"

echo "On commande un sandwich poulet burger :"
$cmdpath $username $password sandwich "Poulet Burger"

echo "On commande un coca : "
$cmdpath $username $password soda "coca"
