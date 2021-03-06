<?php

// show a list of vehicles available for sale

/*
PVS
1: Vehicle type
*/

require_once('./slotFunctions.php');
$offerListFile = fopen($gamePath.'/saleOffers.slt', 'rb'); //r+b
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb');

$vehicleTypes = [41];

$emptyOffers = new itemSlot(0, $offerListFile, 1000, TRUE);
for ($i=0; $i<$z = sizeof($vehicleTypes); $i++ ) {
	// Load the list of offers for the product
	$productID = $vehicleTypes[$i];
	//echo 'Read offer slot '.$productID;
	$offerList = new itemSlot($productID, $offerListFile, 1000);
	//print_R($offerList->slotData);

	$offerCount = 0;
	$checkCount = 1;
	$numOffers = sizeof($offerList->slotData);
	//$showOffers = [0, 100, 299, 0, 50, 50, 50, 0, 8, 9, 10, $productID];
	$showOffers = [];

	while ($checkCount < $numOffers && $offerCount < 100) {
		//echo 'Read item #'.$checkCount.' ('.$offerList->slotData[$checkCount].')';
		if ($offerList->slotData[$checkCount] > 0) {
			//echo 'Load offer #'.$offerList->slotData[$checkCount].' at spot '.$checkCount;
			//fseek($offerDatFile, $offerList->slotData[$checkCount]);
			//$offerDat = unpack('i*', fread($offerDatFile, 64));
			$thisOffer = loadOffer($offerList->slotData[$checkCount], $offerDatFile);
			$offerDat = $thisOffer->objDat;
			if ($thisOffer->objDat[1] > 0) {
				array_push($showOffers, $offerList->slotData[$checkCount]);
				$showOffers = array_merge($showOffers, $thisOffer->objDat);
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
}
fclose($offerListFile);
fclose($offerDatFile);

$vinfo = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17];

//echo implode(',', $vinfo);
echo implode(',', $showOffers);

?>
