<?php

require_once('./objectClass.php');
//$objFile = fopen($gamePath.'/objects.dat', 'rb');
$projectsFile = fopen($gamePath.'/projects.prj', 'r+b');	

// Verify that the player controls the project
$thisProject = loadProject($postVals[1], $projectsFile);
if ($thisProject->get('owner') != $pGameID) {exit('error 2801-1');}

// Update the price of new construction points
$thisProject->save('currPrice', $postVals[2]);

//fclose($objFile);
fclose($projectsFile);

?>