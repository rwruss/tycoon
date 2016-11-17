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

laborSelect = new uList(laborArray, {useItems:[1, 5, 7, 9]});
laborBox1 = laborSelect.SLsingleButton(showLaborArea, {renderFunction: (function (x, y, z) {
  console.log("item #" + z);
  return x.renderQty(y, 100);})});
</script>';

fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
