<?php

require_once('./objectClass.php');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load the itemcost for this item

// Open the player object

// Verify they have enough gold for this transaction
$playerGold = 1000;
$itemCost = $postVals[1];
if ($playerGold < $itemCost) {
	echo 'You do not have enough gold to make this purchase';
	exit();
}

// Open the player business for this game
$thisBusiness = loadObject($pGameID, $objFile, 400);

// Add the objects to the player profile
$desc = 'boost'.$postVals[1];
$thisBusiness->save($desc, $thisBusiness->get($desc)+1);
echo 'You now have '.$thisBusiness->get($desc);

fclose($objFile);

?>