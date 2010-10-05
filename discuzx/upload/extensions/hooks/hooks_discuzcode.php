<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.
	This is NOT a freeware, use is subject to license terms

	$HeadURL: svn://localhost/trunk/discuz_x/upload/extensions/hooks/hooks_core.php $
	$Revision: 109 $
	$Author: bluelovers$
	$Date: 2010-08-02 06:22:26 +0800 (Mon, 02 Aug 2010) $
	$Id: hooks_core.php 109 2010-08-01 22:22:26Z user $
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

?>