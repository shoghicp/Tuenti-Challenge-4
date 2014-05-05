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
$submission = new Submission(17);


class DirectoryEntry extends FileEntry{
	protected $disk;
	protected $folders = null;

	public function __construct(Split1GBFAT32DiskImage $disk, $filename, $extension, $startCluster){
		$this->filename = $filename;
		$this->ext = $extension;
		$this->cluster = $startCluster;
		$this->disk = $disk;
	}

	public function getFile($path){
		if($this->folders === null){
			$this->folders = $this->getContents();
		}
		$current = array_shift($path);
		if(isset($this->folders[$current])){
			if($this->folders[$current] instanceof DirectoryEntry){
				if(count($path) === 0){
					return false;
				}
				return $this->folders[$current]->getFile($path);
			}else{
				return $this->folders[$current]->getContents();
			}
		}else{
			return false;
		}
	}

	/**
	 * @return FileEntry[]|DirectoryEntry[]
	 */
	public function getContents(){
		return $this->disk->getDirectory($this->cluster);
	}

	public function getSize(){
		return 0;
	}
}

class FileEntry{
	protected $filename;
	protected $ext;
	protected $cluster;
	protected $size;
	/** @var \Split1GBFAT32DiskImage */
	protected $disk;

	public function __construct(Split1GBFAT32DiskImage $disk, $filename, $extension, $startCluster, $size){
		$this->filename = $filename;
		$this->ext = $extension;
		$this->cluster = $startCluster;
		$this->size = $size;
		$this->disk = $disk;
	}

	public function getContents(){
		$corrupted = null;

		$data = $this->disk->getClusterChain($this->cluster, $corrupted);
		if($corrupted or strlen($data) < $this->size){
			return false;
		}
		return substr($data, 0, $this->size);
	}

	public function getName(){
		return $this->filename;
	}

	public function getExtension(){
		return $this->ext;
	}

	public function getCluster(){
		return $this->cluster;
	}

	public function getSize(){
		return $this->size;
	}

	public function getFullName(){
		return $this->filename . ($this->ext !== "" ? "." . $this->ext : "");
	}
}



$number = (int) $submission->getLine();

$image = new Split1GBFAT32DiskImage(FILE_PATH . "TUENTIDISK.BIN");
$partitions = $image->getPartitions();
$partition = $partitions[0];
$boot = $image->setBootSector($partition[0]);

/** @var FileEntry[]|DirectoryEntry[] $rootDir */
$rootDir = $image->getRoot();

for($f = 0; $f < $number; ++$f){
	$path = explode("/", trim($submission->getLine(), "/"));
	$current = array_shift($path);
	if(isset($rootDir[$current])){

		if($rootDir[$current] instanceof DirectoryEntry){
			$content = $rootDir[$current]->getFile($path);
		}else{
			$content = $rootDir[$current]->getContents();
		}
		if($content === false){
			echo "CORRUPT" . PHP_EOL;
		}else{
			echo md5($content) . PHP_EOL;
		}

	}else{
		echo "CORRUPT".PHP_EOL;
	}
}

$submission->send();


/*
 * Overcomes the 2GB PHP limitation
 */
class Split1GBFAT32DiskImage{
	/** @var resource[] */
	private $images = [];
	private $fileSize = "0";
	private $number;
	private $current = 0;
	private $FATStart = 0;
	private $rootStart = 0;
	private $sectorsPerCluster = 0;
	private $rootDirectoryStart;

	public function __construct($path){
		$number = 1;
		while(file_exists($path . "." . str_pad($number, 3, "0", STR_PAD_LEFT))){
			$this->images[$number - 1] = fopen($path . "." . str_pad($number, 3, "0", STR_PAD_LEFT), "rb");
			$this->fileSize = bcadd($this->fileSize, fstat($this->images[$number - 1])["size"]);
			++$number;
		}
		$this->number = $number - 2;
		$this->current = 0;
	}

	public function getPartitions(){
		$this->seek(0);
		$sector = $this->read(512);
		$checksum = substr($sector, -2);
		if($checksum !== "\x55\xaa"){ //Not valid
			return [];
		}

		$offset = 0x1be;
		$partitions = [];
		for($i = 0; $i < 4; ++$i){
			$flags = ord($sector{$offset++});
			$active = false;
			if(($flags & 0x80) > 0){
				$active = true;
			}
			//$data = unpack("V", "\x00".substr($sector, $offset, 3))[1];
			$offset += 3;
			/*$head = $data >> 16;
			$sector = ($data & 0b0011111100000000) >> 8;
			$cylinder = (($data & 0b1100000000000000) >> 14) + $data & 0xff;
			$firstLBA = ((($cylinder * )))*/

			$type = ord($sector{$offset++});

			$offset += 3;

			$firstLBA = unpack("V", substr($sector, $offset, 4))[1];
			$offset += 4;
			$sectorNumber = unpack("V", substr($sector, $offset, 4))[1];
			$offset += 4;
			$partitions[] = [$firstLBA, $sectorNumber, $type, $active];
		}
		return $partitions;
	}

	public function nextFAT($n){
		if(!$this->seek($this->FATStart * 512 + $n * 4)){
			return -1;
		}
		$entry = unpack("V", $raw = $this->read(4))[1];
		$entry &= 0x0FFFFFFF;

		if($entry === $n){
			return -1; //Recursion! CORRUPT
		}elseif($entry === 0){ //free
			return 0;
		}elseif($entry === 1){
			return 0;
		}elseif($entry < 0x0fffffef){
			return $entry;
		}elseif($entry <= 0x0ffffff7){
			return -1; //CORRUPT!
		}elseif($entry >= 0x0ffffff8){
			return 0;
		}else{
			return 0; //??
		}
	}

	public function getRoot(){
		return $this->getDirectory($this->rootDirectoryStart);
	}

	public function getDirectory($clusterStart){
		$files = [];
		$clusters = $this->getClusterChain($clusterStart);
		$len = strlen($clusters);
		for($offset = 0; $offset < $len; $offset += 32){
			if(($file = $this->getFile(substr($clusters, $offset, 32))) instanceof FileEntry){
				$files[$file->getFullName()] = $file;
			}else{
				break;
			}
		}

		return $files;
	}

	public function getFile($entry){
		if($entry{0} === "\x00"){
			return null;
		}

		$filename = rtrim(substr($entry, 0, 8));
		$extension = rtrim(substr($entry, 8, 3));
		$attr = ord($entry{0x0b});
		if($attr === 0x0f or ($attr & 0b11000000) > 0){
			return null;
		}
		$highCluster = unpack("v", substr($entry, 0x14, 2))[1] & 0x0fff;
		$cluster = ($highCluster << 16) | unpack("v", substr($entry, 0x1a, 2))[1];
		$size = unpack("V", substr($entry, 0x1c, 4))[1];

		if(($attr & 0x10) > 0){ //Directory
			$file = new DirectoryEntry($this, $filename, $extension, $cluster);
		}else{
			$file = new FileEntry($this, $filename, $extension, $cluster, $size);
		}

		return $file;
	}

	public function setBootSector($n){
		$sector = $this->getSector($n);
		$data = [];
		$data[ 0] = $identifier = substr($sector, 0x3, 8);
		$data[ 1] = $bytesPerSector = unpack("v", substr($sector, 0x0b, 2))[1];
		$data[ 2] = $sectorsPerCluster = ord($sector{0x0d});
		$data[ 3] = $reservedSectors = unpack("v", substr($sector, 0x0e, 2))[1];
		$data[ 4] = $numberOfFATs = ord($sector{0x10});
		$data[ 5] = $rootEntries = unpack("v", substr($sector, 0x11, 2))[1];
		$data[ 6] = $numberOfSectors = unpack("v", substr($sector, 0x13, 2))[1];
		$data[ 7] = $mediaDescriptor = ord($sector{0x15});
		$data[ 8] = $sectorsPerFAT = unpack("v", substr($sector, 0x16, 2))[1];
		$data[ 9] = $sectorsPerHead = unpack("v", substr($sector, 0x18, 2))[1];
		$data[10] = $headsPerCylinder = unpack("v", substr($sector, 0x1a, 2))[1];
		$data[11] = $hiddenSectors = unpack("V", substr($sector, 0x1c, 4))[1];
		$data[12] = $bigNumberOfSectors = unpack("V", substr($sector, 0x20, 4))[1];
		$data[13] = $bigSectorsPerFAT = unpack("V", substr($sector, 0x24, 4))[1];
		$data[14] = $extFlags = unpack("v", substr($sector, 0x28, 2))[1];
		$data[15] = $FSVersion = unpack("v", substr($sector, 0x2a, 2))[1];
		$data[16] = $rootDirectoryStart = unpack("V", substr($sector, 0x2c, 4))[1];
		$data[17] = $FSInfoSector = unpack("v", substr($sector, 0x30, 2))[1];
		$data[18] = $backupBootSector = unpack("v", substr($sector, 0x32, 2))[1];
		$this->sectorsPerCluster = $sectorsPerCluster;
		$this->FATStart = $n + $reservedSectors;
		$this->rootStart = $n + $reservedSectors + $numberOfFATs * $bigSectorsPerFAT;
		$this->rootDirectoryStart = $rootDirectoryStart;
		return $data;
	}

	public function seek($offset){
		$n = bcdiv($offset, 1073741824, 0);
		if($n >= count($this->images)){
			return false;
		}
		$seek = bcmod($offset, 1073741824);
		if($n >= $this->number){
			return false;
		}
		$this->current = $n;
		fseek($this->images[$n], $seek);
		return true;
	}

	public function getClusterChain($start, &$corrupted = null){
		$clusters = $this->getCluster($start);
		$current = $start;
		$visited[$start] = true;
		while(($next = $this->nextFAT($current)) > 0){
			if(isset($visited[$next])){
				$corrupted = true;
				return $clusters;
			}
			$clusters .= $this->getCluster($next);
			$visited[$next] = true;
			$current = $next;
		}

		if($next === -1){
			$corrupted = true;
		}else{
			$corrupted = false;
		}
		return $clusters;
	}

	public function getCluster($n){
		$n -= 2;
		return $this->getSectors($this->rootStart + $n * $this->sectorsPerCluster, $this->sectorsPerCluster);
	}

	public function getSector($n){
		if(!$this->seek(bcmul($n, 512))){
			return false;
		}
		return $this->read(512);
	}

	public function getSectors($n, $c){
		if(!$this->seek(bcmul($n, 512))){
			return false;
		}
		return $this->read(bcmul($c, 512));
	}

	public function read($count){
		$toRead = $count;
		$bytes = "";
		while($toRead > 0 and !feof($this->images[$this->current])){
			$bytes .= fread($this->images[$this->current], $toRead);
			$toRead = $count - strlen($bytes);
		}

		if($toRead > 0){
			if($this->current === $this->number){ //Final
				return $bytes;
			}
			++$this->current;
			$bytes .= $this->read($toRead);
		}
		return $bytes;
	}

}