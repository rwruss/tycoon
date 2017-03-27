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
print_R($contractInfo);
if ($contractInfo[21] != $pGameID) exit('error 2701-3');

// Load the factory and get product ID
$thisFactory = loadObject($postVals[1], $objFile, 1600);
$productID = $thisFactory->tempList['prod'.($i+1)];

//confirm that the products and quantities match
if ($contractInfo[3] != $productID) exit('error 2701-1');

//confirm that the quantity is available for transfer
$outstandingQty = $contractInfo[4] - $contractInfo[17];
if ($outstandingQty < 1) exit('error 2701-2');
$sentQty = min($outstandingQty, $postVals[4]);

// check for enough available inventory
if ($thisFactory->objDat[$thisFactory->prodInv+$postVals[2]] < $sentQty) exit('error 2701-4');

$sentQual = round($sentQty*$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+0]/$thisFactory->objDat[$thisFactory->prodInv+$postVals[2]]);
$sentPol = round($sentQty*$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+1]/$thisFactory->objDat[$thisFactory->prodInv+$postVals[2]]);
$sentRights = round($sentQty*$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+2]/$thisFactory->objDat[$thisFactory->prodInv+$postVals[2]]);
$materialCost = round($sentQty*$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+3]/$thisFactory->objDat[$thisFactory->prodInv+$postVals[2]]);
$laborCost = round($sentQty*$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+4]/$thisFactory->objDat[$thisFactory->prodInv+$postVals[2]]);

// Adjust the quantities and stats in the contract
$contractInfo[17] += $sentQty;
$contractInfo[18] += $sentQual;
$contractInfo[19] += $sentPol;
$contractInfo[20] += $sentRights;

// Adjust the quantities and stats in the factory
$thisFactory->prodInv+$postVals[2] -= $sentQty;
$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+0] -= $sentQual;
$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+1] -= $sentPol;
$thisFactory->objDat[$thisFactory->productStats + $postVals[2]*5+2] -= $sentRights;

// Check if contract is complete and close if it is
if ($contractInfo[17] == $contractInfo[4]) $contractInfo[8] = 2;

// Load items for tax calcs
$sellingFactory = loadObject($contractInfo[12], $objFile, 1600);
$sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile);
$buyingCity = loadCity($buyingFactory->get('region_3'), $cityFile);
$sellingPlayer = loadObject($sellingFactory->get('owner'), $objFile, 400);

// Apply taxes and adjust money
$taxAmounts = taxAmounts ($materialCost, $laborCost, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile); //($materialCost, $laborCost, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile) {

// save items
$thisFactory->saveAll();
$contractDat = '';
for ($i=1; $i<26; $i++) {
	$contractDat .= pack('i*', $contractInfo[$i]);
}
fseek($contractFile, $postVals[3]);
fwrite($contractFile, $contractDat);

fclose($contractFile);
fclose($objFile);

?>
