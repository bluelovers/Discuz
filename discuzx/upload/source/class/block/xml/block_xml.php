<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_xml.php 19516 2011-01-05 07:11:32Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_xml {

	var $blockdata = array();

	function block_xml($xmlid = null) {
		if(!empty($xmlid)) {
			$blockxml = DB::fetch_first("SELECT * FROM ".DB::table('common_block_xml')." WHERE id='$xmlid'");
			if(!$blockxml) {
				return;
			}
			$this->blockdata = $blockxml;
			$this->blockdata['data'] = (array)unserialize($blockxml['data']);
		} else {
			$query = DB::query('SELECT * FROM '.DB::table('common_block_xml'));
			while($value = DB::fetch($query)) {
				$one = $value;
				$one['data'] = (array)unserialize($value['data']);
				$this->blockdata[] = $one;
			}
		}
	}

	function name() {
		return dhtmlspecialchars($this->blockdata['data']['name']);
	}

	function blockclass() {
		return dhtmlspecialchars($this->blockdata['data']['blockclass']);
	}

	function fields() {
		return dhtmlspecialchars($this->blockdata['data']['fields']);
	}

	function getsetting() {
		return dhtmlspecialchars($this->blockdata['data']['getsetting']);
	}

	function getdata($style, $parameter) {
		$array = array();
		foreach($parameter as $key => $value) {
			if(is_array($value)) {
				$parameter[$key] = implode(',', $value);
			}
		}
		$parameter['clientid'] = $this->blockdata['clientid'];
		$parameter['op'] = 'getdata';
		$parameter['charset'] = CHARSET;
		$parameter['version'] = $this->blockdata['version'];
		$xmlurl = $this->blockdata['url'];
		$parse = parse_url($xmlurl);
		if(!empty($parse['host'])) {
			define('IN_ADMINCP', true);
			require_once libfile('function/importdata');
			$importtxt = @dfsockopen($xmlurl, 0, create_sign_url($parameter, $this->blockdata['key'], $this->blockdata['signtype']));
		} else {
			$importtxt = @file_get_contents($xmlurl);
		}
		if($importtxt) {
			require libfile('class/xml');
			$array = xml2array($importtxt);
		}
		$idtype = 'xml_'.$this->blockdata['id'];
		foreach($array['data'] as $key=>$value) {
			$value['idtype'] = $idtype;
			$array['data'][$key] = $value;
		}
		if(empty($array['data'])) $array['data'] = null;
		return $array;
	}

}

?>