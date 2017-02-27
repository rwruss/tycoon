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

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$schoolFile = fopen($gamePath.'/schools.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');


// if factory is hiring - load the factory and check that player controls it
if ($postVals[3] > 0) {
	$thisFactory = loadObject($postVals[1], $objFile, 1000);
	if ($thisFactory->get('owner') != $pGameID) exit("You are not authorized to hire at this factory");

	$spotFail = true;
	for ($i=0; $i<7; $i++) {
		if ($thisFactory->objDat[131+$i*10] == 0) {
			$spotFail = false;
			$factorySpot = $i;
			break;
		}
	}
	if ($spotFail) exit("No more room for labor at this factory");
}

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
		//error fix me!
	}

	if ($schoolFail) exit("This school cannot train this type of labor");
} else {
	// hiring from the global labor pool
	$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'r+b');
	$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'r+b');
	
	if (flock($laborPoolFile, LOCK_EX) {
		if (flock($laborSlotFile, LOCK_EX) {
	
			// load the labor item details 
			fseek($laborPoolFile, $postVals[2]);
			$laborDat = unpack('i*', fread($laborPoolFile, 40));
			
			// add a marker for an empty labor spot
			$emptySpots = new itemSlot(0, $laborSlotFile, 40);
			$emptySpots->addItem($postVals[2], $laborSlotFile);
			
			// remove the labor from its type slot
			$homeCity = loadCity($laborDat[9], $cityFile);
			$cityLabor = new itemSlot($homeCity->get('cityLaborSlot'), $laborSlotFile, 40);
			$cityLabor->deleteByValue($postVals[2], $laborSlotFile);
			
			// remove the labor from its city slot
			$laborTypeList = new itemSlot($laborType, $laborSlotFile, 40);
			$laborTypeList->deleteByValue($postVals[2], $laborSlotFile);
			
			flock($laborSlotFile, LOCK_UN);
		}
		flock($laborPoolFile, LOCK_UN);
	}			
		
	fclose($laborPoolFile);
	fclose($laborSlotFile);
}

// Add the labor to the factory or the company
$now = time();
if ($postVals[3] > 0) {
	echo 'Add to factory labor spot '.$factorySpot;
	$thisFactory->changeLaborItem($factorySpot, [$postVals[2], 0, 0, $now, 0, 0, $now, $now, 0, 0]); //($spotNumber, $attrArray)
} else {
	echo 'Add to company labor spot';
	$thisBusiness = loadObject($pGameID, $objFile, 400);

	$laborSlot = $thisBusiness->get('laborSlot');
	if ($laborSlot == 0) {
		$laborSlot = newSlot($slotFile);
		$thisBusiness->save('laborSlot', $laborSlot);
		echo 'Save new labor slot #'.$laborSlot;
	}
	$laborList = new blockSlot($laborSlot, $slotFile, 40);

	$location = 0;
	for ($i=1; $i<sizeof($laborList->slotData); $i+=10) {
		if ($laborList->slotData[$i] == 0) {
			$location = $i;
			break;
		}
	}
	$laborStr = pack('i*', $postVals[2], 0, 0, $now, 0, 0, $now, $now, 0, 0);
	$laborList->addItem($slotFile, $laborStr, $location);
}
/*
echo '<script>';
$schoolSize = sizeof($schoolDat);
for ($i=1; $i<$schoolSize; $i+=2) {
	echo 'laborArray['.$schoolDat[$i].'].renderHire(target, '.$schoolDat[$i+1].', '.$postVals[3].', '.$postVals[4].');';
}
echo '</script>';
*/
fclose($objFile);
fclose($schoolFile);
fclose($cityFile);
fclose($slotFile)

?>
