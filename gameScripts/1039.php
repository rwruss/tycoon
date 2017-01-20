<?php

require_once('./objectClass.php');

// Load the item value for this item
$packageGold = [100, 1000, 10000];

// Open the player object
$playerFile = fopen("../users/userDat.dat", "r+b");
fseek($playerFile, $_SESSION["playerId"]*500);
$playerDat = unpack('i*', fread($playerFile, 500));
$thisUser = new user($_SESSION["playerId"], $playerDat, $playerFile);

$thisUser->save('gold', $thisUser->get('gold')+$packageGold[$postVals[1]]);

echo '<script>thisPlayer.gold = '.$thisUser->get('gold').'</script>';

fclose($playerFile);

?>
