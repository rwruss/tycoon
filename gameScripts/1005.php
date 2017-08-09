<?php

/*
PVS
1 - factory ID
2 - JUNK
3 - index spot for new item to be produced
*/

require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b'); //r+b
$objFile = fopen($gamePath.'/objects.dat', 'r+b'); // r+b
$laborEqFile = fopen($scnPath.'/laborEq.dat', 'rb'); // rb
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b'); //r+b

$thisFactory = loadObject($postVals[1], $objFile, 1000);

// Confrim that player can give this order
if ($thisFactory->get('owner') != $pGameID) {
	exit('error 5001-1-'.$thisFactory->get('owner').'-'.$pGameID);
}

$now = time();
// Confirm there is no task already started
if ($thisFactory->get('prodLength') + $thisFactory->get('prodStart') > $now) {
	exit("error 5001-2");
}

$optionList = $thisFactory->productionOptions();
$optionCheck = true;
$productIndex = $postVals[3] - 1;

if ($productIndex < 0) exit ('error 5001-1');
// Verify that the production item is valid for this factory

/*
$optionCheck = false;
echo 'look for '.$postVals[3].' in <br>';
print_r($optionList);
for ($i=0; $i<5; $i++) {
	if ($postVals[3] == $optionList[$i]) {
		$optionCheck = true;
		$prodNumber = $i;
		break;
	}
}
*/
$thisProduct = loadProduct($optionList[$productIndex], $objFile, 400);
$productionRate = $thisFactory->setProdRate($optionList[$productIndex], $thisProduct, $laborEqFile);

if ($optionCheck) {
	echo 'Set factory production';
	// Update current production
	if ($thisFactory->get('currentProd') > 1)	$thisFactory->updateStocks($offerDatFile);

	// Set new item production
	echo 'Set production of item '.$optionList[$productIndex].' to '.$productionRate;
	$thisFactory->save('currentProd', $optionList[$productIndex]);
	$thisFactory->save('prodRate', $productionRate);

	echo '<script>
	productMaterial = ['.implode(',', $thisProduct->reqMaterials).'];
	selFactory.showProdRequirements(factoryDiv.reqBox.materials, productMaterial);

	selFactory.productLabor = ['.implode(',', $thisProduct->reqLabor).'];
	//showRequiredLabor(businessDiv.laborSection.required, productLabor);
	selFactory.showReqLabor(factoryDiv.laborSection.required);

	//factoryRate(headSection.rate, '.($productionRate/100).');
	selFactory.setProdRate('.($productionRate/100).', factoryDiv.headSection.rate);
	</script>';
} else {
	echo 'Not able to set';
}


fclose($objFile);
fclose($slotFile);
fclose($offerDatFile);
fclose($laborEqFile);

?>
