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
$submission = new Submission(7);

ini_set("memory_limit", -1);


$fp = fopen(FILE_PATH . "phone_call.log", "r");
$log = array();

$i = 0;
while(($line = fgets($fp)) !== false){
	$line = explode(" ", trim($line));
	if(!isset($log[$line[0]])){
		$log[$line[0]] = array();
	}
	if(!isset($log[$line[1]])){
		$log[$line[1]] = array();
	}
	$log[$line[0]][$i] = $line[1];
	$log[$line[1]][$i] = $line[0];
	++$i;
}

fclose($fp);

$tA = trim($submission->getLine());
$tB = trim($submission->getLine());

$path = array();
$q = new SplQueue();
$q->enqueue($tA);
$path[$tA] = true;

$final = null;
while(!$q->isEmpty()){
	$t = $q->dequeue();
	if($t === $tB){
		$final = $t;
		break;
	}
	foreach($log[$t] as $l => $e){
		if(!isset($path[$e])){
			$path[$e] = $l;
			$q->enqueue($e);
		}
	}
}

if($final === null){
	echo "Not connected" . PHP_EOL;
}else{
	echo "Connected at ".$path[$final] . PHP_EOL;
}

$submission->send();