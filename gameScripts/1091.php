<?php

require_once('./objectClass.php');
require_once('./transportClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');

echo 'Purchase vehicle type '.$postVals[1];

// create a new route spot for this transport item
$routeHead = array_fill(0, 14, 0);
$routeBody = array_fill(1,40,0);

$routeHead[0] = $pGameID; // owner
$routeHead[1] = 0; // type/mode
$routeHead[2] = 0; // speed
$routeHead[3] = 0; // cost/vol
$routeHead[4] = 0; // cost/weight
$routeHead[5] = 0; // capactiy - volume
$routeHead[6] = 0; // capacity - weight
$routeHead[7] = 0; // status
$routeHead[8] = 0; // runs/day
$routeHead[9] = 0; // Lifetime Cost
$routeHead[10] = 0; // Lifetime Earning
$routeHead[11] = 0; // Period Cost
$routeHead[12] = 0; // Period Earnings
$routeHead[13] = $postVals[1]; // Vehicle*/

$routeID = 0;
if (flock($transportFile, LOCK_EX)) {
  fseek($transportFile, 0, SEEK_END);
  $routeID = ftell($transportFile);

  fwrite($transportFile, packArray($routeHead, 'i'));
  fwrite($transportFile, packArray($routeBody, 's'));
  flock($transportFile, LOCK_UN);
}

echo 'Created route '.$routeID;

// add to the list of players transport items
$thisPlayer = loadObject($pGameID, $objFile, 400);

// Load areas where this player has rights
if ($thisPlayer->get('transportOptions') == 0) {
  $newSlot = newSlot($transportFile, 40);
  $thisPlayer->save('transportOptions', $newSlot);
}

$routeList = new itemSlot($thisPlayer->get('transportOptions'), $transportFile, 40);
$routeList->addItem($routeID, $transportFile);

fclose($transportFile);
fclose($objFile);
?>
