<?php

/**
 *
 */

require './../../source/class/class_core.php';

$discuz = & discuz_core::instance();
$discuz ->init();

echo "<p>dgmdate(time()) = ".dgmdate(time());

echo "<p>dgmdate(time(), 'u') = ".dgmdate(time(), 'u');

?>