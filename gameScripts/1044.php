<?php

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$messageFile = fopen($gamePath.'/messages.dat', 'rb');

// Read message list for player
$thisBusiness = loadObject($pGameID, $objFile, 400);


$msgStart = $thisBusiness->get('msgStartSpot');
$msgSize = $thisBusiness->get('msgStartSize');
for ($i=0; $i<20; $i++) {
	fseek($messageFile, $msgStart);
	$msgDat = fread($messageFile, 80);
	$msgHead = unpack('i*', substr($msgDat, 0, 40));
}


echo '<script>
useDeskTop.newPane("msgPane");
thisDiv = useDeskTop.getPane("msgPane");
</script>';

fclose($objFile);
fclose($messageFile);

?>