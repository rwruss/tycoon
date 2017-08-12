<?php

if ($thisObj->get('constStatus') > 0) {
	$projectsFile = fopen($gamePath.'/projects.prj', 'rb');
	echo 'Load project '.$thisObj->get('constStatus');
	$thisProject = loadProject($thisObj->get('constStatus'), $projectsFile);
	fclose($projectsFile);
	print_r($thisProject->objDat);
}


$constructDelta = $thisObj->get('constStatus');
if ($thisObj->get('factoryLevel') == 0) {
	// Check if construction of the factory is complete
	if ($thisObj->get('constStatus') > 0) {
		$pctComplete = $thisProject->get('currPoints')/$thisProject->get('totalPoints') * 100;
		$ptsRrm = $thisProject->get('totalPoints') - $thisProject->get('currPoints');
		echo '<script>
		selectedFactory = '.$postVals[1].';
		factoryDiv = useDeskTop.newPane("factoryInfo");
		factoryDiv.innerHTML = "";
		textBlob("", factoryDiv, "This facility is still being built ('.$thisObj->get('constStatus').').  Construction is '.$pctComplete.'% complete ('.$ptsRrm.' points remaining).");
		textBlob("", factoryDiv, "You are currently offering '.$thisObj->get('upgradePrice').' for each unit of new construction.  Adjust this price below.");

		let priceBar = slideValBar(factoryDiv, "", 0, 10000);
		let priceButton = newButton(factoryDiv);
		priceButton.innerHTML = "Set new price";
		priceButton.sendStr = "1082,'.$postVals[1].',";
		priceButton.addEventListener("click", function () {scrMod(this.sendStr + priceBar.slide.value)});

		textBlob("", factoryDiv, "Or, use independent construction...");
		let indLaborBar = slideValBar(factoryDiv, "", 0, '.$ptsRrm.');
		let indLaborButton = newButton(factoryDiv);
		indLaborButton.innerHTML = "Use local services ($100/point)";
		indLaborButton.sendStr = "1083,'.$thisObj->get('constStatus').',";
		indLaborButton.addEventListener("click", function () {scrMod(this.sendStr + indLaborBar.slide.value)})

		//thisUpgrade = new factoryUpgrade('.$postVals[1].', '.($thisObj->get('constructCompleteTime')).');
		//thisUpgrade.render(factoryDiv);
		</script>';
		exit();
	}
}

if ($thisObj->get('constStatus') > 0) {
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
		//fseek($offerDatFile, $thisObj->get('offer'.$i));
		$thisOffer = loadOffer($thisObj->get('offer'.$i), $offerDatFile);
		//$tmpDat = unpack('i*', fread($offerDatFile, 44));
		if ($thisOffer->objDat[1] > 0) {
			$saleDat[] = $thisObj->get('offer'.$i);
			$saleDat = array_merge($saleDat, $thisOffer->objDat);
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
		//fseek($offerDatFile, $thisObj->objDat[$thisObj->orderListStart+$i]);
		//$offerDat = unpack('i*', fread($offerDatFile, 64));
		
		$thisOffer = loadOffer($thisObj->objDat[$thisObj->orderListStart+$i], $offerDatFile);
		
		array_push($materialOrders, $postVals[1], $thisObj->objDat[$thisObj->orderListStart+$i],  $i); //factory ID, offer id, spot
		$materialOrders = array_merge($materialOrders, $thisOffer->objDat);
		//$materialOrders = $materialOrders + $offerDat;
		print_r($thisOffer->objDat);
	} else array_push($materialOrders, $postVals[1],$i,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
}
fclose($offerDatFile);

print_r($materialOrders);

// Load factory contracts and invoice orders
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$headStr = '';
$contractStr = '';
$contractCount = 0;
$invoiceLink = array();
for ($i=0; $i<5; $i++) {
	if ($thisObj->objDat[$thisObj->contractsOffset+$i] > 0) {
		$contractStr .= pack('i', $i);
		$contractCount++;
		fseek($contractFile, $thisObj->objDat[$thisObj->contractsOffset+$i]);
		$contractDat = fread($contractFile, 100);
		$contractStr .= $contractDat.pack('i', $thisObj->objDat[$thisObj->contractsOffset+$i]);
		$contractInfo = unpack('i*', $contractDat);
		print_r($contractInfo);
		$invoiceLink[] = $contractInfo[22];
	}
}

print_r($invoiceLink);
$invoiceSend = [];
for ($i=0; $i<sizeof($invoiceLink); $i++) {
	$invCount = 0;
	$invoiceNum = $invoiceLink[$i];

	while ($invoiceNum > 0 && $invCount < 10) {
		fseek($contractFile, $invoiceNum);
		$invoiceDat = fread($contractFile, 116);
		//$contractStr .= $invoiceDat;

		//$invoiceInfo = unpack('s*', substr($invoiceDat, 56));
		$invoiceInfo = array_merge(unpack('i*', substr($invoiceDat, 0, 56)), unpack('s*', substr($invoiceDat, 56)));
		echo 'SHOW INVOICE INFO for invoice('.$invCount.'):';
		print_r($invoiceInfo);
		$invoiceSend = array_merge($invoiceSend, $invoiceInfo);
		$invoiceNum = $invoiceInfo[10];
		$invCount++;
	}
	$headStr .= pack('i', $invCount);
}
$headStr = pack('i', $contractCount).$headStr;
$contractStr = $headStr.$contractStr;
fclose($contractFile);

print_r($thisObj->invStats());

$optionList = $thisObj->productionOptions();
for ($i=0; $i<5; $i++) {
}

echo '<script>
selFactory = playerFactories['.$postVals[2].'];

selectedFactory = '.$postVals[1].';
factoryDiv = useDeskTop.newPane("factoryInfo");
factoryDiv.innerHTML = "";
selFactory.factorySales = ['.implode(',', $saleDat).'];';

/*
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
}*/

echo '
selFactory.factoryUpgradeProducts = [];
selFactory.factoryUpgradeServies = [];
selFactory.productStores = ['.implode(',', $thisObj->tempList).','.implode(',', $thisObj->productStores).'];
selFactory.productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
selFactory.productLabor = ['.implode(',', $productInfo->reqLabor).'];
selFactory.materialInv = ['.implode(',', $thisObj->resourceInv()).'];
selFactory.materialOrder = ['.implode(',', $materialOrders).'];
selFactory.inProduction = ['.$thisObj->get('prodLength').', '.$thisObj->get('prodStart').', '.$thisObj->get('prodQty').'];
selFactory.contracts = ['.implode(',', array_merge(unpack('i*', $contractStr), $invoiceSend)).'];

//loadFactoryLabor(['.implode(',', array_slice($thisObj->objDat, ($thisObj->laborOffset-1), 100)).']);
selFactory.labor = ['.implode(',', array_slice($thisObj->objDat, ($thisObj->laborOffset-1), 100)).'];

inventoryItems = [];
for (i=0; i<selFactory.materialInv.length; i+=2) {
	inventoryItems.push(new product({objID:selFactory.materialInv[i]}));
}
//invList = new uList(inventoryItems);

/*
prodList = new uList([new product({objID:0}), new product({objID:'.$thisObj->getTemp('prod1').'})';

for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo ']);*/
let tmpProdItems = [];
let selectedIndex = 0;
tmpProdItems.push(new factoryProduction(0,0,0,0,0));
//tmpProdItems.push(new factoryProduction('.$postVals[1].', '.($thisObj->get('prodLength') + $thisObj->get('prodStart')).','.$thisObj->get('currentProd').','.$thisObj->get('prodQty').',6));
for (let i=0; i<5; i++) {
	if (selFactory.productStores[i] > 0) {
		if (selFactory.productStores[i] != '.$thisObj->get('currentProd').')	tmpProdItems.push(new factoryProduction('.$postVals[1].', 0, selFactory.productStores[i], 0,i+1));
		else {
			tmpProdItems.push(new factoryProduction('.$postVals[1].', '.($thisObj->get('prodLength') + $thisObj->get('prodStart')).','.$thisObj->get('currentProd').','.$thisObj->get('prodQty').',i+1));
			selectedIndex = i+1;
		}
	}
}
console.log(tmpProdItems)
prodList = new uList(tmpProdItems);
factoryDiv.headSection = addDiv("", "stdFloatDiv", factoryDiv);
factoryDiv.headSection.style.height = 250;
factoryDiv.headSection.rate = textBlob("", factoryDiv.headSection, "Rate: '.($thisObj->get('prodRate')/100).'<br>Lifetime Earnings: $'.($thisObj->get('totalSales')/100).'<br>Period Earnings: $'.($thisObj->get('periodSales')/100).'");

contractsButton = newButton(factoryDiv.headSection, function () {
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

sellButton = newButton(factoryDiv.headSection, function () {scrMod("1043,'.$postVals[1].'")});
sellButton.innerHTML = "Sell Factory";

sendButton = newButton(factoryDiv.headSection, function () {scrMod("1005,'.$postVals[1].',"+ SLreadSelection(factoryProductionBox))});
sendButton.innerHTML = "Set production";

startButton1 = newButton(factoryDiv.headSection, function () {scrMod("1028,'.$postVals[1].',1")});
startButton1.innerHTML = "Work for - 1 hour";

startButton2 = newButton(factoryDiv.headSection, function () {scrMod("1028,'.$postVals[1].',2")});
startButton2.innerHTML = "Work for - 2 hour";

startButton3 = newButton(factoryDiv.headSection, function () {
	selFactory.startProduction(3, this.parentNode.parentNode.prodContain)
});
startButton3.innerHTML = "Work for - 4 hour";

startButton4 = newButton(factoryDiv.headSection, function () {
	setupPromise("1028,'.$postVals[1].',4").then(v => {
		let result = setArrayInts(v.split(","));
		console.log(result);
		if (result[0] < 0) {
			switch (result[0]) {
				case -1:
					console.log("resource qty error");
					break;
			}
		} else {
		let fProduction = new factoryProduction(result[0], result[1], result[2], result[3]);
		this.parentNode.parentNode.prodContain.innerHTML = "";
		let fProductionBox = fProduction.render(this.parentNode.parentNode.prodContain);

		selFactory.materialInv = result.slice(4);
		selFactory.showInventory(factoryDiv.reqBox.stores);
		}
	})
});
startButton4.innerHTML = "Work for - 8 hour";

factoryDiv.prodContain = addDiv("", "orderContain", factoryDiv.headSection);
selFactory.production = new factoryProduction('.$postVals[1].', '.($thisObj->get('prodLength') + $thisObj->get('prodStart')).', '.$thisObj->get('currentProd').', 100, '.$thisObj->get('currentProd').');
//fProductionBox = selFactory.production.render(factoryDiv.prodContain);
console.log(prodList);
console.log(selectedIndex);
factoryProductionBox = prodList.SLsingleButton(factoryDiv.prodContain, {setVal:selectedIndex});

upgradeButton = newButton(factoryDiv.headSection, function () {
	resourceQuery(factoryUpgradeProducts, factoryUpgradeServices, function () {
		scrMod("1031,'.$postVals[1].'");})
	});
upgradeButton.innerHTML = "Upgrade Factory";

factoryDiv.productInvSection = addDiv("", "stdFloatDiv", factoryDiv);
factoryDiv.productInvSection.innerHTML = "INVENTORY";
textBlob("", factoryDiv.productInvSection, "Output Inventory");

//showOutputs(factoryDiv.productInvSection, productStores);
selFactory.showOutputs(factoryDiv.productInvSection);

salesSection = addDiv("", "stdFloatDiv", factoryDiv);
saleButton = newButton(salesSection, function () {scrMod("1013,'.$postVals[1].'")});
saleButton.innerHTML = "Sell Products";

factoryDiv.laborSection = addDiv("", "stdFloatDiv", factoryDiv);
factoryDiv.laborPool = addDiv("", "stdFloatDiv", factoryDiv);
textBlob("", factoryDiv.laborPool, "Unassigned Labor");
factoryDiv.laborSection.aassigned = addDiv("", "stdFloatDiv", factoryDiv.laborSection);
textBlob("", factoryDiv.laborSection.aassigned, "Labor working here");

//showLabor('.$postVals[1].', factoryLabor);
selFactory.showLabor(factoryDiv.laborSection.aassigned);

factoryDiv.reqBox = addDiv("", "stdFloatDiv", factoryDiv);
textBlob("", factoryDiv.reqBox, "Per unit of production, this requires:");
factoryDiv.reqBox.materials = addDiv("", "stdFloatDiv", factoryDiv.reqBox);

//showProdRequirements(reqBox.materials, productMaterial);
selFactory.showProdRequirements(factoryDiv.reqBox.materials);

factoryDiv.laborSection.required = addDiv("", "stdFloatDiv", factoryDiv.laborSection);
//showRequiredLabor(factoryDiv.laborSection.required, productLabor);
selFactory.showReqLabor(factoryDiv.laborSection.required);

factoryDiv.reqBox.stores = addDiv("", "stdFloatDiv", factoryDiv);
//showInventory('.$postVals[1].', materialInv);
selFactory.showInventory(factoryDiv.reqBox.stores);

var orderSection = addDiv("", "stdFloatDiv", factoryDiv);
var orderHead = addDiv("", "stdFloatDiv", orderSection);

factoryDiv.orderItems = addDiv("", "stdFloatDiv", orderSection);
factoryDiv.saleItems = addDiv("", "stdFloatDiv", orderSection);
textBlob("", orderHead, "Current orders");

//showOrders(factoryDiv.orderItems, factoryOrders);
selFactory.showOrders(factoryDiv.orderItems);

//showSales(factoryDiv.saleItems, factorySales);
selFactory.showSales(factoryDiv.saleItems);

factoryDiv.contracts = addDiv("", "stdFloatDiv", factoryDiv);
//factoryContracts(['.implode(',', array_merge(unpack('i*', $contractStr), $invoiceSend)).'], factoryDiv.contracts);
selFactory.showContracts(factoryDiv.contracts);

</script>';

fclose($offerDatFile);

?>
