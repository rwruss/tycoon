<?php

// show information about a player

// load player information
require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$trgBusiness = loadObject($postVals[1], $objFile, 400);

echo '<script>
useDeskTop.newPane("playerProfile");
playerDiv = useDeskTop.getPane("playerProfile");
playerDiv.innerHTML = "";';

if ($trgBusiness->get('owner') == $pGameID) {
	
} else {
	
}

fclose($objFile);

?>