<?php

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$transportFile = fopen($gamePath.'/transRights.trf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisPlayer = loadObject($pGameID, $objFile, 400);

// load areas where no routes are required
$openAreas = new itemSlot(0, $transportFile, 40, TRUE);

// Load areas where this player has rights
$playerAreas = new itemSlot($thisPlayer->get('transportAccess'), $transportFile, 40);

fclose($transportFile);
fclose($objFile);

echo '1,2,1500939886,4';

?>
