<?php

require('bamf.php');

$r = new Router();

$r->add('/bamf/', function() {
	echo "hello";
});

$r->add('/bamf/asd', function() {
	echo "hello there :3";

	$ar = array(
		'foo' => 'bar',
		'baz' => 'qux'
	);

	$t = new Template('testplate.php', $ar);
	$t->render();
});


$r->route();

?>
