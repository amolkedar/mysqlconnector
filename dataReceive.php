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

$tableExists =  $dbObject->table_exists($tableName);
print_r($tableExists);
echo "\n";;
//echo "printing table exists" . json_encode($tableExists);

if (empty($tableExists)) {
	echo "table does not exists" ;
	$dbObject->create($tableName, $incomingData);	
} else {
	echo "Table exists";
	$dbObject->insert($tableName, $incomingData);	
}




?>