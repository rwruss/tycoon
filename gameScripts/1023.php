<?php

// Show labot item detail at a factory
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the factory and get the labor details for the item in question
$thisFactory = loadObject($postVals[1], $objFile, 1000);
$laborDetails = array_slice($thisFactory->objDat, $thisFactory->laborOffset + ($postVals[2]-1)*10 - 1, 10); // correction to post vals since it starts at index 1

print_r($laborDetails);

// Out put promotion options and set pay...
echo '<script>
useDeskTop.newPane("laborItemPane");
thisDiv = useDeskTop.getPane("laborItemPane");


thisLaborItem = new laborItem({objID:'.$postVals[2].', pay:'.$laborDetails[5].', ability:'.$laborDetails[8].', laborType:'.$laborDetails[1].'});
thisLaborItem.renderSummary(thisDiv);

payArea = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", payArea, "Current pay for this employee");
newPay = payBox(payArea, 1000);
newPay.slider.slide.step = ".01";
setSlideVal(newPay, '.($laborDetails[5]/100).');
</script>';

fclose($cityFile);
fclose($slotFile);
fclose($objFile);

?>
