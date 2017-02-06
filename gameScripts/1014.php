<?php

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

$saleDat = pack('i*', $postVals[4], intval($postVals[5]*100), $postVals[1], 100, 100, 100, time(), 0, 0, 0);
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

fclose($objFile);
fclose($offerFile);

echo '<script>
productStores = ['.implode(',', $thisObj->tempList).','.implode(',', $thisObj->productStores).']
showOutputs(productInvSection, productStores);
</script>';

?>
