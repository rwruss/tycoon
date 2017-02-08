<?php

require_once('./objectClass.php');

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
	$msgSubj = trim(substr($msgDat, 11, 20));
	$msgFromName = trim(substr($msgDat, 20));
}
print_r($msgHead);

/*
echo '<script>
useDeskTop.newPane("msgPane");
msgDiv = useDeskTop.getPane("msgPane");

msgDiv.msgItems = addDiv("", "", msgDiv);
msgSummary(msgDiv.msgItems, "Name", '.$msgHead[3].', '.$msgHead[2].', "'.$msgSubj.'", '.$msgHead[1].');
</script>';
*/

echo '<script>
useDeskTop.newPane("msgPane");
msgDiv = useDeskTop.getPane("msgPane");
msgDiv.innerHTML = "";

testMsg = new message({subject:"Test message"});
testMsg.renderSummary(msgDiv);

testMsg2 = new message({subject:"Test message 2"});
testMsg2.renderSummary(msgDiv);';

fclose($objFile);
fclose($messageFile);

?>
