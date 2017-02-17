<?php

// update per percent construction price for a school

require_once('./objectClass.php');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Verify that player is the leader of this city and can set the price
$trgCity = loadCity($postVals[2], $cityFile);

if ($pGameID != $trgCity->get('leader')) exit('You are not authorized to make this change.');

echo 'Set school tpye '.$postVals[2].' production cost to '.$postVals[3];
$trgCity->setVal(91+($postVals[2]-1)*3+2, $postVals[3]);

fclose($cityFile);
?>
