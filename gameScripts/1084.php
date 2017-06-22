<?php

// Load the recommended route for a sale and provide transport options
/*
PVS
1 = Selling Factory ID
2 = Target City ID
3 = Product ID
4 = Sale Qty
*/

require_once('./objectClass.php');
require_once('./transportClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$routeFile = fopen($gamePath.'/routes.rtf', 'rb');
$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');

// Get product information
$prodSpace = 1;
$prodWeight = 1;

// Load the selling factory to get the origin location
$sellingFactory = loadObject($postVals[1], $objFile, 1000);
$routeNum = calcRouteNum($sellingFactory->get('region_3'), $postVals[2]);
echo 'Factory at '.$sellingFactory->get('region_3').' to city '.$postVals[2].' is route '.$routeNum;

// Load the rotue information
fseek($routeFile, $routeNum*4);
$routeHead = unpack('i*', fread($routeFile, 8));
print_r($routeHead);
fseek($routeFile, $routeHead[1]);
$routeDat = fread($routeFile, $routeHead[2]);
$routeInfo = unpack('i*', $routeDat);
print_r($routeInfo);

if (sizeof($routeInfo) > 5) {
	$modeChanges = [$routeInfo[2]];
	for ($i=5; $i<sizeof($routeInfo); $i+=3) {
		if ($routeInfo[$i] != $routeInfo[$i-3]) {
			$modeChanges[] = $i-3;
			$modeChanges[] = $i;
		}
	}
} else {
	$modeChanges = [$routeInfo[1], $routeInfo[1]];
}

for ($i=0; $i<sizeof($modeChanges); $i+=2) {
	echo 'One mode from '.$modeChanges[$i].' to '.$modeChanges[$i+1].'<br>';

	// Look up available transport for each segment of the route
	fseek($transportFile, calcRouteNum($modeChanges[$i], $modeChanges[$i+1])*4);
}

$legInfo = [];

/*
// Get the recommended route node list
$routeNum = 0;
echo 'Load route number '.$routeNum.'.  Factory '.$postVals[1].'(city '.$sellingFactory->get('region_3').') to city '.$postVals[2].'<p>';
$routeNodes = new itemSlot($routeNum, $routeFile, 40);
print_r($routeNodes->slotData);

$routeList = $routeNodes->slotData;
if ($sellingFactory->get('region_3') == $postVals[2]) {
	echo 'Staying in same area';
	$loNode = min($sellingFactory->get('region_3'), $postVals[2])-1;
	$hiNode = max($sellingFactory->get('region_3'), $postVals[2]);

	$routeNum = $loNode*($loNode+1)/2;
	$routeList[1] = $routeNum;
	$routeList[2] = $routeNum;
}
$totalMinPriceOption = [];
$totalMinTimeOption = [];
$legInfo = [];
for ($r=1; $r<=sizeof($routeList)-1; $r++) {
	echo 'Check route segment '.$routeList[$r].' to '.$routeList[$r+1];
	// Look up the current best route
	$optionList = new itemSlot($routeNum, $routeFile, 40);

	// Load the best route option for each leg of the route
	for ($i=1; $i<=sizeof($optionList->slotData); $i++) {
		fseek($routeFile, $optionList->slotData[$i]);
		$thisOption = new routeOpt(fread($routeFile, 100));

		// Determine which stops are the end of the section in question
		$ends = [0,0];
		$endCount = 0;
		for ($l=0; $l<10; $l++) {
			if ($thisOption->stops[$i] == $routeList[$r]) $ends[0] = $i;
			if ($thisOption->stops[$i] == $routeList[$r+1]) $ends[1] = $i;
			if ($ends[0]*$ends[1] > 0) break;  // both ends have been found
		}

		// Calculate the total distance for this portion of the route
		$legDistance = 0;
		for($s=0; $s<$ends[1] - $ends[0]; $s++) {
			$legDistance += $thisOption->stopsDist[$ends[0]+$s];
		}

		// Calc time and cost for this segment
		$sectionTime = $legDistance/$thisOption->get('speed'); // Total time = section distance / speed
		$sectionCost = max($thisOption->get('spaceCost')*$prodSpace, $thisOption->get('weightCost')*$prodWeight);

		array_push($legInfo, $routeList[$r], $optionList->slotData[$i], $sectionTime, $sectionCost, $thisOption->get('owner')); // route num, option #, time, cost, owner
	}
}
*/
fclose($objFile);
fclose($routeFile);
fclose($transportFile);

echo implode(',', $legInfo);

function calcRouteNum($city1, $city2) {
	$loCity = min($city1, $city2);
	$hiCity = max($city1, $city2);

	$routeNum = ($hiCity-1)*($hiCity)/2 + $hiCity - $loCity;
	return $routeNum;
}

?>
