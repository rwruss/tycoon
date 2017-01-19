<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load inventory of production boosts for this player
if ($_SESSION['boosts'][$postVals[3]] < 1) {
	echo 'You don\'t have enough of this boost';
	exit();
}

// Load factory Data
$thisFactory = loadObject($postVals[2], $objFile, 1000);

$boostDurations = [60, 600, 1800, 3600];
//1484288204
// Verify taht there is ongoing construction
$now = time();
echo $thisFactory->get('constructCompleteTime').' - '.$boostDurations[$postVals[3]];
if ($thisFactory->get('constructCompleteTime') > $now) {
  $thisFactory->save('constructCompleteTime', $thisFactory->get('constructCompleteTime')-$boostDurations[$postVals[3]]);
  echo '<script>
  thisUpgrade.boostClock('.$boostDurations[$postVals[3]].');
  </script>';
} else {
  echo 'There is nothing to boost here. End:'.$thisFactory->get('constructCompleteTime').', now:'.$now;
  exit();
}

fclose($objFile);

?>
