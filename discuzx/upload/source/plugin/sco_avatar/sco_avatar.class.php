<?php

include_once 'sco_dx_plugin.class.php';

class plugin_sco_avatar extends _sco_dx_plugin {
	function plugin_sco_avatar() {
		$this->_init($this->_get_identifier(__METHOD__));
	}
}

class plugin_sco_avatar_home extends plugin_sco_avatar {
	/**
	 * 在瀏覽 修改頭像 時執行
	 *
	 * 此時尚未執行 require_once libfile('home/'.$mod, 'module');
	 *
	 * @see home.php
	 * @link home.php?mod=spacecp&ac=avatar
	 **/
	function spacecp_avatar() {
		/*
		echo '<pre>';

		echo $this->identifier."\n";
		print_r($this);
		*/

		global $_G;
		$op = getgpc('op');

		include $this->_template('spacecp_avatar');
		exit();
	}
}

?>