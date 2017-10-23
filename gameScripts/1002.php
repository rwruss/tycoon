<?php

echo '<script>
businessDiv = useDeskTop.newPane("businessObjects");
businessDiv.innerHTML = "";

var headSection = addDiv("", "stdFloatDiv", businessDiv);
businessDiv.listSection = addDiv("", "stdFloatDiv", businessDiv);
businessDiv.shipments = addDiv("", "stdFloatDiv", businessDiv);
businessDiv.laborHead = addDiv("abcd", "stdFloatDiv", businessDiv);
businessDiv.laborSection = addDiv("abcd", "stdFloatDiv", businessDiv);

businessDiv.shipSection = addDiv("abcd", "stdFloatDiv", businessDiv);
businessDiv.shipHead = addDiv("abcd", "stdFloatDiv", businessDiv.shipSection);
businessDiv.shipBody = addDiv("abcd", "stdFloatDiv", businessDiv.shipSection);

console.log(playerFactories);
for (let i=0; i<playerFactories.length; i++) {
	let thisSummary = playerFactories[i].renderSummary(businessDiv.listSection);
	let thisFactory = playerFactories[i];
	thisSummary.sendStr = thisFactory.objID + "," + i;
	thisSummary.addEventListener("click", function () {scrMod("1003,"+this.sendStr);})
}

contractsButton = newButton(headSection);
contractsButton.innerHTML = "Company Contracts";
contractsButton.addEventListener("click", function (e) {
	e.stopPropagation();
	thisDiv = useDeskTop.newPane("companyContracts");
	thisDiv.innerHTML = "";
	thisDiv.buyContracts = addDiv("", "stdFloatDiv", thisDiv);
	thisDiv.sellContracts = addDiv("", "stdFloatDiv", thisDiv);

	thisDiv.buyContracts.innerHTML = "Buyinh";
	thisDiv.sellContracts.innerHTML = "<span style=\"float:left\">Sellin</span>";

	cSearch = newButton(thisDiv);
	cSearch.innerHTML = "Bid on contracts";
	cSearch.addEventListener("click", function (e) {
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
				if (test.byteLength == 0) {
					contractBids.results.innerHTML = "No options";
				} else {
					for (var i=0; i<test.byteLength; i+=108) {
						let thisContract = newContract(x.slice(i, i+108));
						let contractItem = thisContract.render(contractBids.results);
						contractItem.addEventListener("click", function () {

						});
					}
				}
			})
		});
		searchButton.innerHTML = "search";
	});


	loadBuffer("1071,'.$pGameID.'", function (x) {
		let test = new Int32Array(x);
		console.log(test);
		console.log(test.byteLength);
		for (var i=0; i<test.byteLength; i+=108) {
			let thisContract = newContract(x.slice(i, i+108));
			//let thisContract = new contract(x.slice(i, i+108));
			//let contractItem = thisContract.render(thisDiv.buyContracts);
			if (thisContract.owner == thisPlayer.playerID) thisContract.render(thisDiv.buyContracts);
			else if (thisContract.seller == thisPlayer.playerID) thisContract.render(thisDiv.sellContracts);
		}
	})
	/*
	let projectsButton = newButton(headSection);
	projectButton.innerHTML = "Company Projects";
	projectButton.addEventListener("click", function (e) {
		e.stopPropagation();
		let serviceContracts = useDeskTop.newPane("serviceContracts");
		getASync().then(v=>{

		});
	})*/
});


bidButton = newButton(headSection);
bidButton.innerHTML = "Company Bids";
bidButton.addEventListener("click", function (e) {
	e.stopPropagation();
	thisDiv = useDeskTop.newPane("companyBids");
	thisDiv.innerHTML = "";
	console.log(thisPlayer);

	scrMod("1073," + thisPlayer.playerID);
	});


invoiceButton = newButton(headSection);
invoiceButton.innerHTML = "Open Invoices";
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


sendButton = newButton(headSection);
sendButton.addEventListener("click", function (e) {
	e.stopPropagation();
	factoryBuildMenu();});
sendButton.innerHTML = "Build a new facility";
textBlob("", businessDiv.laborHead, "company labor");

transButton = newButton(headSection);
transButton.addEventListener("click", function (e) {
	e.stopPropagation();
	transportMenu();});
transButton.innerHTML = "Transport Options";

tmpLabor = companyLabor;
companyLaborList(tmpLabor, businessDiv.laborSection);

businessDiv.shipHead.innerHTML = "Shipments en Route";
showShipments(shipmentList, businessDiv.shipBody);
</script>';
?>
