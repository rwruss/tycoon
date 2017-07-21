<?php

// get the tax rates for a shipment at a city
/* PVS:
1 = invoice/shipment ID
*/

require_once('./objectClass.php');
require_once('./taxCalcs.php');
require_once('./invoiceFunctions.php');
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the invoice information
fseek($contractFile, $postVals[1]);
$invoiceDat = fread($contractFile, 140);
$invoiceInfo = unpack('i*', substr($invoiceDat, 0, 80));

// load the sending factory
$sellingFactory = loadObject($invoiceInfo[17], $objFile, 1000);

// load buying city, selling city, and selling player
$buyingCity = loadCityDemands($invoiceInfo[18], $cityFile, 1000);
$sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile, 1000);
$sellingPlayer = loadObject($pGameID, $objFile, 400);

// calculate the taxes
$transInfo = array_fill(0, 20, 0);
$transInfo[1] = $invoiceInfo[3];
$transInfo[2] = 0;
$transInfo[3] = $invoiceInfo[17];
$transInfo[5] = $invoiceInfo[6];
$transInfo[6] = $invoiceInfo[7];
$transInfo[7] = $invoiceInfo[2];
$transInfo[14] = $invoiceInfo[15];
$transInfo[15] = $invoiceInfo[16];

$useTaxRates = taxRates($transInfo, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile);

fclose($contractFile);
fclose($objFile);
fclose($slotFile);
fclose($cityFile);

echo implode(',', $useTaxRates);

?>
