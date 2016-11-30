<?php

print_r($postVals);

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the business & factory
$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1000);

// Determine add and remove lists
$addList = [];
$keepList = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
for ($i=2; $i<12; $i++) {
  if ($postVals[$i] > 10) {
    $addList[] = $postVals[$i]-10;
  } else $keepList[$postVals[$i]] = 1;

}

print_r($thisFactory->objDat);

echo 'Add to factory:';
print_R($addList);

echo 'Keep List';
print_R($keepList);

$startFactoryLabor = array_slice($thisFactory->objDat, 131, 100);

// Load the business labor slot to get the relevant items
$openSlotSpots = $addList;
$slotsToClear = sizeof($openSlotSpots);
$businessLabor = new blockSlot($thisBusiness->get('laborSlot'), $slotFile, 40);

$usedItems = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

for ($i=0; $i<10; $i++) {
	$newLaborID = $postVals[2+$i];
	if ($newLaborID < 10) {
		$usedITems[$newLaborID] = 1; // marks the unit as used so it is not added to the slot list
		if ($newLaborID == $i) {
		// No change to this unit
		} else {
			// Add a unit that is already in the factory
			for ($datItem = 0; $datItem<10; $datItem++) {
				$thisFactory->objDat[131 + $i*10+$datItem] = $startFactoryLabor[$newLaborID*10+$datItem];
				$thisFactory->objDat[131 + $newLaborID*10 + $datItem] = 0;
			}			
		}
	}
	else 
}


/// load production requirements
fseek($thisFactory->linkFile, $thisFactory->get('currentProd')*1000);
$productInfo = unpack('i*', fread($thisFactory->linkFile, 200));

/// Load product labor equivalencies
$laborTotal = 0;
$laborCount = 1;

for ($i=0; $i<8; $i++) {
	if ($productInfo[18+$i]>0) {
		$offset = 50+$i*25;
		$laborCount += $productInfo[$offset];
		for ($j=0; $j<10; $j++) {
			if ($productInfo[$offset+$j*2] == $thisFactory->objDat[$thisFactory->laborOffset+$i*10]) $laborTotal += $productInfo[$offset+$j*2+1];
		}
	}
}
$laborModifier = $laborTotal/max(1.0, $laborCount)*100;
$laborModifier = 1.0; //override
$thisFactory->set('currentRate', $laborModifier);

echo 'LABOR MOD OF '.$laborModifier;

$thisFactory->saveAll($objFile);

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
