<?php

/*
Process paying an invoice on a contract
PVS
1 = invoice number
*/

require_once('./objectClass.php');
require_once('./slotFunctions.php');

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

// Verify that the player is the payer on the contract
if ($contractInfo[1] != $pGameID) exit('error 7701-1');

// verrify that the player has enough money
$buyingPlayer = loadObject($contractInfo[1], $objFile, 400);
if ($buyingPlayer->get('money') < $invoiceInfo[13]) exit('you need more money');

// record taxes
//$transDat = [sent quantity, unit price, selling factory ID, sent pollution, sent rights, material cost, labor cost]
$transDat = array_fill(1, 20, 0);
$transDat[0] = $invoiceInfo[3]; // qunatity
$transDat[5] = $invoiceInfo[6]; // Pollution
$transDat[6] = $invoiceInfo[7]; // Rights
$transDat[14] = $invoiceInfo[15]; // Material Cost
$transDat[15] = $invoiceInfo[16]; // Labor Cost

//$taxRates = array_values($taxInfo);
$taxAmounts = taxCost($taxInfo, $transDat);

// Record the taxes paid
$sellFactory = loadObject($invoiceInfo[17], $objFile, 1600);
for ($i=1; $i<7; $i++) {
	$sellFactory->objDat[$sellFactory->paidTaxOffset + $i] += $taxAmounts[$i];
	$sellFactory->objDat[$sellFactory->paidTaxOffset + $i+10] += $taxAmounts[$i+10];
	$sellFactory->objDat[$sellFactory->paidTaxOffset + $i+20] += $taxAmounts[$i+20];
}

// Save taxes due to the selling region/city
$sllerTaxDat = '';
$sellingRegion = $sellFactory->get('region_2');
$sellingNation = $sellFactory->get('region_1');

// Add the tax to city/region/nation treasuries
$taxIncomeFile = fopen($gamePath.'/taxReceipts.txf', 'ab');
for ($i=1; $i<10; $i++) {
	$sllerTaxDat .= pack('s*', $sellFactory->get('region_3'), $i, $taxAmounts[$i]);
	$sllerTaxDat .= pack('s*', $sellingRegion, $i, $taxAmounts[$i+10]);
	$sllerTaxDat .= pack('s*', $sellingNation, $i, $taxAmounts[$i+20]);
}

// Add the tax to the buying nation for any import Tax
$buyingFactory = loadObject($contractInfo[11], $objFile, 1600);
$buyerTaxDat = '';
$buyingNation = $buyingFactory->get('region_1');
$buyerTaxDat .= pack('s*', $buyingNation, 9, $taxAmounts[29]);

echo 'Record tax transactions';
if (flock($taxIncomeFile, LOCK_EX)) {
	fwrite($taxIncomeFile, $sllerTaxDat.$buyerTaxDat);
	flock($taxIncomeFile, LOCK_UN);
}
fclose($taxIncomeFile);

$totalTax = array_sum($taxAmounts);
$sellerTax = $totalTax - $taxAmounts[7]-$taxAmounts[17]-$taxAmounts[27]-$taxAmounts[29];
$buyerTax = $taxAmounts[7]+$taxAmounts[17]+$taxAmounts[27]+$taxAmounts[29];
$buyerCost = $transaction[1] * $transaction[2] + $buyerTax;

// add the money to the selling player
$baseCost = $transaction[1] * $transaction[2];
$sellFactory->set('totalSales', $sellFactory->get('totalSales')+$baseCost-$sellerTax);
$sellFactory->set('periodSales', $sellFactory->get('periodSales')+$baseCost-$sellerTax);
$sellFactory->saveAll();

// add money to the seller
$sellingPlayer = loadObject($pGameID, $objFile, 400);
$sellingPlayer->save('money', $sellingPlayer->get('money')+$baseCost-$sellerTax);

// deduct the money from the buying player
$buyingPlayer->save('money', $buyingPlayer->get('money')-$invoiceInfo[13]);

// update the invoice status to paid
fseek($contractFile, $postVals[1]);
fwrite($contractFile, pack('i', 2));

// Delete the invoice from the buying players invoice List
$invoiceSlot = new itemSlot($buyingPlayer->get('openInvoices'), $slotFile, 40);
$invoiceSlot->deleteByValue($invoiceID, $slotFile);

fclose($contractFile);
fclose($objFile);

?>
