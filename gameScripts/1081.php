<?php

$numCities = 10;

/*
PVS
1 - shipment number
*/

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$routeFile = fopen($gamePath.'/routes.rtf', 'rb');

// Load the shipment information
fseek($contractFile, $postVals[1]);
$invoiceDat = fread($contractFile, 140);

$invoiceInfo = unpack('i*', substr($invoiceDat, 0, 80));

// Load the route List for the two cities
// Sn = n(a1+an)/2
$loCity = min($invoiceInfo[18], $invoiceInfo[19]);
$hiCity = max($invoiceInfo[18], $invoiceInfo[19]);

$loCity = 5;
$hiCity = 7;

$nthVal = $numCities - $loCity; // 6
$routeNum = $loCity * (($numCities-1) + $nthVal)/2 + ($hiCity-$loCity);

echo 'Look up route '.$routeNum.' for cities '.$loCity. ' and '.$hiCity;

fseek($routeFile, $routeNum*40);
$routeHead = unpack('i*', fread($routeFile, 40));

// Load the transport providers for the destination city
$routeList = [];
$nextRoute = $routeHead[6];
while ($nextRoute > 0) {
  fseek($routeFile, $nextRoute);
  $routeInfo = unpack('i*', fread($routeFile, 40));

  $nextRoute = $routeInfo[6];
}

// Output route options
echo implode(',', $routeList);

fclose($objFile);
fclose($cityFile);
fclose($contractFile);

?>
