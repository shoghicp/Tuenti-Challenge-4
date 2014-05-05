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
$submission = new Submission(14);

$scenarios = (int) $submission->getLine();

for($scenario = 0; $scenario < $scenarios; ++$scenario){
	$data = explode(",", $submission->getLine());
	$totalStations = (int) $data[0];
	$totalRoutes = (int) $data[1];
	$fuel = (int) $data[2];

	for($i = 0; $i < $totalStations; ++$i){
		$data = explode(" ", $submission->getLine());
		$data[1] = explode(",", $data[1]);
		new Station($data[0], $data[1][0], $data[1][1]);
		new Wagon($data[0], $data[2], $data[3]);
	}

	for($i = 0; $i < $totalRoutes; ++$i){
		$data = explode(" ", $submission->getLine());
		$start = array_shift($data);
		$route = new Route($start, $fuel);

		foreach($data as $pair){
			$pair = explode("-", $pair);
			$route->addNode($pair[0]);
			$route->addNode($pair[1]);
			$route->connectNodes($pair[0], $pair[1], Station::getStation($pair[0])->distance(Station::getStation($pair[1])));
		}
	}

	//get wagon paths
	foreach(Wagon::getAll() as $wagon){
		$wagon->generatePaths();
	}

	$hasEitherMoved = false;
	foreach(Route::getAll() as $route){
		$bestMoves = [];
		$bestScore = [];
		$moves = [];
		$hasMoved = false;
		foreach($route->getMoves() as $move => $distance){
			foreach(Station::getStation($move)->getWagons() as $wagon){
				foreach($wagon->getPaths() as $k => $distanceLeft){
					if(!$route->hasNode($k)){
						continue;
					}

					$id = $wagon->getID()."->".$k;
					$moves[$id] = [$wagon, $k];
					$bestMoves[$id] = $distanceLeft + $distance;
					$bestScore[$id] = $wagon->getScore();
				}
			}
		}
	}

	$maxScore = 0;
	getBestMoves(new ClonableObjectArray(Route::getAll()), new ClonableObjectArray(Wagon::getAll()), new ClonableObjectArray(Station::getAll()), $maxScore, 0);
	echo $maxScore . PHP_EOL;

	Wagon::clear();
	Route::clear();
	Station::clear();

}



/**
 * @param Route[]   $routes
 * @param Wagon[]   $wagons
 * @param Station[] $stations
 * @param int       $maxScore
 * @param int       $score
 *
 * @return int
 */
function getBestMoves($routes, $wagons, $stations, &$maxScore, $score){
	foreach($routes as $route){

		foreach($route->getMoves() as $move => $distance){
			if($route->getFuel() < $distance){
				continue;
			}
			$hasMoved = false;
			foreach($stations[$route->getCurrent()]->getWagons() as $wagon){
				$paths = $wagon->getPaths();
				if(isset($paths[$move])){
					$routesC = clone $routes;
					$stationsC = clone $stations;
					$wagonsC = clone $wagons;
					$routesC[$route->getID()]->move($move);
					$s = $wagonsC[$wagon->getID()]->move($move, $stationsC);
					$hasMoved = true;
					$s = getBestMoves($routesC, $wagonsC, $stationsC, $maxScore, $s + $score);
					if($s > $maxScore){
						$maxScore = $s;
					}
				}
			}
			if($hasMoved === false){
				$routesC = clone $routes;
				$routesC[$route->getID()]->move($move);
				$s = getBestMoves($routesC, $wagons, $stations, $maxScore, $score);
				if($s > $maxScore){
					$maxScore = $s;
				}
			}

		}
	}
	return $score;
}


class ClonableObjectArray extends ArrayObject{

	public function __construct(array $a){
		foreach($a as $key => $value){
			$this->offsetSet($key, $value);
		}
	}

	public function __clone(){
		foreach($this as $key => $value){
			$this->offsetSet($key, clone $value);
		}
	}
}

class Wagon{
	private static $counter = 0;
	/**
	 * @var Wagon[]
	 */
	protected static $wagons = [];

	protected $id;
	protected $current;
	protected $destination;
	protected $score;
	protected $traverseTable = [];

	public static function getAll(){
		return self::$wagons;
	}

	public static function getWagon($id){
		return self::$wagons[$id];
	}

	public static function clear(){
		static::$counter = 0;
		self::$wagons = [];
	}

	public function __construct($start, $destination, $score){
		$this->id = static::$counter++;
		$this->current = $start;
		$this->destination = $destination;
		$this->score = (int) $score;
		Station::getStation($this->current)->addWagon($this);
		self::$wagons[$this->getID()] = $this;
	}

	public function getID(){
		return $this->id;
	}

	public function getCurrent(){
		return $this->current;
	}

	public function getScore(){
		return $this->score;
	}

	public function getPaths(){
		return $this->traverseTable[$this->current];
	}

	public function generatePaths(){
		$q = new SplQueue();

		$paths = [];

		foreach(Route::getAll() as $route){
			if(!$route->hasNode($this->current)){
				continue;
			}

			$q->enqueue([$this->current, [], [], 0]);
		}

		while(!$q->isEmpty()){
			$step = $q->dequeue();
			$step[1][] = [$step[0], $step[3]];
			$step[2][$step[0]] = $step[3];
			if($step[0] === $this->destination){
				$step[1][-1] = array_sum($step[2]);
				$paths[] = $step[1];
				continue;
			}
			foreach(Route::getAll() as $route){
				if(!$route->hasNode($step[0])){
					continue;
				}

				$node = $route->getNode($step[0]);
				foreach($node as $k => $d){
					if(isset($step[2][$k])){ //Loop
						continue;
					}
					$q->enqueue([$k, $step[1], $step[2], $d]);
				}
			}

		}

		$traverseTable = [];

		foreach($paths as $path){
			$totalDistance = $path[-1];
			unset($path[-1]);
			$last = null;
			foreach($path as $order => $data){
				$distanceLeft = $totalDistance;
				$totalDistance -= $data[1];
				if($last !== null){
					if(isset($traverseTable[$last][$data[0]]) and $distanceLeft < $traverseTable[$last][$data[0]]){
						$traverseTable[$last][$data[0]] = $distanceLeft;
					}else{
						$traverseTable[$last][$data[0]] = $distanceLeft;
					}

					if(!isset($traverseTable[$last])){
						$traverseTable[$last] = [];
					}
				}
				$last = $data[0];
			}
		}

		$this->traverseTable = $traverseTable;
	}

	public function move($to, &$array = null){
		if($array === null){
			$array = Station::getAll();
		}
		$array[$this->current]->removeWagon($this);
		$this->current = $to;
		if($this->current === $this->destination){
			unset(self::$wagons[$this->getID()]);
			//var_dump($this->getScore());
			return $this->getScore();
		}else{
			$array[$this->current]->addWagon($this);
			return 0;
		}
	}
}

class Station{

	/**
	 * @var Station[]
	 */
	protected static $stations = [];


	public static function clear(){
		self::$stations = [];
	}

	protected $name;
	protected $x;
	protected $y;

	/**
	 * @var Wagon[]
	 */
	protected $wagons = [];

	public static function getAll(){
		return self::$stations;
	}

	public static function getStation($name){
		return self::$stations[$name];
	}

	public function __construct($name, $x, $y){
		$this->name = $name;
		$this->x = (int) $x;
		$this->y = (int) $y;
		self::$stations[$name] = $this;
	}

	public function getWagons(){
		return $this->wagons;
	}

	public function addWagon(Wagon $wagon){
		$this->wagons[$wagon->getID()] = $wagon;
	}

	public function removeWagon(Wagon $wagon){
		unset($this->wagons[$wagon->getID()]);
	}

	public function getX(){
		return $this->x;
	}

	public function getY(){
		return $this->y;
	}

	public function distance(Station $station){
		return sqrt(pow($this->getX() - $station->getX(), 2) + pow($this->getY() - $station->getY(), 2));
	}
}

// No checking at all!
class Route{

	private static $counter = 0;
	/**
	 * @var Route[]
	 */
	protected static $routes = [];

	protected $id;
	protected $nodes = [];
	protected $current;
	protected $fuel;

	public static function clear(){
		static::$counter = 0;
		self::$routes = [];
	}

	public static function getAll(){
		return self::$routes;
	}

	public static function getRoute($id){
		return self::$routes[$id];
	}

	public function __construct($start, $fuel){
		$this->current = $start;
		$this->fuel = (float) $fuel;
		$this->id = static::$counter++;
		self::$routes[$this->id] = $this;
	}

	public function getID(){
		return $this->id;
	}

	public function getCurrent(){
		return $this->current;
	}

	public function getFuel(){
		return $this->fuel;
	}

	public function addNode($name){
		if(!isset($this->nodes[$name])){
			$this->nodes[$name] = [];
		}
	}

	public function getMoves(){
		$moves = [];
		foreach($this->nodes[$this->current] as $k => $d){
			if($d <= $this->getFuel()){
				$moves[$k] = $d;
			}
		}
		return $moves;
	}

	public function connectNodes($node1, $node2, $distance){
		$this->nodes[$node1][$node2] = $distance;
		$this->nodes[$node2][$node1] = $distance;
	}

	public function hasNode($name){
		return isset($this->nodes[$name]);
	}

	public function getNode($name){
		return $this->nodes[$name];
	}

	public function move($to){
		if(isset($this->nodes[$this->current][$to])){
			$fuel = $this->nodes[$this->current][$to];
			if($fuel <= $this->getFuel()){
				$this->current = $to;
				$this->fuel -= $fuel;
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
}
