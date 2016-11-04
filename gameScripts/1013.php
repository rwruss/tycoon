<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);

echo '
useDeskTop.newPane("factorySales");
thisDiv = useDeskTop.getPane("factorySales");
thisDiv.innerHTML = "";
productMaterial = ['.implode(',', $productInfo->reqMaterials).'];
productLabor = ['.implode(',', $productInfo->reqLabor).'];
materialInv = ['.implode(',', $thisObj->resourceStores).'];
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];

prodList = new saleList([new product({objID:'.$thisObj->getTemp('prod1').'})';
for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).'})';
}
echo ']);

saleBox1 = prodList.SLsingleButton(thisDiv'.$currentProduction.');
salePrice = priceBox(thisDiv,0.00);

sendButton = newButton(thisDiv, function () {scrMod("1014,'.$postVals[1].',"+ SLreadSelection(saleBox1) + "," + salePrice.value)});
sendButton.innerHTML = "Create Offer";';

fclose(objFile);

?>