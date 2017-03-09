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

$offerListFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
$offerDatFile = fopen($gamePath.'/saleOffers.dat', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisPlayer = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 400);
$now = time();

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
	$orderItems = $thisObj->materialOrders();
    for ($i=0; $i<10; $i++) {
		if ($orderItems[$i] == 0) {
		$orderNumber = $i;
		break;
	}
	$thisFactory->save($thisFactory->orderListStart+$orderNumber, $postVals[2]);
	/*
  // record in this players pending order slot
  for ($i=1; $i<=10; $i++) {
    if ($thisFactory->get('orderItem'.$i) == 0) {
      $orderNumber = $i;
      $thisFactory->set('orderTime'.$i, time()+60);
      $thisFactory->set('orderItem'.$i, $postVals[4]);
      $thisFactory->set('orderQty'.$i, 100);
      $thisFactory->saveAll($objFile);
      //print_r($thisFactory->objDat);

      break;
    }
  }*/

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
    $totalCost = $offerDat[1]*$offerDat[2];
    if ($totalCost <= $thisPlayer->get('money')) $moneyCheck = false;

    if ($moneyCheck) exit('not enough money for this deal.  Have'.$thisPlayer->get('money').' -> Need '.$totalCost);

    // deduct the money from this player
    $thisPlayer->set('money', $thisPlayer->get('money')-$totalCost);
	
	$targetFactory = loadObject($offerDat[3], $objFile, 400);
	$targetCity = loadCity($targetFactory->get('region3'), $cityFile);
	
	$targetPlayer = loadObject($targetFactory->get('owner'), $objFile, 400);
	
	// calculate taxes on the selling player
	$taxes = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	$inputCost = 0;
	$taxInfo = [$targetFactory->get('owner'), $targetFactory->get('subType'), $targetFactory->get('industry'), $offerDat[3], $targetPlayer->get('teamID'), ]; // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
	
	$cityTaxEx = new itemSlot($targetCity->get('cTax'), $slotFile, 40);
	$regionTaxEx = new itemSlot($targetCity->get('rTax'), $slotFile, 40);
	$nationTaxEx = new itemSlot($targetCity->get('nTax'), $slotFile, 40);

	$cityLaws = new itemSlot($targetCity->get('cLaw'), $slotFile, 40);
	$regionLaws = new itemSlot($targetCity->get('rLaw'), $slotFile, 40);
	$nationLaws = new itemSlot($targetCity->get('nLaw'), $slotFile, 40);
	
	calcTaxes($cityTaxEx->slotData, $taxInfo);
	calcTaxes($regionTaxEx->slotData, $taxInfo);
	calcTaxes($nationTaxEx->slotData, $taxInfo);
	
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

    // add the money to the selling player
	$targetFactory->set('totalSales', $targetFactory->get('totalSales')+$totalCost);
	$targetFactory->set('periodSales', $targetFactory->get('periodSales')+$totalCost);
	
	
	$targetPlayer->set('money', $targetPlayer->get('money')+$totalCost);

  // remove the order from the selling factory
  for ($i=1; $i<9; $i++) {
    if ($targetFactory->get('offer'.$i) == $postVals[2]) {
      $targetFactory->set('offer'.$i,0);
      break;
    }
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
	$orderItems = $thisObj->materialOrders();
    for ($i=0; $i<10; $i++) {
		if ($orderItems[$i] == 0) {
		$orderNumber = $i;
		break;
	}
	$thisFactory->save($thisFactory->orderListStart+$orderNumber, $postVals[2]);
	/*
      if ($thisFactory->get('orderItem'.$i) == 0) {
        $thisFactory->set('orderTime'.$i, time()+60);
        $thisFactory->set('orderItem'.$i, $postVals[4]);
        $thisFactory->set('orderQty'.$i, $offerDat[1]);
        $thisFactory->saveAll($objFile);
        $orderNumber = $i;
        break;
      }*/
	  
    }

    // overwrite the order in the offer list dat
	/*
    fseek($offerDatFile, $postVals[2]);
    $offerDat = fwrite($offerDatFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
    flock($offerDatFile, LOCK_UN);
	*/
	
	// Record the player ordering and the arrival time in the offer list file
	fseek($offerDatFile, $postVals[2]+44);
	fwrite($offerDatFile, pack('i*', $pGameID, $now));
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

function calcTaxes($slotData, $thisInfo) { // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
	$taxes[1] += $slotData[1];
	$taxes[2] += $slotData[2];
	$taxes[3] += $slotData[3];
	$taxes[4] += $slotData[4];
	$taxes[5] += $slotData[5];
	$taxes[6] += $slotData[6];
	$taxes[6] += $slotData[7];
	
	for ($i=10; $i<sizeof($slotData); $i+=4) {
		if ($thisInfo[$slotData[$i]] == $slotData[$i+2]) {
			$taxes[$slotData[$i+1]] += $slotData[$i+3];
		}
	}
	/*
	for ($i=10; $i<sizeof($slotData); $i+=4) {
		switch($slotData[$i]) {
			case 1: // exemptions by company
				if ($slotData[$i+2] == $thisCompany) {
					$taxes[$slotData[$i+1]] += $slotData[$i+3];
				}
				break;
			
			case 2: // exemptions by factory type
				if ($slotData[$i+2] == $thisFactoryType) {
					$taxes[$slotData[$i+1]] += $slotData[$i+3];
				}
				break;
				
			case 3: // exemptions by industry
				if ($slotData[$i+2] == $thisIndustry) {
					$taxes[$slotData[$i+1]] += $slotData[$i+3];
				}
				break;
				
			case 4: // exemptions by factory ID
				if ($slotData[$i+2] == $thisFID) {
						$taxes[$slotData[$i+1]] += $slotData[$i+3];
					}
				break;
			
			case 5: // exemptions by conglomerate
				if ($slotData[$i+2] == $thisCongID) {
						$taxes[$slotData[$i+1]] += $slotData[$i+3];
					}
				break;
				
			case 6: // exemptions by product
			if ($slotData[$i+2] == $thisProduct) {
						$taxes[$slotData[$i+1]] += $slotData[$i+3];
					}
				break;
		}
	}*/
}

?>
