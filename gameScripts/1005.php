<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$laborEqFile = fopen($gamePat.'/laborEqFile', 'rb');

$thisFactory = loadObject($postVals[1], $objFile, 400);

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

if ($optionCheck) {
	echo 'Set factory production';
	// Update current production
	if ($thisFactory->get('currentProd') > 1)	$thisFactory->updateStocks();

	// Read product Data
	fseek($objFile, $postVals[3]*1000);
	$productInfo = unpack('i*', fread($objFile, 200));
	$newProduct = loadProduct($postVals[3], $objFile, 400);
	//print_r($productInfo);
	
	// Review labor affects
	$neededLabor = [];
	$productionRate = 0;
	$productionItems = 0;

	// Check first 7 labor types at the factory
	for ($i=0; $i<7; $i++) {
		if ($productInfo[38+$i] > 0) {
			fseek($laborEqFile, $thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]*4000);
			$eqDat = unpack('i*', fread($laborEqFile, 80));
			
			$eqArray = array_fill(0, 1000, 0);
			$eqArray[$eqDat[1]] = $eqDat[2];
			$eqArray[$eqDat[3]] = $eqDat[4];
			$eqArray[$eqDat[5]] = $eqDat[6];
			$eqArray[$eqDat[7]] = $eqDat[8];
			$eqArray[$eqDat[9]] = $eqDat[10];
			$eqArray[$eqDat[11]] = $eqDat[12];
			$eqArray[$eqDat[13]] = $eqDat[14];
			$eqArray[$eqDat[15]] = $eqDat[16];
			$eqArray[$eqDat[17]] = $eqDat[18];
			$eqArray[$eqDat[19]] = $eqDat[20];
			
			$effectiveRate = $eqArray[$thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]]/10000;
			$workTime = max(1,$thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]);
			//$laborLevel = log($workTime, 2.0)+1;
			$laborLevel = $workTime/36000;
			$productionRate += (0.5+$laborLevel)*$effectiveRate;
			$productionItems++;
		}
		
		/*
		if ($productInfo[38+$i] != $thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]) {
			$neededLabor[] = $productInfo[38+$i];
		}
		if ($productInfo[38+$i] > 0) {
			$workTime = max(1,$thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]);
			$laborLevel = log($workTime, 2.0)+1;
			$productionRate += 0.5+$laborLevel;
			$productionItems++;
		}*/
	}
	
	$totalRate = $productionRate/$productionItems;
	

	// Set new item production
	echo 'Set production of item '.$postVals[3].' to '.$productInfo[11];
	$thisFactory->save('currentProd', $postVals[3]);
	$thisFactory->save('prodRate', $newProduct->get('numMaterial'));

	echo '<script>
	productMaterial = ['.implode(',', $newProduct->reqMaterials).'];
	showProdRequirements(reqBox.materials, productMaterial);

	productLabor = ['.implode(',', $newProduct->reqLabor).'];
	showRequiredLabor(businessDiv.laborSection.required, productLabor);
	</script>';
} else {
	echo 'Not able to set';
}


fclose($objFile);
fclose($slotFile);

?>
