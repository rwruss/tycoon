<?php

/*
PVs
2 = new factory type
3 = city location for factory
*/

$factoryType = $postVals[1];
$cityLoc = $postVals[2];

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load template data
fseek($objFile, $factoryType*$templateBlockSize);
$templateDat = unpack('i*', fread($objFile, $templateBlockSize));

// Verify that the player has enough money to build this factory
$factoryCost = $templateDat[8];
$thisBusiness = loadObject($pGameID, $objFile, 400);
if ($factoryCost > $thisBusiness->get('money')) {
	echo 'You do not have enough money to build this type of factory.  You need '.$factoryCost.' - '.$thisBusiness->get('money').' = '.($factoryCost - $thisBusiness->get('money')).' to start construction.';
	exit();
}

// Deduct the cost of the factory
$thisBusiness->save('money', $thisBusiness->get('money')-$factoryCost);

// Create a new factory object
if (flock($objFile, LOCK_EX)) {
	// get new ID
	fseek($objFile, 0, SEEK_END);
	$newID = ftell($objFile)/100;

  fseek($objFile, $newID*$defaultBlockSize + 1596);
  fwrite($objFile, pack('i', 0));

	echo 'Template type: '.$factoryType.' for new object '.$newID;
	$newObjDat = array_fill(1, 400, 0);
	$newObj = new factory($newID, $newObjDat, $objFile);

	// Set default parameters for this factory
	$now = time();
	$newObj->set('factoryLevel', 0);
	$newObj->set('factoryStatus', 0);
	$newObj->set('constructCompleteTime', $now+600);
	$newObj->set('upgradeInProgress', 1);
	$newObj->set('currentProd', $templateDat[11]);
	$newObj->set('oType', 3);
	$newObj->set('owner', $pGameID);
	$newObj->set('lastUpdate', $now);
	$newObj->set('subType', $factoryType);
	$newObj->set('region_3', $cityLoc);
	$newObj->set('region_2', 0);
	$newObj->set('region_1', 0);
	$newObj->saveAll($objFile);

	$testDat = unpack('i*', fread($slotFile, 40));
	echo 'Prelim slot check:';
	print_r($testDat);

	// Add unit to player's list of objects
  if ($thisBusiness->get('ownedObjects') == 0) {
    $thisBusiness->save('ownedObjects', newSlot($slotFile));
  }
	echo 'Load slot '.$thisBusiness->get('ownedObjects');
  $ownedObjects = new itemSlot($thisBusiness->get('ownedObjects'), $slotFile, 40);
  $ownedObjects->addItem($newID, $slotFile);

  //$newObj->overViewInfo()
  //echo '<script>playerFactories.push(new factory({subType:('.$factoryType.'-numProducts), objID:'.$newID.', prod:0, rate:0}))</script>';

	//print_r($ownedObjects->slotData);
	flock($objFile, LOCK_UN);
}



fclose($objFile);
fclose($slotFile);

echo '<script>
addFactory(['.implode(',', $newObj->overViewInfo()).']);
thisPlayer.money = '.$thisBusiness->get('money').'
</script>';

?>
