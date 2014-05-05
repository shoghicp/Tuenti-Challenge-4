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

class Track{
	const START_END = "#";
	const H_TRACK = "-";
	const V_TRACK = "|";
	const AW_CURVE = "/";
	const CW_CURVE = "\\";
	const SPACE = " ";

	const DIRECTION_H = 1;
	const DIRECTION_V = 2;

	const DIRECTION_H_LEFT = 0;
	const DIRECTION_H_RIGHT = 1;
	const DIRECTION_V_DOWN = 2;
	const DIRECTION_V_UP = 3;

	private $map = array();
	private $needsRotation = false;

	public function __construct($track){
		$this->parseTrack(trim($track));
	}

	public function printTrack(){
		if($this->needsRotation === true){
			$map = array_reverse($this->map);
		}else{
			$map = $this->map;
		}
		foreach($map as $row){
			if($this->needsRotation === true){
				$row = array_reverse($row);
			}
			echo implode($row) . "\n";
		}
	}

	private function parseTrack($track){
		$offset = 0;
		$minV = 0;
		$minH = 0;
		$maxV = 0;
		$maxH = 0;

		$h = 0;
		$v = 0;
		$direction = self::DIRECTION_H_LEFT;
		$map = array();

		while(true){
			$this->parseBlock($map, $h, $v, $direction, $track{$offset++});

			if(isset($track{$offset})){
				if($h < $minH){
					$minH = $h;
				}
				if($h > $maxH){
					$maxH = $h;
				}
				if($v < $minV){
					$minV = $v;
				}
				if($v > $maxV){
					$maxV = $v;
				}
			}else{
				break;
			}
		}

		for($v = $minV; $v <= $maxV; ++$v){
			$this->map[$v - $minV] = array();
			for($h = $minH; $h <= $maxH; ++$h){
				if(!isset($map[$v][$h])){
					$this->map[$v - $minV][$h - $minH] = self::SPACE;
				}else{
					$this->map[$v - $minV][$h - $minH] = $map[$v][$h];
				}
			}
		}

	}

	private function parseBlock(&$map, &$h, &$v, &$direction, $char){
		if(!isset($map[$v])){
			$map[$v] = array();
		}
		if($char === self::H_TRACK and ($direction & self::DIRECTION_V) > 0){
			$char = self::V_TRACK;
		}

		$map[$v][$h] = $char;

		switch($char){
			//By default does nothing, so bad chars get replaced
			case self::START_END:
				if($direction === self::DIRECTION_H_LEFT){
					++$h;
				}else{ //Let's suppose that we get good input :P
					$this->needsRotation = true;
					--$h;
				}
				break;

			case self::H_TRACK:
				if($direction === self::DIRECTION_H_LEFT){
					++$h;
				}else{ //Let's suppose that we get good input :P
					--$h;
				}
				break;

			case self::V_TRACK:
				if($direction === self::DIRECTION_V_UP){
					--$v;
				}else{
					++$v;
				}
				break;

			case self::AW_CURVE:
				if($direction === self::DIRECTION_V_UP){
					++$h;
					$direction = self::DIRECTION_H_LEFT;
				}elseif($direction === self::DIRECTION_V_DOWN){
					--$h;
					$direction = self::DIRECTION_H_RIGHT;
				}elseif($direction === self::DIRECTION_H_LEFT){
					--$v;
					$direction = self::DIRECTION_V_UP;
				}elseif($direction === self::DIRECTION_H_RIGHT){
					++$v;
					$direction = self::DIRECTION_V_DOWN;
				}
				break;

			case self::CW_CURVE:
				if($direction === self::DIRECTION_V_UP){
					--$h;
					$direction = self::DIRECTION_H_RIGHT;
				}elseif($direction === self::DIRECTION_V_DOWN){
					++$h;
					$direction = self::DIRECTION_H_LEFT;
				}elseif($direction === self::DIRECTION_H_LEFT){
					++$v;
					$direction = self::DIRECTION_V_DOWN;
				}elseif($direction === self::DIRECTION_H_RIGHT){
					--$v;
					$direction = self::DIRECTION_V_UP;
				}
				break;
		}
	}
}