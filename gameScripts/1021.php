<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[2], $cityFile);
$thisFactory = loadObject($postVals[1], $objFile, 1000);

echo 'Hire labor list item '.$postVals[4].' from city '.$postVals[2].' for factory '.$postVals[1];
$laborDat = array_slice($thisCity->objDat, $thisCity->laborStoreOffset+$postVals[4]*10, 10);

// Confirm labor object still exists
$availableCheck = false;
if ($laborDat[0] == 0) $availableCheck = true;

if (!$availableCheck) exit('This unit is no longer available');


print_r($laborDat);
// Load labor Dat and adjust parameters
$now = time();
$laborDat[7] = $now;
for ($i=0; $i<10; $i++) {
	echo 'CHeck spot '.$i.' --> '.$thisFactory->objDat[$thisFactory->laborOffset+$i*10];
	if ($thisFactory->objDat[$thisFactory->laborOffset+$i*10] == 0) {
		$laborSpotCheck = $i;
	}
}

if ($laborSpotCheck) {
	// Add the labor and associated parameters to the factory labor
	echo 'add to factory';
	$thisFactory->adjustLabor($laborSpotCheck, $laborDat);
} else {
	// Add the labor and associated parameters to the business labor
	echo 'add to business';
	$thisBusiness = loadObject($pGameID, $objFile, 400);
	
	$laborSlot = $thisBusiness->get('laborSlot');
	if ($laborSlot == 0) {
		$laborSlot = newSlot($slotFile);
		$thisBusiness->save('laborSlot', $laborSlot);
	}
	$laborList = new blockSlot($laborSlot, $slotFile, 40);
	
	$location = 0;
	for ($i=1; $i<sizeof($laborList->slotData); $i+=10;) {
		if ($laborList->slotData[$i] == 0) {
			$location = $i;
			break;
		}
	}
	$laborList->addItem($slotFile, $laborStr, $location);
}

// Delete the reference to the labor in the city labor inventory
$emptyDat = pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
if (flock($cityFile, LOCK_EX)) {
	$thisCity->changeLaborItem($postVals[4], [0,0,0,0,0,0,0,0,0,0]);
	
	flock($cityFile, LOCK_UN);
}


fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
