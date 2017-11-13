<?php

$defaultBlockSize = 100;
$unitBlockSize = 400;
$jobBlockSize = 200;
$warBlockSize = 200;
$factorySize = 1100;
$supplyBlockSize = 360000;

session_start();
$gameID = $_GET['gid'];
if ($gameID != $_SESSION['instance']) {echo "<script>alert('Game mismatch')</script>";exit;}
if (!isset($_SESSION['gameIDs'][$gameID])) echo "<script>window.location.replace('./index.php')</script>";

if (!isset($_SESSION['game_'.$gameID])) {
	$paramFile = fopen('../games/'.$gameID.'/params.ini', 'rb');
	$params = unpack('i*', fread($paramFile, 100));
	$_SESSION['game_'.$gameID]['scenario'] = $params[9];
	$_SESSION['game_'.$gameID]['scenario'] = 1;
	$_SESSION['game_'.$gameID]['culture'] = 1; // Set and record player culture
	fclose($paramFile);
}
$pGameID = $_SESSION['gameIDs'][$gameID];
$postVals = explode(",", $_POST['val1']);

$inputValidate = TRUE;
foreach ($postVals as $value) {
	if (!is_numeric ($value)) $inputValidate = FALSE;
}
$gamePath = "../games/".$gameID;
$scnPath = "../scenarios/".$_SESSION['game_'.$gameID]['scenario'];
if ($inputValidate) {

	include("../gameScripts/".$postVals[0].".php");
} else {
	if ($postVals[0] > 3000) {
		include("../gameScripts/".$postVals[0].".php");
	} else {
	echo 'Validation error';
	print_r($postVals);
	exit();
	}
}
?>
