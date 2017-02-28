<?php

//print_R($postVals);
require_once('./slotFunctions.php');
require_once('./objectClass.php');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$laborPoolFile = fopen($gamePath.'/laborPool.dat', 'rb');
$laborSlotFile = fopen($gamePath.'/laborLists.slt', 'rb');

// Load the city information
$thisCity = loadCity($postVals[1], $cityFile);

// Load the city labor
echo 'Check slot '.$thisCity->get('cityLaborSlot');
$laborPool = [];
if ($thisCity->get('cityLaborSlot')>0) {
	$cityLabor = new itemSlot($thisCity->get('cityLaborSlot'), $laborSlotFile, 40);
	for ($i=1; $i<sizeof($cityLabor->slotData); $i+=10) {
		if ($cityLabor->slotData[$i] > 0) {
			$laborPool = array_merge($laborPool, array_slice($cityLabor->slotData, $i-1, 10));
		}
	}
}


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
cityTabs = new tabMenu(["Overview", "Government", "Labor", "Schools"]);
cityTabs.renderTabs(detailSection);
cityTabs.renderKids[0].innerHTML = "Overview";
cityTabs.renderKids[2].innerHTML = "Labor";

cityTabs.tabFunction(0, function() {console.log("i select u")});

showCity.townDemo = ['.(implode(',', array_slice($thisCity->objDat, 50, 20))).'];
showCity.leaderDemo = ['.(implode(',', array_slice($thisCity->objDat, 70, 20))).'];';


echo '
textBlob("", cityTabs.renderKids[1], "Government and demographic information");
showCity.renderDemos(cityTabs.renderKids[1]);
buildParks(cityTabs.renderKids[1], '.$postVals[1].', [1, -1, 2, 2]);
edictDetail(cityTabs.renderKids[1], '.$postVals[1].', [1, -1, 2, 2], "Adjust Taxes", ["Increase 1%", "Decrease 1%"]);

cityTabs.renderKids[2].subTarget = addDiv("", "stdFloatDiv", cityTabs.renderKids[2]);
laborTypeMenu(cityTabs.renderKids[2], 0);
showCityLabor(cityTabs.renderKids[2], '.$postVals[1].', ['.implode(',', $laborPool).']);
showSchools(cityTabs.renderKids[3], '.$postVals[1].',0, ['.implode(',', array_slice($thisCity->objDat, 80, 30)).']);
let buildSchools = newButton(cityTabs.renderKids[3]);
buildSchools.innerHTML = "Build new Schools";
buildSchools.addEventListener("click", function () {scrMod("1053,'.$postVals[1].'")})
</script>';

// Output demands for products of the city

fclose($cityFile);
fclose($laborPoolFile);
fclose($laborSlotFile);

?>
