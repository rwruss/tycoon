<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);

// Verify that the production item is valid for this factory
$optionCheck = false;
$optionList = $this->productionOptions();
for ($i=0; $i<5; $i++) {
	if ($postVals[2] == $optionList[$i]) {
		$optionCheck = true;
		$prodNumber = $i;
		break;
	}
}

if ($optionCheck) {
	// Update current production
	$thisObj->updateStocks();

	// Set new item production
	$thisObj->save('currentProd', $prodNumber);
}


fclose($objFile);
fclose($slotFile);

?>