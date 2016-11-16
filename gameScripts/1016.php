<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the city information
$thisCity = loadCity($postVals[2], $cityFile);

// Calculate the current demands for each product at the city and the corresponding price
$now = time();
$elapsed = $now-$thisCity->get('lastUpdate');
$price = 100;
$numProducts = 10;
echo '<script>
cityProducts = new Array();';
for ($i=1; $i<$numProducts; $i++) {
  $baseDemand = $thisCity->baseDemand($i);
  //echo 'Base Demand: '.$thisCity->get('population').' * '.$thisCity->demandRate($i).' = '.$baseDemand;
  $actualDemand = min($thisCity->currentDemand($i, $now), 2.0*$baseDemand);
  //echo 'Actual Demand: '.$actualDemand;
  $itemPrice = intval(min($actualDemand/$baseDemand, 2.0)*$price);
  echo 'cityProducts.push(new offer(['.$i.', 1000, '.$itemPrice.', '.$postVals[2].']));';
}

// Output what the city will buy and at what price

fclose($objFile);
fclose($offerFile);
fclose($cityFile);

echo 'textBlob("", productArea, "city information and options");
cityDeals = new saleList(cityProducts);
cityOffers = cityDeals.SLsingleButton(productArea);
cityOButton = newButton(productArea, function () {scrMod("1017,'.$postVals[1].',"+ SLreadSelection(cityBox1) + "," + SLreadSelection(cityOffers))})
cityOButton.innerHTML = "Select this item";
</script>';

?>
