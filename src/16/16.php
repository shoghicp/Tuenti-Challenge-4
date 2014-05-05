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
$submission = new Submission(16);

ini_set("memory_limit", "2G");

define("CHUNK_SIZE_HALF", 500);
define("CHUNK_SIZE", CHUNK_SIZE_HALF * 2);

$details = explode(",", $submission->getLine());


$start = (int) $details[0];
$count = (int) $details[1];

$points = new SplFileObject(FILE_PATH . "points", "rb");

//get to the entry we want
for($i = 1; $i < $start; ++$i){
	$points->fgets();
}

$chunks = [];
$collisions = 0;

$X = null;
$Y = null;
for($i = 0; $i < $count; ++$i){
	$line = sscanf($points->fgets(), "%*[ \t]%d%*[ \t]%d%*[ \t]%d");
	$x = $line[0];
	$y = $line[1];
	$radius = $line[2];
	$index = chunk($x, $y);
	getXY($index, $X, $Y);
	$object = [$x, $y, $radius];
	foreach(getNeighbours($x, $y, $radius) as $chunkIndex){
		if(!isset($chunks[$chunkIndex])){ //Empty
			continue;
		}
		foreach($chunks[$chunkIndex] as $targetObject){
			if(collides($object, $targetObject)){
				++$collisions;
			}
		}
	}
	if(!isset($chunks[$index])){
		$chunks[$index] = [];
	}
	$chunks[$index][] = $object;

	/*if(($i % 16) === 0){
		fwrite(STDERR, "\r$i\t$collisions\t".($i / $count) * 100);
	}*/
}
//fwrite(STDERR, PHP_EOL);
$x = null;
$y = null;

echo $collisions . PHP_EOL;


$submission->send();

function getNeighbours($x, $y, $radius){
	$bX = $x - ($x % CHUNK_SIZE);
	$bY = $y - ($y % CHUNK_SIZE);
	$X = (int) ($bX / CHUNK_SIZE);
	$Y = (int) ($bY / CHUNK_SIZE);

	$left = false;
	$right = false;
	$up = false;
	$down = false;

	$n = [chunkIndex($X, $Y)];
	if(($x - $radius) <= ($bX + CHUNK_SIZE_HALF)){
		$n[] = chunkIndex($X - 1, $Y);
		$right = true;
	}
	if(($y - $radius) <= ($bY + CHUNK_SIZE_HALF)){
		$n[] = chunkIndex($X, $Y - 1);
		$up = true;
	}
	if(($x + $radius) >= ($bX + CHUNK_SIZE_HALF)){
		$n[] = chunkIndex($X + 1, $Y);
		$left = true;
	}
	if(($y + $radius) >= ($bY + CHUNK_SIZE_HALF)){
		$n[] = chunkIndex($X, $Y + 1);
		$down = true;
	}
	if($right){
		if($up){
			$n[] = chunkIndex($X - 1, $Y - 1);
		}
		if($down){
			$n[] = chunkIndex($X - 1, $Y + 1);
		}
	}
	if($left){
		if($up){
			$n[] = chunkIndex($X + 1, $Y - 1);
		}
		if($down){
			$n[] = chunkIndex($X + 1, $Y + 1);
		}
	}
	return $n;
}

function collides($object1, $object2){
	return (pow($object1[0] - $object2[0], 2) + pow($object1[1] - $object2[1], 2)) <= pow($object1[2] + $object2[2], 2);
}

function chunkIndex($X, $Y){
	return "$X:$Y";
}

function chunk($x, $y){
	return chunkIndex(floor($x / CHUNK_SIZE), floor($y / CHUNK_SIZE));
}

function getXY($index, &$X, &$Y){
	$d = explode(":", $index);
	$X = (int) $d[0];
	$Y = (int) $d[1];
}