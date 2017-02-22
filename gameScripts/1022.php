<?php

//print_r($postVals);
/*
pvs
1-factory ID
2-factory labor slot
3-new labor item
4-new labor item pay rate
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$laborEqFile = fopen($scnPath.'/laborEq.dat', 'rb');

// Load the business & factory
$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1000);

$lOff = $thisFactory->laborOffset;
$startFactoryLabor = array_slice($thisFactory->objDat, $lOff-1, 100);

// Load the business labor slot to get the relevant items
$openSlotSpots = [];
if ($thisBusiness->get('laborSlot') == 0) {
	$thisBusiness->save('laborSlot', newSlot($slotFile));
}
$businessLabor = new blockSlot($thisBusiness->get('laborSlot'), $slotFile, 40);
//echo 'Labor slot Data:';
//print_r($businessLabor->slotData);

$usedItems = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$emptyData = pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
for ($i=0; $i<10; $i++) {
	$newLaborID = $postVals[3+$i*2];
  if ($newLaborID > 0) {
    if ($newLaborID < 11) {
      $usedItems[$newLaborID-1] = 1; // marks the unit as used so it is not added to the slot list
      if ($newLaborID == $i+1) {
        echo 'No change at spot '.$i;
      } else {
        echo 'Move item '.$newLaborID.' into spot '.$i.' from elsewhere in factory';
        for ($datItem = 0; $datItem<10; $datItem++) {
  				$thisFactory->objDat[$lOff + $i*10+$datItem] = $startFactoryLabor[$newLaborID*10-10+$datItem];
  				//$thisFactory->objDat[$lOff + $newLaborID*10-10 + $datItem] = 0;
				//echo ($lOff + $i*10+$datItem).' --> '.($startFactoryLabor[$newLaborID*10-10+$datItem]).' & '.($lOff + $newLaborID*10-10 + $datItem).' = 0<br>';
        }
			}
    } else {
      // Add item from the blockSlot to the factory and remove from the blockslot
      echo 'Add item '.$newLaborID.' (slot spot '.($newLaborID-10).') from the slot to the factory at spot '.$i;
  		$thisFactory->objDat[$lOff+$i*10] = $businessLabor->slotData[$newLaborID-10];
  		$thisFactory->objDat[$lOff+$i*10+1] = $businessLabor->slotData[$newLaborID-10+1];
  		$thisFactory->objDat[$lOff+$i*10+2] = $businessLabor->slotData[$newLaborID-10+2];
  		$thisFactory->objDat[$lOff+$i*10+3] = $businessLabor->slotData[$newLaborID-10+3];
  		$thisFactory->objDat[$lOff+$i*10+4] = $businessLabor->slotData[$newLaborID-10+4];
  		$thisFactory->objDat[$lOff+$i*10+5] = $businessLabor->slotData[$newLaborID-10+5];
  		$thisFactory->objDat[$lOff+$i*10+6] = $businessLabor->slotData[$newLaborID-10+6];
  		$thisFactory->objDat[$lOff+$i*10+7] = $businessLabor->slotData[$newLaborID-10+7];
  		$thisFactory->objDat[$lOff+$i*10+8] = $businessLabor->slotData[$newLaborID-10+8];
  		$thisFactory->objDat[$lOff+$i*10+9] = $businessLabor->slotData[$newLaborID-10+9];

			print_r($businessLabor->slotData);

  		$openSlotSpots[] = $newLaborID-10;
			$businessLabor->addItem($slotFile, $emptyData, $newLaborID-10);
    }
  } else {
    echo 'Make spot '.$i.' blank';
    $thisFactory->objDat[$lOff+$i*10] = 0;
		$thisFactory->objDat[$lOff+$i*10+1] = 0;
		$thisFactory->objDat[$lOff+$i*10+2] = 0;
		$thisFactory->objDat[$lOff+$i*10+3] = 0;
		$thisFactory->objDat[$lOff+$i*10+4] = 0;
		$thisFactory->objDat[$lOff+$i*10+5] = 0;
		$thisFactory->objDat[$lOff+$i*10+6] = 0;
		$thisFactory->objDat[$lOff+$i*10+7] = 0;
		$thisFactory->objDat[$lOff+$i*10+8] = 0;
		$thisFactory->objDat[$lOff+$i*10+9] = 0;
  }

}

echo 'Used items:';
print_r($usedItems);

// Move items from the company slot into the factory

// Move unused items from the factory into the slots

for ($i=1; $i<sizeof($businessLabor->slotData); $i+=10) {
	if ($businessLabor->slotData[$i] == 0) $openSlotSpots[] = $i;
}

for($i=0; $i<10; $i++) {
	if ($usedItems[$i] == 0 && $startFactoryLabor[$i*10] > 0) {
    echo 'Move item '.$i.' tot the slots';
		$val = $i*10;
		//print_R(array_slice($startFactoryLabor, $val, 10));
		$laborDat = pack('i*', $startFactoryLabor[$val], $startFactoryLabor[$val+1], $startFactoryLabor[$val+2], $startFactoryLabor[$val+3], $startFactoryLabor[$val+4], $startFactoryLabor[$val+5], $startFactoryLabor[$val+6], $startFactoryLabor[$val+7], $startFactoryLabor[$val+8], $startFactoryLabor[$val+9]);
		$loc = array_shift($openSlotSpots);
		if (is_null($loc)) $loc = 0;

		$laborCheck = unpack('i*', $laborDat);

		$businessLabor->addItem($slotFile, $laborDat, $loc);
	}
}

$thisProduct = loadProduct($thisFactory->get('currentProd'), $objFile, 400);
$productionRate = $thisFactory->setProdRate($postVals[3], $thisProduct, $laborEqFile);
$thisFactory->set('prodRate', $productionRate);
$thisFactory->saveAll($objFile);

echo '<script>factoryRate(headSection.rate, '.($productionRate/100).');
factoryLabor = [new laborItem({objID:0, pay:0, ability:0, laborType:0})';
$startVals = [];
for ($i=0; $i<10; $i++) {
	if ($thisFactory->objDat[$thisFactory->laborOffset + $i*10] == 0) {
		$useID = 0;
		$startVals[$i] = 0;
	} else {
		$startVals[$i] = $i+1;
		$useID = $i+1;
	}
	echo ', new laborItem({objID:"'.$useID.'", pay:'.$thisFactory->objDat[$thisFactory->laborOffset+5 + $i*10].', ability:'.$thisFactory->objDat[$thisFactory->laborOffset+8 + $i*10].', laborType:'.$thisFactory->objDat[$thisFactory->laborOffset+1 + $i*10].'})';
}

echo '];
showLabor('.$postVals[1].', factoryLabor);
	</script>';

fclose($laborEqFile);
fclose($objFile);
fclose($slotFile);

?>
