<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$offerFile = fopen($gamePath.'/saleOffers.slt', 'rb');
$cityFile = fopen($gamePath.'/saleOffers.slt', 'rb');

// Load the city information
$thisCity = loadObject($postVals[2], $cityFile);

// Calculate the current demands for each product at the city and the corresponding price

// Output what the city will buy and at what price

fclose($objFile);
fclose($offerFile);
fclose($cityFile);

echo '<script>
textBlob("", productArea, "city information and options");
</script>';

?>