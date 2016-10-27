<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Create a new factory object
echo 'Template type: '.$postVals[2];
$thisObj = loadObject($postVals[2], $objFile, 1000);

print_r($thisObj->objDat);

fclose($objFile);
fclose($slotFile);

?>
