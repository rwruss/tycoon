<?php

//transportClass.php

class routeObj {
	public $id, $stops, $stopsDist, $objDat, $attrList, $file;

	function __construct($id, $dat, $file) {
		$this->id = $id;
		$this->objDat = unpack('s*', substr($dat, 4, 20));
		$this->stops = unpack('s*', substr($dat, 56, 20));
		$this->stopsDist = unpack('s*', substr($dat, 76, 20));
		$this->file = $file;

		$this->attrList['owner'] = 1;
		$this->attrList['mode'] = 2;
		$this->attrList['speed'] = 3;
		$this->attrList['spaceCost'] = 4;
		$this->attrList['weightCost'] = 5;

		$this->attrList['spaceCap'] = 6;
		$this->attrList['weightCap'] = 7;
		$this->attrList['status'] = 8;
		$this->attrList['runFreq'] = 9;
		
		$this->attrList['lEarning'] = 11;
		$this->attrList['pEarning'] = 13;
		$this->attrList['vehicle'] = 14;
	}
	
	function adjVal($desc, $incr) {
		if (array_key_exists($desc, $this->attrList)) {
			$this->objDat[$this->attrList[$desc]] -= $incr;
		} else {
			echo 'seterr: DESC: "'.$desc.'" Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
	}

	function get($desc) {
		if (array_key_exists($desc, $this->attrList)) {
			return $this->objDat[$this->attrList[$desc]];
		} else {
			echo 'DESC: "'.$desc.'" Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
	}
	
	function set($desc, $val) {
		if (array_key_exists($desc, $this->attrList)) {
			$this->objDat[$this->attrList[$desc]] = $val;
		} else {
			echo 'seterr: DESC: "'.$desc.'" Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
	}
	
	function saveAll() {
		fseek($file, $this->id);
		fwrite($file, packArray($this->objDat));
	}

	function legInfo ($start, $end) {
		// look for the fastest way on this route for the two ends of the leg
		$stops = array_slice(array_filter($this->stops), 0, 10);
		$stopList = array_merge($stops, $stops);

		$dists = array_slice(array_filter($this->stopsDist), 10, 10);
		$distList = array_merge($dists, $dists);

		$endPoints = [-1,-1];
		for ($i=0; $i<10; $i++) {
			if ($stopList[$i] == $start) $endPoints[0] = $i;
			if ($stopList[$i] == $end && $endPoints[0] >= 0) {
				$endPoints[1] = $i;
				break;
			}
		}

		$totalDistance = 0;
		for ($i=$endPoints[0]; $i<=$endPoints[1]; $i++) {
			$totalDistance += $distList[$i];
		}

		$legInfo = array_fill(0, 10, 0);
		$legInfo[0] = $totalDistance;

		return $legInfo;
	}
}

function loadRoutePath ($routeNum, $routeFile) {
	fseek($routeFile, $routeNum);
	$routeHead = unpack('i*', fread($routeFile, 12));
	fseek($routeFile, $routeHead[1]);
	$routeDat = fread($routeFile, $routeHead[2]);

	return new routeObj($routeNum, $routeDat, $routeFile);
}

function calcRouteNum($city1, $city2) {
	$loCity = min($city1, $city2);
	$hiCity = max($city1, $city2);

	$routeNum = ($hiCity-1)*($hiCity)/2 + $hiCity - $loCity;
	return $routeNum;
}
function loadPathHead($routeFile, $routeNum) {
	fseek($routeFile, $routeNum*12);
	$routeHead = unpack('i*', fread($routeFile, 12));

	return $routeHead;
}

function loadRouteOptions($pathRoute, $tranportFile) {
	// Look up available transport for each segment of the route
	$legRoutes = new itemSlot($pathRoute, $transportFile, 40);
	$legInfo = [];
	$tmpArray = array_fill(0,13,0);
	for ($j=1; $j<=sizeof($legRoutes->slotData); $j++) {
		// Load the route data
		fseek($transportFile, $legRoutes->slotData[$j]);
		$routeDat = fread($transportFile, 140);
		$routeInfo = unpack('i*', substr($routeDat, 0, 56));
		$routeStops = unpack('s*', substr($routeDat, 56));

		// Determine total travel time
		$routeDist = array_sum(array_slice($routeStops, 11));
		$routeTime = $routeDist/$routeInfo[3];
		
		$tmpArray[0] = $j; // option ID
		$tmpArray[1] = $i; // leg Num
		$tmpArray[2] = $legRoutes->slotData[$j]; // route ID
		$tmpArray[3] = $routeInfo[1]; // owner
		$tmpArray[4] = $routeInfo[2]; // mode
		$tmpArray[5] = $routeDist; // distance
		$tmpArray[6] = $routeInfo[3]; // speed
		$tmpArray[7] = $routeInfo[4]; // cost/vol
		$tmpArray[8] = $routeInfo[5]; // cost/wt
		$tmpArray[9] = $routeInfo[6]; // cap-vol
		$tmpArray[10] = $routeInfo[7]; // cap-wt
		$tmpArray[11] = $routeInfo[8]; // status
		$tmpArray[12] = $routeInfo[14]; // vehicle
		
		$legInfo = array_merge($legInfo, $tmpArray);
	}
	return $tmpArray;
}

function loadPath($routeFile, $routeNum) {
	$pathHead = loadPathHead($routeFile, $routeNum);
	//print_r($pathHead);

	fseek($routeFile, $pathHead[1]);
	$routeDat = fread($routeFile, $pathHead[2]);

	return unpack('i*', $routeDat);
}

function packArray($data, $type='i') {
  $str = '';
  foreach ($data as $value) {
    $str .= pack($type, $value);
  }
  return $str;
}

function processRouteCosts($thisPlayer, $legCosts, $routeObjects, $legCaps, $objFile) {
	// deduct the shipping cost from the selling player
	$totalCost = array_sum($legCosts);
	$thisPlayer->save('money', $thisPlayer->get('money') - $totalCost[$i]);

	// credit the shipping cost to the shipping company and deduct from the shipper
	for($i=0; $i<$z=sizeof($routeObjects); $i++) {
		if (getClass($routeObjects[$i]) == routeObj) {
			$routeObjects[$i]->adjVal('spaceCap', -$legCaps[$i*2]);
			$routeObjects[$i]->adjVal('weightCap', -$legCaps[$i*2+1]);
			$routeObjects[$i]->adjVal('lEarning', $legCosts[$i]);
			$routeObjects[$i]->adjVal('pEarning', $legCosts[$i]);
			$rotueObjects[$i]->saveAll();
		}
	}
	/*
	for ($i=0; $i<sizeof($legOwners); $i++) {
		if ($legOwners[$i] > 0) {
			$transportingPlayer = loadObject($legOwners[$i], $objFile, 400);
			$transportingPlayer->save('money', $transportingPlayer->get('money') + $legCosts[$i]);
		}
	}*/
}

function processRouteStats() {
	
}

function routeLegs($routeInfo) {
	$modeChanges = [];
	if (sizeof($routeInfo) > 5) {
		$modeChanges = [$routeInfo[1]]; // starting node id
		for ($i=5; $i<sizeof($routeInfo); $i+=3) { // starting at second node route type(i=5)
			if ($routeInfo[$i] != $routeInfo[$i-3]) { // compare the route types of this leg and the pvs leg
				$modeChanges[] = $routeInfo[$i-4]; // the end node ID for the previous mode
				$modeChanges[] = $routeInfo[$i-1]; // make this node ID the start for the next mode
			}
		}
		if (end($modeChanges) != $routeInfo[sizeof($routeInfo)-2]) $modeChanges[] = $routeInfo[sizeof($routeInfo)-2]; // record the final node ID
	} else {
		$modeChanges = [$routeInfo[1], $routeInfo[1]];
	}

	return $modeChanges;
}

function routeLegDetails($routeList, &$legRoutes, &$legCosts, &$legTimes, &$legOwners, &$legCaps, $transportFile) {
	$modeChangeNum = 0;
	for ($i=0; $i<sizeof($routeList); $i+++) {
		if ($routeList[$i] > 0 ) {
			$tmpRoute = loadRoute($routeList[$i], $transportFile);
			$legInfo = $tmpRoute->legInfo($modeChanges[$modeChangeNum], $modeChanges[$modeChangeNum+1]);
			$legTimes[] = $legInfo[0]/$tmpRoute->get('speed');
			$legCosts[] = $shipmentWeight/$tmpRoute->get('weightCost');
			$legOwners[] = $tmpRoute->get('owner');
			array_push($legCaps, $tmpRoute->get('spaceCap'), $tmpRoute->get('weightCap'));
			$legRoutes[] = $tmpRoute;
		} else {
			$pathNum = calcRouteNum($modeChanges[$modeChangeNum], $modeChanges[$modeChangeNum+1]);
			fseek($routeFile, $pathNum*12);
			$pathHead = unpack('i*', fread($routeFile, 12));
			echo '<p>Default transport option is selected for leg '.$pathNum.'<br>';
			$legTimes[] = $pathHead[3];
			$legCosts[] = $pathHead[3];
			$legOwners[] = 0;
			$legRoutes[] = 0;
			print_r($pathHead);
		}
		$modeChangeNum += 2;
	}
}


?>
