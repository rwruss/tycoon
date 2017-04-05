<?php

// file a claim on a contract
/*
pvs:
1 - contract ID;
*/

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// load the contract dat
fseek($contractFile, $postVals[1]);
$contractInfo = unpack('i*', fread($contractFile, 100));

// update the contract status
$playerCheck = false;
if ($pGameID == $contractInfo[1]) {
	// the buyer is filing the claim
	$contractInfo[11] = 3;
	$playerCheck = true;
}
if ($pGameID == $contractInfo[21]) {
	$contractInfo[11] = 4;
	$playerCheck = true;
}

if (!$playerCheck) exit("You cannot file a suit on this contract");
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