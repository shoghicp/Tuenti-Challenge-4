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
$submission = new Submission(9);

define("DESTINATION", "AwesomeVille");

$cities = (int) $submission->getLine();
$roadTypes = array(
	"normal" => 0,
	"dirt" => 1,
);

for($n = 0; $n < $cities; ++$n){
	$name = trim($submission->getLine());
	$speed = array_map("intval", explode(" ", trim($submission->getLine())));

	$nodesTo = array(
		$name => array(),
		DESTINATION => array(),
	);
	$nodesFrom = array(
		DESTINATION => array(),
		$name => array(),
	);
	$input = array();
	$output = array();

	$numbers = array_map("intval", explode(" ", trim($submission->getLine())));
	for($k = 0; $k < $numbers[1]; ++$k){
		$road = explode(" ", trim($submission->getLine()));
		if($road[0] === $road[1]){ //hmm...
			continue;
		}
		if(!isset($nodesTo[$road[0]])){
			$nodesTo[$road[0]] = array();
		}
		if(!isset($nodesFrom[$road[1]])){
			$nodesFrom[$road[1]] = array();
		}
		if(!isset($nodesTo[$road[1]])){
			$nodesTo[$road[1]] = array();
		}
		if(!isset($nodesFrom[$road[0]])){
			$nodesFrom[$road[0]] = array();
		}

		$input[$road[0]] = 0;
		$output[$road[0]] = 0;
		$input[$road[1]] = 0;
		$output[$road[1]] = 0;
		$nodesFrom[$road[1]][$road[0]] = $speed[$roadTypes[$road[2]]] * $road[3];
		$nodesTo[$road[0]][$road[1]] = $speed[$roadTypes[$road[2]]] * $road[3];

	}


	//Remove a few cycles
	$changed = true;
	$delete = array();
	while($changed === true){
		$changed = false;
		foreach($nodesTo as $key => $data){
			unset($nodesTo[$key][$name]);
			if(count($data) === 0 and $key !== DESTINATION){
				unset($nodesTo[$key]);
				unset($input[$key]);
				unset($output[$key]);
				unset($nodesFrom[$key]);
				$delete[$key] = true;
			}
		}

		foreach($nodesFrom as $key => $data){
			unset($nodesFrom[$key][DESTINATION]);
			if(count($data) === 0 and $key !== $name){
				unset($nodesTo[$key]);
				unset($input[$key]);
				unset($output[$key]);
				unset($nodesFrom[$key]);
				$delete[$key] = true;
			}
		}

		foreach($delete as $key => $b){
			unset($nodesTo[$key]);
			unset($input[$key]);
			unset($output[$key]);
			unset($nodesFrom[$key]);
			foreach($nodesTo as $k => $v){
				unset($nodesTo[$k][$key]);
			}
			foreach($nodesFrom as $k => $v){
				unset($nodesFrom[$k][$key]);
			}
		}

		if(count($delete) > 0){
			$changed = true;
		}

		$delete = array();
	}

	$q = new SplQueue();
	$input[$name] = PHP_INT_MAX;
	$output[$name] = PHP_INT_MAX;

	$cnt = 0;

	while($cnt < count($input) or $changed === true){
		$changed = false;
		foreach($nodesTo[$name] as $key => $speed){
			$q->enqueue(array($key, [], 0));
		}

		while(!$q->isEmpty()){
			$data = $q->dequeue();
			$node = $data[0];
			if(isset($data[1][$node]) and ($data[2] - $data[1][$node]) > 1){
				continue;
			}

			$input[$node] = 0;

			foreach($nodesFrom[$node] as $key => $speed){
				if(!isset($data[1][$key]) or ($data[2] - $data[1][$key]) <= 1){
					$input[$node] += min($output[$key], $speed);
					$data[1][$key] = $data[2];
				}
			}

			if($output[$node] === $input[$node]){
				continue;
			}else{
				$changed = true;
			}

			$output[$node] = $input[$node];

			if($node === DESTINATION){
				continue;
			}

			$data[1][$node] = $data[2];

			foreach($nodesTo[$node] as $key => $speed){
				if(!isset($data[1][$key]) or ($data[2] - $data[1][$key]) <= 1){
					$q->enqueue(array($key, $data[1], $data[2] + 1));
				}
			}
		}

		++$cnt;
	}
	echo $name . " ". floor(($output[DESTINATION] * 1000) / 5).PHP_EOL;

}