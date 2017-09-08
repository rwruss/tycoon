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

if ($thisFactory->get('groupType') > 1) {
	$productionSpots = $thisFactory->productionSpotQty;

	$pgfFile = fopen($gamePath.'/productGroups.pgf', 'rb');
	fseek($pgfFile, $thisFactory->get('groupType')*8);
	$headDat = unpack('i*', fread($pgfFile, 8));
	print_r($headDat);
	fseek($pgfFile, $headDat[1]);
	$optionList = unpack('i*', fread($pgfFile, $headDat[2]));
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

echo '<p>Option list:';
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
$currentProductionList = [];
$productionSpots = 5;
for ($i=0; $i<$productionSpots; $i++) {
	$productIndex = $postVals[2+$i];
	echo '<p>Set factory production item '.$i.' ('.$productIndex.')';
	// Update current production
	//if ($thisFactory->objDat[$thisFactory->currentProductionOffset+$i] > 1)	$thisFactory->updateStocks($offerDatFile);

	// Set new item production

	if (!in_array($productIndex, $optionList)) {echo "NOT AVAILABLE";}
	else if ($productIndex > 0) {
		$thisProduct = loadProduct($productIndex, $objFile, 400);
		//$productionRate = $thisFactory->setProdRate($optionList[$productIndex], $thisProduct, $laborEqFile, $i);
		$productionRate = $thisFactory->setProdRate($i);

		echo 'Set production of item '.$productIndex.' to '.$productionRate[0].'<p>';
		// set production items and production rates
		$thisFactory->objDat[$thisFactory->currentProductionOffset+$i] = $productIndex;
		$thisFactory->objDat[$thisFactory->currentProductionRateOffset+$i] = $productionRate[0];

		// set quality rates for each item
		$thisFactory->productionQuality[$i+1] = $productionRate[1];

		$currentProductionList[$i] = $thisFactory->objDat[$thisFactory->currentProductionOffset+$i];
		$productSkillList = array_merge($productSkillList, $thisProduct->reqLabor);
		$productMatList = array_merge($productMatList, $thisProduct->reqMaterials);
	} else {
		// set production items and production rates
		$thisFactory->objDat[$thisFactory->currentProductionOffset+$i] = 0;
		$thisFactory->objDat[$thisFactory->currentProductionRateOffset+$i] = 0;

		// set quality rates for each item
		$thisFactory->productionQuality[$i+1] = $productionRate[1];
	}
}

//$thisFactory->saveAll();
$thisFactory->saveProductionRates();

fclose($objFile);
fclose($slotFile);
fclose($offerDatFile);
//fclose($laborEqFile);

echo 'Current prod is '.implode(',', $currentProductionList).'<p>';

	echo '<script>
	selFactory.productMaterial = ['.implode(',', $thisProduct->reqMaterials).'];
	selFactory.showProdRequirements(factoryDiv.reqBox.materials);

	selFactory.productLabor = ['.implode(',', $productSkillList).'];
	selFactory.showReqLabor(factoryDiv.laborSection.required);

	selFactory.setProdRate('.($productionRate[0]/100).', factoryDiv.headSection.rate);
	</script>';

?>
