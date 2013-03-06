<?php

class Router3 {
	
	private $path_tree;

	public function __construct() {
		$this->path_tree = new RNode();
	}

	public function add($path, $func) {
		$path_arr = $this->parse_uri($path);
		$this->add_path($this->path_tree, $path_arr, $func);
		print_r($this->path_tree);
	}

	public function route() {
		$path = $_SERVER['REQUEST_URI'];
		$path_arr = $this->parse_uri($path);
		$this->route_path($this->path_tree, $path_arr);
	}

	private function route_path(&$parent, $path) {
		$path_seg = array_shift($path);
		if($path_seg == NULL) {
			$func = $parent->get_action();
			call_user_func($func);
		} else {
			if($child = $parent->get_child($path_seg)) {
				$this->route_path($child, $path);
			} else {
				echo "<strong><h1>404 ERROR</h1></strong><br />";
			}
		}
	}

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
	
	private function parse_uri($path) {
		$tokens = explode('/', $path);

		foreach($tokens as $k => $v) {
			if($v == '') {
				unset($tokens[$k]);
			}
		}
		array_values($tokens);

		return $tokens;
	}
}

class RNode {

	private $action;
	private $children = [];

	public function get_child($child_name) {
		if(array_key_exists($child_name, $this->children)) {
			return $this->children[$child_name];
		} else {
			return FALSE;
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

?>
