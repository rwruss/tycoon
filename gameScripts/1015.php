<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$offerFile = fopen($gamePath.'/saleOffers.slt', 'r+b');

fclose($objFile);
fclose($offerFile);

// Load list of cities that can be sold to

echo '<script>
useDeskTop.newPane("citySale");
thisDiv = useDeskTop.getPane("citySale");
thisDiv.innerHTML = "";

var citySelect = new uList(cityList);
cityBox1 = citySelect.SLsingleButton(thisDiv);
cityButton = newButton(thisDiv, function () {scrMod("1016,'.$postVals[1].',"+ SLreadSelection(cityBox1))})
cityButton.innerHTML = "select this city";

var productArea = addDiv("", "standardContain", thisDiv);
</script>';

?>