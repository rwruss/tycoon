<?php
// 330-17
// Show full content of in game player message;

require_once('./objectClass.php');
$msgDtls = explode("-", $postVals[1]);  // start, length, checkval

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$messageFile = fopen($gamePath.'/messages.dat', 'rb');

fseek($messageFile, $msgDtls[0]);
$msgDat = fread($messageFile, $msgDtls[1]);
$msgHead = unpack('i*', substr($msgDat, 0, 40));

// verify the security key for the message
if ($msgHead[7] - $pGameID != $msgDat[2]) echo "Not authorized to view this message";

echo substr($msgDat, 100);

fclose($messageFile);

?>