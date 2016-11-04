<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

echo '<script>
for (let i=0; i<playerFactories.length; i++) {
	if (playerFactories[i].objID = '.$postVals[1].') {
		console.log(playerFactories[i]);
		var defaultItem = factoryArray[playerFactories[i].subType];

		var priceForms = factoryPricing(playerFactories[i], thisDiv);
		sendPrices = newButton(thisDiv, function () {
			var formVals = priceForms[0].value;

			for (let z=1; z<priceForms.length; z++) {
				formVals += ","+priceForms[z].value;
			}
			scrMod("1012,'.$postVals[1].',"+formVals);
		});
	}
}
</script>';

fclose($slotFile);
fclose($objFile);

?>
