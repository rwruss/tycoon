<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load inventory of production boosts for this player

// Load factory Data
$now = time();
$thisFactory = loadObject($postVals[1], $objFile, 1000);
if ($thisFactory->get('constructCompleteTime') <= $now) {
  $thisFactory->set('constructCompleteTime', $now+3600);
  $thisFactory->set('upgradeInProgress', $thisFactory->get('factoryLevel')+1);

  $thisFactory->saveAll($thisFactory->linkFile);

  echo 'Updgrade from level '.$thisFactory->get('factoryLevel').' to level '.($thisFactory->get('factoryLevel')+1);
} else {
  echo 'There is already an update in progress';
}

fclose($objFile);

?>
