<?php

//print_R($postVals);
``
$cityFile = fopen($gamePath.'/cities.dat', 'rb');

// Load the city information
$thisCity = loadCity($postVals[2], $cityFile);

?>
