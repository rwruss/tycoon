<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Create a new factory object
if (flock($objFil, LOCK_EX)) {
	// get new ID
	fseek($objFile, 0, SEEK_END);
	$newID = ftell($objFile)/100;
	
	echo 'Template type: '.$postVals[2];
	$newObj = array_fill(1, 250, 0);
	
	//$thisObj = loadObject($postVals[2]*10, $objFile, 1000);

	print_r($thisObj->objDat);
	flock($objFile, LOCK_UN);
}


fclose($objFile);
fclose($slotFile);

?>
