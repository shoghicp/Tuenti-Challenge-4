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
$submission = new Submission(15);

$puzzles = (int) $submission->getLine();

for($puzzle = 0; $puzzle < $puzzles; ++$puzzle){
	$map = [];
	$data = explode(" ", trim($submission->getLine()));
	$start = strtolower(array_shift($data)) === "white" ? ReversiBoard::WHITE_DISC : ReversiBoard::BLACK_DISC;
	$turns = (int) array_pop($data);

	for($y = 0; $y < 8; ++$y){
		$map[$y] = trim($submission->getLine());
	}
	$board = new ReversiBoard($map);
	//var_dump($board);

	$result = null;

	foreach($board->getMoves($start) as $move){
		if(($move[0] === 0 or $move[0] === 7) and ($move[1] === 0 or $move[1] === 7)){
			$result = [$move[0], $move[1]];
			break;
		}
		$newBoard = clone $board;
		$newBoard->doMove($move);
		if(doMoves($newBoard, $start, $start === ReversiBoard::BLACK_DISC ? ReversiBoard::WHITE_DISC : ReversiBoard::BLACK_DISC, $turns - 1)){
			$result = [$move[0], $move[1]];
			break;
		}
	}

	if($result === null){
		echo "Impossible" . PHP_EOL;
	}else{
		echo chr($result[0] + 0x61) . ($result[1] + 1) . PHP_EOL;
	}

}

$submission->send();

function doMoves(ReversiBoard $board, $w, $v, $moves){
	if($moves <= 0){
		return false;
	}

	if($w === $v){
		--$moves;
	}

	$next = $v === ReversiBoard::BLACK_DISC ? ReversiBoard::WHITE_DISC : ReversiBoard::BLACK_DISC;
	$allMoves = $board->getMoves($v);

	if(count($allMoves) === 0){
		if(doMoves($board, $w, $next, $moves)){
			return true;
		}else{
			return false;
		}
	}else{
		$cancel = false;
		$return = true;
		foreach($allMoves as $move){
			if($w === $v and $moves === 0 and ($move[0] === 0 or $move[0] === 7) and ($move[1] === 0 or $move[1] === 7)){
				return true;
			}elseif($cancel === true){
				continue;
			}
			$newBoard = clone $board;
			$newBoard->doMove($move);
			if(!doMoves($newBoard, $w, $next, $moves)){
				if($w !== $v){
					$cancel = true;
				}
				$return = false;
			}elseif($w === $v and $moves === 1){
				return true;
			}
		}

		return $return === true and $cancel === false;
	}


}


class ReversiBoard{
	const EMPTY_DISC = ".";
	const WHITE_DISC = "O";
	const BLACK_DISC = "X";

	protected $empty = [];
	protected $map;

	public function __construct(array $map){
		$this->map = $map;
		$this->checkEmpty();
	}

	protected function checkEmpty(){
		for($y = 0; $y < 8; ++$y){
			$this->empty[$y] = [];
			for($x = 0; $x < 8; ++$x){
				if($this->get($x, $y) === self::EMPTY_DISC){
					$this->empty[$y][$x] = true;
				}
			}
		}
	}

	public function get($x, $y){
		return $this->map[$y]{$x};
	}

	protected function set($x, $y, $v){
		$this->map[$y]{$x} = $v;
	}

	public function getEmpty(){
		return $this->empty;
	}

	public function doMove(array $move){
		unset($this->empty[$move[1]][$move[0]]);
		$this->set($move[0], $move[1], $move[2]);
		foreach($move[3] as $data){
			$this->set($data[0], $data[1], $move[2]);
		}
	}

	public function getMoves($v){
		$moves = [];
		foreach($this->getEmpty() as $y => $d){
			foreach($d as $x => $b){
				if(count($m = $this->canPlace($x, $y, $v)) > 0){
					$moves[] = [$x, $y, $v, $m];
				}
			}
		}
		return $moves;
	}

	public function canPlace($x, $y, $v){
		if(isset($this->empty[$y][$x])){
			$need = $v === self::BLACK_DISC ? self::WHITE_DISC : self::BLACK_DISC;

			$changes = [];

			$step = [];
			$yOff = $y - 1;
			while($yOff >= 0){
				if(($piece = $this->get($x, $yOff)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$x, $yOff];
				--$yOff;
			}

			$step = [];
			$yOff = $y + 1;
			while($yOff < 8){
				if(($piece = $this->get($x, $yOff)) !== $need){
					global $puzzle;
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$x, $yOff];
				++$yOff;
			}

			$step = [];
			$xOff = $x - 1;
			while($xOff >= 0){
				if(($piece = $this->get($xOff, $y)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$xOff, $y];
				--$xOff;
			}

			$step = [];
			$xOff = $x + 1;
			while($xOff < 8){
				if(($piece = $this->get($xOff, $y)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$xOff, $y];
				++$xOff;
			}

			$step = [];
			$xOff = $x + 1;
			$yOff = $y + 1;
			while($xOff < 8 and $yOff < 8){
				if(($piece = $this->get($xOff, $yOff)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$xOff, $yOff];
				++$xOff;
				++$yOff;
			}

			$step = [];
			$xOff = $x + 1;
			$yOff = $y - 1;
			while($xOff < 8 and $yOff >= 0){
				if(($piece = $this->get($xOff, $yOff)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$xOff, $yOff];
				++$xOff;
				--$yOff;
			}

			$step = [];
			$xOff = $x - 1;
			$yOff = $y + 1;
			while($xOff >= 0 and $yOff < 8){
				if(($piece = $this->get($xOff, $yOff)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$xOff, $yOff];
				--$xOff;
				++$yOff;
			}

			$step = [];
			$xOff = $x - 1;
			$yOff = $y - 1;
			while($xOff >= 0 and $yOff >= 0){
				if(($piece = $this->get($xOff, $yOff)) !== $need){
					if(count($step) > 0 and $piece !== self::EMPTY_DISC){
						$changes = array_merge($changes, $step);
					}
					break;
				}
				$step[] = [$xOff, $yOff];
				--$xOff;
				--$yOff;
			}


			return $changes;
		}else{
			return [];
		}
	}
}