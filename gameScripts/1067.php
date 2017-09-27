<?php

/*
load the list of contracts open for bid and output the information
PVS
1 - Product Type Requested
*/
require_once('./slotFunctions.php');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$contractListFile = fopen($gamePath.'/contractList.clf', 'rb');

//$sendDat = [];
$contractList = new itemSlot($postVals[1], $contractListFile, 40);
$contractStr ='';
for ($i=1; $i<sizeof($contractList->slotData); $i++) {
	if ($contractList->slotData[$i] > 0) {
		fseek($contractFile, $contractList->slotData[$i]);
		$contractRead = fread($contractFile, 100); // contract data
		$contractDat = unpack('i*', $contractRead);
		/*
		$sendDat[] = 0;
		$sendDat = array_merge($sendDat, $contractDat);
		$sendDat[] = $contractList->slotData[$i];*/

		// Pad front with zero and add item number at end
		$contractStr .= pack('i', 0).$contractRead.pack('i', $contractList->slotData[$i]);
	}
}

//echo '<script>showContracts(['.implode(',', $sendDat).'], contractBids.results)</script>';
echo $contractStr;

fclose($contractFile);
fclose($contractListFile);

?>
