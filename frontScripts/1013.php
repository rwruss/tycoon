<?php

/*
PV
1 => ID of new game
*/
require_once('./slotFunctions.php');
session_start();
$_SESSION['gameId'] = $postVals[1];

// record player as a user in this new game
$playerFile = fopen("../games/".$postVals[1]."/objects.dat", "r+b");
if (flock($playerFile, LOCK_EX)) {
  fseek($playerFile, 0, SEEK_END);

  $pGameID = max(1,ftell($playerFile)/100);
  echo 'Set player ID to '.$pGameID.'<br>';

  $playerDat = array_fill(1, 100, 0);
  $playerDat[4] = 1;
  $playerDat[14] = 10000;
  $playerDat[100] = -1;

  fseek($playerFile, $pGameID*100);
  fwrite($playerFile, packArray($playerDat));
  //fwrite($playerFile, pack('i', 1));
  //fseek($playerFile, $pGameID*100+399);
  //fwrite($playerFile, pack("C", 99));

  // Add this player to the file

  //fseek($playerFile, $pGameID*100);
  //fwrite($playerFile, pack('i*', 0, 0, 0, 1));
  flock($playerFile, LOCK_UN);
}
fclose($playerFile);

// Add player to list of players in this game
$pListFile = fopen("../games/".$postVals[1]."/players.Dat", "ab");
fwrite($pListFile, pack("i*", $_SESSION['playerId'], -$pGameID));
fclose($pListFile);

// Record this game in the player's list of open games
$uDatFile = fopen("../users/userDat.dat", "r+b");
fseek($uDatFile, $_SESSION['playerId']*500);
$uDat = fread($uDatFile, 500);
$gameSlot = unpack("N", substr($uDat, 8, 4));

if ($gameSlot[1] == 0) {
	echo "get new Slot";
	$uSlot = fopen("../users/userSlots.slt", "r+b");
	$newSlot = startASlot($uSlot, "../users/userSlots.slt");

	fseek($uDatFile, $_SESSION['playerId']*500+8);
	fwrite($uDatFile, pack("N", $newSlot));

	addDataToSlot("../users/userSlots.slt", $newSlot, pack("N", $postVals[1]), $uSlot);
	fclose($uSlot);
	}
else {
	echo "Add game to slot";
	$uSlot = fopen("../users/userSlots.slt", "r+b");

	addDataToSlot("../users/userSlots.slt", $gameSlot[1], pack("N", $postVals[1]), $uSlot);
	fclose($uSlot);
	}

echo "conPane - game ".$postVals[1]."

<script>window.location.replace('./play.php?gameID=".$postVals[1]."')</script>";

function packArray($data) {
  $str = pack('i', current($data));
  for ($i=1; $i<sizeof($data); $i++) {
    $str = $str.pack('i', next($data));
  }
  return $str;
}

?>
