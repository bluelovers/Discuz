<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloudstat.class.php 22784 2011-05-20 10:22:53Z wangwumin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_cloudstat {
	var $discuzParams = array();
	var $virtualDomain = '';
	var $extraParams = array();

	function global_footerlink() {
		global $_G;
		if($_G['inajax']) {
			return '';
		}
		return $this->_makejs();
	}

	function _makejs() {
		global $_G;
		$dzjs = $this->_makedzjs();
		if (!$_G['inajax']) {
			$return = '&nbsp;&nbsp;<span id="tcss"></span><script type="text/javascript" reload="1">appendscript(\'http://tcss.qq.com/ping.js\', \''.VERHASH.'\', \'1\', \'utf-8\');safescript(\'cloudstatjs\', function () {pgvMain('.$dzjs.')}, 1000, 5);</script>';
		} else {
			$return = '<script type="text/javascript" reload="1">safescript(\'cloudstatjs\', function () {pgvMain('.$dzjs.')}, 1000, 5);</script>';
		}
		return $return;
	}

	function _makedzjs() {
		global $_G;

		$this->discuzParams['r2'] = $_G['setting']['my_siteid'];

		$this->discuzParams['ui'] = $_G['uid'] ? $_G['uid'] : 0;

		if($_G['uid'] && ($_G['timestamp'] - $_G['member']['regdate'] > 86400)) {
			$this->discuzParams['ty'] = 2;
		}

		$this->discuzParams['rt'] = $_G['basescript'];

		if($_G['mod']) {
			$this->discuzParams['md'] = $_G['mod'];
		}

		if($_G['fid']) {
			$this->discuzParams['fi'] = $_G['fid'];
		}

		if($_G['tid']) {
			$this->discuzParams['ti'] = $_G['tid'];
		}

		if($_G['page']) {
			$this->discuzParams['pn'] = $_G['page'];
		} else {
			$this->discuzParams['pn'] = 1;
		}

		if($_G['member']['conisbind']) {
			$this->discuzParams['qq'] = $_G['member']['conuin'];
		}

		$cloudstatpost = getcookie('cloudstatpost');
		dsetcookie('cloudstatpost');
		$cloudstatpost = explode('D', $cloudstatpost);
		if($cloudstatpost[0] == 'thread') {
			$this->discuzParams['nt'] = 1;
			$this->discuzParams['ti'] = $cloudstatpost[1];
			$subject = $_G['forum_thread']['subject'];
			if ('GBK' != strtoupper($_G['charset'])) {
				$subject = diconv($subject, $_G['charset'], 'GBK');
			}
			$this->extraParams[] = "tn=" . urlencode($subject);
		} elseif($cloudstatpost[0] == 'post') {
			$this->discuzParams['nt'] = 2;
			$this->discuzParams['ti'] = $cloudstatpost[1];
			$this->discuzParams['pi'] = $cloudstatpost[2];
		}

		$cloudstaticon = intval($_G['setting']['cloud_staticon']);
		if ($cloudstaticon && !$_G['inajax']) {
			$this->discuzParams['logo'] = $cloudstaticon;
		}

		$refInfo = parse_url($_G['siteurl']);
		if('/' == substr($refInfo['path'], -1)) {
			$refInfo['path'] = substr($refInfo['path'], 0, -1);
		}
		$this->virtualDomain = $refInfo['host'] . $refInfo['path'];

		return $this->_response_format(array(
			'discuzParams' => $this->discuzParams,
			'virtualDomain' => $this->virtualDomain,
			'extraParams' => implode(';', $this->extraParams)
		));
	}

	function _response_format($result) {
		if(function_exists('json_encode')) {
			$json = json_encode($result);
		} else {
			$json = $this->_array2json($result);
		}
		return $json;
	}

	function _array2json($array) {
		$piece = array();
		foreach ($array as $k => $v) {
			$piece[] = $k . ':' . $this->_php2json($v);
		}

		if ($piece) {
			$json = '{' . implode(',', $piece) . '}';
		} else {
			$json = '[]';
		}
		return $json;
	}

	function _php2json($value) {
		if (is_array($value)) {
			return $this->_array2json($value);
		}
		if (is_string($value)) {
			$value = str_replace(array("\n", "\t"), array(), $value);
			$value = addslashes($value);
			return '"'.$value.'"';
		}
		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}
		if (is_null($value)) {
			return 'null';
		}

		return $value;
	}

}

class plugin_cloudstat_forum extends plugin_cloudstat {

	function post_cloudstat_message($param) {
		global $_G;
		$param = $param['param'];
		if(in_array($param[0], array('post_newthread_succeed', 'post_newthread_mod_succeed'))) {
			dsetcookie('cloudstatpost', 'threadD'.$param[2]['tid'], 300);
		} elseif(in_array($param[0], array('post_reply_succeed', 'post_reply_mod_succeed'))) {
			dsetcookie('cloudstatpost', 'postD'.$param[2]['tid'].'D'.$param[2]['pid'], 300);
		}
	}

	function viewthread_postbottom_output() {
		global $_G;
		$cloudstatjs = array();
		if($_G['inajax'] && !empty($_G['gp_viewpid'])) {
			$cloudstatjs[] = $this->_makejs();
		}
		return $cloudstatjs;
	}
}

?>