<?php

/*
Process sending goods from a factory for a contract
PVS
1 - factory ID
2 - product index
3 - contract ID
4 - product QTY
*/

/* !!! TURNED OFF UNTIL PROCESSING FOR SHIPMENTS AND OPEN CONTRACTS IS FINALIZED
require_once('./slotFunctions.php');
require_once('./taxCalcs.php');
require_once('./objectClass.php');
require_once('./invoiceFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

// Load the contract and confirm the sending player is this player
fseek($contractFile, $postVals[3]);
$contractInfo = unpack('i*', fread($contractFile, 100));
if ($contractInfo[21] != $pGameID) exit('error 2701-3'); // player is not the sender for this contract

// Load the factory and get product ID
$sellFactory = loadObject($postVals[1], $objFile, 1600);
$productID = $sellFactory->tempList['prod'.($postVals[2]+1)];

//confirm that the products and quantities match
if ($contractInfo[3] != $productID) exit('error 2701-1'); // wrong product for this contract

//confirm that the quantity is available for transfer
$outstandingQty = $contractInfo[4] - $contractInfo[17];
if ($outstandingQty < 1) exit('error 2701-2');  // contract amount has already been sent
$sentQty = min($outstandingQty, $postVals[4]);

// check for enough available inventory
if ($sellFactory->objDat[$sellFactory->prodInv+$postVals[2]] < $sentQty) exit('error 2701-4');  // sender does not have enough inventory at this location

// calculate transaction costs
$sentQual = round($sentQty*$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+0]/$sellFactory->objDat[$sellFactory->prodInv+$postVals[2]]);
$sentPol = round($sentQty*$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+1]/$sellFactory->objDat[$sellFactory->prodInv+$postVals[2]]);
$sentRights = round($sentQty*$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+2]/$sellFactory->objDat[$sellFactory->prodInv+$postVals[2]]);
$materialCost = round($sentQty*$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+3]/$sellFactory->objDat[$sellFactory->prodInv+$postVals[2]]);
$laborCost = round($sentQty*$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+4]/$sellFactory->objDat[$sellFactory->prodInv+$postVals[2]]);

// Adjust the quantities and stats in the contract
$contractInfo[17] += $sentQty;
$contractInfo[18] += $sentQual;
$contractInfo[19] += $sentPol;
$contractInfo[20] += $sentRights;

// Adjust the quantities and stats in the SELLING factory
echo 'Indexes: '.$sellFactory->productStats.' + '.$postVals[2].' *5 +0 = '.($sellFactory->productStats + $postVals[2]*5+0).' - '.$sentQual;
$sellFactory->objDat['prodInv'+$postVals[2]] -= $sentQty;
$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+0] -= $sentQual;
$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+1] -= $sentPol;
$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+2] -= $sentRights;

// Check if contract is complete and close if it is
if ($contractInfo[17] == $contractInfo[4]) $contractInfo[8] = 2;

// Load items for tax calcs
$sellFactory = loadObject($postVals[1], $objFile, 1600);
$buyingFactory = loadObject($contractInfo[12], $objFile, 1600);
$sellingCity = loadCity($sellFactory->get('region_3'), $cityFile);
$buyingCity = loadCity($buyingFactory->get('region_3'), $cityFile);
$sellingPlayer = loadObject($sellFactory->get('owner'), $objFile, 400);

$transaction = array_fill(0, 25, 0);
$transaction[1] = $sentQty;
$transaction[2] = $contractInfo[16]; // accepted price
$transaction[3] = $postVals[1]; // selling factory ID
$transaction[5] = $sentPol; // pollution
$transaction[6] = $sentRights; // rights
$transaction[7] = $contractInfo[3]; // product ID
$transaction[14] = $materialCost;
$transaction[15] = $laborCost;

// Apply taxes and adjust money
$taxRates = taxRates ($transaction, $sellFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile); //($materialCost, $laborCost, $sellFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile) {
$taxAmounts = taxCost($taxRates, $transaction);

$totalTax = array_sum($taxAmounts);
$sellerTax = $totalTax - $taxAmounts[7]-$taxAmounts[17]-$taxAmounts[27]-$taxAmounts[29];
$buyerTax = $taxAmounts[7]+$taxAmounts[17]+$taxAmounts[27]+$taxAmounts[29];
$buyerCost = $transaction[1] * $transaction[2] + $buyerTax;

print_r($taxAmounts);

// if autopay is on adjust money.  If not, create an invoice
$invoice = false;
$buyingPlayer = loadObject($sellFactory->get('owner'), $objFile, 400);
if ($contractInfo[23] == 1 || 1) {
	// autopay on -> transfer money

	// verify buyer has enough money
	echo 'Money check: '.$buyingPlayer->get('money').' vs '.$transaction[1].' * '.$transaction[2].' = '.($transaction[1] * $transaction[2]);
	if ($buyingPlayer->get('money') < $transaction[1] * $transaction[2]) {

		// transfer the money
		echo 'Total buyer cost is '.$buyerCost.' ('.$transaction[1] * $transaction[2].' + '.$buyerTax.')';
		$buyingPlayer->set('money', $buyingPlayer->get('money')-$buyerCost);

		// Record the taxes paid
		for ($i=1; $i<7; $i++) {
			$sellFactory->objDat[$sellFactory->paidTaxOffset + $i] += $taxAmounts[$i];
			$sellFactory->objDat[$sellFactory->paidTaxOffset + $i+10] += $taxAmounts[$i+10];
			$sellFactory->objDat[$sellFactory->paidTaxOffset + $i+20] += $taxAmounts[$i+20];
		}

		// Save taxes due to the selling region/city
		// Add taxes to buying and selling region
		$buyTaxAreas = [0,0,$buyingFactory->get('region_1')];
		$sellTaxAreas = [$thisFactory->get('region_3'), $thisFactory->get('region_2'), $thisFactory->get('region_1')];
		saveRegionTaxes($sellTaxAreas, $buyTaxAreas, $taxAmounts);

		// add the money to the selling player
		$baseCost = $transaction[1] * $transaction[2];
		$sellFactory->set('totalSales', $sellFactory->get('totalSales')+$baseCost-$sellerTax);
		$sellFactory->set('periodSales', $sellFactory->get('periodSales')+$baseCost-$sellerTax);

		$sellingPlayer->set('money', $sellingPlayer->get('money')+$baseCost-$sellerTax);

	} else $invoice = true;
}

// create invoice
$now = time();
$invoiceInfo = array_fill(1, 20, 0);
$invoiceInfo[1] = 1; // status: unpaid
$invoiceInfo[2] = $contractInfo[3]; // Proudct ID
$invoiceInfo[3] = $sentQty;
$invoiceInfo[4] = $contractInfo[16]; // contract Price
$invoiceInfo[5] = $sentQual;
$invoiceInfo[6] = $sentPol;
$invoiceInfo[7] = $sentRights;
$invoiceInfo[8] = $now;
$invoiceInfo[9] = 0;
$invoiceInfo[11] = $contractInfo[22]; // invoice link
$invoiceInfo[12] = $now + 600; // Delivery time
$invoiceInfo[13] = $buyerCost;
$invoiceInfo[14] = $postVals[3];
$invoiceInfo[15] = $materialCost;
$invoiceInfo[16] = $laborCost;
$invoiceInfo[17] = $postVals[1]; // selling factory ID

// Save the invoice to teh file
$newInvoiceID = writeInvoice($invoiceInfo, $taxRates, $contractFile);

// record new invoice pointer in the contract
echo 'record invoice at ('.($postVals[3] + 84).')';
$contractInfo[22] = $newInvoiceID;

// save items
$sellFactory->saveAll();
$contractDat = '';
for ($i=1; $i<26; $i++) {
	$contractDat .= pack('i*', $contractInfo[$i]);
}
fseek($contractFile, $postVals[3]);
fwrite($contractFile, $contractDat);

fclose($contractFile);

// update the sending factory stats in the browser
echo '<script>updateFactory(['.implode(',', $sellFactory->overViewInfo()).'])</script>';

// record the invoice ID in the buying player's unpaid invoice List
if ($buyingPlayer->get('openInvoices') == 0) {
	$newSlot = newSlot($slotFile, 40);
	$buyingPlayer->save('openInvoices', $newSlot);
}
$invoiceSlot = new itemSlot($buyingPlayer->get('openInvoices'), $slotFile, 40);
$invoiceSlot->addItem($newInvoiceID, $slotFile);

// Record the sale in the GDP of the city, region, and nation
$gdpGain = $sentQty*$contractInfo[16]; // GDP Gain
$sellingCity->save('pDGP', $sellingCity->get('pGDP')+$gdpGain);

$sellingRegion = loadRegion($thisFactory->get('region_2'), $objFile);
$sellingRegion->save('pDGP', $sellingRegion->get('pGDP')+$gdpGain);

$sellingNation = loadRegion($thisFactory->get('region_1'), $objFile);
$sellingNation->save('pDGP', $sellingNation->get('pGDP')+$gdpGain);

fclose($slotFile);
fclose($objFile);
*/
?>
