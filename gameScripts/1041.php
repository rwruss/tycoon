<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

// Load player-business data
$thisBusiness = loadObject($pGameID, $objFile, 400);

// confirm that player is in a team
if ($thisBusiness->get('teamID') < 1) {
  echo 'You aren\'t in a team yet.';
  exit();
}

// load team data
$thisTeam = loadTeam($thisBusiness->get('teamID'), $objFile);

fclose($objFile);
fclose($slotFile);

?>
