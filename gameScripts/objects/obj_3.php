<?php

$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');

$now = time();
$constructDelta = $thisObj->get('constructCompleteTime') - $now;
if ($thisObj->get('factoryLevel') == 0) {
	// Check if construction of the factory is complete
	if ($constructDelta > 0) {
		echo '<script>
		selectedFactory = '.$postVals[1].';
		factoryDiv = useDeskTop.newPane("factoryInfo");
		factoryDiv.innerHTML = "";
		textBlob("", factoryDiv, "This facility is still being built.  Would you like to speed it up?;");
		/*
		var buildTimeBox = addDiv("", "orderTime", factoryDiv);
		buildTimeBox.runClock = true;
		countDownClock('.($thisObj->get('constructCompleteTime')).', buildTimeBox, function () {console.log("finish factory construction")});
		speedUpButton = newButton(factoryDiv, function () {scrMod("1029,1,'.$postVals[1].'")});
		speedUpButton.innerHTML = "Speed Up Construction";
		console.log("done");*/
		thisUpgrade = new factoryUpgrade('.$postVals[1].', '.($thisObj->get('constructCompleteTime')).');
		thisUpgrade.render(factoryDiv);
		</script>';
		exit();
	}
}

if ($constructDelta > 0) {
	echo 'Upgrade to level '.($thisObj->get('factoryLevel') + 1).' is in progress.  '.($constructDelta).' remaining to complete;';
}
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb');
$thisObj->updateStocks($offerDatFile);
if ($thisObj->get('currentProd') > 0) {
	$currentProduction = ', {setVal:'.$thisObj->get('currentProd').'}';
} else $currentProduction = '';

$currentProduction = ', {setVal:'.$thisObj->get('currentProd').'}';

//echo 'Load production object '.$thisObj->get('currentProd');
$productInfo = loadProduct($thisObj->get('currentProd'), $objFile, 400);

// Load factory sales
$saleDat = [];
for ($i=1; $i<9; $i++) {
	if ($thisObj->get('offer'.$i) > 0) {
		fseek($offerDatFile, $thisObj->get('offer'.$i));
		$tmpDat = unpack('i*', fread($offerDatFile, 44));
		if ($tmpDat[1] > 0) {
			$saleDat[] = $thisObj->get('offer'.$i);
			$saleDat = array_merge($saleDat, $tmpDat);
		} else {
			$thisObj->save('offer'.$i, 0);
		}
	}
}
//print_r($thisObj->objDat);

// Load updated material order information for this factory


$materialOrders = [];
for ($i=0; $i<10; $i++) {
	if ($thisObj->objDat[$thisObj->orderListStart+$i] > 0) {
		fseek($offerDatFile, $thisObj->objDat[$thisObj->orderListStart+$i]);
		$offerDat = unpack('i*', fread($offerDatFile, 64));
		array_push($materialOrders, $postVals[1], $i, $offerDat); //id, qty, time
		$materialOrders = array_merge($materialOrders, $offerDat);
	} else array_push($materialOrders, $postVals[1],$i,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
}
fclose($offerDatFile);

// Load factory contracts and invoice orders
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$contractStr = '';
$contractItems = array();
for ($i=0; $i<5; $i++) {
	$contractItems[] = $i;
	$contractStr .= pack('i', $i);
	if ($thisObj->objDat[$thisObj->contractsOffset+$i] > 0) {
		fseek($contractFile, $thisObj->objDat[$thisObj->contractsOffset+$i]);
		$contractDat = fread($contractFile, 100);
		$contractStr .= $contractDat.pack('i', $thisObj->objDat[$thisObj->contractsOffset+$i]);
		$contractInfo = unpack('i*', $contractDat);
		$contractItems = array_merge($contractItems, $contractInfo);
		$contractItems[] = $thisObj->objDat[$thisObj->contractsOffset+$i];
	}
	// Load the latest invoices for open contracts
	if ($contractInfo[22] > 0) {
		$invoiceNum = $contractInfo[22];
		$count = 0;
		while ($invoiceNum > 0 && $count < 10) {
			fseek($contractFile, $invoiceNum);
			$invoiceDat = fread($contractFile, 100);
			$invoiceInfo = unpack('i*', $invoiceDat);
			$contractInfo = array_merge($contractInfo, $invoiceInfo);
			$invoiceNum = $invoiceDat[11];
			$count++;
		}
	}
}
fclose($contractFile);

echo '<script>
factoryUpgradeProducts = [];
factoryUpgradeServices = [];
selectedFactory = '.$postVals[1].';
factoryDiv = useDeskTop.newPane("factoryInfo");
factoryDiv.innerHTML = "";
factorySales = ['.implode(',', $saleDat).'];';

if ($constructDelta > 0) {
	echo 'var updateArea = addDiv("", "stdFloatDiv", factoryDiv);';

	if ($thisObj->get('factoryLevel') == 0) {
		// this is new construction
		echo '';
	} else {
		// this is an upgrade
		echo 'thisUpgrade = new factoryUpgrade('.$postVals[1].', '.($thisObj->get('constructCompleteTime')).');
		thisUpgrade.render(updateArea)';
	}
}

echo '
productStores = ['.implode(',', $thisObj->tempList).','.implode(',', $thisObj->productStores).']
productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
productLabor = ['.implode(',', $productInfo->reqLabor).'];
materialInv = ['.implode(',', $thisObj->resourceInv()).'];
materialOrder = ['.implode(',', $materialOrders).'];
inProduction = ['.$thisObj->get('prodLength').', '.$thisObj->get('prodStart').', '.$thisObj->get('prodQty').'];

factoryOrders = new Array();
for (var i=0; i<materialOrder.length; i+=18) {
	factoryOrders.push(new factoryOrder(materialOrder.slice(i, i+18)));
	//factoryOrders.push(new factoryOrder('.$postVals[1].', materialOrder[i], materialOrder[i+1], materialOrder[i+2], i/3));
}
loadFactoryLabor(['.implode(',', array_slice($thisObj->objDat, ($thisObj->laborOffset-1), 100)).']);
//console.log("flabor is");
//console.log(factoryLabor);
inventoryItems = [];
for (i=0; i<materialInv.length; i+=2) {
	inventoryItems.push(new product({objID:materialInv[i]}));
}
invList = new uList(inventoryItems);
console.log(invList);
/*
inputItems = [];
for (i=0; i<productMaterial.length; i++) {
	inputItems.push(new product({objID:productMaterial[i]}));
}
inputList = new uList(inputItems);
*/
prodList = new uList([new product({objID:0}), new product({objID:'.$thisObj->getTemp('prod1').'})';

for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo ']);
headSection = addDiv("", "stdFloatDiv", factoryDiv);
headSection.rate = textBlob("", headSection, "Rate: '.($thisObj->get('prodRate')/100).'<br>Lifetime Earnings: $'.($thisObj->get('totalSales')/100).'<br>Period Earnings: $'.($thisObj->get('periodSales')/100).'");

contractsButton = newButton(headSection, function () {
	event.stopPropagation();
	thisDiv = useDeskTop.newPane("factoryContracts");
	thisDiv.innerHTML = "CONTRACT INFO";
	thisDiv.buyContracts = addDiv("", "stdFloatDiv", thisDiv);
	//scrMod("1064,'.$postVals[1].'")});
	loadBuffer("1064,'.$postVals[1].'", function (x) {
		let test = new Int32Array(x);
		console.log(test);
		console.log(test.byteLength);
		for (var i=0; i<test.byteLength; i+=108) {
			let thisContract = new contract(x.slice(i, i+108));
			let contractItem = thisContract.render(thisDiv.buyContracts);
		}})})
contractsButton.innerHTML = "Contracts";

sellButton = newButton(headSection, function () {scrMod("1043,'.$postVals[1].'")});
sellButton.innerHTML = "Sell Factory";

sendButton = newButton(headSection, function () {scrMod("1005,'.$postVals[1].',"+ SLreadSelection(factoryProductionBox))});
sendButton.innerHTML = "Set production";

startButton1 = newButton(headSection, function () {scrMod("1028,'.$postVals[1].',1")});
startButton1.innerHTML = "Work for - 1 hour";

startButton2 = newButton(headSection, function () {scrMod("1028,'.$postVals[1].',2")});
startButton2.innerHTML = "Work for - 2 hour";

startButton3 = newButton(headSection, function () {scrMod("1028,'.$postVals[1].',3")});
startButton3.innerHTML = "Work for - 4 hour";

startButton4 = newButton(headSection, function () {scrMod("1028,'.$postVals[1].',4")});
startButton4.innerHTML = "Work for - 8 hour";

prodContain = addDiv("", "orderContain", headSection);
fProduction = new factoryProduction('.$postVals[1].', '.($thisObj->get('prodLength') + $thisObj->get('prodStart')).', '.$thisObj->get('currentProd').', 100);
fProductionBox = fProduction.render(prodContain);
factoryProductionBox = prodList.SLsingleButton(fProductionBox'.$currentProduction.');

upgradeButton = newButton(headSection, function () {
	resourceQuery(factoryUpgradeProducts, factoryUpgradeServices, function () {
		scrMod("1031,'.$postVals[1].'");})
	});
upgradeButton.innerHTML = "Upgrade Factory";

productInvSection = addDiv("", "stdFloatDiv", factoryDiv);
textBlob("", productInvSection, "Output Inventory");

showOutputs(productInvSection, productStores);

salesSection = addDiv("", "stdFloatDiv", factoryDiv);
saleButton = newButton(salesSection, function () {scrMod("1013,'.$postVals[1].'")});
saleButton.innerHTML = "Sell Products";

factoryDiv.laborSection = addDiv("", "stdFloatDiv", factoryDiv);
factoryDiv.laborPool = addDiv("", "stdFloatDiv", factoryDiv);
textBlob("", factoryDiv.laborPool, "Unassigned Labor");
factoryDiv.laborSection.aassigned = addDiv("", "stdFloatDiv", factoryDiv.laborSection);
textBlob("", factoryDiv.laborSection.aassigned, "Labor working here");
showLabor('.$postVals[1].', factoryLabor);

reqBox = addDiv("", "stdFloatDiv", factoryDiv);
textBlob("", reqBox, "Per unit of production, this requires:");
reqBox.materials = addDiv("", "stdFloatDiv", reqBox);
showProdRequirements(reqBox.materials, productMaterial);

factoryDiv.laborSection.required = addDiv("", "stdFloatDiv", factoryDiv.laborSection);
showRequiredLabor(factoryDiv.laborSection.required, productLabor);


reqBox.stores = addDiv("", "stdFloatDiv", factoryDiv);
showInventory('.$postVals[1].', materialInv);

var orderSection = addDiv("", "stdFloatDiv", factoryDiv);
var orderHead = addDiv("", "stdFloatDiv", orderSection);

factoryDiv.orderItems = addDiv("", "stdFloatDiv", orderSection);
factoryDiv.saleItems = addDiv("", "stdFloatDiv", orderSection);
textBlob("", orderHead, "Current orders");
showOrders(factoryDiv.orderItems, factoryOrders);
showSales(factoryDiv.saleItems, factorySales);

factoryDiv.contracts = addDiv("", "stdFloatDiv", factoryDiv);
factoryDiv.contracts.innerHTML = "'.implode(',', $contractItems).'";
factoryContract(['.implode(',', $contractItems).'], factoryDiv.contracts);

</script>';

fclose($offerListFile);
//fclose($offerDatFile);

?>
