<?php

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

// Load the player
$thisPlayer = loadObject($pGameID, $objFile, 400);

// Load the invoice List
if ($thisPlayer->get('openInvoices') == 0) {
	exit('');
}
$invoiceList = new itemSlot($thisPlayer->get('openInvoices'), $slotFile, 40);
print_r($invoiceList->slotData);
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

fclose($contractFile);
fclose($objFile);
fclose($slotFile);

?>
