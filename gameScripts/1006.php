<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisObj = loadObject($postVals[1], $objFile, 400);

// show product options for this type of facility

echo '<script>
useDeskTop.newPane("factoryOrders");
thisDiv = useDeskTop.getPane("factoryOrders");

inputList = new uList([0,'.implode(array_slice($thisObj->templateDat, 20, 20).']);

inputBox1 = prodList.SLsingleButton(thisDiv, {setVal:0});

sendButton = newButton(thisDiv, function () {scrMod("1007,'.$postVals[1].'," + SLreadSelection(inputBox1))});
sendButton.innerHTML = "Update production";

</script>';

fclose($objFile);

?>