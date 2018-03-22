<?php 

Class Db_object {

	public static function table_exists($db_table) {
		
		return static::find_by_query("show tables like '" .$db_table. "' " );
	}

	public static function find_all($db_table) {
		
		return static::find_by_query("select * from " . $db_table . " " );
	}

	public function find_if_row_exists($db_table, $db_table_fields) {
		global $database;
		
		$properties = $this->clean_properties($db_table_fields);
		$columns = array_values($properties);
		$the_object_array = array();

		$sql = "select * from " . $db_table . " where id = ". $columns[0] ." " ;
		$result_set = $database->query($sql);
		while($row = mysqli_fetch_array($result_set)) {
		
			$the_object_array[] = $row;
		}		

		return $the_object_array;		
	}

	public function find_if_column_exists($db_table, $db_table_fields) {
		global $database;
		
		$properties = $this->clean_properties($db_table_fields);
		$columns = array_keys($properties);
		$the_object_array = array();

		$result_set = $database->query("select column_name from information_schema.columns where table_name = '" .$db_table. "'" );
		while($row = mysqli_fetch_array($result_set)) {			
			$the_object_array[] = $row['column_name'];
		}

		// print_r($columns);			
		// print_r($the_object_array);			
		$result = array_diff($columns, $the_object_array);
		print_r($result);			

		return $result;		
	}

	public static function find_by_query($sql) {
		global $database;
		$result_set = $database->query($sql);
		$the_object_array = array();

		while($row = mysqli_fetch_array($result_set)) {
			//$the_object_array[] = static::instantiation($row);
			$the_object_array[] = $row;
		}
		
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

	private function has_the_attribute($the_attribute) {

		$object_properties = get_object_vars($this);
		return array_key_exists($the_attribute, $object_properties);

	}

	protected function properties($db_table_fields) {

		$properties = array();
		foreach ($db_table_fields as $db_field) {
			if(property_exists($this, $db_field)){
				$properties[$db_field] = $this->$db_field;
			}
		}
		
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


	public function save($db_table, $db_table_fields) {

		$columns_to_be_added = static::find_if_column_exists($db_table, $db_table_fields);

		if (count($columns_to_be_added) > 0) {
			$this->alter($db_table, $columns_to_be_added);
	  	    // Check the condition to decide if its update or insert
			if (count($this->find_if_row_exists($db_table, $db_table_fields)) > 0) {
				echo " alter and then update";
				return $this->update($db_table, $db_table_fields);								
			} else {
				echo " alter and then insert";
				return $this->insert($db_table, $db_table_fields);			
			}		
		} else {
			if (count($this->find_if_row_exists($db_table, $db_table_fields)) > 0) {
				echo " in update";
				return $this->update($db_table, $db_table_fields);								
			} else {
				echo " in insert";
				return $this->insert($db_table, $db_table_fields);			
			}
		}
	}


	public function insert($db_table, $db_table_fields) {
		global $database;
		
		$properties = $this->clean_properties($db_table_fields);

		$sql = "INSERT INTO " .$db_table . "(" . implode(',', array_keys($properties)) . ")";
		$sql .= "VALUES ('";
		$sql .= implode("','", array_values($properties));
		$sql .= "')"  ;  

		// echo 'SQL is ' .$sql .'\n';
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
		for ($i=0; $i < 13; $i++) { 
			unset($columns[$i]);
		}		

		$sql = "CREATE TABLE " .$db_table . " (
		ID INT NOT NULL,
	    PARENT_RECORD_ID int(11) DEFAULT NULL,
	    PARENT_PAGE_ID int(11) DEFAULT NULL,
  		PARENT_ELEMENT_ID int(11) DEFAULT NULL,
		CREATED_DATE 		datetime DEFAULT NULL,
		CREATED_BY 			varchar(200) DEFAULT NULL,
		CREATED_LOCATION 	varchar(200) DEFAULT NULL,
		CREATED_DEVICE_ID 	varchar(100) DEFAULT NULL,
		MODIFIED_DATE 		datetime DEFAULT NULL,
		MODIFIED_BY 		varchar(200) DEFAULT NULL,
		MODIFIED_LOCATION 	varchar(200) DEFAULT NULL,
		MODIFIED_DEVICE_ID 	varchar(100) DEFAULT NULL,
		SERVER_MODIFIED_DATE datetime DEFAULT NULL,
		";
		foreach ($columns as $key => $value) {

			$sql .= $value . " text,";
		}			
		$sql = substr_replace($sql,"",-1);
		$sql .= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";  
		//echo 'CREATE SQL is ' .$sql . "\n";
		if ($database->query($sql)) {
			$this->id = $database->insert_id();			
			$this->insert($db_table, $db_table_fields);
			return true;

		} else  {
			return false;
		}
			
	} //create method

	public function alter($db_table, $columns_to_be_added) {
		global $database;
		$properties = $this->clean_properties($columns_to_be_added);		
		$sql = "ALTER TABLE ". $db_table;
		foreach ($columns_to_be_added as $key => $value) {
			$sql .=  " ADD " . $value . " text,";
		}
		$sql = substr_replace($sql,"",-1);
		$sql .= "";
		echo 'ALTER SQL is ' .$sql . "\n";
		if ($database->query($sql)) {								
			return true;
		} else  {
			return false;
		}

	} // alter method



	public function update($db_table, $db_table_fields) {
		global $database;
		$properties = $this->clean_properties($db_table_fields);
		$property_pairs = array();
		$columns = array_values($properties);

		foreach ($properties as $key => $value) {
			$property_pairs[] = "{$key}='{$value}'";
		}

		$sql = "UPDATE " .$db_table . " SET ";
		$sql .= implode(", ", $property_pairs);
		$sql .= " WHERE id = " . $database->escape_string($columns[0]);
		
		//echo 'UPDATE SQL is ' .$sql . "\n";
		$database->query($sql);

		return (mysqli_affected_rows($database->connection) == 1) ? true : false; 
	
	} // Update method



}

 ?>