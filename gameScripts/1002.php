<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
useDeskTop.newPane("businessObjects");
thisDiv = useDeskTop.getPane("businessObjects");';
/*
if ($thisBusiness->get('ownedObjects') > 0) {
	$ownedObjects = new itemSlot($thisBusiness->get('ownedObjects'), $slotFile, 40);
	//print_r($ownedObjects->slotData);
	for ($i=1; $i<sizeof($ownedObjects->slotData); $i++) {
		if ($ownedObjects->slotData[$i] > 0) {
			//echo 'Object '.$ownedObjects->slotData[$i].'<br>';
			$thisObject = loadObject($ownedObjects->slotData[$i], $objFile, 400);
		}
	}

} else {
	echo '
	textBlob("", thisDiv, "You do not own anything yet - start buying!");
	optionBox1 = defaultBuildings.SLsingleButton(thisDiv);

	sendButton = newButton(thisDiv, function () {
		console.log(SLreadSelection(optionBox1));
		let setVal=SLreadSelection(optionBox1);
		let checkSelection = setVal.split(',');
		if (checkSelection[checkSelection.length-1] != "0")	scrMod("1008,"+SLreadSelection(optionBox1));
	});
	sendButton.innerHTML = "Build This";
	';
}*/
echo 'console.log(playerFactories);
for (let i=0; i<playerFactories.length; i++) {
	let thisSummary = playerFactories[i].renderSummary(thisDiv);
	let thisFactory = playerFactories[i];
	thisSummary.addEventListener("click", function () {scrMod("1003,"+thisFactory.objID);
		console.log(thisFactory)})
}

optionBox1 = defaultBuildings.SLsingleButton(thisDiv);

sendButton = newButton(thisDiv, function () {
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
