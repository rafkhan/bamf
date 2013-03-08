<?php

/*
 * TODO: 404
 */
class Router {
	
	private $path_tree;

	public function __construct() {
		$this->path_tree = new RNode();
	}

	/*
	 * Add function to the path tree.
	 *
	 * @param string $path
	 * @param function $func
	 */
	public function add($path, $func) {
		$path_arr = $this->parse_uri($path);
		$this->add_path($this->path_tree, $path_arr, $func);
		print_r($this->path_tree);
		echo '<br /><br />';
	}

	/*
	 * Execute the function on the path the user navigated too.
	 */
	public function route() {
		$path = $_SERVER['REQUEST_URI'];
		$path_arr = $this->parse_uri($path);
		$this->route_path($this->path_tree, $path_arr);
	}

	/*
	 * Recursively traverses the path tree and executes the function at the
	 * endpoint. 404s if no action at endpoint
	 *
	 * @param RNode &$parent  Parent node to search in
	 * @param Array $path  Array of path segments (Strings)
	 */
	private function route_path(&$parent, $path) {
		$path_seg = array_shift($path);
		if($path_seg == NULL) {
			$func = $parent->get_action();
			if($func != NULL) {
				call_user_func($func);
			} else {
				echo "<strong><h1>404 ERROR</h1></strong><br />";
			}
		} else {
			if($child = $parent->get_child($path_seg)) {
				$this->route_path($child, $path);
			} else {
				echo "<strong><h1>404 ERROR</h1></strong><br />";
			}
		}
	}

	/*
	 * Recursively traverses the tree, creates non-existant nodes, and assigns
	 * function to the endpoint.
	 *
	 * @param RNode &$parent  The parent node to append action or other node to.
	 * @param Array $path  Array of path segment (strings)
	 */
	private function add_path(&$parent, $path, $func) {
		$path_seg = array_shift($path);
		if($path_seg == NULL) {
			$parent->set_action($func);
		} else {
			if(!($child = $parent->get_child($path_seg))) {
				$child = $parent->add_child($path_seg);
			}
			$this->add_path($child, $path, $func);
		}
	}

	/*
	 * Splits the URI at '/', removes empty elements, and looks for
	 * varargs in the URI.
	 *
	 * @param string $path
	 * @return Array
	 */
	private function parse_uri($path) {
		$tokens = explode('/', $path);

		foreach($tokens as $k => $v) {
			if($v == '') {
				unset($tokens[$k]);
			} elseif($v == '@') {
				//TODO: set key for variable args
			}
		}
		array_values($tokens);

		return $tokens;
	}
}

class RNode {

	private $action;
	private $children = NULL;

	public function get_child($child_name) {
		if($this->children != NULL) {
			if(array_key_exists($child_name, $this->children)) {
				return $this->children[$child_name];
			} else {
				return FALSE;
			}
		}
	}

	public function add_child($name) {
		$this->children[$name] = new RNode();
		return $this->children[$name];
	}

	public function set_action($act) {
		$this->action = $act;
	}

	public function get_action() {
		return $this->action;
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

	/*
	 * TODO: Docs
	 */
	public function __construct($host, $user, $pw, $db) {
		$this->conn = new mysqli($host, $user, $pw, $db);
		$conn_err = $this->conn->connect_errno;
		if($conn_err) {
			throw new Exception($this->errnomsg . 'MySQL connection failed');
		}
	}

	/*
	 * TODO: Document
	 */
	public function select($table, $find, $args) {
		$q = $this->select_query($table, $find, $args);
		$res = $this->query_stmt($q, $args);
		echo "$q<br />";
		print_r($res->fetch_assoc());
	}

	/*
	 * TODO: Documemt
	 */
	public function insert($table, $args) {
		$q = $this->insert_query($table, $args);
		echo "$q<br />";
		$res = $this->query_stmt($q, $args);
	}

	/*
	 * Executes a prepared statement query,
	 * subs in $args
	 *
	 * todo: doc
	 */
	public function query_stmt($query, $args) {
		$stmt = $this->stmt_prepare($query);
		return $this->stmt_exec($stmt, $this->stmt_types($args), $args);
	}

	/*
	 * Generates paramaterized query for SELECT
	 *
	 * @param string $table  The table to search in
	 * @param string|array $find   The column(s) you want to search in
	 * @param hash $args //TODO: add shit here
	 */
	private function select_query($table, $find, $args) {
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
	 * TODO: Document
	 */
	private function insert_query($table, $args) {
		$q = "INSERT INTO $table (";

		$c = count($args);
		$i = 0;
		foreach($args as $k => $v) {
			$q .= $k;
			if($i < $c - 1) {
				$q .= ',';
			}
			$i++;
		}

		$q .= ') VALUES (';

		for($i = 0; $i < $c; $i++) {
			$q .= '(?)';
			if($i < $c - 1) {
				$q .= ',';
			}
		}

		$q .= ')';
		return $q;
	}


	/*
	 * @param string  Query as a prepared statement
	 * @return mysqli_stmt
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
	 * @param hash $arg  Finds the types of the values in this
	 * @return string  Type string for bind_param() 
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
	 * @param mysqli_stmt $stmt  MySQLi statement to execute
	 * @param string $types  Type string to be bound
	 * @param array $vals  Array of values to be bound
	 * @return mysqli_result
	 */
	private function stmt_exec($stmt, $types, $vals) {
		array_unshift($vals, $types); //prepend $types with $value

		/*
		 * I honestly have no idea why I need to do this.
		 * You have to assign the values in the new array to be references
		 * to the items in the old array.
		 */
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
		return '[ERRNO: '.$this->conn->errno.'] ' . $this->conn->error . ' ';
	}
}

?>
