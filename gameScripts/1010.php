<?php

/*
1010 - PROCESS: Order materials for a factory
PostVals
1 = factory ID
2 = Order ID
3 = Select Object Type (not used)
4 = product type
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$offerListFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

$thisPlayer = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1600);
$now = time();

$taxes = array_fill(0,31,0);

// Verify that there are open order spots
$spotFail = TRUE;
$orderNumber = 0;
$orderItems = $thisFactory->materialOrders();
  for ($i=0; $i<10; $i++) {
    if ($orderItems[$i] == 0) {
    $orderNumber = $i;
    $spotFail = FALSE;
    break;
   }
 }

if ($spotFail) exit('error 0101-1');

if ($postVals[2] == 0) {
  // this is the default offer;
  echo 'default offer';
  $offerDat = [0, 100, 0, 299, 50, 50, 50, 0, 0, 0];

  // check that the player has enough money
  $moneyCheck = true;
  $totalCost = $offerDat[1]*$offerDat[2];
  if ($totalCost <= $thisPlayer->get('money')) $moneyCheck = false;

  if ($moneyCheck) exit('not enough money for this deal');

  // deduct the money from this player
  $thisPlayer->set('money', $thisPlayer->get('money')-$totalCost);

  // record in this players pending order slot

	$thisFactory->save($thisFactory->orderListStart+$orderNumber, $postVals[2]);

} else {
  // load the specific offer
  //$offerList = new blockSlot($postVals[3], $offerDatFile, 4000);
  if (flock($offerDatFile, LOCK_EX)) {
    fseek($offerDatFile, $postVals[2]);
    $offerDat = unpack('i*', fread($offerDatFile, 64));
    print_r($offerDat);
    if ($offerDat[1] > 0 ) {
      // offer still available
    } else {
      exit('offer no longer available');
    }

    // check that the player has enough money
    $moneyCheck = true;
    $totalCost = $offerDat[1]*$offerDat[2]; // Quantity * Unit Price
    if ($totalCost <= $thisPlayer->get('money')) $moneyCheck = false;

    if ($moneyCheck) exit('not enough money for this deal.  Have'.$thisPlayer->get('money').' -> Need '.$totalCost);	

    // deduct the money from this player
    $thisPlayer->set('money', $thisPlayer->get('money')-$totalCost);

	$targetFactory = loadObject($offerDat[3], $objFile, 1600);
	$targetCity = loadCity($targetFactory->get('region_3'), $cityFile);

	$targetPlayer = loadObject($targetFactory->get('owner'), $objFile, 400);

	// calculate taxes on the selling player
	$inputCost = 0;
	$taxInfo = [0, $targetFactory->get('owner'), $targetFactory->get('subType'), $targetFactory->get('industry'), $offerDat[3], $targetPlayer->get('teamID'), ]; // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]

	$cityTaxEx = new itemSlot($targetCity->get('cTax'), $slotFile, 40);
	$regionTaxEx = new itemSlot($targetCity->get('rTax'), $slotFile, 40);
	$nationTaxEx = new itemSlot($targetCity->get('nTax'), $slotFile, 40);


  //overRideDurs
  $cityTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10,1,1,460,-10];
  $regionTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];
  $nationTaxEx->slotData = [0,1,2,3,4,5,6,7,8,9,10];

  for ($i=1; $i<11; $i++) {
    $taxes[$i] = $cityTaxEx->slotData[$i];
    $taxes[$i+10] = $regionTaxEx->slotData[$i];
    $taxes[$i+20] = $nationTaxEx->slotData[$i];
  }


	echo 'Tax infor for player ('.$pGameID.')';
	print_r($taxInfo);

	$cityLaws = new itemSlot($targetCity->get('cLaw'), $slotFile, 40);
	$regionLaws = new itemSlot($targetCity->get('rLaw'), $slotFile, 40);
	$nationLaws = new itemSlot($targetCity->get('nLaw'), $slotFile, 40);

	calcTaxes($cityTaxEx->slotData, $taxInfo);
	calcTaxes($regionTaxEx->slotData, $taxInfo);
	calcTaxes($nationTaxEx->slotData, $taxInfo);

	echo 'Final tax rates:';
	print_r($taxes);

	$taxAmounts = array_fill(1, 30, 0);
	/*
	$taxAmounts[1] = $taxes[1]* ($totalCost-$inputCost - $laborCost); // Income Tax
	$taxAmounts[3] = $taxes[3] * ($totalCost - $inputCost); // VAT
	$taxAmounts[5] = $taxes[5]*$offerDat[5]; // Pollution Tax
	$taxAmounts[6] = $taxes[6]*$offerDat[6]; // Rights Tax
	$taxAmounts[7] = $taxes[7] * $totalCost; // Sales Tax

	$taxAmounts[11] = $taxes[11]* ($totalCost-$inputCost - $laborCost); // Income Tax
	$taxAmounts[13] = $taxes[13] * ($totalCost - $inputCost); // VAT
	$taxAmounts[15] = $taxes[15]*$offerDat[5]; // Pollution Tax
	$taxAmounts[16] = $taxes[16]*$offerDat[6]; // Rights Tax
	$taxAmounts[17] = $taxes[17] * $totalCost; // Sales Tax

	$taxAmounts[21] = $taxes[21]* ($totalCost-$inputCost - $laborCost); // Income Tax
	$taxAmounts[23] = $taxes[23] * ($totalCost - $inputCost); // VAT
	$taxAmounts[25] = $taxes[25]*$offerDat[5]; // Pollution Tax
	$taxAmounts[26] = $taxes[26]*$offerDat[6]; // Rights Tax
	$taxAmounts[27] = $taxes[27] * $totalCost; // Sales Tax
	*/
	
	$totalTax = array_sum($taxAmounts);
	$sellerTax = $totalTax - $taxAmounts[7]-$taxAmounts[17]-$taxAmounts[27];
	$buyerTax = $taxAmounts[7]+$taxAmounts[17]+$taxAmounts[27];
	
	// Record the taxes paid
	for ($i=1; $i<7; $i++) {
		$targetFactory->objDat[$targetFactory->paidTaxOffset + $i] += $taxAmounts[$i];
		$targetFactory->objDat[$targetFactory->paidTaxOffset + $i+10] += $taxAmounts[$i+10];
		$targetFactory->objDat[$targetFactory->paidTaxOffset + $i+20] += $taxAmounts[$i+20];
	}
	
	// Add the tax to city/region/nation treasuries
	

    // add the money to the selling player
	$targetFactory->set('totalSales', $targetFactory->get('totalSales')+$totalCost-$sellerTax);
	$targetFactory->set('periodSales', $targetFactory->get('periodSales')+$totalCost-$sellerTax);

	$targetPlayer->set('money', $targetPlayer->get('money')+$totalCost-$sellerTax);

  // remove the order from the selling factory
  for ($i=1; $i<9; $i++) {
    if ($targetFactory->get('offer'.$i) == $postVals[2]) {
      $targetFactory->set('offer'.$i,0);
      break;
    }
  
  $targetFactory->saveAll($targetFactory->linkFile);

	if ($targetFactory->get('owner') == $pGameID) {
		$thisPlayer->set('money', $thisPlayer->get('money')+$totalCost);
	} else {
		$targetPlayer = loadObject($targetFactory->get('owner'), $objFile, 400);
		echo 'Target money: '.$targetPlayer->get('money').' + '.$totalCost;
		$targetPlayer->save('money', $targetPlayer->get('money')+$totalCost);
	}

    // record in this players pending order slot
	$thisFactory->save($thisFactory->orderListStart+$orderNumber, $postVals[2]);

	// Record the player ordering and the arrival time in the offer list file
	fseek($offerDatFile, $postVals[2]+44);
	fwrite($offerDatFile, pack('i*', $pGameID, $now+600, ));
	flock($offerDatFile, LOCK_UN);

    // overwrite the order in the slot List
    $offerList = new itemSlot($postVals[4], $offerListFile, 1000);
    $offerList->deleteByValue($postVals[2], $offerListFile);
  }
}

// Load updated material order information for this factory
$materialOrders = [];
for ($i=0; $i<10; $i++) {
	if ($thisFactory->objDat[$thisFactory->orderListStart+$i] > 0) {
		fseek($offerDatFile, $thisFactory->objDat[$thisFactory->orderListStart+$i]);
		$offerDat = unpack('i*', fread($offerDatFile, 64));
		array_push($materialOrders, $offerDat[13], $offerDat[1], $offerDat[11]); //time, id, qty
	}
}

echo '<script>
materialOrder = ['.implode(',', $materialOrders).'];
//businessDiv.orderItems.innerHTML = "";

/*
factoryOrders = new Array();
for (var i=0; i<materialOrder.length; i+=3) {
	factoryOrders.push(new factoryOrder('.$postVals[1].', materialOrder[i], materialOrder[i+1], materialOrder[i+2], i/3));
}
for (i=0; i<factoryOrders.length; i++) {
	factoryOrders[i].render(businessDiv.orderItems);
}
*/
factoryOrders['.($orderNumber-1).'].updateOrder('.$postVals[1].', materialOrder['.(($orderNumber-1)*3).'], materialOrder['.(($orderNumber-1)*3+1).'], materialOrder['.(($orderNumber-1)*3+2).'], '.($orderNumber-1).');
thisDiv = useDeskTop.getPane("businessObjects");
thisPlayer.money = '.$thisPlayer->get('money').'
</script>';
fclose($cityFile);
fclose($offerDatFile);
fclose($objFile);
fclose($slotFile);

function calcTaxes($slotData, $thisInfo) { // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
  global $taxes;

  print_r($slotData);
  $taxes[1] += $slotData[1];
	$taxes[2] += $slotData[2];
	$taxes[3] += $slotData[3];
	$taxes[4] += $slotData[4];
	$taxes[5] += $slotData[5];
	$taxes[6] += $slotData[6];
	$taxes[6] += $slotData[7];

	for ($i=11; $i<sizeof($slotData); $i+=4) {
    echo $thisInfo[$slotData[$i]].' vs '.$slotData[$i+2].' --> ';
		if ($thisInfo[$slotData[$i]] == $slotData[$i+2]) {
      echo 'adjust tax type '.$slotData[$i+1].' by '.$slotData[$i+3];
			$taxes[$slotData[$i+1]] += $slotData[$i+3];
		}
	}
}

?>
