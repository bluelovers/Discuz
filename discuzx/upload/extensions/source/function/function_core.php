<?php

/**
 * @author bluelovers
 **/

/**
 * Push one or more array onto the end of array
 *
 * @param &$array
 * @param $args
 **/
function array_push_array(&$array, $args) {
	$args = func_get_args();
	array_shift($args);

	if (count($args)) {
	    foreach ($args as $v) {
	    	if (!empty($v)) {
				foreach ($v as $r) {
					array_push($array, $r);
				}
			}
		}
	}
}

?>