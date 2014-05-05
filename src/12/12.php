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
$submission = new Submission(12);

ini_set("memory_limit", "256M");

$cases = (int) $submission->getLine();
for($case = 1; $case <= $cases; ++$case){
	$map = array();
	$start = null;
	$goal = null;

	$i = 0;
	$dimensions = explode(" ", $submission->getLine());
	$width = (int) $dimensions[0];
	$height = (int) $dimensions[1];

	for($i = 0; $i < $height; ++$i){
		$line = trim($submission->getLine());
		$map[$i] = $line;
		if(($pos = strpos($line, "S")) !== false){
			$start = $i * $width + $pos;
		}
		if(($pos = strpos($line, "X")) !== false){
			$goal = $i * $width + $pos;
		}
	}

	if($start === null or $goal === null){
		echo "Case #$case: ERROR" . PHP_EOL;
	}else{

		$nodes = array();
		$moves = [
			// movI, movJ, direction
			0 => [ //down
				[ 1,  0, 0], //down
				[ 0, -1, 2] //left
			],
			1 => [ //up
				[ -1, 0, 1], //up
				[  0, 1, 3] //right
			],
			2 => [ //left
				[  0, -1, 2], //left
				[ -1,  0, 1] //up
			],
			3 => [ //right
				[ 0,  1, 3], //right
				[ 1,  0, 0] //down
			]

			/*[-1,  0],
			[ 1,  0],
			[ 0, -1],
			[ 0,  1]*/
		];

		//$nodeMap = array();
		$nodeTable = array();
		$nodeIndex = array();

		foreach($map as $i => $line){
			for($j = 0; $j < $width; ++$j){
				$index = $i * $width + $j;
				$nodeMap[$index] = $line{$j};
				$nodeTable[$index] = [$i, $j];
				for($k = 0; $k < 4; ++$k){
					$move_index = $k * 100000 + $index;
					$nodes[$move_index] = array();
					$nodeIndex[$move_index] = $index;
					foreach($moves[$k] as $mx => $move){
						$mi = $i + $move[0];
						$mj = $j + $move[1];
						if(isset($map[$mi]{$mj}) and $map[$mi]{$mj} !== "#" and $map[$mi]{$mj} !== "S"){
							$nodes[$move_index][] = $mi * $width + $mj + $move[2] * 100000;
						}
					}
				}
			}
		}

		$minLength = PHP_INT_MAX;
		$closedset = array();
		$openset = new MinPriorityQueue();
		$came_from = array();
		$g_score = array();
		$f_score = array();

		for($d = 0; $d < 4; ++$d){
			$start_index = $d * 100000 + $start;
			$g_score[$start_index] = 0;
			$f_score[$start_index] = $g_score[$start_index];// + heuristic($start, $goal);
			//var_dump($nodes[$start_index]);
			$openset->insert($start_index, $f_score[$start_index]);
		}

		//die();

		while(!$openset->isEmpty()){
			$current = $openset->extract();

			$current_index = $nodeIndex[$current];

			//fwrite(STDERR, "[".$nodeTable[$current_index][0].", ".$nodeTable[$current_index][1]."]".PHP_EOL);
			//usleep(100000);
			if($current_index === $goal){
				$minLength = reconstruct($came_from, $current);
				break;
			}

			$closedset[$current] = true;

			foreach($nodes[$current] as $neighbor){
				if(isset($closedset[$neighbor])){
					continue;
				}

				$neighbor_index = $nodeIndex[$neighbor];
				$tentative_g_score = $g_score[$current] + 1;

				if(!$openset->exists($neighbor) or $tentative_g_score < $g_score[$neighbor]){
					$came_from[$neighbor] = $current;
					$g_score[$neighbor] = $tentative_g_score;
					$f_score[$neighbor] = $g_score[$neighbor];// + heuristic($neighbor_index, $goal);
					$openset->insert($neighbor, $f_score[$neighbor]);
				}
			}
		}

		if($minLength === PHP_INT_MAX){
			echo "Case #$case: ERROR" . PHP_EOL;
		}else{
			echo "Case #$case: $minLength" . PHP_EOL;
		}
	}
	//die();
}

$submission->send();

function heuristic($node1, $node2){
	global $nodeTable;
	$node1 = $nodeTable[$node1];
	$node2 = $nodeTable[$node2];
	return abs($node1[0] - $node2[0]) + abs($node1[1] - $node2[1]);
}

function reconstruct($came_from, $cur){
	$p = 0;
	while(isset($came_from[$cur])){
		$cur = $came_from[$cur];
		++$p;
	}
	return $p;
}

//Needs to be modified... can't use SplPriorityQueue
class MinPriorityQueue{
	private $queue = array();

	public function isEmpty(){
		return count($this->queue) === 0;
	}

	public function exists($id){
		return isset($this->queue[$id]);
	}

	public function extract(){
		end($this->queue);
		$id = key($this->queue);
		unset($this->queue[$id]);
		return $id;
	}

	public function insert($id, $priority){
		$this->queue[$id] = $priority;
		arsort($this->queue);
	}

}