<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_analytics extends _sco_dx_plugin {

	public function __construct() {
		$this->_init($this->_get_identifier(__CLASS__));

		// set instance = $this
		$this->_this(&$this);
	}

	/**
	 * get identifier from __CLASS__
	 *
	 * @todo 將此 method 更新到 _sco_dx_plugin
	 **/
	function _get_identifier($method) {
		$a = explode('::', $method);
		$k = array_pop($a);

		// remove plugin_ from identifier
		if (strpos($k, 'plugin_') === 0) {
			$k = substr($k, strlen('plugin_'));
		} elseif (strpos($k, 'mobileplugin_') === 0) {
			$k = substr($k, strlen('mobileplugin_'));
		}

		return $k;
	}

	function global_header_javascript() {
		$ret = '';

		$this->_fix_plugin_setting();

		$ret .= $this->_my_ga_web_html();

		return $ret;
	}

	function global_footerlink() {
		$ret = '';

		$this->_fix_plugin_setting();

		$ret .= $this->_my_ya_tw();

		$ret = '<div style="display: none; visibility: hidden;">'.$ret.'</div>';

		return $ret;
	}

	function _my_ga_web_html() {
		$ret = '';

		// google-analytics 網頁追蹤碼
		if ($this->_getglobal('ga_web_id', 'setting')) {

			$this
				->_setglobal('ga_web_id', $this->_getglobal('ga_web_id', 'setting'))
			;

			$ret .= $this->_fetch_template($this->_template('ga_web'), $this->attr['global']);
		}

		return $ret;
	}

	/**
	 * Yahoo 站長工具(台灣)
	 *
	 * @link http://tw.webmaster.yahoo.com/stats/code.html?unit_id=191960
	 */
	function _my_ya_tw($nojs = false) {
		$_setting_key = 'ya_tw_id';
		$ret = '';

		if ($this->_getglobal($_setting_key, 'setting')) {
			$this
				->_setglobal($_setting_key, $this->_getglobal($_setting_key, 'setting'))
				->_setglobal($_setting_key.'_nojs', (bool)$nojs)
			;

			$ret .= $this->_fetch_template($this->_template('yahoo/ya_tw'), $this->attr['global']);
		}

		return $ret;
	}

	function _my_histats($nojs = false) {
		$_setting_key = 'histats_id';
		$ret = '';

		if ($this->_getglobal($_setting_key, 'setting')) {
			$this
				->_setglobal($_setting_key, $this->_getglobal($_setting_key, 'setting'))
				->_setglobal($_setting_key.'_nojs', (bool)$nojs)
				->_setglobal('histats_server', $this->_getglobal('histats_server', 'setting'))
			;

			$ret .= $this->_fetch_template($this->_template('histats'), $this->attr['global']);
		}

		return $ret;
	}

}

class mobileplugin_sco_analytics extends plugin_sco_analytics {

	public function __construct() {
		parent::__construct();

		$this
			->_setglobal('sco_analytics_nojs', true)
		;

		return $this;
	}

	function global_footer_mobile() {

		// 修正 setting 的內容
		$this->_fix_plugin_setting();

		$ret = '';

		$ret .= $this->_my_ga_mobile_html();
		$ret .= $this->_my_ya_tw(true);

		$ret = '<div style="display: none; visibility: hidden;">'.$ret.'</div>';

		return $ret;
	}

	/**
	 * 手機追蹤程式碼
	 * 專為行動電話而設的網站
	 *
	 * @copyright Copyright 2009 Google Inc. All Rights Reserved.
	 */
	function _my_googleAnalyticsGetImageUrl() {

		$GA_ACCOUNT = $this->_getglobal('GA_ACCOUNT');
		$GA_PIXEL = $this->_getglobal('GA_PIXEL');

		$url = "";
		$url .= $GA_PIXEL . "?";
		$url .= "utmac=" . $GA_ACCOUNT;
		$url .= "&utmn=" . rand(0, 0x7fffffff);
		$referer = $_SERVER["HTTP_REFERER"];
		$query = $_SERVER["QUERY_STRING"];
		$path = $_SERVER["REQUEST_URI"];
		if (empty($referer)) {
			$referer = "-";
		}
		$url .= "&utmr=" . urlencode($referer);
		if (!empty($path)) {
			$url .= "&utmp=" . urlencode($path);
		}
		$url .= "&guid=ON";
		return str_replace("&", "&amp;", $url);
	}

	/**
	 * 手機追蹤程式碼
	 * 專為行動電話而設的網站
	 *
	 * @copyright Copyright 2009 Google Inc. All Rights Reserved.
	 */
	function _my_ga_mobile_html() {
		$ret = '';

		if ($this->_getglobal('ga_mobile_id', 'setting')) {

			$this
				->_setglobal('GA_PIXEL', 'source/plugin/sco_analytics/bin/ga.php')
				->_setglobal('GA_ACCOUNT', $this->_getglobal('ga_mobile_id', 'setting'))
			;

			$googleAnalyticsImageUrl = $this->_my_googleAnalyticsGetImageUrl();
			$ret = '<img src="' . $googleAnalyticsImageUrl . '" />';
		}

		return $ret;
	}

}

?>