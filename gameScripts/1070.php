<?php

/*
Receive an acceptance of a bid offer
PVS
1 - bid ID
*/
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$bidFile = fopen($gamePath.'/contractBids.cbf', 'rb');

// Load the bid
fseek($bidFile, $postVals[1]);
$bidInfo = unpack('i*', fread($bidFile, 80));

// Load the contract
fseek($contractFile, $bidInfo[8]);
$contractInfo = unpack('i*', fread($contractFile, 100));

// verify that the player controls the contract
if ($contractInfo[1] != $pGameID) exit ('error 0701-1');

// verify that there is not already an accepted bid
if ($contractInfo[8] != 1) exit('error 0701-3');

// record the bid dat in the contract
$contractInfo[8] = 2; // update status
$contractInfo[16] = $bidInfo[3];
$contractInfo[21] = $bidInfo[2];

$contractDat = '';
for ($i=1; $i<26; $i++) {
	$contractDat .= pack('i', $contractInfo[$i]);
}

// remove the contract from the list of open contracts for the product
$contractListFile = fopen($gamePath.'/contractList.clf', 'rb');
$contractSlot = new itemSlot($contractInfo[3], $contractListFile, 40);
$contractSlot->deleteByValue($postVals[1], $contractListFile);
fclose($contractListFile);

fseek($contractFile, $postVals[1]);
fwrite($contractFile, $contractDat);

fclose($contractFile);
fclose($bidFile);

?>
