<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
businessDiv = useDeskTop.newPane("businessObjects");
businessDiv.innerHTML = "";

var headSection = addDiv("", "stdFloatDiv", businessDiv);
var listSection = addDiv("", "stdFloatDiv", businessDiv);
businessDiv.laborHead = addDiv("abcd", "stdFloatDiv", businessDiv);
businessDiv.laborSection = addDiv("abcd", "stdFloatDiv", businessDiv);


console.log(playerFactories);
for (let i=0; i<playerFactories.length; i++) {
	let thisSummary = playerFactories[i].renderSummary(listSection);
	let thisFactory = playerFactories[i];
	thisSummary.addEventListener("click", function () {scrMod("1003,"+thisFactory.objID);})
}

//optionBox1 = defaultBuildings.SLsingleButton(headSection, {setVal:38});

sendButton = newButton(headSection);
sendButton.addEventListener("click", function () {
	event.stopPropagation();
	factoryBuildMenu();});
sendButton.innerHTML = "Build a new facility";
textBlob("", businessDiv.laborHead, "company labor");

tmpLabor = companyLabor;
companyLaborList(tmpLabor, businessDiv.laborSection);
</script>';
fclose($slotFile);
fclose($objFile);

?>
