<?php

/*
PostVals
1 - Factory ID
2 - City ID
3 - Product ID
4 - Product Qty
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');
require_once('./taxCalcs.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

// Verify that the factory can sell this product
$thisFactory = loadObject($postVals[1], $objFile, 1000);
$optionCheck = false;
$optionList = $thisFactory->productionOptions();
echo 'look for '.$postVals[3].' in <br>';
print_r($optionList);
for ($i=0; $i<5; $i++) {
	if ($postVals[3] == $optionList[$i]) {
		$optionCheck = true;
		$prodNumber = $i;
		break;
	}
}

// Verify the qunatity sold by the factory
$qtyCheck = true;
if ($optionCheck) {
  if ($thisFactory->get('prodInv'.($prodNumber+1)) >= $postVals[4]) {
    echo 'Qantity ok ('.$thisFactory->get('prodInv'.($i+1)).' vs '.$postVals[4].')';
    $qtyCheck = false;
  }
} else exit('Not a valid item');

// Verify factory has enough inventroy for this sale
if ($qtyCheck) exit ('note enough of this ('.$thisFactory->get('prodInv'.($prodNumber+1)).' < '.$postVals[4].')');
$saleQty = $postVals[4];

// Load the city information
$buyingCity = loadCityDemands($postVals[2], $cityFile);
//print_R($buyingCity->objDat);
$cityRegion = loadRegion($buyingCity->get('parentRegion'), $cityFile);

// Update city demand

$now = time();
/*
echo 'Base demand: '.$buyingCity->baseDemand($postVals[3]);
$basePrice = 100;
$baseDemand = $buyingCity->baseDemand($postVals[3]);
$startDemand = $buyingCity->currentDemand($postVals[3], $now);
$endDemand = max(intval($startDemand + $saleQty),0);

if ($baseDemand > 0) {
	$startPrice = $basePrice * (2-$startDemand/$baseDemand) * (2-$startDemand/$baseDemand);
	$endPrice = $basePrice * (2-$endDemand/$baseDemand) * (2-$endDemand/$baseDemand);
	$usePrice = ($startPrice+$endPrice)/2;
	echo 'Start Price: '.$startPrice.', Final Price: '.$endPrice.' ('.($startDemand/$baseDemand).')<br>';
} else $usePrice = 0;
*/

$usePrice = 0;
$currentSupply = $buyingCity->supplyLevel($postVals[3]);
$currentSupply = 375000;
$population = 1000000;
$payDemos = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];
$demandLevels = [0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1, 0];
$populationDemo = [0, 25, 25, 23, 10, 6, 3, 3, 2, 2, 1, 0];
$demandQty = [];
for ($i=9; $i>0; $i--) {
	$demandQty[$i] = $population*$populationDemo[$i]*$demandLevels[$i]/100;
	if ($currentSupply < $demandQty[$i]) {
		echo 'Remaining Demand : '.($demandQty[$i] - $currentSupply);
		$pctSupplied = $currentSupply/$demandQty[$i];
		$usePrice = round($payDemos[$i+1]-$pctSupplied*($payDemos[$i+1] - $payDemos[$i]), 2);
		break;
	}
	$currentSupply -= $demandQty[$i];
}
echo 'Use price is '.$usePrice;

//$usePrice *= $buyingCity->get('pollutionAdj')*$buyingCity->get('rightsAdj');

$grossSale = $saleQty * $usePrice;
$profit = $grossSale;

echo 'Sale price is '.$usePrice.' for a total sale value of '.$usePrice.' x '.$saleQty.' = '.$grossSale.'<br>';

// Calculate taxes on the sale
if ($thisFactory->get('region_3') != $postVals[2]) {
	$sellingCity = loadCity($thisFactory->get('region_3'), $cityFile);
} else {$sellingCity = $buyingCity;}
$thisPlayer = loadObject($pGameID, $objFile, 400);

$testCity = $buyingCity;
$thisFactory->set('region_3', 1);

echo '<p>BUYING CITY ('.$postVals[2].')<p>SELLING CITY ('.$thisFactory->get('region_3').'):';
print_r($sellingCity->objDat);

// determine the index of the product being sold
$prodIndex = 0;
$optionCheck = false;
$optionList = $thisFactory->productionOptions();
echo 'look for '.$postVals[3].' in <br>';
print_r($optionList);
for ($i=0; $i<5; $i++) {
	if ($postVals[3] == $optionList[$i]) {
		$optionCheck = true;
		$prodIndex = $i;
		break;
	}
}

if (!$optionCheck) exit('Not a valid item to sell');

// calculate transaction costs
$sentQual = round($saleQty*$thisFactory->objDat[$thisFactory->productStats + $prodIndex*5+0]/$thisFactory->objDat[$thisFactory->prodInv+$prodIndex]);
$sentPol = round($saleQty*$thisFactory->objDat[$thisFactory->productStats + $prodIndex*5+1]/$thisFactory->objDat[$thisFactory->prodInv+$prodIndex]);
$sentRights = round($saleQty*$thisFactory->objDat[$thisFactory->productStats + $prodIndex*5+2]/$thisFactory->objDat[$thisFactory->prodInv+$prodIndex]);
$materialCost = round($saleQty*$thisFactory->objDat[$thisFactory->productStats + $prodIndex*5+3]/$thisFactory->objDat[$thisFactory->prodInv+$prodIndex]);
$laborCost = round($saleQty*$thisFactory->objDat[$thisFactory->productStats + $prodIndex*5+4]/$thisFactory->objDat[$thisFactory->prodInv+$prodIndex]);
echo '<p>Labor Cost:'.$laborCost.'<br>Material Cost:'.$materialCost.'<br>Sent Pol:'.$sentPol.'<br>Sent Rights :'.$sentRights;

//see taxCalcs.php for transaction format
$transaction = array_fill(0, 25, 0);
$transaction[1] = $postVals[4]; // sold qty
$transaction[2] = $usePrice; // sale price
$transaction[3] = $postVals[1]; // selling factory ID
$transaction[5] = 0; // pollution
$transaction[6] = 0; // rights
$transaction[7] = $postVals[3]; // product ID
$transaction[14] = 0; // material cost
$transaction[15] = $laborCost; // labor cost

echo '<p>Transaction info:<br>';
print_r($transaction);

$taxRates = taxRates($transaction, $thisFactory, $buyingCity, $sellingCity, $thisPlayer, $slotFile);
echo '<p>Calced tax rates:<br>';
print_r($taxRates);

$taxAmounts = taxCost($taxRates, $transaction);
echo '<p>Calced tax amts::<br>';
print_r($taxAmounts);
//taxRates($transDat, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile)

// calculate net sale price on the transaction
$totalTax = $taxAmounts[1] + $taxAmounts[3] + $taxAmounts[5] + $taxAmounts[6];
$totalTax += $taxAmounts[11] + $taxAmounts[13] + $taxAmounts[15] + $taxAmounts[16];
$totalTax += $taxAmounts[21] + $taxAmounts[23] + $taxAmounts[25] + $taxAmounts[26];
$netSale = $grossSale - $totalTax;

echo '<p>PROFIT CALC:<br>'.$grossSale.' - '.$totalTax.' = '.($grossSale - $totalTax).'<p>';

// Add taxes to buying and selling region
$buyTaxAreas = [0,0,$postVals[2]];
$sellTaxAreas = [$thisFactory->get('region_3'), $thisFactory->get('region_2'), $thisFactory->get('region_1')];
saveRegionTaxes($sellTaxAreas, $buyTaxAreas, $taxAmounts);

// Add sales to factory tax base for it's own region
$thisFactory->adjVal('totalSales', $netSale);
$thisFactory->adjVal('periodSales', $netSale);

// record adjusted city supply and update time
//echo '<p>Save new damend rate:';
$buyingCity->addSupply($postVals[3], $buyingCity->supplyLevel($postVals[3])+$postVals[4]);
echo '<p>Save Last update:';
echo '<br>Last update = '.$buyingCity->get('lastUpdate').'. Now is '.$now.'<br>';
//$buyingCity->save('lastUpdate', $now);

// add money to playerFactories

echo '<p>Save player money';
$thisPlayer->save('money', $thisPlayer->get('money') + $netSale);
echo '<br>final money: '.$thisPlayer->get('money');

// remove qunatity from factory
$thisFactory->adjVal('prodInv'.($prodNumber+1), -$saleQty);
$thisFactory->adjVal('totalSales', $netSale);
$thisFactory->adjVal('periodSales', $netSale);
$thisFactory->saveAll($objFile);

echo 'final qty: '.$thisFactory->get('prodInv'.($prodNumber+1)).'
<script>
console.log("scr1017");
updateFactory(['.implode(',', $thisFactory->overViewInfo()).']);
thisPlayer.money = '.$thisPlayer->get('money').';

</script>';

fclose($objFile);
fclose($cityFile);

?>
