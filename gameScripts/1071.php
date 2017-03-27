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

$thisPlayer = loadObject($pGameID, $objFile, 400);
$contractList = new itemSlot($thisPlayer->get('contractList'), $slotFile, 40);

echo 'Read slot '.$thisPlayer->get('contractList');

// load each contract
$buyInfo = [];
$sellInfo = [];
for ($i=1; $i<sizeof($contractList->slotData); $i++) {
	fseek($contractFile, $contractList->slotData[$i]);
	$contractInfo = unpack('i*', fread($contractFile, 100));
	if ($contractInfo[1] == $pGameID) $buyInfo = array_merge($buyInfo, $contractInfo);
	else $sellInfo = array_merge($sellInfo, $contractInfo);
}

// output the info for each conctract
echo '<script>showContracts(['.implode(',',$buyInfo).'], thisDiv.buyContracts);
showContracts(['.implode(',',$sellInfo).'], thisDiv.sellContracts);</script>';

fclose($slotFile);
fclose($objFile);
fclose($contractFile);

?>