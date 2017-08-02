<?php

// show a list of vehicles available for sale

/*
PVS
NONE!
*/

require_once('./slotFunctions.php');

$vehicleFile = fopen('vehicles.vif', 'rb');

// load the slot for vehicles available for sale
$saleList = new itemSlot(1, $vehicleFile, 40);

for ($i=1; $i<sizeof($saleList->slotData); $i++) {
	fseek($vehicleFile, 100*$saleList->slotData[$i]);
	$vinfo = unpack('i*', fread($vehicleFile, 100));
}

?>