<?php

/*
Post Vals:
1 -> scenario ID
2 -> Player Side/Faction
3 -> Max number of players
*/

echo "conPaneProcess Game Creation";
include("./slotFunctions.php");
session_start();
// Get new game ID
$numGames = filesize("../games/gameList.lst")/4;
$newGameId = $numGames+1;

$gameListFile = fopen("../games/gameList.lst", "ab");
fwrite($gameListFile, pack("N", $newGameId));

echo "Create game ".$newGameId;
mkdir("../games/".$newGameId);

$openGamesFile = fopen("../games/openGames.dat", "ab");
fwrite($openGamesFile, pack("N*", $newGameId, time()));
fclose($openGamesFile);


// Add game to players list of games
$uDatFile = fopen("../users/userDat.dat", "r+b");
fseek($uDatFile, $_SESSION['playerId']*500);
$uDat = fread($uDatFile, 500);
$gameSlot = unpack("N", substr($uDat, 8, 4));

// Copy over game files from scenario folder
if ($handle = opendir('../scenarios/'.$postVals[1]))
	{
    echo "Entries:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle)))
		{
        echo "$entry<br>";
		copy('../scenarios/'.$postVals[1].'/'.$entry, '../games/'.$newGameId.'/'.$entry);
		}
	closedir($handle);
	}
// Add basic player info and unstarted status
$playerFile = fopen("../games/".$newGameId."/objects.dat", "r+b");
fseek($playerFile, 0, SEEK_END);

$pGameID = max(1,ftell($playerFile)/100);
echo 'Set player ID to '.$pGameID.'<br>';

fseek($playerFile, $pGameID*100+12);
fwrite($playerFile, pack('i', 1));
fseek($playerFile, $pGameID*100+399);
fwrite($playerFile, pack("C", 99));

// Add this player to the file

//fseek($playerFile, $pGameID*100);
//fwrite($playerFile, pack('i*', 0, 0, 0, 1));
fclose($playerFile);

// Prep game slot file
$gameSlotFile = fopen("../games/".$newGameId."/gameSlots.slt", "wb");
fseek($gameSlotFile, 39);
fwrite($gameSlotFile, pack("C", 0));
fseek($gameSlotFile, 0);

//Create list of players in game

$newFile = fopen("../games/".$newGameId."/players.Dat", "wb");
fwrite($newFile, pack("i*", $_SESSION['playerId'], -$pGameID));


fclose($newFile);

// Update parameters file
$paramFile = fopen("../games/".$newGameId."/params.ini", "r+b");
fwrite($paramFile, pack("N", time()));
fseek($paramFile, 199);
fwrite($paramFile, pack("C", 0));
fclose($paramFile);

// add Game ID to player game list
if ($gameSlot[1] == 0) {
	echo "get new Slot";
	$uSlot = fopen("../users/userSlots.slt", "r+b");
	$newSlot = startASlot($uSlot, "../users/userSlots.slt");

	fseek($uDatFile, $_SESSION['playerId']*500+8);
	fwrite($uDatFile, pack("N", $newSlot));

	addDataToSlot("../users/userSlots.slt", $newSlot, pack("N", $newGameId), $uSlot);
	fclose($uSlot);
	}
else {
	echo "Add game to slot";
	$uSlot = fopen("../users/userSlots.slt", "r+b");

	addDataToSlot("../users/userSlots.slt", $gameSlot[1], pack("N", $newGameId), $uSlot);
	fclose($uSlot);
	}
fclose($gameSlotFile);
fclose($uDatFile);

?>
