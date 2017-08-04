<?php

$address = 'localhost';
$port = 27015;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
	exit(socket_strerror(socket_last_error($socket));
}

$result = socket_connect($socket, $address, $port);
if ($result === false) {
	exit(socket_strerror(socket_last_error($socket));
}

$in = "hello socket";
socket_send($socket, $in, strlen($in), MSG_WAITALL);

while ($out = socket_read($socket, 2048)) {
	echo $out;
}

socket_close($socket);

?>