<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.

	$HeadURL: $
	$Revision: $
	$Author: bluelovers$
	$Date: $
	$Id: $
*/

/*
require_once './extensions/hooks/hooks_discuzcode.php';

$obj = scoembed::instance(array('id' => 'testid'));

echo "<pre>";
print_r($obj->toArray());
echo " ==\n";
print($obj->toArray(1));
echo " ==\n";
print($obj->toJson());
echo " ==\n";
print($obj->toHtml());
*/

define('JSON_HEX_ALL', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

class scoembed {
	static $data_array_def = array(
		'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
		'mimeType' => 'application/x-shockwave-flash',
		'allowNetworking' => 'internal',
		'allowScriptAccess' => 'never',
		'quality' => 'high',
		'bgcolor' => '#ffffff',
		'wmode' => 'transparent',
		'windowless' => 'true',
		'background' => 'transparent',
		'allowfullscreen' => 'true',
		'pluginspage' => 'http://www.macromedia.com/go/getflashplayer',
		'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0',
	);

	static $attr = array(
		'object' => array(
			'classid', 'codebase',
		),
		'embed' => array(
			'pluginspage',
		),
		'all' => array(
			'name', 'id',
			'src', 'movie', 'width', 'height',
			'type',
			'align', 'vspace', 'hspace',
			'class', 'title', 'accesskey', 'tabindex',
			'wmode', 'quality', 'windowless',
		),
		'params' => array(
			'src', 'movie',
		),
	);

	var $data_array = array(

	);

	var $options = array(
		'json_decode' => true,
		'json_encode' => JSON_HEX_ALL,
	);

	public static function &instance($data = array(), $datatype = 'array') {
		$ref = new ReflectionClass(get_called_class());
		$instances = $ref->newInstance($data, $datatype);

		return $instances;
	}

	function __construct($data = array(), $datatype = 'array') {
		$this->data_array = $this->_parse($data, $datatype);
	}

	function _parse($data, $datatype = 'array') {
		if ($datatype == 'json') {
			$data = json_decode($data, $this->options['json_decode']);
		}

		return $data;
	}

	function _output() {
		$data = array_merge(static::$data_array_def, $this->data_array);

		$data['src'] = $data['movie'] = ($data['src'] ? $data['src'] : $data['movie']);

		return $data;
	}

	function toArray($implode = false) {
		$data = $this->_output();

		if ($implode) {
			$ret = array();
			foreach ($data as $_k => $_v) {
				$ret[] = $_k;
				$ret[] = addslashes($_v);
			}
			$data = $ret;
		}

		return $implode ? "'".implode("','", $data)."'" : $data;
	}

	function toJson() {
		$data = $this->_output();
		return json_encode($data, $this->options['json_encode']);
	}

	function toHtml($lf = false) {
		$data = $this->_output();

		$html = array();

		$lf = $lf ? "\n" : '';

		foreach ($data as $_k => $_v) {
			$isembed = 0;
			$isobject = 0;
			$isparams = true;

			if (in_array($_k, static::$attr['embed'])) {
				$isembed = true;
				$isparams = false;
			} elseif (in_array($_k, static::$attr['object'])) {
				$isobject = true;
				$isparams = false;
			}

			if (in_array($_k, static::$attr['all'])) {
				$isembed = true;
				$isobject = true;
				$isparams = true;
			}

			if (!$isparams && (in_array($_k, static::$attr['params']) || (!$isobject && !$isembed))) {
				$isparams = true;
				$isembed = true;
			}

			$isobject && $html[0] .= ' '.$_k.'="'.addslashes($_v).'"';
			$isembed && $html[2] .= ' '.$_k.'="'.addslashes($_v).'"';
			$isparams && $html[1] .= $lf.'<param name="'.$_k.'" value="'.addslashes($_v).'" />';
		}

		$str = '<object '.trim($html[0]).'>'.trim($html[1]).$lf.'<embed '.trim($html[2]).'></embed>'.$lf.'</object>';

		return $str;
	}
}

Scorpio_Hook::add('Func_discuzcode:After_nl2br', '_eFunc_discuzcode_After_nl2br');

function _eFunc_discuzcode_After_nl2br($conf) {
	$conf['message'] = nl2brcode(str_replace('[tab][/tab]', "\t", $conf['message']), 1);
}

Scorpio_Hook::add('Func_discuzcode:Before_jammer', '_eFunc_discuzcode_Before_jammer');

function _eFunc_discuzcode_Before_jammer($conf) {
	$find = $replace = array();

	$find[]		= '/\s+$/is';
	$replace[]	= '';

	if ($find && $replace) {
		$conf['message'] = preg_replace($find, $replace, $conf['message']);
	}
}

Scorpio_Hook::add('Func_discuzcode:Before_code', '_eFunc_discuzcode_Before_code');

function _eFunc_discuzcode_Before_code($conf) {
	$conf['message'] = scotext::lf($conf['message']);
}

Scorpio_Hook::add('Func_discuzcode:Before_bbcodes', '_eFunc_discuzcode_Before_bbcodes');

function _eFunc_discuzcode_Before_bbcodes($conf) {
	$find = $replace = array();

	$find[]		= '/(\[\/h[1-6]\])\n{2,}(\[h[1-6]\])/is';
	$replace[]	= "\\1\n\\2";

	$find[]		= '/\[h([1-6])\](.+?)\[\/h\\1\](?:\r\n|\n)?/is';
	$replace[]	= "<h\\1 class=\"bbcode_headline\">\\2</h\\1>";

	$find[]		= '/\s*\[(seo)(?:=([\w,]+))?\]((?:[^\[]|\[(?!\/\\1\])).*)\[\/\\1\]\s*/iesU';
	$replace[]	= '';

	if ($find && $replace) {
		$conf['message'] = preg_replace($find, $replace, $conf['message']);
	}
}

?>