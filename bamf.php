<?php

/*
 * TODO: Describe this class
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
 * Renders templates and doesn't afraid of anything
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

	public function select($table, $find, $args) {
		$q = $this->select_query_gen($table, $find, $args);
		$stmt = $this->stmt_prepare($q);
		$res = $this->stmt_exec($stmt, $this->stmt_types($args), $args);
		print_r($res->fetch_assoc());
		echo "<br />";
	}

	/*
	 * Generates paramaterized query for SELECT
	 *
	 * @param  string      $table  The table to search in
	 * @param  str || arr  $find   The column(s) you want to search in
	 * @param  hash        $args
	 */
	private function select_query_gen($table, $find, $args) {
		$q = 'SELECT ';

		/*
		 * Figure out what to select for. If it's an array, the user is
		 * lookind for multiple columns. If it's a string, the user is
		 * looking for the column specified in the string.
		 */
		$find_type = gettype($find);
		if($find_type == 'array') {
			$c = count($find);
			for($i = 0; $i < $c; $i++) {
				$q .= $find[$i];
				if($i < $c - 1) {
					$q .= ', ';
				} else {
					$q .= ' ';
				}
			}
		} elseif($find_type == 'string') {
			$q .= "$find ";
		}

		$q .= "FROM $table WHERE ";

		$c = count($args);
		$i = 0;
		foreach($args as $k => $v) {
			$q .= "$k = (?) ";
			if($i < $c - 1) {
				$q .= 'AND ';
			}
			$i++;
		}
		echo $q . '<br />';
		return $q;
	}

	/*
	 * @param   string  Query as a prepared statement
	 * @return  mysqli_stmt
	 */
	private function stmt_prepare($q) {
		if($stmt = $this->conn->prepare($q)) {
			return $stmt;
		} else {
			throw new Exception($this->errnomsg() . 'Failed preparing query: '
			   . $this->conn->error);
		}
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
	private function stmt_types($arg) {
		$t = '';
		foreach($arg as $val) {
			switch(gettype($val)) {
			case 'integer':
				$t .= 'i';
				break;
			case 'double':
				$t .= 'd';
				break;
			case 'string':
				$t .= 's';
				break;
			}
		}
		return $t;
	}

	/*
	 * @param   mysqli_stmt    $stmt   MySQLi statement to execute
	 * @param   string         $types  Type string to be bound
	 * @param   array          $vals   Array of values to be bound
	 * @return  mysqli_result
	 */
	private function stmt_exec($stmt, $types, $vals) {
		array_unshift($vals, $types); //prepend $types with $value

		$ref_vals = array();
		foreach($vals as $k => $v) {
			$ref_vals[$k] = &$vals[$k];
		}

		if(call_user_func_array(array($stmt, 'bind_param'), $ref_vals)) {
			if($stmt->execute()) {
				return $stmt->get_result();
			} else {
				throw new Exception($this->errnomsg() . 
					'Failed executing prepared stmt');
			}
		} else {
			throw new Exception($this->errnomsg() . 
				'Failed executing prepared stmt');
		}	
	}

	private function errnomsg() {
		return '[ERRNO: '.$this->conn->errno.'] ';
	}
}

?>

