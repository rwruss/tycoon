<?php

$maxLen = 0;
$transFile = fopen('C:\Users\Rhodes Russell\Documents\Capital One\2017bank.csv', 'r');
$datFile = fopen('./transactions/2017.dat', 'r+b');

fseek($datFile, 0, SEEK_END);
$items = ceil(ftell($datFile)/50);

$count = $items;
echo 'Start at '.$count.' items<p>';
//$line = fgets($transFile); // Read the header line

while (($line = fgets($transFile)) !== false) {
  $lineItems = explode(',', $line);

  // check for accidental splits due to commas in description
  $checkSize = sizeof($lineItems);
  if ($checkSize > 7) {
    // merge the offenders into one descripton
    /*
    echo '<p>REASSIGN VALUES';
    print_r($lineItems);
    for ($i=4; $i<$checkSize-3; $i++) {
      $lineItems[3] .= $lineItems[$i];
    }

    $lineItems[4] = $lineItems[$checkSize-3];
    $lineItems[5] = $lineItems[$checkSize-2];
    $lineItems[6] = $lineItems[$checkSize-1];


    echo '<br>TO: ';
    print_r($lineItems);*/
  }
  if ($lineItems[3] == "CAPITAL ONE ONLINE PYMT") {
    echo 'SKIP A PAYMENT ITEM';
  } else {
    //if (strlen($lineItems[5]) > 0) $val = intval($lineItems[5]*100);
    //else   $val = -intval($lineItems[6]*100);
    //$cardNum = $lineItems[2];
    $val = -$lineItems[6]*100;
    $cardNum = 1;
    $intTime = strtotime($lineItems[0]);
    $packDat = pack('i*', $intTime, $val, $cardNum, 0); // date, amount, card number, category
    echo '<p>'.$line.' ---> '.$intTime.'<br>';
    echo $intTime.', '.$val.', '.$cardNum. ', 0, '.$lineItems[2].'<br>';

    $maxLen = max($maxLen, strlen($lineItems[3]));
    fseek($datFile, $count*50);
    fwrite($datFile, $packDat.trim(trim($lineItems[2], '"')));
    $count++;
  }

}

echo '<p>MAX DESC: '.$maxLen;
fclose($datFile);
fclose($transFile);

?>
