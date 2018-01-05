<?php

function loadGovtInfo ($govtFile, $position) {
  fseek($govtFile, $position);
  $baseDat = fread($govtFile, 1000);

  $returnDat = substr($baseDat, 20);

  echo 'Read Dat::';
  echo substr($baseDat, 0, 20);
  $nextBlock = unpack('i*', substr($baseDat, 0,20));
  print_r($nextBlock);

  while ($nextBlock[2] > 0) {
    fseek($govtFile, $nextBlock[2]);
    $datBlock = fread($govtFile, $nextBlock[3]);

    $nextBlock = unpack('i*', substr($datBlock,0,20));
	   $returnDat = substr($datBlock, 20);
  }

  return $returnDat;
}

?>
