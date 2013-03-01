<?php

require('bamf.php');
require('dbamf.php');

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

	$db = new DBamf('127.0.0.1', 'root', '', 'chm');
	$db->select('chm_categories', array('qwe','asd','zxc'), 
	  array('rty' => 'fgh', 'vbn' => 'uio'));

	$t = new Template('testplate.php', $ar);
	$t->render();
});


$r->route();

?>
