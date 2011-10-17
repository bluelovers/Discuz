<?php

/**
 *      借助geegle api生成。
 *     作者QQ：21365421，不接受任何咨询，仅表示版权。
 *
 *
 */

/**
 * @author conroy
 *
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_mpdcode {
	function plugin_mpdcode() {
		global $_G;

		$this->wh = intval($_G['cache']['plugin']['mpdcode']['widhtHeight']);
		if (empty($this->wh)) {
			$this->wh = '100';
		}
	}
}
class plugin_mpdcode_forum extends plugin_mpdcode {

	function ad_thread($_conf) {
		/**
		 * ad_a_pr/thread/a_pr/3/$postcount
		 */
		$parameter = implode('/', $_conf['params']);

		if ($parameter == 'thread/a_pr/3/0') {
			$_regex = '/(\<div class="a_pr"(?:[^\>]*)\>)/i';

			if (preg_match($_regex, $_conf['content'])) {
				$_conf['content'] = preg_replace($_regex, '\\1'.$this->_output_html(1), $_conf['content']);
			} else {
				$_conf['content'] .= $this->_output_html();
			}
		}

		return $_conf['content'];
	}

	function _output_html($nodiv = false) {
		global $_G, $postlist;

		$_post = reset($postlist);

		if (!$_post['first']) return array();

		$url = $this->_mobile_url($_G['tid']);

		$chl = urlencode($url);

		$EC_level = 'L';
		$margin = '0';
		$return .= '手机二维码访问：<br/><a href="' . dhtmlspecialchars($url) . '" target="_blank"><img src="http://chart.apis.google.com/chart?chs=' . $this->wh . 'x' . $this->wh . '&cht=qr&chld=' . $EC_level . '|' . $margin . '&chl=' . $chl . '" alt="QR code"/></a>';

		if (!$nodiv) {
			$return = '<div class="y">'.$return.'</div>';
		} else {
			$return .= '<br/><br/>';
		}

		return $return;
	}

	function _mobile_url($tid = null) {
		global $_G;

		$url = '';

		$url .= 'http://';

		if ($_G['setting']['domain']['app']['mobile']) {
			$url .= $_G['setting']['domain']['app']['mobile'];
		} else {
			$url .= $_SERVER['HTTP_HOST'];
		}

		$url .= '/';

		if (!isset($tid)) $tid = $_G['tid'];

		if (@in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
			$canonical = rewriteoutput('forum_viewthread', 1, '', $tid, 1, '', '');
		} else {
			$canonical = 'forum.php?mod=viewthread&tid=' . $tid;
		}

		$url .= $canonical;

		if (strpos($url, '?') === false) {
			$url .= '?';
		} else {
			$url .= '&';
		}

		$url .= 'mobile=yes';

		return $url;
	}
}

?>