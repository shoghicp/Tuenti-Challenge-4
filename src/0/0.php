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

require(dirname(__FILE__) . "/../Submission.php");
$submission = new Submission(0);

while(($line = $submission->getLine()) !== false){
	$result = 0;
	foreach(explode(" ", $line) as $component){
		if($component !== ""){
			$result = bcadd($result, $component);
		}
	}
	echo $result . PHP_EOL;
}

$submission->send();