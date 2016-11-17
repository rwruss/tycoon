<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

// Load labor stdFloatDiv
$laborSlot = new itemSlot($thisBusiness->get('laborSlot'), $slotFile, 40);
print_r($laborSlot->slotData);

echo '<script>
useDeskTop.newPane("laborPane");
thisDiv = useDeskTop.getPane("laborPane");

var addLabor = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", addLabor, "Labor Pool - show available labor");

useFunction = function (x, y, z) {
  console.log("item #" + z);
  return x.renderQty(y, 100);}
laborSelect = new uList(laborArray, {useItems:[1, 5, 7, 9]});
laborBox1 = laborSelect.SLsingleButton(addLabor, {renderFunction: (function (x, y, z) {
  console.log("item #" + z);
  return x.renderQty(y, 100);})});

laborAdj = newButton(addLabor, function () {scrMod("1019,'.$postVals[1].'")});
laborAdj.innerHTML = "Hire labor";</script>';


fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
