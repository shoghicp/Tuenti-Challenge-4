<?php

/*
 * Tuenti Challenge 4
 *
 * Submission by Shoghi Cervantes
 * @shoghicp / shoghicp@gmail.com
 *
 * Files and helper classes will be uploaded to
 * https://github.com/shoghicp/Tuenti-Challenge-4
 *
 */

$mitm = fsockopen("54.83.207.90", 6969);

while(($line = fgets($mitm)) !== false){
	$line = trim($line);
	echo $line . PHP_EOL;
	echo ": ";
	$mod = trim(fgets(STDIN));
	if($mod === ""){
		$mod = substr($line, strpos($line, ":") + 1);
	}
	fwrite($mitm, $mod . "\n");
}

fclose($mitm);