<?php

/*
load the list of contracts open for bid and output the information
PVS
1 - Product Type Requested
*/
require_once('./slotFunctions.php');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$contractListFile = fopen($gamePath.'/contracts.ctf', 'rb');

$sendDat = [];
$contractList = new itemSlot($postVals[1], $contractListFile, 40);
for ($i=0; $i<sizeof($contractList->slotData); $i++) {
	if ($contractList->slotData[$i] > 0) {
		fseek($contractFile, $contractList->slotData[$i]);
		$contractDat = unpack('i*', fread($contractFile, 40));
		$sendDat = array_merge($sendDat, $contractDat);
	}
}

echo implode(',', $sendDat);

fclose($contractFile);
fclose($contractListFile);

?>