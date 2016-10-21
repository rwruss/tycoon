<?php

$new_names = array("aaa", "bbb", "ccc", "ddd", "eee", 
					"fff", "GGG", "HHH", "iii", "jjj", 
					"kkk", "lll", "mmm", "nnn", "ooo", 
					"ppp", "qqq", "rrr", "sss", "ttt", 
					"uuu", "vvv", "www", "xxx", "yyy", "zzz");
$nameFile = fopen("./users/userNames.dat", "wb");

for ($i=0; $i<26; $i++) {
	fseek($nameFile, 100+$i*40);
	fwrite($nameFile, $new_names[$i]);
	}
fseek($nameFile, 100+25*40+39);
fwrite($nameFile, pack("C", 0));
fseek($nameFile, 0);
fwrite($nameFile, pack("N", 26));
fclose($nameFile);
?>