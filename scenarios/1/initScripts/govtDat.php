<?php
header('Content-Type: text/html; charset=utf-16', true);
$govtFile = fopen('./govActions.dat', 'r+b');

fseek($govtFile, 1000);
fwrite($govtFile, pack('i*', 0, 0));
/*
fwrite($govtFile, '{"test":test}');
*/

//print_r(unpack("n", $packTest));
fwrite($govtFile, pack("n", 0xFEFF));
$offset = 100000;
for ($i=0; $i<20000; $i++) {
  fwrite($govtFile, pack("n", $offset+$i));
}

fflush($govtFile);

fseek($govtFile, 0);
$str = fread($govtFile, 40000);

//$str = mb_convert_encoding($str, "UTF-16");
//echo FxEFF;

echo $str;
echo strlen($str);
fclose($govtFile);
?>
