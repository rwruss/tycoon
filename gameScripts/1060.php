<?php

// load labor information
require_once('./slotFunctions.php');
require_once('./objectClass.php');

//$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
//$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'rb');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'rb');
//$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

$laborList = [];
$laborSlot = new itemSlot($postVals[1], $laborSlotFile, 40);
//print_R($laborSlot->slotData);
for ($i=1; $i<=sizeof($laborSlot->slotData); $i++) {
  if ($laborSlot->slotData[$i]>0) {
    fseek($laborPoolFile, $laborSlot->slotData[$i]);
    $laborDat = unpack('i*', fread($laborPoolFile, 40));
    //print_R($laborDat);
    $laborList = array_merge($laborList, $laborDat);
  }
}

echo implode(',', $laborList);
//echo '13, 2, 3, 4, 5, 6, 7, 8, 9, 0';

fclose($laborPoolFile);
fclose($laborSlotFile);

?>
