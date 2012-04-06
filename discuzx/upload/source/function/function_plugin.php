<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_plugin.php 22837 2011-05-25 06:58:35Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function plugininstall($pluginarray, $installtype = '') {
	if(!$pluginarray || !$pluginarray['plugin']['identifier']) {
		return false;
	}
	$plugin = DB::fetch_first("SELECT name, pluginid FROM ".DB::table('common_plugin')." WHERE identifier='{$pluginarray[plugin][identifier]}' LIMIT 1");
	if($plugin) {
		return false;
	}

	$pluginarray['plugin']['modules'] = unserialize(dstripslashes($pluginarray['plugin']['modules']));
	$pluginarray['plugin']['modules']['extra']['installtype'] = $installtype;
	if(updatepluginlanguage($pluginarray)) {
		$pluginarray['plugin']['modules']['extra']['langexists'] = 1;
	}
	if(!empty($pluginarray['intro'])) {
		if(!empty($pluginarray['intro'])) {
			require_once libfile('function/discuzcode');
			$pluginarray['plugin']['modules']['extra']['intro'] = discuzcode(dstripslashes(strip_tags($pluginarray['intro'])), 1, 0);
		}
	}
	$pluginarray['plugin']['modules'] = addslashes(serialize($pluginarray['plugin']['modules']));

	$data = array();
	foreach($pluginarray['plugin'] as $key => $val) {
		if($key == 'directory') {
			$val .= (!empty($val) && substr($val, -1) != '/') ? '/' : '';
		} elseif($key == 'available') {
			$val = 0;
		}
		$data[$key] = $val;
	}

	$pluginid = DB::insert('common_plugin', $data, 1);

	if(is_array($pluginarray['var'])) {
		foreach($pluginarray['var'] as $config) {
			$data = array('pluginid' => $pluginid);
			foreach($config as $key => $val) {
				$data[$key] = $val;
			}
			DB::insert('common_pluginvar', $data);
		}
	}

	if(!empty($dir) && !empty($pluginarray['importfile'])) {
		require_once adminfile('function/importdata');
		foreach($pluginarray['importfile'] as $importtype => $file) {
			if(in_array($importtype, array('smilies', 'styles'))) {
				$files = explode(',', $file);
				foreach($files as $file) {
					if(file_exists($file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/'.$file)) {
						$importtxt = @implode('', file($file));
						$imporfun = 'import_'.$importtype;
						$imporfun();
					}
				}
			}
		}
	}

	updatecache(array('plugin', 'setting', 'styles'));
	return true;
}

function pluginupgrade($pluginarray, $installtype) {
	if(!$pluginarray || !$pluginarray['plugin']['identifier']) {
		return false;
	}
	$plugin = DB::fetch_first("SELECT name, pluginid, modules FROM ".DB::table('common_plugin')." WHERE identifier='{$pluginarray[plugin][identifier]}' LIMIT 1");
	if(!$plugin) {
		return false;
	}
	if(is_array($pluginarray['var'])) {
		$query = DB::query("SELECT variable FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]'");
		$pluginvars = $pluginvarsnew = array();
		while($pluginvar = DB::fetch($query)) {
			$pluginvars[] = $pluginvar['variable'];
		}
		foreach($pluginarray['var'] as $config) {
			if(!in_array($config['variable'], $pluginvars)) {
				$data = array('pluginid' => $plugin[pluginid]);
				foreach($config as $key => $val) {
					$data[$key] = $val;
				}
				DB::insert('common_pluginvar', $data);
			} else {
				$sql = $comma = '';
				foreach($config as $key => $val) {
					if($key != 'value') {
						$sql .= $comma.$key.'=\''.$val.'\'';
						$comma = ',';
					}
				}
				if($sql) {
					DB::query("UPDATE ".DB::table('common_pluginvar')." SET $sql WHERE pluginid='$plugin[pluginid]' AND variable='$config[variable]'");
				}
			}
			$pluginvarsnew[] = $config['variable'];
		}
		$pluginvardiff = array_diff($pluginvars, $pluginvarsnew);
		if($pluginvardiff) {
			DB::query("DELETE FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]' AND variable IN (".dimplode($pluginvardiff).")");
		}
	}

	$langexists = updatepluginlanguage($pluginarray);

	$pluginarray['plugin']['modules'] = unserialize(dstripslashes($pluginarray['plugin']['modules']));
	$plugin['modules'] = unserialize($plugin['modules']);
	if(!empty($plugin['modules']['system'])) {
		$pluginarray['plugin']['modules']['system'] = $plugin['modules']['system'];
	}
	$plugin['modules']['extra']['installtype'] = $installtype;
	$pluginarray['plugin']['modules']['extra'] = $plugin['modules']['extra'];
	if(!empty($pluginarray['intro']) || $langexists) {
		if(!empty($pluginarray['intro'])) {
			require_once libfile('function/discuzcode');
			$pluginarray['plugin']['modules']['extra']['intro'] = discuzcode(dstripslashes(strip_tags($pluginarray['intro'])), 1, 0);
		}
		$langexists && $pluginarray['plugin']['modules']['extra']['langexists'] = 1;
	}
	$pluginarray['plugin']['modules'] = addslashes(serialize($pluginarray['plugin']['modules']));

	DB::query("UPDATE ".DB::table('common_plugin')." SET version='{$pluginarray[plugin][version]}', modules='{$pluginarray[plugin][modules]}' WHERE pluginid='$plugin[pluginid]'");

	updatecache(array('plugin', 'setting', 'styles'));
	return true;
}

function modulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function updatepluginlanguage($pluginarray) {
	global $_G;
	if(!$pluginarray['language']) {
		return false;
	}
	$pluginarray['language'] = dstripslashes($pluginarray['language']);
	foreach(array('script', 'template', 'install') as $type) {
		loadcache('pluginlanguage_'.$type, 1);
		if(!empty($pluginarray['language'][$type.'lang'])) {
			$_G['cache']['pluginlanguage_'.$type][$pluginarray['plugin']['identifier']] = $pluginarray['language'][$type.'lang'];
		}
		save_syscache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
	}
	return true;
}

function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace(array(' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'), $sql);
	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' `{tablepre}'), array(' '.$tablepre, ' `'.$tablepre), $sql));

	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(createtable($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function pluginstat($type, $data) {
	global $_G;
	$url = 'http://stat.discuz.com/plugins.php?action='.$type.'&id='.rawurlencode($data['identifier'].'@'.$_G['setting']['version']).'&version='.rawurlencode($data['version']).'&url='.rawurlencode($_G['siteurl']).'&ip='.$_G['clientip'];
	echo '<iframe src="'.$url.'" style="display:none"></iframe>';
}

function pluginvalidator($identifier) {
	global $importtxt;
	$validatorkey = '';
	if(file_exists(DISCUZ_ROOT.'./source/plugin/'.$identifier.'/validator.xml')) {
		$importtxt = file_get_contents(DISCUZ_ROOT.'./source/plugin/'.$identifier.'/validator.xml');
		$validatorarray = getimportdata('Discuz! Plugin Validator');
		if(!empty($validatorarray) && is_array($validatorarray)) {
			$md5 = '';
			foreach($validatorarray as $file) {
				$md5 .= file_exists(DISCUZ_ROOT.$file) ? @md5_file($file) : '';
			}
			$validatorkey = md5(md5($md5).$identifier);
		}
	}
	return $validatorkey;
}

function pluginupgradecheck($checkdata) {
	$result = array();
	if(!$checkdata) {
		return $result;
	}
	$data = dfsockopen('http://addons.discuz.com/register.php', 0, http_build_query($checkdata), '', false, '', 5);
	$data = explode("\n", $data);
	foreach($data as $row) {
		if(!$row) {
			continue;
		}
		list($id, $value) = explode("\t", $row);
		if(!ispluginkey($id)) {
			continue;
		}
		$valuee = explode(':', $value);
		$result[$id]['result'] = $valuee[0];
		$result[$id]['newver'] = $valuee[1];
	}
	return $result;
}

?>