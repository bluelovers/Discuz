<?php

class _sco_dx_plugin {

	var $identifier = null;

	var $module		= null;

	var $attr = array();

	function _init($identifier) {
		$this->identifier = $identifier;
		$this->attr['identifier'] = &$this->identifier;

		$this->attr['module'] = &$this->module;

		$this->attr['directory'] = 'source/plugin/'.$this->identifier.'/';

		$this->_get_setting($this);
	}

	function &_instance($identifier, $module = null) {
		eval('$obj = new plugin_'.$identifier.'();');

		if (isset($module)) $obj->$module = $module;

		return $obj;
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
		}

		return $k;
	}

	function _get_setting($identifier) {
		global $_G;

		if(!isset($_G['cache']['plugin'])) {
			loadcache('plugin');
		}

		if (is_object($identifier) && is_a($identifier, '_sco_dx_plugin')) {
			$identifier->attr['setting_source'] = &$_G['cache']['plugin'][$identifier->identifier];
			$identifier->attr['setting'] = $identifier->attr['setting_source'];

			// 載入語言包
			foreach(array('script', 'template', 'install') as $type) {
				!isset($_G['cache']['pluginlanguage_'.$type]) && loadcache('pluginlanguage_'.$type);
				$identifier->attr['lang'][$type] = &$_G['cache']['pluginlanguage_'.$type][$identifier->identifier];
			}

			return true;
		} elseif (isset($_G['cache']['plugin'][$identifier])) {
			return $_G['cache']['plugin'][$identifier];
		}

		return false;
	}

	function _template($file) {
		$args = func_get_args();

		if (is_array($file)) {
			$args[0] = implode(':', $file);
		} elseif (strpos($file, ':') === false) {
			$args[0] = $this->identifier.':'.$file;
		}

		return call_user_func_array('template', $args);
	}

	/**
	 *
	 * @example
		$data_sco = _loop_glob('./data_sco', '*.sql');
		foreach ($data_sco as $_f) {
			showjsmessage('Load'.' '.$_f.' ... '.lang('succeed'));
			$sql = file_get_contents(ROOT_PATH.'./install/'.$_f);
			$sql = str_replace("\r\n", "\n", $sql);
			runquery($sql);
		}
	 **/
	function _loop_glob($path, $mask = '*', $array = array()) {
		$path = rtrim(str_replace('/./', '/', $path), '/').'/';

		if ($mask != '*') {
			foreach (glob($path.'*', GLOB_ONLYDIR) as $f) {
				$f = str_replace('/./', '/', $f);
				self::_loop_glob($f, $mask, &$array);
			}
		}

		foreach (glob($path.$mask) as $f) {
			$f = str_replace('/./', '/', $f);
			if (is_dir($f)) {
				self::_loop_glob($f, $mask, &$array);
			} else {
				$array[$f] = $f;
			}
		}
		return $array;
	}

}

?>