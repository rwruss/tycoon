<?php

// 1038 - PROCESS: Material Order Speed Up at Factory (from 1036)
/*
PVS
1 - factory ID
2 - order spot
3 - boost duration
*/
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

// Verify that the material order is ongoing
$now = time();
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');
if ($thisFactory->objDat[$thisFactory->orderListStart + $postVals[2]] > 0) {
	//fseek($offerDatFile, $thisFactory->objDat[$thisFactory->orderListStart + $postVals[2]]);
	//$objDat = unpack('i*', fread($offerDatFile, 64));
	
	$thisOrder = loadOrder($thisFactory->objDat[$thisFactory->orderListStart + $postVals[2]], $offerDatFile);
	
	if ($thisOrder->objDat[13] > $now) {
		fseek($offerDatFile, $thisFactory->objDat[$thisFactory->orderListStart + $postVals[2]] + 48);
		fwrite($offerDatFile, $thisOrder->objDat[13]-$boostDurations[$postVals[3]]);
		
		$thisUser->save('boost'.$postVals[3], $thisUser->get('boost'.$postVals[3])-1);
		$_SESSION['boosts'][$postVals[3]]--;
	}
}
/*
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
*/
fclose($offerDatFile);
fclose($playerFile);
fclose($objFile);

?>