<?php

// Load the recommended route for a sale and provide transport options
/*
PVS
1 = Selling Factory ID
2 = Target City ID
3 = Product ID
4 = Sale Qty
*/

require_once('./objectClass.php');
require_once('./transportClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$routeFile = fopen($gamePath.'/routes.rtf', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');

// Get product information
$prodSpace = 1;
$prodWeight = 1;

// Load the selling factory to get the origin location
$sellingFactory = loadObject($postVals[1], $objFile, 1000);
$pathNum = calcRouteNum($sellingFactory->get('region_3'), $postVals[2]);
//echo 'Factory at '.$sellingFactory->get('region_3').' to city '.$postVals[2].' is route '.$routeNum;

// Load the rotue information
$pathInfo = loadRoutePath($routeFile, $pathNum);
$modeChanges = routeLegs($pathInfo);

//insert the default option
$legInfo = []; // leg, company, time, cost, vehicle, capacity
for ($i=0; $i<sizeof($modeChanges); $i+=2) {
	//echo 'One mode from '.$modeChanges[$i].' to '.$modeChanges[$i+1].'<br>';

	//get the information for each leg
	$legRoute = calcRouteNum($modeChanges[$i], $modeChanges[$i+1]);
	fseek($routeFile, $legRoute*4);
	$legHead = unpack('i*', fread($routeFile, 12));

	// Insert spot for default option for this leg
	array_push($legInfo, $i, 0,0,0,0,$legHead[3],1,0,0,0,0,0,0); // optionID, legNum, routeID, owner, mode, distance, speed, cost/vol, cost/wt, cap-vol, cap-wt, status, vehicle

	// Look up available transport for each segment of the route
	$legRoutes = new itemSlot($legRoute, $transportFile, 40);
	for ($i=1; $i<=sizeof($legRoutes->slotData); $i++) {
		// Load the route data
		fseek($transportFile, $legRoutes->slotData[$i]);
		$routeDat = fread($transportFile, 100);
		$routeInfo = unpack('i*', substr($routeDat, 0, 40));
		$routeStops = unpack('s*', substr($routeDat, 56, 40));

		// Determine total travel time
		$routeDist = array_sum(array_slice($routeStops, 11));
		$routeTime = $routeDist/$routeInfo[3];
	}
}

fclose($objFile);
fclose($routeFile);
fclose($transportFile);

echo implode(',', $legInfo);

?>
