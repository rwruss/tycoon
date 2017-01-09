<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
useDeskTop.newPane("businessObjects");
thisDiv = useDeskTop.getPane("businessObjects");
thisDiv.innerHTML = "";

var headSection = addDiv("", "stdFloatDiv", thisDiv);
var listSection = addDiv("", "stdFloatDiv", thisDiv);

console.log(playerFactories);
for (let i=0; i<playerFactories.length; i++) {
	let thisSummary = playerFactories[i].renderSummary(listSection);
	let thisFactory = playerFactories[i];
	thisSummary.addEventListener("click", function () {scrMod("1003,"+thisFactory.objID);
		console.log(thisFactory)})
}

optionBox1 = defaultBuildings.SLsingleButton(headSection, {setVal:33});

sendButton = newButton(headSection, function () {
	console.log(SLreadSelection(optionBox1));
	let setVal=SLreadSelection(optionBox1);
	let checkSelection = setVal.split(',');
	if (checkSelection[checkSelection.length-1] != "0")	scrMod("1008,"+SLreadSelection(optionBox1));
});
sendButton.innerHTML = "Build This";
</script>';
fclose($slotFile);
fclose($objFile);

?>
