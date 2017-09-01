<?php

/*
pvs
1 - factory ID
2 - labor spot
*/

// adjust for new labor type?
// Show labot item detail at a factory
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the factory and get the labor details for the item in question
$thisFactory = loadObject($postVals[1], $objFile, 1400);
$laborDetails = array_slice($thisFactory->objDat, $thisFactory->laborOffset + ($postVals[2]-1)*10 - 1, 10); // correction to post vals since it starts at index 1

$promoDat = unpack('i*', file_get_contents('../scenarios/1/laborDetails.dat', NULL, NULL, $laborDetails[0]*1000, 44));
$promoOpts = [];

// Check if ability is high enough to allow promotion
if ($laborDetails[8]/518400 > $promoDat[1]) {
	$promoOpts = [$promoDat[2], $promoDat[3], $promoDat[4], $promoDat[5], $promoDat[6], $promoDat[7], $promoDat[8], $promoDat[9], $promoDat[10], $promoDat[11]];
}


// Out put promotion options and set pay...
echo '<script>
useDeskTop.newPane("laborItemPane");
thisDiv = useDeskTop.getPane("laborItemPane");
thisDiv.innerHTML = "";

thisDiv.laborDescArea = addDiv("", "stdFloatDiv", thisDiv);
thisLaborItem = new laborItem(['.$postVals[2].', '.implode(',', $thisFactory->laborItems[$postVals[2]]->laborDat).']);
factoryLaborDetail(thisLaborItem, '.$postVals[1].', thisDiv.laborDescArea);

thisDiv.payArea = addDiv("", "stdFloatDiv", thisDiv);
laborPaySettings(thisLaborItem, '.$postVals[1].', thisDiv.payArea);
thisDiv.promotionArea = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", thisDiv.promotionArea, "Promotion options");

saveSettings = newButton(thisDiv.promotionArea);
saveSettings.addEventListener("click", function() {
	console.log("1058,'.$postVals[1].','.$postVals[2].',"+thisLaborItem.objID+","+thisDiv.laborPay.slider.slide.value)
	//scrMod("1058,'.$postVals[1].','.$postVals[2].',"+thisLaborItem.objID+","+thisDiv.laborPay.slider.slide.value)
})
saveSettings.innerHTML = "Save Settings";
thisDiv.laborArea = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", thisDiv.laborArea, "Other Labor Options");
laborTabs = new tabMenu(["Company Labor", "Hire Labor"]);
laborTabs.renderTabs(thisDiv.laborArea);

tmpLabor = companyLabor;
companyLaborOptions(tmpLabor, '.$postVals[1].', laborTabs.renderKids[0]);
factoryHireMenu(laborTabs.renderKids[1], '.$postVals[1].');
laborTabs.renderKids[1].subTarget = addDiv("", "stdFloatDiv", laborTabs.renderKids[1]);
</script>';

fclose($cityFile);
fclose($objFile);

?>
