<?php

echo '<script>

optionList = ["1 Minute Speed Up", "10 Minute Speed Up", "30 Minute Speed Up", "60 Minute Speed Up"];
saleButtons = new Array();
for (var i=0; i<optionList.length; i++) {
	let thisItemNum = i;
	saleButtons[i] = newButton(boostDiv, function () {scrMod("1034,"+thisItemNum});
	saleButtons[i].innerHTML = optionList[i];
}
</script>

?>