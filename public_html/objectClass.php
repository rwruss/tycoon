<?php

$templateBlockSize = 1000;
class object {
	protected $unitBin, $id, $attrList, $itemBlockSize;
	public $objDat, $linkFile;
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
			echo 'DESC: "'.$desc.'" Not found in type '.$this->objDat[4].' ('.$desc.')';
			return false;
		}
	}

	function set($desc, $val) {
		if (array_key_exists($desc, $this->attrList)) {
			//echo 'Found '.$desc.' use spot '.$this->attrList[$desc].'.  Type: '.gettype ($this->objDat);
			$this->objDat[$this->attrList[$desc]] = $val;
			echo 'Set '.$desc.' ('.$this->attrList[$desc].') to '.$val;
		}
	}

	function adjVal($desc, $incr) {
		if (array_key_exists($desc, $this->attrList)) {
			//echo 'Found '.$desc.' use spot '.$this->attrList[$desc].'.  Type: '.gettype ($this->objDat);
			$this->objDat[$this->attrList[$desc]] += $incr;
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
		$this->objDat[$loc] = $val;
		echo 'ID: '.$this->unitID;
		echo 'Save '.$val.' at spot '.($this->unitID*$this->itemBlockSize + $loc*4-4);
	}

	function saveBlock($loc, $str) {
		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + $loc*4);
		fwrite($this->linkFile, $str);
	}


	function saveAll($file) {
		// Pack the char data
		$packStr = '';
		foreach ($this->objDat as $value) {
			$packStr.=pack('i', $value);
		}
		fseek($file, $this->unitID*$this->itemBlockSize);
		$saveLen = fwrite($file, $packStr);
		echo "SAVED ".$saveLen." at location ".($this->unitID*$this->itemBlockSize);
	}
}

class user extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['lastLogin'] = 1;
		$this->attrList['gold'] = 2;

		$this->attrList['boost0'] = 51;
		$this->attrList['boost1'] = 52;
		$this->attrList['boost2'] = 53;
		$this->attrList['boost3'] = 54;
		$this->attrList['boost4'] = 55;
		$this->attrList['boost5'] = 56;
		$this->attrList['boost6'] = 57;
		$this->attrList['boost7'] = 58;
		$this->attrList['boost8'] = 59;
		$this->attrList['boost9'] = 60;
		$this->attrList['boost10'] = 61;
		$this->attrList['boost11'] = 62;
		$this->attrList['boost12'] = 63;
		$this->attrList['boost13'] = 64;
		$this->attrList['boost14'] = 65;
		$this->attrList['boost15'] = 66;
		$this->attrList['boost16'] = 67;
		$this->attrList['boost17'] = 68;
		$this->attrList['boost18'] = 69;
		$this->attrList['boost19'] = 70;

		$this->itemBlockSize = 500;
	}
}

class business extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['ownedObjects'] = 11;
		$this->attrList['money'] = 14;
		$this->attrList['laborSlot'] = 15;
		$this->attrList['teamID'] = 17;

		$this->attrList['boost1'] = 20;
		$this->attrList['boost2'] = 21;
		$this->attrList['boost3'] = 22;
		$this->attrList['boost4'] = 23;
		$this->attrList['boost5'] = 24;
		$this->attrList['boost6'] = 25;
		$this->attrList['boost7'] = 26;
		$this->attrList['boost8'] = 27;
		$this->attrList['boost9'] = 28;
		$this->attrList['boost10'] = 29;

		for ($i=0; $i<20; $i++) {
			$this->attrList['service'.$i] = 100+$i;
		}
	}
}

class factory extends object {
	public $resourceStores, $templateDat, $materialOrders, $tempList, $laborOffset, $productStores;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['factoryLevel'] = 1;
		$this->attrList['factoryStatus'] = 2;
		$this->attrList['constructCompleteTime'] = 3;
		$this->attrList['upgradeInProgress'] = 8;
		$this->attrList['region'] = 11;
		$this->attrList['totalSales'] = 12;
		$this->attrList['periodSales'] = 13;

		$this->attrList['remainderTime'] = 14;
		$this->attrList['prodLength'] = 15;
		$this->attrList['prodStart'] = 16;
		$this->attrList['prodQty'] = 17;

		$this->attrList['currentProd'] = 19; // Product ID that is currently being produced
		$this->attrList['initProdDuration'] = 20;
		$this->attrList['prodRate'] = 21;

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

		$this->resourceStores = $this->resourceInv();

		$this->productStores[] = $this->objDat[51];
		$this->productStores[] = $this->objDat[52];
		$this->productStores[] = $this->objDat[53];
		$this->productStores[] = $this->objDat[54];
		$this->productStores[] = $this->objDat[55];
	}

	function resourceInv() {
		//print_r(array_slice($this->templateDat, 15, 20));
		$tmp = [];
		for ($i=0; $i<20; $i++) {
			if ($this->templateDat[16+$i] > 0) array_push($tmp, $this->templateDat[16+$i], $this->objDat[31+$i]);
		}
		return $tmp;
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
		$now = time();
		$saveFactory = false;

		// Update facility construction or upgrades as needed
		$constructDelta = $this->get('constructCompleteTime') - $now;
		if ($constructDelta <= 0 && $this->get('upgradeInProgress') > 0) {
			$this->set('factoryLevel', $this->get('upgradeInProgress'));
			$this->set('upgradeInProgress', 0);
			$saveFactory = true;
		}

		// Sort material requirements into the storage index for the factory

		$rscSpots = [];

		for ($i=0; $i<sizeof($this->resourceStores)/2; $i++) {
			$rscSpots[$this->resourceStores[$i*2]] = $i;
			//print_r($rscSpots);
		}

		//order info : time, resource type #, qty
		for ($i=0; $i<10; $i++) {
			if ($this->objDat[56+$i*3] <= $now && $this->objDat[56+$i*3] != 0) {

				// Add material from arrived order
					//$this->resourceStores[$rscSpots[$this->objDat[56+$i*3+1]]] += $this->objDat[56+$i*3+2];
					echo 'adjusted stores: Item: '.$this->objDat[56+$i*3+1].' (Spot '.$rscSpots[$this->objDat[56+$i*3+1]].') + '.$this->objDat[56+$i*3+2].'<>';
					//echo 'New qty at 31 + '.$rscSpots[$this->objDat[56+$i*3+1]].':'.$this->objDat[31+$rscSpots[$this->objDat[56+$i*3+1]]];
					$this->objDat[31+$rscSpots[$this->objDat[56+$i*3+1]]] += $this->objDat[56+$i*3+2];
					$this->objDat[56+$i*3] = 0;
					$this->objDat[57+$i*3] = 0;
					$this->objDat[58+$i*3] = 0;

					$this->productStores[0] = $this->objDat[51];
					$this->productStores[1] = $this->objDat[52];
					$this->productStores[2] = $this->objDat[53];
					$this->productStores[3] = $this->objDat[54];
					$this->productStores[4] = $this->objDat[55];

					$saveFactory = true;

			} else {
				//echo $this->objDat[56+$i*3].' > '.$now.'<br>';
			}
		}

		// Update production
		if ($this->get('prodStart') + $this->get('prodLength') <= $now && $this->get('prodStart') > 0) {
			// Production is complete
			//echo 'Update completed production';

			//Find product index
			for ($i=0; $i<5; $i++){
				if ($this->templateDat[11+$i] == $this->get('currentProd')) {
					$productIndex = $i;
					break;
				}
			}
			$this->objDat[51+$productIndex] += $this->get('prodQty');

			$this->set('prodStart', 0);

			// Update labor experience
			for ($i=0; $i<10; $i++) {
				$this->objDat[$this->laborOffset+$i*10+7] += $this->objDat[15];
			}

			$saveFactory = true;
		} else {
			//echo $this->get('prodStart').' + '.$this->get('prodLength').' > '.$now;
		}

		if ($saveFactory) $this->saveAll($this->linkFile);
	}

	function materialOrders() {
		return array_slice($this->objDat, 55, 30);
	}

	function adjustLabor($laborSpot, $laborDat) {
		$packDat = pack('i*', $laborDat[0], $laborDat[1], $laborDat[2], $laborDat[3], $laborDat[4], $laborDat[5], $laborDat[6], $laborDat[7], $laborDat[8], $laborDat[9]);

		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + ($this->laborOffset+$laborSpot*10)*4-4);
		fwrite($this->linkFile, $packDat);

		$this->objDat[$this->laborOffset+$laborSpot*10] = $laborDat[0];
		$this->objDat[$this->laborOffset+$laborSpot*10+1] = $laborDat[1];
		$this->objDat[$this->laborOffset+$laborSpot*10+2] = $laborDat[2];
		$this->objDat[$this->laborOffset+$laborSpot*10+3] = $laborDat[3];
		$this->objDat[$this->laborOffset+$laborSpot*10+4] = $laborDat[4];
		$this->objDat[$this->laborOffset+$laborSpot*10+5] = $laborDat[5];
		$this->objDat[$this->laborOffset+$laborSpot*10+6] = $laborDat[6];
		$this->objDat[$this->laborOffset+$laborSpot*10+7] = $laborDat[7];
		$this->objDat[$this->laborOffset+$laborSpot*10+8] = $laborDat[8];
		$this->objDat[$this->laborOffset+$laborSpot*10+9] = $laborDat[9];

		echo 'Write data:';
		print_r($laborDat);
	}
	/*
	function updateProductionRate() {
		global $gameID;
		$scnNum = $_SESSION['game_'.$gameID]['scenario'];
		// Load product currently being produced
		echo 'Read from '.($this->get('currentProd')*1000);
		fseek($this->linkFile, $this->get('currentProd')*1000);
		$productInfo = unpack('i*', fread($this->linkFile, 1000));
		$baseRate = $productInfo[11];

		// Adjust for labor experience and equivalencies
		$laborEqFile = fopen('../scenarios/'.$scnNum.'/laborEq.dat', 'rb');
		$totalLaborWeight = 0;
		$laborPoints = 0;
		echo 'Current labor:<p>';
		print_r($productInfo);
		for ($i=0; $i<10; $i++) {

			$totalLaborWeight += $productInfo[48+$i];
			//fseek($laborEqFile, $productInfo[38+$i]*4000+$this->objDat[$this->laborOffset+$i*10]*4);
			fseek($laborEqFile, $productInfo[38+$i]*4000);
			$eq = unpack('i*', fread($laborEqFile, 400));
			print_r($eq);
			//echo 'Item '.$productInfo[38+$i].' at ('.($productInfo[38+$i]*4000).') for '.$this->objDat[$this->laborOffset+$i*10].' which has a value of '.$eq[1+$this->objDat[$this->laborOffset+$i*10]];

			$checkType = $this->objDat[$this->laborOffset+$i*10+1];
			$skillMultiplier = pow(1.1, intval($this->objDat[$this->laborOffset+$i*10+8]/518400));
			echo 'Points for type ('.$checkType.') = '.$eq[1+$checkType].' * '.$productInfo[48+$i].' * '.$skillMultiplier.'<p>';
			$laborPoints += $eq[1+$checkType]*$productInfo[48+$i]*$skillMultiplier;
		}
		fclose($laborEqFile);

		// Save the new result
		$newRate = intval($laborPoints/max($totalLaborWeight, 1.0)); // override
		echo 'Save new rate of '.$newRate.' ('.$laborPoints.'/'.$totalLaborWeight.')';
		$this->save('currentRate', $newRate);
	}*/
}

class city extends object {
	private $dRateOffset, $dLevelOffset;
	public $laborStoreOffset, $laborDemandOffset;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		// Total of 22250 items
		$this->dRateOffset = 250;
		$this->dLevelOffset = 10250;
		$this->laborDemandOffset = 20250;
		$this->laborStoreOffset = 21250;
		$this->itemBlockSize = 105000;

		$this->attrList['population'] = 12;
		$this->attrList['affluence'] = 14;
		$this->attrList['baseTime'] = 15;
		$this->attrList['laborUpdateTime'] = 16;
		$this->attrList['schoolSlot'] = 17;
		$this->attrList['laborSlot'] = 18;
		$this->attrList['parentRegion'] = 19;
	}

	function demandRate($productID) {
		return $this->objDat[$this->dRateOffset+$productID];
		}

	function saveDRate($productID, $val) {
		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + ($this->dLevelOffset+$productID)*4-4);
		fwrite($this->linkFile, pack('i', $val));
		echo 'ID: '.$this->unitID;
		echo 'Save '.$val.' at spot '.($this->unitID*$this->itemBlockSize + ($this->dLevelOffset+$productID)*4-4);
		$this->objDat[$this->dLevelOffset+$productID] = $val;
	}

	function demandLevel($productID) {
		return $this->objDat[$this->dLevelOffset+$productID];
	}

	function save($desc, $val) {

		if (array_key_exists($desc, $this->attrList)) {
			fseek($this->linkFile, $this->unitID*$this->itemBlockSize + $this->attrList[$desc]*4-4);
			fwrite($this->linkFile, pack('i', $val));
			echo 'ID: '.$this->unitID;
			echo 'Save '.$val.' at spot '.($this->unitID*$this->itemBlockSize + $this->attrList[$desc]*4-4);
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

	function updateLabor($now, $schoolList, $baseRates) {

		if ($now - $this->get('laborUpdateTime') < 60) {
			echo "no uptade";
			return;
		}
		/*
		echo 'Number of schools: '.sizeof($schoolList->slotData);
		print_r($schoolList->slotData);
		$schoolTypes = array_fill(0, 100, 0);
		for ($i=1; $i<=sizeof($schoolList->slotData); $i++) {
			echo 'Add school type '.$schoolList->slotData[$i];
			$schoolTypes[$schoolList->slotData[$i]]++;
		}

		$addList = [];
		for ($i=1; $i<sizeof($schoolTypes); $i++) {
			if ($schoolTypes[$i] > 0) {
				$thisSchool = new School($i);
				$elapsed = $now - $this->get('laborUpdateTime');

				if ($elapsed > 14400) {
					// reset all labor
					for ($i=0; $i<1000; $i++) {
						$this->objDat[$this->laborStoreOffset+$i] = 0;
					}

					$this->set('laborUpdateTime', $now);
					foreach($thisSchool->schoolRates as $laborType => $trainRate) {
						$addAmt = min(10, $trainRate);
						$addList[$laborType] = $addAmt;
						echo 'Schools train '.$addAmt.' of type '.$laborType.' with a rate of '.$trainRate;
					}
				}
			}
		}
		*/
		// overwrite for different types
		$addList[1] = 3;
		$addList[2] = 3;
		$addList[3] = 3;
		$addList[4] = 3;
		$addList[5] = 3;
		$addList[6] = 3;
		$addList[7] = 3;
		$addList[8] = 3;
		$addList[9] = 3;
		$addList[10] = 3;
		$addList[11] = 3;
		$addList[12] = 3;
		$addList[13] = 3;

		$laborCount = 0;
		foreach ($addList as $laborID => $addAmount) {
			echo 'Add '.$addAmount.' of labor type '.$laborID;
			for ($n=0; $n<$addAmount; $n++) {
				$pay = intval($baseRates[$laborID]*$this->get('affluence')*rand(90,110)/(10000));
				echo 'Pay:'.$pay.' ('.$baseRates[$laborID].' * '.$this->get('affluence').')';

				//$dat = pack('i*', 1, $laborID, 0, 0, 0, $pay, $now, 0, 0, 0);  , , ,  ,  , ,  ,
				//$dat = pack('i*', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);  // education level, type, ability, start time, home region, expected pay, last update time, target upgrade
				if ($laborCount > 1000) {
					echo 'no more space ';
					break 2;
				} else {
					$laborCount++;
					$offset = $this->laborStoreOffset+$laborCount*10+1;
					echo 'Add to loc '.$laborCount;
					$this->objDat[$offset] = 1; // education level
					$this->objDat[$offset+1] = $laborID; // type
					$this->objDat[$offset+2] = 0; // open spot
					$this->objDat[$offset+3] = $now; // start/creation time
					$this->objDat[$offset+4] = 0; // job start time
					$this->objDat[$offset+5] = $pay; // pay
					$this->objDat[$offset+6] = $now; // last update time
					$this->objDat[$offset+7] = 0; // home
					$this->objDat[$offset+8] = 0; // ability
					$this->objDat[$offset+9] = 0; // target upgrade
				}
			}
		}

	$this->set('laborUpdateTime', $now);
	$this->saveAll($this->linkFile);
	}

	function changeLaborItem($spotNumber, $attrArray) {
		$datStr = pack('i*', $attrArray[0], $attrArray[1], $attrArray[2], $attrArray[3], $attrArray[4], $attrArray[5], $attrArray[6], $attrArray[7], $attrArray[8], $attrArray[9]);
		$fileOffset = $this->laborStoreOffset+$spotNumber*10;
		$this->saveBlock($fileOffset, $datStr);
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
			//if ($this->objDat[38+$i] > 0) $this->reqLabor[] = $this->objDat[38+$i];
			$this->reqLabor[] = $this->objDat[38+$i];
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
				$this->schoolRates = array_fill(1, 4, 5);
			break;
		}
	}
}

class region extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

	$this->attrList['money'] = 10;
	$this->attrList['internalTax'] = 11;
	$this->attrList['externalTax'] = 12;
	$this->attrList['tarrif'] = 13;
	}
}


function loadProduct($id, $file, $size) {
	fseek($file, $id*1000);
	$dat = unpack('i*', fread($file, $size));

	return new product($id, $dat, $file);
}

function loadCity($id, $file) {
	fseek($file, $id*105000);
	$dat = unpack('i*', fread($file, 105000));

	return new city($id, $dat, $file);
}

function loadRegion($id, $file) {
	fseek($file, $id*105000);
	$dat = unpack('i*', fread($file, 105000));

	return new region($id, $dat, $file);
}

function loadUser($id, $file) {
	fseek($file, $id*500);
	$dat = unpack('i*', fread($file, 500));

	return new user($id, $dat, $file);
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
