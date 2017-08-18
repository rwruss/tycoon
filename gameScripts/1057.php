<?php

/*
pvs
1-city ID
2-labor type # or item #
3 - factory
4 - school type
*/

echo 'Hire labor type '.$postVals[2].' from school type '.$postVals[4].' at city '.$postVals[1].' for factory '.$postVals[3];

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb'); //r+b
$schoolFile = fopen($gamePath.'/schools.dat', 'rb'); //r+b
$cityFile = fopen($gamePath.'/cities.dat', 'rb'); //r+b
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb'); //r+b

$now = time();

// if factory is hiring - load the factory and check that player controls it
if ($postVals[3] > 0) {
	$thisFactory = loadObject($postVals[3], $objFile, 1600);
	if ($thisFactory->get('owner') != $pGameID) exit("You are not authorized to hire at this factory");

	$spotFail = true;
	for ($i=0; $i<10; $i++) {
		if ($thisFactory->laborItems[$i]->laborDat[2] == 0) {
			$spotFail = false;
			$factorySpot = $i;
			break;
		}
	}
	if ($spotFail) exit("No more room for labor at this factory");
}

$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'r+b');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'r+b');
if ($postVals[1] > 0) {
	// Load the city
	$thisCity = loadCity($postVals[3], $cityFile);

	// Load the school types
	fseek($schoolFile, $postVals[4]*8);
	$schoolHead = unpack('i*', fread($schoolFile, 8));

	fseek($schoolFile, $schoolHead[1]);
	$schoolDat = unpack('i*', fread($schoolFile, $schoolHead[2]));

	// verify that the school exists in the city
	$schoolLevel = $thisCity->objDat[91+($postVals[4]-1)*3];

	// verify that the school can train this type of labor
	$schoolFail = true;
	$schoolSize = sizeof($schoolDat);
	for ($i=1; $i<$schoolSize; $i+=2) {
		$schoolFail = false;
	}

	if ($schoolFail) exit("This school cannot train this type of labor");
		
	// school quality
	$schoolQuality = 100;
	
	// load the default for this labor type
	fseek($laborPoolFile, $postVals[2]*48);
	$newLaborItem = new labor(fread($laborPoolFile, 48), null);
	
	// create a new labor type
	$newLaborItem->laborDat = array_fill(1, 29, 0);
	$newLaborItem->laborDat[1] = $postVals[3]; // current city
	$newLaborItem->laborDat[2] = 0; // current pay
	$newLaborItem->laborDat[3] = 0; // labor type
	$newLaborItem->laborDat[4] = $now; // creating time
	$newLaborItem->laborDat[4] = $postVals[3]; // home city
	$newLaborItem->laborDat[5] = 0; // talent
	$newLaborItem->laborDat[6] = 0; // motivation
	$newLaborItem->laborDat[7] = 0; // intelligence
	$newLaborItem->laborDat[18] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[19] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[20] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[21] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[22] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[23] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[24] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[25] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[26] = $schoolQuality; // skill Points
	$newLaborItem->laborDat[27] = $schoolQuality; // skill Points
} else {
	// hiring from the global labor pool
	echo 'Hire labor item '.$postVals[2].' from the labor pool';
	
	

	if (flock($laborPoolFile, LOCK_EX)) {
		if (flock($laborSlotFile, LOCK_EX)) {

			// load the labor item details
			fseek($laborPoolFile, $postVals[2]);
			$newLaborItem = new labor(fread($laborPoolFile, 44), $laborPoolFile);
			/*
			$laborDat = unpack('i*', fread($laborPoolFile, 40));
			print_r($laborDat);*/

			// remove the labor from its city slot
			$homeCity = loadCity($newLaborItem->laborItems[1], $cityFile);
			$cityLabor = new itemSlot($homeCity->get('cityLaborSlot'), $laborSlotFile, 40);
			$cityLabor->deleteByValue($postVals[2], $laborSlotFile);

			// remove the labor from its type slot
			$laborTypeList = new itemSlot($newLaborItem->laborItems[3], $laborSlotFile, 40);
			$laborTypeList->deleteByValue($postVals[2], $laborSlotFile);

			flock($laborSlotFile, LOCK_UN);
		}
		flock($laborPoolFile, LOCK_UN);
	}

	
}

// Add the labor to the factory or the company
if ($postVals[3] > 0) {
	// add to factory
	echo 'Add to factory labor spot '.$factorySpot;
	//$thisFactory->adjustLabor($factorySpot, array_values($laborDat)); //($spotNumber, $attrArray)
	$thisFactory->laborItems[$factorySpot] = $newLaborItem;
	$thisFactory->saveLabor();
	
	if ($postVals[1] == 0) { // hiring for existing labor pool so delete the item from the pool
		// delete from labor pool
		fseek($laborPoolFile, $postVals[2]);
		fwrite($laborPoolFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
		
		// add a marker for an empty labor spot
		echo 'record empty marker';
		$emptySpots = new itemSlot(0, $laborSlotFile, 40, TRUE);
		$emptySpots->addItem($postVals[2], $laborSlotFile);
	}
	
} else {
	
	echo 'Add to company labor spot';
	$thisBusiness = loadObject($pGameID, $objFile, 400);

	$laborSlot = $thisBusiness->get('laborSlot');
	if ($laborSlot == 0) {
		$laborSlot = newSlot($slotFile);
		$thisBusiness->save('laborSlot', $laborSlot);
		echo 'Save new labor slot #'.$laborSlot;
	}
	$laborList = new itemSlot($laborSlot, $slotFile, 40);
	$laborList->addItem($postVals[2]);

	echo '<script>addCompanyLabor(['.implode(unpack('i*', $laborStr)).'], companyLabor)</script>';
}
fclose($laborPoolFile);
fclose($laborSlotFile);
fclose($objFile);
fclose($schoolFile);
fclose($cityFile);
fclose($slotFile)

?>
