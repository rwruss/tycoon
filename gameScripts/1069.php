<?php

/*
Load a list of bids received for a contract
PVS
1 - Proposal Number
*/

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$bidFile = fopen($gamePath.'/contractBids.cbf', 'rb');

// load the proposal
fseek($contractFile, $postVals[1]);
$proposalDat = unpack('i*', fread($contractFile, 100));

// verify that the player is the owner of the proposal
if ($proposalDat[1] != $pGameID) exit ('error 9601-1');

// load the bids
$bidInfo = [];
$nextBid = $proposalDat[11];
while($nextBid > 0) {
	fseek($bidFile, $nextBid);
	$bidDat = unpack('i*', fread($bidFile, 80));
	$bidInfo = array_merge($bidInfo, $bidDat);
	$bidInfo[] = $nextBid;
	$nextBid = $bidDat[1];
}

// output the bids
echo implode(',', $bidInfo);

fclose($contractFile);
fclose($bidFile);

?>
