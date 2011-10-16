<?php
/**
 *      借助geegle api生成。
 *     作者QQ：21365421，不接受任何咨询，仅表示版权。
 *
 *
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_mpdcode{
	function plugin_mpdcode() {
		global $_G;

//		$this->title = $_G['cache']['plugin']['myapp']['showtitle'];
		$this->wh = intval($_G['cache']['plugin']['mpdcode']['widhtHeight']);
		if(empty($this->wh)) {
				$this->wh = '100';
			}
	}
}
class plugin_mpdcode_forum extends plugin_mpdcode{
	function viewthread_useraction_output() {
		$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$url ='http://'.$url;

		if (strpos($chl, '?') === false) {
			$url .= '?';
		} else {
			$url .= '&';
		}

		$url .= 'mobile=yes';

		$chl = urlencode($url);
//		$widhtHeight ='150';
		$EC_level='L';
		$margin='0';
		$return.= '<div style="float:right;">手机二维码访问：<br/><img src="http://chart.apis.google.com/chart?chs='.$this->wh.'x'.$this->wh.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$chl.'" alt="QR code" widhtHeight="'.$size.'" widhtHeight="'.$size.'"/></div>';
		return $return;
}
}
?>