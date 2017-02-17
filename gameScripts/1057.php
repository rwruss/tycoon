<?php

/*
pvs
1-city ID
2-labor item #
3 - factory
4 - school type
*/

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');
$schoolFile = fopen($gamePath.'/schools.dat', 'rb');
$cityFile = fopen($gamePath.'/cities.dat', 'r+b');

// if factory is hiring - load the factory and check that player controls it
if ($postVals[3] > 0) {
	$thisFactory = loadObject($postVals[1], $objFile, 1000);
	if ($thisFactory->get('owner') != $pGameID) exit("You are not authorized to hire at this factory");
	
	$spotFail = true;
	for ($i=0; $i<7; $i++) {
		if ($thisFactory->objDat[131+$i*10] == 0) {
			$spotFail = false;
			$factorySpot = $i;
			break;
		}
	}	
	if ($spotFail) exit("No more room for labor at this factory");
}

// Load the city
$thisCity = loadCity($postVals[3], $cityFile);

// Load the school types
fseek($schoolFile, $postVals[4]*8);
$schoolHead = unpack('i*', fread($schoolFile, 8));

fseek($schoolFile, $schoolHead[1]);
$schoolDat = unpack('i*', fread($schoolFile, $schoolHead[2]));

// verify that the school exists in the city
$schoolLevel = $thisCity->objDat[91+($postVals[4]-1)*3];

// verify that the school can train this type of labor
$schoolFail = true;
$schoolSize = sizeof($schoolDat);
for ($i=1; $i<$schoolSize; $i+=2) {
	$schoolFail = false;
	error fix me!
}

if ($schoolFail) exit("This school cannot train this type of labor");

// Add the labor to the factory or the company
$now = time();
if ($postVals[3] > 0) {
	$thisFactory -> changeLaborItem($factorySpot, [0, $postVals[2], 0, $now, 0, 0, $now, $now, 0, 0]) //($spotNumber, $attrArray)
}
/*
echo '<script>';
$schoolSize = sizeof($schoolDat);
for ($i=1; $i<$schoolSize; $i+=2) {
	echo 'laborArray['.$schoolDat[$i].'].renderHire(target, '.$schoolDat[$i+1].', '.$postVals[3].', '.$postVals[4].');';
}
echo '</script>';
*/
fclose($objFile);
fclose($schoolFile);
fclose($cityFile);

?>