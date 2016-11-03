<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

echo '<script>
for (let i=0; i<playerFactories.length; i++) {
	if (playerFactories[i].objID = '.$postVals[1].') {
		console.log(playerFactories[i]);
		var defaultItem = factoryArray[playerFactories[i].subType];

		factoryPricing(playerFactories[i], thisDiv);
		sendPrices = newButton(thisDiv, "");
	}
}
</script>';

fclose($slotFile);
fclose($objFile);

?>
