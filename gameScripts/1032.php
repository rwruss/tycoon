<?php
echo '<script>

useDeskTop.newPane("goldStore");
goldDiv = useDeskTop.getPane("goldStore");
optionList = ["100", "1000", "10000"];
saleButtons = new Array();
for (var i=0; i<optionList.length; i++) {
	let thisOption = i;
	let thisItem = newButton(goldDiv, function () {scrMod("1039,"+thisOption)});
}
</script>';

?>