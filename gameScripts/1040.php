<?php

// Search for services to buy
echo '<script>
useDeskTop.newPane("dialogPane");
var targetPane = useDeskTop.getPane("dialogPane");
targetPane.innerHTML = "";

targetPane.options = addDiv("", "stdFloatDiv", targetPane);
targetPane.results = addDiv("", "stdFloatDiv", targetPane);

textBlob("", targetPane, "Output Inventory");
for (var i=0; i<20; i++) {
  let thisService = serviceArray[i].renderSummary(targetPane.options);
  thisService.addEventListener("click", function () {scrMod()});
}
</script>';

?>
