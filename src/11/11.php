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
$submission = new Submission(11);

ini_set("memory_limit", -1);
@mkdir(FILE_PATH."keycache/");


$perms = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

$keyPart = array();
$len = strlen($perms);
$limit = $len * $len * $len;
for($i = 0; $i < $limit; ++$i){
	$keyPart[$i] = $perms[$i % $len].$perms[($i / $len) % $len].$perms[($i / ($len * $len)) % $len];
}

//shuffle($keyPart);

$decrypted = array();

while(($line = $submission->getLine()) !== false){
	$input = array_map(function($input){return explode(",", trim($input));}, explode(";", $line));
	$eventNumber = (int) array_shift($input)[0];
	$partialKeys = new SplPriorityQueue();
	$userData = array();
	$todo = array();
	foreach($input as $data){
		$lastTime = (int) file_get_contents(FILE_PATH."last_times/".getPath($data[0]).".timestamp");
		if(!isset($decrypted[$data[0]])){
			if(file_exists(FILE_PATH."keycache/".getPath($data[0]).".key")){
				//The extra bytes will get removed automatically :)
				$partialKeys->insert(array($data[0], file_get_contents(FILE_PATH."keycache/".getPath($data[0]).".key"), $lastTime), $lastTime);
			}else{
				$partialKeys->insert(array($data[0], $data[1], $lastTime), $lastTime);
			}
			$userData[$data[0]] = file_get_contents(FILE_PATH."encrypted/".getPath($data[0]).".feed");
		}
		$todo[] = $data[1];
	}

	$list = new LimitedMinHeap($eventNumber);
	$events = array();

	bfDecrypt($userData, $partialKeys, $decrypted, $events, $list);


	$out = array();
	while(!$list->isEmpty()){
		$i = $list->extract();
		$out[$i] = $events[$i];
	}
	rsort($out);

	echo implode(" ", $out) . PHP_EOL;
}
$submission->send();

class LimitedMinHeap extends SplMinHeap{

	protected $limit;

	public function __construct($limit){
		$this->limit = (int) $limit;
	}

	public function isFull(){
		return $this->count() >= $this->limit;
	}

	public function insert($value){
		parent::insert($value);
		if($this->count() > $this->limit){
			$this->extract();
		}
	}

}

function getPath($string){
	return substr((string) $string, -2)."/".$string;
}

function bfDecrypt(array $data, SplPriorityQueue $partialKey, &$decrypted, &$events, LimitedMinHeap $list){
	global $keyPart;
	$IV = str_repeat("\x00", 16);
	$f = mcrypt_module_open(MCRYPT_RIJNDAEL_128, "", MCRYPT_MODE_ECB, "");

	while(!$partialKey->isEmpty()){
		$d = $partialKey->extract();
		$i = $d[0];
		$key = $d[1];
		if($list->isFull() and $d[2] < $list->top()){
			break;
		}
		foreach($keyPart as $k){
			mcrypt_generic_init($f, substr($key . $k, 0, 32), $IV);
			$dec = mdecrypt_generic($f, $data[$i]);

			if(substr($dec, 0, strlen((string) $i)) === (string) $i){
				$padding = ord($dec{strlen($dec) - 1});
				$dec = substr($dec, 0, -$padding);
				$decrypted[$i] = $dec;
				unset($data[$i]);
				@mkdir(FILE_PATH . "keycache/".substr((string) $i, -2)."/");
				file_put_contents(FILE_PATH."keycache/".getPath($i).".key", substr($key . $k, 0, 32));
				$d = array_map(function($e){
					return explode(" ", trim($e));
				}, explode("\n", $dec));

				foreach($d as $event){
					if(count($event) !== 3 or ($list->isFull() and $event[1] < $list->top())){
						break;
					}
					$list->insert($event[1]);
					$events[$event[1]] = $event[2];
				}
				break;
			}
		}
	}
}