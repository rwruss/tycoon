<?php

/* 
Find options for purchasing resources for a factory
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisFactory = loadObject($postVals[1], $objFile, 400);

// confirm that the selected item is a valid item for this factory
$optionCheck = false;
$productID = $postVals[3];
for ($i=0; $i<20; $i++) {
	echo $thisFactory->templateDat[16+$i].' vs '.$productID.'<br>';
	if ($thisFactory->templateDat[16+$i] == $productID) {

		$optionCheck = true;
		break;
	}
}

if (!$optionCheck) exit('error 9001-1');
// confirm that there are order spots available
$spotCheck = false;
//$orderItems = $thisFactory->materialOrders();
for ($i=0; $i<10; $i++) {
	if ($orderItems[$i] == 0) {
		$spotCheck = $i;
		break;
	}
	/*
	if ($orderItems[$i*3] == 0) {
		$spotCheck = $i;
		break;
	}*/
}

if ($spotCheck === false) exit('error 9001-2');

if ($optionCheck && $spotCheck !== false) {
	// Load the list of offers for the product
	$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
	$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb');

	// Search for new items to produce
	echo 'Read offer slot '.$productID;
	$offerList = new itemSlot($productID, $offerListFile, 1000);
	$emptyOffers = new itemSlot(0, $offerListFile, 1000);

	$offerCount = 0;
	$checkCount = 1;
	$numOffers = sizeof($offerList->slotData);
	$showOffers = [0, 100, 299, 0, 50, 50, 50, 0, 8, 9, 10, $productID];
	while ($checkCount < $numOffers && $offerCount < 100) {
		echo 'Read item #'.$checkCount.' ('.$offerList->slotData[$checkCount].')';
		if ($offerList->slotData[$checkCount] > 0) {
			fseek($offerDatFile, $offerList->slotData[$checkCount]);
			$tmpDat = unpack('i*', fread($offerDatFile, 52));
			if ($tmpDat[1] > 0) {
				array_push($showOffers, $offerList->slotData[$checkCount]);
				$showOffers = array_merge($showOffers, $tmpDat);
				$offerCount++;
			} else {
				// Delete the reference in the list of offers
				$offerList->deleteItem($checkCount, $offerListFile);

				// Add to list of empty offers
				$emptyOffers->addItem($offerList->slotData[$checkCount]);
			}
		}
		$checkCount++;
	}

	echo '<script>receiveOffers(['.implode(',', $showOffers).'])</script>';
}

fclose($objFile);

?>
