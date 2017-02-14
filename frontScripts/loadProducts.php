<?php

$dataBlockSize = 1000;

$scenario = 1;
$objFile = fopen('../scenarios/'.$scenario.'/objects.dat', 'w');
$nameFile = fopen('../scenarios/'.$scenario.'/objNames.dat', 'w');
$laborNameFile = fopen('../scenarios/'.$scenario.'/laborNames.dat', 'w');
$laborDetailFile = fopen('../scenarios/'.$scenario.'/laborDetails.dat', 'w');
$laborEqFile = fopen('../scenarios/'.$scenario.'/laborEq.dat', 'w');

fseek($laborEqFile, 1000*8000*4-4);
fwrite($laborEqFile, pack('i', 0));


// Load labor descriptions and equivalancies
$laborFile = fopen('../scenarios/'.$scenario.'/laborDesc.csv', 'rb');
$count = 0;
while (($line = fgets($laborFile)) !== false) {
	$lineItems = explode(',', $line);
  $laborItems[trim($lineItems[0])] = $count;
  $count++;
}
foreach ($laborItems as $key => $value) {
  echo 'Key '.$key.' has a length of '.strlen($key).' and a value of '.$value.'<br>';
}
print_R($laborItems);
echo '<p>';

// Record first line separately as it has no prereqs or eqs
fseek($laborFile, 0);
$line = fgets($laborFile);
$lineItems = explode(',', $line);
fwrite($laborNameFile, '"'.trim($lineItems[0]).'", ');
$laborCount = 1;
while (($line = fgets($laborFile)) !== false) {
	$lineItems = explode(',', $line);
	fwrite($laborNameFile, '"'.trim($lineItems[0]).'", ');

	// REcord promotion options for each labor items
	$promotionDat = '';
	for ($i=1; $i<11; $i++) {
		$promotionDat .= pack('i', $laborItems[trim($lineItems[$i])]);
	}
	fseek($laborDetailFile, $laborCount*1000+4);
	fwrite($laborDetailFile, $promotionDat);

	$eqArray = array_fill(1, 1000, 0);
	$laborItemNum = $laborItems[trim($lineItems[0])];
	$eqArray[$laborItemNum] = 10000;

	echo '<p>EQS for item #'.$laborCount.' - item #'.$laborItemNum.'<br>';
		if ($lineItems[11] != '') {
			for ($j=11; $j<sizeof($lineItems); $j+=2) {
				$eqItemNum = $laborItems[trim($lineItems[$j])];
 				echo 'eq for '.trim($lineItems[$j]).' (item #'.$laborItemNum.') is ('.$laborItems[trim($lineItems[$j])].')->> '.trim($lineItems[$j]).'<br>';

				$eqArray[$eqItemNum] = intval($lineItems[$j+1]*100);
		}
	}
	echo 'EQ Array Sum:'.array_sum($eqArray);
	fseek($laborEqFile, $laborCount*4000);
	fwrite($laborEqFile, packArray($eqArray));
	$laborCount++;
}

// Load each product description into the product array
$productFile = fopen('../scenarios/'.$scenario.'/products.csv', 'rb');
fgets($productFile);
$count = 0;
while (($line = fgets($productFile)) !== false) {
  $lineItems = explode(',', $line);
  fwrite($nameFile, '"'.$lineItems[0].'",');

  $productList[trim($lineItems[0])] = $count;

  $count++;
}
echo '<p>';
print_R($productList);
echo '<p>';

// load product requirements into an array indexed by product description
fseek($productFile, 0);
fgets($productFile);
$count = 0;
while (($line = fgets($productFile)) !== false) {
  $lineItems = explode(',', $line);
  $productReqs[trim($lineItems[0])][0] = $productList[$lineItems[1]];
  $productReqs[trim($lineItems[0])][1] = $productList[$lineItems[2]];
  $productReqs[trim($lineItems[0])][2] = $productList[$lineItems[3]];
  $productReqs[trim($lineItems[0])][3] = $productList[$lineItems[4]];
  $productReqs[trim($lineItems[0])][4] = $productList[$lineItems[5]];
  $productReqs[trim($lineItems[0])][5] = $productList[$lineItems[6]];
  $productReqs[trim($lineItems[0])][6] = $productList[$lineItems[7]];
  $productReqs[trim($lineItems[0])][7] = $productList[$lineItems[8]];
  $productReqs[trim($lineItems[0])][8] = $productList[$lineItems[9]];
  $productReqs[trim($lineItems[0])][9] = $productList[$lineItems[10]];

	echo trim($lineItems[0]).' Requires:<br>';
	for ($i=0; $i<10; $i++) {
		echo $productReqs[trim($lineItems[0])][$i].'<br>';
	}

  // read ingredients into array
  $productArray = array_fill(1, 250, 0);
  $productArray[4] = 4;
  $productArray[9] = $count;
  $productArray[11] = $lineItems[41];

  for ($i=0; $i<10; $i++) {
    $productArray[18+$i] = $productList[$lineItems[1+$i]];
    $productArray[28+$i] = $lineItems[11+$i];
    $productArray[38+$i] = $laborItems[$lineItems[21+$i]];
		$productArray[48+$i] = $lineItems[31+$i];
		//echo 'EQ #'.$i.':'.$lineItems[31+$i].'<p>';
  }
	//print_r($productArray);
	$packedProducts = packArray($productArray);
  fseek($objFile, $count*$dataBlockSize);
  fwrite($objFile, $packedProducts);
	echo 'Write '.(strlen($packedProducts)).' bytes at '.($count*$dataBlockSize).'<p>';
  $count++;
}

echo '<p>PRODUCT REQUIREMENTS<Br>';
print_R($productReqs);
echo '<p>';

fseek($productFile, 0);
fgets($productFile);
while (($line = fgets($productFile)) !== false) {
  //echo $line.'<br>';
  $lineItems = explode(',', $line);

  $lineArray = array_fill(0, 100, 0);
  // Read labor items
  for ($i=0; $i<10; $i++) {
    $lineArray[37+$i] = $laborItems[$lineItems[21+$i]];
  }
  //print_r($lineArray);
}

// Assign storage spots to factories
$factoryFile = fopen('../scenarios/'.$scenario.'/factoryDesc.csv', 'rb');
// $count = 1; Factories need to be added to the count total since they need unique IDs from the products
echo '<p>';
$factoryInventories = [];
fgets($factoryFile);
while (($line = fgets($factoryFile)) !== false) {

  $lineItems = explode(',', $line);
  fwrite($nameFile, '"'.$lineItems[0].'",');


  //$prodReq = array_fill(0, 10, 0);
  $factoryInventories[$lineItems[0]] = array_fill(0,20,0);
  $factoryObj = array_fill(1, 250, 0);
  // set object type and subtype
  $factoryObj[4] = 7;
  $factoryObj[8] = $lineItems[6];
  $factoryObj[9] = $count;

  for ($i=1; $i<=5; $i++) {
    $requiredProduct = trim($lineItems[$i]);
    $factoryObj[10+$i] = $productList[$requiredProduct];
    echo '#'.$count.' - '.$lineItems[0].' produces '.$lineItems[$i].' which requires '.$productReqs[$requiredProduct][0].'<br>';
		for ($reqNum=0; $reqNum<10; $reqNum++) {
			if ($productReqs[$requiredProduct][$reqNum] > 0) {

		    for ($invSpot = 0; $invSpot<20; $invSpot++) {
		      $inventoryCheck = true;
		      echo 'Check '.$factoryInventories[$lineItems[0]][$invSpot].' vs '.$productReqs[$requiredProduct][$reqNum].'<br>';
		      if (intval($factoryInventories[$lineItems[0]][$invSpot]) == intval($productReqs[$requiredProduct][$reqNum])) {
		        // already in inventory
		        echo '&nbsp&nbsp&nbsp&nbspAlready there<br>';
		        $inventoryCheck = false;
		        break;
		      }
		      elseif ($factoryInventories[$lineItems[0]][$invSpot] == 0) {
		        $factoryInventories[$lineItems[0]][$invSpot] = $productReqs[$requiredProduct][$reqNum];
		        $inventoryCheck = false;
		        echo '&nbsp&nbsp&nbsp&nbspAdded to spot<br>';
		        break;
		      }
		    }
			}
		}

  if ($inventoryCheck) echo 'CANT SETUP FACTORY TYPE '.$lineItems[0].' - TOO MUCH INVENTORY<Br>';
  }
  echo '<p>';
  print_r($factoryInventories[$lineItems[0]]);
  echo '<p>';


  // record production options
  for ($i=0; $i<5; $i++) {

  }

  // record inventories
  for ($i=0; $i<20; $i++) {
    $factoryObj[16+$i] = $factoryInventories[$lineItems[0]][0+$i];
  }

	$writeArray = packArray($factoryObj);

  fseek($objFile, $count*$dataBlockSize);
  fwrite($objFile, $writeArray);

	echo 'Seek to '.($count*$dataBlockSize).' and write '.(strlen($writeArray)).', FInal pos: '.(ftell($objFile)).'<br>';
  $count++;
}

fclose($productFile);
fclose($laborFile);
fclose($factoryFile);
fclose($objFile);
fclose($nameFile);
fclose($laborNameFile);
fclose($laborDetailFile);
fclose($laborEqFile);

// create sales file
$salesFile = fopen('../scenarios/'.$scenario.'/saleOffers.slt', 'wb');
fseek($salesFile, 10000*1000-4); // allow for 10k products
fwrite($salesFile, pack('i', 0));
fclose($salesFile);


function packArray($data) {
  $str = pack('i', current($data));
  for ($i=1; $i<sizeof($data); $i++) {
    $str = $str.pack('i', next($data));
  }
  return $str;
}

?>
