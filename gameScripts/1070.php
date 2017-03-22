<?php

/*
Receive an acceptance of a bid offer
PVS
1 - contractID
2 - bid ID
*/
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b');
$bidFile = fopen($gamePath.'/contractBids.cbf', 'rb');

// verify that the player controls the contract
fseek($contractFile, $postVals[1]);
$contractInfo = unpack('i*', fread($contractFile, 100));
if ($contractInfo[1] != $pGameID) exit ('error 0701-1');

// verify that there is not already an accepted bid
if ($contractInfo[8] != 1) exit('error 0701-3');

// verify that the bid is for this contract
fseek($bidFile, $postVals[2]);
$bidInfo = unpack('i*', fread($bidFile, 80));

if ($bidInfo[8] != $postVals[1]) exit ('error 0701-2');

// record the bid dat in the contract
$contractInfo[8] = 2; // update status
$contractInfo[16] = $bidInfo[3];
$contractInfo[21] = $bidInfo[2];

$contractDat = '';
for ($i=1; $i<26; $i++) {
	$contractDat .= pack('i', $contractInfo[$i]);
}

// remove the contract from the list of open contracts for the product
$contractListFile = fopen($gamePath.'/contracts.ctf', 'rb');
$contractSlot = new itemSlot($contractInfo[3], $contractListFile, 40);
$contractSlot->deleteByValue($postVals[1], $contractListFile);
fclose($contractListFile);

fseek($contractFile, $postVals[1]);
fwrite($contractFile, $contractDat);

fclose($contractFile);
fclose($bidFile);

?>