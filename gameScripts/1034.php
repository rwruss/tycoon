<?php

require_once('./objectClass.php');

// Load the itemcost for this item

// Open the player object
$playerFile = fopen("../users/userDat.dat", "r+b");
fseek($playerFile, 0, SEEK_END);
echo 'Filesize: '.ftell($playerFile).' Seek to '.($_SESSION["playerId"]*500);

fseek($playerFile, $_SESSION["playerId"]*500);

$playerDat = unpack('i*', fread($playerFile, 500));
$thisUser = new user($_SESSION["playerId"], $playerDat, $playerFile);

// Verify they have enough gold for this transaction
$playerGold = $thisUser->get('gold');
//$playerGold = 1000; // Override


// Calculate used gold and record new boost quantities
$goldCost = 0;
for ($i=0; $i<sizeof($postVals)-1; $i++) {
	$goldCost += $i*$postVals[1+$i];
}

if ($playerGold < $goldCost) {
	echo 'You do not have enough gold to make this purchase.  Have '.$playerGold.' Need '.$goldCost;
	exit();
}

echo 'PROCEED:Have '.$playerGold.' Need '.$goldCost;

for ($i=0; $i<sizeof($postVals)-1; $i++) {
	$thisUser->set('boost'.$i, $thisUser->get('boost'.$i)+$postVals[1+$i]);
	$_SESSION['boosts'][$i] = $thisUser->get('boost'.$i);
}

$thisUser->set('gold', $thisUser->get('gold') - $goldCost);
echo 'Set: '.$thisUser->get('gold').' - '.$goldCost;
$_SESSION['gold'] = $thisUser->get('gold');

$thisUser->saveAll($playerFile);

echo '<script>thisPlayer.gold = '.$thisUser->get('gold').'</script>';

fclose($playerFile);

?>
