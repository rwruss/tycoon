<?php

$dataBlockSize = 1000;

$scenario = 1;
$objFile = fopen('../scenarios/'.$scenario.'/objects.dat', 'r+b');
$nameFile = fopen('../scenarios/'.$scenario.'/objNames.dat', 'w');

// Load labor descriptions
$laborFile = fopen('../scenarios/'.$scenario.'/laborDesc.csv', 'rb');
$count = 0;
while (($line = fgets($laborFile)) !== false) {
  $laborItems[trim($line)] = $count;
  $count++;
}
foreach ($laborItems as $key => $value) {
  echo 'Key '.$key.' has a length of '.strlen($key).' and a value of '.$value.'<br>';
}
print_R($laborItems);

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

// load product requirements
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

  // read ingredients into array
  $productArray = array_fill(1, 250, 0);
  $productArray[4] = 4;
  $productArray[9] = $count;
  $productArray[11] = $lineItems[32];
  for ($i=0; $i<10; $i++) {

    $productArray[18+$i] = $productList[$lineItems[1+$i]];
    $productArray[28+$i] = $lineItems[11+$i];
    $productArray[38+$i] = $laborItems[$lineItems[21+$i]];
  }

  fseek($objFile, $count*$dataBlockSize);
  fwrite($objFile, packArray($productArray));
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

while (($line = fgets($factoryFile)) !== false) {

  $lineItems = explode(',', $line);
  fwrite($nameFile, '"'.$lineItems[0].'",');


  $prodReq = array_fill(0, 10, 0);
  $factoryInventories[$lineItems[0]] = array_fill(0,20,0);
  $factoryObj = array_fill(1, 250, 0);
  // set object type and subtype
  $factoryObj[4] = 7;
  $factoryObj[9] = $count;

  for ($i=1; $i<5; $i++) {
    $requiredProduct = trim($lineItems[$i]);
    $factoryObj[10+$i] = $productList[$requiredProduct];
    echo '#'.$count.' - '.$lineItems[0].' produces '.$lineItems[$i].' which requires '.$productReqs[$requiredProduct][0].'<br>';
    for ($prodReq = 0; $prodReq<20; $prodReq++) {
      $inventoryCheck = true;
      echo 'Check '.$factoryInventories[$lineItems[0]][$prodReq].' vs '.$productReqs[$requiredProduct][0].'<br>';
      if (intval($factoryInventories[$lineItems[0]][$prodReq]) == intval($productReqs[$requiredProduct][0])) {
        // already in inventory
        echo '&nbsp&nbsp&nbsp&nbspAlready there<br>';
        $inventoryCheck = false;
        break;
      }
      elseif ($factoryInventories[$lineItems[0]][$prodReq] == 0) {
        $factoryInventories[$lineItems[0]][$prodReq] = $productReqs[$requiredProduct][0];
        $inventoryCheck = false;
        echo '&nbsp&nbsp&nbsp&nbspAdded to spot<br>';
        break;
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


  fseek($objFile, $count*$dataBlockSize);
  fwrite($objFile, packArray($factoryObj));

  $count++;
}

fclose($productFile);
fclose($laborFile);
fclose($factoryFile);
fclose($objFile);
fclose($nameFile);

// create sales file
$salesFile = fopen('../scenarios/'.$scenario.'/saleOffers.slt', 'wb');
fseek($salesFile, sizeof($productReqs)*1001*4-4);
fwrite($salesFile, pack('i', 0));
fclose($salesFile);


function packArray($data) {
  $str = '';
  for ($i=1; $i<=sizeof($data); $i++) {
    $str = $str.pack('i', $data[$i]);
  }
  return $str;
}

?>
