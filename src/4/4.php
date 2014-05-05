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
$submission = new Submission(4);

$start = $submission->getLine();
$end = $submission->getLine();

$valid = array();

while(($line = $submission->getLine()) !== false){
	if($line !== ""){
		$valid[$line] = array();
	}
}

unset($valid[$end]);

foreach($valid as $k => $v){
	foreach($valid as $k2 => $v2){
		if($k !== $k2 and diff($k, $k2) === 1){
			$valid[$k][$k2] =& $valid[$k2];
		}
	}

	if(diff($k, $end) === 1){
		$valid[$k][$end] = true;
	}
}

function do_step($used, $valid, $actual, $target){
	$used[$actual] = true;
	$paths[] = array();
	foreach($valid as $k => $v){
		if($k === $target){
			$used[$target] = true;
			return $used;
		}elseif(!isset($used[$k])){
			$paths[] = do_step($used, $v, $k, $target);
		}
	}

	$min = PHP_INT_MAX;
	$select = null;
	foreach($paths as $path){
		if($path !== null and count($path) < $min and count($path) > 0){
			$select = $path;
		}
	}

	return $select;
}

function diff($str1, $str2){
	$len = strlen($str1);
	$diff = 0;
	for($i = 0; $i < $len; ++$i){
		if($str1{$i} !== $str2{$i}){
			++$diff;
		}
	}
	return $diff;
}

if($start === $end){
	echo $end . PHP_EOL;
}else{
	$used = do_step(array($start => true), $valid[$start], $start, $end);
	foreach($used as $k => $v){
		if($k === $start){
			echo $k;
		}else{
			echo "->$k";
		}
	}
	echo PHP_EOL;
}

$submission->send();