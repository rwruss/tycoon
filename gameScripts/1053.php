<?php

// school construction menu
echo '
<script>
useDeskTop.newPane("schoolConst");
var schoolConst = useDeskTop.getPane("schoolConst");
schoolConst.innerHTML = "";

schoolItems = new uList(schoolList);
schoolItems.SLShowAll(schoolConst, function(x, y, z) {x.buildOpt(y, z, '.$postVals[1].')});
</script>';

?>
