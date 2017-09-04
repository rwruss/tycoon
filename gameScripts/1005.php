<?php

/*
PVS
1 - factory ID
2 - JUNK
3 - index spot for new item to be produced
*/

require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb'); //r+b
$objFile = fopen($gamePath.'/objects.dat', 'rb'); // r+b
//$laborEqFile = fopen($scnPath.'/laborEq.dat', 'rb'); // rb
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb'); //r+b

echo 'load factory';
$thisFactory = loadObject($postVals[1], $objFile, 1400);

// Confrim that player can give this order
if ($thisFactory->get('owner') != $pGameID) {
	exit('error 5001-1-'.$thisFactory->get('owner').'-'.$pGameID);
}

// determine how many production spots are available and get available production options
if ($thisFactory->get('groupType') > 0) {
	$productionSpots = $thisFactory->productionSpotQty;

	$pgfFile = fopen($gamePath.'/productGroups.pgf', 'rb');
	fseek($pgfFile, $thisObj->get('groupType')*8);
	$headDat = unpack('i*', fread($pgfFile, 8));
	fseek($pgfFile, $headDat[0]);
	$optionList = unpack('i*', fread($pgfFile, $headDat[1]));
	fclose($pgfFile);
} else {
	$productionSpots = 1;
	$optionList = $thisFactory->productionOptions();
}

$now = time();
// Confirm there is no task already started
if ($thisFactory->get('prodLength') + $thisFactory->get('prodStart') > $now) {
	exit("error 5001-2");
}

print_r($optionList);
$productIndex = $postVals[3] - 1;

if ($productIndex < 0) exit ('error 5001-3');


$thisProduct = loadProduct($optionList[$productIndex], $objFile, 400);
//$productionRate = $thisFactory->setProdRate($optionList[$productIndex], $thisProduct, $laborEqFile);

// update the current production prior to changing anything
//echo 'update factory stocks';
$thisFactory->updateStocks($offerDatFile);

$productSkillList = [];
$productMatList = [];
for ($i=0; $i<$productionSpots; $i++) {
	echo 'Set factory production';
	// Update current production
	//if ($thisFactory->objDat[$thisFactory->currentProductionOffset+$i] > 1)	$thisFactory->updateStocks($offerDatFile);

	// Set new item production
	$productIndex = $postVals[3+2*$i] - 1;
	$thisProduct = loadProduct($optionList[$productIndex], $objFile, 400);
	//$productionRate = $thisFactory->setProdRate($optionList[$productIndex], $thisProduct, $laborEqFile, $i);
	$productionRate = $thisFactory->setProdRate($i);

	echo 'Set production of item '.$optionList[$productIndex].' to '.$productionRate.'<p>';
	$thisFactory->objDat[$thisFactory->currentProductionOffset+$i] = $optionList[$productIndex];
	$thisFactory->objDat[$thisFactory->currentProductionRateOffset+$i] = $productionRate;
	//$thisFactory->save('currentProd', $optionList[$productIndex]);
	//$thisFactory->save('prodRate', $productionRate);
	$currentProductionList[$i] = $thisFactory->objDat[$thisFactory->currentProductionOffset+$i];
	$productSkillList = array_merge($productSkillList, $thisProduct->reqLabor);
	$productMatList = array_merge($productMatList, $thisProduct->reqMaterials);
}

$thisFactory->saveAll();

fclose($objFile);
fclose($slotFile);
fclose($offerDatFile);
//fclose($laborEqFile);

echo 'Current prod is '.implode(',', $currentProductionList).'<p>';

	echo '<script>
	productMaterial = ['.implode(',', $thisProduct->reqMaterials).'];
	selFactory.showProdRequirements(factoryDiv.reqBox.materials, productMaterial);

	selFactory.productLabor = ['.implode(',', $productSkillList).'];
	selFactory.showReqLabor(factoryDiv.laborSection.required);

	selFactory.setProdRate('.($productionRate/100).', factoryDiv.headSection.rate);
	</script>';

?>
