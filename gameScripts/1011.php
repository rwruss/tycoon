<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
for (let i=0; i<playerFactories.length; i++) {
	if (playerFactories[i].objID = '.$postVals[1].') {
		var defaultItem = factoryArray[playerFactories[i].subType];
		
		for (let j=1; j<6; j++) {
			priceLists[j] = addDiv("", "stdContain", thisDiv);
			
		}
		sendPrices = newButton(thisDiv, function () {
			for (let k=1; k<6; k++) {
				
			}
			scrMod("1012,'.$postVals[1]'"
			});
		/*
			rsc1box = addDiv("", "stdContain", thisDiv);
			productArray[defaultItem.rsc1].renderSummary(rsc1box);
			slideValBar(rsc1box, 1, 0, 10000); // function (trg, slideID, low, hi
			
			rsc2box = addDiv("", "stdContain", thisDiv);
			productArray[defaultItem.rsc2].renderSummary(rsc2box);
			slideValBar(rsc2box, 2, 0, 10000); // function (trg, slideID, low, hi
			
			rsc3box = addDiv("", "stdContain", thisDiv);
			productArray[defaultItem.rsc3].renderSummary(rsc3box);
			slideValBar(rsc3box, 3, 0, 10000); // function (trg, slideID, low, hi
			
			rsc4box = addDiv("", "stdContain", thisDiv);
			productArray[defaultItem.rsc4].renderSummary(rsc4box);
			slideValBar(rsc4box, 4, 0, 10000); // function (trg, slideID, low, hi
			
			rsc5box = addDiv("", "stdContain", thisDiv);
			productArray[defaultItem.rsc5].renderSummary(rsc1box);
			slideValBar(rsc1box, 5, 0, 10000); // function (trg, slideID, low, hi
			*/

	}
}
</script>';

fclose($slotFile);
fclose($objFile);

?>