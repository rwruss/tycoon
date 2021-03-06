<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisFactory = loadObject($postVals[1], $objFile, 1000);


// Load company labor
echo 'Labor in slot '.$thisBusiness->get('laborSlot');
$laborSlot = new itemSlot($thisBusiness->get('laborSlot'), $slotFile, 40);
print_r($laborSlot->slotData);

echo '<script>

companyLabor = [';

$startSpot = 1;
for ($i=1; $i<sizeof($laborSlot->slotData); $i+=10) {
	if ($laborSlot->slotData[$i] > 0) {
		echo 'new laborItem({objID:'.($i+10).', pay:'.$laborSlot->slotData[$i+5].', ability:'.$laborSlot->slotData[$i+8].', laborType:'.$laborSlot->slotData[$i].'})';
		$startSpot = $i+10;
		break;
	}
}
for ($i=$startSpot; $i<sizeof($laborSlot->slotData); $i+=10) {
	if ($laborSlot->slotData[$i] > 0) {
		echo ', new laborItem({objID:'.($i+10).', pay:'.$laborSlot->slotData[$i+5].', ability:'.$laborSlot->slotData[$i+8].', laborType:'.$laborSlot->slotData[$i].'})';
	}
}


//echo 'new laborItem({objID:11, pay:1000, ability:50, laborType:4}), new laborItem({objID:12, pay:1000, ability:50, laborType:6}),new laborItem({objID:13, pay:1000, ability:50, laborType:5})];
echo '];
factoryLabor = [new laborItem({objID:0, pay:0, ability:0, laborType:0})';
$startVals = [];
for ($i=0; $i<10; $i++) {
	if ($thisFactory->objDat[$thisFactory->laborOffset + $i*10] == 0) {
		$useID = 0;
		$startVals[$i] = 0;
	} else {
		$startVals[$i] = $i+1;
		$useID = $i+1;
	}
	echo ', new laborItem({objID:"'.$useID.'", pay:'.$thisFactory->objDat[$thisFactory->laborOffset+5 + $i*10].', ability:'.$thisFactory->objDat[$thisFactory->laborOffset+8 + $i*10].', laborType:'.$thisFactory->objDat[$thisFactory->laborOffset+1 + $i*10].'})';
}

echo '];

useDeskTop.newPane("laborPane");
thisDiv = useDeskTop.getPane("laborPane");

var factoryLaborSection = addDiv("", "stdFloatDiv", thisDiv);
textBlob("factoryLabor", factoryLaborSection, "Current labor at this facility");
var factoryLaborItems = addDiv("factoryLabor", "stdFloatDiv", factoryLaborSection);

var laborOptionList = new uList(factoryLabor.concat(companyLabor));
console.log(laborOptionList);
laborSpots = Array();
laborOptionList.setEmpty(0);
laborSpots[0] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[0].'});
laborSpots[1] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[1].'});
laborSpots[2] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[2].'});
laborSpots[3] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[3].'});
laborSpots[4] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[4].'});
laborSpots[5] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[5].'});
laborSpots[6] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[6].'});
laborSpots[7] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[7].'});
laborSpots[8] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[8].'});
laborSpots[9] = laborOptionList.SLsingleButton(factoryLaborSection, {setVal:'.$startVals[9].'});

saveLabor = newButton(thisDiv, function () {
	let results = "";
	for (let i=0; i<10; i++) {
		results += "," + SLreadSelection(laborSpots[i]);
	}
	scrMod("1022,'.$postVals[1].'" + results);
});
saveLabor.innerHTML = "Save Labor";

var addLabor = addDiv("companyLabor", "stdFloatDiv", thisDiv);
textBlob("", addLabor, "Company Labor Pool");
var companyLaborItems = addDiv("", "stdFloatDiv", addLabor);

laborAdj = newButton(addLabor, function () {scrMod("1019,'.$postVals[1].'")});
laborAdj.innerHTML = "Hire labor";</script>';

fclose($objFile);
fclose($slotFile);
fclose($cityFile);


?>
