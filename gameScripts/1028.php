<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load target factory
$thisObj = loadObject($postVals[1], $objFile, 400);
$thisObj->updateStocks();

print_r($thisObj->objDat);
print_r($thisObj->resourceInv());
print_r($thisObj->resourceStores);

// Confrim that player can give this order
if ($thisObj->get('owner') != $pGameID) {
	exit("error 8201-1");
}

$now = time();
// Confirm there is no task already started
if ($thisObj->get('prodLength') + $thisObj->get('prodStart') > $now) {
	exit("error 8201-2");
}

// Calculate the amount of product to be produced
$durations = [0, 3600, 7200, 14400, 28800];
$production = $thisObj->get('currentRate')*$durations[$postVals[2]]/360000; // 3600 seconds x 100 for decimal factor in production rate
//echo 'Production = '.$thisObj->get('currentRate').' * '.$durations[$postVals[2]].'/36000';

// <-- Verify that there are enough required resources
// load production requirements
fseek($thisObj->linkFile, $thisObj->get('currentProd')*1000);
$productInfo = unpack('i*', fread($thisObj->linkFile, 200));

// Sort material requirements into the storage index for the factory
$rscSpots = [];
for ($i=0; $i<sizeof($thisObj->resourceStores); $i+=2) {
	$rscSpots[$thisObj->resourceStores[$i]] = $i;
}

$rscFail = [];
$usageList=[];
for ($i=0; $i<10; $i++) { // i is the index of the resource required by the product
	if ($productInfo[$i+18] > 0) {
		//echo 'Look for resource '.$productInfo[$i+18];
		for ($j=0; $j<sizeof($thisObj->resourceStores); $j+=2) {  // j is the index of the storage location at the factory
			if ($thisObj->resourceStores[$j] == $productInfo[$i+18]) {
				echo 'Resources spot '.$j.' (type '.$thisObj->resourceStores[$j].') which has a stock of '.$thisObj->resourceStores[$j+1].' has a usage rate of '.$productInfo[$i+28].' Need '.($production*$productInfo[$i+28]).'<br>';
				if ($production*$productInfo[$i+28] <= $thisObj->resourceStores[$j+1]) {
					$usageList[$j] = $production*$productInfo[$i+28];  // Record the usage rate for the store location (units per item produced)
				} else {
					$rscFail[$j] = $production*$productInfo[$i+28] - $thisObj->resourceStores[$j+1];
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
	$thisObj->objDat[31+$spot] -= $qty;
}

// Start the work
$overRideDurs = [0, 30, 60, 90, 120];
$thisObj->set('prodLength', $overRideDurs[$postVals[2]]);
$thisObj->set('prodStart', $now);
$thisObj->set('prodQty', $production);

$thisObj->saveAll($thisObj->linkFile);

echo 'Make '.$production.' in '.$durations[$postVals[2]].' ('.$overRideDurs[$postVals[2]].') Seconds.
<script>
var timeBox = addDiv("", "orderTime", prodContain);
timeBox.runClock = true;
countDownClock('.($thisObj->get('prodLength') + $thisObj->get('prodStart')).', timeBox, function () {console.log("update factory")});
updateMaterialInv('.$postVals[1].', ['.implode(',', $thisObj->resourceInv()).']);
</script>';

fclose($objFile);

?>
