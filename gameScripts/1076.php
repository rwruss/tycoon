<?php

/*
Cancel a contract that hasn't been executed
PVS
1 - contract #
*/

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load the contract
fseek($contractFile, $postVals[1]);
$contractInfo = unpack('i*', fread($contractFile, 100));

// validate player
if ($contractInfo[1] != $pGameID) exit('You can\'t cancel this contract');

// Load the factory
echo 'Load fac '.$contractInfo[12];
$thisFactory = loadObject($contractInfo[12], $objFile, 1600);

// Delete from the contract list at the factory
for ($i=0; $i<5; $i++) {
	if ($thisFactory->objDat[$thisFactory->contractsOffset+$i] == $postVals[1]) {
		$thisFactory->saveItem($thisFactory->contractsOffset+$i, 0);
		break;
	}
}

// Delete from the contract bid List
$contractListFile = fopen($gamePath.'/contractList.clf', 'rb');
$productContracts = new itemSlot($postVals[2], $contractListFile, 40);
$productContracts->deleteByValue($postVals[1], $contractListFile);

// change the contract status
fseek($contractFile, $postVals[1]+28);
fwrite($contractFile, pack('i', 5));

fclose($contractFile);
fclose($objFile);
fclose($contractListFile);

?>
