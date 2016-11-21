<?php

$cityFile = fopen('cities.dat', 'wb');

$numProducts = 10000;
$numLabor = 1000;
$extra = 250;

$now = time();

$cityArray = array_fill(1, $extra+($numProducts+$numLabor)*2, 1000);
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


$cityData = packArray($cityArray);
$dataCheck = unpack('i*', $cityData);
$dataSize = strlen($cityData);
//print_R($dataCheck);
echo 'Data block size is '.$dataSize;

for ($i=0; $i<500; $i++) {
  fseek($cityFile, $i*$dataSize);
  fwrite($cityFile, $cityData);
}

fclose($cityFile);

function packArray($data) {
  $str = '';
  for ($i=1; $i<=sizeof($data); $i++) {
    $str = $str.pack('i', $data[$i]);
  }
  return $str;
}

?>
