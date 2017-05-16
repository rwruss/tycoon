<?php

// Open required files
$supplyFile = fopen($gamePath.'/citySupply.csf'; 'rb')

// Load the city + product information
fseek($supplyFile, $postVals[1]*1000000 + $postVals[2]*100);
$prodInfo = unpack('i4/n*', fread($supplyFile, 100));

fclose($supplyFile);

echo '[3.14, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0];';

?>
