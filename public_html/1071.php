<?php

/* 
Output a list of contracts that the player currently has open
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load the player data and the contract slot
$thisPlayer = loadObject($pGameID, $unitFile, 400);
$contractList = new itemSlot($thisPlayer->get('contractList'), $slotFile, 40);

// load each contract
$sendInfo = [];
for ($i=1; $i<sizeof($contractList->slotData); $i++) {
	fseek($contractFile, $contractList->slotData[$i]);
	$contractInfo = unpack('i*', fread($contractFile, 100));
	$sendInfo = array_merge($sendInfo, $contractInfo);
}

// output the info for each conctract
echo '<script>showContracts(['.implode(',',$sendInfo).'], thisDiv)</script>';

fclose($slotFile);
fclose($objFile);
fclose($contractFile);

?>