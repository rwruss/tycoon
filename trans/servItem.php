<?php

$postVals = explode(",", $_POST['val1']);

$inputValidate = TRUE;
foreach ($postVals as $value) {
	if (!is_numeric ($value)) $inputValidate = FALSE;
}

if ($inputValidate) {
	include("./scripts/".$postVals[0].".php");
} else {
	echo 'Validation error';
	print_r($postVals);
}
?>
