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
$thisCity = loadCity($postVals[2], $cityFile, 4000);
$cityRegion = loadRegion($thisCity->get('parentRegion'), $cityFile);



// Update city demand

$now = time();
/*
$elapsed = $now-$thisCity->get('lastUpdate');

$baseDemand = $thisCity->get('population')*$thisCity->demandRate($i);
$startDemand = min($elapsed*$thisCity->demandRate($i)/3600+$thisCity->demandLevel($i), 2.0*$baseDemand);
echo 'Start Demand:('.$now.' - '.$thisCity->get('lastUpdate').') * '.$thisCity->demandRate($i).' / 3600 + ' .$thisCity->demandLevel($i);
*/
echo 'Base demand: '.$thisCity->baseDemand($postVals[3]);
$basePrice = 100;
$baseDemand = $thisCity->baseDemand($postVals[3]);
$startDemand = $thisCity->currentDemand($postVals[3], $now);
$endDemand = max(intval($startDemand - $postVals[4]),0);

$startPrice = intval(min($startDemand/$baseDemand, 2.0)*$basePrice);
$endPrice = intval(min($endDemand/$baseDemand, 2.0)*$basePrice);
$profit = (($startPrice+$endPrice)/2)*$postVals[4];

// Add sales to factory tax base for it's own region
$factoryTax = 0;
$thisFactory->adjVal('totalSales', $profit);
$thisFactory->adjVal('periodSales', $profit);

if ($thisFactory->get('region1') != $thisCity->get('parentRegion')) {
	// apply tarrif on sold goods
	$tarrifAmount = 0;
	//$cityRegion->adjVal('money', $tarrifAmount);
}

// Calculate taxes on the sale

echo 'Start/end city demand:'.$startDemand.' / '.$endDemand.'<br>
Start/End price '.$startPrice.' / '.$endPrice.' FInal average price: '.(($startPrice+$endPrice)/2).' for a profit of '.$profit;

// record adjusted city demand
$thisCity->saveDRate($i, $endDemand);
$thisCity->save('lastUpdate', $now);
echo 'Last update = '.$thisCity->get('lastUpdate').' Now is '.$now;

// add money to playerFactories
$thisPlayer = loadObject($pGameID, $objFile, 400);
$thisPlayer->save('money', $thisPlayer->get('money') + $profit);
echo 'final money: '.$thisPlayer->get('money');

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
