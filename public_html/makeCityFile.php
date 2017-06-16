<?php

$numCities = 10;
$numRegions = 3444;
$numNations = 196;

$scenario = 1;
/*
$baseSlots = ($numCities+$numRegions+$numNations);
$areaSize = $baseSlots*1000;
*/
$cityFile = fopen('cities.dat', 'wb');

$countries = 206;
$regions = 3444;

$areaHeader = ($countries+$regions+1)*50*4;

$numProducts = 10000;
$generalItems = 250;

$blockSize = ($numProducts *2 + $generalItems) * 4;
$now = time();

/*
File Size:
250 general items
10000 product demand rates
10000 current demand rates

TOtal: 20250 x 4 = 81000
*/

$cityArray = array_fill(1, 250, 0);
$cityArray[5] = 5;
$cityArray[10] = $now;  // update time
$cityArray[11] = 10; // size tier
$cityArray[12] = 1000000; // population
$cityArray[13] = 0; // education levle
$cityArray[14] = 100; // affluence
$cityArray[15] = $now; // labor update time
$cityArray[16] = $now; // base labor time
$cityArray[17] = 0; // school slot
$cityArray[18] = 0; // school slot
$cityArray[26] = 1000000; //money

$cityArray[93] = 100; // school status
$cityArray[96] = 100; // school status
$cityArray[99] = 100; // school status
$cityArray[102] = 100; // school status
$cityArray[105] = 100; // school status
$cityArray[108] = 100; // school status
$cityArray[111] = 100; // school status
$cityArray[114] = 100; // school status
$cityArray[117] = 100; // school status
$cityArray[120] = 100; // school status

$cityData = packArray($cityArray);
$dataCheck = unpack('i*', $cityData);
$dataSize = strlen($cityData);

$cityDemands = array_fill(1, 20000, 10000);
$demandStr = packArray($cityDemands, 'i');
//print_R($dataCheck);
echo 'Data block size is '.$dataSize;

for ($i=0; $i<$numCities; $i++) {
  fseek($cityFile, $areaHeader+$i*$blockSize);
  fwrite($cityFile, $cityData.$demandStr);
}


fclose($cityFile);

$supplyFile = fopen('citySupply.csf', 'wb');
fseek($supplyFile, 1040000-4);
fwrite($supplyFile, pack('i', 0));
fclose($supplyFile);

$cityList = [];

// Determine the transport mode from the list file

$connectListFile = fopen('c:/websites/tycoon/scenarios/'.$scenario.'/routes.csv', 'rb');
$line = fgets($connectListFile);
$lineInfo = exlode(',', $line);
$transportMode = $line[3];
// Assign city numbers to the list and store in an array, store the self distances in another array
$cityCount = 1;
$cityNames = [];
$landOrSea = [];
while (($line = fgets($connectListFile)) !== false) {
	$lineItems = explode(',', $line);
	$cityList[$lineItems[0]] = $cityCount;
	$selfDist[$lineItems[0]] = $lineItems[1];
	$cityNames[$cityCount] = $lineItems[0];
	$landOrSea[$lineItems[0]] = $lineItems[2];
	$cityCount++;
}

// Determine the route connections for each city and record
$connectDatFile = fopen('c:/websites/tycoon/scenarios/'.$scenario.'/connections.cxf', 'wb');
fseek($connectListFile, 0);

$useDistance = 0;
$numRoutes = $cityCount*($cityCount+1)/2;
$routeDistances = array_fill(0, $numRotues, 0);
$routeTypes = array_fill(0, $numRotues, 0);

while (($line = fgets($connectListFile)) !== false) {
	$lineItems = explode(',', $line);
	
	for ($i=3; $i<sizeof($lineItems); $i++) {
		if ($cityList[$lineItems[0]] < $cityList[$lineItems[$i])
		$thisRoute = calcRouteNum($cityList[$lineItems[0]], $cityList[$lineItems[$i]]);
	
		$useDistance = intval(($selfDist[$lineItems[0]] + $selfDist[$lineItems[$i]])/2);
		$routeDistances[$thisRoute] = $useDistance;		
		$routeTypes[$thisRoute] = max($landOrSea[$lineItems[0]], $landOrSea[$lineItems[$i]]);
	}
}
$cityCount = 5;
$routeFile = fopen('c:/websites/tycoon/scenarios/'.$scenario.'/rotues.rtf', 'wb')
$nodeCost = array_fill(0, $cityCount+1, 999999);
$pvsNode = array_fill(0, $cityCount+1, 0);
$listStartIndex = 0;
for ($city = 1; $city <= $cityCount; $city++) {
	
	// Run a* on the rotue connections
	$nodeCost = array_fill(0, $cityCount+1, 999999);
	$pvsNode = array_fill(0, $cityCount+1, 0);
	
	$checkList = [$city];
	while (sizeof($checkList) > 0) {
		$pvsCity = array_shift($checkList);
		for ($dstCity = 1; $dstCity <= $cityCount; $dstCity++) {
			$routeNum = calcRouteNum($city, $dstCity);
			$checkDist = $nodeCost[$pvsCity]+$useDistance[$routeNum]
			if ($nodeCost[$dstCity] > $checkDist && $useDiatance[$routeNum] > 0) { // this route is shorter than the previous found route
				$nodeCost[$dstCity] = $checkDist; // set the cost to get to this node to the new cost
				$pvsNode[$dstCity] = $pvsCity; // record the new better previous node
				$checkList[] = $dstCity; // add this node to the list of items to be rechecked
			}
		}
	}
	
	// Output the results for each city
	$nodeList = [];
	$routeTypeList = [];
	for ($trgCity=$city; $trgCity>0; $trgCity--) {
		$writeArray = [];
		$nodeList = [];
		$routeTypeList = [];
		echo '<p>Final destination is '.$cityNames[$trgCity].'<br>';
		$pvsCity = $trgCity;
		$lastCity = $city;
		while ($pvsCity != $city) {
			$routeNum = calcRouteNum($city, $dstCity);
			$nodeList[] = $pvsCity;
			$routeNum = alcRouteNum($lastCity, $pvsCity);
			$routeType = $routeTypes[$routeNum];
			$routeTypeList[] = $routeType;
			
			echo '--> '.$cityNames[$pvsCity].' T:'.$routeType.', D:'.$useDistance[$routeNum].'<br>';
			
			array_push($writeArray, $pvsCity, $routeType, $useDistance[$routeNum]); // next city, route type (sea/land), leg distance
			
			$lastCity = $pvsCity;
			$pvsCity = $pvsNode[$pvsCity];
		}
		
	// Store the results of each route for the city
	$routeNum = calcRouteNum($city, $trgCity);
	fseek($routeFile, $numRoutes*8+$listStartIndex);
	$writeLength = fwrite($routeFile, packArray($writeArray));
	fseek($routeFile, $routeNum*8);
	fwrite($routeFile, pack('i*', $numRoutes*8+$listStartIndex, $writeLength));
	$listStartIndex += $writeLength;
	}	
}
fclose($routeFile);

// Save the route distance information
fseek($routeDistFile, $numRoutes*$transportMode*4);
fwrite($routeDistFile, packArray($routeDistances));

function packArray($data, $type='i') {
  $str = '';
  for ($i=1; $i<=sizeof($data); $i++) {
    $str = $str.pack($type, $data[$i]);
  }
  return $str;
}

function calcRouteNum($city1, $city2) {
	$loCity = min($city1, $city2);
	$hiCity = max($city1, $city2);
	
	$routeNum = ($loCity-1)*($loCity)/2 + $hiCity - $loCity;
	return $routeNum;
}

?>
