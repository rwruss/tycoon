<?php

/* Find options for purchasing resources for a factory
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

//$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);

// confirm that the selected item is a valid item for this factory
$optionCheck = false;
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
//echo 'loaded a '.$productID;
//fseek($objFile, 1000*$productID);
//$productData = unpack('i*', fread($objFile, 1000));

if (!$optionCheck) exit('error 9001-1');
// confirm that there are order spots available
$spotCheck = false;
$orderItems = $thisObj->materialOrders();
for ($i=0; $i<10; $i++) {
	if ($orderItems[$i*3] == 0) {
		$spotCheck = $i;
		break;
	}
}

if ($spotCheck === false) exit('error 9001-2');

if ($optionCheck && $spotCheck !== false) {
	// Load the list of offers for the product
	$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
	$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb');
	
	// Search for new items to produce
	$offerList = new itemSlot($productID, $offerListFile, 1004);
	$emptyOffers = new itemSlot(0, $offerListFile, 1004);
	
	$offerCount = 0;
	$checkCount = 1;
	$numOffers = sizeof($offerList->slotData);
	$showOffers = [0, 100, 299, 0, 50, 50, 50, $productID, 8, 9, 10];
	while ($checkCount < $numOffers && $offerCount < 100) {
		fseek($offerDatFile, $offerList->slotData[$checkCount]*40);
		$tmpDat = unpack('i*', fread($offerDatFile, 40);
		if ($tmpDat[1] > 0) {
			array_push($showOffers, $offerList->slotData[$checkCount], $productID);
			$showOffers = array_merge($showOffers, $tmpDat);
		} else {
			// Delete the reference in the list of offers
			$offerList->deleteItem($checkCount, $offerListFile);
			
			// Add to list of empty offers
			$emptyOffers->addItem($offerList->slotData[$checkCount]);
		}
	}

	echo '<script>receiveOffers(['.implode(',', $showOffers).'])</script>';
	/* {
	0 - object ID & product ID
	1 - quantity
	2 - price
	3 - seller ID
	4 - quality
	5 - pollution
	6 - rights
	
	this.objID = details[0];
		this.qty = details[1];
		this.price = details[2];
		this.sellingFactory = details[3];
		this.quality = details[4];
		this.pollution = details[5];
		this.rights = details[6];
		this.productID = details[7];
		this.seller = details[8];
		this.sellCong = details[9];

	}	
	echo '<script>
	var orderPane = useDeskTop.getPane("xyzPane");
	offerArea = addDiv("", "", orderPane);
	offerList = [];
	offerList.push(new offer([0, 100, 299, 0, 50, 50, 50, '.$productID.', 8, 9, 10]));';


	$numItems = sizeof($offerList->slotData);
	for ($slotItem=1; $slotItem<$numItems; $slotItem+=10) {
		$placeNum = $offerList->slotList[floor($slotItem/1000)]*4004+($slotItem%1000)*4;
		if ($offerList->slotData[$slotItem] > 0) echo 'offerList.push(new offer(['.$placeNum.', '.$offerList->slotData[$slotItem].', '.$offerList->slotData[$slotItem+1].', 3, 4, 5, 6, '.$productID.', 8, 9, 10]));';
	}

	
	echo 'showOffers = new uList(offerList);
	showOffers.SLShowAll(offerArea, function(x, y) {
		let offerItem = x;
		let item = x.renderSummary(y);
		item.buyBox.addEventListener("click", function () {scrMod("1010,'.$postVals[1].',"+offerItem.objID + "," +  SLreadSelection(orderPane.orderBox1))});
		});
	</script>';
	*/
}

fclose($offerFile);
fclose($objFile);

?>
