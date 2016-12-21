<?php

$thisObj->updateStocks();
if ($thisObj->get('currentProd') > 0) {
	$currentProduction = ', {setVal:'.$thisObj->get('currentProd').'}';
} else $currentProduction = '';

if ($thisObj->get('currentProd') == 0) {
	//$thisObj->save('currentProd', 1);
	//$thisObj->set('currentProd', 1);
}
//echo 'Load object '.$thisObj->get('currentProd');
$productInfo = loadProduct($thisObj->get('currentProd'), $objFile, 400);

echo '<script>
thisDiv.innerHTML = "";
productStores = ['.implode(',', $thisObj->tempList).','.implode(',', $thisObj->productStores).']
productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
productLabor = ['.implode(',', $productInfo->reqLabor).'];
materialInv = ['.implode(',', $thisObj->resourceStores).'];
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];
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

var someProduct = new product({objID:999});
console.log(someProduct);
prodList = new uList([new product({objID:'.$thisObj->getTemp('prod1').'})';

for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo ']);
textBlob("", thisDiv, "Rate: '.$thisObj->get('currentRate').'");
optionBox1 = prodList.SLsingleButton(thisDiv'.$currentProduction.');

sendButton = newButton(thisDiv, function () {scrMod("1005,'.$postVals[1].',"+ SLreadSelection(optionBox1))});
sendButton.innerHTML = "Set production";

var productInvSection = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", productInvSection, "Output Inventory");
for (var i=0; i<5; i++) {
	if (productStores[i]>0) {
		productArray[productStores[i]].renderQty(productInvSection, productStores[i+5]);
	}
}

var laborSection = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", laborSection, "Labor Pool - show available labor");
for (var i=1; i<factoryLabor.length; i++) {

	let laborItem = factoryLabor[i].renderSummary(laborSection);
	//let laborItem = laborBox(factoryLabor[i], laborSection);
	let itemNum = i;
	console.log("item " + itemNum);
	laborItem.addEventListener("click", function () {scrMod("1023,'.$postVals[1].',"+itemNum)});
}
laborButton = newButton(laborSection, function () {scrMod("1018,'.$postVals[1].'")});
laborButton.innerHTML = "Adjust Labor";

var reqBox = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", reqBox, "Per unit of production, this requires:");
reqBox.materials = addDiv("", "stdFloatDiv", reqBox);

for (var i=0; i<productMaterial.length; i+=2) {
	materialBox(productMaterial[i], productMaterial[i+1], reqBox.materials);
}
reqBox.labor = addDiv("", "stdFloatDiv", reqBox);
for (var i=0; i<productLabor.length; i++) {
	laborBox(productLabor[i], reqBox.labor);
}

var storesSection = addDiv("", "stdContainer", thisDiv);
textBlob("", storesSection, "Current resource stores:");
for (var i=0; i<materialInv.length; i+=2) {
	materialBox(materialInv[i], materialInv[i+1], storesSection);
}

if (invList.parentList.length > 0) {
	var orderSection = addDiv("", "stdContainer", thisDiv);
	var orderHead = addDiv("", "stdContain", orderSection);
	var orderItems = addDiv("", "stdContain", orderSection);
	textBlob("", orderHead, "Current orders");
	for (var i=0; i<materialOrder.length; i+=3) {
		let thisBox = orderBox(materialOrder[i], materialOrder[i+1], materialOrder[i+2], orderItems);
		if (materialOrder[i] == 0) thisBox.addEventListener("click", function () {
			useDeskTop.newPane("orderPane");
			orderPane = useDeskTop.getPane("orderPane");
			console.log(invList);
			textBlob("", orderPane, "Select which item you want to order");
			orderBox1 = invList.SLsingleButton(orderPane);
			orderSelectButton = newButton(orderPane, function () {scrMod("1009,'.$postVals[1].',"+ SLreadSelection(orderBox1))});
			orderSelectButton.innerHTML = "Find Offers";
			offerContainer = addDiv("", "stdContain", orderPane);
			});
	}
}
saleButton = newButton(thisDiv, function () {scrMod("1013,'.$postVals[1].'")});
saleButton.innerHTML = "Sell Products";

</script>';

?>
