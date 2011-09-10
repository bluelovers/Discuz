<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

class _sco_dx_tag {

	function utf8_strlen($str) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
	}

}

?>