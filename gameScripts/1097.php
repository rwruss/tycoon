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
$newPrice = $postVals[4];
$maxPol = $postVals[5];
$maxRights = $postVals[6];

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb'); //r+b
fseek($contractFile, $contractID);
$contractInfo = unpack('i*', fread($contractFile, 100));

$contractInfo[4] = $newQty; // quantity
$contractInfo[5] = $newQual; // quality
$contractInfo[6] = $maxPol; // Max Pollution
$contractInfo[7] = $maxRights; // max Rights
$contractInfo[16] = $newPrice; // accepted price

$cfDat = '';
for ($i=1; $i<26; $i++) {
	$cfDat.= pack('i', $contractInfo[$i]);
}

fseek($contractFile, $contractID);
fwrite($contractFile, $cfDat);
fclose($contractFile);

//$contractInfo[0] = 0;
array_unshift($contractInfo, 1,0);
//print_r($contractInfo);
echo implode('|', $contractInfo);

?>
