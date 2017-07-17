<?php

// Open required files
$numProducts = 10000;

$supplyFile = fopen($gamePath.'/citySupply.csf', 'rb');
fseek($supplyFile, 0, SEEK_END);
$size = ftell($supplyFile);
// Load the city + product information
//$seekTo = $postVals[1]*$numProducts*4
echo 'Seek to '.(($postVals[1])*$numProducts*32 + $postVals[2]*32).'<Br>';
fseek($supplyFile, ($postVals[1])*$numProducts*32 + $postVals[2]*32);
$readDat = fread($supplyFile, 32);
echo 'Read '.strlen($readDat).' of '.$size;
$prodInfo = unpack('i3/n*', $readDat);

fclose($supplyFile);

// override
echo '0,0,0,0,314,1,2,3,4,5,6,7,8,9,0';

?>
