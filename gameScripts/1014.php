<?php

/*
PVS
1: Selling Factory
2:
3: Product ID to Sell
4: Qunatity of proudct to sell
5: Unit price for sale
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

$sellingFactory = loadObject($postVals[1], $objFile, 1000);
$thisBusiness = loadObject($pGameID, $objFile, 400);

// Process a sale offer for a factory
echo 'Factory: '.$postVals[1].' -> Sell '.$postVals[4].' of product '.$postVals[3].' at a unit price of '.$postVals[5];

// Remove the qunatity of items from the factory inventory
/// Locate product inventory numer in factory
$productCheck = true;
for ($i=1; $i<6; $i++) {
	if ($sellingFactory->tempList['prod'.$i] == $postVals[3]) {
		$inventorySlot = $i;
		$productCheck = false;
		break;
	}
}

/// Check that there is enough inventory
if ($productCheck) {
	echo 'This factory cannot sell that product';
	exit();
}

$invCheck = true;
if ($sellingFactory->get('prodInv'.$inventorySlot) >= $postVals[4]) {
	//$newQty = $sellingFactory->get('prodInv'.$inventorySlot) - $postVals[4];
	$newQty = $sellingFactory->objDat[$sellingFactory->prodInv+$inventorySlot-1] - $postVals[4];
	echo 'Set new inventory to '.$newQty;
	$sellingFactory->save('prodInv'.$inventorySlot, $newQty);
	$invCheck = false;
}

// Add the sale and details to the sale slot file
if ($invCheck) {
	echo 'You don\'t have enough inventory for that sale';
	exit();
}

// confirm that the factory has open sale slots
$facSlot = 0;
for ($i=1; $i<9; $i++) {
	if ($sellingFactory->get('offer'.$i) == 0) {
		$facSlot = $i;
		break;
	}
}

if ($facSlot == 0) exit ('Can\'t create any more offers for this factory');

// [0, company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID, city ID, region ID, nation ID]
$taxInfo = [0, $sellingFactory->get('owner'), $sellingFactory->get('subType'), $sellingFactory->get('industry'), $postVals[1],
	$_SESSION['game_'.$gameID]['teamID'] = 1, $sellingFactory->get('region_3'), $sellingFactory->get('region_2'), $sellingFactory->get('region_1')];

// Calculte sales tax rate for the selling city/region/nation
$taxes = array_fill(0,31,0);
$sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile);

$cityTaxEx = new itemSlot($sellingCity->get('cTax'), $slotFile, 40);
$regionTaxEx = new itemSlot($sellingCity->get('rTax'), $slotFile, 40);
$nationTaxEx = new itemSlot($sellingCity->get('nTax'), $slotFile, 40);

// override taxes for testing
$cityTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10,1,1,460,-10];
$regionTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];
$nationTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];

for ($i=1; $i<11; $i++) {
		$taxes[$i] = $cityTaxEx->slotData[$i];
		$taxes[$i+10] = $regionTaxEx->slotData[$i];
		$taxes[$i+20] = $nationTaxEx->slotData[$i];
	}

calcTaxes($cityTaxEx->slotData, $taxInfo, $taxes);
calcTaxes($regionTaxEx->slotData, $taxInfo, $taxes);
calcTaxes($nationTaxEx->slotData, $taxInfo, $taxes);

$salesTax = $taxes[7]+$taxes[17]+$taxes[27];

// Calculate material cost for the sale and deduct from inventory material cost
$productNum = $inventorySlot-1;
$materialCost = $postVals[4]*$sellingFactory->objDat[$sellingFactory->productStats+$productNum*5+3]/$sellingFactory->objDat[$sellingFactory->prodInv + $productNum]; // Num Selling * Material Cost/Total Inventory
$sellingFactory->saveItem($sellingFactory->productStats+$productNum*5+3, $sellingFactory->objDat[$sellingFactory->productStats+$productNum*5+3] - $materialCost);

// Calculate labor cost for the sale and deduct from inventory material cost
$laborCost = $postVals[4]*$sellingFactory->objDat[$sellingFactory->productStats+$productNum*5+4]/$sellingFactory->objDat[$sellingFactory->prodInv + $productNum]; // Num Selling * Labor Cost/Total Inventory
$sellingFactory->saveItem($sellingFactory->productStats+$productNum*5+4, $sellingFactory->objDat[$sellingFactory->productStats+$productNum*5+4] - $laborCost);

echo 'Sales tax is '.$salesTax;

$thisProduct = loadProduct($, $objFile, 1000);

// create sale dat
$location = 0;
$conglID = 0;
// Quantity, Price, Selling Factory ID, Quality, Pollution, Rights, Time, Location, sellerID, Selling Conglomerate ID, Item ID, Buying Player, Delivery Time, Material Cost, Labor Cost, Sales Tax Rate
$saleDat = array_fill(1, 25, 0);
$saleDat[1] = $postVals[4]; // Qunatity
$saleDat[2] = intval($postVals[5]*100); // Price
$saleDat[3] = $postVals[1]; // Selling factory ID
$saleDat[4] = 100; // Quality
$saleDat[5] = 100; // Pollution
$saleDat[6] = 100; // Rights
$saleDat[7] = time(); // Time
$saleDat[8] = $location; // location
$saleDat[9] = $pGameID; // seller ID
$saleDat[10] = $conglID; // selling conglomerate ID
$saleDat[11] = $postVals[3]; // item ID
$saleDat[12] = 0; // buying player
$saleDat[13] = 0; // delivery time
$saleDat[14] = $materialCost;
$saleDat[15] = $laborCost;
$saleDat[16] = $salesTax;
$saleDat[17] = $sellingFactory->get('region_3'); // origin of sale
$saleDat[18] = 0; // destination of sale
$saleDat[19] = $thisProduct->get('unitWeight')*$postVals[4]; // Product weight
$saleDat[20] = $thisProduct->get('unitVolume')*$postVals[4]; // Product Volume


//$saleDat = pack('i*', $postVals[4], intval($postVals[5]*100), $postVals[1], 100, 100, 100, time(), $location, $pGameID, $conglID, $postVals[3], 0, 0, $materialCost, $laborCost, $salesTax);

// Look for space in the offerDatFile
$checkGroup = 0;
if (flock($offerDatFile, LOCK_EX)) {
	fseek($offerDatFile, 0, SEEK_END);
	$offerSize = ftell($offerDatFile);
	//$offerGroups = floor($offerSize/4000);

	$offerLoc=0;
	$emptyOffers = new itemSlot(0, $offerListFile, 1000, TRUE);
	for ($i=1; $i<sizeof($emptyOffers); $i++) {
		if ($emptyOffers[$i] > 0) {
			$offerLoc = $emptyOffers[$i];
			$emptyOffers->deleteItem($i, $offerListFile);
			break;
		}
	}
	if ($offerLoc == 0) $offerLoc = max(100,ceil($offerSize/100)*100);

	fseek($offerDatFile, $offerLoc);
	fwrite($offerDatFile, packArray($saleDat));
	flock($offerDatFile, LOCK_UN);
}

if (flock($offerListFile, LOCK_EX)) {
	// Record the sale in the list for the product
	echo 'Add to product slot at '.$postVals[3];
	$saleKey = pack('i*', $offerLoc);
	$prodSlot = new itemSlot($postVals[3], $offerListFile, 1000);
	$prodSlot->addItem($offerLoc, $offerListFile);

	// Record the sale in the list for the player
	echo 'Add to player slot at '.$thisBusiness->get('openOffers');
	$playerSlot = new itemSlot($thisBusiness->get('openOffers'), $offerListFile, 1000);
	$playerSlot->addItem($offerLoc, $offerListFile);

	// Record the sale in the list for the conglomerate
	if ($thisBusiness->get('teamID') > 0) {
		$thisCong = loadObject($thisBusiness->get('teamID'), $objFile, 1000);
		$congSlot = new itemSlot($thisCong->get('openOffers'), $offerListFile, 1000);
		$congSlot->addItem($offerLoc, $offerListFile);
	}

	flock($offerListFile, LOCK_UN);
}

// record the sale dat in the factory
$sellingFactory->save('offer'.$facSlot, $offerLoc);

fclose($objFile);
fclose($offerListFile);
fclose($offerDatFile);
fclose($cityFile);
fclose($slotFile);

function calcTaxes($slotData, $thisInfo, &$taxList) { // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
	for ($i=11; $i<sizeof($slotData); $i+=4) {
    echo $thisInfo[$slotData[$i]].' vs '.$slotData[$i+2].' --> ';
		if ($thisInfo[$slotData[$i]] == $slotData[$i+2]) {
      echo 'adjust tax type '.$slotData[$i+1].' by '.$slotData[$i+3];
			$taxList[$slotData[$i+1]] += $slotData[$i+3];
		}
	}
}

?>
