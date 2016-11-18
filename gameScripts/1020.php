<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisCity = loadCity($postVals[1], $cityFile);

$now = time();
$thisCity->updateLabor($now);

echo '<script>

laborSelect = new uList(laborArray, {useItems:[1, 5, 7, 8, 9]});
laborSelect.addFilter("edClass", "Education");
laborBox1 = laborSelect.SLsingleButton(showLaborArea, {renderFunction: (function (x, y, z) {
  console.log("item #" + z);
  return x.renderQty(y, 100);})});
hireButton =  newButton(thisDiv, function () {
  let readCheck = SLreadSelection(laborBox1);
  //console.log("readcheck is " + readCheck);
  if (readCheck)  scrMod("1021,'.$postVals[1].',"+ readCheck);
  else console.log("no value");
})
hireButton.innerHTML = "Hire";
</script>';

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
