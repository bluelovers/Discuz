<?php

$moduleinfo = array(
	'name' => '投票',
	'identifier' => 'poll',
	'available' => 1,
	'displayorder' => 0,
	'version' => '20100915'
);

$moduleinformation = array(
	0 => array(
		'col' => 'COUNT(*)',
		'table' => 'item',
		'conditions' => "`contenttype`='1'",
		'returnkey' => 'pitems',
	),
	1 => array(
		'table' => 'item',
		'returnkey' => 'items',
	),
	2 => array(
		'table' => 'choice',
		'returnkey' => 'choices',
	),
	3 => array(
		'table' => 'value',
		'returnkey' => 'values',
	),
);

?>