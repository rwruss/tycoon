<?php

// Search for services to buy
echo '<script>
useDeskTop.newPane("dialogPane");
var targetPane = useDeskTop.getPane("dialogPane");
targetPane.innerHTML = "";

textBlob("", targetPane, "Output Inventory");
for (var i=0; i<20; i++) {
  serviceArray[i].renderSummary(targetPane);
}
</script>';

?>
