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
$invoiceDat = fread($contractFile, 116);
$invoiceInfo = unpack('i*', substr($invoiceDat, 0, 56));
$taxInfo = unpack('s*', substr($invoiceDat, 56));
echo 'Invoice Info';
print_R($invoiceInfo);

echo 'Tax Info';
print_r($taxInfo);

// Load the contract
fseek($contractFile, $invoiceInfo[14]);
$contractDat = fread($contractFile, 100);
$contractInfo = unpack('i*', $contractDat);
print_r($contractInfo);

// Verify that the player is the payer on the invoice

// verrify that the player has enough money

// deduct the money from the player

// add money to the seller

// record taxes

fclose($contractFile);
fclose($objFile);

?>
