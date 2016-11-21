<?php

$templateBlockSize = 1000;
class object {
	protected $linkFile, $unitBin, $id, $attrList, $itemBlockSize;

	function __construct($id, $dat, $file) {
		$this->linkFile = $file;

		if (sizeof($dat) == 0) {
			echo 'Start a blank unit';
			$this->objDat = array_fill(1, 100, 0);
		} else {
			$this->objDat = $dat;
		}
		//echo 'Set as type '.gettype($this->objDat);
		$this->unitID = $id;

		$this->attrList = [];
		$this->attrList['xLoc'] = 1;
		$this->attrList['yLoc'] = 2;
		$this->attrList['icon'] = 3;
		$this->attrList['oType'] = 4;
		$this->attrList['owner'] = 5;
		$this->attrList['subType'] = 9;
		$this->attrList['lastUpdate'] = 10;

		$this->itemBlockSize = 100;
	}

	function get($desc) {
		if (array_key_exists($desc, $this->attrList)) {
			return $this->objDat[$this->attrList[$desc]];
		} else {
			echo 'Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
	}

	function set($desc, $val) {
		if (array_key_exists($desc, $this->attrList)) {
			//echo 'Found '.$desc.' use spot '.$this->attrList[$desc].'.  Type: '.gettype ($this->objDat);
			$this->objDat[$this->attrList[$desc]] = $val;
		}
	}

	function save($desc, $val) {
		if (array_key_exists($desc, $this->attrList)) {
			$this->saveItem($this->attrList[$desc], $val);
			$this->objDat[$this->attrList[$desc]] = $val;
		} else {
			return false;
		}
	}

	function saveItem($loc, $val) {
		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + $loc*4-4);
		fwrite($this->linkFile, pack('i', $val));
		echo 'ID: '.$this->unitID;
		echo 'Save '.$val.' at spot '.($this->unitID*$this->itemBlockSize + $loc*4-4);
	}


	function saveAll($file) {
		// Pack the char data
		$packStr = '';
		foreach ($this->objDat as $value) {
			$packStr.=pack('i', $value);
		}
		fseek($file, $this->unitID*100);
		$saveLen = fwrite($file, $packStr);
	}
}

class business extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['ownedObjects'] = 11;
		$this->attrList['money'] = 14;
		$this->attrList['laborSlot'] = 15;
	}
}

class factory extends object {
	public $resourceStores, $templateDat, $materialOrders, $tempList, $laborOffset;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['remainderTime'] = 14;
		$this->attrList['currentProd'] = 19; // which inventory item is being produced - NOT the product ID
		$this->attrList['currentRate'] = 20;
		$this->attrList['labor1'] = 21;
		$this->attrList['labor2'] = 22;
		$this->attrList['labor3'] = 23;
		$this->attrList['labor4'] = 24;
		$this->attrList['labor5'] = 25;
		$this->attrList['labor6'] = 26;
		$this->attrList['labor7'] = 27;
		$this->attrList['labor8'] = 28;
		$this->attrList['labor9'] = 29;
		$this->attrList['labor10'] = 30;
		$this->attrList['inputInv1'] = 31;
		$this->attrList['inputInv2'] = 32;
		$this->attrList['inputInv3'] = 33;
		$this->attrList['inputInv4'] = 34;
		$this->attrList['inputInv5'] = 35;
		$this->attrList['inputInv6'] = 36;
		$this->attrList['inputInv7'] = 37;
		$this->attrList['inputInv8'] = 38;
		$this->attrList['inputInv9'] = 39;
		$this->attrList['inputInv10'] = 40;
		$this->attrList['inputInv11'] = 41;
		$this->attrList['inputInv12'] = 42;
		$this->attrList['inputInv13'] = 43;
		$this->attrList['inputInv14'] = 44;
		$this->attrList['inputInv15'] = 45;
		$this->attrList['inputInv16'] = 46;
		$this->attrList['inputInv17'] = 47;
		$this->attrList['inputInv18'] = 48;
		$this->attrList['inputInv19'] = 49;
		$this->attrList['inputInv20'] = 50;
		$this->attrList['prodInv1'] = 51;
		$this->attrList['prodInv2'] = 52;
		$this->attrList['prodInv3'] = 53;
		$this->attrList['prodInv4'] = 54;
		$this->attrList['prodInv5'] = 55;

		$this->attrList['orderTime1'] = 56;
		$this->attrList['orderTime2'] = 59;
		$this->attrList['orderTime3'] = 62;
		$this->attrList['orderTime4'] = 65;
		$this->attrList['orderTime5'] = 68;
		$this->attrList['orderTime6'] = 71;
		$this->attrList['orderTime7'] = 74;
		$this->attrList['orderTime8'] = 77;
		$this->attrList['orderTime9'] = 80;
		$this->attrList['orderTime10'] = 83;

		$this->attrList['orderItem1'] = 57;
		$this->attrList['orderItem2'] = 60;
		$this->attrList['orderItem3'] = 63;
		$this->attrList['orderItem4'] = 66;
		$this->attrList['orderItem5'] = 69;
		$this->attrList['orderItem6'] = 72;
		$this->attrList['orderItem7'] = 75;
		$this->attrList['orderItem8'] = 78;
		$this->attrList['orderItem9'] = 81;
		$this->attrList['orderItem10'] = 84;

		$this->attrList['orderQty1'] = 58;
		$this->attrList['orderQty2'] = 61;
		$this->attrList['orderQty3'] = 64;
		$this->attrList['orderQty4'] = 67;
		$this->attrList['orderQty5'] = 70;
		$this->attrList['orderQty6'] = 73;
		$this->attrList['orderQty7'] = 76;
		$this->attrList['orderQty8'] = 79;
		$this->attrList['orderQty9'] = 82;
		$this->attrList['orderQty10'] = 85;

		$this->attrList['price1'] = 91;
		$this->attrList['price2'] = 92;
		$this->attrList['price3'] = 93;
		$this->attrList['price4'] = 94;
		$this->attrList['price5'] = 95;

		$inputIndex = 1;
		$inputInventoryIndex = 61;

		$this->laborOffset = 131;

		// Load template information
		global $templateBlockSize;
		fseek($file, $dat[9]*$templateBlockSize);
		$this->templateDat = unpack('i*', fread($file, $templateBlockSize));
		//print_r($tmpDat);

		$this->tempList['prod1'] = $this->templateDat[11];
		$this->tempList['prod2'] = $this->templateDat[12];
		$this->tempList['prod3'] = $this->templateDat[13];
		$this->tempList['prod4'] = $this->templateDat[14];
		$this->tempList['prod5'] = $this->templateDat[15];

		$this->resourceStores = [];
		for ($i=0; $i<20; $i++) {
			if ($this->templateDat[16+$i] > 0) array_push($this->resourceStores, $this->templateDat[16+$i], $this->objDat[31+$i]);
		}
	}

	function productionOptions() {
		return ([$this->tempList['prod1'], $this->tempList['prod2'], $this->tempList['prod3'], $this->tempList['prod4'], $this->tempList['prod5']]);
	}

	function inventoryOptions() {
	}

	function getTemp($desc) {
		if (isset($this->tempList[$desc])) return $this->tempList[$desc];
		else echo $desc.' not found in the template';
	}

	function updateStocks() {
		if ($this->get('currentProd') == 0) return;

		// load production requirements
		fseek($this->linkFile, $this->get('currentProd')*1000);
		$productInfo = unpack('i*', fread($this->linkFile, 200));

		echo 'required rsc:';
		print_r($productInfo);

		// NEED TO DETERMINE PRODUCT INDEX

		// Sort material requirements into the storage index for the factory
		//$referenceList = array_fill(0, 20, 0);
		echo 'Resources stores<br>';
		print_r($this->resourceStores);
		$rscSpots = [];
		for ($i=0; $i<sizeof($this->resourceStores); $i+=2) {
			$rscSpots[$this->resourceStores[$i]] = $i;
		}
		echo 'Resources spots<br>';
		print_r($rscSpots);
		$referenceList=[];
		for ($i=0; $i<10; $i++) { // i is the index of the resource required by the product
			if ($productInfo[$i+18] > 0) {
				echo 'Look for resource '.$productInfo[$i+18];
				for ($j=0; $j<sizeof($this->resourceStores); $j+=2) {  // j is the index of the storage location at the factory
					if ($this->resourceStores[$j] == $productInfo[$i+18]) {
						echo 'Resources spot '.$j.' (type '.$this->resourceStores[$j].') which has a stock of '.$this->resourceStores[$j+1].' has a usage rate of '.$productInfo[$i+28].'<br>';
						$referenceList[$j] = $productInfo[$i+28];  // Record the usage rate for the store location (units per item produced)
						break;
					}
				}
			}
		}
		echo 'referene list<br>';
		print_r($referenceList);

		// Load pending deliveries
		$now = time();
		$elapsed = $now - $this->get('lastUpdate');
		$deleteOrder = [];
		$events = [$this->get('lastUpdate'), 0, 0, $now, 0, 0];
		for ($i=0; $i<10; $i++) {
			echo 'Check '.$this->objDat[56+$i*3].'<br>';
			// if event has occured, load it then delete it
			if ($this->objDat[56+$i*3] <= $now) {
				array_push($events, $this->objDat[56+$i*3], $this->objDat[57+$i*3], $this->objDat[58+$i*3]);
				$this->objDat[56+$i*3] = 0;
				$this->objDat[57+$i*3] = 0;
				$this->objDat[58+$i*3] = 0;
			} else echo $this->objDat[56+$i*3].' > '.$now.'<br>';
		}
		echo 'List of events: ('.$now.')<br>';
		print_r($events);
		for ($i=0; $i<sizeof($events)/3; $i++) {
			$timeList[$i] = $events[$i*3];
		}
		array_push($events, $now, 0, 0);

		asort($timeList);
		$eventOrder = array_keys($timeList);
		$totalProduction = 0;
		echo 'List of tuimes: ('.$now.')<br>';
		print_r($timeList);
		echo 'List of events:<br>';
		print_r($eventOrder);
		if ($this->get('currentRate') == 0)		$this->set('currentRate', 1);
		for ($i=1; $i<sizeof($eventOrder); $i++) {
			$elapsed = $events[$eventOrder[$i]*3] - $events[$eventOrder[$i-1]*3];
			echo 'Elapsed: ('. $events[$eventOrder[$i]*3].' - '.$events[$eventOrder[$i-1]*3].' = )'.$elapsed.'<br>';

			// Check for limiting resource or time
			$checkQty = [];

			// Get max amount produced in time and save remainder time
			$checkQty[] = ($elapsed+$this->get('remainderTime'))/$this->get('currentRate');
			$this->set('remainderTime', $elapsed+$this->get('remainderTime')%$this->get('currentRate'));

			// Get max amount produced by each input
			for ($j=0; $j<sizeof($referenceList); $j++) {
				$checkQty[] = $this->resourceStores[$j*2+1]/$referenceList[$j];
			}

			$produced = min($checkQty);
			echo 'Produce '.$produced.' items';
			print_r($checkQty);
			for ($j=0; $j<sizeof($referenceList); $j++) {
				echo $this->resourceStores[$j*2+1].' - '.$produced*$referenceList[$j].'<Br>';
				$this->resourceStores[$j*2+1] -= $produced*$referenceList[$j];
			}

			// Add material from arrived order
			if ($events[$eventOrder[$i]*3+1] > 0) {
				$this->resourceStores[$rscSpots[$events[$eventOrder[$i]*3+1]]*2+1] += $events[$eventOrder[$i]*3+2];
				echo 'adjusted stores:';
				print_r($this->resourceStores);
			}
			$totalProduction += $produced;
		}

		//Find product index
		for ($i=0; $i<5; $i++){
			if ($this->templateDat[11+$i] == $this->get('currentProd')) {
				$productIndex = $i;
				break;
			}
		}

		// Record updated product stocks
		echo 'Add a total of '.$totalProduction.' at index '.$productIndex;
		$this->objDat[51+$productIndex] += $totalProduction;

		// Record updated input stocks
		for ($i=0; $i<sizeof($referenceList); $i++) {
			echo 'set store spot '.$i.' to a value of '.$this->resourceStores[$i*2+1];
			$this->objDat[31+$i] = $this->resourceStores[$i*2+1];
		}

		// Delete orders that have arrived - done above

		// Save updated information
		$this->set('lastUpdate', $now);
		echo 'final info:<br>';
		print_r($this->objDat);
		$this->saveAll($this->linkFile);

	}

	function materialOrders() {
		return array_slice($this->objDat, 55, 30);
	}

	function adjustLabor($laborSpot, $laborDat) {
		$packDat = pack('i*', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + ($this->laborOffset+$laborSpot*10)*4-4);
		fwrite($this->linkFile, $packDat);

		$this->objDat[$this->laborOffset+$laborSpot*10] = $laborDat[1];
		$this->objDat[$this->laborOffset+$laborSpot*10+1] = $laborDat[2];
		$this->objDat[$this->laborOffset+$laborSpot*10+2] = $laborDat[3];
		$this->objDat[$this->laborOffset+$laborSpot*10+3] = $laborDat[4];
		$this->objDat[$this->laborOffset+$laborSpot*10+4] = $laborDat[5];
		$this->objDat[$this->laborOffset+$laborSpot*10+5] = $laborDat[6];
		$this->objDat[$this->laborOffset+$laborSpot*10+6] = $laborDat[7];
		$this->objDat[$this->laborOffset+$laborSpot*10+7] = $laborDat[8];
		$this->objDat[$this->laborOffset+$laborSpot*10+8] = $laborDat[9];
		$this->objDat[$this->laborOffset+$laborSpot*10+9] = $laborDat[10];
	}
}

class city extends object {
	private $dRateOffset, $dLevelOffset, $cityBlockSize, $laborDemandOffset, $laborStoreOffset;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		// Total of 22250 items
		$this->dRateOffset = 250;
		$this->dLevelOffset = 10250;
		$this->laborDemandOffset = 20250;
		$this->laborStoreOffset = 21250;
		$this->cityBlockSize = 89000;
		$this->itemBlockSize = 89000;

		$this->attrList['population'] = 12;
		$this->attrList['affluence'] = 14;
		$this->attrList['baseTime'] = 15;
		$this->attrList['laborUpdateTime'] = 16;
		$this->attrList['schoolSlot'] = 17;
		$this->attrList['laborSlot'] = 18;
	}

	function demandRate($productID) {
		return $this->objDat[$this->dRateOffset+$productID];
		}

	function saveDRate($productID, $val) {
		fseek($this->linkFile, $this->unitID*$this->cityBlockSize + ($this->dLevelOffset+$productID)*4-4);
		fwrite($this->linkFile, pack('i', $val));
		echo 'ID: '.$this->unitID;
		echo 'Save '.$val.' at spot '.($this->unitID*$this->cityBlockSize + ($this->dLevelOffset+$productID)*4-4);
		$this->objDat[$this->dLevelOffset+$productID] = $val;
	}

	function demandLevel($productID) {
		return $this->objDat[$this->dLevelOffset+$productID];
	}

	function save($desc, $val) {

		if (array_key_exists($desc, $this->attrList)) {
			fseek($this->linkFile, $this->unitID*$this->cityBlockSize + $this->attrList[$desc]*4-4);
			fwrite($this->linkFile, pack('i', $val));
			echo 'ID: '.$this->unitID;
			echo 'Save '.$val.' at spot '.($this->unitID*$this->cityBlockSize + $this->attrList[$desc]*4-4);
			$this->objDat[$this->attrList[$desc]] = $val;
		} else {
			return false;
		}
	}

	function baseDemand($productNumber) {
		// production per million people per hour x 2 days
		return ($this->get('population')*$this->demandRate($productNumber)*48/1000000);
	}

	function currentDemand($productNumber, $now) {
		$elapsed = $now-$this->get('lastUpdate');
		return(min($elapsed*$this->demandRate($productNumber)/(3600*1000000)+$this->demandLevel($productNumber), 2.0*$this->baseDemand($productNumber)));
	}

	function updateLabor($now, $schoolList, $baseRates, $slotFile) {
		echo 'Number of schools: '.sizeof($schoolList->slotData);
		print_r($schoolList->slotData);
		$schoolTypes = array_fill(0, 100, 0);
		for ($i=1; $i<=sizeof($schoolList->slotData); $i++) {
			echo 'Add school type '.$schoolList->slotData[$i];
			$schoolTypes[$schoolList->slotData[$i]]++;
		}
		//echo 'School types:';
		//print_r($schoolTypes);
		$addList = [];
		for ($i=1; $i<sizeof($schoolTypes); $i++) {
			if ($schoolTypes[$i] > 0) {
				$thisSchool = new School($i);

				$newTime = $now - $this->get('baseTime');
				$pvsTime = $this->get('laborUpdateTime') - $this->get('baseTime');
				echo 'NEwtime:'.$newTime.' - PvsTime '.$pvsTime;

				foreach($thisSchool->schoolRates as $laborType => $trainRate) {
					$addAmt = $schoolTypes[$i]*(intval($newTime/$trainRate) - intval($pvsTime/$trainRate));
					$addList[$laborType] = $addAmt;
					echo 'Schools train '.$addAmt.' of type '.$laborType.' with a rate of '.$trainRate;
				}
			}
		}

		if ($this->get('laborSlot') == 0) {
			echo 'Make a new slot';
			$this->save('laborSlot', newSlot($slotFile));
		}
		echo 'Labor slot is '.$this->get('laborSlot');
		$laborSlot = new blockSlot($this->get('laborSlot'), $slotFile, 40);
		print_R($laborSlot->slotData);

		$emptySpots = [];
		for ($i=1; $i<sizeof($laborSlot->slotData); $i+=10) {
			if ($laborSlot->slotData[$i] == 0) $emptySpots[] = $i;
		}
		echo 'Empty Spots:';
		print_r($emptySpots);

		echo 'Add list:';
		print_r($addList);
		foreach ($addList as $laborID => $addAmount) {
			echo 'Add '.$addAmount.' of labor type '.$laborID;
			for ($n=0; $n<$addAmount; $n++) {
				$pay = intval($baseRates[$laborID]*$this->get('affluence')*rand(90,110)/(10000));
				echo 'Pay:'.$pay.' ('.$baseRates[$laborID].' * '.$this->get('affluence').')';
				$loc = array_pop($emptySpots);
				$dat = pack('i*', 1, $laborID, 0, 0, 0, $pay, $now, 0, 0, 0);  // education level, type, ability, start time, home region, expected pay, last update time, target upgrade
				//$dat = pack('i*', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);  // education level, type, ability, start time, home region, expected pay, last update time, target upgrade
				if (is_null($loc)) {
					echo 'Add to end';
					$laborSlot->addItem($slotFile, $dat, 0);
				} else {

					echo 'Add to loc '.$loc;
					$laborSlot->addItem($slotFile, $dat, $loc);
				}
			}
		}
	// Record updated time
	$this->save('laborUpdateTime', $now);
	return ($laborSlot);
	}

	function availableLabor() {
		return array_slice($this->objDat, $this->laborStoreOffset, 1000);
	}

	function saveLabor($id, $delta) {
		$loc = $this->laborStoreOffset + $id;
		$newVal = $this->objDat[$loc] + $delta;
		$this->saveItem($loc, $newVal);
	}

}

class product extends object {
	public $reqMaterials, $reqLabor;
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['numMaterial'] = 11;
		$this->attrList['numLabor'] = 12;
		$this->attrList['matReq1'] = 18;
		$this->attrList['matReq2'] = 19;
		$this->attrList['matReq3'] = 20;
		$this->attrList['matReq4'] = 21;
		$this->attrList['matReq5'] = 22;
		$this->attrList['matReq6'] = 23;
		$this->attrList['matReq7'] = 24;
		$this->attrList['matReq8'] = 25;
		$this->attrList['matReq9'] = 26;
		$this->attrList['matReq10'] = 27;
		$this->attrList['matQty1'] = 28;
		$this->attrList['matQty2'] = 29;
		$this->attrList['matQty3'] = 30;
		$this->attrList['matQty4'] = 31;
		$this->attrList['matQty5'] = 32;
		$this->attrList['matQty6'] = 33;
		$this->attrList['matQty7'] = 34;
		$this->attrList['matQty8'] = 35;
		$this->attrList['matQty9'] = 36;
		$this->attrList['matQty10'] = 37;

		$this->reqMaterials = [];
		$this->reqLabor = [];

		//print_r($this->objDat);

		for ($i=0; $i<10; $i++) {
			if ($this->objDat[18+$i] > 0) array_push($this->reqMaterials, $this->objDat[18+$i], $this->objDat[28+$i]);
			if ($this->objDat[38+$i] > 0) $this->reqLabor[] = $this->objDat[38+$i];
		}
	}
}

class labor extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);
	}
}

class factoryTemplate extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);
	}
}

class school {
	public $schoolRates;
	function __construct($schoolType) {
		switch($schoolType) {
			case 1:
				$this->schoolRates = array_fill(1, 4, 3600);
			break;
		}
	}
}


function loadProduct($id, $file, $size) {
	fseek($file, $id*1000);
	$dat = unpack('i*', fread($file, $size));

	return new product($id, $dat, $file);
}

function loadCity($id, $file) {
	fseek($file, $id*89000);
	$dat = unpack('i*', fread($file, 89000));

	return new city($id, $dat, $file);
}

function loadObject($id, $file, $size) {
	global $defaultBlockSize;
	//echo 'Seek to '.($id*$defaultBlockSize);

	fseek($file, $id*$defaultBlockSize);
	$dat = unpack('i*', fread($file, $size));
	//print_r($dat);
	switch($dat[4]) {
		case 1:
			return new business($id, $dat, $file);
		break;

		case 2:
			return new labor($id, $dat, $file);
		break;

		case 3:
			return new factory($id, $dat, $file);
		break;

		case 5:
			return new city($id, $dat, $file);
		break;

		case 7:
			return new factoryTemplate($id, $dat, $file);
		break;

		default:
			print_r($dat);
			exit('error '.$dat[4]);
		break;
	}

}

?>
