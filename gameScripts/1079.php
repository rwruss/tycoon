<?php

// Open required files
$supplyFile = fopen($gamePath.'/citySupply.csf', 'rb');
fseek($supplyFile, 0, SEEK_END);
$size = ftell($supplyFile);
// Load the city + product information
fseek($supplyFile, ($postVals[1]-1)*1000000 + $postVals[2]*100);
$readDat = fread($supplyFile, 100);
//echo 'Read '.strlen($readDat).' of '.$size;
$prodInfo = unpack('i4/n*', $readDat);

fclose($supplyFile);

// override
echo '0,0,0,0,314,1,2,3,4,5,6,7,8,9,0';

?>
