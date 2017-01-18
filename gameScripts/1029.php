<?php

echo '<script>
console.log("boostmenu")
useDeskTop.newPane("boostMenu");
boostDiv = useDeskTop.getPane("boostMenu");
boostDiv.innerHTML = "";
//useDeskTop.paneToTop(boostDiv);

var boostTargetHolder = addDiv("", "stdFloatDiv", boostDiv);
var boostListHolder = addDiv("", "stdFloatDiv", boostDiv);
var upgradeCopy = thisUpgrade.render(boostTargetHolder, false);


boostButton1 = newButton(boostListHolder, function () {scrMod("1030,1,'.$postVals[2].',0");});
boostButton1.innerHTML = "1 minute";

boostButton2 = newButton(boostListHolder, function () {scrMod("1030,1,'.$postVals[2].',1");});
boostButton2.innerHTML = "10 minutes";

boostButton3 = newButton(boostListHolder, function () {scrMod("1030,1,'.$postVals[2].',2");});
boostButton3.innerHTML = "30 minutes";

boostButton3 = newButton(boostListHolder, function () {scrMod("1030,1,'.$postVals[2].',3");});
boostButton3.innerHTML = "1 hour";

</script>';

?>
