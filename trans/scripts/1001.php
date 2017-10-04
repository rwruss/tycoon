<?php

$datFile = fopen('./transactions/2017.dat', 'rb');
$categories = file_get_contents('./transactions/categories.dat');
$catItems = explode('|', $categories);

fseek($datFile, 0, SEEK_END);
$fileSize = ftell($datFile);
fseek($datFile, 0);
$data = fread($datFile, $fileSize);
fclose($datFile);

$items = ceil(strlen($data)/50);
//echo 'Load '.$items.' items<p>';

echo sizeof($catItems).'|'.$categories.'|';

for ($i=1; $i<$items; $i++) {
  $head = unpack('i*', substr($data, $i*50, 16));
  $desc = substr($data, $i*50+16, 34);

  //echo $head[1].', '.$head[2].', '.$head[3].', '.$head[4].','.$desc.'<br>';
  echo $i.'|'.implode('|',$head).'|'.trim($desc).'|';
}

?>
