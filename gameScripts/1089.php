<?php

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$transportFile = fopen($gamePath.'/transOpts.tof', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisPlayer = loadObject($pGameID, $objFile, 400);

// Load areas where this player has rights
$playerRoutes = [1,1,2,3,4,5,6,7,8,9,10,11,12,13,14,1,2,3,0,0,0,0,0,0,0,100,150,200,0,0,0,0,0,0,0];
if ($thisPlayer->get('transportOptions') > 0) {
  $routeList = new itemSlot($thisPlayer->get('transportOptions'), $transportFile, 40);
  for ($i=1; $i<=sizeof($routeList->slotData); $i++) {
    fseek($transportFile, $routeList->slotData[$i]);
    $tmpDat = fread($transportFile, 100);

    $routeHead = unpack('i*', substr($tmpDat, 0, 52));
    $routeDtls = unpack('s*', substr($tmpDat, 52));

    echo $routeList->slotData[$i].','.implode(',', $routeHead).','.implode(',', $routeDtls);
  }
}

fclose($transportFile);
fclose($objFile);

echo implode(',', $playerRoutes);

?>
