<?php

//print_R($postVals);
require_once('./objectClass.php');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the city information
$thisCity = loadCity($postVals[1], $cityFile);

// Output parameters of the city (population, wealth, etc)
echo '<script>
useDeskTop.newPane("cityInfo");
var cityPane = useDeskTop.getPane("cityInfo");
cityPane.innerHTML = "";

textBlob("", cityPane, "City information");
var showCity = new city(['.$postVals[1].', "some citgy", '.implode(',', array_slice($thisCity->objDat, 11, 25)).']);
showCity.loadDemands(['.implode(',', array_slice($thisCity->objDat, $thisCity->laborDemandOffset-1, 10000)).'], ['.implode(',', array_slice($thisCity->objDat, $thisCity->laborStoreOffset-1, 10000)).']);
showCity.renderDetail(cityPane);
var detailSection = addDiv("", "stdFloatDiv", cityPane);

let optionButton1 = newButton(cityPane);
optionButton1.innerHTML = "Labor at City";
optionButton1.addEventListener("click", function () {
  showLaborArea = detailSection;
  scrMod("1020,0,0,'.$postVals[1].'")});

let optionButton2 = newButton(cityPane);
optionButton2.innerHTML = "Run for Mayor";

let optionButton3 = newButton(cityPane);
optionButton3.innerHTML = "Demographics";
optionButton3.addEventListener("click", function () {
  detailSection.innerHTML = "DEMOS";
});

let optionButton4 = newButton(cityPane);
optionButton4.innerHTML = "Demands/Prices";
optionButton4.addEventListener("click", function() {
  showCity.demandMenu(detailSection);
  showCity.renderDemands(detailSection, [1, 2, 3, 4, 5,6]);
});
</script>';

// Output demands for products of the city

fclose($cityFile);

?>
