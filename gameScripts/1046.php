<?php

// Show full content of in game player message;

require_once('./objectClass.php');

$objFile = fopen($gamePath.'/objects.dat', 'rb');
$messageFile = fopen($gamePath.'/messages.dat', 'rb');

?>