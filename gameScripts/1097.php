<?php

/*
1097.php
Adjsut the settings for an open contract

PVS; 
1 - Contract ID
2 - quantity
3 - quality
4 - max pollution
5 - max rights
*/

$contractID = $postVals[1];
$newQty = $postVals[2];
$newQual = $postVals[3];
$maxPol = $postVals[4];
$maxRights = $postVals[5];*/

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb'); //r+b
fseek($contractFile, $contractID);
$contractInfo = unpack('i*', fread($contractFile, 100));

$contractInfo[4] = $quantity; // quantity
$contractInfo[5] = $quality; // quality
$contractInfo[6] = $maxPollution; // Max Pollution
$contractInfo[7] = $maxRights; // max Rights
$contractInfo[9] = 0; // accepted price

$cfDat = '';
for ($i=1; $i<26; $i++) {
	$cfDat.= pack('i', $contractInfo[$i]);
}

fseek($contractFile, $contractID);
fwrite($contractFile, $cfDat);
fclose($contractFile);

?>