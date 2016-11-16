<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[1], $cityFile);

$now = time();
$thisCity->updateLabor($now);

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
