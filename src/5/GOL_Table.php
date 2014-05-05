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

class GOL_Table{

	const CELL_ALIVE = "X";
	const CELL_DEAD = "-";

	private $width;
	private $height;
	private $map;

	public function __construct($map){
		$this->height = count($map);
		$this->width = strlen($map[0]);
		$this->map = $map;

	}

	public function step(){
		$newTable = array();
		for($r = 0; $r < $this->height; ++$r){
			$newTable[$r] = "";
			for($c = 0; $c < $this->width; ++$c){
				$count = $this->countAlive($r, $c);
				if($count < 2){
					$newTable[$r] .= self::CELL_DEAD;
				}elseif($count < 3){
					$newTable[$r] .= $this->map[$r]{$c};
				}elseif($count === 3){
					$newTable[$r] .= self::CELL_ALIVE;
				}else{
					$newTable[$r] .= self::CELL_DEAD;
				}
			}
		}
		$this->map = $newTable;
	}

	public function getHash(){
		$hash = hash_init("sha1");
		for($r = 0; $r < $this->height; ++$r){
			hash_update($hash, $this->map[$r]);
		}
		return hash_final($hash);
	}

	private function countAlive($r, $c){
		$count = 0;
		for($i = $r - 1; $i <= ($r + 1); ++$i){
			for($j = $c - 1; $j <= ($c + 1); ++$j){
				if(($i !== $r or $j !== $c) and $i >= 0 and $j >= 0 and isset($this->map[$i]{$j})){
					if($this->map[$i]{$j} === self::CELL_ALIVE){
						++$count;
					}
				}
			}
		}

		return $count;
	}
}