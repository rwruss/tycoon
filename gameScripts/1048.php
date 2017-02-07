<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load this business
$thisBusiness = loadObject($pGameID, $objFile, 400);

$messageFile = fopen($gamePath.'/messages.dat', 'ab');

$to = substr($_POST['val1'], 5, $postVals[1]);
$subject = substr($_POST['val1'], 6+$postVals[1], $postVals[2]);
$message = substr($_POST['val1'], 6+$postVals[1]+$postVals[2]);
$msgSize = strlen($message)+100;

if (flock($messageFile, LOCK_EX)) {
	fseek($messageFile, 0, SEEK_END);
	$msgStart = ftell($messageFile);
	
	$headDat = pack('i*', 1, now(), $pGameID, 0, $msgStart, $msgStart+, 0, 0, 0, 0);
	$subject = substr($subject, 0, 20);
	
	flock($messageFile, LOCK_UN);
}

// Link previous last message
fseek($mesageFile, $thisBusiness->get('msgEndSpot')+32);
fwrite($messageFile, pack('i*', $msgStart, $msgSize));

// Record location of this message as new last message
$thisBusiness->set('msgEndSpot', $msgStart);
$thisBusiness->set('msgEndSize', $msgSize);
$thisBusiness->saveAll($objFile);

fclose($messageFile);

?>