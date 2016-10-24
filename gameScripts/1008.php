<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[2], $objFile, 400);

print_r($thisObj->objDat);

fclose($objFile);
fclose($slotFile);

?>
