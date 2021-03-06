<?php

// Output a menu of labor that can be hired from a city

require_once('./objectClass.php');

$schoolFile = fopen($gamePath.'/schools.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

// Load the city
$thisCity = loadCity($postVals[1], $cityFile);

// Load the school type
echo 'seek to '.($postVals[3]*8);
fseek($schoolFile, $postVals[3]*8);
$schoolHead = unpack('i*', fread($schoolFile, 8));
print_R($schoolHead);
if ($schoolHead[2] > 0) {
	fseek($schoolFile, $schoolHead[1]);
	$schoolDat = unpack('i*', fread($schoolFile, $schoolHead[2]));

	print_r($schoolDat);

	echo '<script>
	thisDiv = useDeskTop.newPane("hireLabor");
	thisDiv.innerHTML = "";';
	$schoolSize = sizeof($schoolDat);
	for ($i=1; $i<$schoolSize; $i+=2) {
		echo 'laborArray['.$schoolDat[$i].'].renderHire(thisDiv, '.$schoolDat[$i+1].', "'.$postVals[1].','.$schoolDat[$i].','.$postVals[2].','.$postVals[3].'");';
	}
	echo '</script>';
} else {
	echo '<script>
	thisDiv = useDeskTop.newPane("hireLabor");
	thisDiv.innerHTML = "No labor at this school";
	</script>';
}

fclose($schoolFile);
fclose($cityFile);

?>
