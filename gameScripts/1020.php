<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[1], $cityFile);

// Update city labor to show latest items
$now = time();
$citySchools = new itemSlot($thisCity->get('schoolSlot'), $slotFile, 40);
$cityLaborSlot = $thisCity->updateLabor($now, $citySchools, $laborRates, $slotFile);


echo '<script>

laborList = ['.;

if (sizeof($cityLaborSlot->slotData) > 9) {
	echo 'new laborItem(1, 2, 3, 4, 5, 6, 7, 8, 9)';
}
for ($i=11; $i<cityLaborSlot->slotData; $i++) {
	echo ', new laborItem(1, 2, 3, 4, 5, 6, 7, 8, 9)';
}

echo '];

laborSelect = new uList(laborList);
laborSelect.addFilter("edClass", "Education");
laborBox1 = laborSelect.SLsingleButton(showLaborArea, {renderFunction: (function (x, y, z) {
  console.log("item #" + z);
  return x.renderQty(y, 100);})});
hireButton =  newButton(thisDiv, function () {
  let readCheck = SLreadSelection(laborBox1);
  //console.log("readcheck is " + readCheck);
  if (readCheck)  scrMod("1021,'.$postVals[1].','.$postVals[3].',"+ readCheck);
  else console.log("no value");
})
hireButton.innerHTML = "Hire";
</script>';

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
