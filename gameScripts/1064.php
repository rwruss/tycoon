<?php

/* Show current contracts for a factory
PVs
1 - factory ID
*/

// Verify that the player can view this information
require_once('./objectClass.php');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisFactory = loadObject($postVals[1], $objFile, 1600);

if ($thisFactory->get('owner') != $pGameID) exit('error 4601-1');

$contractInfo = [];
$emptyContract = array_fill(1, 25, 0);
$emptyContract[12] = $postVals[1];
for ($i=0; $i<5; $i++) {
	$contractInfo[] = $i;
	if ($thisFactory->objDat[$thisFactory->contractsOffset+$i] > 0) {
		fseek($contractFile, $thisFactory->objDat[$thisFactory->contractsOffset+$i]);
		$contractDat = unpack('i*', fread($contractFile, 100));
		$contractInfo = array_merge($contractInfo, $contractDat);
		$contractInfo[] = $thisFactory->objDat[$thisFactory->contractOffset+$i];
	} else {
		$contractInfo = array_merge($contractInfo, $emptyContract);
		$contractInfo[] = 0;
	}
}

echo '<script>showContracts(['.implode(',', $contractInfo).'], thisDiv)</script>';

fclose($objFile);
fclose($slotFile);
fclose($contractFile);

?>
