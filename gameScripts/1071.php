<?php

/*
Output a list of contracts that the player currently has open
*/

require_once('./slotFunctions.php');
require_once('./objectClass.php');

$slotFile = fopen($gamePath.'/gameSlots.slt', 'rb');
$contractFile = fopen($gamePath.'/contracts.ctf', 'rb');
$objFile = fopen($gamePath.'/objects.dat', 'rb');

// Load the player data and the contract slot

$thisPlayer = loadObject($pGameID, $objFile, 400);
$contractList = new itemSlot($thisPlayer->get('contractList'), $slotFile, 40);

//echo 'Read slot '.$thisPlayer->get('contractList');
//print_r($contractList->slotData);

// load each contract
$buyInfo = [];
$sellInfo = [];
$buyStr = '';
for ($i=1; $i<sizeof($contractList->slotData); $i++) {
	if ($contractList->slotData[$i] > 0) {
		fseek($contractFile, $contractList->slotData[$i]);
		$contractDat = fread($contractFile, 100);
		$contractInfo = unpack('i*', $contractDat);
		if ($contractInfo[1] == $pGameID)  {
			$buyInfo[] = 0;
			$buyInfo = array_merge($buyInfo, $contractInfo);
			$buyInfo[] = $contractList->slotData[$i];

			$buyStr .= pack('i', 0);
			$buyStr .= $contractDat;
			$buyStr .= pack('i', $contractList->slotData[$i]);
		}	else {
			$sellInfo[] = 0;
			$sellInfo = array_merge($sellInfo, $contractInfo);
			$sellInfo[] = $contractList->slotData[$i];

			$buyStr .= pack('i', 0);
			$buyStr .= $contractDat;
			$buyStr .= pack('i', $contractList->slotData[$i]);
		}
	}
}

// output the info for each conctract
echo $buyStr;
/*
echo '<script>showContracts(['.implode(',',$buyInfo).'], thisDiv.buyContracts);
showContracts(['.implode(',',$sellInfo).'], thisDiv.sellContracts);</script>';*/

fclose($slotFile);
fclose($objFile);
fclose($contractFile);

?>
