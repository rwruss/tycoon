<?php

if (sizeof($postVals) != 3) exit('error 8201-0');

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');

// Load target factory
$thisFactory = loadObject($postVals[1], $objFile, 1600);
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

$thisProduct = loadProduct($postVals[2], $objFile, 400);

// Calculate the amount of product to be produced
$durations = [0, 3600, 7200, 14400, 28800];
$production = intval($thisFactory->get('prodRate')*$durations[$postVals[2]]/360000); // 3600 seconds x 100 for decimal factor in production rate
//echo 'Prod rate is '.$thisFactory->get('prodRate');
if ($thisFactory->get('prodRate') <= 0) exit('Can\'t product anything right now');
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
		for ($j=0; $j<$limit = 16; $j++) {  // j is the index of the storage location at the factory
			if ($thisFactory->resourceStores[$j*2] == $productInfo[$i+18]) {
				//echo 'Resources spot '.$j.' (type '.$thisFactory->resourceStores[$j*2].') which has a stock of '.$thisFactory->resourceStores[$j*2+1].' has a usage rate of '.$productInfo[$i+28].' Need '.($production*$productInfo[$i+28]).'<br>';
				if ($production*$productInfo[$i+28] <= $thisFactory->resourceStores[$j*2+1]) {
					//echo $production*$productInfo[$i+28].' <= '.$thisFactory->resourceStores[$j*2+1];
					$usageList[$j] = $production*$productInfo[$i+28];  // Record the usage rate for the store location (units per item produced)
				} else {
					$rscFail[$j*2] = $production*$productInfo[$i+28] - $thisFactory->resourceStores[$j*2+1];
				}
				break;
			}
		}
	}
}

if (sizeof($rscFail) > 0) {
	foreach ($rscFail as $rscID=>$rscQty) {
		print_R($rscFail);
		//echo 'Need '.$rscQty.' of resource type '.$thisFactory->resourceStores[$rscID];
		}
	exit();
}
// Verify that there are enough required resources -->

// Deduct the required inputs and calculate input Costs
$productCost = 0;
$productQuality = 0;
$productPollution = 0;
$productRights = 0;

foreach ($usageList as $spot => $qtyUsed) {
	// calc amounts for each input trait.  Total points for each trait * qty / total amount in storage
	$inputCost = $thisFactory->objDat[$thisFactory->inputCost+$spot]*$qtyUsed/max(1, $thisFactory->objDat[31+$spot]); //total costs * amount being made/amount in inventory
	$inputQuality = $thisFactory->objDat[$thisFactory->inputQuality+$spot]*$qtyUsed/max(1,$thisFactory->objDat[31+$spot]);
	$inputPollution = $thisFactory->objDat[$thisFactory->inputPollution+$spot]*$qtyUsed/max(1, $thisFactory->objDat[31+$spot]);
	$inputRights = $thisFactory->objDat[$thisFactory->inputRights+$spot]*$qtyUsed/max(1, $thisFactory->objDat[31+$spot]);

	// adjust total amounts for the factory
	$thisFactory->objDat[$thisFactory->inputCost+$spot] -= $inputCost;
	$thisFactory->objDat[$thisFactory->inputQuality+$spot] -= $inputQuality;
	$thisFactory->objDat[$thisFactory->inputPollution+$spot] -= $inputPollution;
	$thisFactory->objDat[$thisFactory->inputRights+$spot] -= $inputRights;

	$thisFactory->objDat[$thisFactory->inputOffset+$spot] -= $qtyUsed;

	// adjsut trait amounts for the product
	$productCost += $inputCost;
	$productQuality += $inputQuality;
	$productPollution += $inputPollution;
	$productRights += $inputRights;
}

// Calculate the labor costs
$laborCost = 0;
$qualityPoints = 0;
$totalQualWeight = 1;
for ($i=0; $i<7; $i++) {
	//echo 'Labor Cost: '.$thisFactory->objDat[$thisFactory->laborOffset+$i*10+5].' * '.$durations[$postVals[2]].' / 3600 ('.($thisFactory->objDat[$thisFactory->laborOffset+$i*10+5]*$durations[$postVals[2]]/3600).')<br>';
	$laborCost += $thisFactory->objDat[$thisFactory->laborOffset+$i*10+5]*$durations[$postVals[2]]/3600;

	$talentStats = unpack("C*", pack("i", $thisFactory->objDat[$thisFactory->laborOffset+$i*10+9]));
	$qualityPoints += (1+($talentStats[1]-50)/100) * (1 + ($talentStats[2]-50)/200)*$talentStats[4];
	$totalQualWeight += $talentStats[4];
}

// Caluclate the quality adjustment
$qualityMod = $qualityPoints/$totalQualWeight;

// caluclate the pollution added to the product
$productPollution += $durations[$postVals[2]]/86400 * $thisFactory->get('polPerDay');

// Calculate the rights added to the product
$productRights += $durations[$postVals[2]]/86400 * $thisFactory->get('rtsPerDay');

// Start the work
$overRideDurs = [0, 10, 10, 10, 10];
$thisFactory->set('prodLength', $overRideDurs[$postVals[2]]);
$thisFactory->set('prodStart', $now);
$thisFactory->set('prodQty', $production);
$thisFactory->set('initProdDuration', $overRideDurs[$postVals[2]]);
$thisFactory->set('prodRights', $productRights);
$thisFactory->set('prodPollution', $productPollution);
$thisFactory->set('prodQuality', $productQuality*$qualityMod);
$thisFactory->set('prodCost', $productCost);
$thisFactory->set('prodLaborCost', $laborCost);

$thisFactory->saveAll($thisFactory->linkFile);

if ($thisFactory->get('currentProd') > 0) {
	$currentProduction = ', {setVal:'.$thisFactory->get('currentProd').'}';
} else $currentProduction = '';

$currentProduction = ', {setVal:'.$thisFactory->get('currentProd').'}';

/*
echo 'Make '.$production.' in '.$durations[$postVals[2]].' ('.$overRideDurs[$postVals[2]].') Seconds.
<script>

prodContain.innerHTML = "";
fProduction = new factoryProduction('.$postVals[1].', '.($thisFactory->get('prodLength') + $thisFactory->get('prodStart')).', '.$thisFactory->get('currentProd').', '.$production.');
fProductionBox = fProduction.render(prodContain);
factoryProductionBox = prodList.SLsingleButton(fProductionBox'.$currentProduction.');
updateMaterialInv('.$postVals[1].', ['.implode(',', $thisFactory->resourceInv()).']);
</script>';
*/

$returnArray = [];
$returnArray[0] = $postVals[1];  // This factory ID
$returnArray[1] = ($thisFactory->get('prodLength') + $thisFactory->get('prodStart'));  // Completion Time
$returnArray[2] = $thisFactory->get('currentProd'); // Product ID
$returnArray[3] = $production;  // Qty to be produced

echo implode(',', array_merge($returnArray, $thisFactory->resourceInv()));

fclose($offerDatFile);
fclose($objFile);

?>
