<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[2], $cityFile);
$thisFactory = loadObject($postVals[1], $objFile, 1000);



// Read object dat for player storage
//$laborDat = pack()

// Overwrite existing data with empty spot


echo 'Hire labor list item '.$postVals[4].' from city '.$postVals[2].' for factory '.$postVals[1];
$laborDat = array_slice($thisCity->objDat, $thisCity->laborStoreOffset+$postVals[4]*10, 10);
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
}

/*
$laborTemplateFile = fopen($scnPath.'/laborTemplate.dat', 'rb');
fseek($laborTemplateFile, $postVals[4]*8);

$laborSlot = new blockSlot($thisBusiness->get('laborSlot'), $slotFile, 40);
$now = time();
$blockDat = fread($laborTemplateFile, 8).pack('i*', 0, 0, $thisCity->get('region'), 100, $now, 0, 0, 0); // ability, start time, home region, expected pay, last update
*/
fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
