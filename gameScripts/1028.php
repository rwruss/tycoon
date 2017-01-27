<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load target factory
$thisFactory = loadObject($postVals[1], $objFile, 1000);
$thisFactory->updateStocks();

print_r($thisFactory->objDat);
print_r($thisFactory->resourceInv());
print_r($thisFactory->resourceStores);

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
fseek($thisFactory->linkFile, $thisFactory->get('currentProd')*1000);
$productInfo = unpack('i*', fread($thisFactory->linkFile, 200));

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

// Calculate the amount of product to be produced
$durations = [0, 3600, 7200, 14400, 28800];
$production = $thisFactory->get('prodRate')*$durations[$postVals[2]]/360000; // 3600 seconds x 100 for decimal factor in production rate

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
	for ($i=0; $i<sizeof($rscFail); $i++) {
		echo 'Need '.$rscFail[$i].' of resource type '.$productInfo[$i+18];
		}
	exit();
}
// Verify that there are enough required resources -->

// Deduct the required inputs
foreach ($usageList as $spot => $qty) {
	$thisFactory->objDat[31+$spot] -= $qty;
}

// Start the work
$overRideDurs = [0, 30, 60, 90, 120];
$thisFactory->set('prodLength', $overRideDurs[$postVals[2]]);
$thisFactory->set('prodStart', $now);
$thisFactory->set('prodQty', $production);
$thisFactory->set('initProdDuration', $overRideDurs[$postVals[2]]);

$thisFactory->saveAll($thisFactory->linkFile);

echo 'Make '.$production.' in '.$durations[$postVals[2]].' ('.$overRideDurs[$postVals[2]].') Seconds.
<script>
var timeBox = addDiv("", "orderTime", prodContain);
timeBox.runClock = true;
countDownClock('.($thisFactory->get('prodLength') + $thisFactory->get('prodStart')).', timeBox, function () {console.log("update factory")});
updateMaterialInv('.$postVals[1].', ['.implode(',', $thisFactory->resourceInv()).']);
</script>';

fclose($objFile);

?>
