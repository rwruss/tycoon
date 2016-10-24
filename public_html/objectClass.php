<?php

class object {
	protected $linkFile, $unitBin, $id, $attrList;

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
		$this->attrList['lastUpdate'] = 10;
		/*


		$this->attrList['controller'] = 6;
		$this->attrList['status'] = 7;
		$this->attrList['culture'] = 8;
		$this->attrList['religion'] = 9;
		$this->attrList['troopType'] = 10;
		$this->attrList['currentTask'] = 11;
		$this->attrList['updateTime'] = 27;
		*/
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
			echo 'Found '.$desc.' use spot '.$this->attrList[$desc].'.  Type: '.gettype ($this->objDat);
			$this->objDat[$this->attrList[$desc]] = $val;
		}
	}

	function save($desc, $val) {
		global $defaultBlockSize;

		if (array_key_exists($desc, $this->attrList)) {
			fseek($this->linkFile, $this->unitID*$defaultBlockSize + $this->attrList[$desc]*4-4);
			fwrite($this->linkFile, pack('i', $val));
			echo 'ID: '.$this->unitID;
			echo 'Save '.$val.' at spot '.($this->unitID*$defaultBlockSize + $this->attrList[$desc]*4-4);
			$this->objDat[$this->attrList[$desc]] = $val;
		} else {
			return false;
		}
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
	}
}

class factory extends object {
	protected $resourceStores, $templateDat, $materialOrders;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->attrList['currentProd'] = 19; // which inventory item is being produced
		$this->attrList['currentRate'] = 20;
		$this->attrList['prodOpt1'] = 31;
		$this->attrList['prodOpt2'] = 32;
		$this->attrList['prodOpt3'] = 33;
		$this->attrList['prodOpt4'] = 34;
		$this->attrList['prodOpt5'] = 35;
		$this->attrList['inputInv1'] = 36;
		$this->attrList['inputInv2'] = 37;
		$this->attrList['inputInv3'] = 38;
		$this->attrList['inputInv4'] = 39;
		$this->attrList['inputInv5'] = 40;
		$this->attrList['inputInv6'] = 41;
		$this->attrList['inputInv7'] = 42;
		$this->attrList['inputInv8'] = 43;
		$this->attrList['inputInv9'] = 44;
		$this->attrList['inputInv10'] = 45;
		$this->attrList['inputInv11'] = 46;
		$this->attrList['inputInv12'] = 47;
		$this->attrList['inputInv13'] = 48;
		$this->attrList['inputInv14'] = 49;
		$this->attrList['inputInv15'] = 50;
		$this->attrList['inputInv16'] = 51;
		$this->attrList['inputInv17'] = 52;
		$this->attrList['inputInv18'] = 53;
		$this->attrList['inputInv19'] = 54;
		$this->attrList['inputInv20'] = 55;
		$this->attrList['invItem1'] = 61;
		$this->attrList['invItem2'] = 62;
		$this->attrList['invItem3'] = 63;
		$this->attrList['invItem4'] = 64;
		$this->attrList['invItem5'] = 65;
		$this->attrList['invItem6'] = 66;
		$this->attrList['invItem7'] = 67;
		$this->attrList['invItem8'] = 68;
		$this->attrList['invItem9'] = 69;
		$this->attrList['invItem10'] = 70;
		$this->attrList['invItem11'] = 71;
		$this->attrList['invItem12'] = 72;
		$this->attrList['invItem13'] = 73;
		$this->attrList['invItem14'] = 74;
		$this->attrList['invItem15'] = 75;
		$this->attrList['invItem16'] = 76;
		$this->attrList['invItem17'] = 77;
		$this->attrList['invItem18'] = 78;
		$this->attrList['invItem19'] = 79;
		$this->attrList['invItem20'] = 80;

		$inputIndex = 1;
		$inputInventoryIndex = 61;

		// Load template information
		fseek($file, $dat[9]*$defaultBlockSize);
		$this->templateDat = unpack('i*', fread($file, 200));

		$this->resourceStores = [];
		for ($i=0; $i<20; $i++) {
			array_push($this->resourceStores, $this->templateDat[16+$i], $this->objDat[31+$i]);
		}
	}

	function updateStocks() {
		// load production requirements
		fseek($this->linkFile, $this->get('currentProd')*$defaultBlockSize);
		$productInfo = unpack('i*', $this->linkFile, 200);

		// Sort material requirements into the storage index for the factory
		$referenceList = array_fill(0, 20, 0);
		for ($i=0; $i<10; $i++) { // i is the index of the resource required by the product
			for ($j=0; $j<20; $j++) {  // j is the index of the storage location at the factory
				if ($this->resourceStores[$j] == $productInfo[$i+18]) {
					$referenceList[$j] = $productInfor[$i+28];  // Record the usage rate for the store location (units per item produced)
					break;
				}
			}
		}

		// Load pending deliveries
		$now = time();
		$deleteOrder = [];
		$events = [$this->get('lastUpdate'), 0, 0];
		for ($i=0; $i<10; $i++) {
			if ($this->objDat[60+$i*3] <= $now) {
				array_push($events, $this->objDat[60+$i*3], $this->objDat[61+$i*3], $this->objDat[62+$i*3]);
				$this->objDat[60+$i*3] = 0;
				$this->objDat[61+$i*3] = 0;
				$this->objDat[62+$i*3] = 0;
			}
		}

		for ($i=0; $i<sizeof($events)/3; $i++) {
			$timeList[$i] = $events[$i*3];
		}
		array_push($events, $now, 0, 0);

		asort($timeList);
		$eventOrder = array_keys($timeList);
		$totalProduction = 0;
		for ($i=1; $i<sizeof($eventOrder); $i++) {
			$elpased = $events[$eventOrder[$i]*3] - $events[$eventOrder[$i-1]*3];

			// Check for limiting resource or time
			$checkQty = [];
			$checkQty[] = $elapsed/$this->get('currentRate');
			for ($i=0; $i<20; $i++) {
				$checkQty[] = $this->resourceStores[$i]/$referenceList[$i];
			}

			$produced = min($checkQt);
			for ($i=0; $i<20; $i++) {
				$this->resourceStores[$i] -= $produced*$referenceList[$i];
			}
			$totalProduction += $produced;

			// Add material from arrived order
			//$this->objDat[] +=
		}
		//Find product index
		for ($i=0; $i<5; $i++){
			if ($this->templateDat[11+$i] == $this->get('currentProd')) {
				$productIndex = $i;
				break;
			}
		}

		// Record updated product stocks and input stocks
		$this->objDat[51+$productIndex] += $totalProduction;
		for ($i=0; $i<20; $i++) {
			$this->objDat[31+$i] = $this->resourceStores[$i];
		}

		// Delete orders that have arrived - done above
		$this->set('lastUpdate', $now);
		$this->saveAll();
	}

	function materialOrders() {
		return array_slice($this->objDat, 56, 30);
	}
}

class product extends object {
	protected $reqMaterials, $reqLabor;
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

		default:
			exit('error '.$dat[4]);
		break;
	}

}

?>
