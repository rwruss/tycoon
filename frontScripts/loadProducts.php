<?php

require_once('../public_html/objectClass.php');

$dataBlockSize = 1000;

$scenario = 1;
$objFile = fopen('../scenarios/'.$scenario.'/objects.dat', 'w');
$nameFile = fopen('../scenarios/'.$scenario.'/objNames.dat', 'w');
$laborNameFile = fopen('../scenarios/'.$scenario.'/laborNames.dat', 'w');
$laborDetailFile = fopen('../scenarios/'.$scenario.'/laborDetails.dat', 'w');
//$laborEqFile = fopen('../scenarios/'.$scenario.'/laborEq.dat', 'w');

//fseek($laborEqFile, 1000*8000*4-4);
//fwrite($laborEqFile, pack('i', 0));

// load skills
$skillFile = fopen('../scenarios/'.$scenario.'/skillList.csv', 'rb');
$skillCount = 1;
$skillList = [];
while(($line = fgets($skillFile)) !== false) {
	$lineItems = explode(',', $line);
	$skillList[trim($lineItems[0])] = $skillCount;
	$skillCount++;
}
fclose($skillFile);

$now = time();
// Load labor descriptions and equivalancies
$laborDescFile = fopen('../scenarios/'.$scenario.'/laborSkillSets.csv', 'rb');
$count = 0;
while (($line = fgets($laborDescFile)) !== false) {
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
fseek($laborDescFile, 0);
$line = fgets($laborDescFile);
$lineItems = explode(',', $line);
fwrite($laborNameFile, '"'.trim($lineItems[0]).'", ');
$laborCount = 1;
$schoolLists = [];
$schoolLists[0] = [];
$schoolLists[1] = [];
$schoolLists[2] = [];
$schoolLists[3] = [];
$schoolLists[4] = [];
$schoolLists[5] = [];
$schoolLists[6] = [];
$schoolLists[7] = [];
$schoolLists[8] = [];
$schoolLists[9] = [];
$schoolLists[10] = [];


while (($line = fgets($laborDescFile)) !== false) {
	$lineItems = explode(',', $line);
	print_r($lineItems);
	fwrite($laborNameFile, '"'.trim($lineItems[0]).'", ');

	// Add item to relevant school lists
	for ($i=0; $i<10; $i++) {
		if ($lineItems[$i+51] > 0) {
			$schoolLists[$i][] = $laborCount;
			//$schoolLists[$i][] = $lineItems[$i+51];
		}
	}
	$laborCount++;
}


// Create slots in the labor pool for each type of labor
$laborSlotFile = fopen('../scenarios/'.$scenario.'/laborLists.slt', 'r+b');
fseek($laborSlotFile, $laborCount*40-4);
fwrite($laborSlotFile, pack('i', 0));
fclose($laborSlotFile);

// Create template labor items for each type
$laborPoolFile = fopen('../scenarios/'.$scenario.'/laborPool.dat', 'w');
fseek($laborPoolFile, 0);
for ($i=0; $i<$laborCount; $i++) {
	$newLabor = loadLaborItem(0, NULL);

	// create labor templates
	$newLabor->laborDat[0] = 0; // current city
	$newLabor->laborDat[1] = 0; // current pay
	$newLabor->laborDat[2] = $i; // labor type
	$newLabor->laborDat[3] = 0; // creation time
	$newLabor->laborDat[4] = 0; // Home City
	$newLabor->laborDat[5] = 0; // talent
	$newLabor->laborDat[6] = 0; // motivation
	$newLabor->laborDat[7] = 0; // intelligence
	
	// skill then level
	for ($j=0; $j<10; $j++) {
		$newLabor->laborDat[8+$j*2] = $skillList[trim($lineItems[1+$j])];
		$newLabor->laborDat[9+$j*2] = $skillList[trim($lineItems[11+$j])];
	}
	
	fwrite($laborPoolFile, $newLabor->packLabor());
	// Record labor maximum points
	$tmpA = array_fill(0, 10, 0);
	$tmpA[0] = $skillList[trim($lineItems[21])];
	$tmpA[1] = $skillList[trim($lineItems[22])];
	$tmpA[2] = $skillList[trim($lineItems[23])];
	$tmpA[3] = $skillList[trim($lineItems[24])];
	$tmpA[4] = $skillList[trim($lineItems[25])];
	$tmpA[5] = $skillList[trim($lineItems[26])];
	$tmpA[6] = $skillList[trim($lineItems[27])];
	$tmpA[7] = $skillList[trim($lineItems[28])];
	$tmpA[8] = $skillList[trim($lineItems[29])];
	$tmpA[9] = $skillList[trim($lineItems[30])];
	
	fwrite($laborPoolFile, packArray($tmpA));
}
fclose($laborPoolFile);
/*
$laborHead = pack('i*', 0, 0);
$laborFoot = pack('i*', 0, 0, 0, 0, 0, 0, 0, 0, 0);
for ($i=0; $i<$laborCount; $i++) {
	$laborBod = pack('S*', $i, 0);
	echo '<br>'.$i.' -> '.fwrite($laborPoolFile, $laborHead.$laborBod.$laborFoot);
}
*/
echo '<p>Recorded '.$laborCount.' labor templates<p>';

// record schools file
$schoolFile = fopen('../scenarios/'.$scenario.'/schools.dat', 'wb');
fseek($schoolFile, 88);
for ($i=1; $i<11; $i++) {
	fwrite($schoolFile, packArray($schoolLists[$i]));
}
fseek($schoolFile, 8);
$schoolStart = 88;
for ($i=1; $i<11; $i++) {
	$spotSize = sizeof($schoolLists[$i])*4;
	echo 'School '.$i.' size is '.$spotSize;

	fwrite($schoolFile, pack('i*', $schoolStart, $spotSize));
	$schoolStart += $spotSize;
}

fclose($schoolFile);



print_r($skillList);

// Load each product description into the product array
$productFile = fopen('../scenarios/'.$scenario.'/products_new.csv', 'rb');
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
$productTypes = [];
$productTypes['Item'] = 1;
$productTypes['Transport'] = 2;
$productTypes['Service'] = 3;
while (($line = fgets($productFile)) !== false) {

  $lineItems = explode(',', $line);
	print_R($lineItems);
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
		echo $productReqs[trim($lineItems[0])][$i].' - '.($lineItems[$i+1]).'<br>';
	}

  // read ingredients into array
	echo 'Product type '.$lineItems[63].'<br>';
  $productArray = array_fill(1, 250, 0);
  $productArray[4] = 4; // type
  $productArray[9] = $count; // ??
  $productArray[11] = $lineItems[41]; // base prod rate/hour
	$productArray[12] = $productTypes[trim($lineItems[63])]; // product type
	$productArray[13] = $lineItems[43];  // unit weight
	$productArray[14] = $lineItems[44]; // unit volume

  for ($i=0; $i<10; $i++) {
    $productArray[18+$i] = $productList[$lineItems[1+$i]]; // material requirements
    $productArray[28+$i] = $lineItems[11+$i]; // material quantities


  }
	for ($i=0; $i<20; $i++) {
		if ($lineItems[21+$i] != 'x') {
			$productArray[38+$i] = $skillList[$lineItems[21+$i]]; // skill requirements
			$productArray[58+$i] = $lineItems[41+$i]; // skill weights

			echo 'Skill: '.$lineItems[21+$i].' - # '.$skillList[$lineItems[21+$i]].'<br>';
		} else {
			echo $lineItems[21+$i].'<br>';
		}
	}
	//print_r($productArray);
	$packedProducts = packArray($productArray);
  fseek($objFile, $count*$dataBlockSize);
  fwrite($objFile, $packedProducts);
	echo 'Write '.(strlen($packedProducts)).' bytes at '.($count*$dataBlockSize).'<p>';
  $count++;
}

$numProducts = $count;

echo '<p>PRODUCT REQUIREMENTS<Br>';
print_R($productReqs);
echo '<p>';

fseek($productFile, 0);
fgets($productFile);
/*
while (($line = fgets($productFile)) !== false) {
  //echo $line.'<br>';
  $lineItems = explode(',', $line);

  $lineArray = array_fill(0, 100, 0);
  // Read labor items
  for ($i=0; $i<10; $i++) {
    $lineArray[37+$i] = $laborItems[$lineItems[21+$i]];
  }
  //print_r($lineArray);
}*/

// Assign storage spots to factories
$factoryFile = fopen('../scenarios/'.$scenario.'/factoryDesc.csv', 'rb');
// $count = 1; Factories need to be added to the count total since they need unique IDs from the products
echo '<p>';
$factoryInventories = [];
$factoryBitScreens = [];
fgets($factoryFile);
while (($line = fgets($factoryFile)) !== false) {

  $lineItems = explode(',', $line);
  fwrite($nameFile, '"'.$lineItems[0].'",');


  //$prodReq = array_fill(0, 10, 0);
  $factoryInventories[$lineItems[0]] = array_fill(0,20,0);
  //$factoryBitScreens[$lineItems[0]] = [0,0,0,0,0];
  $factoryObj = array_fill(1, 250, 0);
  // set object type and subtype
  $factoryObj[4] = 7;
  $factoryObj[8] = $lineItems[6];
  $factoryObj[9] = $count;

  // Load what products the factory can produce and assign inventory slots to the required materials for that product
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
					//$factoryBitScreens[$lineItems[0]][$i] = $factoryBitScreens[$lineItems[0]][$i] | (1 << $invSpot);
					break;
				  }
				  elseif ($factoryInventories[$lineItems[0]][$invSpot] == 0) {
					$factoryInventories[$lineItems[0]][$invSpot] = $productReqs[$requiredProduct][$reqNum];
					$inventoryCheck = false;
					echo '&nbsp&nbsp&nbsp&nbspAdded to spot<br>';
					//$factoryBitScreens[$lineItems[0]] = $factoryBitScreens[$lineItems[0]][$i] | (1 << $invSpot);
					break;
				  }
				}
			} else $inventoryCheck = false;
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
fclose($laborDescFile);
fclose($factoryFile);
fclose($objFile);
fclose($nameFile);
fclose($laborNameFile);
fclose($laborDetailFile);
//fclose($laborEqFile);

// create sales file
$salesFile = fopen('../scenarios/'.$scenario.'/saleOffers.slt', 'wb');
fseek($salesFile, 10000*1000-4); // allow for 10k products
fwrite($salesFile, pack('i', 0));
fclose($salesFile);

// load contract list file and create slots based on number of products
$contractListFile = fopen('../scenarios/'.$scenario.'/contractList.clf', 'wb');
fseek($contractListFile, $numProducts*40-4);
fwrite($contractListFile, pack('i', 0));
fclose($contractListFile);


function packArray($data, $type = 'i') {
  $str = pack($type, current($data));
  for ($i=1; $i<sizeof($data); $i++) {
    $str = $str.pack($type, next($data));
  }
  return $str;
}

function newLaborItem() {
	
}

?>
