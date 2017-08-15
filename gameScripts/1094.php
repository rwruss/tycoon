<?php

/*
Receive transport options for a material order at a factory
*/

/*
PVS
1 - offer ID
2+ route selections
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');
require_once('./taxCalcs.php');
require_once('./invoiceFunctions.php');
require_once('./transportClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$routeFile = fopen($gamePath.'/routes.rtf', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');

// Load the player information
$thisPlayer = ??

// Load the offer information
$thisOffer = loadOffer($postVals[2], $objFile);
if ($thisOffer->get('buyer') != $pGameID) exit ('error 9401-1');

/// Apply the selected routes
$routeObjects = [];
$legCosts = [];
$legTimes = [];
$legOwners = [];
$legCaps = []; // leg capacity -> volume, weight
//$modeChanges = routeLegs($pathInfo);


$routeList = [];
for ($i=2; $i<$z=sizeof($postVals); $i+=2) {
	$routeList[] = $postVals[$i];
}

routeLegDetails($routeList, $routeObjects, $legCosts, $legTimes, $legOwners, $legCaps, $transportFile);
// verify enough capacity
for ($i=0; $i<$z=sizeof($routeList)); $i++) {
	if ($legCaps[$i*2] < $thisOffer->get('volume') || $legCaps[$i*2+1] < $thisOffer->get('weight')) exit ('error 9401-2');
}

$totalCost = array_sum($legCosts);
$totalTime = array_sum($legTimes);

processRouteCosts($thisPlayer, $legCosts, $routeObjects, $legCaps, $objFile);

// update the delivery time of the order at the factory
$now = time();
$thisOffer->save('deliverTime', $now+$totalTime);

fclose($objFile);
fclose($offerDatFile);
fclose($transportFile);
fclose($routeFile);

// need to add factory ID, order ID, order number
echo implode(',', $thisOffer->objDat);

?>