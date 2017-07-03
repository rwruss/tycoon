<?php

//transportClass.php

class routeObj {
	public $id, $stops, $stopsDist, $objDat, $attrList;

	function __construct($id, $dat) {
		$this->id = $id;
		$this->objDat = unpack('s*', substr($dat, 4, 20));
		$this->stops = unpack('s*', substr($dat, 56, 20));
		$this->stopsDist = unpack('s*', substr($dat, 76, 20));

		$this->attrList['owner'] = 1;
		$this->attrList['mode'] = 2;
		$this->attrList['speed'] = 3;
		$this->attrList['spaceCost'] = 4;
		$this->attrList['weightCost'] = 5;

		$this->attrList['spaceCap'] = 6;
		$this->attrList['weightCap'] = 7;
		$this->attrList['status'] = 8;
		$this->attrList['runFreq'] = 9;
		$this->attrList['vehicle'] = 9;
	}

	function get($desc) {
		if (array_key_exists($desc, $this->attrList)) {
			return $this->objDat[$this->attrList[$desc]];
		} else {
			echo 'DESC: "'.$desc.'" Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
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
	}
}

function loadRoute ($routeNum, $routeFile) {
	fseek($routeFile, $routeNum);
	$routeHead = unpack('i*', fread($routeFile, 12));
	fseek($routeFile, $routeHead[1]);
	$routeDat = fread($routeFile, $routeHead[2]);

	return new routeObj($routeNum, $routeDat);
}

function calcRouteNum($city1, $city2) {
	$loCity = min($city1, $city2);
	$hiCity = max($city1, $city2);

	$routeNum = ($hiCity-1)*($hiCity)/2 + $hiCity - $loCity;
	return $routeNum;
}

function routeLegs($routeInfo) {
	$modeChanges = [];
	if (sizeof($routeInfo) > 5) {
		$modeChanges = [$routeInfo[1]]; // starting node id
		for ($i=5; $i<sizeof($routeInfo); $i+=3) {
			if ($routeInfo[$i] != $routeInfo[$i-3]) { // compare the route types of this leg and the pvs leg
				$modeChanges[] = $i-4; // the end node ID for the previous mode
				$modeChanges[] = $i-1; // make this node ID the start for the next mode
			}
		}
		$modeChanges[] = $routeInfo[sizeof($routeInfo)-2]; // record the final node ID
	} else {
		$modeChanges = [$routeInfo[1], $routeInfo[1]];
	}

	return $modeChanges;
}

function loadRoutePath($routeFile, $routeNum) {
	fseek($routeFile, $routeNum*4);
	$routeHead = unpack('i*', fread($routeFile, 12));
	fseek($routeFile, $routeHead[1]);
	$routeDat = fread($routeFile, $routeHead[2]);

	return unpack('i*', $routeDat);
}

?>
