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
	$thisFactory = loadObject($postVals[3], $objFile, 1000);
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

	$laborDat = [$postVals[2], 0, 0, $now, 0, 0, $now, $now, $postVals[3], 0];
} else {
	// hiring from the global labor pool
	echo 'Hire labor item '.$postVals[2].' from the labor pool';
	$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'r+b');
	$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'r+b');

	if (flock($laborPoolFile, LOCK_EX)) {
		if (flock($laborSlotFile, LOCK_EX)) {

			// load the labor item details
			fseek($laborPoolFile, $postVals[2]);
			$laborDat = unpack('i*', fread($laborPoolFile, 40));
			print_r($laborDat);

			// add a marker for an empty labor spot
			echo 'record empty marker';
			$emptySpots = new itemSlot(0, $laborSlotFile, 40, TRUE);
			$emptySpots->addItem($postVals[2], $laborSlotFile);

			// remove the labor from its city slot
			$homeCity = loadCity($laborDat[9], $cityFile);
			$cityLabor = new itemSlot($homeCity->get('cityLaborSlot'), $laborSlotFile, 40);
			$cityLabor->deleteByValue($postVals[2], $laborSlotFile);

			// remove the labor from its type slot
			$laborTypeList = new itemSlot($laborDat[1], $laborSlotFile, 40);
			$laborTypeList->deleteByValue($postVals[2], $laborSlotFile);

			flock($laborSlotFile, LOCK_UN);
		}
		flock($laborPoolFile, LOCK_UN);
	}

	fclose($laborPoolFile);
	fclose($laborSlotFile);
}

// Add the labor to the factory or the company

if ($postVals[3] > 0) {
	echo 'Add to factory labor spot '.$factorySpot;
	$thisFactory->adjustLabor($factorySpot, array_values($laborDat)); //($spotNumber, $attrArray)
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

	$location = ceil(sizeof($laborList->slotData)/10)*10+1;
	for ($i=1; $i<sizeof($laborList->slotData); $i+=10) {
		if ($laborList->slotData[$i] == 0) {
			$location = $i;
			break;
		}
	}
	//$laborStr = pack('i*', $postVals[2], 0, 0, $now, 0, 0, $now, $now, 0, 0);
	$laborDat = [];
	$laborDat[0] = 0; // labor type
	$laborDat[1] = $now; // creation time - need to adjust for days/hours
	$laborDat[2] = 0; // home city
	$laborDat[3] = 0; // skill pts 1
	$laborDat[4] = 0; // skill pts 2
	$laborDat[5] = 0; // skill pts 3
	$laborDat[6] = 0; // skill pts 4
	$laborDat[7] = 0; // skill pts 5
	$laborDat[8] = 0; // skill pts 6
	$laborDat[9] = 0; // skill pts 7
	$laborDat[10] = 0; // skill pts 8
	$laborDat[11] = 0; // skill pts 9
	$laborDat[12] = 0; // skill pts 10
	$laborDat[13] = 1; // Talent
	$laborDat[14] = 1; // Motivation
	$laborDat[15] = 1; // Intelligence
	$laborDat[16] = 1; // skill 1
	$laborDat[17] = 2; // skill 2
	$laborDat[18] = 3; // skill 3
	$laborDat[19] = 4; // skill 4
	$laborDat[20] = 5; // skill 5
	$laborDat[21] = 6; // skill 6
	$laborDat[22] = 7; // skill 7
	$laborDat[23] = 8; // skill 8
	$laborDat[24] = 9; // skill 9
	$laborDat[25] = 10; // skill 10

	print_r(array_slice($laborDat, 0, 3));
	print_r(array_slice($laborDat, 3, 10));
	print_r(array_slice($laborDat, 13, 10));

	$s2 = packArray(array_slice($laborDat, 0, 13), 's');
	$s3 = packArray(array_slice($laborDat, 13, 13), 'C');

	$laborStr = packArray(array_slice($laborDat, 3, 13), 's').packArray(array_slice($laborDat, 13, 13), 'C');
	echo 'LABOR STRING LENGTH is '.strlen($laborStr).' --> '.strlen($s2).' + '.strlen($s3);


	$laborList->addItem($slotFile, $laborStr, $location);

	echo '<script>addCompanyLabor(['.implode(unpack('i*', $laborStr)).'], companyLabor)</script>';
}

fclose($objFile);
fclose($schoolFile);
fclose($cityFile);
fclose($slotFile)

?>
