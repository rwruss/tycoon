<?php

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
productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
productLabor = ['.implode(',', $productInfo->reqLabor).'];
materialInv = ['.implode(',', $thisObj->resourceStores).'];
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];

textBlob("", thisDiv, "Currently Producting:" + objNames['.$thisObj->get('currentProd').']);

prodList = new uList([new product({objID:'.$thisObj->getTemp('prod1').'})';
for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo '])

optionBox1 = prodList.SLsingleButton(thisDiv'.$currentProduction.');

sendButton = newButton(thisDiv, function () {scrMod("1005,'.$postVals[1].',"+ SLreadSelection(optionBox1))});
sendButton.innerHTML = "Update production";

priceButton = newButton(thisDiv, function () {scrMod("1011,'.$postVals[1].'")});
priceButton.innerHTML = "Set Prices";

textBlob("", thisDiv, "Per unit of production, this requires:");
for (var i=0; i<productMaterial.length; i+=2) {
	materialBox(productMaterial[i], productMaterial[i+1], thisDiv);
}
for (var i=0; i<productLabor.length; i++) {
	laborBox(productLabor[i], thisDiv);
}

textBlob("", thisDiv, "Current resource stores:");
for (var i=0; i<materialInv.length; i+=2) {
	materialBox(materialInv[i], materialInv[i+1], thisDiv);
}

textBlob("", thisDiv, "Current orders");
for (var i=0; i<materialOrder.length; i+=3) {
	var thisBox = orderBox(materialOrder[i], materialOrder[i+1], materialOrder[i+2], thisDiv);
	if (materialOrder[i] == 0) thisBox.addEventListener("click", function () {
		useDeskTop.newPane("orderPane");
		orderPane = useDeskTop.getPane("orderPane");

		textBlob("", thisDiv, "Select which item you want to order");
		orderBox1 = prodList.SLsingleButton(materialInv);

		orderSelectButton = newButton(thisDiv, function () {scrMod("1009,'.$postVals[1].', SLreadSelection(optionBox1).")});
		orderSelectButton.innerHTML = "Select Item";
		//scrMod("1009,'.$postVals[1].'")
		});
}

</script>';

?>
