<?php

$maxLen = 0;
$transFile = fopen('./transactions/2016.csv', 'r');
$datFile = fopen('./transactions/2016.dat', 'wb');
$count = 1;
$line = fgets($transFile); // Read the header line

while (($line = fgets($transFile)) !== false) {
  $lineItems = explode(',', $line);

  if (strlen($lineItems[5]) > 0) $val = intval($lineItems[5]*100);
  else   $val = intval($lineItems[6]*100);
  $cardNum = $lineItems[2];
  $intTime = strtotime($lineItems[0]);
  $packDat = pack('i*', $intTime, $val, $cardNum, 0); // date, amount, card number, category
  echo $line.' ---> '.$intTime.'<br>';

  $maxLen = max($maxLen, strlen($lineItems[3]));
  fseek($datFile, $count*50);
  fwrite($datFile, $packDat.$lineItems[3]);
  $count++;
}

echo '<p>MAX DESC: '.$maxLen;
fclose($datFile);
fclose($transFile);

?>
