<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisObj = loadObject($postVals[1], $objFile, 1000);

// confirm that the player owns this object
if ($thisObj->get('owner') == $pGameID) {
	include('../gameScripts/objects/obj_'.$thisObj->get('oType').'.php');
} else {
	echo 'You do not own this object';
}

fclose($objFile);
fclose($slotFile);

?>
