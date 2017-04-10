<?php

// Create a new contract solicitation at a factory
/*
PVS
1-factory ID
2-product ID
3 - junk
4-quantity
5-min quality
6-max pollution
7-max rights
*/

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// verify that the player is authorized to make this contract
$thisFactory = loadObject($postVals[1], $objFile, 1600);

if ($thisFactory->get('owner') != $pGameID) exit ('error 6601-1');
$now = time();
$contractInfo = [];
$contractInfo[1] = $pGameID; // owner/buyer
$contractInfo[2] = $now;
$contractInfo[3] = $postVals[2]; // item being purchased
$contractInfo[4] = $postVals[4]; // quantity
$contractInfo[5] = $postVals[5]; // pollutions
$contractInfo[6] = $postVals[6]; // Max Pollution
$contractInfo[7] = $postVals[7]; // max Rights
$contractInfo[8] = 1; // status
$contractInfo[9] = 0; // accepted price
$contractInfo[10] = $now + 8*3600;
$contractInfo[11] = 0;
$contractInfo[12] = $postVals[1]; // traget factory
$contractInfo[13] = 0;
$contractInfo[14] = 0;
$contractInfo[15] = 0;
$contractInfo[16] = 0;
$contractInfo[17] = 0;
$contractInfo[18] = 0;
$contractInfo[19] = 0;
$contractInfo[20] = 0;
$contractInfo[21] = 0;
$contractInfo[22] = 0;
$contractInfo[23] = 0;
$contractInfo[24] = 0;
$contractInfo[25] = 0;

$cfDat = '';
for ($i=1; $i<26; $i++) {
	$cfDat.= pack('i', $contractInfo[$i]);
}

// save the data for the new contract in the contract file
$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b');
if (flock($contractFile, LOCK_EX)) {
	fseek($contractFile, 0, SEEK_END);
	$cfSize = ftell($contractFile);
	$newLoc = max(100,ceil($cfSize/100)*100);

	fseek($contractFile, $newLoc);
	fwrite($contractFile, $cfDat);

	flock($contractFile, LOCK_UN);
}
fclose($contractFile);

// save the contract number in the factory file
for ($i=0; $i<5; $i++) {
	if ($thisFactory->objDat[$thisFactory->contractsOffset+$i] == 0) {
		$thisFactory->saveItem($thisFactory->contractsOffset+$i, $newLoc);
		break;
	}
}

// save the contract in the list of bidding contracts for this material
$contractListFile = fopen($gamePath.'/contractList.clf', 'r+b');
$productContracts = new itemSlot($postVals[2], $contractListFile, 40);
$productContracts->addItem($newLoc, $contractListFile);

// save the contract to the player's list of open contracts
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$thisPlayer = loadObject($pGameID, $objFile, 400);
if ($thisPlayer->get('contractList') == 0) {
	$newSlot = newSlot($slotFile, 40);
	$thisPlayer->save('contractList', $newSlot);
}
$contractList = new itemSlot($thisPlayer->get('contractList'), $slotFile, 40);
$contractList->addItem($newLoc, $slotFile);

echo 'contract #'.$newLoc.' created and added to slot '.$thisPlayer->get('contractList');

fclose($slotFile);
fclose($objFile);


?>
