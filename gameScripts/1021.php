<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[1], $cityFile);

 echo 'Hire labor type '.$postVals[3].' for city '.$postVals[1];

 // confirm there is enough labor of this type to hire

 // remove the labor from the city store

 // Add the labor and associated parameters to the city

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
