<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
useDeskTop.newPane("hireLabor");
thisDiv = useDeskTop.getPane("hireLabor");
thisDiv.innerHTML = "";
cityList = [new city({objID:1, objName:"Round Rock"})];

var citySelect = new uList(cityList);
cityBox1 = citySelect.SLsingleButton(thisDiv);
cityButton = newButton(thisDiv, function () {scrMod("1020,'.$postVals[1].',"+ SLreadSelection(cityBox1))})
cityButton.innerHTML = "select this city";

var showLaborArea = addDiv("productArea", "standardContain", thisDiv);
textBlob("", showLaborArea, "Options in this city");';

fclose($objFile);
fclose($slotFile);

?>
