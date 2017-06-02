<?php

/*
PVS 
1 - shipment number
*/

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');

// Load the shipment information
fseek($contractFile, $postVals[1]);
$invoiceDat = fread($contractFile, 140);

$invoiceInfo = unpack('i*', substr($invoiceDat, 0, 80));
$taxInfo = unpack('s*', substr($invoiceDat, 80));

// Load the transport providers for the source city

// Load the transport providers for the destination city

// Determine if any providers serve both citys

// Output options for providers taht serve both cities

fclose($objFile);
fclose($cityFile);
fclose($contractFile);

?>