<?php

/* Show current contracts for a factory
PVs
1 - factory ID
*/

// Verify that the player can view this information
require_once('./objectClass.php');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisFactory = loadObject($postVals[1], $objFile, 1600);

if ($thisFactory->get('owner') != $pGameID) exit('error 4601-1');

$contractInfo = [];
$emptyContract = array_fill(1, 25, 0);
$emptyContract[12] = $postVals[1];

$emptyStr = '';
for ($i=1; $i<26; $i++) {
	$emptyStr .= pack('i', $emptyContract[$i]);
}
$emptyStr .= pack('i', 0);
$contractStr = '';
for ($i=0; $i<5; $i++) {
	$contractInfo[] = $i;
	$contractStr .= pack('i', $i);
	if ($thisFactory->objDat[$thisFactory->contractsOffset+$i] > 0) {
		//echo 'Load contract '.$thisFactory->objDat[$thisFactory->contractsOffset+$i];
		fseek($contractFile, $thisFactory->objDat[$thisFactory->contractsOffset+$i]);
		/*
		$contractDat = unpack('i*', fread($contractFile, 100));
		$contractInfo = array_merge($contractInfo, $contractDat);
		$contractInfo[] = $thisFactory->objDat[$thisFactory->contractsOffset+$i];*/
		$contractStr .= fread($contractFile, 100).pack('i', $thisFactory->objDat[$thisFactory->contractsOffset+$i]);

	} else {
		$contractInfo = array_merge($contractInfo, $emptyContract);
		$contractInfo[] = 0;

		$contractStr .= $emptyStr;
	}
}

//echo '<script>showContracts(['.implode(',', $contractInfo).'], thisDiv)</script>';
echo $contractStr;

fclose($objFile);
fclose($contractFile);

?>
