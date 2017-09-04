<?php

// ADJUST THIS FOR THE NEW LABOR FORMAT - yes
// move workers between a factory and company labor slots

//print_r($postVals);
/*
pvs
1-factory ID
2-factory labor slot
3-new labor item
4-new labor item pay rate
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'r+b');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'r+b');

// Load the business & factory
$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1400);

if (flock($laborPoolFile, LOCK_EX)) {
	if ($postVals[3] > 99) {
		// * move a new labor item into this slot * \\

		// load the new labor
		$newLabor = loadLaborItem($postVals[3], $laborPoolFile);
		echo 'New Labor:';
		print_r($newLabor);

		$oldLabor = $thisFactory->laborItems[$postVals[2]];
		if ($oldLabor->laborDat[3] > 0) {
			// move the existing labor out of the factory and into the business slot file at the location of the old item
			fseek($laborPoolFile, $postVals[3]);
			fwrite($laborPoolFile, $oldLabor->packLabor());
		} else {

			// No existing labor - delete the reference to this labor from the business labor pool and add it to the empty list
			$laborSlot = $thisBusiness->get('laborSlot');
			echo 'Delete this labor spot from slot '.$laborSlot;
			if (flock($slotFile, LOCK_EX)) {
				if ($laborSlot == 0) {
					$laborSlot = newSlot($slotFile);
					$thisBusiness->save('laborSlot', $laborSlot);
					//echo 'Save new labor slot #'.$laborSlot;
				}
				$laborList = new itemSlot($laborSlot, $slotFile, 40);
				$laborList->deleteByValue($oldLabor->laborDat[3]);
				flock($slotFile, LOCK_UN);
			}
		}

		// save the new labor into the factory
		$thisFactory->laborItems[$postVals[2]] = $newLabor;
		//print_r($thisFactory->laborItems[$postVals[2]]);
		$thisFactory->saveLabor();
	}
	else if ($postVals[3] > 0) {
		// adjust the existing labor item in the factory slot
		$oldLabor = $thisFactory->laborItems[$postVals[2]];
	}
	else if ($postVals[3] == 0) {
		// remove an item from the factory with no replacement
		$newLabor = loadLaborItem(0, $laborPoolFile);
		$oldLabor = $thisFactory->laborItems[$postVals[2]];
		if (flock($laborPoolFile, LOCK_EX)) {
			addLaborToPool($oldLabor, $laborPoolFile, $laborSlotFile);
			flock($laborPoolFile, LOCK_UN);
		}

		$thisFactory->laborItems[$postVals[2]] = $newLabor;
		$thisFactory->saveLabor();
	}

	flock($laborPoolFile, LOCK_UN);
}

$productionSpots = $thisFactory->objDat[$thisFactory->productionSpotQty];
for ($i=0; $i<$productionSpots; $i++) {
	$productionRate = $thisFactory->setProdRate($i);
	$thisFactory->objDat[$thisFactory->currentProductionRateOffset+$i] = $productionRate[0];
	$thisFactory->productionQuality[$i+1]] = $productionRate[1];
}

$thisFactory->saveProductionRates();

function addLaborToPool($laborItem, $laborPoolFile, $laborSlotFile) {
	$useSpot = 0;

	// look for an empty spot or create a new one
	$emptySpots = new itemSlot(0, $laborSlotFile, 40, TRUE);
	for ($i=1; $i<$z = sizeof($emptySpots->slotData); $i++) {
		if ($emptySpots->slotData[$i] > 0) {
			$useSpot = $emptySpots[$i];
			$emptySpots->deleteByValue($useSpot);

			fseek($laborPoolFile, $useSpot);
			fwrite($laborPoolFile, $laborItem->packLabor());
		}
	}
	if ($useSpot == 0) {
		fseek($laborPoolFile, 0, SEEK_END);
		$useSpot = ftell($laborPoolFile);
		fwrite($laborPoolFile, $laborItem->packLabor());
	}

	return $useSpot;
}



?>
