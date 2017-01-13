<?php

echo '<script>
console.log("boostmenu")
useDeskTop.newPane("boostMenu");
boostDiv = useDeskTop.getPane("boostMenu");
boostDiv.innerHTML = "";
//useDeskTop.paneToTop(boostDiv);

boostButton1 = newButton(boostDiv, function () {scrMod("1030,1,'.$postVals[2].',0");thisDiv = useDeskTop.getPane("businessObjects");thisDiv = useDeskTop.getPane("businessObjects");event.stopPropagation();});
boostButton1.innerHTML = "1 minute";

boostButton2 = newButton(boostDiv, function () {scrMod("1030,1,'.$postVals[2].',1");thisDiv = useDeskTop.getPane("businessObjects");thisDiv = useDeskTop.getPane("businessObjects");event.stopPropagation();});
boostButton2.innerHTML = "10 minutes";

boostButton3 = newButton(boostDiv, function () {scrMod("1030,1,'.$postVals[2].',2");thisDiv = useDeskTop.getPane("businessObjects");thisDiv = useDeskTop.getPane("businessObjects");event.stopPropagation();});
boostButton3.innerHTML = "30 minutes";

boostButton3 = newButton(boostDiv, function () {scrMod("1030,1,'.$postVals[2].',3");thisDiv = useDeskTop.getPane("businessObjects");thisDiv = useDeskTop.getPane("businessObjects");event.stopPropagation();});
boostButton3.innerHTML = "1 hour";

</script>';

?>
