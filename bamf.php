<?php

/*
 * TODO: Describe this class?
 */
class Router {

	/*
	 * @var hash
	 *
	 * The hash index is the path, the value is the callback.
	 */
	private $uri_paths;


	public function __construct() {
		$this->uri_paths = [];
	}

	/*
	 * Adds path and callback
	 *
	 * First this sanitizes the string, and then appends to
	 * $this->uri_paths with the path as a key.
	 *
	 * @param   $path    string    URI path to add
	 * @param   $action  function  Function to execute on $path
	 * @return  void
	 */
	public function add($path, $action) {
		$path = self::fix_path($path);
		$this->uri_paths[$path] = $action;
	}

	/*
	 * This is where the magic happens
	 *
	 * @return void
	 */
	public function route() {
		$path = $_SERVER['REQUEST_URI'];
		$path = self::fix_path($path);

		if(array_key_exists($path, $this->uri_paths)) {
			$action = $this->uri_paths[$path];
			call_user_func($action);
		} else {
			//TODO: 404
		}
	}

	/*
	 * Removes beginning and trailing slashes
	 *
	 * @return String
	 */
	private static function fix_path($path) {
		//TODO: do shit to $path
		return $path;
	}
}

/*
 * TODO: Document this
 */
class Template {
	private $file;
	private $args;

	public function __construct($f, $a) {
		$this->file = $f;
		$this->args = $a;
	}

	public function __get($key) {
		if(array_key_exists($key, $this->args)) {
			return $this->args[$key];
		} else {
			return undefined;
		}
	}

	public function render() {
		if(file_exists($this->file)) {
			include $this->file;
		}
	}	
}

class Database {

	private $conn;

	public function __construct($host, $user, $pw, $db) {
		$this->conn = new mysqli($host, $user, $pw, $db);
		$conn_err = $this->conn->connect_errno;
		if($conn_err) {
			throw new Exception($this->errnomsg . 'MySQL connection failed');
		}
	}

	/*
	 * Find single row
	 *
	 * Searches in $table for a single row.
	 * Where the keys in $arg correspond to columns in $table and the values
	 * at the keys are the values in the table.
	 *
	 * @param   string  $table  Table to search in
	 * @param   hash    $arg    Hash keys are table columns
	 * @return  hash            Keys are columns, values correspond to table
	 *
	 */
	public function find_by($table, $arg) {
		$query = "SELECT * FROM $table WHERE ";
		
		$query = $query . $this->find_prep_stmt($arg);
		$types = $this->gen_find_by_types($arg);

		try {
			$stmt = $this->stmt_prepare($query);
			$this->stmt_exec($stmt, $types, array('asd', 'qwe', 1, 3.14159));
		} catch(Exception $e) {
			echo $this->conn->error . "<br />";
		}
		

		echo $query;
		echo '<br />';
	}

	/*
	 * Find all rows
	 *
	 * This works exactly like Database::find_by()
	 *
	 * @param   string  $table  Table to search in
	 * @param   hash    $arg    Hash keys are table columns
	 * @return  hash            Keys are columns, values correspond to table
	 */
	public function find_all_by($table, $arg) {
		$query = "SELECT * FROM $table WHERE ";
		
		$query = $query . $this->find_prep_stmt($arg);

		echo $query;
		echo '<br />';
	}

	/*
	 * Make prepared statement template for find_by() and find_all_by()
	 *
	 *
	 * This generates a string in the form of:
	 *  -> key1 = (?) AND key2 = (?) 
	 * Where the keys are those of a hash passed to the function. This is
	 * used to generate the template for a prepared statement.
	 *
	 * @param   hash    $arg  The keys in this hash are used
	 * @return  string        Prepared statement template bit
	 */
	private function find_prep_stmt($arg) {
		$count = count($arg) - 1;
		$i = 0;

		$query = '';
		foreach($arg as $key => $val) {
			$query = $query . "$key = (?) ";
			if($i < $count) {
				$query = $query . 'AND ';
			}
			$i++;
		}

		return $query;
	}

	/*
	 * Make prepared statement type listing string
	 *
	 * This generates a string to be used in mysqli_stmt:bind_param(), it
	 * contains the types of variables which are to be bound by the prepared
	 * statement. 
	 *
	 * @param   hash    $arg  Finds the types of the values in this
	 * @return  string        Type string for bind_param() 
	 */
	private function gen_find_by_types($arg) {
		$t = '';
		foreach($arg as $val) {
			switch(gettype($val)) {
			case 'integer':
				$t = $t . 'i';
				break;
			case 'double':
				$t = $t . 'd';
				break;
			case 'string':
				$t = $t . 's';
				break;
			}
		}
		return $t;
	}

	/*
	 * @param   string  Query as a prepared statement
	 * @return  mysqli_stmt
	 */
	private function stmt_prepare($q) {
		if($stmt = $this->conn->prepare($q)) {
			return $stmt;
		} else {
			throw new Exception($this->errnomsg() . 'Failed preparing query');
		}
	}

	/*
	 * @param   mysqli_stmt  $stmt   MySQLi statement to execute
	 * @param   string       $types  Type string to be bound
	 * @param   array        $vals   Array of values to be bound
	 * @return  bool
	 */
	private function stmt_exec($stmt, $types, $vals) {
		array_unshift($vals, $types);
		if(!call_user_func_array(array($stmt, 'bind_param'), $vals)) {
			throw new Exception($this->errnomsg() . 
				'Failed executing prepared stmt');
		}
	}

	private function errnomsg() {
		return '[ERRNO: '.$this->conn->errno.'] ';
	}

}

/*
 * TODO: Document this
 */
class Util {

	public static function sanitize_input_str($str) {
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return htmlentities(mysql_real_escape_string($str));
	}

}

?>
