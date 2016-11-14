<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

fclose($objFile);
fclose($offerFile);
fclose($cityFile);

?>