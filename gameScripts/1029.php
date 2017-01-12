<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load inventory of production boosts for this player

// Load factory Data
$thisFactory = loadObject($postVals[2], $objFile, 1000);

echo '<script>
useDeskTop.newPane("boostMenu");
boostDiv = useDeskTop.getPane("boostMenu");

boostButton1 = newButton(productInvSection, function () {scrMod("1030,1,'.$postVals[1].',1");thisDiv = useDeskTop.getPane("businessObjects");});
boostButton1.innerHTML = "Boost 1";

boostButton2 = newButton(productInvSection, function () {scrMod("1030,1,'.$postVals[1].',2");thisDiv = useDeskTop.getPane("businessObjects");});
boostButton2.innerHTML = "Boost 2";

boostButton3 = newButton(productInvSection, function () {scrMod("1030,1,'.$postVals[1].',3");thisDiv = useDeskTop.getPane("businessObjects");});
boostButton3.innerHTML = "Boost 3";
thisDiv = useDeskTop.getPane("businessObjects");';

fclose($objFile);
?>