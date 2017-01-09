<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

$thisPlayer = loadObject($pGameID, $objFile, 400);
fclose($objFile);

echo '<script>
useDeskTop.newPane("businessDetail");
thisDiv = useDeskTop.getPane("businessDetail");
thisDiv.innerHTML = "";
textBlob("", thisDiv, "Company information - total funds: '.($thisPlayer->get('money')/100).'");';

?>
