<?php

class DRouter {

	private $path_tree;

	public function __construct() {
		$this->path_tree = [];
	}

	public function add($path, $action) {
		$path_arr = $this->parse_uri($path, $action);	
		$this->add_path($this->path_tree, $path_arr, $action);
		print_r($this->path_tree);
		echo '<br />';
	}

	public function route() {
		echo '<br />routing!<br />';
	}

	public function exec_uri($parent, $path) {
		$path_key = array_shift($path);

		if($path_key != NULL) {
			if(array_key_exists($path_key, $parent_node)) {

			} else {
			}
		}
	}

	private function add_path(&$parent_node, $path, $action) {
		$path_key = array_shift($path);

		if($path_key != NULL) {
			if(array_key_exists($path_key, $parent_node)) {
				$this->add_path($parent_node[$path_key], $path, $action);
			} else {
				$parent_node[$path_key] = [];
				$this->add_path($parent_node[$path_key], $path, $action);
			}
		} else {
			$parent_node = $action;
		}
	}

	private function parse_uri($path, $action) {
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

?>
