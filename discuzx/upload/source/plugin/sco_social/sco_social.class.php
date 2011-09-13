<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_social extends _sco_dx_plugin {

	public function __construct() {
		$this->_init($this->_get_identifier(__CLASS__));

		// set instance = $this
		$this->_this(&$this);
	}

}

class plugin_sco_social_forum extends plugin_sco_social {

	function viewthread_posttop() {
		/*
		$args = func_get_args();

		print_r($args);

		dexit(array(
			$args
			, __METHOD__
		));

		return __CLASS__;
		*/
	}

}

?>