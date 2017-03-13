<?php

// move workers between a factory and company labor slots

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

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$laborEqFile = fopen($scnPath.'/laborEq.dat', 'rb');

// Load the business & factory
$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1100);

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

if ($postVals[3] > 99) {
  // move item from laborslot to factory

  // copy labor item data over to the factory
  echo 'Move company labor to factory';
  $fLaborOffset = $lOff+($postVals[2]-1)*10;
  $slotLocation = ($postVals[3] - 100)*10+1;
  $thisFactory->objDat[$fLaborOffset] = $businessLabor->slotData[$slotLocation];
  $thisFactory->objDat[$fLaborOffset+1] = $businessLabor->slotData[$slotLocation+1];
  $thisFactory->objDat[$fLaborOffset+2] = $businessLabor->slotData[$slotLocation+2];
  $thisFactory->objDat[$fLaborOffset+3] = $businessLabor->slotData[$slotLocation+3];
  $thisFactory->objDat[$fLaborOffset+4] = $businessLabor->slotData[$slotLocation+4];
  $thisFactory->objDat[$fLaborOffset+5] = intval($postVals[4]*100); // current Pay
  $thisFactory->objDat[$fLaborOffset+6] = $businessLabor->slotData[$slotLocation+6];
  $thisFactory->objDat[$fLaborOffset+7] = $businessLabor->slotData[$slotLocation+7];
  $thisFactory->objDat[$fLaborOffset+8] = $businessLabor->slotData[$slotLocation+8];
  $thisFactory->objDat[$fLaborOffset+9] = $businessLabor->slotData[$slotLocation+9];

  $businessLabor->addItem($slotFile, $emptyData, $slotLocation);
}
else if ($postVals[3] > 0) {
  // adjust existing labor item
  echo 'Adjust existing labor item';
  $fLaborOffset = $lOff+$postVals[2]*10;
  $thisFactory->objDat[$fLaborOffset+5] = intval($postVals[4]*100); // current Pay
}
else if ($postVals[3] == 0) {
  echo 'move factory labor to company slot';
  $fLaborOffset = $lOff+($postVals[2]-1)*10;
  $laborDat = pack('i*', $thisFactory->objDat[$fLaborOffset],
    $thisFactory->objDat[$fLaborOffset+1],
    $thisFactory->objDat[$fLaborOffset+2],
    $thisFactory->objDat[$fLaborOffset+3],
    $thisFactory->objDat[$fLaborOffset+4],
    $thisFactory->objDat[$fLaborOffset+5],
    $thisFactory->objDat[$fLaborOffset+6],
    $thisFactory->objDat[$fLaborOffset+7],
    $thisFactory->objDat[$fLaborOffset+8],
    $thisFactory->objDat[$fLaborOffset+9]
  );
  $loc = sizeof($businessLabor->slotData);
  for ($i=1; $i<sizeof($businessLabor->slotData); $i+=10) {
    if ($businessLabor->slotData[$i] == 0) {
      $loc = $i;
      break;
    }
  }
  $businessLabor->addItem($slotFile, $laborDat, $loc);
  $thisFactory->objDat[$fLaborOffset] = 0;
  $thisFactory->objDat[$fLaborOffset+1] = 0;
  $thisFactory->objDat[$fLaborOffset+2] = 0;
  $thisFactory->objDat[$fLaborOffset+3] = 0;
  $thisFactory->objDat[$fLaborOffset+4] = 0;
  $thisFactory->objDat[$fLaborOffset+5] = 0;
  $thisFactory->objDat[$fLaborOffset+6] = 0;
  $thisFactory->objDat[$fLaborOffset+7] = 0;
  $thisFactory->objDat[$fLaborOffset+8] = 0;
  $thisFactory->objDat[$fLaborOffset+9] = 0;

  }

// recalculate production at this factory
$thisProduct = loadProduct($thisFactory->get('currentProd'), $objFile, 400);
$productionRate = $thisFactory->setProdRate($postVals[3], $thisProduct, $laborEqFile);
$thisFactory->set('prodRate', $productionRate);

//$thisFactory->saveAll($objFile);
$startFactoryLabor = array_slice($thisFactory->objDat, $lOff-1, 100);
print_r($startFactoryLabor);

// Reload company labor
$companyLabor = [];
for ($i=1; $i<sizeof($businessLabor->slotData); $i+=10) {
	if ($businessLabor->slotData[$i]>0) $companyLabor = array_merge($companyLabor, array_slice($businessLabor->slotData, $i-1, 10));
}
//output revised company labor and factory labor
echo '<script>
loadCompanyLabor(['.implode(',', $companyLabor).']);
loadFactoryLabor(['.implode(',', array_slice($thisFactory->objDat, ($thisFactory->laborOffset-1), 100)).']);
showLabor('.$postVals[1].', factoryLabor);
</script>';

fclose($objFile);
fclose($slotFile);
fclose($laborEqFile);

?>
