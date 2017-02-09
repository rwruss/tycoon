<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');

$thisObj = loadObject($postVals[1], $objFile, 400);

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

// create sale dat
$location = 0;
$conglID = 0;
$saleDat = pack('i*', $postVals[4], intval($postVals[5]*100), $postVals[1], 100, 100, 100, time(), $location, $pGameID, $conglID);

// Look for space in the offerDatFile
$checkGroup = 0;
if (flock($offerDatFile, LOCK_EX)) {
	fseek($offerDatFile, 0, SEEK_END);
	$offerSize = ftell($offerDatFile);
	$offerGroups = floor($offerSize/4000);

	$offerLoc=0;
	for ($checkGroup=0; $checkGroup<$offerGroups; $checkGroup++) {
		fseek($offerDatFile, $checkGroup*4000);
		$chkDat = unpack('i*', fread($offerDatFile));
		for ($i=1; $i<1000; $i+=10) {
			if ($chkDat[$i] == 0) {
				$offerLoc = $checkGroup*4000+4*$i-4;
				break 2;
			}
		}
	}
	if ($offerLoc == 0) $offerLoc = ceil($offerSize/40)*40;

	fseek($offerDatFile, $offerLoc);
	fwrite($offerDatFile, $saleDat);
	flock($offerDatFile, LOCK_UN);
}

if (flock($offerFile, LOCK_EX)) {
	// Record the sale in the list for the product
	$saleKey = pack('i*', $offerLoc, $postVals[3]);
	$prodSlot = new blockSlot($postVals[3], $offerFile, 1004);
	$loc = 0;
	for ($i=1; $i<sizeof($prodSlot->slotData); $i+=2) {
		if ($prodSlot->slotData[$i] == 0) {
			$loc = $i;
			break;
		}
	}
	$prodSlot->addItem($offerFile, $saleKey, $loc);

	// Record the sale in the list for the player
	$playerSlot = new blockSlot($thisBusiness->get('openOffers'), $offerFile, 1004);
	$loc = 0;
	for ($i=1; $i<sizeof($playerSlot->slotData); $i+=2) {
		if ($playerSlot->slotData[$i] == 0) {
			$loc = $i;
			break;
		}
	}
	$playerSlot->addItem($offerFile, $saleKey, $loc);

	// Record the sale in the list for the conglomerate
	if ($thisBusiness->get('teamID') > 0) {
		$thisCong = loadObject($thisBusiness->get('teamID'), $objFile, 1000);
		$congSlot = new blockSlot($thisCong->get('openOffers'), $offerFile, 1004);

		$loc = 0;
		for ($i=1; $i<sizeof($congSlot->slotData); $i+=2) {
			if ($congSlot->slotData[$i] == 0) {
				$loc = $i;
				break;
			}
		}
		$congSlot->addItem($offerFile, $saleKey, $loc);
	}

	flock($offerFile, LOCK_UN);
}

fclose($objFile);
fclose($offerFile);
fclose($offerDatFile);

/*
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerFile = fopen($gamePath.'/saleOffers.slt', 'r+b');

$thisObj = loadObject($postVals[1], $objFile, 400);

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

$location = 0;
$conglID = 0;
$saleDat = pack('i*', $postVals[4], intval($postVals[5]*100), $postVals[1], 100, 100, 100, time(), $location, $pGameID, $conglID);
if (flock($offerFile, LOCK_EX)) {
	$saleSlot = new blockSlot($postVals[3], $offerFile, 4004);
	$location = sizeof($saleSlot->slotData);
	for ($i=1; $i<sizeof($saleSlot->slotData); $i+=10) {
		if ($saleSlot->slotData[$i] == 0) {
			$location = $i;
			break;
		}
	}
	$saleSlot->addItem($offerFile, $saleDat, $location);
	flock($offerFile, LOCK_UN);
}

// record offer in player's sales information
$thisBusiness = loadObject($pGameID, $objFile, 400);
$businessSales = new blockSlot($thisBusiness->get('salesList'), $slotFile, 40);

// record in conglomerate's sales information

fclose($objFile);
fclose($offerFile);

echo '<script>
productStores = ['.implode(',', $thisObj->tempList).','.implode(',', $thisObj->productStores).']
showOutputs(productInvSection, productStores);
</script>';
*/
?>
