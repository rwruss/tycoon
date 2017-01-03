<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

$thisCity = loadCity($postVals[2], $cityFile);
$thisFactory = loadObject($postVals[1], $objFile, 1000);

echo 'Hire labor list item '.$postVals[4].' from city '.$postVals[2].' for factory '.$postVals[1];
$laborDat = array_slice($thisCity->objDat, $thisCity->laborStoreOffset+$postVals[4]*10, 10);
echo 'Labor Dat:';
print_R($laborDat);

// Confirm labor object still exists
$availableCheck = false;
if ($laborDat[0] == 0) $availableCheck = true;

if ($availableCheck) exit('This unit is no longer available');


print_r($laborDat);
// Load labor Dat and adjust parameters
$now = time();
$laborDat[7] = $now;
$laborSpotCheck = -1;
for ($i=0; $i<10; $i++) {
	echo 'CHeck spot '.$i.' --> '.$thisFactory->objDat[$thisFactory->laborOffset+$i*10];
	if ($thisFactory->objDat[$thisFactory->laborOffset+$i*10] == 0) {
		$laborSpotCheck = $i;
		break;
	}
}

if ($laborSpotCheck >= 0) {
	// Add the labor and associated parameters to the factory labor
	echo 'add to factory spot '.$laborSpotCheck;
	$thisFactory->adjustLabor($laborSpotCheck, $laborDat);
} else {
	// Add the labor and associated parameters to the business labor
	echo 'add to business';
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
	$laborList->addItem($slotFile, $laborStr, $location);
}

// Delete the reference to the labor in the city labor inventory

if (flock($cityFile, LOCK_EX)) {
	$thisCity->changeLaborItem($postVals[4], [0,0,0,0,0,0,0,0,0,0]);

	flock($cityFile, LOCK_UN);
}

print_r($thisFactory->objDat);
echo 'Final factory labor:';
print_R(array_slice($thisFactory->objDat, $thisFactory->laborOffset-1, 100));

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
