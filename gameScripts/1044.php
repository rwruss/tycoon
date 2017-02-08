<?php

/*
PV
1 -> first message location
*/

require_once('./objectClass.php');
require_once('./slotFunctions.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$messageFile = fopen($gamePath.'/messages.dat', 'rb');
$slotFile = fopen($gamePath.'/', 'rb');

// Read message list for player
$thisBusiness = loadObject($pGameID, $objFile, 400);
$msgSlot = new itemSlot($thisBusiness->get('msgSlot'), $slotFile, 40);

$numMsg = sizeof($msgSlot);
$showQty = 20;
if ($numMsg - $postVals[1] < 20) {
	$showQty = $numMsg - $postVals[1];
}

echo '<script>
useDeskTop.newPane("msgPane");
msgDiv = useDeskTop.getPane("msgPane");
msgDiv.innerHTML = "";
messages = new Array();';

$firstMsg = 2*$postVals[1];
$showQty *= 2;
// Message start, read
for ($i=0; $i<$showQty; $i+=2) {
	if ($msgSlot[$i] > 0) {
		fseek($messageFile, $msgSlot[$i+$firstMsg]);
		$msgDat = fread($messageFile, 100);
		$msgHead = unpack('i*', substr($msgDat, 0, 40));
		$msgSubj = trim(substr($msgDat, 11, 20));
		$msgFromName = trim(substr($msgDat, 20));
		
		echo 'messages.push(new message[{subject:"'.$msgSubj.'", fromName:"'.$msgFromName.'", fromID:"'.$msgHead[3].'", read:"'.$msgSlot[$i+1].'", id:"'.$msgSlot[$i].'-'.$msgHead[8].'-'.($msgHead[7]-$pGameID).'", time:'.$msgHead[2].'}])';
	}
}

echo 'for (var i=0; i<messages.length; i++) {
	messages[i].renderSummary(msgDiv);
}
</script>';

fclose($objFile);
fclose($messageFile);
fclose($slotFile);

?>
