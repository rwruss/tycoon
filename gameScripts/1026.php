<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
useDeskTop.newPane("hireLabor");
thisDiv = useDeskTop.getPane("hireLabor");
useDeskTop.paneToTop(thisDiv.parentNode.parentObj);
console.log(thisDiv.parentNode.parentObj);
thisDiv.innerHTML = "";
cityList = [new city([1, "Round Rock"])];

console.log(cityList);
for (var i=0; i<cityList.length; i++) {
  let thisCity = cityList[i].renderSummary(thisDiv);
  let cityNum = i;
  thisCity.addEventListener("click", function () {scrMod("1027,"+cityNum)});
}';

fclose($objFile);
fclose($slotFile);

?>
