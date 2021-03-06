<?php

/*
Receive an acceptance of a bid offer
PVS
1 - bid ID
*/
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b');
$bidFile = fopen($gamePath.'/contractBids.cbf', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

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
$contractListFile = fopen($gamePath.'/contractList.clf', 'r+b');
$contractSlot = new itemSlot($contractInfo[3], $contractListFile, 40);
$contractSlot->deleteByValue($postVals[1], $contractListFile);
fclose($contractListFile);

// mark rejected bids with updated status (rejected)
$nextBid[1] = $contractInfo[11];
$rejected = pack('i', 2);
while ($nextBid[1] > 0) {
	fseek($bidFile, $nextBid[1]);
	$nextBid = unpack('i', fread($bidFile, 4));

	fseek($bidFile, $nextBid[1]+72);
	fwrite($bidFile, $rejected);
}

print_r($contractInfo);

// Add to list of open contracts for bidding player
$biddingPlayer = loadObject($bidInfo[2], $objFile, 400);
echo 'Add contract to slot '.$biddingPlayer->get('contractList');
$pBidList = new itemSlot($biddingPlayer->get('contractList'), $slotFile, 40);
$pBidList->addItem($bidInfo[8], $slotFile);

// Mark selected bid with accepted status
fseek($bidFile, $postVals[1]+72);
fwrite($bidFile, pack('i', 1));

fseek($contractFile, $bidInfo[8]);
fwrite($contractFile, $contractDat);

fclose($contractFile);
fclose($bidFile);
fclose($slotFile);
fclose($objFile);

?>
