<?php

require_once('./objectClass.php');

// Load the itemcost for this item

// Open the player object
$playerFile = fopen("../users/userDat.dat", "r+b");
fseek($playerFile, $pGameId*500);
$playerDat = unpack('i*', fread($playerFile, 500));
$thisUser = new user($pGameID, $playerDat, $playerFile);

// Verify they have enough gold for this transaction
$playerGold = $thisUser->get('gold');
$playerGold = 1000; // Override
$itemCost = $postVals[1];
if ($playerGold < $itemCost) {
	echo 'You do not have enough gold to make this purchase';
	exit();
}

// Calculate used gold and record new boost quantities
$goldCost = 0;
session_start();
for ($i=0; $i<20; $i++) {
	$thisUser->set('boost'.$i, $thisUser->get('boost'.$i)+$postVals[1+$i]);
	$_SESSION['boosts'][$i] = $thisUser->get('boost'.$i);
	$goldCost += $i*$postVals[1+$i];
}

$thisUser->set($thisUser->get('gold'), $thisUser->get('gold') - $goldCost);
$_SESSION['gold'] = $thisUser->get('gold');

$thisUser->saveAll($playerFile);

echo '<script>thisPlayer.gold = '.$thisUser->get('gold').'</script>';

fclose($playerFile);

?>
