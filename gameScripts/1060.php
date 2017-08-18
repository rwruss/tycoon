<?php

// load labor information
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'rb');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'rb');

$laborList = [];
$laborSlot = new itemSlot($postVals[1], $laborSlotFile, 40);
for ($i=1; $i<=sizeof($laborSlot->slotData); $i++) {
  if ($laborSlot->slotData[$i]>0) {
    fseek($laborPoolFile, $laborSlot->slotData[$i]);
    $thisLabor = new labor(fread($laborPoolFile, 40)));

  }
}

//echo implode(',', $laborList);


fclose($laborPoolFile);
fclose($laborSlotFile);

?>
