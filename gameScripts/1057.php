<?php

/*
pvs
1-city ID
2-labor type # or item #
3 - factory
4 - school type
*/

/*
Case 1 - Hire a new labor item from a school to a factory
Case 2 - Hire a new labor item from a school to a business ($postVals[3] == 0)
Case 3 - Hire an existing labor item from the labor pool to a factory
Case 4 - Hire an existing labor item from the labor pool to a business ($postVals[3] == 0)
*/


echo 'Hire labor type '.$postVals[2].' from school type '.$postVals[4].' at city '.$postVals[1].' for factory '.$postVals[3];

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b'); //r+b
$schoolFile = fopen($gamePath.'/schools.dat', 'r+b'); //r+b
$cityFile = fopen($gamePath.'/cities.dat', 'r+b'); //r+b
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b'); //r+b

$now = time();
$gameHour = floor(($now-1501545600)/3600); // hours since August 1, 2017, 0:0:0 GMT
$useLaborID = $postVals[2];
$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'r+b');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'r+b');

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

// adjust the labor information
$trgLabor->laborDat[1] = 1; // Current city
$returnStr = '';
if ($postVals[3] == 0) {
	// hire to a business

	// get the labor item
	if ($postVals[2] < 25) {
		$useLabor = templateLabor($postVals[2], $gameHour, $laborPoolFile);
	} else $useLabor = $existingLabor($postVals[2], $laborPoolFile);

	//echo 'HIRING LABOR:';
	print_r($useLabor);
	// Adjust labor parameters
	$trgLabor->laborDat[1] = 1; // Current city

	// Save labor
	if ($postVals[2] < 25) {
		$useLocation = saveAsNew($useLabor, $laborPoolFile, $laborSlotFile);
	} else {
		fseek($laborPoolFile, $postVals[2]);
		fwrite($laborPoolFile, $useLabor->packLabor());
		$useLocation = $postVals[2];
	}

	// add to business item list
	//echo 'Add to company labor spot';
	$thisBusiness = loadObject($pGameID, $objFile, 400);

	$laborSlot = $thisBusiness->get('laborSlot');
	if ($laborSlot == 0) {
		$laborSlot = newSlot($slotFile);
		$thisBusiness->save('laborSlot', $laborSlot);
		//echo 'Save new labor slot #'.$laborSlot;
	}
	$laborList = new itemSlot($laborSlot, $slotFile, 40);
	$laborList->addItem($useLocation);

	$returnStr = implode(',', $useLabor->laborDat);
} else {
	// hire to a factory

	// determine factory labor slot (if available) - done above

	// get the labor item
	if ($postVals[2] < 25) {
		$useLabor = templateLabor($postVals[2], $gameHour, $laborPoolFile);
	} else {
		$useLabor = $existingLabor($postVals[2], $laborPoolFile);
		deleteLabor($postVals[2], $laborPoolFile, $laborSlotFile);
	}

	// Adjust labor parameters
	$trgLabor->laborDat[1] = 1; // Current city

	// save in factory
	$thisFactory->laborItems[$factorySpot] = $useLabor;
	$thisFactory->saveLabor();
}

// remove from city pool list
if ($useLabor->laborDat[1] > 0) {
	$homeCity = loadCity($useLabor->laborDat[1], $cityFile);
	$cityLabor = new itemSlot($homeCity->get('cityLaborSlot'), $laborSlotFile, 40);
	$cityLabor->deleteByValue($postVals[2], $laborSlotFile);
}

// remove from type pool list
$laborTypeList = new itemSlot($useLabor->laborDat[3], $laborSlotFile, 40);
$laborTypeList->deleteByValue($postVals[2], $laborSlotFile);

echo $returnStr;

function deleteLabor($id, $laborPoolFile, $laborSlotFile) {
	$emptyLabor = loadLaborItem(0, NULL);
	fseek($laborPoolFile, $id);
	fwrite($laborPoolFile, $emptyLabor->packLabor());

	$emptySpots = new itemSlot(0, $laborSlotFile, 40, TRUE);
	$emptySpots->addItem($id);
}

function saveAsNew($laborItem, $laborPoolFile, $laborSlotFile) {
	// look for empty spots in the laborPoolFile
	$useLocation = 0;
	$emptySpots = new itemSlot(0, $laborSlotFile, 40, TRUE);
	for ($i=1; $i<$z=sizeof($emptySpots->slotData); $i++) {
		if ($emptySpots->slotData[$i] > 0) {
			$useLocation = $emptySpots[$i];
			$emptySpots->deleteByValue($emptySpots[$i]);
			break;
		}
	}

	if ($useLocation == 0) {
		fseek($laborPoolFile, 0, SEEK_END);
		$useLocation = ftell($laborPoolFile);
	}

	fseek($laborPoolFile, $useLocation);
	fwrite($laborPoolFile, $laborItem->packLabor());
	return ($useLocation);
}

function templateLabor($typeID, $gameHour, $laborPoolFile) {
	// Load a labor template
	fseek($laborPoolFile, $typeID*88);
	$trgLabor = new labor(fread($laborPoolFile, 48));

	$trgLabor->laborDat[4] = $gameHour; // creation time
	return $trgLabor;
}

function existingLabor($id, $laborPoolFile) {
	// load an existing labor item
	fseek($laborPoolFile, $id);
	$trgLabor = new labor(fread($laborPoolFile, 48));

	return $trgLabor;
}


fclose($laborPoolFile);
fclose($laborSlotFile);
fclose($objFile);
fclose($schoolFile);
fclose($cityFile);
fclose($slotFile)

?>
