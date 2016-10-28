<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

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

	// Add unit to player's list of objects
  $thisBusiness = loadObject($pGameID, $objFile, 400);
  if ($thisBusiness->get('ownedObjects') > 0) {
    $thisBusiness->save('ownedObjects', newSlot($slotFile));
  }
  $ownedObjects = new itemSlot($thisBusiness->get('ownedObjects'), $slotFile, 40);
  $ownedObjects->addItem($newID);
  
  /*
  this.type = options.objType || 'unknown',
		this.unitName = options.objName || 'unnamed',
		this.status = options.status || 0,
		this.objID = options.objID;
  */
  echo '<script>new factory({type:factory, status:1; objID:'.$newID.', prod:0, rate:0})</script>';

	print_r($ownedObjects->slotData);
	flock($objFile, LOCK_UN);
}


fclose($objFile);
fclose($slotFile);

?>
