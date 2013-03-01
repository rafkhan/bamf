<?php

class DBamf {

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
		$stmt = $this->prepare_stmt($q);

	}

	/*
	 * Generates paramaterized query for SELECT
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

		return $q;
	}

	/*
	 * @param   string  Query as a prepared statement
	 * @return  mysqli_stmt
	 */
	private function prepare_stmt($q) {
		if($stmt = $this->conn->prepare($q)) {
			return $stmt;
		} else {
			throw new Exception($this->errnomsg() . 'Failed preparing query');
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
	private function gen_find_by_types($arg) {
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

$db = new DBamf('127.0.0.1', 'root', '', 'chm');

?>
