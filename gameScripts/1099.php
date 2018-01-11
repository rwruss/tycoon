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

// send the first case
$itemHead = unpack("Ctype/Cid/Csw/iamt/iPID", $list[0]);
echo $itemHead['id'].','.$itemHead['sw'].','.$itemHead['amt'].','.$itemHead['PID'].',"'.substr($list[0], 10).'"';

// send the remaining cases
for ($i=1; $i<sizeof($list); $i++) {
  //echo '<hr>';
  $itemHead = unpack("Ctype/Cid/Csw/iamt/iPID", $list[$i]);
  //print_r($itemHead);
  //echo substr($value, 10);
  echo ','.$itemHead['id'].','.$itemHead['sw'].','.$itemHead['amt'].','.$itemHead['PID'].',"'.substr($list[$i], 10).'"';
}



fclose($govtFile);

?>
