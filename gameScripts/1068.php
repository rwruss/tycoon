<?php

/*
Receive a bid on a contract
PVS
1 - contract ID
2 - player ID
3 - proposed price
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$bidFile = fopen($gamePath.'/contractBids.cbf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

// Load the contract and confirm that it is open for bid
fseek($contractFile, $postVals[1]);
$contractDat = unpack('i*', fread($contractFile, 100));

if ($contractDat[8] != 1) exit('error 8601-1');

// Record the bid
$bidInfo = array_fill(0, 20, 0);
$bidInfo[0] = $contractDat[11];  // previous bid number
$bidInfo[1] = $postVals[2]; // bidding player
$bidInfo[2] = $postVals[3]*100; // bidding price
$bidInfo[3] = 0; // bid quality
$bidInfo[4] = 0; // bid pollution
$bidInfo[5] = 0; // bid rights
$bidInfo[6] = 0; // bid time
$bidInfo[7] = $postVals[1]; // contract ID
$bidInfo[8] = time(); // send time
$bidInfo[9] = 0; // Expire Time
$bidInfo[10] = $contractDat[3]; // Bid product
$bidInfo[11] = $contractDat[4]; // Bid quantity

$bidDat = '';
for ($i=0; $i<20; $i++) {
	$bidDat .= pack('i', $bidInfo[$i]);
}

print_R($bidInfo);

if (flock($bidFile, LOCK_EX)) {
	fseek($bidFile, 0, SEEK_END);
	$size = ftell($bidFile);

	$useLoc = max(1, ceil($size/80));
	fseek($bidFile, $useLoc);
	fwrite($bidFile, $bidDat);

	flock($bidFile, LOCK_UN);
}

// Record the bid in the list of bids for the contract
fseek($contractFile, $postVals[1]+40);
fwrite($contractFile, pack('i', $useLoc));

// Record the bid in the bidding player's list of bids
$biddingPlayer = loadObject($pGameID, $objFile, 400);
if ($biddingPlayer->get('openBids') >0) {
	$bidSlot = new itemSlot($biddingPlayer->get('openBids'), $slotFile, 40);
	$bidSlot->addItem($useLoc, $slotFile);
} else {
	exit('error 8601-1');
}

fclose($contractFile);
fclose($bidFile);
fclose($objFile);
fclose($slotFile);

?>
