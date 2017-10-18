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
//$contractID = $postVals[2]; // always zero
$productID = $postVals[3];
$quantity = $postVals[4];
$quality = $postVals[5];
$price = $postVals[6]; // 0 if a closed contract
$maxPollution = $postVals[7];
$maxRights = $postVals[8];
$contractType = $postVals[9]; // 1 = open contract, 2 = bid contract

require_once('./objectClass.php');
require_once('./slotFunctions.php');

echo 'Change the project for factory '.$postVals[1].' to accepting bids';

$objFile = fopen($gamePath.'/objects.dat', 'r+b'); //r+b
$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b'); //r+b
$projectsFile = fopen($gamePath.'/projects.prj', 'r+b'); //r+b

// verify that the player is authorized to make this contract
$thisFactory = loadObject($postVals[1], $objFile, 1600);
if ($thisFactory->get('owner') != $pGameID) exit ('error 6901-1');

// Verify that the factory has a project in progress and it is not already linked to a contract
echo 'This Factory has project '.$thisFactory->get('constStatus').' in progress';
$factoryProject = loadProject($thisFactory->get('constStatus'), $projectsFile);
if ($factoryProject->get('contractID') > 0) exit ('error 6901-2');
$contractID = $factoryProject->get('contractID');

$contractListFile = fopen($gamePath.'/contractList.clf', 'r+b'); //r+b
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b'); //r+b

// create a new contract
$typeMatrix = [6, 1];

$now = time();
$contractInfo[1] = $pGameID; // owner/buyer
$contractInfo[2] = $now;
$contractInfo[3] = $productID; // item being purchased
$contractInfo[4] = $quantity; // quantity
$contractInfo[5] = $quality; // quality
$contractInfo[6] = $maxPollution; // Max Pollution
$contractInfo[7] = $maxRights; // max Rights
$contractInfo[8] = 0; // status (6= open contract)
$contractInfo[9] = 0; // accepted price
$contractInfo[10] = 0; // completion time
$contractInfo[11] = 0; // bid link
$contractInfo[12] = $factoryID; // traget factory
$contractInfo[13] = 0;
$contractInfo[14] = 0;
$contractInfo[15] = 0;
$contractInfo[16] = $price;
$contractInfo[17] = 0;
$contractInfo[18] = 0;
$contractInfo[19] = 0;
$contractInfo[20] = 0;
$contractInfo[21] = 0;
$contractInfo[22] = 0;
$contractInfo[23] = 0;
$contractInfo[24] = 0;
$contractInfo[25] = 0;

if ($contractType == 1) {
	$contractInfo[8] = 6; // status (6= open contract)
} else $contractInfo[8] = 1;

$cfDat = '';
for ($i=1; $i<26; $i++) {
	$cfDat.= pack('i', $contractInfo[$i]);
}

// get contract ID for this factory

echo '<p>Linked contract value is '.$contractID;

// save the data for the new contract in the contract file
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


// verify that the project has a linked contract


fclose($contractFile);

// add to the contract list

fclose($contractListFile);



fclose($slotFile);
fclose($objFile);
fclose($projectsFile);
?>
