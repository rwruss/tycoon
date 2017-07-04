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
$lineInfo = explode(',', $line);
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
print_r($cityList);
// Determine the route connections for each city and record
$connectDatFile = fopen('c:/websites/tycoon/scenarios/'.$scenario.'/connections.cxf', 'wb');
fseek($connectListFile, 0);

$useDistance = 0;
$numRoutes = $cityCount*($cityCount+1)/2;
$routeDistances = array_fill(0, $numRoutes, 0);
$routeTypes = array_fill(0, $numRoutes, 0);
$nodeList = [];
$cityCount = 1;
fgets($connectListFile);
while (($line = fgets($connectListFile)) !== false) {
	$lineItems = explode(',', $line);
	echo 'Check '.(sizeof($lineItems)-1).' items<P>';
  print_r($lineItems);
  echo '<p>';
	$cityNodes = [];

  // record the node internal distance
  $thisRoute = calcRouteNum($cityList[$lineItems[0]], $cityList[$lineItems[0]]);
  $routeDistances[$thisRoute] = $selfDist[$lineItems[0]];

	for ($i=3; $i<sizeof($lineItems); $i++) {
    $trgCity = trim($lineItems[$i]);
    if ($trgCity == '') break;
    echo $i.': Compare city '.$lineItems[0].' to '.$trgCity.'<br>';
    $cityNodes[] = $cityList[$trgCity];
		//if ($cityList[$lineItems[0]] < $cityList[$lineItems[$i]]) {
  		$thisRoute = calcRouteNum($cityList[$lineItems[0]], $cityList[$trgCity]);

  		$useDistance = intval(($selfDist[$lineItems[0]] + $selfDist[$trgCity])/2);
  		$routeDistances[$thisRoute] = $useDistance;
  		$routeTypes[$thisRoute] = max($landOrSea[$lineItems[0]], $landOrSea[$trgCity]);
      echo '    Route: '.$thisRoute.' distance is '.$useDistance.'<br>';
    //}
	}
  $nodeList[$cityCount] = $cityNodes;
  $cityCount++;
}

echo '<p>Calculated distances:<p>';
print_r($routeDistances);
echo '<p>';

//$cityCount = 5;
$routeFile = fopen('c:/websites/tycoon/scenarios/'.$scenario.'/routes.rtf', 'wb');
$nodeCost = array_fill(0, $cityCount+1, 999999);
$pvsNode = array_fill(0, $cityCount+1, 0);
$listStartIndex = 0;

print_r($nodeList);

for ($city = 1; $city < $cityCount; $city++) {
  echo '<p>Check '.$cityNames[$city].' ('.$city.') Connections<br>';
	// Run a* on the rotue connections
	$nodeCost = array_fill(0, 100+1, 999999);
	$pvsNode = array_fill(0, 100+1, 0);

	$checkList = [$city];
  $count = 0;
  $nodeCost[$city] = 0;
	while (sizeof($checkList) > 0 && $count < 25) {
		$pvsCity = array_shift($checkList);
    echo '<br>Check nodes at '.$cityNames[$pvsCity].'<br>';
    print_r($nodeList[$pvsCity]);
		for ($i = 0; $i < sizeof($nodeList[$pvsCity]); $i++) {
      $dstCity = $nodeList[$pvsCity][$i];

			$routeNum = calcRouteNum($pvsCity, $dstCity);
			$checkDist = $nodeCost[$pvsCity]+$routeDistances[$routeNum];
      echo '<br>Dst City '.$cityNames[$dstCity].' ('.$dstCity.') has a node cost of '.$nodeCost[$dstCity].' and a check dist of '.$checkDist.'.  R: '.$routeNum.' ->('.$nodeCost[$pvsCity].' + '.$routeDistances[$routeNum].') - ';
			if ($nodeCost[$dstCity] > $checkDist && $routeDistances[$routeNum] > 0) { // this route is shorter than the previous found route
				$nodeCost[$dstCity] = $checkDist; // set the cost to get to this node to the new cost
				$pvsNode[$dstCity] = $pvsCity; // record the new better previous node
				$checkList[] = $dstCity; // add this node to the list of items to be rechecked
        echo 'Make '.$pvsCity.' predecessor for '.$dstCity;
			}
		}
    $count++;
	}

	// Output the results for each city
	$pathList = [];
	$routeTypeList = [];
	for ($trgCity=$city; $trgCity>0; $trgCity--) {
		$writeArray = [];
		$pathList = [];
		$routeTypeList = [];
    $pvsCity = $trgCity;
		$lastCity = $city;
    $totalDistance = 0;
		echo '<p>180 '.$cityNames[$city].' to '.$cityNames[$trgCity].' ('.$pvsCity.'/'.$city.')<br>';
    if ($cityNames[$city] == $cityNames[$trgCity]) {
      $routeNum = calcRouteNum($city, $pvsNode[$city]);
      array_push($writeArray, $pvsCity, $routeTypes[$routeNum], $routeDistances[$routeNum]);
      $totalDistance += $routeDistances[$routeNum];
    } else {
  		while ($pvsCity != $city) {
        if ($pvsCity == 0) {
          echo '--> NO CONNECTION<br>';
          break;
        }
  			$routeNum = calcRouteNum($city, $dstCity);
  			$pathList[] = $pvsCity;
  			$routeNum = calcRouteNum($lastCity, $pvsCity);
  			$routeType = $routeTypes[$routeNum];
  			$routeTypeList[] = $routeType;

  			echo '--> '.$cityNames[$pvsCity].' R:'.$routeNum.' T:'.$routeType.', D:'.$routeDistances[$routeNum].'<br>';

  			array_push($writeArray, $pvsCity, $routeType, $routeDistances[$routeNum]); // next city, route type (sea/land), leg distance
        $totalDistance += $routeDistances[$routeNum];
  			$lastCity = $pvsCity;
  			$pvsCity = $pvsNode[$pvsCity];
  		}

    if (sizeof($writeArray)>0) {
      echo '--> '.$cityNames[$pvsCity].' R:'.$routeNum.' T:'.$routeType.', D:'.$routeDistances[$routeNum].'<br>';
      $routeNum = calcRouteNum($city, $pvsNode[$city]);
      array_push($writeArray, $pvsCity, $routeType, $routeDistances[$routeNum]);
      $totalDistance += $routeDistances[$routeNum];
    }
  }
	// Store the results of each route for the city
	$routeNum = calcRouteNum($city, $trgCity);
	fseek($routeFile, $numRoutes*12+$listStartIndex);
	$writeLength = fwrite($routeFile, packArray($writeArray));
	
	// Go back and record the head information for the route
	fseek($routeFile, $routeNum*12);
	echo '212: Head for route '.$routeNum.' -> '.$listStartIndex.' (+ '.($numRoutes*12).'), '.$writeLength.'<p>';
	fwrite($routeFile, pack('i*', $numRoutes*12+$listStartIndex, $writeLength, $totalDistance));
	$listStartIndex += $writeLength;
  }
}
fclose($routeFile);


function packArray($data, $type='i') {
  $str = '';
  foreach ($data as $value) {
    $str .= pack($type, $value);
  }
  return $str;
}

function calcRouteNum($city1, $city2) {
	$loCity = min($city1, $city2);
	$hiCity = max($city1, $city2);

	$routeNum = ($hiCity-1)*($hiCity)/2 + $hiCity - $loCity;
	return $routeNum;
}

?>
