<?php

// Update a route

/*
PVS
0: Route ID
1-9: Stops
*/

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisPlayer = loadObject($pGameID, $objFile, 400);

// Verify that the player has access rights in all of the selected cities

// load areas where no routes are required
$freeAreas = new itemSlot(0, $transportFile, 40, TRUE);

// Load areas where this player has rights
$playerAreas = new itemSlot($thisPlayer->get('transportAccess'), $transportFile, 40);

$openAreas = [0,1,2,3,4,5,6,7,8,9];

// Load the cities and check each region for travel rights
$failedCheck = false;
$failedList = [];
$numCities = sizeof($postVals);
for ($i=1; $i<$numCities; $i++) {
  $checkCity = loadCity($postVals[$i], $cityFile);
  echo 'City '.$postVals[$i].' is in region '.$checkCity->get('nation');
  if (!in_array($checkCity->get('nation'), $openAreas)) {
    $failedCheck = true;
    $failedList[] = $postVals[$i];
  }
  echo '<br>';
}

if ($failedCheck) {
  exit('You don\'t have access to all of these areas');
}

// Adjust the route

fclose($cityFile);
fclose($transportFile);
fclose($objFile);


?>
