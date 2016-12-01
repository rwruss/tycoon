<?php

//print_r($postVals);

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

//print_r($thisFactory->objDat);

echo 'Add to factory:';
print_R($addList);

echo 'Keep List';
print_R($keepList);

$lOff = $thisFactory->laborOffset;
$startFactoryLabor = array_slice($thisFactory->objDat, $lOff, 100);

// Load the business labor slot to get the relevant items
$openSlotSpots = [];
$businessLabor = new blockSlot($thisBusiness->get('laborSlot'), $slotFile, 40);

$usedItems = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

for ($i=0; $i<10; $i++) {
	$newLaborID = $postVals[3+$i*2];
	if ($newLaborID < 11) {
		$usedItems[$newLaborID-1] = 1; // marks the unit as used so it is not added to the slot list
		if ($newLaborID == $i+1) {
		// No change to this unit
		} else {
			// Add a unit that is already in the factory
			for ($datItem = 0; $datItem<10; $datItem++) {
				$thisFactory->objDat[$lOff + $i*10+$datItem] = $startFactoryLabor[$newLaborID*10+$datItem];
				$thisFactory->objDat[$lOff + $newLaborID*10 + $datItem] = 0;
			}
		}
	}
	else {
		// Add item from the blockSlot to the factory and remove from the blockslot
		$thisFactory->objDat[$lOff+$i*10] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+1] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+2] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+3] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+4] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+5] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+6] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+7] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+8] = $businessLabor[$newLaborID];
		$thisFactory->objDat[$lOff+$i*10+9] = $businessLabor[$newLaborID];
		
		$openSlotSpots[] = $newLaborID;
	}
}

echo 'Used items:';
print_r($usedItems);

// Move items from the company slot into the factory

// Move unused items from the factory into the slots
for($i=0; $i<10; $i++) {
	if ($usedItems[$i] == 0) {
		$val = $i*10;
		$laborDat = pack('i*', $startFactoryLabor[$val], $startFactoryLabor[$val+1], $startFactoryLabor[$val+2], $startFactoryLabor[$val+3], $startFactoryLabor[$val+4], $startFactoryLabor[$val+5], $startFactoryLabor[$val+6], $startFactoryLabor[$val7], $startFactoryLabor[$val+8], $startFactoryLabor[$val+9]);
		$loc = array_shift($openSlotSpots);
		if (is_null($loc)) $loc = 0;
		
		$businessLabor->addItem($slotFile, $laborDat, $loc);
	}
}

// Clear empty labor slots
for ($i=0; $i<sizeof($openSlotSpots); $i++) {
	$laborDat = pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	$businessLabor->addItem($slotFile, $laborDat, $openSlotSpots[$i]);
}

// Calculate the facility production rate based on the assigned labor

/// load production requirements
fseek($objFile, $thisFactory->get('currentProd')*1000);
$productInfo = unpack('i*', fread($objFile, 1000));

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
