<?php

require('bamf.php');

$r = new Router();

$r->add('/', function() {
	echo "asdasdasfnao";
});

$r->add('/bamf/asd', function() {
	
	$db = new Database('127.0.0.1', 'root', '', 'chm');

	$db->select('chm_images', array('title','description','image_href'), 
	array('cat_id' => 12));
//	$db->insert('chm_images', array('cat_id' => 7, 'title' => "seven"));

	$ar = array('foo' => 'FOOO', 'baz' => 'BAAAZ');
	$t = new Template('testplate.php', $ar);
	$t->render();
});



$r->add('/bamf/asd/@/2/@', function() {
	echo "woah";	
});


$r->route();

?>
