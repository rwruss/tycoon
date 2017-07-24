<?php

/* Load city demand deciles and city pay informcation
PVS:
1 - city ID
2 - product ID
*/

require_once('./objectClass.php');

// Open required files
$numProducts = 10000;

// Load the demand information
$supplyFile = fopen($gamePath.'/citySupply.csf', 'rb');
fseek($supplyFile, 0, SEEK_END);
$size = ftell($supplyFile);
// Load the city + product information
//$seekTo = $postVals[1]*$numProducts*4
//echo 'Seek to '.(($postVals[1])*$numProducts*32 + $postVals[2]*32).'<Br>';
fseek($supplyFile, ($postVals[1])*$numProducts*32 + $postVals[2]*32);
$readDat = fread($supplyFile, 32);
//echo 'Read '.strlen($readDat).' of '.$size;
$prodInfo = unpack('i3/n*', $readDat);

fclose($supplyFile);

// Load the city pay information
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the city information
$thisCity = loadCity($postVals[1], $cityFile);
$nationalPay = [0, 1, 1.25, 1.75, 3, 8, 12, 27, 80, 523, 1024, 2768];
$cityPay = [0, 25, 25, 23, 10, 6, 3, 3, 2, 2, 1, 0];

$cityRegion = loadRegion($thisCity->get('parentRegion'), $cityFile);
$cityNation = loadRegion($thisCity->get('nation'), $cityFile);
//$nationalPay = $cityNation->regionPay();
fclose($cityFile);

// override
echo '0,314,1,2,3,4,5,6,7,8,9,0,'.implode(',', $cityPay).','.implode(',', $nationalPay).','.$thisCity->get('population');

?>
