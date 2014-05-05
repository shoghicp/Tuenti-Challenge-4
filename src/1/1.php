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
$submission = new Submission(1);

define("MALE", 0);
define("FEMALE", 1);

if(!file_exists(FILE_PATH . "students.db")){
	$db = new SQLite3(FILE_PATH . "students.db");
	$db->query("CREATE TABLE students (name TEXT PRIMARY KEY, gender INTEGER, age INTEGER, studies TEXT, year INTEGER );");
	$fp = fopen(FILE_PATH . "students.txt", "r");
	while(($line = fgets($fp)) !== false){
		if(trim($line) !== ""){
			$data = explode(",", $line);
			$db->query("INSERT INTO students (name, gender, age, studies, year) VALUES (
				'".$data[0]."',
				".(strtolower($data[1]) === "m" ? MALE : FEMALE).",
				".intval($data[2]).",
				'".$data[3]."',
				".intval($data[4])."
			);"); //Unvalidated data D:
		}
	}
	fclose($fp);
}else{
	$db = new SQLite3(FILE_PATH . "students.db");
}

$n = (int) $submission->getLine();
for($i = 1; $i <= $n; ++$i){
	$search = explode(",", $submission->getLine());
	$query = $db->query("SELECT name FROM students WHERE
		gender = ".(strtolower($search[0]) === "m" ? MALE : FEMALE)."
		AND age = ".intval($search[1])."
		AND studies = '".$search[2]."'
		AND year = ".intval($search[3])."
	ORDER BY name ASC;");
	if($query instanceof SQLite3Result){
		$result = "";
		while($row = $query->fetchArray(SQLITE3_ASSOC)){
			$result .= $row["name"].",";
		}
		if($result === ""){
			$result = "NONE ";
		}
		echo "Case #$i: ".substr($result, 0, -1) . PHP_EOL;
	}else{
		echo "Case #$i: NONE\n";
	}
}

$submission->send();