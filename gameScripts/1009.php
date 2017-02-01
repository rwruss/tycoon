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
$productID = $postVals[3];
for ($i=0; $i<20; $i++) {
		echo $thisObj->templateDat[16+$i].' vs '.$productID.'<br>';
	if ($thisObj->templateDat[16+$i] == $productID) {

		$optionCheck = true;
		break;
	}
}
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
	$offerList = new blockSlot($productID, $offerFile, 4004);

	// Sort offers based on price low to high
	echo 'Offer Slot';
	//print_r($offerList->slotData);
	$offerSize = sizeof($offerList->slotData);
	for ($i=1; $i<=$offerSize; $i+=10) {
		if ($offerList->slotData[$i] > 0) {
			$priceList[$i] = $offerList->slotData[$i+1];
		}
	}
	/* {
	0 - object ID & product ID
	1 - quantity
	2 - price
	3 - seller ID
	4 - quality
	5 - pollution
	6 - rights

	}	*/
	echo '<script>
	var orderPane = useDeskTop.getPane("xyzPane");
	offerArea = addDiv("", "", orderPane);
	offerList = [];
	offerList.push(new offer([0, 100, 299, 0, 50, 50, 50, '.$productID.', 8, 9, 10]));';


	$numItems = sizeof($offerList->slotData);
	for ($slotItem=1; $slotItem<$numItems; $slotItem+=10) {
		$placeNum = $offerList->slotList[floor($slotItem/1000)]*4004+$slotItem%1000;
		if ($offerList->slotData[$slotItem] > 0) echo 'offerList.push(new offer(['.$placeNum.', '.$offerList->slotData[$slotItem].', '.$offerList->slotData[$slotItem+1].', 3, 4, 5, 6, '.$productID.', 8, 9, 10]));';
	}
	
echo 'showOffers = new uList(offerList);
	showOffers.showAll(offerArea, function(x, y) {
		let offerItem = x;
		let item = x.renderSummary(y);
		item.buyBox.addEventListener("click", scrMod("1010,'.$postVals[1].',"+offerItem.objID));
		});
	</script>';
/*
	echo 'console.log(offerList);
		showOffers = new uList(offerList);
		console.log("parentList");
		console.log(showOffers.parentList);
		showOffers.addSory("distance", "Distance");
		showOffers.addSort("price", "Price");
		showOffers.addSort("quantity", "Amount");
		showOffers.addSort("quality", "Quality");
		showOffers.addSort("rights", "Rights");
		showOffers.addSort("pollution", "Pollution");

		orderBox2 = showOffers.SLsingleButton(orderPane.offerContainer);
		orderBox2.click();

		//orderButton = newButton(orderPane.offerContainer, function () {console.log(SLreadSelection(orderBox1))});
		orderButton = newButton(orderPane.offerContainer, function () {scrMod("1010,'.$postVals[1].'," + SLreadSelection(orderPane.orderBox1) + "," +  SLreadSelection(orderBox2))});
		orderButton.innerHTML = "Place Order";
		</script>';*/
}

fclose($offerFile);
fclose($objFile);

?>
