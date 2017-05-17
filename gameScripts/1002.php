<?php

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
	//let thisFactory = playerFactories[i];
	thisSummary.sendStr = thisFactory.objID + "," + i;
	thisSummary.addEventListener("click", function () {scrMod("1003,"+this.sendStr);})
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

invoiceButton = newButton(headSection);
invoiceButton.addEventListener("click", function (e) {
	e.stopPropagation();
	thisDiv = useDeskTop.newPane("companyInvoices");
	thisDiv.innerHTML = "";

	loadBuffer("1078," + thisPlayer.playerID, function (x) {
		if (x.byteLength == 0) thisDiv.innerHTML = "No current invoices";
		var invoiceItems = invoiceList(x);
		for (var i=0; i<invoiceItems.length; i++) {
			invoiceItems[i].renderFSum(thisDiv);
		}
	})});
invoiceButton.innerHTML = "Open Invoices";

sendButton = newButton(headSection);
sendButton.addEventListener("click", function (e) {
	e.stopPropagation();
	factoryBuildMenu();});
sendButton.innerHTML = "Build a new facility";
textBlob("", businessDiv.laborHead, "company labor");

tmpLabor = companyLabor;
companyLaborList(tmpLabor, businessDiv.laborSection);
</script>';
?>
