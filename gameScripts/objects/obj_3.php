<?php

if ($thisObj->get('currentProd') > 0) {
	$currentProduction = ', {setVal:'.$thisObj->get('currentProd').'}';
} else $currentProduction = '';


$productInfo = loadObject($thisObj->get('currentProd'), $unitFile, 400);

echo '<script>
productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
productLabor = ['.implode(',', $productInfo->reqLabor).'];
materialInv = ['.implode(',', $thisObj->resourceStores).'];
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];

prodList = new uList(['.$thisObj->get('prod1').', '.$thisObj->get('prod2').', '.$thisObj->get('prod3').', '.$thisObj->get('prod4').', '.$thisObj->get('prod5').']);
optionBox1 = prodList.SLsingleButton(thisDiv'.$currentProduction.');

sendButton = newButton(thisDiv, function () {scrMod("1005,'.$postVals[1].', SLreadSelection(optionBox1).")});
sendButton.innerHTML = "Update production";

textBlob("", thisDiv, "Per unit of production, this requires:");
for (var i=0; i<productMaterial.length; i+=2) {
	materialBox(productMaterial[i], productMaterial[i+1], thisDiv);
}
for (var i=0; i<productLabor.length; i++) {
	laborBox(productLabor[i], thisDiv);
}

textBlob("", thisDiv, "Current resource stores:");
for (var i=0; i<productMaterial.length; i+=2) {
	materialBox(productMaterial[i], productMaterial[i+1], thisDiv);
}

textBlob("", thisDiv, "Current orders");
for (var i=0; i<materialOrder.length; i+=3) {
	var thisBox = orderBox(materialOrder[i], materialOrder[i+1], materialOrder[i+2], thisDiv);
	if (materialOrder[i]) == 0) thisBox.addEventListener("click", function () {
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