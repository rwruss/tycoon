<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[2], $cityFile);
$thisFactory = loadObject($postVals[1], $objFile, 400);

$laborSlot = new blockSlot($thisCity->get('laborSlot'), $slotFile, 40);

// Read object dat for player storage
//$laborDat = pack()

// Overwrite existing data with empty spot
$laborSlot->addItem($slotFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0), $postVals[4]);
print_R($laborSlot->slotData);

echo 'Hire labor list item '.$postVals[4].' from city '.$postVals[2].' for factory '.$postVals[1];
/*
// confirm there is enough labor of this type to hire
$laborCheck = true;
$thisCity->updateLabor($slotFile);
$laborQty = $thisCity->availableLabor();
if ($laborQty[$postVals[4] > 0) $laborCheck = false;

// remove the labor from the city store
if ($laborCheck) {
	$thisCity->saveLabor()
} else exit('Not enough labor to hire');

// Verify that the factory has an open labor spot
$laborSpotCheck = false;
for ($i=0; $i<10; $i++) {
	if ($thisFactory->objDat[$this->laborOffset+$i*10] == 0) {
		$laborLoc = $i;
		$laborSpotCheck = true;
		break;
	}
}
*/
// Load labor Dat and adjust parameters
$now = time();
$laborDat[7] = $now;

if ($laborSpotCheck) {
	// Add the labor and associated parameters to the factory labor
	$thisFactory->adjustLabor($laborLoc, $laborDat);
} else {
	// Add the labor and associated parameters to the business labor
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
