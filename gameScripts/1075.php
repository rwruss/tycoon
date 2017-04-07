<?php

// add legal support for a claim
/*
pvs:
1 - contract ID;
*/

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// load the contract dat
fseek($contractFile, $postVals[1]);
$contractInfo = unpack('i*', fread($contractFile, 100));

// verify contract status and time
if ($contractInfo[8] != 3 || $contractInfo[8] != 4) exit('Contract is not in court');
if ($contractInfo[15] + 86400 < time()) exit('Case has already been completed');

// validate player and update the contract legal support
$playerCheck = false;
if ($pGameID == $contractInfo[1]) {
	// the buyer is adding legal support
	$contractInfo[24] += 1;
	$playerCheck = true;
}
if ($pGameID == $contractInfo[21]) {
	// the seller is adding legal support
	$contractInfo[25] += 1;
	$playerCheck = true;
}

// verify that the player is valid
if (!$playerCheck) exit("You cannot add legal services to this contract");
// set the claim time to now
$contractInfo[24] = time();

$contractDat = '';
for ($i=1; $i<26; $i++) {
	$contractDat .= pack('i', $contractInfo[$i]);
}

fseek($contractFile, $postVals[1]);
fwrite($contractFile, $contractDat);

fclose($contractFile);
fclose($objFile);

?>