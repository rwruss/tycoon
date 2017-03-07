<?php

require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$laborEqFile = fopen($scnPath.'/laborEq.dat', 'rb');

$thisFactory = loadObject($postVals[1], $objFile, 1000);

// Confrim that player can give this order
if ($thisFactory->get('owner') != $pGameID) {
	exit("error 5001-1");
}

$now = time();
// Confirm there is no task already started
if ($thisFactory->get('prodLength') + $thisFactory->get('prodStart') > $now) {
	exit("error 5001-2");
}

// Verify that the production item is valid for this factory
$optionCheck = false;
$optionList = $thisFactory->productionOptions();
echo 'look for '.$postVals[3].' in <br>';
print_r($optionList);
for ($i=0; $i<5; $i++) {
	if ($postVals[3] == $optionList[$i]) {
		$optionCheck = true;
		$prodNumber = $i;
		break;
	}
}

$thisProduct = loadProduct($postVals[3], $objFile, 400);
$productionRate = $thisFactory->setProdRate($postVals[3], $thisProduct, $laborEqFile);

if ($optionCheck) {
	echo 'Set factory production';
	// Update current production
	if ($thisFactory->get('currentProd') > 1)	$thisFactory->updateStocks();

	// Set new item production
	echo 'Set production of item '.$postVals[3].' to '.$productionRate;
	$thisFactory->save('currentProd', $postVals[3]);
	$thisFactory->save('prodRate', $productionRate);

	echo '<script>
	productMaterial = ['.implode(',', $thisProduct->reqMaterials).'];
	showProdRequirements(reqBox.materials, productMaterial);

	productLabor = ['.implode(',', $thisProduct->reqLabor).'];
	showRequiredLabor(businessDiv.laborSection.required, productLabor);

	factoryRate(headSection.rate, '.($productionRate/100).');
	</script>';
} else {
	echo 'Not able to set';
}


fclose($objFile);
fclose($slotFile);

?>
