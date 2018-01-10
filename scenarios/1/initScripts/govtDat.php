<?php
//header('Content-Type: text/html; charset=utf-16', true);
$govtFile = fopen('./govActions.dat', 'w+b');

//fseek($govtFile, 1000);
//$packDat = pack('C*', 3*16+12, 7*16+12, 7*16+12, 3*16+14);
//$packDat = pack('C*', 0,  13*16 + 14);

/*
$val = 1*16*16*16*16 + 15*16*16*16 + 6*16*16 + 2*16 + 13;
$val = 4036991149;
echo decbin($val);
echo '<p><hr><p>';
$packDat = pack("N", $val);
fwrite($govtFile, $packDat);

echo $packDat;
echo '<p><hr><p>';
print_r(unpack('C*', $packDat));
print_r(unpack('N', $packDat));
*/

$splitStr = pack("N", 0);
$headDat = pack("C*", 1, 1, 1);
$headDat.= pack("i*", 1000, 123456);

fwrite($govtFile, $headDat."Player Name");
fwrite($govtFile, $splitStr.$headDat."Player Name");
fwrite($govtFile, $splitStr.$headDat."Player Name");
fwrite($govtFile, $splitStr.$headDat."Player Name");

fflush($govtFile);
fseek($govtFile, 0, SEEK_END);
$size = ftell($govtFile);

fseek($govtFile, 0);
$dat = fread($govtFile, $size);

echo $dat;
$list = explode($splitStr, $dat);
print_r($list);

foreach($list as $value) {
  echo '<hr>';
  $itemHead = unpack("Ctype/Cid/Csw/iamt/iPID", $value);
  print_r($itemHead);
  echo substr($value, 10);
}

fclose($govtFile);

/*
a-10
b-11
c-12
d-13
e-14
f-15

*/
?>
