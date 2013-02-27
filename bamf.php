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
	 * @param   $path    string
	 * @param   $action  hash
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
