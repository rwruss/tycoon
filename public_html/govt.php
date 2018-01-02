<?php

function loadGovtInfo ($govtFile, $position) {
  fseek($govtFile, $position);
  $baseDat = fread($govtFile, 1000);

  $returnDat = substr($baseDat, 8);

  echo 'Read Dat::';
  echo substr($baseDat, 0, 8);
  $nextBlock = unpack('i*', substr($baseDat, 0,8));
  print_r($nextBlock);

  while ($nextBlock[1] > 0) {
    fseek($govtFile, $nextBlock[1]);
    $datBlock = fread($govtFile, $nextBlock[2]);

    $nextBlock = unpack('i*', substr($datBlock, 8));
  }

  return $returnDat;
}

?>
