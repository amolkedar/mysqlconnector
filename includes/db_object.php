<?php 

Class Db_object {

	public static function table_exists($db_table) {
		

		return static::find_by_query("show tables like '" .$db_table. "' " );
	}

	public static function find_all($db_table) {
		
		return static::find_by_query("select * from " . $db_table . " " );
	}

	public static function find_by_query($sql) {
		global $database;
		$result_set = $database->query($sql);
		$the_object_array = array();

		while($row = mysqli_fetch_array($result_set)) {
			//$the_object_array[] = static::instantiation($row);
			$the_object_array[] = $row;
		}
		
		//echo " find_by_query are " . print_r($the_object_array) . "\n";
		return $the_object_array;
	}

	public static function instantiation($the_record) {

		$calling_class = get_called_class();
		$the_object = new $calling_class;
		foreach ($the_record as $the_attribute => $value) {
			if($the_object->has_the_attribute($the_attribute)) {
				$the_object->$the_attribute = $value;
			}
		}

		return $the_object;

	}

	protected function properties($db_table_fields) {
		echo "i am in properties" .'\n';
		$properties = array();
		foreach ($db_table_fields as $db_field) {
			if(property_exists($this, $db_field)){
				$properties[$db_field] = $this->$db_field;
			}
		}
		echo " properties are " . print_r($properties) . "\n";
		return $properties;

	}

	protected function clean_properties($db_table_fields) {
		global $database;
		
		$clean_properties = array();
		foreach ($db_table_fields as $key => $value) {
			$clean_properties[$key] = $database->escape_string($value);
		}
		
		return $clean_properties;
	}


	public function save($db_table) {
		return isset($this->id) ? $this->update() : $this->insert();

	}


	public function insert($db_table, $db_table_fields) {
		global $database;
		
		$properties = $this->clean_properties($db_table_fields);


		$sql = "INSERT INTO " .$db_table . "(" . implode(',', array_keys($properties)) . ")";
		$sql .= "VALUES ('";
		$sql .= implode("','", array_values($properties));
		$sql .= "')"  ;  

		echo 'SQL is ' .$sql .'\n';
		if ($database->query($sql)) {
			$this->id = $database->insert_id();
			
			return true;

		} else  {
			return false;
		}
			
	} //insert method

	public function create($db_table, $db_table_fields) {
		global $database;
		
		$properties = $this->clean_properties($db_table_fields);
		$columns = array_keys($properties);
		unset($columns[0]);
		print_r($columns);	

		$sql = "CREATE TABLE " .$db_table . " (
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
		foreach ($columns as $key => $value) {

			$sql .= $value . " text,";
		}			
		$sql = substr_replace($sql,"",-1);
		$sql .= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";  
		echo 'SQL is ' .$sql . "\n";
		// if ($database->query($sql)) {
		// 	$this->id = $database->insert_id();			
		// 	return true;

		// } else  {
		// 	return false;
		// }
			
	} //create method

	public function update() {
		global $database;
		$properties = $this->clean_properties();
		$property_pairs = array();
		foreach ($properties as $key => $value) {
			$property_pairs[] = "{$key}='{$value}'";
		}

		$sql = "UPDATE " .static::$db_table . " SET ";
		$sql .= implode(", ", $property_pairs);
		$sql .= " WHERE id = " . $database->escape_string($this->id);

		$database->query($sql);

		return (mysqli_affected_rows($database->connection) == 1) ? true : false; 
	
	} // Update method



}

 ?>