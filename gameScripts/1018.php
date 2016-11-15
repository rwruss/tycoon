<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

$thisBusiness = loadObject($pGameID, $objFile, 400);

// Load labor stdFloatDiv
$laborSlot = new itemSlot($thisBusiness->get('laborSlot'), $slotFile, 40);
print_r($laborSlot->slotData);

echo '<script>
useDeskTop.newPane("laborPane");
thisDiv = useDeskTop.getPane("laborPane");

var addLabor = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", addLabor, "Labor Pool - show available labor");

laborSelect = new uList(laborArray);
laborBox1 = laborSelect.SLsingleButton(addLabor);

laborAdj = newButton(addLabor, function () {scrMod("1019,'.$postVals[1].'")});
laborAdj.innerHTML = "Hire labor";';


fclose($objFile);
fclose($slotFile);

?>
