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

define("FILE_PATH", dirname(__FILE__) . "/");
require(FILE_PATH . "../Submission.php");
$submission = new Submission(10);

date_default_timezone_set("GMT");
$input = trim($submission->getLine());

function toBits($bin){
	$bits = "";
	$len = strlen($bin);
	for($i = 0; $i < $len; ++$i){
		$bits .= str_pad(decbin(ord($bin{$i})), 8, "0", STR_PAD_LEFT);
	}
	return $bits;
}

function toBin($bits){
	return implode(array_map(function($b){return chr(bindec($b));}, str_split($bits, 8)));
}

function check($input, $k, &$lastTime){
	$fp = stream_socket_client("tcp://random.contest.tuenti.net:80");
	fwrite($fp, "GET /index.php?input=$input&password=$k HTTP/1.0\r\nHost: random.contest.tuenti.net\r\n\r\n");
	$str = "";
	while(!feof($fp)){
		$str .= fread($fp, 4096);
	}
	fclose($fp);

	$str = explode("\r\n", $str);

	$lastTime = @strtotime(substr($str[5], 6));

	$result = trim(array_pop($str));

	if($result !== "wrong!"){
		return $result;
	}
	return false;
}

$lastPid = intval(@file_get_contents(FILE_PATH . "lastPid"));
$time = time();
check($input, 0, $time);
srand(@mktime(@date("H", $time), @date("i", $time), 0) * $lastPid);
if(($result = check($input, rand(), $time)) !== false){
	echo $result.PHP_EOL;
}else{
	//Start at 301, default RESERVED_PID + 1 on kernel/pid.c
	for($i = 301; $i < 65535; ++$i){
		srand(@mktime(@date("H", $time), @date("i", $time), 0) * $i);
		$result = check($input, rand(), $time);

		if($result !== false){
			file_put_contents(FILE_PATH . "lastPid", $i);
			echo $result.PHP_EOL;
			break;
		}
	}
}

$submission->send();