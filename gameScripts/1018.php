<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1000);

// Load labor stdFloatDiv
$laborSlot = new itemSlot($thisBusiness->get('laborSlot'), $slotFile, 40);
print_r($laborSlot->slotData);

echo '<script>

companyLabor = [';

$startSpot = 1;
for ($i=1; $i<sizeof($laborSlot->slotData); $i+=10) {
	if ($laborSlot->slotData[$i] > 0) {
		echo 'new laborItem({objID:'.$i.', pay:'.$laborSlot->slotData[$i+5].', ability:'.$laborSlot->slotData[$i+8].', laborType:'.$laborSlot->slotData[$i+1].'})';
		$startSpot = $i+1;
		break;
	}
}
for ($i=$startSpot; $i<sizeof($laborSlot->slotData); $i+=10) {
	if ($laborSlot->slotData[$i] > 0) {
		echo ', new laborItem({objID:'.$i.', pay:'.$laborSlot->slotData[$i+5].', ability:'.$laborSlot->slotData[$i+8].', laborType:'.$laborSlot->slotData[$i+1].'})';
	}
}


echo '];

factoryLabor = [';
echo 'new laborItem({objID:100, pay:'.$thisFactory->objDat[$thisFactory->laborOffset+5].', ability:'.$thisFactory->objDat[$thisFactory->laborOffset+8].', laborType:'.$thisFactory->objDat[$thisFactory->laborOffset+1].'})';
for ($i=1; $i<10; $i++) {
	echo ', new laborItem({objID:'.(100+$i).', pay:'.$thisFactory->objDat[$thisFactory->laborOffset+5 + $i].', ability:'.$thisFactory->objDat[$thisFactory->laborOffset+8 + $i].', laborType:'.$thisFactory->objDat[$thisFactory->laborOffset+1 + $i].'})';
}

echo '];

useDeskTop.newPane("laborPane");
thisDiv = useDeskTop.getPane("laborPane");

var factoryLaborSection = addDiv("", "stdFloatDiv", thisDiv);
textBlob("factoryLabor", factoryLaborSection, "Current labor at this facility");
var factoryLaborItems = addDiv("factoryLabor", "stdFloatDiv", factoryLaborSection);

var addLabor = addDiv("companyLabor", "stdFloatDiv", thisDiv);
textBlob("", addLabor, "Company Labor Pool");
var companyLaborItems = addDiv("", "stdFloatDiv", addLabor);

for (var i=0; i<factoryLabor.length; i++) {
	var thisLabor = factoryLabor[i].renderSummary(factoryLaborItems);
	thisLabor.addEventListener("click", function () {switchGroups(this, companyLaborItems, factoryLaborItems, "", "")});
}


for (var i=0; i<companyLabor.length; i++) {
	var thisLabor = companyLabor[i].renderSummary(companyLaborItems);
	thisLabor.addEventListener("click", function () {switchGroups(this, companyLaborItems, factoryLaborItems, "", "")});
}

laborSelect = new uList(laborArray, {useItems:[1, 5, 7, 9]});
laborBox1 = laborSelect.SLsingleButton(addLabor, {renderFunction: (function (x, y, z) {
console.log("item #" + z);
return x.renderQty(y, 100);})});

laborAdj = newButton(addLabor, function () {scrMod("1019,'.$postVals[1].'")});
laborAdj.innerHTML = "Hire labor";</script>';


fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
