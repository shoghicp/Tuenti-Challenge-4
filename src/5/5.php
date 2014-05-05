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
require("GOL_Table.php");
$submission = new Submission(5);

$map = array();
$r = 0;
while(($line = $submission->getLine()) !== false){
	if($line !== ""){
		$map[$r++] = trim($line);
	}
}

$map = new GOL_Table($map);

$hashes = array();

for($gen = 0; $gen <= 100; ++$gen){
	$hash = $map->getHash();
	if(isset($hashes[$hash])){
		echo $hashes[$hash] . " " . ($gen - $hashes[$hash]) . PHP_EOL;
		break;
	}else{
		$hashes[$hash] = $gen;
	}

	$map->step();
}

$submission->send();