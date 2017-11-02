<?php

/*
PVs
2 = new factory type
3 = city location for factory
*/

$factoryType = $postVals[1];
$cityLoc = $postVals[2];

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$projectFile = fopen($gamePath.'/projects.prj', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

// Load template data
fseek($objFile, $factoryType*$templateBlockSize);
$templateDat = unpack('i*', fread($objFile, $templateBlockSize));

// Verify that the player has enough money to build this factory
$factoryCost = $templateDat[8];
$thisBusiness = loadObject($pGameID, $objFile, 400);
if ($factoryCost > $thisBusiness->get('money')) {
	//echo 'You do not have enough money to build this type of factory.  You need '.$factoryCost.' - '.$thisBusiness->get('money').' = '.($factoryCost - $thisBusiness->get('money')).' to start construction.';
	exit("-1|Not enough Money");
}

// Deduct the cost of the factory
$thisBusiness->save('money', $thisBusiness->get('money')-$factoryCost);

// Get a new project ID
$newProjID = 0;
$emptyProjects = new itemSlot(0, $projectFile, 100, TRUE);
for ($i=1; $i<$z=sizeof($emptyProjects->slotData); $i++) {
	if ($emptyProjects->slotData[$i] > 0) {
		$newProjID = $emptyProjects->slotData[$i];
		$emptyProjects->deleteByValue($newProjID);
		break;
	}
}
if ($newProjID == 0) {
	if (flock($projectFile, LOCK_EX)) {

		fseek($projectFile, 0, SEEK_END);
		$newProjID = max(ceil(ftell($projectFile)/100),2);
		//echo '<p>NEW PROJECT ID IS '.$newProjID.'<p>';
		if ($newProjID > 0) {
			fseek($projectFile, $newProjID*100+96);
			fwrite($projectFile, pack('i', 0));
			fflush($projectFile);
			flock($projectFile, LOCK_UN);
		} else {
			flock($projectFile, LOCK_UN);
			fclose($projectFile);
			exit('error 8001-1');
		}
	}
}


// Create a new factory object
if (flock($objFile, LOCK_EX)) {
	// get new ID
	fseek($objFile, 0, SEEK_END);
	$newID = ftell($objFile)/100;

	fseek($objFile, $newID*$defaultBlockSize + 1596);
	fwrite($objFile, pack('i', 0));

	//echo 'Template type: '.$factoryType.' for new object '.$newID;
	$newObjDat = array_fill(1, 400, 0);
	$newObj = new factory($newID, packArray($newObjDat), $objFile);

	// Set default parameters for this factory
	$now = time();
	$newObj->set('groupType', $templateDat[6]);
	$newObj->set('factoryLevel', 0);
	$newObj->set('factoryStatus', 0);
	$newObj->set('constStatus', $newProjID);
	$newObj->set('upgradeInProgress', 1);
	$newObj->set('currentProd', $templateDat[11]);
	$newObj->set('oType', 3);
	$newObj->set('owner', $pGameID);
	$newObj->set('lastUpdate', $now);
	$newObj->set('subType', $factoryType);
	$newObj->set('region_3', $cityLoc);
	$newObj->set('region_2', 0);
	$newObj->set('region_1', 0);
	$newObj->objDat[130] = 1;
	$newObj->saveAll($objFile);

	$testDat = unpack('i*', fread($slotFile, 40));
	//echo 'Prelim slot check:';
	print_r($testDat);

	// Add unit to player's list of objects
	if ($thisBusiness->get('ownedObjects') == 0) {
		$thisBusiness->save('ownedObjects', newSlot($slotFile));
	}
	//echo 'Load slot '.$thisBusiness->get('ownedObjects');
	$ownedObjects = new itemSlot($thisBusiness->get('ownedObjects'), $slotFile, 40);
	$ownedObjects->addItem($newID, $slotFile);

	// Add to list of factories at the city
	//echo '<p>LOAD AND SAVE THE CITY:<br>';
	$buildCity = loadCity($cityLoc, $cityFile);
	if ($buildCity->get('factoryList') == 0) {
		//echo 'New factory slot for the city'.
		$newSlot = newSlot($slotFile, 40);
		$buildCity->save('factoryList', $newSlot);
	}
	$cityFactories = new itemSlot($buildCity->get('factoryList'), $slotFile, 40);
	$cityFactories->addItem($newID, $slotFile);
	flock($objFile, LOCK_UN);
}

// create a new project object
$projectDat = array_fill(1, 25, 0);
$projectDat[1] = $pGameID; // owner
$projectDat[2] = $newID; // factory ID
$projectDat[3] = 100; // factory type
$projectDat[4] = 100; //Points required
$projectDat[5] = 0; // points applied
$projectDat[6] = 0; // current price
$projectDat[7] = 1; // status
$projectDat[8] = 0; // status

//echo '<p>Project Data for project '.$newProjID.'<p>';
//print_r($projectDat);

$thisProject = new project($newProjID, packArray($projectDat), $projectFile);
//print_r($thisProject->objDat);
$thisProject->saveAll();

// Add to the list of open projects
$projectList = new itemSlot(1, $projectFile, 100);
$projectList->addItem($newProjID);

fclose($projectFile);
fclose($objFile);
fclose($slotFile);

echo '1|'.$thisBusiness->get('money').'|'.implode('|', $newObj->overViewInfo());

/*
echo '<script>
addFactory(['.implode(',', $newObj->overViewInfo()).']);
thisPlayer.money = '.$thisBusiness->get('money').'
</script>';
*/
?>
