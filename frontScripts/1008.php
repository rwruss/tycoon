<?php
session_start();
echo "scrPane";

//print_r($postVals);

// Find User Id
$testName = strtolower(substr($postVals[1], 0, 30));

$numNames = (filesize("../users/userNames.dat")-100)/40;
$nameFile = fopen("../users/userNames.dat", "r+b");
$lastOrdered = unpack("N", fread($nameFile, 4));

//echo "Check ".$numNames." Names<br>";
$checkPoint = intval($numNames/2);
$interval = intval($numNames/4);
//echo "CheckPoint: ".$checkPoint.", Interval: ".$interval."<br>";
$match=FALSE;
fseek($nameFile, 100+$checkPoint*40);
$compareName = strtolower(trim(fread($nameFile, 36)));
//echo $testName." vs ".trim($compareName)."<br>";
if ($testName < $compareName) {
		$mult = -1;
		}
	else {
		$mult = 1;
		}


while (1< 2) {
	//echo "CheckPoint: ".$checkPoint.", Interval: ".$interval.", Mult: ".$mult."<br>";
	fseek($nameFile, 100+$checkPoint*40);
	$compareName = strtolower(trim(fread($nameFile, 36)));
	echo $testName." vs ".trim($compareName)."<br>";
	if ($testName < $compareName) {
		if ($mult == 1) $interval = intval($interval/2);
		$mult = -1;
		}
	elseif ($testName > $compareName) {
		if ($mult == -1) $interval = intval($interval/2);
		$mult = 1;
		if ($interval<10 && $mult==1) break;
		}
	else {
		//echo "Match Found";
		$match = TRUE;
		break;
		}
	$checkPoint = min($numNames, max(0,$checkPoint + $interval*$mult));

	}
// Iterate through last 10 candidates for matches
echo "FINAL - CheckPoint: ".$checkPoint.", Interval: ".$interval.", Mult: ".$mult."<br>";
fseek($nameFile, 100+$checkPoint*40);
$finalRead = fread($nameFile, min(400, ($numNames-$checkPoint)*40));
for ($i=0; $i<min(10, $numNames-$checkPoint); $i++) {
	//echo $testName." vs ".trim(substr($finalRead, $i*40, 36))."<br>";
	if ($testName == trim(substr($finalRead, $i*40, 36))) {
		$match = TRUE;
		$matchVal = unpack("N", substr($finalRead, $i*40+36, 4));
		}
	}
// Final check is against all unordered names
echo "<p>Read any unordered names - (".$lastOrdered[1].")";
if ($numNames > $lastOrdered[1]) {
	fseek($nameFile, 100+$lastOrdered[1]*40);
	$finalRead = fread($nameFile, ($numNames-$lastOrdered[1])*40);
	$numFinals = strlen($finalRead)/40;
	for ($i=0; $i<$numFinals; $i++) {
		//echo $testName." vs ".trim(substr($finalRead, $i*40, 36))."<br>";
		if ($testName == trim(substr($finalRead, $i*40, 36))) {
			$match = TRUE;
			$matchVal = unpack("N", substr($finalRead, $i*40+36, 4));
			}
		}
	}

if ($match) {
	// Check Password
	$checkFile = fopen("../users/userCheck.dat", "rb");
	fseek($checkFile, $matchVal[1]*255);
	$checkVal = trim(fread($checkFile, 255));
	fclose($checkFile);
	if (md5($postVals[2]) == $checkVal) {
		// Load player data
		$playerFile = fopen("../users/userDat.dat", "r+b");
		fseek($playerFile, $matchVal[1]*500);
		$playerDat = unpack('i*', fread($playerFile, 500));
		fclose($playerFile);

		// Set session variables

		$_SESSION['playerId'] = $matchVal[1];
		$_SESSION['pHandle'] = $testName;
		$_SESSION['boosts'] = array_slice($playerDat, 50, 20);
		$_SESSION['gold'] = $playerDat[2];
		echo "<Script>document.getElementById('plrPane').innerHTML = '".$testName."  - ".$_SESSION['playerId']."';
			passClick(1004);
		</script>";
		}
	else {
		echo md5($postVals[2])." did not match ".$checkVal;
		echo "<Script>alert('Incorrect Password (".$matchVal[1].") - (".$checkVal.")')</script>";
		}
	}
else {
	echo "<Script>alert('User Name not Found')";
	}
fclose($nameFile);




?>
