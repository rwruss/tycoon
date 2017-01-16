<?php

$now = time();
$constructDelta = $thisObj->get('constructCompleteTime') - $now;
if ($thisObj->get('factoryLevel') == 0) {
	// Check if construction of the factory is complete
	if ($constructDelta > 0) {
		echo '<script>
		selectedFactory = '.$postVals[1].';
		thisDiv.innerHTML = "";
		textBlob("", thisDiv, "This facility is still being built.  Would you like to speed it up?;");
		var buildTimeBox = addDiv("", "orderTime", thisDiv);
		buildTimeBox.runClock = true;
		countDownClock('.($thisObj->get('constructCompleteTime')).', buildTimeBox, function () {console.log("finish factory construction")});
		speedUpButton = newButton(thisDiv, function () {scrMod("1029,1,'.$postVals[1].'")});
		speedUpButton.innerHTML = "Speed Up Construction";
		console.log("done");
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

if ($thisObj->get('currentProd') == 0) {
	//$thisObj->save('currentProd', 1);
	//$thisObj->set('currentProd', 1);
}
//echo 'Load object '.$thisObj->get('currentProd');
$productInfo = loadProduct($thisObj->get('currentProd'), $objFile, 400);
//print_r($thisObj->objDat);
echo '<script>
selectedFactory = '.$postVals[1].';
thisDiv.innerHTML = "";';

if ($constructDelta > 0) {
	echo 'var updateArea = addDiv("", "stdFloatDiv", thisDiv);';

	if ($thisObj->get('factoryLevel') == 0) {
		echo 'textBlob("", updateArea, "Building factory");
		buildTimeBox = addDiv("", "timeFloat", updateArea);
		//buildTimeBox.runClock = true;
		countDownClock('.($thisObj->get('constructCompleteTime')).', buildTimeBox, function () {console.log("finish factory construction")});
		//speedUpButton = addDiv("", "buildSpeedUp", buildTimeBox)
		//newButton(updateArea, function () {scrMod("1029,1,'.$postVals[1].'")});
		//speedUpButton.innerHTML = "S";
		buildTimeBox.boostBox.addEventListener("click", function () {scrMod("1029,1,'.$postVals[1].'")});';
	} else {
		echo 'textBlob("", updateArea, "Upgrading factory to level '.$thisObj->get('upgradeInProgress').'");
		buildTimeBox = addDiv("", "timeFloat", updateArea);
		//buildTimeBox.runClock = true;
		countDownClock('.($thisObj->get('constructCompleteTime')).', buildTimeBox, function () {console.log("finish factory construction")});
		//speedUpButton = addDiv("", "buildSpeedUp", buildTimeBox)
		//newButton(updateArea, function () {scrMod("1029,1,'.$postVals[1].'")});
		//speedUpButton.innerHTML = "S";
		buildTimeBox.boostBox.addEventListener("click", function () {scrMod("1029,1,'.$postVals[1].'")});';
	}
}

echo '
productStores = ['.implode(',', $thisObj->tempList).','.implode(',', $thisObj->productStores).']
productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
productLabor = ['.implode(',', $productInfo->reqLabor).'];
materialInv = ['.implode(',', $thisObj->resourceInv()).'];
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];
inProduction = ['.$thisObj->get('prodLength').', '.$thisObj->get('prodStart').', '.$thisObj->get('prodQty').'];
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
console.log(materialInv);
for (i=0; i<materialInv.length; i+=2) {
	inventoryItems.push(new product({objID:materialInv[i]}));
	//console.log("Add to inv: " + materialInv[i]);
}
invList = new uList(inventoryItems);

var someProduct = new product({objID:999});
prodList = new uList([new product({objID:0}), new product({objID:'.$thisObj->getTemp('prod1').'})';

for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo ']);
var headSection = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", headSection, "Rate: '.($thisObj->get('currentRate')/100).'<br>Lifetime Earnings: $'.($thisObj->get('totalSales')/100).'<br>Period Earnings: $'.($thisObj->get('periodSales')/100).'");

sendButton = newButton(headSection, function () {scrMod("1005,'.$postVals[1].',"+ SLreadSelection(optionBox1))});
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
optionBox1 = prodList.SLsingleButton(prodContain'.$currentProduction.');

upgradeButton = newButton(headSection, function () {scrMod("1031,'.$postVals[1].'")});
upgradeButton.innerHTML = "Upgrade Factory";
';
if ($thisObj->get('prodStart') > 0) {
	echo '
		optionBox1.qtyDiv = addDiv("asdf", "productQty", optionBox1);
		optionBox1.qtyDiv.innerHTML = '.$thisObj->get('prodQty').';

		var timeBox = addDiv("", "orderTime", prodContain);
		timeBox.runClock = true;
		countDownClock('.($thisObj->get('prodLength') + $thisObj->get('prodStart')).', timeBox, function () {console.log("update factory")});';
}
echo '
var productInvSection = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", productInvSection, "Output Inventory");

saleButton = newButton(productInvSection, function () {scrMod("1013,'.$postVals[1].'")});
saleButton.innerHTML = "Sell Products";
for (var i=0; i<5; i++) {
	if (productStores[i]>0) {
		productArray[productStores[i]].renderQty(productInvSection, productStores[i+5]);
	}
}

var laborSection = addDiv("", "stdFloatDiv", thisDiv);
laborSection.aassigned = addDiv("", "stdFloatDiv", laborSection);
textBlob("", laborSection.aassigned, "Labor Pool - show available labor");
for (var i=1; i<factoryLabor.length; i++) {

	let laborItem = factoryLabor[i].renderSummary(laborSection.aassigned);
	//let laborItem = laborBox(factoryLabor[i], laborSection.aassigned);
	let itemNum = i;
	laborItem.addEventListener("click", function () {scrMod("1023,'.$postVals[1].',"+itemNum)});
}
laborButton = newButton(laborSection.aassigned, function () {scrMod("1018,'.$postVals[1].'")});
laborButton.innerHTML = "Adjust Labor";

reqBox = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", reqBox, "Per unit of production, this requires:");
reqBox.materials = addDiv("", "stdFloatDiv", reqBox);
for (var i=0; i<productMaterial.length; i+=2) {
	materialBox(productMaterial[i], productMaterial[i+1], reqBox.materials);
}
laborSection.required = addDiv("", "stdFloatDiv", laborSection);
for (var i=0; i<productLabor.length; i++) {
	laborBox(productLabor[i], laborSection.required);
}

reqBox.stores = addDiv("", "stdFloatDiv", thisDiv);
showInventory('.$postVals[1].', materialInv);


if (invList.parentList.length > 0) {
	var orderSection = addDiv("", "stdFloatDiv", thisDiv);
	var orderHead = addDiv("", "stdContain", orderSection);
	orderItems = addDiv("", "stdContain", orderSection);
	textBlob("", orderHead, "Current orders");
	showOrders(materialOrder, '.$postVals[1].', orderItems);

}

</script>';

?>
