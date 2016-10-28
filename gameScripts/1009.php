<?php

/* Find options for purchasing resources for a factory
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);
$thisObj->updateStocks();

// confirm that the selected item is a valid item for this factory
$optionCheck = false;
$productID = thisObj->templateDat[11 + $postVals[2]]
for ($i=0; $i<5; $i++) {
	if ($thisObj->templateDat[11] == $productID) {
		$optionCheck = true;
		break;
	}
}

// confirm that there are order spots available
$spotCheck = 0;
$orderItems = $thisObj->materialOrders();
for ($i=0; $i<10; $i++) {
	if ($orderItems[$i*3] == 0) {
		$spotCheck = $i;
		break;
	}
}

if ($optionCheck && $spotCheck > 0) {
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