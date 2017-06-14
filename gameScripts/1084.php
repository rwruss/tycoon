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
$routeFile = fopen('routes.rtf', 'rb');

// Get product information
$prodSpace = 1;
$prodWeight = 1;

// Load the selling factory to get the origin location
$sellingFactory = loadObject($postVals[1], $objFile, 1000);

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

fclose($objFile);
fclose($routeFile);

echo implode(',', $legInfo);

?>
