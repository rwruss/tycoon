<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');
// Load list of subregions for a given parent region
/*
$regionListFile = fopen($gamePath.'/regionSlots.slt', 'rb');
$thisRegionList = new itemList($postVals[1], $regionListFile, 40);
*/
/*
$newTier = $postVals[2]+1;
echo ($newTier).','.$tierOffset[$newTier].','.array_filter($thisRegionList->slotData);
*/
if ($postVals[2] == 1) echo '2,100,Texas,New Mexico';
if ($postVals[2] >= 2) echo '3,1000,Austin,RR';


?>
