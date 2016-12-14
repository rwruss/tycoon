<?php

// Show labot item detail at a factory
require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
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
payButton = newButton(payArea, function() {scrMod("1024,'.$postVals[1].','.$postVals[2].',"+newPay.slider.slide.value)});

promotionArea = addDiv("", "stdFloatDiv", thisDiv);
textBlob("", promotionArea, "Promotion options");
proOpts = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
proSelect = new uList(laborArray, {items:[0,1,2,3,4,5]});
proSelectButton = proSelect.SLsingleButton(promotionArea, {setVal:0});
savePromote = newButton(promotionArea, function () {scrMod("1025,'.$postVals[1].','.$postVals[2].',"+SLreadSelection(proSelectButton))});
</script>';

fclose($cityFile);
fclose($objFile);

?>
