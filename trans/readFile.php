<?php

$maxLen = 0;
$transFile = fopen('C:\Users\Rhodes Russell\Documents\Capital One\2016.csv', 'r');
$datFile = fopen('./transactions/2016.dat', 'wb');
$count = 1;
$line = fgets($transFile); // Read the header line

while (($line = fgets($transFile)) !== false) {
  $lineItems = explode(',', $line);

  // check for accidental splits due to commas in description
  $checkSize = sizeof($lineItems);
  if ($checkSize > 7) {
    // merge the offenders into one descripton
    echo '<p>REASSIGN VALUES';
    print_r($lineItems);
    for ($i=4; $i<$checkSize-3; $i++) {
      $lineItems[3] .= $lineItems[$i];
    }
    /*
    echo 'item 4 = item '.($checkSize-1).'<br>';
    echo 'item 5 = item '.($checkSize-1).'<br>';
    echo 'item 6 = item '.($checkSize-1).'<br>';*/
    $lineItems[4] = $lineItems[$checkSize-3];
    $lineItems[5] = $lineItems[$checkSize-2];
    $lineItems[6] = $lineItems[$checkSize-1];


    echo '<br>TO: ';
    print_r($lineItems);
  }
  if ($lineItems[3] == "CAPITAL ONE ONLINE PYMT") {
    echo 'SKIP A PAYMENT ITEM';
  } else {
    if (strlen($lineItems[5]) > 0) $val = intval($lineItems[5]*100);
    else   $val = -intval($lineItems[6]*100);
    $cardNum = $lineItems[2];
    $intTime = strtotime($lineItems[0]);
    $packDat = pack('i*', $intTime, $val, $cardNum, 0); // date, amount, card number, category
    echo $line.' ---> '.$intTime.'<br>';
    echo $intTime.', '.$val.', '.$cardNum. ', 0, '.$lineItems[3].'<br>';

    $maxLen = max($maxLen, strlen($lineItems[3]));
    fseek($datFile, $count*50);
    fwrite($datFile, $packDat.trim(trim($lineItems[3], '"')));
    $count++;
  }

}

echo '<p>MAX DESC: '.$maxLen;
fclose($datFile);
fclose($transFile);

?>
