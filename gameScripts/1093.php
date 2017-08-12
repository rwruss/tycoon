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
$origin = $thisOffer->objDat[17];
$dest = $thisOffer->objDat[18];

fclose($offerDatFile);
fclose($objFile);
fclose($routeFile);
fclose($transportFile);

?>