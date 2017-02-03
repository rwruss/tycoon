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

$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisPlayer = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 400);

if ($postVals[2] == 0) {
  // this is the default offer;
  echo 'default offer';
  $offerDat = [0, 100, 0, 299, 50, 50, 50, 0, 0, 0, 0];

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
      $orderNumber = $i-1;
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
  //$offerList = new blockSlot($postVals[3], $offerFile, 4000);
  if (flock($offerFile, LOCK_EX)) {
    fseek($offerFile, $postVals[2]);
    $offerDat = unpack('i*', fread($offerFile, 44));
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
        $thisFactory->set('orderTime'.$i, time()+3600);
        $thisFactory->set('orderItem'.$i, $postVals[4]);
        $thisFactory->set('orderQty'.$i, 100);
        $thisFactory->saveAll($objFile);
        $orderNumber = $i;
        break;
      }
    }

    // overwrite the order in the offer list slotFunctions
    fseek($offerFile, $postVals[2]);
    $offerDat = unpack('i*', fwrite($offerFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)));
    flock($offerFile, LOCK_UN);
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
factoryOrders['.$orderNumber.'].updateOrder('.$postVals[1].', materialOrder['.($orderNumber*3).'], materialOrder['.($orderNumber*3+1).'], materialOrder['.($orderNumber*3+2).'], '.$orderNumber.');
thisDiv = useDeskTop.getPane("businessObjects");
</script>';

fclose($offerFile);
fclose($objFile);

?>
