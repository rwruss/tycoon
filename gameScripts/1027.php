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
			//$laborPool = array_merge($laborPool, array_slice($cityLabor->slotData, $i-1, 10));
			fseek($laborPoolFile, $cityLabor->slotData[$i]);
			$tmpLabor = new labor(fread($laborPoolFile, 48));
			$laborPool = array_merge($laborPool, $tmpLabor->laborDat);
		}
	}
}

//$govtActions = explode('<||>', $govtInfo);

// load city demographics

// load city taxes/exceptions

// load national pay percentiles
$nationalPay = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];

// Output parameters of the city (population, wealth, etc)
echo '<script>
cityPane = useDeskTop.newPane("cityInfo");
cityPane.innerHTML = "";

textBlob("", cityPane, "City information (City #'.$postVals[1].')");
var showCity = new city([';
echo $postVals[1].',"City Name", '.implode(',', array_slice($thisCity->objDat, 11, 25)).','.implode(',', array_slice($thisCity->objDat, 50, 40)).','.implode(',', array_slice($thisCity->objDat, 120, 20)).']';
echo ',[]';
echo ',[1,2,3,4,5,6,7,8,9,10,1,2,3,4,5,6,7,8,9,10,1,2,3,4,5,6,7,8,9,10]);';

echo '
showCity.townDemo = ['.(implode(',', array_slice($thisCity->objDat, 50, 20))).'];
showCity.leaderDemo = ['.(implode(',', array_slice($thisCity->objDat, 70, 20))).'];
showCity.nationalPay(['.implode(',', $nationalPay).']);

showCity.renderDetail(cityPane);
var detailSection = addDiv("", "stdFloatDiv", cityPane);
cityTabs = new tabMenu(["Overview", "Government", "Labor", "Schools", "Markets"]);
cityTabs.renderTabs(detailSection);

cityTabs.tabFunction(0, function() {console.log("i select u")});
cityTabs.tabFunction(1, function() {
	let addLaw = addDiv("", "stdFloatDiv", cityTabs.renderKids[1]);
	addLaw.innerHTML = "Propose new Law";
	addLaw.addEventListener("click", function (e) {
		e.stopPropagation();
		let lawPane = useDeskTop.newPane("lawPane");
		lawPane.innerHTML = "";
		getASync("1100,'.$postVals[1].'").then(v => {
			lawPane.innerHTML = v;
		})
	});
	getASync("1099").then(v => {

		let result = loadGovtItems(v);
		console.log(result);
		for (let i=0; i<result.length; i++) {
			console.log(result[i]);
			//result[i].renderSummary(cityTabs.renderKids[1]);
		}
		result[0].renderSummary(cityTabs.renderKids[1]);
	});
});


textBlob("", cityTabs.renderKids[1], "Government and demographic information");

cityTabs.renderKids[2].subTarget = addDiv("", "stdFloatDiv", cityTabs.renderKids[2]);
laborTypeMenu(cityTabs.renderKids[2], 0);
showCityLabor(cityTabs.renderKids[2], '.$postVals[1].', ['.implode(',', $laborPool).']);
showSchools(cityTabs.renderKids[3], '.$postVals[1].',0, ['.implode(',', array_slice($thisCity->objDat, 80, 30)).']);
let buildSchools = newButton(cityTabs.renderKids[3]);
buildSchools.innerHTML = "Build new Schools";
buildSchools.addEventListener("click", function () {scrMod("1053,'.$postVals[1].'")});

cityTabs.renderKids[4].prodBar = addDiv("", "stdFloatDiv", cityTabs.renderKids[4]);
cityTabs.renderKids[4].factoryBar = addDiv("", "stdFloatDiv", cityTabs.renderKids[4]);

let productSales = selectMenu(playerProdNames);
cityTabs.renderKids[4].prodBar.appendChild(productSales);
cityTabs.renderKids[4].invDiv = addDiv("", "stdFloatDiv", cityTabs.renderKids[4]);
cityTabs.renderKids[4].invHead = addDiv("", "stdFloatDiv", cityTabs.renderKids[4].invDiv);
cityTabs.renderKids[4].invBody = addDiv("", "stdFloatDiv", cityTabs.renderKids[4].invDiv);
cityTabs.renderKids[4].invHead.innerHTML = "inventory here";

productSales.addEventListener("change", function () {
	let thisPrice;
	let thisTaxes;
	let thisRev;
	cityTabs.renderKids[4].factoryBar.innerHTML = "";
	console.log("load facs that make this (" + this.value + ")");
	let tmpList = getFactoriesByProduct(playerFactories, playerProducts[this.value]);

	console.log("show price updates");
	console.log(document.getElementById("cityIncome"));
	document.getElementById("cityIncome").innerHTML = showCity.nationalPayDemos;

	for (i=0; i<tmpList.length; i++) {
		if (tmpList[i] >= 0 && playerFactories[i].prodInv[tmpList[i]] > 0)	{
			let thisFac = playerFactories[i].itemBar(cityTabs.renderKids[4].factoryBar, tmpList[i], "1084," + playerFactories[i].objID +",'.$postVals[1].'," + this.value);
			thisFac.lCost.innerHTML = "Labor Cost: " + playerFactories[i].prodDtls[this.value*5+3];
			thisFac.mCost.innerHTML = "Material Cost: " + playerFactories[i].prodDtls[this.value*5+4];
			thisFac.slide.slide.selectedProduct = this.value;
			thisFac.slide.slide.addEventListener("change", function () {
				let thisPrice;
				var getPrice = function (x, y) {
					return new Promise(resolve => {
						resolve(showCity.demandPrice(x, y));
					}, reject => {
						console.log("rpice failed");
					});
				}

				async function dookie (x, y) {
					let p;
						p = await getPrice(x, y);
					return p;
				}

				console.log("run dookie");
				dookie(this.value, this.selectedProduct).then(v => {
					console.log("V has a value of " + v);
					thisPrice = v;
					thisRev = this.value*thisPrice;
					thisFac.priceBar.innerHTML = this.value + " @ " + thisPrice + " = " + (thisRev).toFixed(2);

					thisTaxes = thisFac.totalTax * this.value*thisPrice/100;

					thisFac.taxCost.innerHTML = "Less taxes: " + (thisTaxes).toFixed(2) + "(" + thisFac.totalTax + " x " + this.value + " x " + thisPrice + ")";
					thisFac.profit.innerHTML = "Net: " + (thisRev - thisTaxes).toFixed(2);

				});
			});
		}
	}

	// load the demand curve for this item for the city
});
forceEvent(productSales, "change");

</script>';

// Output demands for products of the city

fclose($cityFile);
fclose($laborPoolFile);
fclose($laborSlotFile);

?>
