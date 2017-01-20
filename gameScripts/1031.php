<?php

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'r+b');

// Load inventory of production boosts for this player

// Load player Data
$thisBusiness = loadObject($pGameID, $objFile, 400);


// Load factory Data & services
$now = time();
$thisFactory = loadObject($postVals[1], $objFile, 1000);

$constructionCost = $thisFactory->get('level')-2;
$moneyCost = $thisFactory->get('level')*100;

if ($thisPlayer->get('money') < $moneyCost) {
	echo 'You do not have enough money';
	exit();
}

if ($thisPlayer->get('serviceItem1') < $constructionCost) {
	echo 'You do not have enough money';
	exit();
}

if ($thisFactory->get('constructCompleteTime') <= $now) {
  $thisFactory->set('constructCompleteTime', $now+3600);
  $thisFactory->set('upgradeInProgress', $thisFactory->get('factoryLevel')+1);
  
  $thisFactory->set('money', $thisFactory->get('money')-$moneyCost);
  $thisFactory->set('serviceItem1', $thisFactory->get('serviceItem1')-$constructionCost);

  $thisFactory->saveAll($thisFactory->linkFile);

  echo '<script>
  thisPlayer.money = '.$thisFactory->get('money').';
  </script>';
} else {
  echo 'There is already an update in progress';
}

fclose($objFile);

?>
