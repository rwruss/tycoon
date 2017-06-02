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
require_once('./invoiceFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$supplyFile = fopen($gamePath.'/citySupply.csf', 'r+b');
$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b');

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
if ($postVals[4] < 1) exit('no quantity');

$qtyCheck = true;
if ($optionCheck) {
  if ($thisFactory->get('prodInv'.($prodNumber+1)) >= $postVals[4]) {
    echo 'Qantity ok ('.$thisFactory->get('prodInv'.($i+1)).' vs '.$postVals[4].')';
    $qtyCheck = false;
  }
} else exit('Not a valid item');

// verify product number is valids
if ($postVals[3] < 1) {
	exit("invalid product");
} else { echo 'product #'.$postVals[3].' is valud';}

// Verify factory has enough inventroy for this sale
if ($qtyCheck) exit ('note enough of this ('.$thisFactory->get('prodInv'.($prodNumber+1)).' < '.$postVals[4].')');
$saleQty = $postVals[4];

// Load the city information
$buyingCity = loadCityDemands($postVals[2], $cityFile);
$cityRegion = loadRegion($buyingCity->get('parentRegion'), $cityFile);

// Load and Update city supply
$now = time();

$usePrice = 0;
$supplyInfo = $buyingCity->supplyLevel($postVals[3], $supplyFile);
echo '<p>SUPPLY INFO<p>';
print_R($supplyInfo);
$currentSupply = $supplyInfo['h2'] - ($now - $supplyInfo['h1']) * $supplyInfo['h3'];
$currentSupply = 375000;
$population = 1000000;
$payDemos = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];
$demandLevels = [0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1, 0];
//$populationDemo = [0, 25, 25, 23, 10, 6, 3, 3, 2, 2, 1, 0];

$populationDemo = $buyingCity->iPercentiles();
array_unshift($populationDemo, 0);
$populationDemo[] =  0;

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
//print_r($sellingCity->objDat);

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

// adjust product stats at the factory

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

// remove qunatity from factory
$thisFactory->adjVal('prodInv'.($prodNumber+1), -$saleQty);
$thisFactory->adjProduct($prodIndex, $sentQual, $sentPol, $sentRights, $materialCost, $laborCost);
//$thisFactory->adjVal('totalSales', $netSale);
//$thisFactory->adjVal('periodSales', $netSale);
$thisFactory->saveAll($objFile);

// Create transaction (invoice) for the city and assign an anticipated time of arrival
$now = time();
$invoiceInfo = array_fill(1, 20, 0);
$invoiceInfo[1] = 3; // status: unsold to city
$invoiceInfo[2] = $postVals[3]; // Proudct ID
$invoiceInfo[3] = $postVals[4];
$invoiceInfo[4] = 0; // contract Price (TBD)
$invoiceInfo[5] = $sentQual;
$invoiceInfo[6] = $sentPol;
$invoiceInfo[7] = $sentRights;
$invoiceInfo[8] = $now;
$invoiceInfo[9] = 0;
$invoiceInfo[11] = $thisPlayer->get('shipmentLink'); // Link to previously linked item
$invoiceInfo[12] = $now + 600; // Delivery time
$invoiceInfo[13] = 0; // TBD at time of sale
$invoiceInfo[14] = 0; // contract ID (NONE)
$invoiceInfo[15] = $materialCost;
$invoiceInfo[16] = $laborCost;
$invoiceInfo[17] = $postVals[1]; // selling factory ID
$invoiceInfo[18] = $postVals[2]; // target City ID

// Save the invoice to teh file
$taxRates = array_fill(1, 30, 0);
$newInvoiceID = writeInvoice($invoiceInfo, $taxRates, $contractFile);
echo 'Created invoice '.$newInvoiceID;

// change the player information to link to this invoice first
$thisPlayer->save('shipmentLink', $newInvoiceID);

// Add the transaction to the companies list of pending invoices

/***** OLD STUFF
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
echo '<p>Start Supply: '.$supplyInfo[2].' Consumption Rate:'.$supplyInfo[3];
$buyingCity->setSupply($postVals[3], $buyingCity->supplyLevel(max(0, $supplyInfo[2])-$postVals[4]), $supplyFile);
echo '<br> end Supply of '.$buyingCity->supplyLevel($postVals[3]).'<p>Save Last update:';
echo '<br>Last update = '.$buyingCity->get('lastUpdate').'. Now is '.$now.'<br>';
//$buyingCity->save('lastUpdate', $now);

// add money to playerFactories
echo '<p>Save player money';
$thisPlayer->save('money', $thisPlayer->get('money') + $netSale);
echo '<br>final money: '.$thisPlayer->get('money');

*/
$shipmentList = $invoiceInfo;

// read city demographics and shit
$shipmentList[] = $buyingCity->get('population');

$nationalPay = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];
$shipmentList = array_merge($shipmentList, $nationalPay);

$incomeLvls = [25, 25, 23, 10, 6, 3, 3, 2, 2, 1];
$shipmentList = array_merge($shipmentList, $incomeLvls);

// read city product demand
fseek($supplyFile, $invoiceInfo[18]*$supplyBlockSize + $invoiceInfo[2]*40);
$supplyDat = fread($supplyFile, 40);

$demandHead = unpack('i*', substr($supplyDat, 0, 12));
$productDemand = unpack('s*', substr($supplyDat, 12, 20));

$productDemand = [1, 2, 3, 4, 0, 0, 0, 0, 0, 0];
$shipmentList = array_merge($shipmentList, $demandHead, $productDemand);

echo 'final qty: '.$thisFactory->get('prodInv'.($prodNumber+1)).'.<br>
City population: '.$buyingCity->get('population').'
<script>
console.log("scr1017");
updateFactory(['.implode(',', $thisFactory->overViewInfo()).']);

// add the shipment to the shipment List
loadShipments(['.implode(',', $shipmentList).'], shipmentList); // shipmentList
</script>';

fclose($objFile);
fclose($cityFile);
fclose($supplyFile);
fclose($slotFile);
fclose($contractFile);
?>
