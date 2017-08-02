<?php

// Update a route

/*
PVS
0: Route ID
1-2; vol cost and weight cost
3-12: Stops
*/
print_r($postVals);

require_once('./objectClass.php');
require_once('./slotFunctions.php');
require_once('./transportClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$routeFile = fopen($gamePath.'/routes.rtf', 'rb');

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
$maxIndex = sizeof($postVals)-1;
for ($i=3; $i<$maxIndex; $i++) {
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
$maxIndex--; // adjust the index to be one less than the number of stops for the number of routes
$routeList = [];

// get information about each leg of the route and check trans modes
$routeBody = array_fill(1,40,0);
for ($i=4; $i<$maxIndex; $i++) {
	$pathRoute = calcRouteNum($postVals[$i], $postVals[$i+1]);
	$routeList[] = $pathRoute;

  echo 'Seek to path '.$pathRoute.'<br>';
	fseek($routeFile, $pathRoute*12);
	$pathHead = unpack('i*', fread($routeFile, 12));

  print_r($pathHead);

	$routeBody[$i-2] = $postVals[$i];
	$routeBody[$i-2+1] = $postVals[$i+1];
	$routeBody[$i-2+10] = $pathHead[3];

	// Get a list of the cities on the route and make sure they are all the same transport type
	fseek($routeFile, $pathHead[1]);
	$pathDat = unpack('i*', fread($routeFile, $pathHead[2]));
	$routeType = $pathDat[2];
	for ($j=5; $j<sizeof($pathDat); $j+=5) {
		if ($pathDat[$j] != $routeType) {
			exit('Type fail at node '.$i);
    }
	}
}

fseek($transportFile, $postVals[0]*140);
$oldDat = fread($transportFile, 140);

$oldHead = unpack('i*', substr($oldDat, 0, 56));

$routeHead = $oldHead;
echo 'RouteHead:';
print_r($routeHead);
$routeHead[3] = $postVals[2]*100;
$routeHead[4] = $postVals[3]*100;

/*
$routeHead[0] = $pGameID; // owner
$routeHead[1] = 0; // type/mode
$routeHead[2] = $pGameID; // speed
$routeHead[3] = $pGameID; // cost/vol
$routeHead[4] = $pGameID; // cost/weight
$routeHead[5] = $pGameID; // capactiy - volume
$routeHead[6] = $pGameID; // capacity - weight
$routeHead[7] = $pGameID; // status
$routeHead[8] = $pGameID; // runs/day
$routeHead[9] = 0; // Lifetime Cost
$routeHead[10] = 0; // Lifetime Earning
$routeHead[11] = 0; // Period Cost
$routeHead[12] = 0; // Period Earnings
$routeHead[13] = $pGameID; // Vehicle*/


// Determine which route options need to be delete and check if the route has changed
$routeChange = false;
$oldRoutes = unpack('s*', substr($oldDat, 56, 20));
$oldRoutes = array_fill(1,10,0);
for ($i=1; $i<11; $i++) {
	if (!in_array($oldRoutes[$i], $routeList)) {
		$legRoutes = new itemSlot($oldRoutes[$i], $transportFile, 40);
		$legRoutes->deleteByValue($routeHead[1], $transportFile);
	}

	if ($oldRoutes[$i] != $postVals[$i]) $routeChange = true;
}

// Make sure that this route is noted in each of the routes for transport options
for ($i=0; $i<sizeof($routeList); $i++) {
	if (!in_array($routeList[$i], $oldRoutes)) {
		$legRoutes = new itemSlot($routeList[$i], $transportFile, 40);
		$legRoutes->addItem($routeHead[1], $transportFile);
	}
}

if ($routeChange) {
	$routeHead[9] = 0;
	$routeHead[10] = 0;
	$routeHead[11] = 0;
	$routeHead[12] = 0;
}

echo 'route head:';
print_r($routeHead);
echo '<p>route body:';
print_r($routeBody);

fwrite($transportFile, packArray($routeHead, 'i'));
fwrite($transportFile, packArray($routeBody, 's'));

fclose($routeFile);
fclose($cityFile);
fclose($transportFile);
fclose($objFile);


?>
