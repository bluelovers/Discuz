<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_cpanel extends _sco_dx_plugin {

	function deletethread($_args = array()) {
/*
Array
(
    [param] => Array
        (
            [0] => Array
                (
                    [0] => 32989
                )

            [1] => 1
            [2] => 1
            [3] => 1
        )

    [step] => check
)
*/

		list($tids, $membercount, $credit, $ponly) = $_args['param'];
	}

}

?>