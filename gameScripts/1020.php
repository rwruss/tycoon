<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'r+b');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

echo 'Load city '.$postVals[3];
$thisCity = loadCity($postVals[3], $cityFile);

// Update city labor to show latest items
$laborRates = array_fill(0, 1000, 999);
$laborRates[1] = 3600;
$now = time();
$citySchools = new itemSlot($thisCity->get('schoolSlot'), $slotFile, 40);

//insert testVal into schoolSlot
$citySchools->slotData[1] = 1;
//print_r($citySchools->slotData);

// Read city schools and output possible items

echo '<script>
console.log("show school options");
showSchools(showLaborArea, '.$postVals[3].', 0, ['.implode(',', array_slice($thisCity->objDat, 80, 30)).']);;
</script>';
/*
$thisCity->updateLabor($now, $citySchools, $laborRates);
//print_r($thisCity->objDat);




echo '<script>

laborList = [';
$startSpot = 0;
for ($i=0; $i<100; $i++) {
	$offset = $thisCity->laborStoreOffset+$i*10+1;
	if ($thisCity->objDat[$offset+1] > 0) {
		echo 'new laborItem({objID:'.$i.', pay:'.$thisCity->objDat[$offset+5].', ability:'.$thisCity->objDat[$offset+8].', laborType:'.$thisCity->objDat[$offset+1].'})';
		$startSpot = $i+1;
		break;
	}
}
for ($i=$startSpot; $i<100; $i++) {
	$offset = $thisCity->laborStoreOffset+$i*10+1;
	if ($thisCity->objDat[$offset] > 0) {
		echo ', new laborItem({objID:'.$i.', pay:'.$thisCity->objDat[$offset+5].', ability:'.$thisCity->objDat[$offset+8].', laborType:'.$thisCity->objDat[$offset+1].'})';
	}
}
echo '];


showLaborArea.innerHTML = "";
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
*/
fclose($objFile);
fclose($slotFile);
fclose($cityFile);

?>
