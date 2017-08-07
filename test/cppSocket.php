<?php

$address = 'localhost';
//$address = '127.0.0.1';
$port = 27915;

$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
if ($socket === false) {
	exit(socket_strerror(socket_last_error($socket)));
}

//socket_bind($socket, '127.0.0.1');
echo '<p>'.get_resource_type($socket).'<p>';

$result = socket_connect($socket, $address, $port);
if ($result === false) {
	exit(socket_strerror(socket_last_error($socket)));
}

echo 'Result is: '.$result.' for '.$socket.' on '.$address.' at port '.$port.'<p>';

$in = "hello socket and some other stuff that we want to talk about!";
echo 'Send:: '.$in.' -->';
$checkSent = socket_write($socket, $in, strlen($in));
socket_strerror(socket_last_error($socket));
$count = 0;

echo '<p>Bytes sent:'.$checkSent;
flush();

echo '<p>OUTPUT:<p>';
$buffer = '';
//while ($out = socket_read($socket, 1024) && $count <1000) {
$out = socket_recv ($socket, $buffer, 1024, 0);
while ($out && $count <1000) {
	if ($out === false) {
		exit(socket_strerror(socket_last_error($socket)));
	} else {echo $count.':'. $out.' Bytes: '.$buffer;}
	$out = socket_recv ($socket, $buffer, 1024, 0);
	$count++;
}

socket_close($socket);

?>
