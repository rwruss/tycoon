<?php

// show a list of vehicles available for sale

/*
PVS
NONE!
*/

require_once('./slotFunctions.php');

$vehicleFile = fopen($gamePath.'/vehicles.vif', 'rb');

// load the slot for vehicles available for sale
/*
$saleList = new itemSlot(1, $vehicleFile, 40);

for ($i=1; $i<sizeof($saleList->slotData); $i++) {
	fseek($vehicleFile, 100*$saleList->slotData[$i]);
	$vinfo = unpack('i*', fread($vehicleFile, 100));
}*/

$vinfo = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25];

echo implode(',', $vinfo);

?>
