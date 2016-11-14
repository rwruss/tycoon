<?php

$cityFile = fopen('cities.dat', 'wb');

$cityArray = array_fill(1, 1000, 1000);
$cityArray[5] = 5;
$cityArray[11] = 10;
$cityArray[12] = 1000000;
$cityArray[13] = 0;
$cityArray[14] = 0;


$cityData = packArray($cityArray);
$dataCheck = unpack('i*', $cityData);
print_R($dataCheck);

for ($i=0; $i<1000; $i++) {
  fseek($cityFile, $i*4000);
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
