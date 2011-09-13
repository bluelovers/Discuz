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

	function viewthread_posttop_output() {
		/*
		$args = func_get_args();

		print_r($args);

		dexit(array(
			$args
			, __METHOD__
		));

		return 123;
		*/

		global $_G, $postlist;

		$ret = array();
		$_i = 0;

		$this->_setglobal('fb_appid', 159786194105526);

		foreach ($postlist as $pid => $post) {
			$ret[$_i] = $this->_fetch_template($this->_template('fb_like'), array(
				'_i' => $_i,
				'pid' => $pid,
				'post' => $post,

				'siteurl' => $_G['siteurl'],

				'fb_appid' => $this->_getglobal('fb_appid'),

				'adminid' => $_G['adminid'],
				'uid' => $_G['uid'],

				'_href' => "{$_G[siteurl]}forum.php?mod=redirect&goto=findpost&ptid={$post[tid]}&pid={$post[pid]}&fromuid=".$_G['uid'],
			));

			$_i++;
		}

		$this->_hook('Func_output:Before_rewrite_content_echo', array(
			$this,
			'_hook_html_xmlns'
		));

		return $ret;
	}

	function _hook_html_xmlns($_EVENT, $_conf) {
		if(defined('IN_MODCP') || defined('IN_ADMINCP')) return;

		extract($_conf, EXTR_REFS);

		$content = preg_replace('/(\<html\s*)([^\<\>]*)(\>\s*\<head\>)/i', '\\1\\2 xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"\\3', $content);
	}

}

?>