<?php

require_once('./slotFunctions.php');
require_once('./objectClass.php');
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the city information
$thisCity = loadCity($postVals[1], $cityFile);

// Load the school information

// load the project information if there is any

// output the current status of construction
//echo 'Data for school '.$postVals[2];
//print_r($thisCity->schoolDat);
echo '0|'.$postVals[2].'|0';

fclose($cityFile);

?>
