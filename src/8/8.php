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
$submission = new Submission(8);



define("COMBINATIONS", 9 * 8 * 7 * 6 * 5 * 4 * 3 * 2); // 9!
$swapFrom = [0, 1, 3, 4, 6, 7, 0, 1, 2, 3, 4, 5];
$swapTo = [1, 2, 4, 5, 7, 8, 3, 4, 5, 6, 7, 8];

$reqMoves = array();
$boardBuffer = array(
	0 => array(),
	1 => array(),
);

getMoves($reqMoves, $boardBuffer);

$tables = (int) $submission->getLine();
for($x = 0; $x < $tables; ++$x){
	$start = readMatrix($submission);
	$convTable = array();
	foreach($start as $k => $n){
		$convTable[$n] = $k + 1;
		$start[$k] = $k + 1;
	}
	$final = readMatrix($submission);
	foreach($final as $k => $n){
		$final[$k] = $convTable[$n];
	}

	$boardId = hashBoard($final);

	fwrite(STDERR, (isset($reqMoves[$boardId]) ? $reqMoves[$boardId] : -1) . PHP_EOL);
}

function hashBoard($board){
	$result = 0;

	for($i = 0; $i < 9; ++$i){
		$result = $result * 10 + $board[$i];
	}
	return $result;
}

function getMoves(){
	global $reqMoves, $boardBuffer;

	$movNum = 0;
	$moves = 1;

	$base = [0, 0, 0, 0, 0, 0, 0, 0, 0];
	for($j = 0; $j < 2; ++$j){
		for($i = 0; $i < 10000; ++$i){
			$boardBuffer[$j][$i] = $base;
		}
	}
	for($i = 0; $i < 9; ++$i){
		$boardBuffer[0][0][$i] = $i + 1;
	}
	$reqMoves[hashBoard($boardBuffer[0][0])] = 0;

	while($moves !== 0){
		$movNum++;
		$moves = getNextMoves($boardBuffer[($movNum + 1) % 2], $moves, $boardBuffer[$movNum % 2], $movNum);
	}
}

function getNextMoves(&$prevBoard, $prevMoves, &$nextBoard, $movNum){
	global $reqMoves, $boardBuffer, $swapFrom, $swapTo;

	$n = 0;

	for($i = 0; $i < $prevMoves; ++$i){
		for($j = 0; $j < 12; ++$j){
			swapRows($prevBoard[$i], $nextBoard[$n], $swapFrom[$j], $swapTo[$j]);
			$boardId = hashBoard($nextBoard[$n]);
			if(!isset($reqMoves[$boardId])){
				$reqMoves[$boardId] = $movNum;
				++$n;
			}
		}
	}

	return $n;
}

function swapRows($board, &$to, $pos1, $pos2){
	$to = $board;
	$to[$pos1] = $board[$pos2];
	$to[$pos2] = $board[$pos1];
}

function readMatrix(Submission $submission){
	$matrix = array(
		array(),
		array(),
		array(),
	);
	for($i = 0; $i < 4; ++$i){
		$line = $submission->getLine();
		if($i === 0){
			continue;
		}
		$j = $i - 1;
		$line = array_map("trim", explode(",", $line));
		$matrix[$j * 3] = $line[0]." ";
		$matrix[$j * 3 + 1] = $line[1]." ";
		$matrix[$j * 3 + 2] = $line[2]." ";
	}
	return $matrix;
}


$submission->send();