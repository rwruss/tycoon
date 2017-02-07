<?php
// 330-17
// Show full content of in game player message;

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$messageFile = fopen($gamePath.'/messages.dat', 'rb');

fseek($messageFile, $postVals[1]);
$msgDat = fread($messageFile, $postVals[2]);
$msgHead = unpack('i*', substr($msgDat, 0, 40));

// verify the security key for the message
if ($msgHead[7] - $pGameID != $postVals[3]) exit('Not authorized to view this message');

echo substr($msgDat, 100);

fclose($messageFile);

?>