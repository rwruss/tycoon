<?php

$dataFile = fopen('./transactions/2016.dat', 'r+b');

fseek($dataFile, $postVals[1]*50);
$checkDat = fread($dataFile, 50);
$checkInfo = unpack('i*', substr($checkDat, 0, 16));
if ($postVals[3] != $checkInfo[2]) {
	echo '0|An error has occured';
} else {
	fseek($dataFile, $postVals[1]*50+12);
	fwrite($dataFile, pack('i', $postVals[2]);
	echo '1|$postVals[2]';
}

fclose($dataFile);
?>