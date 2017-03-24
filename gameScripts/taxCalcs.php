<?php

/*
general script for calculating taxes on transactions between two factories
*/

function calcTaxes($slotData, $thisInfo, &$taxList) { // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
	for ($i=11; $i<sizeof($slotData); $i+=4) {
    echo $thisInfo[$slotData[$i]].' vs '.$slotData[$i+2].' --> ';
		if ($thisInfo[$slotData[$i]] == $slotData[$i+2]) {
      echo 'adjust tax type '.$slotData[$i+1].' by '.$slotData[$i+3];
			$taxList[$slotData[$i+1]] += $slotData[$i+3];
		}
	}
}

function taxAmounts($materialCost, $laborCost, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile) {
	$baseCost = $offerDat[1]*$offerDat[2];
	
  	// [0, company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID, city ID, region ID, nation ID]
  	$taxInfo = [0, $sellingFactory->get('owner'), $sellingFactory->get('subType'), $sellingFactory->get('industry'), $offerDat[3],
  		$sellingPlayer->get('teamID'), $sellingFactory->get('region_3'), $sellingFactory->get('region_2'), $sellingFactory->get('region_1')];

  	// Calculate import/tarrif taxes for the buyer
  	$importTaxes = array_fill(0,31,0);
  	$importTaxEx = new itemSlot($buyingCity->get('nTax'), $slotFile, 40);
    $importTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];
  	$importTaxes[29] = $importTaxEx->slotData[9];
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
	
	echo 'Tax infor for player ('.$pGameID.')';
  	print_r($taxInfo);

  	$cityLaws = new itemSlot($sellingCity->get('cLaw'), $slotFile, 40);
  	$regionLaws = new itemSlot($sellingCity->get('rLaw'), $slotFile, 40);
  	$nationLaws = new itemSlot($sellingCity->get('nLaw'), $slotFile, 40);

  	calcTaxes($cityTaxEx->slotData, $taxInfo, $taxes);
  	calcTaxes($regionTaxEx->slotData, $taxInfo, $taxes);
  	calcTaxes($nationTaxEx->slotData, $taxInfo, $taxes);

  	// calculate taxes on the selling player
  	//$materialCost = $offerDat[14];
  	//$laborCost = $offerDat[15];

  	echo 'Final tax rates:';
  	print_r($taxes);

  	$taxAmounts = array_fill(1, 30, 0);
  	$taxAmounts[1] = $taxes[1]* ($baseCost-$materialCost - $laborCost)/10000; // Income Tax
  	$taxAmounts[3] = $taxes[3] * ($baseCost - $materialCost)/10000; // VAT
  	$taxAmounts[5] = $taxes[5]*$offerDat[5]/10000; // Pollution Tax
  	$taxAmounts[6] = $taxes[6]*$offerDat[6]/10000; // Rights Tax
  	$taxAmounts[7] = $taxes[7] * $baseCost/10000; // Sales Tax

  	$taxAmounts[11] = $taxes[11]* ($baseCost-$materialCost - $laborCost)/10000; // Income Tax
  	$taxAmounts[13] = $taxes[13] * ($baseCost - $materialCost)/10000; // VAT
  	$taxAmounts[15] = $taxes[15]*$offerDat[5]/10000; // Pollution Tax
  	$taxAmounts[16] = $taxes[16]*$offerDat[6]/10000; // Rights Tax
  	$taxAmounts[17] = $taxes[17] * $baseCost/10000; // Sales Tax

  	$taxAmounts[21] = $taxes[21]* ($baseCost-$materialCost - $laborCost)/10000; // Income Tax
  	$taxAmounts[23] = $taxes[23] * ($baseCost - $materialCost)/10000; // VAT
  	$taxAmounts[25] = $taxes[25]*$offerDat[5]/10000; // Pollution Tax
  	$taxAmounts[26] = $taxes[26]*$offerDat[6]/10000; // Rights Tax
  	$taxAmounts[27] = $taxes[27] * $baseCost/10000; // Sales Tax

  	$taxAmounts[29] = $importTaxes[29]*$baseCost/10000;
	
	return $taxAmounts;
}

?>