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
var showCity = new city(['.$postVals[1].', "some citgy", '.implode(',', array_slice($thisCity->objDat, 0, 25)).']);
showCity.renderDetail(cityPane);

var demandPane = addDiv("", "stdFloatDiv", cityPane);
demandPane.showDiv = showCity.demandMenu(demandPane, ['.implode(',', array_slice($thisCity->objDat, $thisCity->laborDemandOffset-1, 10000)).'], ['.implode(',', array_slice($thisCity->objDat, $thisCity->laborStoreOffset-1, 10000)).']);
showCity.renderDemands(demandPane.showDiv, [1, 2, 3, 4, 5,6]);
</script>';

// Output demands for products of the city

fclose($cityFile);

?>
