<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
thisDiv = useDeskTop.newPane("hireLabor");
thisDiv.innerHTML = "";
for (var i=0; i<cityList.length; i++) {
  let thisCity = cityList[i].renderSummary(thisDiv);
  let cityNum = cityList[i].objID;
  thisCity.addEventListener("click", function () {scrMod("1027,"+cityNum)});
}';

fclose($objFile);
fclose($slotFile);

?>
