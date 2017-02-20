<?php

// Output a menu of labor that can be hired from a city

require_once('./objectClass.php');

$schoolFile = fopen($gamePath.'/schools.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

// Load the city
$thisCity = loadCity($postVals[1], $cityFile);

// Load the school type
fseek($schoolFile, $postVals[3]*8);
$schoolHead = unpack('i*', fread($schoolFile, 8));
print_R($schoolHead);

fseek($schoolFile, $schoolHead[1]);
$schoolDat = unpack('i*', fread($schoolFile, $schoolHead[2]));

echo '<script>
useDeskTop.newPane("hireLabor");
thisDiv = useDeskTop.getPane("hireLabor");
thisDiv.innerHTML = "";';
$schoolSize = sizeof($schoolDat);
for ($i=1; $i<$schoolSize; $i+=2) {
	echo 'laborArray['.$schoolDat[$i].'].renderHire(thisDiv, '.$schoolDat[$i+1].', "'.$postVals[1].','.$schoolDat[$i].','.$postVals[2].','.$postVals[3].'");';
}
echo '</script>';

fclose($schoolFile);
fclose($cityFile);

?>
