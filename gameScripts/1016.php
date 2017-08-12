<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
//$offerListFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the city information
$thisCity = loadCity($postVals[2], $cityFile);

// Calculate the current demands for each product at the city and the corresponding price
$now = time();
$elapsed = $now-$thisCity->get('lastUpdate');
$price = 100;
$numProducts = 10;

/*
Need to adjust to show only products relevant to current factory.
*/

echo '<script>
cityProducts = new Array();';
for ($i=1; $i<$numProducts; $i++) {
  $baseDemand = $thisCity->baseDemand($i);
  //echo 'Base Demand: '.$thisCity->get('population').' * '.$thisCity->demandRate($i).' = '.$baseDemand;
  $actualDemand = min($thisCity->currentDemand($i, $now), 2.0*$baseDemand);
  //echo 'Actual Demand: '.$actualDemand;
  $itemPrice = intval(min($actualDemand/$baseDemand, 2.0)*$price);
  echo 'cityProducts.push(new offer(['.$i.', 100, '.$itemPrice.', '.$postVals[2].', 0, 0, 0, '.$i.']));';
}

// Output what the city will buy and at what price

fclose($objFile);
//fclose($offerListFile);
fclose($cityFile);

echo 'textBlob("", productArea, "city information and options");
cityDeals = new uList(cityProducts);
cityOffers = cityDeals.SLsingleButton(productArea, {renderFunction: function(x,y) {console.log(x);
  return x.renderSale(y);
}});
cityOButton = newButton(productArea, function () {
  qtyArea = addDiv("", "stdFloatDiv", thisDiv);
  qtyArea.innerHTML = "";
  textBlob("", qtyArea, "How many");

  var selectedProd = SLreadSelection(cityOffers).split(",");

  var qtyAvailable = 0;
  console.log(productStores);
  for (i=0; i<5; i++) {
    if (productStores[i] == selectedProd[1]) {
      qtyAvailable = productStores[i+5];
    }
  }
  qtySel = qtyBox(qtyArea, qtyAvailable);
  //setSlideVal(qtySel, 0);
  sellButton = newButton(qtyArea, function() {scrMod("1017,'.$postVals[1].'," +  SLreadSelection(cityOffers) + "," + qtySel.slider.slide.value)});
  sellButton.innerHTML = "SELL";
})
cityOButton.innerHTML = "Select this item";
</script>';

?>
