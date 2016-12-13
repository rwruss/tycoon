<?php

/*
Update the pay rate for a worker at a factory
1 -> factory ID
2 -> labor item (numbered 1-10 so need to subtract 1 to get actual location)
3 -> new position id
*/

// Show labot item detail at a factory
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the factory and get the labor details for the item in question
$thisFactory = loadObject($postVals[1], $objFile, 1000);
$laborDetails = array_slice($thisFactory->objDat, $thisFactory->laborOffset + ($postVals[2]-1)*10 - 1, 10); // correction to post vals since it starts at index 1

// Verify that player can make changes to this factory

// Load list of available promotions for this labor type

// Verify that the position is valid for this labor type
if (0) exit("error 5201-1");

// Update labor type
$baseLaborIndex = $thisFactory->laborOffset + ($postVals[2]-1)*10;
$thisFactory->saveItem($baseLaborIndex+5, intval($postVals[3]*100));

print_r($laborDetails);

fclose($cityFile);
fclose($objFile);

?>