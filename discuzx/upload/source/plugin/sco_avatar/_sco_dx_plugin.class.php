<?php

/**
 * 所有衍生的 plugin class 的 method 盡量以 _my_ 作為開頭
 */
class _sco_dx_plugin {

	var $identifier = null;

	var $module		= null;

	var $attr = array();

	static $instance = null;

	function _init($identifier) {
		$this->identifier = $identifier;
		$this->attr['identifier'] = &$this->identifier;

		$this->attr['module'] = &$this->module;

		$this->attr['directory'] = 'source/plugin/'.$this->identifier.'/';

		$this->_init_setting($this);
	}

	/**
	 * @example $plugin_self = _sco_dx_plugin::_instance($identifier, $module);
	 */
	function &_instance($identifier, $module = null) {
		eval('$obj = new plugin_'.$identifier.'();');

		if (!empty($module)) $obj->_set('module', $module);

		return $obj;
	}

	/**
	 * @return _sco_dx_plugin
	 */
	function _this($_this = null) {
		if ($_this) self::$instance = &$_this;

		return self::$instance;
	}

	function _set($k, $v) {
		if ($k == 'module') {
			$this->module = $v;
			$this->attr['global']['mnid'] = 'plugin_'.$this->identifier.'_'.$this->module;

			$this->_init_pluginmodule();
		} else {
			$this->$k = $v;
		}
	}

	/**
	 * @return _sco_dx_plugin
	 */
	function _setglobal($key , $value, $group = 'global') {
		$_G = &$this->attr;

		$k = explode('/', ($group === null || $group === '') ? $key : $group.'/'.$key);
		switch (count($k)) {
			case 1: $_G[$k[0]] = $value; break;
			case 2: $_G[$k[0]][$k[1]] = $value; break;
			case 3: $_G[$k[0]][$k[1]][$k[2]] = $value; break;
			case 4: $_G[$k[0]][$k[1]][$k[2]][$k[3]] = $value; break;
			case 5: $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] =$value; break;
		}
		return $this;
	}

	function _getglobal($key, $group = 'global') {
		$_G = &$this->attr;

		$k = explode('/', ($group === null || $group === '') ? $key : $group.'/'.$key);
		switch (count($k)) {
			case 1: return isset($_G[$k[0]]) ? $_G[$k[0]] : null; break;
			case 2: return isset($_G[$k[0]][$k[1]]) ? $_G[$k[0]][$k[1]] : null; break;
			case 3: return isset($_G[$k[0]][$k[1]][$k[2]]) ? $_G[$k[0]][$k[1]][$k[2]] : null; break;
			case 4: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]] : null; break;
			case 5: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] : null; break;
		}
		return null;
	}

	function _lang_push($k) {
		array_push(discuz_core::$langplus, $k);
	}

	/**
	 * 載入插件語言包
	 */
	function _lang_load_plugin($k = 'script') {
		global $_G;
		$lang_type = array('script', 'template', 'install');

		$k = in_array($k, $lang_type) ? $k : 'script';

		// 載入語言包
		!isset($_G['cache']['pluginlanguage_'.$k]) && loadcache('pluginlanguage_'.$k);

		foreach ($lang_type as $k) {
			if (isset($_G['cache']['pluginlanguage_'.$k])) {
				$this->attr['lang'][$k] = &$_G['cache']['pluginlanguage_'.$k][$this->identifier];
			}
		}

		return $this;
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

	function _make_id($module = null) {
		return $this->identifier.':'.(empty($module) ? $this->module : $module);
	}

	function _make_url($module = null) {
		return 'plugin.php?id='.$this->_make_id($module);
	}

	function _init_pluginmodule() {
		global $_G;

		$this->attr['pluginmodule'] =
			isset($_G['setting']['pluginlinks'][$this->identifier][$this->module]) ?
				$_G['setting']['pluginlinks'][$this->identifier][$this->module]
				: (isset($_G['setting']['plugins']['script'][$this->identifier][$this->module]) ?
					$_G['setting']['plugins']['script'][$this->identifier][$this->module]
					: array(
						'adminid' => 0,
						'directory' => preg_match("/^[a-z]+[a-z0-9_]*$/i", $this->identifier) ? $this->identifier.'/' : '')
				)
		;
	}

	function _init_setting($identifier) {
		global $_G;

		if(!isset($_G['cache']['plugin'])) {
			loadcache('plugin');
		}

		if (is_object($identifier) && is_a($identifier, '_sco_dx_plugin')) {
			$identifier->attr['setting_source'] = &$_G['cache']['plugin'][$identifier->identifier];
			$identifier->attr['setting'] = $identifier->attr['setting_source'];

			$identifier->_lang_load_plugin();

			// 所有的 plugins jsmenu
			$identifier->attr['plugins']['jsmenu'] = &$_G['setting']['plugins']['jsmenu'];

			// 補充 plugin.php 的 mnid
			if (!isset($_G['setting']['navmn']['plugin.php'])) {
				$_G['setting']['navmn']['plugin.php'] = $_G['setting']['navs'][6]['navid'];
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

	/**
	 * @return {tablepre}plugin_{$this->identifier}_{$tablename}
	 */
	function _table($tablename) {
		return DB::table('plugin_'.$this->identifier.'_'.$tablename);
	}

	function _uc_init() {
		static $loaded;
		if (!isset($loaded)) {
			$loaded = true;
			loaducenter();
		}

		return $this;
	}

	function _uc_call($module, $action, $arg = array()) {
		return uc_api_call($module, $action, $arg);
	}
}

?>