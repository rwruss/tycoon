<?php

echo '<script>

useDeskTop.newPane("boostStore");
boostDiv = useDeskTop.getPane("boostStore");
optionList = ["1 Minute Speed Up", "10 Minute Speed Up", "30 Minute Speed Up", "60 Minute Speed Up"];
saleButtons = new Array();
for (var i=0; i<optionList.length; i++) {
	let thisItemNum = i+1;
	saleButtons[i] = newButton(boostDiv, function () {scrMod("1034,"+thisItemNum)});
	saleButtons[i].innerHTML = optionList[i];
}
</script>';

?>
