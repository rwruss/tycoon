<?php

// Process creation of a school at a town

require_once('./objectClass.php');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// load city
$trgCity = loadCity($postVals[2], $cityFile);

// verify tat schoool can be build

// Check school requirements
$schoolPrice = 100;

$newMoney = $trgCity->get('money') - $schoolPrice;
if ($newMoney < 0) exit('not enough money.  Have '.$trgCity->get('money').', Need '.$schoolPrice);
echo $trgCity->get('money').' - '.$schoolPrice.' = '.$newMoney;

$newLvl = $trgCity->objDat[90+$postVals[1]]+1;
echo 'Upgrade school type '.$postVals[1].' to level '.$newLvl;

$trgCity->save('money', $newMoney);
$trgCity->saveItem(91+($postVals[1]-1)*2, $newLvl);
$trgCity->saveItem(91+($postVals[1]-1)*2+1, 0);

fclose($cityFile);
fclose($objFile);

?>
