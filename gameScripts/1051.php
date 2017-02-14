<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');

// Load the information for the sale
fseek($offerDatFile, $postVals[1]);
$offerDat = unpack('i*', fread($offerDatFile, 44));

if ($offerDat[9] != $pGameID) exit('not authorized');

// Credit the goods back to the factory
$thisFactory = loadObject($offerDat[3], $objFile, 400);
$thisBusiness = loadObject($pGameID, $objFile, 400);

// Process a sale offer for a factory
echo 'Factory: '.$offerDat[3].' -> Refund '.$offerDat[1].' of product '.$offerDat[11].' at a unit price of '.$offerDat[2];

// Remove the qunatity of items from the factory inventory
/// Locate product inventory numer in factory
$productCheck = true;
for ($i=1; $i<6; $i++) {
	if ($thisFactory->tempList['prod'.$i] == $offerDat[11]) {
		$inventorySlot = $i;
		$productCheck = false;
		break;
	}
}

$newQty = $thisFactory->get('prodInv'.$inventorySlot) + $offerDat[1];
echo 'Set new inventory to '.$newQty;
$thisFactory->save('prodInv'.$inventorySlot, $newQty);

// Remove the sales listing
fseek($offerDatFile, $postVals[1]);
fwrite($offerDatFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));

// remove the slot listing for the product
$prodSlot = new itemSlot($offerDat[11], $offerListFile, 1000);
$prodSlot->deleteByValue($postVals[1], $offerListFile);

// remove the slot listing for the player
echo 'Remove to player slot at '.$thisBusiness->get('openOffers');
$playerSlot = new itemSlot($thisBusiness->get('openOffers'), $offerListFile, 1000);
$playerSlot->deleteByValue($postVals[1], $offerListFile);

if ($thisBusiness->get('teamID') > 0) {
	$thisCong = loadObject($thisBusiness->get('teamID'), $objFile, 1000);
	$congSlot = new itemSlot($thisCong->get('openOffers'), $offerListFile, 1000);
	$congSlot->deleteByValue($postVals[1], $offerListFile);
}


fclose($objFile);
fclose($offerListFile);
fclose($offerDatFile);

?>
