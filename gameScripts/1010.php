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
	
	// Load information about buying and selling players, factories, and cities
	$sellingFactory = loadObject($offerDat[3], $objFile, 1600);
	$sellingCity = loadCity($sellingFactory->get('region_3'), $cityFile);
	$buyingCity = loadCity($buyingFactory->get('region_3'));

	$sellingPlayer = loadObject($sellingFactory->get('owner'), $objFile, 400);
	
	// [0, company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID, city ID, region ID, nation ID]
	$taxInfo = [0, $sellingFactory->get('owner'), $sellingFactory->get('subType'), $sellingFactory->get('industry'), $offerDat[3], 
		$sellingPlayer->get('teamID'), $sellingFactory->get('region_3'), $sellingFactory->get('region_2'), $sellingFactory->get('region_1')]; 
	
	// Calculate import/tarrif taxes for the buyer
	$importTaxes = array_fill(0,31,0);
	$importTaxEx = new itemSlot($buyingCity->get('nTax'), $slotFile, 40);
	$importTaxes[29] = $importTaxEx[29];
	calcTaxes($importTaxEx->slotData, $taxInfo, $taxes);
	
	// calculate taxes on the selling player
	$materialCost = $offerDat[14];
	$laborCost = $offerDat[15];

	$cityTaxEx = new itemSlot($sellingCity->get('cTax'), $slotFile, 40);
	$regionTaxEx = new itemSlot($sellingCity->get('rTax'), $slotFile, 40);
	$nationTaxEx = new itemSlot($sellingCity->get('nTax'), $slotFile, 40);

	// override taxes for testing
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

	$cityLaws = new itemSlot($sellingCity->get('cLaw'), $slotFile, 40);
	$regionLaws = new itemSlot($sellingCity->get('rLaw'), $slotFile, 40);
	$nationLaws = new itemSlot($sellingCity->get('nLaw'), $slotFile, 40);

	calcTaxes($cityTaxEx->slotData, $taxInfo, $taxes);
	calcTaxes($regionTaxEx->slotData, $taxInfo, $taxes);
	calcTaxes($nationTaxEx->slotData, $taxInfo, $taxes);

	echo 'Final tax rates:';
	print_r($taxes);

	$taxAmounts = array_fill(1, 30, 0);
	$taxAmounts[1] = $taxes[1]* ($baseCost-$materialCost - $laborCost)/10000; // Income Tax
	$taxAmounts[3] = $taxes[3] * ($baseCost - $materialCost)/10000; // VAT
	$taxAmounts[5] = $taxes[5]*$offerDat[5]/10000; // Pollution Tax
	$taxAmounts[6] = $taxes[6]*$offerDat[6]/10000; // Rights Tax
	$taxAmounts[7] = $taxes[7] * $baseCost/10000; // Sales Tax

	$taxAmounts[11] = $taxes[11]* ($baseCost-$materialCost - $laborCost)/10000; // Income Tax
	$taxAmounts[13] = $taxes[13] * ($baseCost - $materialCost)/10000; // VAT
	$taxAmounts[15] = $taxes[15]*$offerDat[5]/10000; // Pollution Tax
	$taxAmounts[16] = $taxes[16]*$offerDat[6]/10000; // Rights Tax
	$taxAmounts[17] = $taxes[17] * $baseCost/10000; // Sales Tax

	$taxAmounts[21] = $taxes[21]* ($baseCost-$materialCost - $laborCost)/10000; // Income Tax
	$taxAmounts[23] = $taxes[23] * ($baseCost - $materialCost)/10000; // VAT
	$taxAmounts[25] = $taxes[25]*$offerDat[5]/10000; // Pollution Tax
	$taxAmounts[26] = $taxes[26]*$offerDat[6]/10000; // Rights Tax
	$taxAmounts[27] = $taxes[27] * $baseCost/10000; // Sales Tax
	
	$taxAmounts[29] = $importTaxes*$baseCost/10000;
	
	$totalTax = array_sum($taxAmounts);
	$sellerTax = $totalTax - $taxAmounts[7]-$taxAmounts[17]-$taxAmounts[27]-$taxAmoutns[29];
	$buyerTax = $taxAmounts[7]+$taxAmounts[17]+$taxAmounts[27]+$taxAmounts[29];
	
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
		$sllerTaxDat .= pack('s*', $sellingCity, $i, $taxAmounts[$i]);
		$sllerTaxDat .= pack('s*', $sellingRegion, $i, $taxAmounts[$i+10]);
		$sllerTaxDat .= pack('s*', $sellingNation, $i, $taxAmounts[$i+20]);
	}
	
	// Add the tax to the buying nation for any import Tax
	$buyerTaxDat = '';
	$buyingNation = $buyingFactory->get('region_1');
	$buyerTaxDat .= pack('s*', $buyingNation, 9, $taxes[29]);
	
	if flock($taxIncomeFile, LOCK_EX) {
		fwrite($taxIncomeFile, $sllerTaxDat);
		flock($taxIncomeFile, LOCK_UN);
	}	
	fclose($taxIncomeFile);
	
    // add the money to the selling player
	$sellingFactory->set('totalSales', $sellingFactory->get('totalSales')+$baseCost-$sellerTax);
	$sellingFactory->set('periodSales', $sellingFactory->get('periodSales')+$baseCost-$sellerTax);

	$sellingPlayer->set('money', $sellingPlayer->get('money')+$baseCost-$sellerTax);

  // remove the order from the selling factory
  for ($i=1; $i<9; $i++) {
    if ($sellingFactory->get('offer'.$i) == $postVals[2]) {
      $sellingFactory->set('offer'.$i,0);
      break;
    }
  
  $sellingFactory->saveAll($sellingFactory->linkFile);

	if ($sellingFactory->get('owner') == $pGameID) {
		$buyingPlayer->set('money', $buyingPlayer->get('money')+$baseCost);
	} else {
		$sellingPlayer = loadObject($sellingFactory->get('owner'), $objFile, 400);
		echo 'Target money: '.$sellingPlayer->get('money').' + '.$baseCost;
		$sellingPlayer->save('money', $sellingPlayer->get('money')+$baseCost);
	}

    // record in this players pending order slot
	$buyingFactory->save($buyingFactory->orderListStart+$orderNumber, $postVals[2]);

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
	if ($buyingFactory->objDat[$buyingFactory->orderListStart+$i] > 0) {
		fseek($offerDatFile, $buyingFactory->objDat[$buyingFactory->orderListStart+$i]);
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
buyingPlayer.money = '.$buyingPlayer->get('money').'
</script>';
fclose($cityFile);
fclose($offerDatFile);
fclose($objFile);
fclose($slotFile);

function calcTaxes($slotData, $thisInfo, &$taxList) { // [company ID, Factory Type, Industry, Factory ID, Cong ID, Product ID]
	for ($i=11; $i<sizeof($slotData); $i+=4) {
    echo $thisInfo[$slotData[$i]].' vs '.$slotData[$i+2].' --> ';
		if ($thisInfo[$slotData[$i]] == $slotData[$i+2]) {
      echo 'adjust tax type '.$slotData[$i+1].' by '.$slotData[$i+3];
			$taxList[$slotData[$i+1]] += $slotData[$i+3];
		}
	}
}

?>
