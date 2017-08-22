<?php

//phpinfo();

require_once('./objectClass.php');

$tmpA = array_fill(1, 29, 0);
for ($i=1; $i<=29; $i++) {
  $tmpA[$i] = $i;
}

$packArguments = ["N", "N", "S", "S", "S", "C", "C", "C", "C", "C", "S", "C", "S", "C", "S", "C", "S", "C", "S", "C", "S", "C", "S", "C", "S", "C", "S", "C"];

$testPack = pack("NNSSSCCCCSCSCSCSCSCSCSCSCSCSC", ...$tmpA);
echo 'String lineght is '.strlen($testPack);

/*
$testUnpack = unpack(...$packArguments, $testPack);
print_r($testUnpack);
*/
echo '<p>Test labor result';
$testLabor = new labor($testPack);
print_r($testLabor->laborDat);

?>
