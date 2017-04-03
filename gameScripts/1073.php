<?php

/*
Output current bids that company has made
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$bidFile = fopen($gamePath.'/contractBids.cbf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');
$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');

// Load the player information
$biddingPlayer = loadObject($pGameID, $objFile, 400);

// Load the list of bid slots
if ($biddingPlayer->get('openBids') == 0) {
	exit('<script>showBids([], thisDiv)</script>');
}
$bidSlot = new itemSlot($biddingPlayer->get('openBids'), $slotFile, 40);

// Load the bids
$size = sizeof($bidSlot->slotData);
$bidList = [];
print_r($bidSlot->slotData);
for ($i=1; $i<$size; $i++) {
	if ($bidSlot->slotData[$i] > 0) {
		fseek($bidFile, $bidSlot->slotData[$i]);
		$bidInfo = unpack('i*', fread($bidFile, 80));
		$bidList = array_merge($bidList, $bidInfo);
	}
}

echo '<script>showBids(['.implode(',', $bidList).'], thisDiv)</script>';

fclose($bidFile);
fclose($objFile);
fclose($slotFile);

?>
