<?php

/*
PostVals
1 - Factory ID
3 - City ID
5 - Product ID
6 - Product Qty
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


if ($qtyCheck) exit ('note enough of this ('.$thisFactory->get('prodInv'.($prodNumber+1)).' < '.$postVals[4].')');
// Load the city information
$buyingCity = loadCityDemands($postVals[2], $cityFile);
//print_R($buyingCity->objDat);
$cityRegion = loadRegion($buyingCity->get('parentRegion'), $cityFile);

// Update city demand

$now = time();

echo 'Base demand: '.$buyingCity->baseDemand($postVals[3]);
$basePrice = 100;
$baseDemand = $buyingCity->baseDemand($postVals[3]);
$startDemand = $buyingCity->currentDemand($postVals[3], $now);
$endDemand = max(intval($startDemand + $postVals[4]),0);

/*
$startPrice = intval(min($startDemand/$baseDemand, 2.0)*$basePrice);
$endPrice = intval(min($endDemand/$baseDemand, 2.0)*$basePrice);
$profit = (($startPrice+$endPrice)/2)*$postVals[4];
*/

if ($baseDemand > 0) {
	$startPrice = $basePrice * (2-$startDemand/$baseDemand) * (2-$startDemand/$baseDemand);
	$endPrice = $basePrice * (2-$endDemand/$baseDemand) * (2-$endDemand/$baseDemand);
	$usePrice = ($startPrice+$endPrice)/2;
	echo 'Start Price: '.$startPrice.', Final Price: '.$endPrice.' ('.($startDemand/$baseDemand).')<br>';
} else $usePrice = 0;

$totalSale = $postVals[4] * $usePrice;
$profit = $totalSale;

echo 'Sale price is '.$usePrice.' for a total sale value of '.$usePrice.' x '.$postVals[4].' = '.$profit.'<br>';

// Calculate taxes on the sale
if ($thisFactory->get('region_3') != $postVals[2]) {
	$sellingCity = loadCity($thisFactory->get('region_3'), $cityFile);
} else {$sellingCity = $buyingCity;}
$thisPlayer = loadObject($pGameID, $objFile, 400);

$testCity = $buyingCity;
$thisFactory->set('region_3', 1);

echo '<p>BUYING CITY ('.$postVals[2].')<p>SELLING CITY ('.$thisFactory->get('region_3').'):';
print_r($sellingCity->objDat);

//see taxCalcs.php for transaction format
$transaction = array_fill(0, 25, 0);
$transaction[1] = $postVals[4]; // sold qty
$transaction[2] = $usePrice; // sale price
$transaction[3] = $postVals[1]; // selling factory ID
$transaction[5] = 0; // pollution
$transaction[6] = 0; // rights
$transaction[14] = 0;
$transaction[15] = 0;

$taxRates = taxRates($transaction, $thisFactory, $buyingCity, $sellingCity, $thisPlayer, $slotFile);
echo '<p>Calced tax rates:<br>';
print_r($taxRates);

$taxAmounts = taxCost($taxRates, $transaction);
echo '<p>Calced tax amts::<br>';
print_r($taxAmounts);
//taxRates($transDat, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile)

// Add sales to factory tax base for it's own region
$thisFactory->adjVal('totalSales', $profit);
$thisFactory->adjVal('periodSales', $profit);


// record adjusted city demand and update time
echo '<p>Save new damend rate:';
$buyingCity->saveDLevel($postVals[3], $endDemand);
echo '<p>Save Last update:';
echo '<br>Last update = '.$buyingCity->get('lastUpdate').'. Now is '.$now.'<br>';
$buyingCity->save('lastUpdate', $now);

// add money to playerFactories

echo '<p>Save player money';
$thisPlayer->save('money', $thisPlayer->get('money') + $profit);
echo '<br>final money: '.$thisPlayer->get('money');

// remove qunatity from factory
$thisFactory->adjVal('prodInv'.($prodNumber+1), -$postVals[4]);
$thisFactory->adjVal('totalSales', $profit);
$thisFactory->adjVal('periodSales', $profit);
$thisFactory->saveAll($objFile);

echo 'final qty: '.$thisFactory->get('prodInv'.($prodNumber+1)).'
<script>
thisPlayer.money = '.$thisPlayer->get('money').'
</script>';

fclose($objFile);
fclose($cityFile);

?>
