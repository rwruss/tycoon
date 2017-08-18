<?php

/*
PVs
1-labor ID number (relative to company list or factory list)
2-factory ID (zero if it is in the company list)
*/

// load labor information
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'rb');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$laborSlot = new itemSlot($thisPlayer->get('laborSlot'), $slotFile, 40);
$emptyLabor = new labor(pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
if (flock($laborPoolFile, LOCK_EX)) {
	if ($postVals[2] > 0) {
		// remove labor from a factory and put into the labor pool
		$thisFactory = loadObject($postVals[2], $objFile, 1600);
		$removedLabor = $thisFactory->laborItems[$postVals[1]];
		
		$thisFactory->laborItems[$postVals[1]] = $emptyLabor;
		$thisFactory->saveLabor();
		
		// add it in to the labor pool
		$laborID = 0;
		$emptyList = new itemSlot(0, $laborPoolFile, 40, TRUE);
		for ($i=1; $i<$z=sizeof($emptyList->slotData); $i++) {
			if ($emptyList->slotData[$i] > 0) {
				$laborID = $emptyList->slotData[$i];
				$emptyList->removeByValue($emptyList->slotData[$i]);
				break;
			}
		}
		
		if ($laborID == 0) {
			fseek($laborPoolFile, 0, SEEK_END);
			$laborID = ftell($laborPoolFile);
		}
		fseek($laborPoolFile, $laborID);
		fwrite($laborPoolFile, removedLabor->packLabor());
		
	} else {
		// remove the labor from a player labor pool
		$laborID = $laborSlot->slotData[$postVals[1]];
		fseek($laborPoolFile, $laborID);
		$removedLabor = new labor(fread($laborPoolFile, 48));
	}
	flock($laborPoolFile, LOCK_UN);
}

if (flock($laborSlotFile, LOCK_EX)) {
	// add labor to list of labor by type
	$laborTypeList = new itemSlot($laborType, $laborSlotFile, 40);
	$laborTypeList->addItem($laborID, $laborSlotFile);

	// add labor to the list of labor by city
	$thisCity = loadCity($homeCity, $cityFile);
	if ($thisCity->get('cityLaborSlot') == 0) {
		$thisCity->save('cityLaborSlot', newSlot($laborSlotFile));
	}
	
	$cityLabor = new itemSlot($thisCity->get('cityLaborSlot'), $laborSlotFile, 40);
	$cityLabor->addItem($laborID, $laborSlotFile);
}

/*
$emptyData = pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
if ($postVals[2] > 0) {
	// this labor is at a factory
	echo 'Factory labor';
	$thisFactory = loadObject($postVals[2], $objFile, 1000);

	// get the labor dat
	$a = $thisFactory->laborOffset+($postVals[1]-1)*10;
	$laborDat = pack('i*', $thisFactory->objDat[$a], $thisFactory->objDat[$a+1], $thisFactory->objDat[$a+2], $thisFactory->objDat[$a+3], $thisFactory->objDat[$a+4], $thisFactory->objDat[$a+5], $thisFactory->objDat[$a+6], $thisFactory->objDat[$a+7], $thisFactory->objDat[$a+8], $thisFactory->objDat[$a+9]);

	$fLaborOffset = $a;
	$homeCity = $thisFactory->objDat[$fLaborOffset+8];
	$laborType = $thisFactory->objDat[$fLaborOffset];

	// Remove the labor from the factory
	$thisFactory->saveBlock($fLaborOffset, $emptyData);

} else {
	// this labor is in a company list
	$a = ($postVals[1]*10)+1;

	$thisBusiness = loadObject($pGameID, $objFile, 400);
	$businessLabor = new blockSlot($thisBusiness->get('laborSlot'), $slotFile, 40);

	echo 'Read company labotr slot ('.$thisBusiness->get('laborSlot').') starting at index '.$a;

	print_r($businessLabor->slotData);

	$laborDat = pack('i*', $businessLabor->slotData[$a], $businessLabor->slotData[$a+1], $businessLabor->slotData[$a+2], $businessLabor->slotData[$a+3], $businessLabor->slotData[$a+4], $businessLabor->slotData[$a+5], $businessLabor->slotData[$a+6], $businessLabor->slotData[$a+7], $businessLabor->slotData[$a+8], $businessLabor->slotData[$a+9]);
	$homeCity = $businessLabor->slotData[$a+8];
	$laborType = $businessLabor->slotData[$a];

	// Remove labor from company
	$businessLabor->addItem($slotFile, $emptyData, $a);
}

// insert into labor data file
if (flock($laborPoolFile, LOCK_EX)) {
	if (flock($laborSlotFile, LOCK_EX)) {
		fseek($laborPoolFile, 0, SEEK_END);
		$laborSpot = max(40,ftell($laborPoolFile));

		$emptySpots = new itemSlot(0, $laborSlotFile, 40, TRUE);
		for ($i=1; $i<sizeof($emptySpots->slotData); $i++) {
			if ($emptySpots->slotData[$i] > 0) {
				$laborSpot = $emptySpots->slotData[$i];
				$emptySpots->addItemAtSpot(0, $i, $laborSlotFile);  // remove reference to the empty slot
				break;
			}
		}
	flock($laborSlotFile, LOCK_UN);

	fseek($laborPoolFile, $laborSpot);
	fwrite($laborPoolFile, $laborDat);
	}

	// create reference to this labor in the city labor list
	$thisCity = loadCity($homeCity, $cityFile);
	if ($thisCity->get('cityLaborSlot') == 0) {
		$thisCity->save('cityLaborSlot', newSlot($laborSlotFile));
	}

	echo 'Add to city list ('.$thisCity->get('cityLaborSlot').')';
	$cityLabor = new itemSlot($thisCity->get('cityLaborSlot'), $laborSlotFile, 40);
	$cityLabor->addItem($laborSpot, $laborSlotFile);

	// create reference to this labor in the labor type list
	echo 'Add to labor list ('.$laborType.')';
	$laborTypeList = new itemSlot($laborType, $laborSlotFile, 40);
	$laborTypeList->addItem($laborSpot, $laborSlotFile);
	flock($laborSlotFile, LOCK_UN);
}
*/

fclose($objFile);
fclose($slotFile);
fclose($laborPoolFile);
fclose($laborSlotFile);
fclose($cityFile);

?>
