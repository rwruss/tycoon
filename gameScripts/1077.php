<?php

/*
Process paying an invoice on a contract
PVS
1 = invoice number
*/

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load the invoice
fseek($contractFile, $postVals[1]);
$invoiceInfo = unpack('i*', fread($contractFile, 116));

// Load the contract
fseek($contractFile, $invoiceDat[14]);
$contractDat = fread($contractFile, 100);

// Verify that the player is the payer on the invoice

// verrify that the player has enough money

// deduct the money from the player

// add money to the seller

// record taxes

?>