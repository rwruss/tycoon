<?php

$now = time();
$constructDelta = $thisObj->get('constructCompleteTime') - $now;
if ($thisObj->get('factoryLevel') == 0) {
	// Check if construction of the factory is complete
	if ($constructDelta > 0) {
		echo '<script>
		selectedFactory = '.$postVals[1].';
		businessDiv.innerHTML = "";
		textBlob("", businessDiv, "This facility is still being built.  Would you like to speed it up?;");
		/*
		var buildTimeBox = addDiv("", "orderTime", businessDiv);
		buildTimeBox.runClock = true;
		countDownClock('.($thisObj->get('constructCompleteTime')).', buildTimeBox, function () {console.log("finish factory construction")});
		speedUpButton = newButton(businessDiv, function () {scrMod("1029,1,'.$postVals[1].'")});
		speedUpButton.innerHTML = "Speed Up Construction";
		console.log("done");*/
		thisUpgrade = new factoryUpgrade('.$postVals[1].', '.($thisObj->get('constructCompleteTime')).');
		thisUpgrade.render(businessDiv);
		</script>';
		exit();
	}
}

if ($constructDelta > 0) {
	//echo 'Upgrade to level '.($thisObj->get('factoryLevel') + 1).' is in progress.  '.($constructDelta).' remaining to complete;';
}

$thisObj->updateStocks();
if ($thisObj->get('currentProd') > 0) {
	$currentProduction = ', {setVal:'.$thisObj->get('currentProd').'}';
} else $currentProduction = '';

$currentProduction = ', {setVal:'.$thisObj->get('currentProd').'}';


//echo 'Load object '.$thisObj->get('currentProd');
$productInfo = loadProduct($thisObj->get('currentProd'), $objFile, 400);

echo '<script>
factoryUpgradeProducts = [];
factoryUpgradeServices = [];
selectedFactory = '.$postVals[1].';
businessDiv.innerHTML = "";';

if ($constructDelta > 0) {
	echo 'var updateArea = addDiv("", "stdFloatDiv", businessDiv);';

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
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];
inProduction = ['.$thisObj->get('prodLength').', '.$thisObj->get('prodStart').', '.$thisObj->get('prodQty').'];

factoryOrders = new Array();
for (var i=0; i<materialOrder.length; i+=3) {
	factoryOrders.push(new factoryOrder('.$postVals[1].', materialOrder[i], materialOrder[i+1], materialOrder[i+2], i/3));
}
factoryLabor = [new laborItem({objID:0, pay:0, ability:0, laborType:0})';
$startVals = [];
for ($i=0; $i<10; $i++) {
	if ($thisObj->objDat[$thisObj->laborOffset + $i*10] == 0) {
		$useID = 0;
		$startVals[$i] = 0;
	} else {
		$startVals[$i] = $i+1;
		$useID = $i+1;
	}
	echo ', new laborItem({objID:"'.$useID.'", pay:'.$thisObj->objDat[$thisObj->laborOffset+5 + $i*10].', ability:'.$thisObj->objDat[$thisObj->laborOffset+8 + $i*10].', laborType:'.$thisObj->objDat[$thisObj->laborOffset+1 + $i*10].'})';
}

echo '];

inventoryItems = [];
for (i=0; i<materialInv.length; i+=2) {
	inventoryItems.push(new product({objID:materialInv[i]}));
}
invList = new uList(inventoryItems);

prodList = new uList([new product({objID:0}), new product({objID:'.$thisObj->getTemp('prod1').'})';

for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo ']);
headSection = addDiv("", "stdFloatDiv", businessDiv);
headSection.rate = textBlob("", headSection, "Rate: '.($thisObj->get('prodRate')/100).'<br>Lifetime Earnings: $'.($thisObj->get('totalSales')/100).'<br>Period Earnings: $'.($thisObj->get('periodSales')/100).'");

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
//console.log(fProductionBox);
factoryProductionBox = prodList.SLsingleButton(fProductionBox'.$currentProduction.');

upgradeButton = newButton(headSection, function () {
	resourceQuery(factoryUpgradeProducts, factoryUpgradeServices, function () {
		scrMod("1031,'.$postVals[1].'");})
	});
upgradeButton.innerHTML = "Upgrade Factory";

productInvSection = addDiv("", "stdFloatDiv", businessDiv);
textBlob("", productInvSection, "Output Inventory");

saleButton = newButton(productInvSection, function () {scrMod("1013,'.$postVals[1].'")});
saleButton.innerHTML = "Sell Products";
showOutputs(productInvSection, productStores);

businessDiv.laborSection = addDiv("", "stdFloatDiv", businessDiv);
businessDiv.laborPool = addDiv("", "stdFloatDiv", businessDiv);
textBlob("", businessDiv.laborPool, "Unassigned Labor");
businessDiv.laborSection.aassigned = addDiv("", "stdFloatDiv", businessDiv.laborSection);
textBlob("", businessDiv.laborSection.aassigned, "Labor Pool - show available labor");
for (var i=1; i<factoryLabor.length; i++) {

	let laborItem = factoryLabor[i].renderSummary(businessDiv.laborSection.aassigned);
	let itemNum = i;
	if (factoryLabor[i] > 0) 	{}
	laborItem.addEventListener("click", function () {scrMod("1023,'.$postVals[1].',"+itemNum)});
}
laborButton = newButton(businessDiv.laborSection.aassigned, function () {scrMod("1018,'.$postVals[1].'")});
laborButton.innerHTML = "Adjust Labor";

reqBox = addDiv("", "stdFloatDiv", businessDiv);
textBlob("", reqBox, "Per unit of production, this requires:");
reqBox.materials = addDiv("", "stdFloatDiv", reqBox);
showProdRequirements(reqBox.materials, productMaterial);

businessDiv.laborSection.required = addDiv("", "stdFloatDiv", businessDiv.laborSection);
showRequiredLabor(businessDiv.laborSection.required, productLabor);


reqBox.stores = addDiv("", "stdFloatDiv", businessDiv);
showInventory('.$postVals[1].', materialInv);

var orderSection = addDiv("", "stdFloatDiv", businessDiv);
var orderHead = addDiv("", "stdFloatDiv", orderSection);

businessDiv.orderItems = addDiv("", "stdFloatDiv", orderSection);
textBlob("", orderHead, "Current orders");
showOrders(businessDiv.orderItems, factoryOrders);
/*

*/
</script>';

?>
