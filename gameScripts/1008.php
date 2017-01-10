<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Verify that the player has enough money to build this factory
$thisBusiness = loadObject($pGameID, $objFile, 400);
if ($factoryCost > $thisBusiness->get('money')) {
	echo 'You do not have enough money to build this type of factory.  You need '.($factoryCost - $thisBusiness->get('money')).' to start construction.';
	exit();
}


// Create a new factory object
if (flock($objFile, LOCK_EX)) {
	// get new ID
	fseek($objFile, 0, SEEK_END);
	$newID = ftell($objFile)/100;

  fseek($objFile, $newID*$defaultBlockSize + 996);
  fwrite($objFile, pack('i', 0));

	echo 'Template type: '.$postVals[2].' for new object '.$newID;
	$newObjDat = array_fill(1, 250, 0);
  $newObj = new factory($newID, $newObjDat, $objFile);

	// Set default parameters for this factory
	$newObj->set('oType', 3);
	$newObj->set('owner', $pGameID);
	$newObj->set('lastUpdate', time());
	$newObj->set('subType', $postVals[2]);
	$newObj->saveAll($objFile);

	$testDat = unpack('i*', fread($slotFile, 40));
	echo 'Prelim slot check:';
	print_r($testDat);

	// Add unit to player's list of objects
  $thisBusiness = loadObject($pGameID, $objFile, 400);
  if ($thisBusiness->get('ownedObjects') == 0) {
    $thisBusiness->save('ownedObjects', newSlot($slotFile));
  }
	echo 'Load slot '.$thisBusiness->get('ownedObjects');
  $ownedObjects = new itemSlot($thisBusiness->get('ownedObjects'), $slotFile, 40);
  $ownedObjects->addItem($newID, $slotFile);

  /*
  this.type = options.objType || 'unknown',
		this.unitName = options.objName || 'unnamed',
		this.status = options.status || 0,
		this.objID = options.objID;
  */
  echo '<script>factoryArray.push(new factory({objType:factory, status:1, objID:'.$newID.', prod:0, rate:0}))</script>';

	print_r($ownedObjects->slotData);
	flock($objFile, LOCK_UN);
}



fclose($objFile);
fclose($slotFile);

?>
