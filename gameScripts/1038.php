<?php

// 1038 - PROCESS: Material Order Speed Up at Factory (from 1036)

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load inventory of production boosts for this player

// Load factory Data
$thisFactory = loadObject($postVals[2], $objFile, 1000);

$boostDurations = [60, 600, 1800, 3600];

/*
$this->attrList['prodLength'] = 15;
$this->attrList['prodStart'] = 16;
$this->attrList['prodQty'] = 17;
*/

// Verify that the material order is ongoing
$now = time();
echo $thisFactory->get('constructCompleteTime').' - '.$boostDurations[$postVals[3]];
if ($thisFactory->get('prodStart') + $thisFactory->get('prodLength') > $now) {
  $thisFactory->save('prodLength', $thisFactory->get('prodLength')-$boostDurations[$postVals[3]]);
  echo '<script>
  fProduction.boostClock('.$boostDurations[$postVals[3]].');
  </script>';
} else {
  echo 'There is nothing production to boost here. End:'.$thisFactory->get('constructCompleteTime').', now:'.$now;
  exit();
}

fclose($objFile);

?>