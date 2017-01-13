<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load inventory of production boosts for this player
$thisBusiness = loadObject($pGameID, $objFile, 400);
if ($thisBusiness->get('boost'+$postVals[3]) < 1) {
	echo 'You do not have any boosts of this type';
	exit();
}

// Load factory Data
$thisFactory = loadObject($postVals[2], $objFile, 1000);

$boostDurations = [60, 600, 1800, 3600];

// Verify taht there is ongoing construction
$now = time();
echo $thisFactory->get('constructCompleteTime').' - '.$boostDurations[$postVals[3]];
if ($thisFactory->get('constructCompleteTime') <= $now) {
  $thisFactory->save('constructCompleteTime', $thisFactory->get('constructCompleteTime')-$boostDurations[$postVals[3]]);
  
  echo '<script>
  clockBoost(target, '.$boostDurations[$postVals[3]].');
  </script>';
  
} else {
  echo 'There is nothing to boost here.';
  exit();
}


fclose($objFile);

?>
