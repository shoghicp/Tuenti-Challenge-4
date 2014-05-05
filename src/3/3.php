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
$submission = new Submission(3);

$n = (int) $submission->getLine();
for($i = 1; $i <= $n; ++$i){
	$arg = explode(" ", $submission->getLine());
	echo round(sqrt($arg[0] * $arg[0] + $arg[1] * $arg[1]), 2, PHP_ROUND_HALF_UP) . PHP_EOL;
}

$submission->send();