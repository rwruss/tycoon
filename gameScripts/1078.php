<?php

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load the player
$thisPlayer = loadObject($pGameID, $objFile, 400);

// Load the invoice List
$invoiceList = new itemSlot($thisPlayer->get('openInvoices'), $slotFile, 40);

$invStr = '';
// Output the invoices
for ($i=0; $i<sizeof($invoiceList->slotData); $i++) {
	if ($invoiceList->slotData[$i] > 0) {
		fseek($contractFile, $invoiceList->slotData[$i]);
		$invDat = fread($contractFile, 140);
		$invStr .= $invDat;
	}
}

echo $invStr;

?>