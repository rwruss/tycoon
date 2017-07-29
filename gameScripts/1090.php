<?php

// Update a route

/*
PVS
0: Route ID
1-10: Stops
*/

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$thisPlayer = loadObject($pGameID, $objFile, 400);

// Verify that the player has access rights in all of the selected cities
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');

// load areas where no routes are required
$openAreas = new itemSlot(0, $transportFile, 40, TRUE);

// Load areas where this player has rights
$playerAreas = new itemSlot($thisPlayer->get('transportAccess'), $transportFile, 40);


fclose($transportFile);


?>