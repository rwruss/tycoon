<?php

require_once('./objectClass.php');
require_once('./taxCalcs.php');
require_once('./invoiceFunctions.php');
require_once('./slotFunctions.php');

$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$supplyFile = fopen($gamePath.'/citySupply.csf', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

fseek($contractFile, $postVals[1]);
$invoiceDat = fread($contractFile, 140);

$invoiceInfo = unpack('i*', substr($invoiceDat, 0, 80));
$taxInfo = unpack('s*', substr($invoiceDat, 80));

//print_r($invoiceInfo);
//print_r($taxInfo);

// Verify goods not already sold
if ($invoiceInfo[1] != 3) exit('error 0801-02');

// load the sending factory
$sellingFactory = loadObject($invoiceInfo[17], $objFile, 1000);

// verify that the player control this shipment
if ($sellingFactory->get('owner') != $pGameID) exit('error 0801-01');

// load buying city, selling city, and selling player
$buyingCity = loadCityDemands($invoiceInfo[18], $cityFile, 1000);
$sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile, 1000);
$sellingPlayer = loadObject($pGameID, $objFile, 400);

// calculate the price
$now = time();

$usePrice = 0;
$supplyInfo = $buyingCity->supplyLevel($invoiceInfo[2], $supplyFile);

echo '<p>Supply Info<p>';
print_r($supplyInfo);
echo '<p>';

$currentSupply = $supplyInfo['h2'] - ($now - $supplyInfo['h1']) * $supplyInfo['h3'];
$currentSupply = 375000;
$population = 1000000;
$payDemos = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];
$demandLevels = [0,1,2,3,4,0,0,0,0,0,0,0];

$populationDemo = $buyingCity->iPercentiles();
array_unshift($populationDemo, 0);
$populationDemo[] =  0;
$populationDemo = [0, 25, 25, 23, 10, 6, 3, 3, 2, 2, 1, 0];

echo 'Income percentiles for city ('.$invoiceInfo[18].'):<br>';
print_r($populationDemo);

$demandQty = [];
for ($i=0; $i<sizeof($populationDemo); $i++) {
  $demandQty[$i] = $population*$populationDemo[$i]*$demandLevels[$i]/100;
}

$usePrice = calcPrice($demandQty, $payDemos, $currentSupply);
print_r($demandQty);
echo 'Function price is '.calcPrice($demandQty, $payDemos, $currentSupply);

//$usePrice *= $buyingCity->get('pollutionAdj')*$buyingCity->get('rightsAdj');

// calculate the taxes
$transInfo = array_fill(0, 20, 0);
$transInfo[1] = $invoiceInfo[3];
$transInfo[2] = $usePrice;
$transInfo[3] = $invoiceInfo[17];
$transInfo[5] = $invoiceInfo[6];
$transInfo[6] = $invoiceInfo[7];
$transInfo[7] = $invoiceInfo[2];
$transInfo[14] = $invoiceInfo[15];
$transInfo[15] = $invoiceInfo[16];

$useTaxRates = taxRates($transInfo, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile);
$taxCost = taxCost($useTaxRates, $transInfo);

// transfer funds
$grossSale = $invoiceInfo[3] * $usePrice;
$taxTotal = intval(array_sum($taxCost));
$profit = $grossSale - $taxTotal;
echo 'Sale price is '.$usePrice.' for a total sale value of '.$usePrice.' x '.$invoiceInfo[3].' = '.$grossSale.'<br>
Tax Cost is '.$taxTotal.' for a final profit of '.$profit;

// save taxes
$buyTaxAreas = [0,0,$sellingFactory->get('region_3')];
$sellTaxAreas = [$invoiceInfo[18], $buyingCity->get('parentRegion'), $buyingCity->get('nation')];
saveRegionTaxes($sellTaxAreas, $buyTaxAreas, $taxCost);

// Add sales to factory tax base for it's own region
$sellingFactory->adjVal('totalSales', $profit);
$sellingFactory->adjVal('periodSales', $profit);

// record adjusted city supply and update time
$buyingCity->updateSupply($invoiceInfo[2], $invoiceInfo[3], $supplyFile); // product ID, added qty, file

// add money to playerFactories
echo '<p>Save player money';
$sellingPlayer->save('money', $sellingPlayer->get('money') + $profit);
echo '<br>final money: '.$sellingPlayer->get('money');

// delete the shipment reference by setting status to 99 for future deleting when the list is cycled in play.php
fseek($contractFile, $postVals[1]);
fwrite($contractFile, pack('i', 99));
$invoiceInfo[1] = 99;

echo '<script>
console.log("scr1080");
updateFactory(['.implode(',', $sellingFactory->overViewInfo()).']);
thisPlayer.money = '.$sellingPlayer->get('money').';
updateShipment(['.implode(',', $invoiceInfo).'], shipmentList);
</script>';

fclose($supplyFile);
fclose($slotFile);
fclose($contractFile);
fclose($objFile);
fclose($cityFile);

?>
