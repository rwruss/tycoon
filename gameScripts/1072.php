<?php

/*
Process sending goods from a factory for a contract
PVS
1 - factory ID
2 - product index
3 - contract ID
4 - product QTY
*/

require_once('./slotFunctions.php');
require_once('./taxCalcs.php');
require_once('./objectClass.php');

//$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the contract and confirm the sending player is this player
fseek($contractFile, $postVals[3]);
$contractInfo = unpack('i*', fread($contractFile, 100));
if ($contractInfo[21] != $pGameID) exit('error 2701-3'); // player is not the sender for this contract

// Load the factory and get product ID
$sellFactory = loadObject($postVals[1], $objFile, 1600);
$productID = $sellFactory->tempList['prod'.($i+1)];

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

// Adjust the quantities and stats in the factory
$sellFactory->prodInv+$postVals[2] -= $sentQty;
$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+0] -= $sentQual;
$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+1] -= $sentPol;
$sellFactory->objDat[$sellFactory->productStats + $postVals[2]*5+2] -= $sentRights;

// Check if contract is complete and close if it is
if ($contractInfo[17] == $contractInfo[4]) $contractInfo[8] = 2;

// Load items for tax calcs
$sellingFactory = loadObject($contractInfo[12], $objFile, 1600);
$sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile);
$buyingCity = loadCity($buyingFactory->get('region_3'), $cityFile);
$sellingPlayer = loadObject($sellingFactory->get('owner'), $objFile, 400);

$transaction = array_fill(0, 25, 0);
$transaction[1] = $sentQty;
$transaction[2] = $contractInfo[16];
$transaction[3] = $postVals[1]; // selling factory ID
$transaction[5] = $sentPol;
$transaction[6] = $sentRights;
$transaction[14] = $materialCost;
$transaction[15] = $laborCost;

// Apply taxes and adjust money
$taxAmounts = taxAmounts ($transaction, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile); //($materialCost, $laborCost, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile) {

$totalTax = array_sum($taxAmounts);	
$sellerTax = $totalTax - $taxAmounts[7]-$taxAmounts[17]-$taxAmounts[27]-$taxAmounts[29];
$buyerTax = $taxAmounts[7]+$taxAmounts[17]+$taxAmounts[27]+$taxAmounts[29];
	
// if autopay is on adjust money.  If not, create an invoice
$invoice = false;
if ($contractInfo[23] == 1) {
	// autopay on -> transfer money
	
	// verify buyer has enough money
	$buyingPlayer = loadObject($sellFactory->get('owner'), $objFile, 400);
	if ($buyingPlayer->get('money') < $transaction[1] * $transaction[2]) {
		
		// transfer the money 		
		$buyingPlayer->set('money', $buyingPlayer->get('money')-$buyerCost);
		
		// Record the taxes paid
		for ($i=1; $i<7; $i++) {
			$sellingFactory->objDat[$sellingFactory->paidTaxOffset + $i] += $taxAmounts[$i];
			$sellingFactory->objDat[$sellingFactory->paidTaxOffset + $i+10] += $taxAmounts[$i+10];
			$sellingFactory->objDat[$sellingFactory->paidTaxOffset + $i+20] += $taxAmounts[$i+20];
		}
		
		// Save taxes due to the selling region/city
		$sllerTaxDat = '';
		$sellingRegion = $sellingFactory->get('region_2');
		$sellingNation = $sellingFactory->get('region_1');
		
		// Add the tax to city/region/nation treasuries
		$taxIncomeFile = fopen($gamePath.'/taxReceipts.txf', 'ab');
		for ($i=1; $i<10; $i++) {
			$sllerTaxDat .= pack('s*', $sellingFactory->get('region_3'), $i, $taxAmounts[$i]);
			$sllerTaxDat .= pack('s*', $sellingRegion, $i, $taxAmounts[$i+10]);
			$sllerTaxDat .= pack('s*', $sellingNation, $i, $taxAmounts[$i+20]);
		}

		// Add the tax to the buying nation for any import Tax
		$buyerTaxDat = '';
		$buyingNation = $buyingFactory->get('region_1');
		$buyerTaxDat .= pack('s*', $buyingNation, 9, $taxes[29]);

		if (flock($taxIncomeFile, LOCK_EX)) {
			fwrite($taxIncomeFile, $sllerTaxDat);
			flock($taxIncomeFile, LOCK_UN);
		}
		fclose($taxIncomeFile);
		
		// add the money to the selling player
		$sellingFactory->set('totalSales', $sellingFactory->get('totalSales')+$baseCost-$sellerTax);
		$sellingFactory->set('periodSales', $sellingFactory->get('periodSales')+$baseCost-$sellerTax);

		$sellingPlayer->set('money', $sellingPlayer->get('money')+$baseCost-$sellerTax);
		
	} else $invoice = true;
}

if ($contractInfo[23] == 0) $invoice = true;	// autopay off -> create invoice

if ($invoice) {
	// get a new invoice number
	if (flock($contractFile, LOCK_EX) {
		fseek($contractFile, 0, SEEK_END);
		$size = ftell($contractFile);
		
		// autopay off or not enough money-> create invoice
		$invoiceInfo = array_fill(1, 10, 0);
		$invoiceInfo[1] = 1; // status: unpaid
		$invoiceInfo[2] = $contractInfo[22]; // invoice link
		$invoiceInfo[3] = $sentQty[4];
		$invoiceInfo[4] = $postVals[3];
		$invoiceInfo[5] = $sentQual;
		$invoiceInfo[6] = $sentPol;
		$invoiceInfo[7] = $sentRights;
		$invoiceInfo[8] = time();
		
		$invoiceDat = '';
		for ($i=1; $i<11; $i++) {
			$invoiceDat .= pack('i', $invoiceInfo[$i]);
		}
		for ($i=1; $i<30; $i++) {
			$invoiceDat .= pack('s', $taxAmounts[$i]);
		}
		
		fseek($contractFile, $size);
		fwrite($contractFile, $invoiceDat);
		flock($contractFile, LOCK_UN);
		
		// record new invoice pointer in the contract
		fseek($contractFile, $postVals[3] + 84);
		fwrite($contractFile, $size);		
	}	
}

// save items
$sellFactory->saveAll();
$contractDat = '';
for ($i=1; $i<26; $i++) {
	$contractDat .= pack('i*', $contractInfo[$i]);
}
fseek($contractFile, $postVals[3]);
fwrite($contractFile, $contractDat);

fclose($contractFile);
fclose($objFile);

?>
