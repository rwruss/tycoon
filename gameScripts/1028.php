<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb');

// Load target factory
$thisFactory = loadObject($postVals[1], $objFile, 1000);
$thisFactory->updateStocks($offerDatFile);

//print_r($thisFactory->objDat);
//print_r($thisFactory->resourceInv());
//print_r($thisFactory->resourceStores);

// Confrim that player can give this order
if ($thisFactory->get('owner') != $pGameID) {
	exit("error 8201-1");
}

$now = time();
// Confirm there is no task already started
if ($thisFactory->get('prodLength') + $thisFactory->get('prodStart') > $now) {
	exit("error 8201-2");
}

// Verify that the correct labor is assigned to start this task
//fseek($thisFactory->linkFile, $thisFactory->get('currentProd')*1000);
//$productInfo = unpack('i*', fread($thisFactory->linkFile, 200));

$thisProduct = loadProduct($postVals[2], $objFile, 400);
//$productionRate = $thisFactory->get('prodRate');
//$productionRate = $thisFactory->setProdRate($postVals[3], $thisProduct, $laborEqFile);
/*
$laborFail = false;
$neededLabor = [];
$productionRate = 0;
$productionItems = 0;

for ($i=0; $i<7; $i++) {
	if ($productInfo[38+$i] != $thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]) {
		$laborFail = true;
		$neededLabor[] = $productInfo[38+$i];
	}
	if ($productInfo[38+$i] > 0) {
		$workTime = max(1,$thisFactory->objDat[$thisFactory->laborOffset+10*$i+1]);
		$laborLevel = log($workTime, 2.0)+1;
		$productionRate += 0.5+$laborLevel;
		$productionItems++;
	}
}

if ($laborFail) {
	echo 'You need to assign the following labor types:';
	foreach($neededLabor as $val) {
		echo 'Type:'.$val;
	}
	exit();
}

// Caluclate the production rate
$totalRate = $productionRate/$productionItems;
*/
// Calculate the amount of product to be produced
$durations = [0, 3600, 7200, 14400, 28800];
$production = intval($thisFactory->get('prodRate')*$durations[$postVals[2]]/360000); // 3600 seconds x 100 for decimal factor in production rate

// <-- Verify that there are enough required resources
// load production requirements
fseek($thisFactory->linkFile, $thisFactory->get('currentProd')*1000);
$productInfo = unpack('i*', fread($thisFactory->linkFile, 200));

// Sort material requirements into the storage index for the factory
$rscSpots = [];
for ($i=0; $i<sizeof($thisFactory->resourceStores); $i+=2) {
	$rscSpots[$thisFactory->resourceStores[$i]] = $i;
}

$rscFail = [];
$usageList=[];
for ($i=0; $i<10; $i++) { // i is the index of the resource required by the product
	if ($productInfo[$i+18] > 0) {
		//echo 'Look for resource '.$productInfo[$i+18];
		for ($j=0; $j<sizeof($thisFactory->resourceStores); $j+=2) {  // j is the index of the storage location at the factory
			if ($thisFactory->resourceStores[$j] == $productInfo[$i+18]) {
				echo 'Resources spot '.$j.' (type '.$thisFactory->resourceStores[$j].') which has a stock of '.$thisFactory->resourceStores[$j+1].' has a usage rate of '.$productInfo[$i+28].' Need '.($production*$productInfo[$i+28]).'<br>';
				if ($production*$productInfo[$i+28] <= $thisFactory->resourceStores[$j+1]) {
					$usageList[$j] = $production*$productInfo[$i+28];  // Record the usage rate for the store location (units per item produced)
				} else {
					$rscFail[$j] = $production*$productInfo[$i+28] - $thisFactory->resourceStores[$j+1];
				}
				break;
			}
		}
	}
}

if (sizeof($rscFail) > 0) {
	foreach ($rscFail as $rscID=>$rscQty) {
		print_R($rscFail);
		echo 'Need '.$rscQty.' of resource type '.$thisFactory->resourceStores[$rscID];
		}
	exit();
}
// Verify that there are enough required resources -->

// Deduct the required inputs and calculate input Costs
$productCost = 0;
$productQuality = 0;
$productPollution = 0;
$productRights = 0;

foreach ($usageList as $spot => $qty) {	
	// calc amounts for each input trait
	$inputCost = $thisFactory->objDat[$thisFactory->inputCost+$spot]*$qty/$thisFactory->objDat[$thisFactory->inputOffset+$spot];
	$inputQuality = $thisFactory->objDat[$thisFactory->inputQuality+$spot]*$qty/$thisFactory->objDat[$thisFactory->inputQuality+$spot];
	$inputPollution = $thisFactory->objDat[$thisFactory->inputPollution+$spot]*$qty/$thisFactory->objDat[$thisFactory->inputPollution+$spot];
	$inputRights = $thisFactory->objDat[$thisFactory->inputRights+$spot]*$qty/$thisFactory->objDat[$thisFactory->inputRights+$spot];
	
	// adjust total amounts for the factory
	$thisFactory->objDat[$thisFactory->inputCost+$spot] -= $inputCost;
	$thisFactory->objDat[$thisFactory->inputQuality+$spot] -= $inputQuality;
	$thisFactory->objDat[$thisFactory->inputPollution+$spot] -= $inputPollution;
	$thisFactory->objDat[$thisFactory->inputRights+$spot] -= $inputRights;
	
	$thisFactory->objDat[$thisFactory->inputOffset+$spot] -= $qty;
	
	// adjsut trait amounts for the product	
	$productCost += $inputCost;
	$productQuality += $inputQuality;
	$productPollution += $inputPollution;
	$productRights += $inputRights;
}

// Calculate the labor costs
$laborCost = 0;
for ($i=0; $i<7; $i++) {
	$laborCost += $thisFactory->objDat[$thisFactory->laborOffset+$i*10+5]*$durations/3600;
}

// Start the work
$overRideDurs = [0, 10, 10, 10, 10];
$thisFactory->set('prodLength', $overRideDurs[$postVals[2]]);
$thisFactory->set('prodStart', $now);
$thisFactory->set('prodQty', $production);
$thisFactory->set('initProdDuration', $overRideDurs[$postVals[2]]);
$thisFactory->set('prodRights', $productRights);
$thisFactory->set('prodPollution', $productPollution);
$thisFactory->set('prodQuality', $productQuality);
$thisFactory->set('prodCost', $productCost);

$thisFactory->saveAll($thisFactory->linkFile);

if ($thisFactory->get('currentProd') > 0) {
	$currentProduction = ', {setVal:'.$thisFactory->get('currentProd').'}';
} else $currentProduction = '';

$currentProduction = ', {setVal:'.$thisFactory->get('currentProd').'}';

echo 'Make '.$production.' in '.$durations[$postVals[2]].' ('.$overRideDurs[$postVals[2]].') Seconds.
<script>

prodContain.innerHTML = "";
fProduction = new factoryProduction('.$postVals[1].', '.($thisFactory->get('prodLength') + $thisFactory->get('prodStart')).', '.$thisFactory->get('currentProd').', '.$production.');
fProductionBox = fProduction.render(prodContain);
factoryProductionBox = prodList.SLsingleButton(fProductionBox'.$currentProduction.');
updateMaterialInv('.$postVals[1].', ['.implode(',', $thisFactory->resourceInv()).']);
</script>';

fclose($offerDatFile);
fclose($objFile);

?>
