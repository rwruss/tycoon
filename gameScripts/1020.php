<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

$thisCity = loadCity($postVals[3], $cityFile);

// Update city labor to show latest items
$laborRates = array_fill(0, 1000, 1000);
$laborRates[1] = 3600;
$now = time();
$citySchools = new itemSlot($thisCity->get('schoolSlot'), $slotFile, 40);

//insert testVal into schoolSlot
$citySchools->slotData[1] = 1;
print_r($citySchools->slotData);


$cityLaborSlot = $thisCity->updateLabor($now, $citySchools, $laborRates, $slotFile);
print_r($cityLaborSlot->slotData);

$checkSlot = new blockSlot($thisCity->get('laborSlot'), $slotFile, 40);
echo 'Slot Check:';
print_R($checkSlot->slotData);

echo '<script>

laborList = [';

if (sizeof($cityLaborSlot->slotData) > 9) {
	echo 'new laborItem({objID:1, pay:'.$cityLaborSlot->slotData[6].', ability:'.$cityLaborSlot->slotData[8].', laborType:'.$cityLaborSlot->slotData[8+2].'})';
}
for ($i=11; $i<sizeof($cityLaborSlot->slotData); $i+=10) {
  if ($cityLaborSlot->slotData[$i] > 0)	echo ', new laborItem({objID:'.$i.', pay:'.$cityLaborSlot->slotData[$i+5].', ability:'.$cityLaborSlot->slotData[$i+6].', laborType:'.$cityLaborSlot->slotData[$i+1].'})';
}

echo '];

laborSelect = new uList(laborList);
laborSelect.addFilter("edClass", "Education");
laborBox1 = laborSelect.SLsingleButton(showLaborArea);
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
