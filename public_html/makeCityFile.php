<?php

$numCities = 10;
$numRegions = 3444;
$numNations = 196;

$baseSlots = ($numCities+$numRegions+$numNations);
$areaSize = $baseSlots*1000;

$cityFile = fopen('cities.dat', 'wb');

$countries = 206;
$regions = 3444;

$areaHeader = ($countries+$regions+1)*50*4;

$numProducts = 10000;
$generalItems = 250;

$blockSize = ($numProducts *2 + $generalItems) * 4;
$now = time();

/*
File Size:
250 general items
10000 product demand rates
10000 current demand rates

TOtal: 20250 x 4 = 81000
*/

$cityArray = array_fill(1, 250, 0);
$cityArray[5] = 5;
$cityArray[10] = $now;  // update time
$cityArray[11] = 10; // size tier
$cityArray[12] = 1000000; // population
$cityArray[13] = 0; // education levle
$cityArray[14] = 100; // affluence
$cityArray[15] = $now; // labor update time
$cityArray[16] = $now; // base labor time
$cityArray[17] = 0; // school slot
$cityArray[18] = 0; // school slot
$cityArray[26] = 1000000; //money

$cityArray[93] = 100; // school status
$cityArray[96] = 100; // school status
$cityArray[99] = 100; // school status
$cityArray[102] = 100; // school status
$cityArray[105] = 100; // school status
$cityArray[108] = 100; // school status
$cityArray[111] = 100; // school status
$cityArray[114] = 100; // school status
$cityArray[117] = 100; // school status
$cityArray[120] = 100; // school status

$cityData = packArray($cityArray);
$dataCheck = unpack('i*', $cityData);
$dataSize = strlen($cityData);
//print_R($dataCheck);
echo 'Data block size is '.$dataSize;

for ($i=0; $i<$baseSlots; $i++) {
  fseek($cityFile, $areaHeader+$i*$blockSize);
  fwrite($cityFile, $cityData);
}

$cityDemands = array_fill(1, 20000, 0);
$demandStr = packArray($cityDemands, 's');
for ($i=0; $i<$numCities; $i++) {
	fwrite($cityFile, $demandStr);
}

fclose($cityFile);

function packArray($data, $type='i') {
  $str = '';
  for ($i=1; $i<=sizeof($data); $i++) {
    $str = $str.pack($type, $data[$i]);
  }
  return $str;
}

?>
