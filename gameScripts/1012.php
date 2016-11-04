<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load the factory and record the new pricing
$thisFactory = loadObject($postVals[1], $objFile, 400);

// Convert all prices to cents
$priceVals = [];
for ($i=0; $i<5; $i++) {
	$priceVals[] = intval($postVals[2+$i]*100);
}

print_r($priceVals);

$thisFactory->set('price1', $priceVals[0]);
$thisFactory->set('price2', $priceVals[1]);
$thisFactory->set('price3', $priceVals[2]);
$thisFactory->set('price4', $priceVals[3]);
$thisFactory->set('price5', $priceVals[4]);
$thisFactory->saveAll($objFile);

echo '<script>updateFactory({objID:'.$postVals[1].', prices:['.implode(',', $priceVals).']});</script>';

fclose($slotFile);
fclose($objFile);

?>
