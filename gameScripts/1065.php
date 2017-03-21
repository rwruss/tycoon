<?php

/*
Remove a player from a contract agreement
PV1 - contract ID

*/

// verify that the player is part of the contract agreement

$contractFile = fopen($gamePath.'/contracts.ctf', 'r+b');
fseek($contractFile, $postVals[1]);
$contractDat = unpack('i*', fread($contractFile, 100));

if ($contractDat[1] == $pGameID) {
	// contract canceled by buyer
	
	// set contract status to 2 (cancelled by buyer)
	$newStatus = 2;
}
else if ($contractDat[15] == $pGameID) {
	// contract canceled by seller
	
	// set contract status to 3 (cancelled by seller)
	$newStatus = 1;
}
else exit ('error 5601-1');

fseek($contractFile, $postVals[1]+28);
fwrite($contractFile, pack('i', $newStatus);
fclose($contractFile);
?>