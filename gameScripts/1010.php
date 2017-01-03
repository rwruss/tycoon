<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$offerFile = fopen($gamePath.'/saleOffers.slt', 'r+b');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisPlayer = loadObject($pGameID, $objFile, 400);
$thisObj = loadObject($postVals[1], $objFile, 400);

if ($postVals[5] == 0) {
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
    if ($thisObj->get('orderItem'.$i) == 0) {
      $thisObj->set('orderTime'.$i, time()+60);
      $thisObj->set('orderItem'.$i, $postVals[3]);
      $thisObj->set('orderQty'.$i, 100);
      $thisObj->saveAll($objFile);
      print_r($thisObj->objDat);

      break;
    }
  }

} else {
  // load the specific offer
  //$offerList = new blockSlot($postVals[3], $offerFile, 4000);
  if (flock($offerFile, LOCK_EX)) {
    fseek($offerFile, $postVals[5]);
    $offerDat = unpack('i*', fread($offerFile, 44));

    if ($offerDat[1] > 0 ) {
      // offer still available
    } else {
      exit('offer no longer available');
    }


    // check that the player has enough money
    $moneyCheck = true;
    $totalCost = $offerDat[1]*$offerDat[2];
    if ($totalCost <= $thisPlayer->get('money')) $moneyCheck = false;

    if ($moneyCheck) exit('not enough money for this deal');

    // deduct the money from this player
    $targetPlayer = loadObject($offerDat[3], $objFile, 400);
    $thisPlayer->set('money', $thisPlayer->get('money')-$totalCost);

    // add the money to the selling player
    $targetPlayer->set('money', $targetPlayer->get('money')+$totalCost);

    // record in this players pending order slot
    for ($i=1; $i<=10; $i++) {
      if ($thisObj->get('orderItem'.$i) == 0) {
        $thisObj->set('orderTime'.$i, time()+3600);
        $thisObj->set('orderItem'.$i, $postVals[3]);
        $thisObj->set('orderQty'.$i, 100);
        $thisObj->saveAll($objFile);
        break;
      }
    }

    // overwrite the order in the offer list slotFunctions
    fseek($offerFile, $postVals[5]);
    $offerDat = unpack('i*', fwrite($offerFile, pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)));
    flock($offerFile, LOCK_UN);
  }
}

echo '<script>
materialOrder = ['.implode(',', $thisObj->materialOrders()).'];
console.log(materialOrder);
console.log('.$postVals[1].');
console.log(orderItems);
showOrders(materialOrder, '.$postVals[1].');
</script>';

fclose($offerFile);
fclose($objFile);

?>
