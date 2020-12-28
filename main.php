<?php

error_reporting(-1);

ini_set("display_errors", '1');
ini_set("display_startup_errors", '1');
ini_set("default_charset", "utf-8");
ini_set('assert.exception', '1');
ini_set("memory_limit", '2G');

$stdin = fopen("php://stdin", "r+");
echo "Please enter target path: \n";
$dir = trim(fgets($stdin));

echo "Please enter a file name: \n";
$filename = trim(fgets($stdin));

$types = [];

function getTypes(array $data, array $result = []) : array{
	foreach($data as $key => $value){
		$val = is_array($value) ? getTypes($value) : $value;

		if(is_string($key)){
			if(!isset($result[$key])){
				$result[$key] = $val;
			}else{
				$old = $result[$key];

				if(is_array($old) and is_array($val)){
					$result[$key] = getTypes($old, $val);
				}elseif(is_string($old) and is_string($val)){
					$others = explode("|", $old);

					if(!in_array($val, $others)){
						$result[$key] = $old . "|" . $val;
					}
				}
			}
		}else{
			$result[] = $val;
		}
	}

	return $result;
}

/** @var SplFileInfo $file */
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file){
	if($file->getExtension() === "json"){
		$json = json_decode(file_get_contents($file->getPathname()), true);
		if($json !== null){
			$types = getTypes($json, $types);
		}
	}
}

file_put_contents("{$filename}.json", json_encode($types, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nGenerated {$filename}.json";