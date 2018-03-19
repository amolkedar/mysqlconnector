<?php 

require_once("config.php");

class Database {

	public $connections;

	function __construct(){

		$this->open_db_connection();
	}

	public function open_db_connection(){

		$this->connection  = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		if($this->connection->connect_errno) {
			die('database connection failed badly'. $this->connection->connect_errno);
		}
	}

	public function query($sql) {

		$result = $this->connection->query($sql);
		$this->confirm_query($result);
		return $result;

	}

	public function confirm_query($result) {

		if(!$result) {
			die('Query Failed'. $this->connection->errno);
		}
	}

	public function escape_string($string) {

		$escape_string = $this->connection->real_escape_string($string);
		return $escape_string;
	}

	public function insert_id() {

		return $this->connection->insert_id;		
	}

}

$database = new Database();

?>