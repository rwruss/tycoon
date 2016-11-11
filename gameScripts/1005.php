<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisObj = loadObject($postVals[1], $objFile, 400);

// Verify that the production item is valid for this factory
$optionCheck = false;
$optionList = $thisObj->productionOptions();
echo 'look for '.$postVals[3].' in <br>';
print_r($optionList);
for ($i=0; $i<5; $i++) {
	if ($postVals[3] == $optionList[$i]) {
		$optionCheck = true;
		$prodNumber = $i;
		break;
	}
}

if ($optionCheck) {
	echo 'Set factory production';
	// Update current production
	if ($thisObj->get('currentProd') > 1)	$thisObj->updateStocks();

	// Read product Data
	fseek($objFile, $postVals[3]*1000);
	$productInfo = unpack('i*', fread($objFile, 200));

	// Set new item production
	$thisObj->save('currentProd', $postVals[3]);
	$thisObj->save('currentRate', $productInfo[11]);
} else {
	echo 'Not able to set';
}


fclose($objFile);
fclose($slotFile);

?>
