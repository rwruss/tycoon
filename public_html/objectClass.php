<?php

$templateBlockSize = 1000;
class object {
	protected $unitBin, $id, $attrList, $itemBlockSize;
	public $objDat, $linkFile;
	function __construct($id, $dat, $file) {
		$this->linkFile = $file;

		if (sizeof($dat) == 0) {
			//echo 'Start a blank unit';
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
		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + $loc*4-4);
		fwrite($this->linkFile, $str);
	}


	function saveAll($file = null) {
		// Pack the char data
		if ($file === null) $file = $this->linkFile;
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
		$this->attrList['msgSlot'] = 19;
		$this->attrList['deals'] = 40;

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


		$this->attrList['openOffers'] = 41;
		$this->attrList['contractList'] = 43;
		$this->attrList['openBids'] = 44;

		for ($i=0; $i<20; $i++) {
			$this->attrList['service'.$i] = 100+$i;
		}
	}
}

class factory extends object {
	public $resourceStores, $templateDat, $materialOrders, $tempList, $laborOffset, $productStores, $eqRateOffset, $inputCost, $inputPollution, $inputRights,
		$orderListStart, $padTaxOffset, $inputOffset, $productOffset, $productStats, $contractsOffset, $nextUpdate;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->inputCost = 82;  // offset to input material cost for each product
		$this->inputPollution = 98; // offset to input pollution level for each input
		$this->inputRights = 114; // offset to input rights level for each input
		$this->orderListStart = 52;
		$this->paidTaxOffset = 274;
		$this->inputOffset = 31; // offset to inventory for each input
		$this->inputQuality = 304; // offset to input quality level for each input
		$this->laborOffset = 131;
		$this->eqRateOffset = 264;
		$this->productOffset = 47; // offset to inventory slots for each product made at factory
		$this->productStats = 239; // offset to stats for each product made (quality, pollution, rights, material cost, labor cost)
		$this->laborCosts = 77;
		$this->contractsOffset = 320;
		$this->prodInv = 47;

		$this->attrList['factoryLevel'] = 1;
		$this->attrList['factoryStatus'] = 2;
		$this->attrList['constructCompleteTime'] = 3;
		$this->attrList['upgradeInProgress'] = 8;
		$this->attrList['region_3'] = 11;

		$this->attrList['totalSales'] = 12;
		$this->attrList['periodSales'] = 13;

		$this->attrList['remainderTime'] = 14;
		$this->attrList['prodLength'] = 15;
		$this->attrList['prodStart'] = 16;
		$this->attrList['prodQty'] = 17;

		$this->attrList['currentProd'] = 19; // Product ID that is currently being produced
		$this->attrList['initProdDuration'] = 20;
		$this->attrList['prodRate'] = 21;
		$this->attrList['region_1'] = 22;
		$this->attrList['region_2'] = 23;

		$this->attrList['industry'] = 24;
		$this->attrList['prodQuality'] = 25;
		$this->attrList['prodPollution'] = 26;
		$this->attrList['prodRights'] = 27;
		$this->attrList['prodCost'] = 28;
		$this->attrList['prodLaborCost'] = 29;

		/*
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
		*/

		$this->attrList['prodInv1'] = 47;
		$this->attrList['prodInv2'] = 48;
		$this->attrList['prodInv3'] = 49;
		$this->attrList['prodInv4'] = 50;
		$this->attrList['prodInv5'] = 51;

		$this->attrList['offer1'] = 231;
		$this->attrList['offer2'] = 232;
		$this->attrList['offer3'] = 233;
		$this->attrList['offer4'] = 234;
		$this->attrList['offer5'] = 235;
		$this->attrList['offer6'] = 236;
		$this->attrList['offer7'] = 237;
		$this->attrList['offer8'] = 238;

		$inputIndex = 1;
		$inputInventoryIndex = 61;

		// Load template information
		//echo 'load factory type '.$dat[9];
		global $templateBlockSize;
		fseek($file, $dat[9]*$templateBlockSize);
		$this->templateDat = unpack('i*', fread($file, $templateBlockSize));
		//print_r($this->templateDat);

		$this->tempList['prod1'] = $this->templateDat[11];
		$this->tempList['prod2'] = $this->templateDat[12];
		$this->tempList['prod3'] = $this->templateDat[13];
		$this->tempList['prod4'] = $this->templateDat[14];
		$this->tempList['prod5'] = $this->templateDat[15];

		$this->resourceStores = $this->resourceInv();

		$this->productStores[] = $this->objDat[47];
		$this->productStores[] = $this->objDat[48];
		$this->productStores[] = $this->objDat[49];
		$this->productStores[] = $this->objDat[50];
		$this->productStores[] = $this->objDat[51];
	}

	function overViewInfo() {
		$tmpA = [];

		$tmpA[0] = $this->get('subType');
		$tmpA[1] = $this->get('currentProd');
		$tmpA[2] = $this->get('prodRate');
		$tmpA[3] = $this->unitID;
		$tmpA[4] = $this->tempList['prod1'];
		$tmpA[5] = $this->tempList['prod2'];
		$tmpA[6] = $this->tempList['prod3'];
		$tmpA[7] = $this->tempList['prod4'];
		$tmpA[8] = $this->tempList['prod5'];
		$tmpA[9] = $this->get('prodInv1');
		$tmpA[10] = $this->get('prodInv2');
		$tmpA[11] = $this->get('prodInv3');
		$tmpA[12] = $this->get('prodInv4');
		$tmpA[13] = $this->get('prodInv5');
		$tmpA[14] = $this->nextUpdate;
		$tmpA[15] = $this->get('subType');

		return $tmpA;
	}

	function setProdRate($prodID, $thisProduct, $laborEqFile) {
		// Review labor affects
		$productionRate = 0;
		$productionItems = 0;

		// Check first 7 labor types at the factory
		for ($i=0; $i<7; $i++) {
			if ($thisProduct->objDat[38+$i] > 0) {
				print_r($this->objDat);
				echo 'Check labor type '.$this->objDat[$this->laborOffset+10*$i];
				fseek($laborEqFile, $this->objDat[$this->laborOffset+10*$i]*4000);
				$eqDat = unpack('i*', fread($laborEqFile, 80));
				print_r($eqDat);

				$eqArray = array_fill(0, 1000, 0);
				$eqArray[1] = $eqDat[1];
				$eqArray[2] = $eqDat[2];
				$eqArray[3] = $eqDat[3];
				$eqArray[4] = $eqDat[4];
				$eqArray[5] = $eqDat[5];
				$eqArray[6] = $eqDat[6];
				$eqArray[7] = $eqDat[7];
				$eqArray[8] = $eqDat[8];
				$eqArray[9] = $eqDat[19];
				$eqArray[10] = $eqDat[1];
				$eqArray[11] = $eqDat[1];
				$eqArray[12] = $eqDat[1];
				$eqArray[13] = $eqDat[1];
				$eqArray[14] = $eqDat[1];
				$eqArray[15] = $eqDat[1];
				$eqArray[16] = $eqDat[1];
				$eqArray[17] = $eqDat[1];
				$eqArray[18] = $eqDat[1];
				$eqArray[19] = $eqDat[1];
				$eqArray[20] = $eqDat[1];

				$effectiveRate = $eqArray[$this->objDat[$this->laborOffset+10*$i]]/10000;

				$workTime = max(1,$this->objDat[$this->laborOffset+10*$i]);
				//$laborLevel = log($workTime, 2.0)+1;
				$laborLevel = $workTime/36000;
				$productionRate += (0.5+$laborLevel)*$effectiveRate;
				echo 'Labor item '.($this->objDat[$this->laborOffset+10*$i]*4000).' rate is '.((0.5+$laborLevel)*$effectiveRate).' ->> (0.5 + '.$laborLevel.') * '.$effectiveRate;

				// Record labor eq rates
				$this->objDat[$this->eqRateOffset+$i] = $effectiveRate*10000;
				$productionItems++;
			}
		}

		$totalRate = intval($thisProduct->get('baseRate')*$productionRate/$productionItems*100);
		$this->set('prodRate', $totalRate);
		$this->saveAll($this->linkFile);
		return $totalRate;
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

	function updateStocks($orderDatFile) {
		$now = time();
		$saveFactory = false;

		// Update facility construction or upgrades as needed
		$constructDelta = $this->get('constructCompleteTime') - $now;
		if ($constructDelta <= 0 && $this->get('upgradeInProgress') > 0) {
			$this->set('factoryLevel', $this->get('upgradeInProgress'));
			$this->set('upgradeInProgress', 0);
			$saveFactory = true;
		} else $this->nextUpdate = $this->get('constructCompleteTime');

		// Sort material requirements into the storage index for the factory
		$rscSpots = [];
		for ($i=0; $i<sizeof($this->resourceStores)/2; $i++) {
			$rscSpots[$this->resourceStores[$i*2]] = $i;
		}

		// Check for pending material orders
		for ($i=0; $i<10; $i++) {
			if ($this->objDat[$this->orderListStart+$i] > 0) {
				fseek($orderDatFile, $this->objDat[$this->orderListStart+$i]);
				$orderDat = unpack('i*', fread($orderDatFile, 64));

				if ($orderDat[13] <= $now) { // order has arrived
					$this->objDat[$this->inputOffset+$rscSpots[$orderDat[11]]]+= $orderDat[1]; // adjust the material quantity
					$this->objDat[$this->inputCost + $rscSpots[$orderDat[11]]] += $orderDat[1]*$orderDat[2];  // adjust the inventory costs
					$this->objDat[$this->inputPollution + $rscSpots[$orderDat[11]]] += $orderDat[5]; // adjust the inventroy pollution
					$this->objDat[$this->inputRights + $rscSpots[$orderDat[11]]] += $orderDat[6]; // adjust the inventory rights
					$this->objDat[$this->inputQuality + $rscSpots[$orderDat[11]]] += $orderDat[4]; // adjust the inventory quality

					$thisFactory->objDat[$thisFactory->orderListStart+$i] = 0; // delete the reference to the order
					$saveFactory = true;
				} else $this->nextUpdate = min($this->nextUpdate, $orderDat[13]);
			}
		}

		// Update production and production statistics
		if ($this->get('prodStart') > 0) {
			if ($this->get('prodStart') + $this->get('prodLength') <= $now) {
				// Production is complete
				echo 'Update completed production for '.$this->get('prodQty').' of item '.$this->get('currentProd');

				//Find product index
				for ($i=0; $i<5; $i++){
					if ($this->templateDat[11+$i] == $this->get('currentProd')) {
						$productIndex = $i;
						break;
					}
				}

				$this->objDat[$this->productOffset+$productIndex] += $this->get('prodQty');
				$this->objDat[$this->productStats+$productIndex*5+0] += $this->get('prodQuality'); // product quality
				$this->objDat[$this->productStats+$productIndex*5+1] += $this->get('prodPollution'); // product Pollution
				$this->objDat[$this->productStats+$productIndex*5+2] += $this->get('prodRights'); // product Rights
				$this->objDat[$this->productStats+$productIndex*5+3] += $this->get('prodCost'); // product material cost
				$this->objDat[$this->productStats+$productIndex*5+4] += $this->get('prodLaborCost'); // product labor cost

				$this->set('prodStart', 0);

				// Update labor experience
				for ($i=0; $i<7; $i++) {
					//$this->objDat[$this->laborOffset+$i*10+7] += $this->objDat[15];
					$this->objDat[$this->laborOffset+$i*10+8] += intval($this->get('initProdDuration')*$this->objDat[$this->eqRateOffset+$i]/10000);
					echo 'Add '.intval($this->get('initProdDuration')*$this->objDat[$this->eqRateOffset+$i]/10000).' to exp. ->> '.$this->get('initProdDuration').' * '.$this->objDat[$this->eqRateOffset+$i];
				}

				$saveFactory = true;
			} else {
				$this->nextUpdate = min($this->nextUpdate, $this->get('prodStart') + $this->get('prodLength'));
			}
		}

		if ($saveFactory) $this->saveAll($this->linkFile);
	}

	function materialOrders() {
		return array_slice($this->objDat, 51, 10);
	}

	function adjustLabor($laborSpot, $laborDat) {
		$packDat = pack('i*', $laborDat[0], $laborDat[1], $laborDat[2], $laborDat[3], $laborDat[4], $laborDat[5], $laborDat[6], $laborDat[7], $laborDat[8], $laborDat[9]);

		$this->saveBlock($this->laborOffset+$laborSpot*10, $packDat);
		//fseek($this->linkFile, $this->unitID*$this->itemBlockSize + ($this->laborOffset+$laborSpot*10)*4-4);
		//fwrite($this->linkFile, $packDat);

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
}

class city extends object {
	private $dRateOffset, $dLevelOffset, $demandDat;
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
		$this->attrList['leader'] = 20;
		$this->attrList['nation'] = 21;

		$this->attrList['fileBaseSize'] = 22;

		$this->attrList['factoryList'] = 25;
		$this->attrList['money'] = 26;
		$this->attrList['cityLaborSlot'] = 27;

		$this->attrList['cTax'] = 31;
		$this->attrList['rTax'] = 32;
		$this->attrList['nTax'] = 33;
		$this->attrList['cLaw'] = 34;
		$this->attrList['rLaw'] = 35;
		$this->attrList['nLaw'] = 36;

	}

	function loadDemands() {
		fseek($this->linkFile, $this->get('fileBaseSize')+$this->id*1000);
		$this->demandDat = unpack('s*', fread($this->linkFile, 40000));
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
		$this->attrList['baseRate'] = 11;
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

class region extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);
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


function loadProduct($id, $file, $size) {
	fseek($file, $id*1000);
	$dat = unpack('i*', fread($file, $size));

	return new product($id, $dat, $file);
}

function loadCity($id, $file) {
	fseek($file, $id*1000);
	$dat = unpack('i*', fread($file, 1000));

	return new city($id, $dat, $file);
}

function loadRegion($id, $file) {
	fseek($file, $id*1000);
	$dat = unpack('i*', fread($file, 1000));

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
			exit('error '.$dat[4].' OCH');
		break;
	}

}

?>
