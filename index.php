<?php

require('bamf.php');

$r = new Router();

$r->add('/bamf/', function() {
	echo "hello";
});

$r->add('/bamf/asd', function() {

	$ar = array(
		'foo' => 'bar',
		'baz' => 'qux',
		'qwe' => 1,
		'zxc' => 3.14159
	);

	$db = new Database('127.0.0.1', 'root', '', 'chm');

	/*
	$db->select('chm_images', array('title','description','image_href'), 
	             array('cat_id' => 12));
	$db->insert('chm_images', array('cat_id' => 7, 'title' => "seven"));
	 */

	$t = new Template('testplate.php', $ar);
	$t->render();
});


$r->route();

?>
