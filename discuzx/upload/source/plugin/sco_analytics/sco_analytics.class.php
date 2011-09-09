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

}

class mobileplugin_sco_analytics extends plugin_sco_analytics {

	public function __construct() {
		$this->_init($this->_get_identifier(__METHOD__));
	}

	function global_footer_mobile() {

		$this
			->_setglobal('GA_PIXEL', 'source/plugin/sco_analytics/bin/ga.php')
			->_setglobal('GA_ACCOUNT', 'MO-741906-1')
		;

		$googleAnalyticsImageUrl = $this->_my_googleAnalyticsGetImageUrl();
		$ret = '<img src="' . $googleAnalyticsImageUrl . '" />';

		$ret = '<div style="display: none; visibility: hidden;">'.$ret.'</div>';

		return $ret;
	}

	/**
	 * get identifier from __CLASS__
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

}

?>