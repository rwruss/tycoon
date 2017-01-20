<?php

// Process production speed up at a factory
session_start();
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$playerFile = fopen("../users/userDat.dat", "r+b");

// Load inventory of production boosts for this player
$thisUser = loadUser($_SESSION["playerId"], $playerFile);

if ($thisUser->get('boost'.$postVals[3]) < 1) {
	echo 'You don\'t have enough of this boost';
	exit();
}

// Load factory Data
$thisFactory = loadObject($postVals[2], $objFile, 1000);

$boostDurations = [60, 600, 1800, 3600];

// Verify taht there is ongoing production
$now = time();
echo $thisFactory->get('constructCompleteTime').' - '.$boostDurations[$postVals[3]];
if ($thisFactory->get('prodStart') + $thisFactory->get('prodLength') > $now) {
	$thisFactory->save('prodLength', $thisFactory->get('prodLength')-$boostDurations[$postVals[3]]);
	$thisUser->save('boost'.$postVals[3], $thisUser->get('boost'.$postVals[3])-1);

	$_SESSION['boosts'][$postVals[3]]--;
  
	echo '<script>
	fProduction.boostClock('.$boostDurations[$postVals[3]].');
	</script>';
} else {
  echo 'There is nothing production to boost here. End:'.$thisFactory->get('constructCompleteTime').', now:'.$now;
  exit();
}

fclose($playerFile);
fclose($objFile);

?>