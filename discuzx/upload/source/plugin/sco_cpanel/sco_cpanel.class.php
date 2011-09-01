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

	const identifier = 'sco_cpanel';

	function init($identifier) {
		$this->_init($identifier);

		$this->_this(&$this);

		$this->_fix_plugin_setting();
		$this->attr['profile'] = $this->attr['db']['common_plugin'];

		return $this;
	}

	function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
		return submitcheck($var, $allowget, $seccodecheck, $secqaacheck);
	}

	function cpheader() {
		cpheader();

		return $this;
	}

	function cpfooter() {
		/*
		cpfooter();
		dexit();
		*/

		return $this;
	}

	function cplang($name, $replace = array(), $output = false) {
		return cplang($name, $replace, $output);
	}

	/**
	 * 提示消息
	 *
	 * @param $message - lang_admincp_msg.php 語言包中需要輸出的key
	 * @param $url - 提示信息後跳轉的頁面，留空則返回上一頁
	 * @param $type - 特殊提示信息時指定頁面的提示樣式，可選參數：succeed、error、download、loadingform
	 * @param $values - 為語言包中的變量關鍵詞指定值，以數組形式輸入
	 * @param $extra - 消息文字擴展
	 * @param $halt - 是否輸出「Discuz! 提示」標題
	 */
	function cpmsg($message, $url = '', $type = '', $values = array(), $extra = '', $halt = TRUE) {
		return cpmsg($message, $url, $type, $values, $extra, $halt);
	}

	function &mod($mod, $identifier = '') {
		if (empty($identifier)) $identifier = self::identifier;

		include_once libfile('mod/'.$mod, 'plugin/'.$identifier);

		$class = 'plugin_'.$identifier.'_'.$mod;
		$self = new $class();

		$self
			->init($identifier)
			->set(array(
				'mod' => $mod,
			))
		;

		return $self;
	}

	function set($attr) {
		/*
		$this->attr['global'] = $attr;
		*/
		foreach ($attr as $_k => $_v) {
			$this->attr['global'][$_k] = $_v;
		}

		return $this;
	}

	function run() {
		$operation = $this->attr['global']['op'];

		$operation = $operation ? $operation : 'default';

		$method = 'on_op_'.$operation;

		ob_start();
		$this->$method();
		$_content = ob_get_contents();
		ob_end_clean();

		$this->cpheader();
		echo $_content;

		if ($this->_getglobal('debug', 'setting')) {
			var_dump($this);
		}

		$this->cpfooter();

		return $this;
	}

	/**
	 * 預設行為
	 */
	function on_op_default() {
		/*
		$this->on_op_list_fups();
		*/

		return $this;
	}

}

?>