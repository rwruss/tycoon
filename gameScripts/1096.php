<?php

/*
PVS:
1: factory ID
2: product ID
3: Quantity
4: Quality
5: New Price (in cents)
6: max pollution
7: max rights
*/

$factoryID = $postVals[1];
$contractID = $postVals[2];
$productID = $postVals[3];
$quantity = $postVals[4];
$quality = $postVals[5];
$price = $postVals[6];
$maxPollution = $postVals[7];
$maxRights = $postVals[8];

require_once('./objectClass.php');
require_once('./slotFunctions.php');

echo 'Change the project for factory '.$postVals[1].' to accepting bids';

$objFile = fopen($gamePath.'/objects.dat', 'rb'); //r+b
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb'); //r+b
$projectsFile = fopen($gamePath.'/projects.prj', 'rb'); //r+b

// verify that the player is authorized to make this contract
$thisFactory = loadObject($postVals[1], $objFile, 1600);

if ($thisFactory->get('owner') != $pGameID) exit ('error 6601-1');

echo 'This Factory has project '.$thisFactory->get('constStatus').' in progress';



$contractListFile = fopen($gamePath.'/contractList.clf', 'rb'); //r+b
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb'); //r+b

// create a new contract
$now = time();
$contractInfo = [];
$contractInfo[1] = $pGameID; // owner/buyer
$contractInfo[2] = $now;
$contractInfo[3] = $postVals[2]; // item being purchased
$contractInfo[4] = $postVals[3]; // quantity
$contractInfo[5] = $postVals[4]; // quality
$contractInfo[6] = $postVals[6]; // Max Pollution
$contractInfo[7] = $postVals[7]; // max Rights
$contractInfo[8] = 6; // status (6= open contract)
$contractInfo[9] = 0; // accepted price
$contractInfo[10] = 0; // completion time
$contractInfo[11] = 0; // bid link
$contractInfo[12] = $postVals[1]; // traget factory
$contractInfo[13] = 0;
$contractInfo[14] = 0;
$contractInfo[15] = 0;
$contractInfo[16] = $postVals[5];
$contractInfo[17] = 0;
$contractInfo[18] = 0;
$contractInfo[19] = 0;
$contractInfo[20] = 0;
$contractInfo[21] = 0;
$contractInfo[22] = 0;
$contractInfo[23] = 0;
$contractInfo[24] = 0;
$contractInfo[25] = 0;

$cfDat = '';
for ($i=1; $i<26; $i++) {
	$cfDat.= pack('i', $contractInfo[$i]);
}

// get contract ID for this factory
$factoryProject = loadProject($thisFactory->get('constStatus'), $projectsFile);
$contractID = $factoryProject->get('contractID');
echo '<p>Linked contract value is '.$contractID;

// save the data for the new contract in the contract file
if ($contractID > 0) {
	fseek($contractFile, $contractID);
	fwrite($contractFile, $cfDat);
} else {
	if (flock($contractFile, LOCK_EX)) {
		fseek($contractFile, 0, SEEK_END);
		$cfSize = ftell($contractFile);
		$newLoc = max(100,ceil($cfSize/100)*100);

		$contractID = $newLoc;
		echo 'save new contract '.$contractID.' to project';		
		$factoryProject->save('contractID', $contractID);

		fseek($contractFile, $newLoc);
		fwrite($contractFile, $cfDat);

		flock($contractFile, LOCK_UN);
	}
	// save the contract in the list of bidding contracts for this material
	echo 'Load  item slot '.$productID.' for product cotnracts';
	$productContracts = new itemSlot($productID, $contractListFile, 40);
	$productContracts->addItem($newLoc, $contractListFile);

	// save the contract to the player's list of open contracts
	$thisPlayer = loadObject($pGameID, $objFile, 400);
	if ($thisPlayer->get('contractList') == 0) {
		$newSlot = newSlot($slotFile, 40);
		$thisPlayer->save('contractList', $newSlot);
	}
	$contractList = new itemSlot($thisPlayer->get('contractList'), $slotFile, 40);
	$contractList->addItem($newLoc, $slotFile);

	echo 'contract #'.$newLoc.' created and added to slot '.$thisPlayer->get('contractList');
}

// verify that the project has a linked contract


fclose($contractFile);

// add to the contract list

fclose($contractListFile);



fclose($slotFile);
fclose($objFile);
fclose($projectsFile);
?>
