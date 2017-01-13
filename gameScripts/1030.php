<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load inventory of production boosts for this player

// Load factory Data
$thisFactory = loadObject($postVals[2], $objFile, 1000);

$boostDurations = [0, 60, 600, 1800, 3600];
//1484288204
// Verify taht there is ongoing construction
$now = time();
echo $thisFactory->get('constructCompleteTime').' - '.$boostDurations[$postVals[3]];
if ($thisFactory->get('constructCompleteTime') <= $now) {
  $thisFactory->save('constructCompleteTime', $thisFactory->get('constructCompleteTime')-$boostDurations[$postVals[3]]);
} else {
  echo 'There is nothing to boost here.';
  exit();
}


fclose($objFile);

?>
