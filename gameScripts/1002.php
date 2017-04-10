<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
businessDiv = useDeskTop.newPane("businessObjects");
businessDiv.innerHTML = "";

var headSection = addDiv("", "stdFloatDiv", businessDiv);
var listSection = addDiv("", "stdFloatDiv", businessDiv);
businessDiv.laborHead = addDiv("abcd", "stdFloatDiv", businessDiv);
businessDiv.laborSection = addDiv("abcd", "stdFloatDiv", businessDiv);


console.log(playerFactories);
for (let i=0; i<playerFactories.length; i++) {
	let thisSummary = playerFactories[i].renderSummary(listSection);
	let thisFactory = playerFactories[i];
	thisSummary.addEventListener("click", function () {scrMod("1003,"+thisFactory.objID);})
}

contractsButton = newButton(headSection);
contractsButton.addEventListener("click", function (e) {
	e.stopPropagation();
	thisDiv = useDeskTop.newPane("companyContracts");
	thisDiv.innerHTML = "";
	thisDiv.buyContracts = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.sellContracts = addDiv("", "stdFloatDiv", thisDiv);

	thisDiv.buyContracts.innerHTML = "Buyinh";
	thisDiv.sellContracts.innerHTML = "<span style=\"float:left\">Sellin</span>";

	cSearch = newButton(thisDiv, function (e) {
		e.stopPropagation();
		contractBids = useDeskTop.newPane("contractBids");
		contractBids.innerHTML = "Select what product to bid on";
		let selectList = arrayToSelect(contractBids, objNames);
		contractBids.results = addDiv("", "stdFloatDiv", contractBids);
		searchButton = newButton(contractBids, function () {
			console.log(selectList);
			//scrMod("1067," + selectList.options[selectList.selectedIndex].value);
			loadBuffer("1067," + selectList.options[selectList.selectedIndex].value, function (x) {
				let test = new Int32Array(x);
				console.log(test);
				console.log(test.byteLength);
				for (var i=0; i<test.byteLength; i+=108) {
					let thisContract = new contract(x.slice(i, i+108));
					let contractItem = thisContract.render(contractBids.results);
				}
			})
		});
		searchButton.innerHTML = "search";

	});
	cSearch.innerHTML = "Bid on contracts";

	//scrMod("1071,'.$pGameID.'");
	loadBuffer("1071,'.$pGameID.'", function (x) {
		let test = new Int32Array(x);
		console.log(test);
		console.log(test.byteLength);
		for (var i=0; i<test.byteLength; i+=108) {
			let thisContract = new contract(x.slice(i, i+108));
			//let contractItem = thisContract.render(thisDiv.buyContracts);
			if (thisContract.owner == thisPlayer.playerID) thisContract.render(thisDiv.buyContracts);
			else if (thisContract.seller == thisPlayer.playerID) thisContract.render(thisDiv.sellContracts);
		}
	})
});
contractsButton.innerHTML = "Company Contracts";

bidButton = newButton(headSection);
bidButton.addEventListener("click", function (e) {
	e.stopPropagation();
	thisDiv = useDeskTop.newPane("companyBids");
	thisDiv.innerHTML = "";
	console.log(thisPlayer);

	scrMod("1073," + thisPlayer.playerID);
	});
	bidButton.innerHTML = "Company Bids";

sendButton = newButton(headSection);
sendButton.addEventListener("click", function (e) {
	e.stopPropagation();
	factoryBuildMenu();});
sendButton.innerHTML = "Build a new facility";
textBlob("", businessDiv.laborHead, "company labor");

tmpLabor = companyLabor;
companyLaborList(tmpLabor, businessDiv.laborSection);
</script>';
fclose($slotFile);
fclose($objFile);

?>
