<?php

/*
PVS:
1 - factory ID
2 - order ID Number
*/

// load routes for a shipment to a factory
require_once('./objectClass.php');
require_once('./transportClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$routeFile = fopen($gamePath.'/routes.rtf', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');

// Get product information
$prodSpace = 1;
$prodWeight = 1;

// Get the origin of the shipment
$thisOffer = loadOffer($postVals[2], $offerDatFile);

// get the destination of the shipment
print_r($thisOffer->objDat);
$origin = $thisOffer->objDat[17];
$dest = $thisOffer->objDat[18];

$legInfo = [];

// Load the rotue information
$pathNum = calcRouteNum($origin, $dest);
$pathInfo = loadPath($routeFile, $pathNum);
echo 'Path info for path '.$pathNum.' which is from '.$origin.' to '.$dest.'<p>';
$modeChanges = routeLegs($pathInfo);

$legInfo = []; // leg, company, time, cost, vehicle, capacity
for ($i=0; $i<sizeof($modeChanges)/2; $i++) {
	//echo 'One mode from '.$modeChanges[$i].' to '.$modeChanges[$i+1].'<br>';

	//get the information for each leg
	$pathRoute = calcRouteNum($modeChanges[$i*2], $modeChanges[$i*2+1]);
	fseek($routeFile, $pathRoute*12);
	$pathHead = unpack('i*', fread($routeFile, 12));
	print_r($pathHead);
	echo 'Mode '.$modeChanges[$i].' to '.$modeChanges[$i+1].' is route '.$pathRoute.'<br>';
	//print_r($pathHead);

	// Insert spot for default option for this leg
	array_push($legInfo, 0, $i,0,0,0,$pathHead[3],1,1,1,0,0,0,0); // optionID, legNum, routeID, owner, mode, distance, speed, cost/vol, cost/wt, cap-vol, cap-wt, status, vehicle
	print_r($legInfo);
	$legInfo = array_merge($legInfo, loadRouteOptions($pathRoute, $transportFile));
}

fclose($offerDatFile);
fclose($objFile);
fclose($routeFile);
fclose($transportFile);

echo implode(',', $legInfo);

?>
