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

class Submission{
	private $phase;
	private $stdin;
	private $lines;
	private $path;
	private $isFinal;
	private $offset = 0;

	public function __construct($phase){
		$this->path = dirname(__FILE__) . "/../";
		$this->phase = (int) $phase;
		$this->stdin = stream_get_contents(STDIN);
		$this->lines = explode("\n", $this->stdin);

		if(!file_exists($this->path . "input/". $this->phase .".test")){
			file_put_contents($this->path . "input/". $this->phase .".test", $this->stdin);
			$this->isFinal = false;
		}elseif(file_get_contents($this->path . "input/". $this->phase .".test") === $this->stdin){
			$this->isFinal = false;
		}else{
			file_put_contents($this->path . "input/". $this->phase .".final", $this->stdin);
			$this->isFinal = true;
		}
		ob_start(); //Catch all the output!
	}

	public function isFinal(){
		return $this->isFinal === true;
	}

	public function getLine(){
		return !isset($this->lines[$this->offset]) ? false : $this->lines[$this->offset++];
	}

	public function send(){
		$output = ob_get_contents();

		if($this->isFinal()){
			file_put_contents($this->path . "output/". $this->phase .".final", $output);
		}else{
			file_put_contents($this->path . "output/". $this->phase .".test", $output);
		}
	}

}