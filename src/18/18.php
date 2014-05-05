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
$submission = new Submission(18);

$input = $submission->getLine();

$data = get($input, 1);
$base = gmp_init(trim($data[6]));
$modulo = gmp_init(trim($data[7]));
$final = gmp_init(trim($data[8]));
$exponent = gmp_init(2);

$test = gmp_init(77);

var_dump(gmp_cmp($final, gmp_powm($base, $exponent, $modulo)) !== 0);

function get($input, $k){
	$fp = stream_socket_client("tcp://54.83.207.90:9083");
	fwrite($fp, "GET /index.py?$input:$k HTTP/1.0\r\nHost: 54.83.207.90\r\n\r\n");
	$str = "";
	while(!feof($fp)){
		$str .= fread($fp, 4096);
	}
	fclose($fp);

	$str = explode("\n", $str);

	return $str;
}