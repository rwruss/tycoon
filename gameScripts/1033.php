<?php

echo '<script>

boostDiv = useDeskTop.newPane("boostStore");
boostDiv.innerHTML = "";
optionList = ["1 Minute Speed Up", "10 Minute Speed Up", "30 Minute Speed Up", "60 Minute Speed Up"];
saleButtons = new Array();
for (var i=0; i<optionList.length; i++) {
	let thisItem = addDiv("", "", boostDiv);
	saleButtons[i] = incrBox(thisItem);
}

submitButton = newButton(boostDiv);
submitButton.innerHTML = "Purchase Items";
submitButton.addEventListener("click", function () {
	let retStr = "";
	for (var i=0; i<saleButtons.length; i++) {
		retStr += ","+saleButtons[i].setValue;
	}
	scrMod("1034"+retStr);
});
</script>';

?>
