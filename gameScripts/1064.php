<?php

/* Show current contracts for a factory
PVs
1 - factory ID
*/

// Verify that the player can view this information

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisFactory = loadObject($postVals[1], $objFile, 1600);

if ($thisFactory->get('owner') != $pGameID) exit('error 4601-1');

$contractInfo = [];
$emptyContract = array_fill(1, 25, 0);
for ($i=0; $i<5; $i++) {
	$contractInfo[] = $i;
	if ($thisFactory->objDat[$thisFactory->contractOffset+$i] > 0) {
		fseek($contractFile, $thisFactory->objDat[$thisFactory->contractOffset+$i]);
		$contractDat = unpack('i*', fread($contractFile, 100));
		$contractInfo = array_merge($contractInfo, $contractDat);
		$contractInfo[] = $thisFactory->objDat[$thisFactory->contractOffset+$i];
	} else {
		$contractInfo = array_merge($contractInfo, $emptyContract);
		$contractInfo[] = 0;
	}
}

echo 'showContracts(['.implode(',', $contractInfo).'], thisDiv)';

fclose($objFile);
fclose($slotFile);
fclose($contractFile);

?>