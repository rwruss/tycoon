<?php

/*
general script for calculating taxes on transactions between two factories
*/

function calcTaxes($slotData, $thisInfo, &$taxList) { // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
	for ($i=11; $i<sizeof($slotData); $i+=4) {
    echo $thisInfo[$slotData[$i]].' vs '.$slotData[$i+2].' --> ';
		if ($thisInfo[$slotData[$i]] == $slotData[$i+2]) {
      echo 'adjust tax type '.$slotData[$i+1].' by '.$slotData[$i+3].' ('.$thisInfo[$slotData[$i]].' == '.$slotData[$i+2].')';
			$taxList[$slotData[$i+1]] += $slotData[$i+3];
		}
	}
}

function taxRates($transDat, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile) {
	/*
	TRANS DAT START INDEX IS 1
	$transDat[1] = $sentQty;
	$transDat[2] = $contractInfo[16]; // accepted price
	$transDat[3] = $postVals[1]; // selling factory ID
	$transDat[5] = $sentPol; // pollution
	$transDat[6] = $sentRights; // rights
	$transDat[14] = $materialCost;
	$transDat[15] = $laborCost;
	$transDat = [sent quantity, unit price, selling factory ID, sent pollution, sent rights, material cost, labor cost]
	$sellingFactory = selling factory object
	$buyingCity =
	$sellingCity =
	$sellingPlayer =
	$slotFile =
	*/

  	// [0, company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID, city ID, region ID, nation ID]
  	$taxInfo = [0, $sellingFactory->get('owner'), $sellingFactory->get('subType'), $sellingFactory->get('industry'), $transDat[3],
  		$sellingPlayer->get('teamID'), $sellingFactory->get('region_3'), $sellingFactory->get('region_2'), $sellingFactory->get('region_1')];

  	// Calculate import/tarrif taxes for the buyer
  	$importTaxEx = new itemSlot($buyingCity->get('nTax'), $slotFile, 40);
    $importTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];
  	$taxes[29] = $importTaxEx->slotData[9];
  	calcTaxes($importTaxEx->slotData, $taxInfo, $taxes);

		$taxes = array_fill(0,31,0);

		$cityTaxEx = new itemSlot($sellingCity->get('cTax'), $slotFile, 40);
  	$regionTaxEx = new itemSlot($sellingCity->get('rTax'), $slotFile, 40);
  	$nationTaxEx = new itemSlot($sellingCity->get('nTax'), $slotFile, 40);

  	// override taxes for testing
  	$cityTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10,1,1,460,-10];
  	$regionTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];
  	$nationTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];

  	for ($i=1; $i<11; $i++) {
  		$taxes[$i] = $cityTaxEx->slotData[$i];
  		$taxes[$i+10] = $regionTaxEx->slotData[$i];
  		$taxes[$i+20] = $nationTaxEx->slotData[$i];
  	}

  	print_r($taxes);

  	$cityLaws = new itemSlot($sellingCity->get('cLaw'), $slotFile, 40);
  	$regionLaws = new itemSlot($sellingCity->get('rLaw'), $slotFile, 40);
  	$nationLaws = new itemSlot($sellingCity->get('nLaw'), $slotFile, 40);

  	calcTaxes($cityTaxEx->slotData, $taxInfo, $taxes);
  	calcTaxes($regionTaxEx->slotData, $taxInfo, $taxes);
  	calcTaxes($nationTaxEx->slotData, $taxInfo, $taxes);

  	// calculate taxes on the selling player

  	echo 'Final tax rates:';
  	print_r($taxes);

		for ($i=0; $i<31; $i++) {
			$taxes[$i] = max($taxes[$i], 0);
		}

	return $taxes;
}

function taxCost ($taxRates, $transDat) {
	/*
	$transDat = [sent quantity, unit price, selling factory ID, sent pollution, sent rights, material cost, labor cost]
	*/

	$baseCost = $transDat[1]*$transDat[2];
	$taxAmounts = array_fill(1, 30, 0);
  	$taxAmounts[1] = $taxRates[1] * ($baseCost-$transDat[14] - $transDat[15])/100; // Income Tax
  	$taxAmounts[2] = 0; // Property Tax
  	$taxAmounts[3] = $taxRates[3] * ($baseCost - $transDat[14])/100; // VAT
  	$taxAmounts[4] = 0; // Personal Income Tax
  	$taxAmounts[5] = $taxRates[5] * $transDat[5]/100; // Pollution Tax
  	$taxAmounts[6] = $taxRates[6] * $transDat[6]/100; // Rights Tax
  	$taxAmounts[7] = $taxRates[7] * $baseCost/100; // Sales Tax

  	$taxAmounts[11] = $taxRates[11] * ($baseCost-$transDat[14] - $transDat[15])/100; // Income Tax
  	$taxAmounts[12] = 0; // Property Tax
  	$taxAmounts[13] = $taxRates[13] * ($baseCost - $transDat[14])/100; // VAT
  	$taxAmounts[14] = 0; // Personal Income Tax
  	$taxAmounts[15] = $taxRates[15] * $transDat[5]/100; // Pollution Tax
  	$taxAmounts[16] = $taxRates[16] * $transDat[6]/100; // Rights Tax
  	$taxAmounts[17] = $taxRates[17] * $baseCost/100; // Sales Tax

  	$taxAmounts[21] = $taxRates[21] * ($baseCost-$transDat[14] - $transDat[15])/100; // Income Tax
  	$taxAmounts[23] = $taxRates[23] * ($baseCost - $transDat[14])/100; // VAT
  	$taxAmounts[25] = $taxRates[25] * $transDat[5]/100; // Pollution Tax
  	$taxAmounts[26] = $taxRates[26] * $transDat[6]/100; // Rights Tax
  	$taxAmounts[27] = $taxRates[27] * $baseCost/100; // Sales Tax

  	$taxAmounts[29] = $taxRates[29]*$baseCost/100;

	return $taxAmounts;
}

function saveRegionTaxes($sellLocation, $buyLocation, $taxAmounts) {
	global $gamePath;
	
	/*
	sellLocation[0] =  selling City
	sellLocation[1] =  selling Region
	sellLocation[2] =  selling Nation
	
	buyLocation[0] =  buying City
	buyLocation[1] =  buying Region
	buyLocation[2] =  buying Nation
	*/
	
	$sllerTaxDat = '';
	$sellingRegion = $sellFactory->get('region_2');
	$sellingNation = $sellFactory->get('region_1');
	
	// Add the tax to city/region/nation treasuries
	$taxIncomeFile = fopen($gamePath.'/taxReceipts.txf', 'ab');
	for ($i=1; $i<10; $i++) {
		$sllerTaxDat .= pack('s*', $sellLocation[0], $i, $taxAmounts[$i]);
		$sllerTaxDat .= pack('s*', $sellLocation[1], $i, $taxAmounts[$i+10]);
		$sllerTaxDat .= pack('s*', $sellLocation[2], $i, $taxAmounts[$i+20]);
	}

	// Add the tax to the buying nation for any import Tax
	$buyerTaxDat = '';
	$buyingNation = $buyingFactory->get('region_1');
	$buyerTaxDat .= pack('s*', $buyLocation[2], 9, $taxAmounts[29]);

	echo 'Record tax transactions';
	if (flock($taxIncomeFile, LOCK_EX)) {
		fwrite($taxIncomeFile, $sllerTaxDat.$buyerTaxDat);
		flock($taxIncomeFile, LOCK_UN);
	}
	fclose($taxIncomeFile);
}

?>
