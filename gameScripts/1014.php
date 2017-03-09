<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');

$thisObj = loadObject($postVals[1], $objFile, 1000);
$thisBusiness = loadObject($pGameID, $objFile, 400);

// Process a sale offer for a factory
echo 'Factory: '.$postVals[1].' -> Sell '.$postVals[4].' of product '.$postVals[3].' at a unit price of '.$postVals[5];

// Remove the qunatity of items from the factory inventory
/// Locate product inventory numer in factory
$productCheck = true;
for ($i=1; $i<6; $i++) {
	if ($thisObj->tempList['prod'.$i] == $postVals[3]) {
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
if ($thisObj->get('prodInv'.$inventorySlot) >= $postVals[4]) {
	$newQty = $thisObj->get('prodInv'.$inventorySlot) - $postVals[4];
	echo 'Set new inventory to '.$newQty;
	$thisObj->save('prodInv'.$inventorySlot, $newQty);
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
	if ($thisObj->get('offer'.$i) == 0) {
		$facSlot = $i;
		break;
	}
}

if ($facSlot == 0) exit ('Can\'t create any more offers for this factory');

// create sale dat
$location = 0;
$conglID = 0;
$saleDat = pack('i*', $postVals[4], intval($postVals[5]*100), $postVals[1], 100, 100, 100, time(), $location, $pGameID, $conglID, $postVals[3]);

// Look for space in the offerDatFile
$checkGroup = 0;
if (flock($offerDatFile, LOCK_EX)) {
	fseek($offerDatFile, 0, SEEK_END);
	$offerSize = ftell($offerDatFile);
	//$offerGroups = floor($offerSize/4000);

	$offerLoc=0;
	$emptyOffers = new itemSlot(0, $offerListFile, 1004);
	for ($i=1; $i<sizeof($emptyOffers); $i++) {
		if ($emptyOffers[$i] > 0) {
			$offerLoc = $emptyOffers[$i];
			$emptyOffers->deleteItem($i, $offerListFile);
			break;
		}
	}
	if ($offerLoc == 0) $offerLoc = max(64,ceil($offerSize/64)*64);

	fseek($offerDatFile, $offerLoc);
	fwrite($offerDatFile, $saleDat);
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
$thisObj->save('offer'.$facSlot, $offerLoc);

fclose($objFile);
fclose($offerListFile);
fclose($offerDatFile);
?>
