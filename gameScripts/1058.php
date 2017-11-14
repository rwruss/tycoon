<?php

// ADJUST THIS FOR THE NEW LABOR FORMAT - yes
// move workers between a factory and company labor slots

//print_r($postVals);
/*
pvs
1-factory ID
2+ installed labor IDs
*/

print_r($postVals);
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b'); //r+b
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b'); //r+b
$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'r+b'); //r+b
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'r+b'); //r+b

// Load the business & factory
$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1400);

// get business labor slot for adding pool items to (or create it);
$laborSlot = $thisBusiness->get('laborSlot');
if ($laborSlot == 0) {
	$laborSlot = newSlot($slotFile);
	$thisBusiness->save('laborSlot', $laborSlot);
	//echo 'Save new labor slot #'.$laborSlot;
}
$laborList = new itemSlot($laborSlot, $slotFile, 40);

for ($i=0; $i<10; $i++) {
		echo '<P>LABOR ITEM '.$i.'<br>';
		print_r($thisFactory->laborItems[$i]);
}

// determine which labor units stay at the factory and which need to be remove
$laborStatus = array_fill(0,9,-1);
for ($i=0; $i<10; $i++) {
	if ($postVals[2+$i] < 10 && $postVals[2+$i] > 0) $laborStatus[$i] = $postVals[2+$i];
}
print_r($laborStatus);
// remove the unused labor items
for ($i=0; $i<10; $i++) {
	if ($laborStatus[$i] < 0) {
		if ($thisFactory->laborItems[$i]->laborDat[3] == 0) {
			echo '<br>Item '.$i.' is already empty';
		} else {
			echo '<br>Remove item '.$i.' type '.$thisFactory->laborItems[$i]->laborDat[3];
			$oldLaborID = addLaborToPool($thisFactory->laborItems[$i], $laborPoolFile, $laborSlotFile);
			$laborList->addItem($oldLaborID);

			// need to make empty labor dat for this one
			$thisFactory->laborItems[$i]->clear();
		}
	}
	else if ($laborStatus[$i] != $i+1) {
		echo '<br>Item '.$postVals[2+$i].' move to spot '.$i;
		$thisFactory->laborItems[$i] = $thisFactory->laborItems[$postVals[2+$i]-1]; // adjust for +1 offset in post vals
	}
}

// add the moved items from the labor pool
for ($i=1; $i<12; $i++) {
	if ($postVals[1+$i] > 10) {
		echo '<br>Move item '.$postVals[1+$i].' from the labor pool to spot '.$i;
		$newLabor = loadLaborItem($postVals[1+$i], $laborPoolFile);
		$thisFactory->laborItems[$i-1] = $newLabor;
	}
}
echo '<p>Save the labor';
for ($i=0; $i<10; $i++) {
		echo '<P>LABOR ITEM '.$i.'<br>';
		print_r($thisFactory->laborItems[$i]);
}

$thisFactory->saveLabor();



$productionSpots = $thisFactory->objDat[$thisFactory->productionSpotQty];
$updatedProductionRates = [0,0,0,0,0];
for ($i=0; $i<$productionSpots; $i++) {
	$productionRate = $thisFactory->setProdRate($i);
	$thisFactory->objDat[$thisFactory->currentProductionRateOffset+$i] = $productionRate[0];
	$updatedProductionRates[$i] = $productionRate[0];
	$thisFactory->productionQuality[$i+1] = $productionRate[1];
}

echo '<p>Factory items:';
print_r($thisFactory->laborItems);
$returnStr = '0|'.implode('|', $thisFactory->laborItems[0]->laborDat);
for ($i=1; $i<10; $i++) {
	//print_r($thisFactory->laborItems[$i]->laborDat);
	$returnStr .= '|'.$i.'|'.implode('|', $thisFactory->laborItems[$i]->laborDat);
}
$updatedProductionRates = [0,0,0,0,0];
echo '<--!-->1|'.implode('|', $updatedProductionRates).'|'.$returnStr;

fclose($objFile);
fclose($slotFile);
fclose($laborPoolFile);
fclose($laborSlotFile);

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
/*
$oldLaborID = 0;
if (flock($laborPoolFile, LOCK_EX)) {
	if ($postVals[3] > 99) {
		// * move a new labor item into this slot * \\

		// load the new labor
		$newLabor = loadLaborItem($postVals[3], $laborPoolFile);
		//echo 'New Labor:';
		//print_r($newLabor);

		$oldLabor = $thisFactory->laborItems[$postVals[2]];
		if ($oldLabor->laborDat[3] > 0) {
			// move the existing labor out of the factory and into the business slot file at the location of the old item
			fseek($laborPoolFile, $postVals[3]);
			fwrite($laborPoolFile, $oldLabor->packLabor());
			$oldLaborID = $postVals[3];
		} else {

			// No existing labor - delete the reference to this labor from the business labor pool and add it to the empty list
			$laborSlot = $thisBusiness->get('laborSlot');
			//echo 'Delete this labor spot from slot '.$laborSlot;
			if (flock($slotFile, LOCK_EX)) {
				if ($laborSlot == 0) {
					$laborSlot = newSlot($slotFile);
					$thisBusiness->save('laborSlot', $laborSlot);
					//echo 'Save new labor slot #'.$laborSlot;
				}
				$laborList = new itemSlot($laborSlot, $slotFile, 40);
				$laborList->deleteByValue($postVals[3]);
				flock($slotFile, LOCK_UN);
			}
		}

		// save the new labor into the factory
		$thisFactory->laborItems[$postVals[2]] = $newLabor;
		$thisFactory->laborItems[$postVals[2]]->laborDat[2] = intval($postVals[4]*100); // set the new pay rate

		//print_r($thisFactory->laborItems[$postVals[2]]);
		$thisFactory->saveLabor();
	}
	else if ($postVals[3] == $postVals[2]) {
		// adjust the existing labor item in the factory slot
		//echo 'Adjust existing labor';
		$oldLabor = $thisFactory->laborItems[$postVals[2]];
		$oldLabor->laborDat[2] = intval($postVals[4]*100); // set the new pay rate

		$thisFactory->saveLabor();
	}
	else if ($postVals[3] == -1) {
		echo 'Remove existing labor';
		// remove an item from the factory with no replacement
		$newLabor = loadLaborItem(0, $laborPoolFile);
		$oldLabor = $thisFactory->laborItems[$postVals[2]];
		if (flock($laborPoolFile, LOCK_EX)) {
			$oldLaborID = addLaborToPool($oldLabor, $laborPoolFile, $laborSlotFile);
			flock($laborPoolFile, LOCK_UN);
		}
		// record the labor in the company labor list
		if (flock($slotFile, LOCK_EX)) {
			$laborSlot = $thisBusiness->get('laborSlot');
			if ($laborSlot == 0) {
				$laborSlot = newSlot($slotFile);
				$thisBusiness->save('laborSlot', $laborSlot);
				//echo 'Save new labor slot #'.$laborSlot;
			}
			$laborList = new itemSlot($laborSlot, $slotFile, 40);
			$laborList->addItem($oldLaborID);
			flock($slotFile, LOCK_UN);
		}
		// record the empty labor in the old slot
		$thisFactory->laborItems[$postVals[2]] = $newLabor;
		$thisFactory->saveLabor();
	}

	flock($laborPoolFile, LOCK_UN);
}

$productionSpots = $thisFactory->objDat[$thisFactory->productionSpotQty];
$updatedProductionRates = [0,0,0,0,0];
for ($i=0; $i<$productionSpots; $i++) {
	$productionRate = $thisFactory->setProdRate($i);
	$thisFactory->objDat[$thisFactory->currentProductionRateOffset+$i] = $productionRate[0];
	$updatedProductionRates[$i] = $productionRate[0];
	$thisFactory->productionQuality[$i+1] = $productionRate[1];
}

$thisFactory->saveProductionRates();

$returnLabor = $thisFactory->laborItems[$postVals[2]]->laborDat;

echo '1,'.implode(',', $updatedProductionRates).','.$postVals[2].','.implode(',',$returnLabor).','.$postVals[3].','.$oldLaborID.','.implode(',',$oldLabor->laborDat);


*/


?>
