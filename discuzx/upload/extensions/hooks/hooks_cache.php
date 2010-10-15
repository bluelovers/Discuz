<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.

	$HeadURL: $
	$Revision: $
	$Author: bluelovers$
	$Date: $
	$Id: $
*/

Scorpio_Hook::add('Class_template::parse_template:Before_fwrite', '_eClass_template_parse_template_Before_fwrite');

function _eClass_template_parse_template_Before_fwrite($conf) {
	$conf['template'] = scotext::lf($conf['template']);
//	$conf['template'] = preg_replace(array('/(\n)\t+/s', '/(\n){2,}/s', '/^(\xef\xbb\xbf)?[\s]+|[\s]+$/sU', '/(?!^)\xef\xbb\xbf/U'), array('\\1', '\\1', '\\1', ''), $conf['template']);
	$conf['template'] = preg_replace(array('/(\n)\t+/s', '/(\n){2,}/s', '/^(\xef\xbb\xbf)?[\s]+|[\s]+$/sU', '/\xef\xbb\xbf/U'), array('\\1', '\\1', '', ''), $conf['template']);
}

Scorpio_Hook::add('Func_writetocsscache:Before_fwrite', '_eFunc_writetocsscache_Before_fwrite');
Scorpio_Hook::add('Class_template::loadcsstemplate:Before_fwrite', '_eFunc_writetocsscache_Before_fwrite');

function _eFunc_writetocsscache_Before_fwrite($conf) {

//	if (Scorpio_Hook::$event == 'Class_template::loadcsstemplate:Before_fwrite') {
//		dexit($conf['cssdata']);
//	}

	if($conf['entry'] != 'module.css') {
		$conf['cssdata'] = preg_replace('/\/\*((?:[^\*]*|\*(?!\/)).*)\*\//sU', "\n", $conf['cssdata']);
	}

	$conf['cssdata'] = scotext::lf($conf['cssdata']);
	$conf['cssdata'] = preg_replace(array(
		'/[ \t]*([,;:\{\}]+|\n)[ \t]*/s',
		'/\t+/',
		'/(\n| ){2,}/s',
		'/^(\xef\xbb\xbf)?\s+|\s+$/sU',
		'/(?!^)\xef\xbb\xbf/U',
	), array(
		'\\1',
		' ',
		'\\1',
		'\\1',
		'',
	), $conf['cssdata']);

	(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) && $conf['cssdata'] = scotext::lf($conf['cssdata'], '', "\n");

	$conf['cssdata'] = trim($conf['cssdata']);

//	if (Scorpio_Hook::$event == 'Class_template::loadcsstemplate:Before_fwrite') {
//		dexit($conf['cssdata']);
//	}
}

Scorpio_Hook::add('Func_updatecache:After_lostcaches', '_eFunc_updatecache_After_lostcaches');

function _eFunc_updatecache_After_lostcaches($conf) {
	extract($conf, EXTR_REFS);

	static $loadedcache = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	$caches = array();
	foreach ($cachenames as $k) {
		if(!isset($loadedcache[$k])) {
			$loadedcache[$k] = true;

			if ($k == 'blockclass') {
				require_once libfile('function/block');
				blockclass_cache();
			} else {
				$caches[] = $k;
			}
		}
	}
}

?>