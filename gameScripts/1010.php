<?php

/*
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

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisPlayer = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 400);

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
  }

} else {
  // load the specific offer
  //$offerList = new blockSlot($postVals[3], $offerDatFile, 4000);
  if (flock($offerDatFile, LOCK_EX)) {
    fseek($offerDatFile, $postVals[2]);
    $offerDat = unpack('i*', fread($offerDatFile, 44));
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

    // add the money to the selling player
	$targetFactory = loadObject($offerDat[3], $objFile, 400);
	$targetFactory->set('totalSales', $targetFactory->get('totalSales')+$totalCost);
	$targetFactory->set('periodSales', $targetFactory->get('periodSales')+$totalCost);

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
    for ($i=1; $i<=10; $i++) {
      if ($thisFactory->get('orderItem'.$i) == 0) {
        $thisFactory->set('orderTime'.$i, time()+60);
        $thisFactory->set('orderItem'.$i, $postVals[4]);
        $thisFactory->set('orderQty'.$i, $offerDat[1]);
        $thisFactory->saveAll($objFile);
        $orderNumber = $i;
        break;
      }
    }

    // overwrite the order in the offer list dat
    fseek($offerDatFile, $postVals[2]);
    $offerDat = fwrite($offerDatFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
    flock($offerDatFile, LOCK_UN);

    // overwrite the order in the slot List
    $offerList = new itemSlot($postVals[4], $offerListFile, 1000);
    $offerList->deleteByValue($postVals[2], $offerListFile);
  }
}

echo '<script>
materialOrder = ['.implode(',', $thisFactory->materialOrders()).'];
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

fclose($offerDatFile);
fclose($objFile);

?>
