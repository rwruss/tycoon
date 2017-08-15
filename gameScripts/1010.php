<?php

/*
1010 - PROCESS: Order materials for a factory
PostVals
1 = factory ID
2 = Order ID
3 = Select Object Type (not used)
4 = product type
5 = order qty
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');
require_once('./invoiceFunctions.php');
require_once('./taxCalcs.php');

$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b'); //r+b
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b'); // r+b
$cityFile = fopen($gamePath.'/cities.dat', 'rb'); //rb
$objFile = fopen($gamePath.'/objects.dat', 'rb'); //r+b
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b'); // r+b

$buyingPlayer = loadObject($pGameID, $objFile, 400);
$buyingFactory = loadObject($postVals[1], $objFile, 1600);
$now = time();

$taxes = array_fill(0,31,0);

// Verify that there are open order spots
$spotFail = TRUE;
$orderNumber = 0;
$orderItems = $buyingFactory->materialOrders();
  for ($i=0; $i<10; $i++) {
    if ($orderItems[$i] == 0) {
    $orderNumber = $i;
    $spotFail = FALSE;
    break;
   }
 }

if ($spotFail) exit('error 0101-1');

if ($postVals[2] == 0) {
  /*
  // this is the default offer;
  echo 'default offer';
  $offerDat = [0, 100, 0, 299, 50, 50, 50, 0, 0, 0];

  // check that the player has enough money
  $moneyCheck = true;
  $baseCost = $offerDat[1]*$offerDat[2];
  if ($baseCost <= $buyingPlayer->get('money')) $moneyCheck = false;

  if ($moneyCheck) exit('not enough money for this deal');

  // deduct the money from this player
  $buyingPlayer->set('money', $buyingPlayer->get('money')-$baseCost);

  // record in this players pending order slot

	$buyingFactory->save($buyingFactory->orderListStart+$orderNumber, $postVals[2]);
  */
} else {
  // load the specific offer
  //$offerList = new blockSlot($postVals[3], $offerDatFile, 4000);
  if (flock($offerDatFile, LOCK_EX)) {
    //fseek($offerDatFile, $postVals[2]);
    //$offerDat = unpack('i*', fread($offerDatFile, 64));
	$thisOffer = loadOffer($postVals[2], $offerDatFile);
	$offerDat = $thisOffer->objDat;
    //print_r($offerDat);
    if ($offerDat[1] > 0 ) {
      // offer still available
    } else {
      exit('offer no longer available');
    }
    $baseCost = $offerDat[1]*$offerDat[2];

	$purchaseQty = min($postVals[5], $offerDat[1]);

  $transaction = array_fill(0, 25, 0);
  $transaction[1] = $purchaseQty;  // purchased qty
  $transaction[2] = $offerDat[2]; // accepted price
  $transaction[3] = $offerDat[3]; // selling factory ID
  $transaction[5] = $offerDat[5]; // pollution
  $transaction[6] = $offerDat[6]; // rights
  $transaction[7] = $offerDat[11]; // product ID
  $transaction[14] = $offerDat[14];
  $transaction[15] = $offerDat[15];

  $sellingFactory = loadObject($offerDat[3], $objFile, 1600);
  $sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile);
  $buyingCity = loadCity($buyingFactory->get('region_3'), $cityFile);
  $sellingPlayer = loadObject($sellingFactory->get('owner'), $objFile, 400);

  $taxRates = taxRates ($transaction, $sellingFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile); //($materialCost, $laborCost, $sellFactory, $buyingCity, $sellingCity, $sellingPlayer, $slotFile) {
  $taxAmounts = taxCost($taxRates, $transaction);

  	$totalTax = array_sum($taxAmounts);
  	$sellerTax = $totalTax - $taxAmounts[7]-$taxAmounts[17]-$taxAmounts[27]-$taxAmounts[29];
  	$buyerTax = $taxAmounts[7]+$taxAmounts[17]+$taxAmounts[27]+$taxAmounts[29];

    echo 'Buyer Cost is '.$baseCost.' + '.$taxAmounts[7].' + '.$taxAmounts[17].' + '.$taxAmounts[27].' + '.$taxAmounts[29];

    // check that the player has enough money
    $moneyCheck = true;
    $buyerCost = $baseCost + $buyerTax; // Quantity * Unit Price * import tax percent
    if ($buyerCost < $buyingPlayer->get('money')) $moneyCheck = false;

    if ($moneyCheck) exit('not enough money for this deal.  Have'.$buyingPlayer->get('money').' -> Need '.$buyerCost);

    // deduct the money from this player
    $buyingPlayer->set('money', $buyingPlayer->get('money')-$buyerCost);

  	// Record the taxes paid
  	for ($i=1; $i<7; $i++) {
  		$sellingFactory->objDat[$sellingFactory->paidTaxOffset + $i] += $taxAmounts[$i];
  		$sellingFactory->objDat[$sellingFactory->paidTaxOffset + $i+10] += $taxAmounts[$i+10];
  		$sellingFactory->objDat[$sellingFactory->paidTaxOffset + $i+20] += $taxAmounts[$i+20];
  	}

  	// Save taxes due to the selling region/city
  	$sllerTaxDat = '';
  	$sellingRegion = $sellingFactory->get('region_2');
  	$sellingNation = $sellingFactory->get('region_1');

  	// Add the tax to city/region/nation treasuries
  	$taxIncomeFile = fopen($gamePath.'/taxReceipts.txf', 'ab');
  	for ($i=1; $i<10; $i++) {
  		$sllerTaxDat .= pack('s*', $sellingFactory->get('region_3'), $i, $taxAmounts[$i]);
  		$sllerTaxDat .= pack('s*', $sellingRegion, $i, $taxAmounts[$i+10]);
  		$sllerTaxDat .= pack('s*', $sellingNation, $i, $taxAmounts[$i+20]);
  	}

  	// Add the tax to the buying nation for any import Tax
  	$buyerTaxDat = '';
  	$buyingNation = $buyingFactory->get('region_1');
  	$buyerTaxDat .= pack('s*', $buyingNation, 9, $taxes[29]);

  	if (flock($taxIncomeFile, LOCK_EX)) {
  		fwrite($taxIncomeFile, $sllerTaxDat);
  		flock($taxIncomeFile, LOCK_UN);
  	}
  	fclose($taxIncomeFile);

    // add the money to the selling player
  	$sellingFactory->set('totalSales', $sellingFactory->get('totalSales')+$baseCost-$sellerTax);
  	$sellingFactory->set('periodSales', $sellingFactory->get('periodSales')+$baseCost-$sellerTax);

  	$sellingPlayer->set('money', $sellingPlayer->get('money')+$baseCost-$sellerTax);

	// Load the list of open/completed offers
    $openOfferList = new itemSlot(0, $offerListFile, 1000, TRUE);

	// remove the order from the selling factory (if all of the order is purchased)
	if ($purchaseQty >= $offerDat[1]) {
    echo 'Full offer: remove offer from factory ('.$purchaseQty.' vs '.$offerDat[1].')';
		$purchaseID = $postVals[2];
		for ($i=1; $i<9; $i++) {

		  if ($sellingFactory->get('offer'.$i) == $postVals[2]) {
			$sellingFactory->set('offer'.$i,0);
			break;
		  }
		}

	// Record the player ordering and the arrival time in the offer list file
	$offerDat[12] = $pGameID;
	$offerDat[13] = $now+60;

	// overwrite the order in the slot List
    $offerList = new itemSlot($postVals[4], $offerListFile, 1000);
    $offerList->deleteByValue($postVals[2], $offerListFile);

	// add order item to the list of available orders
	$openOfferList->addItem($postVals[2]);
	} else {
    echo '<p>Partial order';
		// this is a partial order - make a new order for delivery at the factory
		$newOrder = $offerDat;
		$newOrder[1] = $purchaseQty;
		$newOrder[12] = $pGameID;
		$newOrder[13] =  $now+60;

		// look for an open purchase spot to use or create one
		$purchaseID = 0;
		for ($i=1; $i<$z = sizeof($openOfferList->slotData); $i++) {
			if ($openOfferList->slotData[$z]>0) {
				$purchaseID = $openOfferList->slotData[$i];
				$openOfferList->deleteByValue($openOfferList->slotData[$i], $offerListFile);
				break;
			}
		}
		if ($purchaseID == 0) {
			fseek($offerDatFile, 0, SEEK_END);
			$purchaseID = max(1,ceil(ftell($offerDatFile)/100))*100;
		}

    echo 'Record new offer '.$purchaseID.'<br>';

		fseek($offerDatFile, $purchaseID);
		fwrite($offerDatFile, packArray($newOrder));

		// record the qty remaining in the offer
		$offerDat[1] -= $purchaseQty;
	}
	fseek($offerDatFile, $postVals[2]);
  	fwrite($offerDatFile, packArray($offerDat));
	flock($offerDatFile, LOCK_UN);

    $sellingFactory->saveAll($sellingFactory->linkFile);

  	if ($sellingFactory->get('owner') == $pGameID) {
  		$buyingPlayer->set('money', $buyingPlayer->get('money')+$baseCost);
  	} else {
  		$sellingPlayer = loadObject($sellingFactory->get('owner'), $objFile, 400);
  		echo 'Target money: '.$sellingPlayer->get('money').' + '.$baseCost;
  		$sellingPlayer->save('money', $sellingPlayer->get('money')+$baseCost);
  	}

    // record in this players pending order slot
    echo '<p>Save order #'.$purchaseID.' into spot '.$orderNumber;
  	$buyingFactory->saveItem($buyingFactory->orderListStart+$orderNumber, $purchaseID);
  }
}
// Load updated material order information for this factory
echo 'Load revised orders.<Br>';
$materialOrders = [];
for ($i=0; $i<10; $i++) {
	if ($buyingFactory->objDat[$buyingFactory->orderListStart+$i] > 0) {
    echo 'Load order #'.$i.'<br>';
		fseek($offerDatFile, $buyingFactory->objDat[$buyingFactory->orderListStart+$i]);
		$offerDat = unpack('i*', fread($offerDatFile, 100));
		array_push($materialOrders, $postVals[1], $buyingFactory->objDat[$buyingFactory->orderListStart+$i],$i); //time, id, qty
		$materialOrders = array_merge($materialOrders, $offerDat);
	} else array_push($materialOrders, $postVals[1],0,$i,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0); // header: factory ID, offer ID, spot
}
print_r($materialOrders);
echo '<script>
selFactory.materialOrder = ['.implode(',', $materialOrders).'];
selFactory.showOrders(factoryDiv.orderItems);

thisDiv = useDeskTop.getPane("factoryInfo");
thisPlayer.money = '.$buyingPlayer->get('money').'
</script>';

fclose($offerListFile);
fclose($cityFile);
fclose($offerDatFile);
fclose($objFile);
fclose($slotFile);

?>
