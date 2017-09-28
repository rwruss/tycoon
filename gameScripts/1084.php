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

$sellingFactory = loadObject($postVals[1], $objFile, 1600);
echo 'Load selling factory.  Travel from '.$sellingFactory->get('region_3').' to '.$postVals[2].'<br>';
$pathNum = calcRouteNum($sellingFactory->get('region_3'), $postVals[2]);
//echo 'Factory at '.$sellingFactory->get('region_3').' to city '.$postVals[2].' is route '.$pathNum;

// Load the rotue information
echo 'Load route information';
//$pathInfo = loadRoutePath($pathNum, $routeFile);
$pathInfo = loadPath($pathNum, $routeFile);
$modeChanges = routeLegs($pathInfo);
print_r($pathInfo);
print_r($modeChanges);
//insert the default option
$legInfo = []; // leg, company, time, cost, vehicle, capacity
//echo 'There are '.sizeof($modeChanges).' mode changes';

$tmpArray = array_fill(0,13,0);
for ($i=0; $i<sizeof($modeChanges)/2; $i++) {
	//echo 'One mode from '.$modeChanges[$i].' to '.$modeChanges[$i+1].'<br>';

	//get the information for each leg
	$pathRoute = calcRouteNum($modeChanges[$i*2], $modeChanges[$i*2+1]);
	fseek($routeFile, $pathRoute*12);
	$pathHead = unpack('i*', fread($routeFile, 12));
	echo 'Mode '.$modeChanges[$i].' to '.$modeChanges[$i+1].' is route '.$pathRoute;
	print_r($pathHead);

	// Insert spot for default option for this leg
	array_push($legInfo, 0, $i,0,0,0,$pathHead[3],1,1,1,0,0,0,0); // optionID, legNum, routeID, owner, mode, distance, speed, cost/vol, cost/wt, cap-vol, cap-wt, status, vehicle
	$legInfo = array_merge($legInfo, loadRouteOptions($pathRoute, $transportFile));
}

fclose($objFile);
fclose($routeFile);
fclose($transportFile);

echo implode(',', $legInfo);

?>
