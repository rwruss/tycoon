<?php

/*
Update the pay rate for a worker at a factory

PostVals:
1 -> factory ID
2 -> labor item (numbered 1-10 so need to subtract 1 to get actual location)
3 -> new pay rate
*/

// Show labot item detail at a factory
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the factory and get the labor details for the item in question
$thisFactory = loadObject($postVals[1], $objFile, 1000);

// Verify that player can make changes to this factory

// Verify that the new pay rate is in the range of 0 - 1000.00
if ($postVals[3] < 0 || $postVals[3] > 1000) exit("error 4201-1");

$baseLaborIndex = $thisFactory->laborOffset + ($postVals[2]-1)*10;
$thisFactory->saveItem($baseLaborIndex+5, intval($postVals[3]*100));

fclose($cityFile);
fclose($objFile);

?>
