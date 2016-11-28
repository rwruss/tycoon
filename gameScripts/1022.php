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


// Load the business labor slot to get the relevant items
$openSlotSpots = $addList;
$slotsToClear = sizeof($openSlotSpots);
$businessLabor = new blockSlot($thisBusiness->get('laborSlot'), $slotFile, 40);

$dataFromSlot = [];
for ($i=0; $i<sizeof($addList); $i++) {
  $dataFromSlot = array_merge($dataFromSlot, array_slice($businessLabor->slotData, $addList[$i], 10));
}

for ($i=1; $i<sizeof($businessLabor->slotData); $i+=10) {
  if ($businessLabor->slotData[$i] == 0) $openSlotSpots[] = $i;
}

// Remove items from factory
$openFacSlots = [];
$dataFromFac = [];
for ($i=0; $i<10; $i++) {
  if ($keepList[$i+1] == 0) {

    $openFacSlots[] = $i;
    if ($thisFactory->objDat[$thisFactory->laborOffset+$i*10] > 0) {
      $base = $thisFactory->laborOffset+$i*10;
      echo 'Remove factory item #'.($i+1).' at loc '.$base;

      print_r(array_slice($thisFactory->objDat, $base, 10));
      $dataFromFac = array_merge($dataFromFac, array_slice($thisFactory->objDat, $base, 10));
      $thisFactory->objDat[$base] = 0;
      $thisFactory->objDat[$base+1] = 0;
      $thisFactory->objDat[$base+2] = 0;
      $thisFactory->objDat[$base+3] = 0;
      $thisFactory->objDat[$base+4] = 0;
      $thisFactory->objDat[$base+5] = 0;
      $thisFactory->objDat[$base+6] = 0;
      $thisFactory->objDat[$base+7] = 0;
      $thisFactory->objDat[$base+8] = 0;
      $thisFactory->objDat[$base+9] = 0;
    }
  }
}

// Add items from factory to business slot
echo 'Data to be removed from factory:';
print_r($dataFromFac);
$clearedSlots = 0;

for ($i=0; $i<sizeof($dataFromFac); $i+=10) {

  $useSlot = array_shift($openSlotSpots);
  $newDat = pack('i*', $dataFromFac[$i], $dataFromFac[$i+1], $dataFromFac[$i+2], $dataFromFac[$i+3], $dataFromFac[$i+4], $dataFromFac[$i+5], $dataFromFac[$i+6], $dataFromFac[$i+7], $dataFromFac[$i+8], $dataFromFac[$i+9]);
  if (!is_null($useSlot)) {
    echo 'Add to slot '.$useSlot;
    //$businessLabor->addItem($slotFile, $newDat, $useSlot);
  } else {
    echo 'Add to end of slot';
    //$businessLabor->addItem($slotFile, $newDat, 0);
  }
  $clearedSlots++;
}

echo 'Clear removed items from slot if '.$clearedSlots.' < '.$slotsToClear;
/*
$emptyDat = pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
while ($clearedSlots < $slotsToClear) {
  $useSlot = array_shift($openSlotSpots);
  $businessLabor->addItem($slotFile, $newDat, $useSlot);

  echo 'Cleared slot '.$useSlot;
  $clearedSlots++;
}
*/
// Add items from business slot to factory
for ($i=0; $i<sizeof($dataFromSlot); $i+=10) {

}

// Remove items from business slot

// Add or remove items from factory

//echo 'Final factory Slots:';
//print_R(array_slice($thisFactory->objDat, $thisFactory->laborOffset, 100));


fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
