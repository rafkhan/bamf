<?php

require('bamf.php');

$r = new Router();

$r->add('/bamf/', function() {
	echo "hello";
});

$r->add('/bamf/asd', function() {

	$ar = array(
		'foo' => 'bar',
		'baz' => 'qux'
	);

	$db = new Database('127.0.0.1', 'root', '', 'chm');
	$db->find_by('table_name', $ar);

	$t = new Template('testplate.php', $ar);
	$t->render();
});


$r->route();

?>
