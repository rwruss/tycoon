<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);

echo '<script>
useDeskTop.newPane("factorySales");
thisDiv = useDeskTop.getPane("factorySales");
thisDiv.innerHTML = "";

materialInv = ['.implode(',', $thisObj->resourceStores).'];
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];

prodList = new saleList([new product({objID:'.$thisObj->getTemp('prod1').', qty:'.$thisObj->get('prodInv1').'})';
for ($i=2; $i<6; $i++) {
	if ($thisObj->getTemp('prod'.$i)>0) echo ', new product({objID:'.$thisObj->getTemp('prod'.$i).', qty:'.$thisObj->get('prodInv'.$i).'})';
}
echo '])
var lotSection = addDiv("", "standardContain", thisDiv);
textBlob("", lotSection, "Sell on Market");
saleBox1 = prodList.SLsingleButton(lotSection);
salePrice = priceBox(lotSection,0.00);

sendButton = newButton(lotSection, function () {scrMod("1014,'.$postVals[1].',"+ SLreadSelection(saleBox1) + "," + salePrice.value)});
sendButton.innerHTML = "Create Offer";

var citySales = addDiv("", "standardContain", thisDiv);
textBlob("", citySales, "Sell in Cities");
citySB = newButton(citySales, function () {scrMod("1015,'.$postVals[1].'")});
citySB.innerHTML = "Sell to City";
</script>';

fclose($objFile);

?>
