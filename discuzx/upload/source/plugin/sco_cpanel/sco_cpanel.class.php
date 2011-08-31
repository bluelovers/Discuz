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

}

?>