<?php

require_once('./objectClass.php');
$projectsFile = fopen($gamePath.'/projects.prj', 'r+b');	
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Verify that the player controls the project
$thisProject = loadProject($postVals[1], $projectsFile);
if ($thisProject->get('owner') != $pGameID) {exit('error 2801-1');}

// Calculate the number of points to apply
$pointsLeft = $thisProject->get('totalPoints') - $thisProject->get('currPoints');
$addPoints = max(0, min($postVals[2], $pointsLeft);

// Verify that the player has enough money for the point amount selected
$thisBusiness = loadObject($pGameID, $unitFile, 400);
if ($thisBusiness->get('money') < $addPoints*100) {exit('error 2801-2');}

// Deduct the money from the player
$thisBusiness->save('money', $thisBusiness->get('money') - $addPoints*100);

// update the amount of construction points at the factory
$thisProject->save('currPoints', $thisProject->get('currPoints') + $addPoints);

fclose($objFile);
fclose($projectFile);

echo '<script>thisPlayer.money = '.$thisBusiness->get('money').'</script>';

?>