<?php

/* Find options for purchasing resources for a factory
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);
//if ($thisObj->get('currentProd') > 0) $thisObj->updateStocks();

// confirm that the selected item is a valid item for this factory
$optionCheck = false;
//$productID = $thisObj->templateDat[16 + $postVals[2]];
$productID = $postVals[2];
for ($i=0; $i<20; $i++) {
	if ($thisObj->templateDat[16+$i] == $productID) {
		$optionCheck = true;
		break;
	}
}
print_R($thisObj->templateDat);
// Load product information
//$thisProduct = loadObject($productID, $objFile, 1000);
echo 'loaded a '.$productID;
fseek($objFile, 1000*$productID);
$productData = unpack('i*', fread($objFile, 1000));
//print_r($productData);
//
if (!$optionCheck) exit('error 9001-1');
// confirm that there are order spots available
$spotCheck = false;
$orderItems = $thisObj->materialOrders();
//print_R($orderItems);
for ($i=0; $i<10; $i++) {
	if ($orderItems[$i*3] == 0) {
		$spotCheck = $i;
		break;
	}
}

if ($spotCheck === false) exit('error 9001-2');

if ($optionCheck && $spotCheck !== false) {
	// Search for new items to produce
	$offerList = new blockSlot($productID, $offerFile, 4000);

	// Sort offers based on price low to high
	$offerSize = sizeof($offerList->slotList);
	for ($i=1; $i<=$offerSize; $i+=10) {
		if ($offerList->slotList[$i] > 0) {
			$priceList[$i] = $offerList->slotList[$i+1];
		}
	}

	echo '<script>offerList = [];';


	for ($i=1; $i<=$offerSize; $i+=10) {

			echo 'offerList.push(new offer([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]))';

	}
	echo 'showOffers = new uList(offerList);
		showOffers.addSort("price", "Price");
		showOffers.addSort("quantity", "Amount");
		showOffers.addSort("quality", "Quality");
		showOffers.addSort("rights", Rights);
		showOffers.addSort("pollution", "Pollution");

		orderBox1 = showOffers.SLsingleButton(orderPane);

		orderButton = newButton(thisDiv, function () {scrMod("1010,'.$postVals[1].', SLreadSelection(orderBox1).")});
		orderButton.innerHTML = "Place Order";
		</script>';
}

fclose($offerFile);
fclose($objFile);

?>
