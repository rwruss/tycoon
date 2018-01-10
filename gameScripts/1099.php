<?php

require_once('./govt.php');

$govtFile = fopen($gamePath.'/govActions.dat', 'rb');

// Load government actions
fseek($govtFile, 0, SEEK_END);
$govtSize = ftell($govtFile);
fseek($govtFile, 0);
$govtInfo = fread($govtFile, $govtSize);
//$govtInfo = loadGovtInfo($govtFile, 0);
$splitStr = pack("N", 0);
$list = explode($splitStr, $govtInfo);

foreach($list as $value) {
  //echo '<hr>';
  $itemHead = unpack("Cid/Csw/iamt/iPID", $value);
  //print_r($itemHead);
  //echo substr($value, 10);
	echo $itemHead['id'].','.$itemHead['sw'].','.$itemHead['amt'].','.$itemHead['PID'].',"'.substr($value, 10).'"<|>';
}

fclose($govtFile);

?>
