<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load target factory
$thisObj = loadObject($postVals[1], $objFile, 400);

// Confrim that player can give this order
if ($thisObj->get('owner') != $pGameID) {
	exit("error 8201-1");
}

$now = time();
// Confirm there is no task already started
if ($thisObj->get('prodLength') + $thisObj->get('prodStart') > $now) {
	exit("error 8201-2");
}

// Calculate the amount of product produced
$durations = [0, 3600, 7200, 14400, 28800];
$production = $this->get('currentRate')*$durations[$postVals[2]]/360000; // 3600 seconds x 100 for decimal factor in production rate

// Start the work
$thisObj->set('prodLength', $durations[$postVals[2]);
$thisObj->set('prodStart', $now);
$thisObj->set('prodQty', 100);

fclose($objFile);

?>