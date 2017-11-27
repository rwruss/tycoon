<?php

$templateBlockSize = 1000;
class object {
	protected $unitBin, $id, $attrList, $itemBlockSize;
	public $objDat, $linkFile;
	function __construct($id, $dat, $file) {
		$this->linkFile = $file;
		$this->unitID = $id;
		$this->attrList = [];

		if (strlen($dat) == 0) {
			$this->objDat = array_fill(1,100,0);
		}
		/*
		if (sizeof($dat) == 0) {
			//echo 'Start a blank unit';
			$this->objDat = array_fill(1, 100, 0);
		} else {
			$this->objDat = $dat;
		}
		//echo 'Set as type '.gettype($this->objDat);
		*/
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
			//echo 'Set '.$desc.' ('.$this->attrList[$desc].') to '.$val;
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
		//echo 'ID: '.$this->unitID;
		//echo 'Save '.$val.' at spot '.($this->unitID*$this->itemBlockSize + $loc*4-4);
	}

	function saveBlock($loc, $str) {
		fseek($this->linkFile, $this->unitID*$this->itemBlockSize + $loc-4);
		$checkVal = fwrite($this->linkFile, $str);
		//echo 'recoreded '.$checkVal.' of '.strlen($str).' at '.$loc;
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
		//echo "SAVED ".$saveLen." at location ".($this->unitID*$this->itemBlockSize).' with a block size of '.$this->itemBlockSize;
	}
}

class user extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);

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

		$this->itemBlockSize = 500;  // not sure if this needs to be anything other than 100
		$this->itemBlockSize = 100;
	}
}

class business extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);

		$this->attrList['ownedObjects'] = 11;
		$this->attrList['money'] = 14;
		$this->attrList['laborSlot'] = 15;
		$this->attrList['serviceSlot'] = 16;
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
		$this->attrList['openInvoices'] = 45;

		$this->attrList['shipmentLink'] = 46;
		$this->attrList['transportOptions'] = 47;
		$this->attrList['transportAccess'] = 48;

		for ($i=0; $i<20; $i++) {
			$this->attrList['service'.$i] = 100+$i;
		}
	}
}

class factory extends object {
	public $resourceStores, $templateDat, $materialOrders, $tempList, $laborOffset, $productStores, $eqRateOffset, $inputCost, $inputPollution, $inputRights,
		$orderListStart, $padTaxOffset, $inputOffset, $prodInv, $productStats, $contractsOffset, $nextUpdate, $offersOffset, $laborItems, $productionSpotQty;

	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', substr($dat, 0, 904));

		//$tmp_a1 = unpack('S*', substr($dat, 904, 260));
		//$tmp_a2 = unpack("C*", substr($dat, 1164, 130));

		$this->inputOffset = 31; // offset to inventory for each input
		$this->prodInv = 47;
		//$this->productOffset = 47; // offset to inventory slots for each product made at factory
		$this->orderListStart = 52;
		$this->laborCosts = 77;
		$this->inputCost = 82;  // offset to input material cost for each product
		$this->inputPollution = 98; // offset to input pollution level for each input
		$this->inputRights = 114; // offset to input rights level for each input

		$this->productionSpotQty = 130;
		$this->offersOffset = 131;
		$this->productStats = 139; // offset to stats for each product made (quality, pollution, rights, material cost, labor cost)
		$this->currentProductionOffset = 164;
		$this->currentProductionRateOffset = 169;
		$this->paidTaxOffset = 174;
		$this->inputQuality = 204; // offset to input quality level for each input
		$this->contractsOffset = 220;

		$this->laborOffset = 227;
		$this->productionQualityOffset = 1384;
		//$this->eqRateOffset = 164;

		$this->attrList['factoryLevel'] = 1;
		$this->attrList['factoryStatus'] = 2;
		$this->attrList['constStatus'] = 3;
		$this->attrList['upgradeInProgress'] = 8;
		$this->attrList['region_3'] = 11;

		$this->attrList['totalSales'] = 12;
		$this->attrList['periodSales'] = 13;

		$this->attrList['remainderTime'] = 14;
		$this->attrList['prodLength'] = 15;
		$this->attrList['prodStart'] = 16;
		$this->attrList['prodQty'] = 17;
		$this->attrList['upgradePrice'] = 18;

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

		$this->attrList['groupType'] = 30;

		$this->attrList['prodInv1'] = 47;
		$this->attrList['prodInv2'] = 48;
		$this->attrList['prodInv3'] = 49;
		$this->attrList['prodInv4'] = 50;
		$this->attrList['prodInv5'] = 51;

		$this->attrList['currentProd'] = 164; // Product ID that is currently being produced

		$this->attrList['offer1'] = 231;
		$this->attrList['offer2'] = 232;
		$this->attrList['offer3'] = 233;
		$this->attrList['offer4'] = 234;
		$this->attrList['offer5'] = 235;
		$this->attrList['offer6'] = 236;
		$this->attrList['offer7'] = 237;
		$this->attrList['offer8'] = 238;

		$this->attrList['polPerDay'] = 325;
		$this->attrList['rtsPerDay'] = 326;

		$inputIndex = 1;
		$inputInventoryIndex = 61;

		// Load template information
		//echo 'load factory type '.$this->objDat[9];
		global $templateBlockSize;
		fseek($file, $this->objDat[9]*$templateBlockSize);
		$this->templateDat = unpack('i*', fread($file, $templateBlockSize));

		if ($this->get('groupType') == 1) {
			$this->tempList['prod1'] = $this->templateDat[11];
			$this->tempList['prod2'] = $this->templateDat[12];
			$this->tempList['prod3'] = $this->templateDat[13];
			$this->tempList['prod4'] = $this->templateDat[14];
			$this->tempList['prod5'] = $this->templateDat[15];

			$this->productStores[] = $this->objDat[47];
			$this->productStores[] = $this->objDat[48];
			$this->productStores[] = $this->objDat[49];
			$this->productStores[] = $this->objDat[50];
			$this->productStores[] = $this->objDat[51];
		} else {

			// A mine or farm
			$this->tempList['prod1'] = $this->objDat[$this->currentProductionOffset+0];
			$this->tempList['prod2'] = $this->objDat[$this->currentProductionOffset+1];
			$this->tempList['prod3'] = $this->objDat[$this->currentProductionOffset+2];
			$this->tempList['prod4'] = $this->objDat[$this->currentProductionOffset+3];
			$this->tempList['prod5'] = $this->objDat[$this->currentProductionOffset+4];

			$this->productStores[] = $this->objDat[47];
			$this->productStores[] = $this->objDat[48];
			$this->productStores[] = $this->objDat[49];
			$this->productStores[] = $this->objDat[50];
			$this->productStores[] = $this->objDat[51];
		}

		$this->resourceStores = $this->resourceInv();

		$this->loadLabor($dat);
		$this->productionQuality = unpack('S*', substr($dat, 347, 12));
		//echo 'LABOR ITEMS<p>';
		//print_r($this->laborItems);
	}

	function adjustLabor($laborSpot, $laborItem) {
		$packDat = pack('i*', $laborDat[0], $laborDat[1], $laborDat[2], $laborDat[3], $laborDat[4], $laborDat[5], $laborDat[6], $laborDat[7], $laborDat[8], $laborDat[9]);

		//$this->saveBlock(($this->laborOffset+$laborSpot*10)*4, $packDat);

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
		//print_r($laborDat);
	}

	function adjProduct($prodIndex, $sentQual, $sentPol, $sentRights, $materialCost, $laborCost) {

		$this->objDat[$this->productStats + $prodIndex*5] -= $sentQual;
		$this->objDat[$this->productStats + $prodIndex*5+1] -= $sentPol;
		$this->objDat[$this->productStats + $prodIndex*5+2] -= $sentRights;
		$this->objDat[$this->productStats + $prodIndex*5+3] -= $materialCost;
		$this->objDat[$this->productStats + $prodIndex*5+4] -= $laborCost;
		/*
		echo '<p>adj '.($this->productStats + $prodIndex*5).' from '.$this->objDat[$this->productStats + $prodIndex*5].' by '.$sentQual;
		echo '<p>adj '.($this->productStats + $prodIndex*5+1).' from '.$this->objDat[$this->productStats + $prodIndex*5].' by '.$sentPol;
		echo '<p>adj '.($this->productStats + $prodIndex*5+2).' from '.$this->objDat[$this->productStats + $prodIndex*5].' by '.$sentRights;
		echo '<p>adj '.($this->productStats + $prodIndex*5+3).' from '.$this->objDat[$this->productStats + $prodIndex*5].' by '.$materialCost;
		echo '<p>adj '.($this->productStats + $prodIndex*5+4).' from '.$this->objDat[$this->productStats + $prodIndex*5].' by '.$laborCost;
		*/
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
		$tmpA[14] = 0; // next update ?
		$tmpA[15] = $this->get('subType');
		$tmpA[16] = $this->get('region_3');

		// add product parameters - material costs
		//print_r(array_slice($this->objDat, 239, 25));
		$tmpA = array_merge($tmpA, array_slice($this->objDat, $this->productStats, 25));

		return $tmpA;
	}

	function loadLabor($dat) {
		for ($i=0; $i<10; $i++) {
			$this->laborItems[$i] = new labor(substr($dat, 904+48*$i, 48), $this->linkFile);
		}
	}

	function saveLabor() {
		$str = '';
		for ($i=0; $i<10; $i++) {
			echo '<P>Pack labor '.$i.'<br>';
			//print_r($this->laborItems[$i]);
			$str .= $this->laborItems[$i]->packLabor();
		}
		//print_r($this->laborItems);
		$this->saveBlock($this->laborOffset*4, $str);
	}

	function saveProductionRates() {
		$prodStr = '';
		$qualStr = '';
		for ($i=0; $i<5; $i++) {
			//echo 'Save rate of '.$this->objDat[$this->currentProductionRateOffset+$i].' and quality of '.$this->productionQuality[$i+1].'<br>';
			$prodStr .= pack('i', $this->objDat[$this->currentProductionRateOffset+$i]);
			$qualStr .= pack('S', $this->productionQuality[$i+1]);
		}
		$this->saveBlock($this->currentProductionRateOffset*4, $prodStr);
		$this->saveBlock($this->productionQualityOffset*4, $qualStr);
	}

	function setProdRate($productionSpot = 0) {
		// calc total skills from labor force
		$skillLevels = array_fill(0, 256, 0);
		$skillModifiers = array_fill(0, 256, 0);
		$totalLaborSkill = 0;
		for ($i=0; $i<10; $i++) {

			if ($this->laborItems[$i]->laborDat[3] > 0) {
				//print_r($this->laborItems[$i]);
				for ($j=0; $j<10; $j++) {
					$skillLevels[$this->laborItems[$i]->laborDat[$j*2+9]] += $this->laborItems[$i]->laborDat[$j*2+10];
					$totalLaborSkill += $this->laborItems[$i]->laborDat[$j*2+10];;
				}
			}
		}

		// load the product information
		$prodDat = loadProduct($this->objDat[$this->currentProductionOffset+$productionSpot], $this->linkFile);
		echo '<p>Checkt product '.$this->objDat[$this->currentProductionOffset+$productionSpot];
		//print_r($prodDat);
		//print_r($prodDat->objDat);
		$totalProdSkill = 0;
		$skillsRequired = 0;
		for ($i=0; $i<20; $i++) {
			echo $totalProdSkill.' += '.$prodDat->objDat[$prodDat->skillRateOffset+$i].'<Br>';
			if ($prodDat->objDat[$prodDat->skillOffset+$i] > 0) {

				$totalProdSkill += $prodDat->objDat[$prodDat->skillRateOffset+$i];
				$skillsRequired++;
			}
		}
		if ($totalProdSkill == 0) $baseProduction = 1;
		else $baseProduction = ($totalLaborSkill/$totalProdSkill);
		echo '<p>Base production is '.$baseProduction.' ('.$totalLaborSkill.' / '.$totalProdSkill.')';

		if ($baseProduction > 0) {
			// get the % required for each skill
			$laborPcts = array_fill(0, $skillsRequired, 0);
			$totalPct = 0;
			for ($i=0; $i<$skillsRequired; $i++) {

				$laborPcts[$i] = $skillLevels[$prodDat->objDat[$prodDat->skillOffset+$i]]/($baseProduction * $prodDat->objDat[$prodDat->skillRateOffset+$i]);
				$totalPct += min(1, $laborPcts[$i])/$skillsRequired;
				//echo 'Skill '.$i.' pct is '.$laborPcts[$i].': '.$skillLevels[$prodDat->objDat[$prodDat->skillOffset+$i]].' / ('.$baseProduction.' * '.$prodDat->objDat[$prodDat->skillRateOffset+$i].')<br>';
			}

			$productionRate = floor($baseProduction*100 * $totalPct*100);
			echo '<br>Final production is '.$productionRate.' = '.$baseProduction.' * '.$totalPct;
			return [$productionRate, $totalPct];
		} else return [0,0];
	}

	function resourceInv() {
		//print_r(array_slice($this->templateDat, 15, 20));
		$tmp = [];
		for ($i=0; $i<16; $i++) {
			if ($this->templateDat[16+$i] > 0) array_push($tmp, $this->templateDat[16+$i], $this->objDat[31+$i]);
		}
		return $tmp;
	}

	function invStats() {
		return array_merge(array_slice($this->objDat, $this->inputCost, 15),
			array_slice($this->objDat, $this->inputPollution, 15),
			array_slice($this->objDat, $this->inputRights, 15),
			array_slice($this->objDat, $this->inputQuality, 15),
			array_slice($this->objDat, $this->inputPollution, 15));
	}

	function prodStats() {

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
		//echo 'update stocks';
		$now = time();
		$saveFactory = false;


		// Update facility construction or upgrades as needed

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

				$thisOrder = loadOffer($this->objDat[$this->orderListStart+$i], $orderDatFile);
				$orderDat = $thisOrder->objDat;

				if ($thisOrder->objDat[13] <= $now) { // order has arrived
					$this->objDat[$this->inputOffset+$rscSpots[$orderDat[11]]]+= $orderDat[1]; // adjust the material quantity
					$this->objDat[$this->inputCost + $rscSpots[$orderDat[11]]] += $orderDat[1]*$orderDat[2];  // adjust the inventory costs
					$this->objDat[$this->inputPollution + $rscSpots[$orderDat[11]]] += $orderDat[5]; // adjust the inventroy pollution
					$this->objDat[$this->inputRights + $rscSpots[$orderDat[11]]] += $orderDat[6]; // adjust the inventory rights
					$this->objDat[$this->inputQuality + $rscSpots[$orderDat[11]]] += $orderDat[4]; // adjust the inventory quality

					$this->objDat[$this->orderListStart+$i] = 0; // delete the reference to the order
					$saveFactory = true;
				} else $this->nextUpdate = min($this->nextUpdate, $orderDat[13]);
			}
		}

		// Update production and production statistics
		//echo 'Check for production updates ('.$this->get('prodStart').') -> ';
		if ($this->get('prodStart') > 0) {
			echo ($this->get('prodStart') + $this->get('prodLength')).' vs '.$now;
			if ($this->get('prodStart') + $this->get('prodLength') <= $now) {
				// Production is complete
				//echo 'Update completed production for '.$this->get('prodQty').' of item '.$this->get('currentProd');

				$totalProduction = $this->objDat[$this->currentProductionRateOffset] + $this->objDat[$this->currentProductionRateOffset+1] + $this->objDat[$this->currentProductionRateOffset+2] + $this->objDat[$this->currentProductionRateOffset+3] + $this->objDat[$this->currentProductionRateOffset+4];
				$productionPct[0] = $this->objDat[$this->currentProductionRateOffset]/$totalProduction;
				$productionPct[1] = $this->objDat[$this->currentProductionRateOffset+1]/$totalProduction;
				$productionPct[2] = $this->objDat[$this->currentProductionRateOffset+2]/$totalProduction;
				$productionPct[3] = $this->objDat[$this->currentProductionRateOffset+3]/$totalProduction;
				$productionPct[4] = $this->objDat[$this->currentProductionRateOffset+4]/$totalProduction;

				$skillMatrix = array_fill(0, 256, 0);
				for ($i=0; $i<5; $i++ ){
					if ($this->objDat[$this->currentProductionOffset+$i] > 0) {
						//$this->objDat[$this->prodInv+$i] += $this->get('prodQty');
						$this->objDat[$this->prodInv+$i] += $this->get('prodLength')*$this->objDat[$this->currentProductionRateOffset+$i];
						$this->objDat[$this->productStats+$i*5+0] += $this->get('prodQuality')*$productionPct[$i]; // product quality
						$this->objDat[$this->productStats+$i*5+1] += $this->get('prodPollution')*$productionPct[$i]; // product Pollution
						$this->objDat[$this->productStats+$i*5+2] += $this->get('prodRights')*$productionPct[$i]; // product Rights
						$this->objDat[$this->productStats+$i*5+3] += $this->get('prodCost')*$productionPct[$i]; // product material cost
						$this->objDat[$this->productStats+$i*5+4] += $this->get('prodLaborCost')*$productionPct[$i]; // product labor cost


						// load the product information
						fseek($this->linkFile, $this->objDat[$this->currentProductionOffset+$i]*1000);
						$thisProduct = new product($this->objDat[$this->currentProductionOffset+$i], fread($this->linkFile, 1000), $this->linkFile);

						// create the matrix of learning for the product
						echo 'ADD LEARNING TO LABOR';
						for ($z=0; $z<20; $z++) {
							$skillMatrix[$thisProduct->objDat[$thisProduct->skillOffset+$z]] += $thisProduct->objDat[$thisProduct->learnOffset+$z]*$productionPct[$i];
						}
					}
				}

				$this->set('prodStart', 0);
				// apply the matrix to each labor item working
				for ($i=0; $i<10; $i++) {
					echo '<p>Labor item '.$i.'<br>';
					for($j=0; $j<10; $j++) {
						// adjusted for shorter production times
						//$this->laborItems[$i]->laborDat[10+$j] += floor(($skillMatrix[$this->laborItems[$i]->laborDat[9+$j*2]]) * ($this->get('prodLength')/3600));
						echo $this->laborItems[$i]->laborDat[10+$j*2].' += '.$skillMatrix[$this->laborItems[$i]->laborDat[9+$j*2]].' * '.$this->get('prodLength').'/3600<br>';
						$this->laborItems[$i]->laborDat[10+$j*2] += floor(($skillMatrix[$this->laborItems[$i]->laborDat[9+$j*2]]) * (28800/3600));

					}

				}
				$this->saveLabor();
				$this->set('prodRate', $this->setProdRate());
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
}

class city extends object {
	private $dRateOffset, $dLevelOffset, $demandDat, $binDat, $supplyBlockSize;
	public $laborStoreOffset, $laborDemandOffset;

	function __construct($id, $dat, $file, $binDat) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);

		//echo 'Bin dat length of '.strlen($binDat);
		$this->binDat = $dat;
		$this->supplyBlockSize = 360000;
		$this->priceBlockSize = 40000;

		// Total of 22250 items
		$this->dRateOffset = 250;
		$this->dLevelOffset = 10250;
		//$this->laborDemandOffset = 20250;
		//$this->laborStoreOffset = 21250;
		$this->itemBlockSize = 81000; // not sure why this would need to be other than 100
		$this->itemBlockSize = 100;

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
		$this->attrList['pGDP'] = 23;

		$this->attrList['factoryList'] = 25;
		$this->attrList['money'] = 26;
		$this->attrList['cityLaborSlot'] = 27;

		$this->attrList['cTax'] = 31;
		$this->attrList['rTax'] = 32;
		$this->attrList['nTax'] = 33;
		$this->attrList['cLaw'] = 34;
		$this->attrList['rLaw'] = 35;
		$this->attrList['nLaw'] = 36;

		$this->attrList['pollutionAdj'] = 37;
		$this->attrList['rightsAdj'] = 38;
	}

	function updateSupply($productID, $adjustment, $supplyFile) {
		fseek($supplyFile, $this->supplyBlockSize*$this->unitID + $this->priceBlockSize + $productID * 32);
		$supplyInfo = unpack('i*', fread($supplyFile, 12));

		$now = time();
		$newSupply = $supplyInfo[2]-($now-$supplyInfo[1])*$supplyInfo[3]/3600 + $adjustment; //old supply - elapsed (hrs) * use rate + added supply

		fseek($supplyFile, $this->supplyBlockSize*$this->unitID + $this->priceBlockSize + $productID * 32);
		fwrite($supplyFile, pack('i*', $now, $newSupply));
	}

	function supplyLevel($productID, $supplyFile) {
		//return $this->objDat[$this->dLevelOffset+$productID];

		fseek ($supplyFile, $this->id * $this->supplyBlockSize + $this->priceBlockSize + $productID*32);
		$supplyDat = unpack('i3h/s10r', fread($supplyFile, 32));

		return $supplyDat;
	}

	function iPercentiles() {
		echo 'Load percentiles '.strlen($this->binDat);
		$pctArray = unpack('s*', substr($this->binDat, 596, 20));
		//$pctArray[0] = 0;
		//$pctArray[11] = 0;
		return $pctArray;
	}

	function prodDemand($prodID, $demandFile) {
		$objSize = 110000;
		fseek($demandFile, $this->id*$objSize);
		$tmpA = unpack('C', fread($demandFile, 100));
	}

	function save($desc, $val) {
		if (array_key_exists($desc, $this->attrList)) {
			$areaHeader = 730200;
			fseek($this->linkFile, $this->unitID*$this->itemBlockSize + $this->attrList[$desc]*4-4 + $areaHeader + $areaHeader);
			fwrite($this->linkFile, pack('i', $val));
			echo 'ID: '.$this->unitID;
			echo 'Save '.$val.' at spot '.($this->unitID*$this->itemBlockSize + $this->attrList[$desc]*4-4 + $areaHeader + $areaHeader);
			$this->objDat[$this->attrList[$desc]] = $val;
		} else {
			return false;
		}

	}

	function baseDemand($productNumber) {
		// production per million people per hour x 2 days
		echo '<br>:'.$this->get('population').' * '.$this->demandRate($productNumber).' *48 /1000000<br>';
		return ($this->get('population')*$this->demandRate($productNumber)*48/1000000);
	}

	function currentDemand($productNumber, $now) {
		$elapsed = $now-$this->get('lastUpdate');
		//return(min($elapsed*$this->demandRate($productNumber)/(3600*1000000)+$this->demandLevel($productNumber), 2.0*$this->baseDemand($productNumber)));
	}

	function changeLaborItem($spotNumber, $attrArray) {
		$datStr = pack('i*', $attrArray[0], $attrArray[1], $attrArray[2], $attrArray[3], $attrArray[4], $attrArray[5], $attrArray[6], $attrArray[7], $attrArray[8], $attrArray[9]);
		$fileOffset = ($this->laborStoreOffset+$spotNumber*10)*4;
		$this->saveBlock($fileOffset, $datStr);
	}

}



class product extends object {
	public $reqMaterials, $reqLabor, $skillOffset, $skillRateOffset, $learnOffset;
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		echo '<p>Loaded product '.$id.'<br>';
		echo $dat;
		$this->objDat = unpack('i*', $dat);

		$this->itemBlockSize = 100;
		$this->skillOffset = 38;
		$this->skillRateOffset = 58;
		$this->learnOffset = 78;

		$this->attrList['baseRate'] = 11;
		$this->attrList['prodType'] = 12;
		$this->attrList['unitWeight'] = 13;
		$this->attrList['unitVolume'] = 14;

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
			$this->reqLabor[] = $this->objDat[$this->skillOffset+$i];
		}
	}


	function productSkills() {
		$tmpArray = array_fill(0, 40, 0);
		echo 'PROD SKILLS';
		print_r($this->objDat);
		for ($i= 0; $i<20; $i++) {
			$tmpArray[$i] = $this->objDat[$this->skillOffset+$i];
			echo ($this->objDat[$this->skillRateOffset+$i]).'<br>';
			$tmpArray[$i+20] = $this->objDat[$this->skillRateOffset+$i];
		}
		print_r($tmpArray);
		return $tmpArray;
	}

	function prodSkillLearning() {
		$tmpArray = array_fill(0, 40, 0);
		for ($i=0; $i<20; $i++) {
			$tmpArray[$i] = $this->objDat[$this->skillOffset+$i];
			$tmpArray[$i+20] = $this->objDat[$this->learnOffset+$i];
		}
	}

}

class region extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);

		$this->attrList['money'] = 11;
		$this->attrList['pGDP'] = 12;
		$this->attrList['regionTaxSlot'] = 13;
		$this->attrList['nationTaxSlot'] = 14;
	}

	function regionPay() {
		return array_slice($this->objDat(10, 12));
	}
}

class labor {
	private $format, $binDat;
	public $laborDat;

	function __construct($dat) {
		//parent::__construct($id, $dat, $file);
		$this->format = "Na/Nb/Sc/Sd/Se/Cf/Cg/Ch/Ci/Sj/Ck/Sl/Cm/Sn/Co/Sp/Cq/Sr/Cs/St/Cu/Sv/Cw/Sx/Cy/Sz/Caa/Sab/Cac";
		$this->packFormat = "NNSSSCCCCSCSCSCSCSCSCSCSCSCSC";
		$this->binDat = $dat;

		$this->laborDat = array_values(unpack($this->format, $dat));
		//echo '<p>LABOR DAT:<p>';
		array_unshift($this->laborDat, 0);
		unset($this->laborDat[0]);
	}

	function packLabor() {
		//echo '<p>Pack this<p>';
		//print_r($this->laborDat);
		return pack($this->packFormat, ...$this->laborDat);
	}

	function clear() {
		foreach ($this->laborDat as &$value) {
			$value = 0;
		}
		unset($value);
	}
}

class factoryTemplate extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);
	}
}

class school {
	public $schoolRates;
	function __construct($schoolType) {
		$this->objDat = unpack('i*', $dat);
		switch($schoolType) {
			case 1:
				$this->schoolRates = array_fill(1, 4, 5);
			break;
		}
	}
}

class project extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);

		$this->attrList['owner'] = 1;
		$this->attrList['factoryID'] = 2;
		$this->attrList['factoryType'] = 3;
		$this->attrList['totalPoints'] = 4;
		$this->attrList['currPoints'] = 5;

		$this->attrList['status'] = 7;
		$this->attrList['contractID'] = 8;

		$this->attrList['nextProj'] = 24;
		$this->attrList['pvsProj'] = 25;
	}
}

class offer extends object {
	function __construct($id, $dat, $file) {
		parent::__construct($id, $dat, $file);

		$this->objDat = unpack('i*', $dat);

		$this->attrList['qty'] = 1;

		$this->attrList['product'] = 11;
		$this->attrList['buyer'] = 12;
		$this->attrList['deliverTime'] = 13;

		$this->attrList['weight'] = 19;
		$this->attrList['volume'] = 20;
	}
}


function newContract($contractDat, $contractFile) {

}

function loadProduct($id, $file) {
	fseek($file, $id*1000);

	$dat = fread($file, 1000);

	return new product($id, $dat, $file);
}

function loadCity($id, $file) {
	$areaHeader = 730200;
	fseek($file, $areaHeader+$id*81000);
	$binDat = fread($file, 1000);
	$dat = unpack('i*', $binDat);

	return new city($id, $binDat, $file, $binDat);
}

function loadCityDemands($id, $file) {
	$areaHeader = 730200;
	fseek($file, $areaHeader+$id*81000);
	$binDat = fread($file, 81000);
	$dat = unpack('i*', $binDat);

	return new city($id, $binDat, $file, $binDat);
}

function loadRegion($id, $file) {
	fseek($file, $id*200);
	//$dat = unpack('i*', fread($file, 200));
	$dat = fread($file, 200);

	return new region($id, $dat, $file);
}

function loadUser($id, $file) {
	fseek($file, $id*500);
	//$dat = unpack('i*', fread($file, 500));
	$dat = fread($file, 500);

	return new user($id, $dat, $file);
}

function loadProject($id, $file) {
	fseek($file, $id*100);
	//$dat = unpack('i*', fread($file, 100));
	$dat = fread($file, 100);

	return new project($id, $dat, $file);
}

function loadOffer($id, $file) {
	fseek($file, $id);
	//$dat = unpack('i*', fread($file, 100));
	$dat = fread($file, 100);

	return new offer($id, $dat, $file);
}

function loadLaborItem($id, $file) {
	//echo 'Load labor item '.$id;
	if ($id > 0) {
		fseek($file, $id);
		return new labor(fread($file, 48));
	} else {
		// return a blank labor item
		return new labor (pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
	}
}

function clearLaborItem($id, $file) {
	fseek($file, $id);
	$emptyA = [0,0,0,0,0,0,0,0,0,0,0,0];
	fwrite($file, packArray($emptyA));
}

function loadObject($id, $file, $size) {
	global $defaultBlockSize;
	//echo 'Seek to '.($id*$defaultBlockSize);

	fseek($file, $id*$defaultBlockSize);
	$binDat = fread($file, $size);
	$dat = unpack('i*', substr($binDat, 0, 16));
	//print_r($dat);
	switch($dat[4]) {
		case 1:
			return new business($id, $binDat, $file);
		break;
		/*
		case 2:
			return new labor($id, $binDat, $file);
		break;
		*/
		case 3:
			return new factory($id, $binDat, $file);
		break;

		case 5:
			return new city($id, $binDat, $file);
		break;

		case 7:
			return new factoryTemplate($id, $binDat, $file);
		break;

		default:
			print_r($dat);
			exit('error '.$dat[4].' OCH 973');
		break;
	}

}

function packArray($data, $format = 'i') {
	reset($data);
	$z = current($data);
	//echo 'pack ('.$format.') '.$z.'<br>';
	$str = pack($format, $z);
	for ($i=1; $i<sizeof($data); $i++) {
		$z = next($data);
		$str = $str.pack($format, $z);
		//echo 'pack ('.$format.')'.$z.' - Length: '.strlen($str).'<br>';
	}
	//echo 'Return '.strlen($str);
	return $str;
}

?>
