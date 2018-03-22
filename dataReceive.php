<?php
include("includes/init.php");

//parse the incoming URL for table name 
$tableName = $_GET['tablename'];

//get the inputs of the incoming JSON into a array
$data = json_decode(file_get_contents('php://input'), true);
$incomingData = $data["0"]["record"];

echo $tableName. "\n";
//print_r($incomingData). "\n";
$dataColumns = array_keys($incomingData); 
$dbObject = new DB_Object();


$finalColumns = array();
$sendToFlatten = array();
$flattenedColumns = array();

foreach ($incomingData as $key => $value) {
		
		if(is_string($value)) {
//			echo "key is String ". $key ."\n";	
			$finalColumns[$key] = $value;
		} else {
			echo "Count of Key is Array ". sizeof($value) ."\n";	
			//$sendToFlatten[$key] = $value;			
			for ($i=0; $i < sizeof($value); $i++) { 
				$sendToFlatten[] = flatten($key, $value[$i]);					
			}						
		}
		
}

foreach ($sendToFlatten as $key => $value) {
	$flattenedColumns[] = array_merge($finalColumns, $value);
}

for ($i=0; $i < sizeof($flattenedColumns); $i++) { 
	//print_r($flattenedColumns[$i]);
	$tableExists =  $dbObject->table_exists($tableName);
	if (count($tableExists) > 0) {
		echo "Table exists";
		echo "\n";
		$dbObject->save($tableName, $flattenedColumns[$i]);	

	} else {
		echo "Table does not exists";
		echo "\n";
		$dbObject->create($tableName, $flattenedColumns[$i]);		

	}
}


function flatten($columnName, array $array) {
	$branch = array();
	foreach ($array as $key => $value) {
		if ($key == 'record') {
				foreach ($value as $key => $value) {
					$branch[$columnName."_".$key] =  $value;
				}
		}
	}
	return $branch;
}



?>