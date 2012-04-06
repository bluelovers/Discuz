<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms'
 *
 *      $Id: admincp_plugins.php 28784 2012-03-13 02:34:22Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!empty($_G['gp_identifier']) && !empty($_G['gp_pmod'])) {
	$operation = 'config';
}

if($operation != 'config' && !$admincp->isfounder) {
	cpmsg('noaccess_isfounder', '', 'error');
}

$pluginid = !empty($_G['gp_pluginid']) ? intval($_G['gp_pluginid']) : 0;
$anchor = !empty($_G['gp_anchor']) ? $_G['gp_anchor'] : '';
$isplugindeveloper = isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] > 0;
if(!empty($_G['gp_dir']) && !ispluginkey($_G['gp_dir'])) {
	unset($_G['gp_dir']);
}

require_once libfile('function/plugin');
require_once libfile('function/cloudaddons');

if(!$operation) {

	if(!submitcheck('submit')) {

		loadcache('plugin');
		shownav('plugin');
		showsubmenu('nav_plugins', array(
			array('plugins_list', 'plugins', 1),
			array('plugins_install', 'plugins&operation=import', 0),
			$isplugindeveloper ? array('plugins_add', 'plugins&operation=add', 0) : array(),
			array('cloudaddons_plugin_link', 'cloudaddons'),
		), '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgradecheck" class="bold" style="float:right;padding-right:40px;">'.$lang['plugins_validator'].'</a>');
		showformheader('plugins');
		showtableheader();
		$query = DB::query("SELECT * FROM ".DB::table('common_plugin')." ORDER BY available DESC, pluginid DESC");
		$outputsubmit = false;
		while($plugin = DB::fetch($query)) {
			$hookexists = FALSE;
			$plugin['modules'] = unserialize($plugin['modules']);
			$submenuitem = array();
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $k => $module) {
					if($module['type'] == 11) {
						$hookorder = $module['displayorder'];
						$hookexists = $k;
					}
					if($module['type'] == 3) {
						$submenuitem[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'&identifier='.$plugin['identifier'].'&pmod='.$module['name'].'" class="act">'.$module['menu'].'</a>';
					}
				}
			}
			$intro = '';
			if($plugin['description'] || $plugin['modules']['extra']['intro'] || $plugin['copyright']) {
				$intro = '<div id="pluginmore'.$plugin['pluginid'].'" style="display:none;margin:0 5px 0 50px"'.(!$plugin['available'] ? ' class="light"' : '').'>'.
					$lang['copyright'].': '.($plugin['copyright'] ? '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$plugin['identifier'].'.plugin" target="_blank" class="'.(!$plugin['available'] ? 'light' : 'normalfont').'">'.dhtmlspecialchars($plugin['copyright']).'</a>' : '').'<br />'.
					(!empty($plugin['modules']['extra']['intro']) ? ($plugin['copyright'] ? '<br />' : '').$plugin['modules']['extra']['intro'].'<br />' : '').nl2br($plugin['description']).
					'</div>';
			}
			$outputsubmit = $hookexists !== FALSE && $plugin['available'] || $outputsubmit;
			showtablerow('class="hover"', array(), array(
				'<img src="'.cloudaddons_pluginlogo_url($plugin['identifier']).'" onerror="this.src=\'static/image/admincp/plugin_logo.png\';this.onerror=null" width="40" height="40" align="left" style="margin-right:5px" />'.
				($hookexists !== FALSE && $plugin['available'] ? '<div class="right">'.$lang['display_order'].": <input class=\"txt num\" type=\"text\" id=\"displayorder_$plugin[pluginid]\" name=\"displayordernew[$plugin[pluginid]][$hookexists]\" value=\"$hookorder\" />" : '').'</div>'.
				(!$plugin['available'] ? '<span class="light">' : '<span class="bold">').dhtmlspecialchars($plugin['name']).' '.dhtmlspecialchars($plugin['version']).'<br /><span class="sml">'.$plugin['identifier'].'</span></span>'.
				'<div><div class="right">'.
					($plugin['modules']['system'] != 2 ? (!$plugin['available'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=enable&pluginid=$plugin[pluginid]\" class=\"bold act\">$lang[enable]</a>&nbsp;" : "<a href=\"".ADMINSCRIPT."?action=plugins&operation=disable&pluginid=$plugin[pluginid]\" class=\"act\">$lang[closed]</a>&nbsp;") : '').
					"<a href=\"".ADMINSCRIPT."?action=plugins&operation=upgrade&pluginid=$plugin[pluginid]\" class=\"act\">$lang[plugins_config_upgrade]</a>&nbsp;".
					(!$plugin['modules']['system'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=delete&pluginid=$plugin[pluginid]\" class=\"act\">$lang[plugins_config_uninstall]</a>&nbsp;" : '').
					($isplugindeveloper && !$plugin['modules']['system'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=edit&pluginid=$plugin[pluginid]\" class=\"act\">$lang[plugins_editlink]</a>&nbsp;" : '').
				'</div>'.
				($intro ? '<a href="javascript:;" onclick="display(\'pluginmore'.$plugin['pluginid'].'\')" class="act'.(!$plugin['available'] ? ' light' : '').'">'.$lang['plugins_home'].'</a>&nbsp;' : '').
				(isset($_G['cache']['plugin'][$plugin['identifier']]) ? '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'" class="act">'.$lang['config'].'</a>&nbsp;' : '').
				implode('&nbsp;', $submenuitem).'&nbsp;&nbsp;'.
				'</div>'.
				$intro
			));
		}

		if($outputsubmit) {
			showsubmit('submit', 'submit');
		} else {
			showsubmit();
		}
		showtablefooter();
		showformfooter();

	} else {

		$query = DB::query("SELECT pluginid, modules FROM ".DB::table('common_plugin')." WHERE available='1'");
		while($plugin = DB::fetch($query)) {
			if(!empty($_G['gp_displayordernew'][$plugin['pluginid']])) {
				$plugin['modules'] = unserialize($plugin['modules']);
				$k = array_keys($_G['gp_displayordernew'][$plugin['pluginid']]);
				$v = array_values($_G['gp_displayordernew'][$plugin['pluginid']]);
				$plugin['modules'][$k[0]]['displayorder'] = $v[0];
				$plugin['modules'] = addslashes(serialize($plugin['modules']));
				DB::query("UPDATE ".DB::table('common_plugin')." SET modules='".$plugin['modules']."' WHERE pluginid='$plugin[pluginid]'");
			}
		}

		updatecache(array('plugin', 'setting', 'styles'));

		cpmsg('plugins_edit_succeed', 'action=plugins', 'succeed');

	}

} elseif($operation == 'enable' || $operation == 'disable') {

	$conflictplugins = '';
	if($operation == 'enable') {
		$plugin = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin')." WHERE pluginid='$_G[gp_pluginid]'");
		if(!$plugin) {
			cpmsg('plugin_not_found', '', 'error');
		}

		require_once libfile('cache/setting', 'function');
		list(,, $hookscript) = get_cachedata_setting_plugin($plugin['identifier']);
		$exists = array();
		foreach($hookscript as $script => $modules) {
			foreach($modules as $module => $data) {
				foreach(array('funcs' => '', 'outputfuncs' => '_output', 'messagefuncs' => '_message') as $functype => $funcname) {
					foreach($data[$functype] as $k => $funcs) {
						$pluginids = array();
						foreach($funcs as $func) {
							$pluginids[$func[0]] = $func[0];
						}
						if(in_array($plugin['identifier'], $pluginids) && count($pluginids) > 1) {
							unset($pluginids[$plugin['identifier']]);
							foreach($pluginids as $pluginid) {
								$exists[$pluginid][$k.$funcname] = $k.$funcname;
							}
						}
					}
				}
			}
		}
		if($exists) {
			$plugins = array();
			$query = DB::query("SELECT identifier, name FROM ".DB::table('common_plugin')." WHERE identifier IN (".dimplode(array_keys($exists)).")");
			while($plugin = DB::fetch($query)) {
				$plugins[] = '<b>'.$plugin['name'].'</b>:'.
					'&nbsp;<a href="javascript:;" onclick="display(\'conflict_'.$plugin['identifier'].'\')">'.cplang('plugins_conflict_view').'</a>'.
					'&nbsp;<a href="'.cloudaddons_pluginlogo_url($plugin['identifier']).'" target="_blank">'.cplang('plugins_conflict_info').'</a>'.
					'<span id="conflict_'.$plugin['identifier'].'" style="display:none"><br />'.implode(',', $exists[$plugin['identifier']]).'</span>';
			}
			$conflictplugins = '<div align="left" style="margin: auto 100px; border: 1px solid #DEEEFA;padding: 4px;line-height: 25px;">'.implode('<br />', $plugins).'</div>';
		}
	}
	$available = $operation == 'enable' ? 1 : 0;
	DB::query("UPDATE ".DB::table('common_plugin')." SET available='$available' WHERE pluginid='$_G[gp_pluginid]'");
	updatecache(array('plugin', 'setting', 'styles'));
	updatemenu('plugin');
	if($operation == 'enable') {
		if(!$conflictplugins) {
			cpmsg('plugins_enable_succeed', 'action=plugins', 'succeed');
		} else {
			cpmsg('plugins_conflict', 'action=plugins', 'succeed', array('plugins' => $conflictplugins));
		}
	} else {
		cpmsg('plugins_disable_succeed', 'action=plugins', 'succeed');
	}
	cpmsg('plugins_'.$operation.'_succeed', 'action=plugins', 'succeed');

} elseif($operation == 'export' && $pluginid) {

	if(!$isplugindeveloper) {
		cpmsg('undefined_action', '', 'error');
	}

	$plugin = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin')." WHERE pluginid='$pluginid'");
	if(!$plugin) {
		cpheader();
		cpmsg('plugin_not_found', '', 'error');
	}

	unset($plugin['pluginid']);

	$pluginarray = array();
	$pluginarray['plugin'] = $plugin;
	$pluginarray['version'] = strip_tags($_G['setting']['version']);

	$query = DB::query("SELECT * FROM ".DB::table('common_pluginvar')." WHERE pluginid='$pluginid'");
	while($var = DB::fetch($query)) {
		unset($var['pluginvarid'], $var['pluginid']);
		$pluginarray['var'][] = $var;
	}
	$modules = unserialize($pluginarray['plugin']['modules']);
	if($modules['extra']['langexists'] && file_exists($file = DISCUZ_ROOT.'./data/plugindata/'.$pluginarray['plugin']['identifier'].'.lang.php')) {
		include $file;
		if(!empty($scriptlang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['scriptlang'] = $scriptlang[$pluginarray['plugin']['identifier']];
		}
		if(!empty($templatelang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['templatelang'] = $templatelang[$pluginarray['plugin']['identifier']];
		}
		if(!empty($installlang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['installlang'] = $installlang[$pluginarray['plugin']['identifier']];
		}
	}
	unset($modules['extra']);
	$pluginarray['plugin']['modules'] = serialize($modules);
	$plugindir = DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['directory'];
	if(file_exists($plugindir.'/install.php')) {
		$pluginarray['installfile'] = 'install.php';
	}
	if(file_exists($plugindir.'/uninstall.php')) {
		$pluginarray['uninstallfile'] = 'uninstall.php';
	}
	if(file_exists($plugindir.'/upgrade.php')) {
		$pluginarray['upgradefile'] = 'upgrade.php';
	}
	if(file_exists($plugindir.'/check.php')) {
		$pluginarray['checkfile'] = 'check.php';
	}

	exportdata('Discuz! Plugin', $plugin['identifier'], $pluginarray);

} elseif($operation == 'import') {

	if(!submitcheck('importsubmit') && !isset($_G['gp_dir'])) {

		shownav('plugin');
		showsubmenu('nav_plugins', array(
			array('plugins_list', 'plugins', 0),
			array('plugins_install', 'plugins&operation=import', 1),
			$isplugindeveloper ? array('plugins_add', 'plugins&operation=add', 0) : array(),
		));

		$query = DB::query("SELECT * FROM ".DB::table('common_plugin')." ORDER BY available DESC, pluginid DESC");
		$installsdir = array();
		while($plugin = DB::fetch($query)) {
			$installsdir[] = $plugin['directory'];
		}

		showtableheader();
		echo '<tr><td><div class="ofolder">source/plugin/</div></td><td></td></tr>';
		$plugindir = DISCUZ_ROOT.'./source/plugin';
		$pluginsdir = dir($plugindir);
		$newplugins = array();
		while($entry = $pluginsdir->read()) {
			if(!in_array($entry, array('.', '..')) && is_dir($plugindir.'/'.$entry) && !in_array($entry.'/', $installsdir)) {
				$entrydir = DISCUZ_ROOT.'./source/plugin/'.$entry;
				$d = dir($entrydir);
				$filemtime = filemtime($entrydir);
				while($f = $d->read()) {
					if(preg_match('/^discuz\_plugin\_'.$entry.'(\_\w+)?\.xml$/', $f)) {
						$entrytitle = $entry;
						$entryversion = $entrycopyright = '';
						if(file_exists($entrydir.'/discuz_plugin_'.$entry.'.xml')) {
							$importtxt = @implode('', file($entrydir.'/discuz_plugin_'.$entry.'.xml'));
							$pluginarray = getimportdata('Discuz! Plugin', 1, 1);
							if(!empty($pluginarray['plugin']['name'])) {
								$entrytitle = dhtmlspecialchars($pluginarray['plugin']['name']);
								$entryversion = dhtmlspecialchars($pluginarray['plugin']['version']);
								$entrycopyright = dhtmlspecialchars($pluginarray['plugin']['copyright']);
							}
						}
						$file = $entrydir.'/'.$f;
						showtablerow('class="hover"', array('width="40%"', '', 'align="right"'), array(
							'<img src="'.cloudaddons_pluginlogo_url($entry).'.png" onerror="this.src=\'static/image/admincp/plugin_logo.png\';this.onerror=null" width="40" height="40" align="left" style="margin:0 5px 0 30px" />'.
							$entrytitle.' '.$entryversion.($filemtime > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : '').'<br />'.
							'<span class="sml">'.$entry.'</span>',
							$entrycopyright,
							'<a href="'.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$entry.'" class="bold act">'.$lang['plugins_config_install'].'</a>'
						));
						break;
					}
				}
			}
		}
		showsubmit();
		showtablefooter();
		echo '<br />';

	} else {

		cloudaddons_validator($_G['gp_dir'].'.plugin');

		if(!isset($_G['gp_dir'])) {
			$pluginarray = getimportdata('Discuz! Plugin');
		} elseif(!isset($_G['gp_installtype'])) {
			$pdir = DISCUZ_ROOT.'./source/plugin/'.$_G['gp_dir'];
			$d = dir($pdir);
			$xmls = '';$count = 0;
			$referer = dreferer();
			while($f = $d->read()) {
				if(preg_match('/^discuz\_plugin_'.$_G['gp_dir'].'(\_\w+)?\.xml$/', $f, $a)) {
					$extratxt = $extra = substr($a[1], 1);
					if(preg_match('/^SC\_GBK$/i', $extra)) {
						$extratxt = '&#31616;&#20307;&#20013;&#25991;&#29256;';
					} elseif(preg_match('/^SC\_UTF8$/i', $extra)) {
						$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
					} elseif(preg_match('/^TC\_BIG5$/i', $extra)) {
						$extratxt = '&#32321;&#39636;&#20013;&#25991;&#29256;';
					} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
						$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
					}
					$url = ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_G['gp_dir'].'&installtype='.rawurlencode($extra).(!empty($referer) ? '&referer='.rawurlencode($referer) : '');
					$xmls .= '&nbsp;<input type="button" class="btn" onclick="location.href=\''.$url.'\'" value="'.($extra ? $extratxt : $lang['plugins_import_default']).'">&nbsp;';
					$count++;
				}
			}
			$xmls .= '<br /><br /><input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=import\'" type="button" value="'.$lang['cancel'].'"/>';
			if($count == 1) {
				dheader('location: '.$url);
			}
			echo '<div class="infobox"><h4 class="infotitle2">'.$lang['plugins_import_installtype_1'].' '.$_G['gp_dir'].' '.$lang['plugins_import_installtype_2'].' '.$count.' '.$lang['plugins_import_installtype_3'].'</h4>'.$xmls.'</div>';
			exit;
		} else {
			$installtype = $_G['gp_installtype'];
			$dir = $_G['gp_dir'];
			$license = $_G['gp_license'];
			$extra = $installtype ? '_'.$installtype : '';
			$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.$extra.'.xml';
			$importtxt = @implode('', file($importfile));
			$pluginarray = getimportdata('Discuz! Plugin');
			if(empty($license) && $pluginarray['license']) {
				require_once libfile('function/discuzcode');
				$pluginarray['license'] = discuzcode(dstripslashes(strip_tags($pluginarray['license'])), 1, 0);
				echo '<div class="infobox"><h4 class="infotitle2">'.$pluginarray['plugin']['name'].' '.$pluginarray['plugin']['version'].' '.$lang['plugins_import_license'].'</h4><div style="text-align:left;line-height:25px;">'.$pluginarray['license'].'</div><br /><br /><center>'.
					'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$dir.'&installtype='.$installtype.'&license=yes\'">'.$lang['plugins_import_agree'].'</button>&nbsp;&nbsp;'.
					'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'">'.$lang['plugins_import_pass'].'</button></center></div>';
				exit;
			}
		}

		if(!ispluginkey($pluginarray['plugin']['identifier'])) {
			cpmsg('plugins_edit_identifier_invalid', '', 'error');
		}
		if(!ispluginkey($pluginarray['plugin']['identifier'])) {
			cpmsg('plugins_edit_identifier_invalid', '', 'error');
		}
		if(is_array($pluginarray['vars'])) {
			foreach($pluginarray['vars'] as $config) {
				if(!ispluginkey($config['variable'])) {
					cpmsg('plugins_import_var_invalid', '', 'error');
				}
			}
		}

		$plugin = DB::fetch_first("SELECT name, pluginid FROM ".DB::table('common_plugin')." WHERE identifier='{$pluginarray[plugin][identifier]}' LIMIT 1");
		if($plugin) {
			cpmsg('plugins_import_identifier_duplicated', '', 'error', array('plugin_name' => $plugin['name']));
		}

		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			$filename = DISCUZ_ROOT.'./source/plugin/'.$_G['gp_dir'].'/'.$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $pluginarray['language']['installlang'];
				@include $filename;
			}
		}

		if(empty($_GET['ignoreversion']) && !in_array(strip_tags($_G['setting']['version']), explode(',', strip_tags($pluginarray['version'])))) {
			if(isset($dir)) {
				cpmsg('plugins_import_version_invalid_confirm', 'action=plugins&operation=import&ignoreversion=yes&dir='.$dir.'&installtype='.$installtype.'&license='.$license, 'form', array('cur_version' => $pluginarray['version'], 'set_version' => $_G['setting']['version']));
			} else {
				cpmsg('plugins_import_version_invalid', '', 'error', array('cur_version' => $pluginarray['version'], 'set_version' => $_G['setting']['version']));
			}
		}

		plugininstall($pluginarray, $installtype);

		updatemenu('plugin');

		if(!empty($dir) && !empty($pluginarray['installfile']) && preg_match('/^[\w\.]+$/', $pluginarray['installfile'])) {
			dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=plugininstall&dir='.$dir.'&installtype='.$installtype);
		}

		if(!empty($dir)) {
			cpmsg('plugins_install_succeed', 'action=plugins', 'succeed');
		} else {
			cpmsg('plugins_import_succeed', 'action=plugins', 'succeed');
		}

	}

} elseif($operation == 'plugininstall' || $operation == 'pluginuninstall' || $operation == 'pluginupgrade') {

	$finish = FALSE;
	$dir = $_G['gp_dir'];
	$installtype = str_replace('/', '', $_G['gp_installtype']);
	$extra = $installtype ? '_'.$installtype : '';
	$xmlfile = 'discuz_plugin_'.$dir.$extra.'.xml';
	$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/'.$xmlfile;
	if(!file_exists($importfile)) {
		cpmsg('plugin_file_error', '', 'error');
	}
	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin');
	if($operation == 'plugininstall') {
		$filename = $pluginarray['installfile'];
	} elseif($operation == 'pluginuninstall') {
		$filename = $pluginarray['uninstallfile'];
	} else {
		$filename = $pluginarray['upgradefile'];
		$toversion = $pluginarray['plugin']['version'];
	}
	loadcache('pluginlanguage_install');
	$installlang = $_G['cache']['pluginlanguage_install'][$dir];

	if(!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
		$filename = DISCUZ_ROOT.'./source/plugin/'.$dir.'/'.$filename;
		if(file_exists($filename)) {
			@include_once $filename;
		} else {
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}

	if($finish) {
		updatecache('setting');
		updatemenu('plugin');
		if($operation == 'plugininstall') {
			cpmsg('plugins_install_succeed', "action=plugins", 'succeed');
		} if($operation == 'pluginuninstall') {
			loadcache('pluginlanguage_install', 1);
			if(isset($_G['cache']['pluginlanguage_install'][$identifier])) {
				unset($_G['cache']['pluginlanguage_install'][$identifier]);
				save_syscache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
			}
			require_once libfile('function/cloudaddons');
			cloudaddons_uninstall($dir.'.plugin', DISCUZ_ROOT.'./source/plugin/'.$dir);
			cpmsg('plugins_delete_succeed', "action=plugins", 'succeed');
		} else {
			cpmsg('plugins_upgrade_succeed', "action=plugins", 'succeed', array('toversion' => $toversion));
		}
	}

} elseif($operation == 'upgrade') {

	$plugin = DB::fetch_first("SELECT name, identifier, directory, modules, version FROM ".DB::table('common_plugin')." WHERE pluginid='$pluginid'");
	$modules = unserialize($plugin['modules']);
	$dir = substr($plugin['directory'], 0, -1);

	if(!$_G['gp_confirmed']) {

		$file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : '').'.xml';
		$upgrade = false;
		if(file_exists($file)) {
			$importtxt = @implode('', file($file));
			$pluginarray = getimportdata('Discuz! Plugin');
			$newver = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
			$upgrade = $newver > $plugin['version'] ? true : false;
		}
		$entrydir = DISCUZ_ROOT.'./source/plugin/'.$dir;
		$upgradestr = '';
		if(file_exists($entrydir)) {
			$d = dir($entrydir);
			while($f = $d->read()) {
				if(preg_match('/^discuz\_plugin\_'.$plugin['identifier'].'(\_\w+)?\.xml$/', $f, $a)) {
					$extratxt = $extra = substr($a[1], 1);
					if(preg_match('/^SC\_GBK$/i', $extra)) {
						$extratxt = '&#31616;&#20307;&#20013;&#25991;&#29256;';
					} elseif(preg_match('/^SC\_UTF8$/i', $extra)) {
						$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
					} elseif(preg_match('/^TC\_BIG5$/i', $extra)) {
						$extratxt = '&#32321;&#39636;&#20013;&#25991;&#29256;';
					} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
						$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
					}
					if($modules['extra']['installtype'] == $extratxt) {
						continue;
					}
					$importtxt = @implode('', file($entrydir.'/'.$f));
					$pluginarray = getimportdata('Discuz! Plugin');
					$newverother = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
					$upgradestr .= $newverother > $plugin['version'] ? '<input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$pluginid.'&confirmed=yes&installtype='.rawurlencode($extra).'\'" type="button" value="'.($extra ? $extratxt : $lang['plugins_import_default']).' '.$newverother.'" />&nbsp;&nbsp;&nbsp;' : '';
				}
			}
		}
		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			$filename = DISCUZ_ROOT.'./source/plugin/'.$plugin['identifier'].'/'.$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
				@include $filename;
			}
		}

		if($upgrade) {

			cpmsg('plugins_config_upgrade_confirm', 'action=plugins&operation=upgrade&pluginid='.$pluginid.'&confirm=yes', 'form', array('pluginname' => $plugin['name'], 'version' => $plugin['version'], 'toversion' => $newver));

		} elseif($upgradestr) {

			echo '<h3>'.cplang('discuz_message').'</h3><div class="infobox"><h4 class="marginbot normal">'.cplang('plugins_config_upgrade_other', array('pluginname' => $plugin['name'], 'version' => $plugin['version'])).'</h4><br /><p class="margintop">'.$upgradestr.
				'<input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'" type="button" value="'.$lang['cancel'].'"/></div></div>';

		} else {

			cpmsg('plugins_config_upgrade_missed', 'action=plugins');

		}

	} else {

		$installtype = !isset($_G['gp_installtype']) ? $modules['extra']['installtype'] : (preg_match('/^\w+$/', $_G['gp_installtype']) ? $_G['gp_installtype'] : '');
		$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($installtype ? '_'.$installtype : '').'.xml';
		if(!file_exists($importfile)) {
			cpmsg('plugin_file_error', '', 'error');
		}

		cloudaddons_validator($dir.'.plugin');

		$importtxt = @implode('', file($importfile));
		$pluginarray = getimportdata('Discuz! Plugin');

		if(!ispluginkey($pluginarray['plugin']['identifier']) || $pluginarray['plugin']['identifier'] != $plugin['identifier']) {
			cpmsg('plugins_edit_identifier_invalid', '', 'error');
		}
		if(is_array($pluginarray['vars'])) {
			foreach($pluginarray['vars'] as $config) {
				if(!ispluginkey($config['variable'])) {
					cpmsg('plugins_upgrade_var_invalid', '', 'error');
				}
			}
		}

		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			if(!empty($pluginarray['language'])) {
				$installlang[$pluginarray['plugin']['identifier']] = dstripslashes($pluginarray['language']['installlang']);
			}
			$filename = DISCUZ_ROOT.'./source/plugin/'.$plugin['directory'].$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
				@include $filename;
			}
		}

		pluginupgrade($pluginarray, $installtype);

		if(!empty($plugin['directory']) && !empty($pluginarray['upgradefile']) && preg_match('/^[\w\.]+$/', $pluginarray['upgradefile'])) {
			dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=pluginupgrade&dir='.$dir.'&installtype='.$modules['extra']['installtype'].'&fromversion='.$plugin['version']);
		}
		$toversion = $pluginarray['plugin']['version'];

		cpmsg('plugins_upgrade_succeed', "action=plugins", 'succeed', array('toversion' => $toversion));

	}

} elseif($operation == 'config') {

	if(empty($pluginid) && !empty($do)) {
		$pluginid = $do;
	}
	$plugin = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin')." WHERE ".($_G['gp_identifier'] ? "identifier='$_G[gp_identifier]'" : "pluginid='$pluginid'"));
	if(!$plugin) {
		cpmsg('plugin_not_found', '', 'error');
	} else {
		$pluginid = $plugin['pluginid'];
	}

	$plugin['modules'] = unserialize($plugin['modules']);

	$pluginvars = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_pluginvar')." WHERE pluginid='$pluginid' ORDER BY displayorder");
	while($var = DB::fetch($query)) {
		if(strexists($var['type'], '_')) {
			continue;
		}
		$pluginvars[$var['variable']] = $var;
	}

	if($pluginvars) {
		$submenuitem[] = array('config', "plugins&operation=config&do=$pluginid", !$_G['gp_pmod']);
	}
	if(is_array($plugin['modules'])) {
		foreach($plugin['modules'] as $module) {
			if($module['type'] == 3) {
				if(!$pluginvars && empty($_G['gp_pmod'])) {
					$_G['gp_pmod'] = $module['name'];
				}
				$submenuitem[] = array($module['menu'], "plugins&operation=config&do=$pluginid&identifier=$plugin[identifier]&pmod=$module[name]", $_G['gp_pmod'] == $module['name'], !$_G['gp_pmod'] ? 1 : 0);
			}
		}
	}

	if(empty($_G['gp_pmod'])) {

		if(!submitcheck('editsubmit')) {
			$operation = '';
			shownav('plugin', $plugin['name']);
			showsubmenuanchors($plugin['name'].' '.$plugin['version'].(!$plugin['available'] ? ' ('.$lang['plugins_unavailable'].')' : ''), $submenuitem);

			if($pluginvars) {
				showformheader("plugins&operation=config&do=$pluginid");
				showtableheader();
				showtitle($lang['plugins_config']);

				$extra = array();
				foreach($pluginvars as $var) {
					if(strexists($var['type'], '_')) {
						continue;
					}
					$var['variable'] = 'varsnew['.$var['variable'].']';
					if($var['type'] == 'number') {
						$var['type'] = 'text';
					} elseif($var['type'] == 'select') {
						$var['type'] = "<select name=\"$var[variable]\">\n";
						foreach(explode("\n", $var['extra']) as $key => $option) {
							$option = trim($option);
							if(strpos($option, '=') === FALSE) {
								$key = $option;
							} else {
								$item = explode('=', $option);
								$key = trim($item[0]);
								$option = trim($item[1]);
							}
							$var['type'] .= "<option value=\"".dhtmlspecialchars($key)."\" ".($var['value'] == $key ? 'selected' : '').">$option</option>\n";
						}
						$var['type'] .= "</select>\n";
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'selects') {
						$var['value'] = unserialize($var['value']);
						$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);
						$var['type'] = "<select name=\"$var[variable][]\" multiple=\"multiple\" size=\"10\">\n";
						foreach(explode("\n", $var['extra']) as $key => $option) {
							$option = trim($option);
							if(strpos($option, '=') === FALSE) {
								$key = $option;
							} else {
								$item = explode('=', $option);
								$key = trim($item[0]);
								$option = trim($item[1]);
							}
							$var['type'] .= "<option value=\"".dhtmlspecialchars($key)."\" ".(in_array($key, $var['value']) ? 'selected' : '').">$option</option>\n";
						}
						$var['type'] .= "</select>\n";
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'date') {
						$var['type'] = 'calendar';
						$extra['date'] = '<script type="text/javascript" src="static/js/calendar.js"></script>';
					} elseif($var['type'] == 'datetime') {
						$var['type'] = 'calendar';
						$var['extra'] = 1;
						$extra['date'] = '<script type="text/javascript" src="static/js/calendar.js"></script>';
					} elseif($var['type'] == 'forum') {
						require_once libfile('function/forumlist');
						$var['type'] = '<select name="'.$var['variable'].'"><option value="">'.cplang('plugins_empty').'</option>'.forumselect(FALSE, 0, $var['value'], TRUE).'</select>';
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'forums') {
						$var['description'] = ($var['description'] ? (isset($lang[$var['description']]) ? $lang[$var['description']] : $var['description']).'<br />' : '').$lang['plugins_edit_vars_multiselect_comment'].'<br />'.$var['comment'];
						$var['value'] = unserialize($var['value']);
						$var['value'] = is_array($var['value']) ? $var['value'] : array();
						require_once libfile('function/forumlist');
						$var['type'] = '<select name="'.$var['variable'].'[]" size="10" multiple="multiple"><option value="">'.cplang('plugins_empty').'</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
						foreach($var['value'] as $v) {
							$var['type'] = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $var['type']);
						}
						$var['variable'] = $var['value'] = '';
					} elseif(substr($var['type'], 0, 5) == 'group') {
						if($var['type'] == 'groups') {
							$var['description'] = ($var['description'] ? (isset($lang[$var['description']]) ? $lang[$var['description']] : $var['description']).'<br />' : '').$lang['plugins_edit_vars_multiselect_comment'].'<br />'.$var['comment'];
							$var['value'] = unserialize($var['value']);
							$var['type'] = '<select name="'.$var['variable'].'[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $var['value']) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>';
						} else {
							$var['type'] = '<select name="'.$var['variable'].'"><option value="">'.cplang('plugins_empty').'</option>';
						}
						$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);

						$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
						$groupselect = array();
						while($group = DB::fetch($query)) {
							$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
							$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $var['value']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
						}
						$var['type'] .= '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
							($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
							($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
							'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'extcredit') {
						$var['type'] = '<select name="'.$var['variable'].'"><option value="">'.cplang('plugins_empty').'</option>';
						foreach($_G['setting']['extcredits'] as $id => $credit) {
							$var['type'] .= '<option value="'.$id.'"'.($var['value'] == $id ? ' selected' : '').'>'.$credit['title'].'</option>';
						}
						$var['type'] .= '</select>';
						$var['variable'] = $var['value'] = '';
					}

					showsetting(isset($lang[$var['title']]) ? $lang[$var['title']] : $var['title'], $var['variable'], $var['value'], $var['type'], '', 0, isset($lang[$var['description']]) ? $lang[$var['description']] : nl2br($var['description']), $var['extra']);
				}
				showsubmit('editsubmit');
				showtablefooter();
				showformfooter();
				echo implode('', $extra);
			}

		} else {

			if(is_array($_G['gp_varsnew'])) {
				foreach($_G['gp_varsnew'] as $variable => $value) {
					if(isset($pluginvars[$variable])) {
						if($pluginvars[$variable]['type'] == 'number') {
							$value = (float)$value;
						} elseif(in_array($pluginvars[$variable]['type'], array('forums', 'groups', 'selects'))) {
							$value = addslashes(serialize($value));
						}
						DB::query("UPDATE ".DB::table('common_pluginvar')." SET value='$value' WHERE pluginid='$pluginid' AND variable='$variable'");
					}
				}
			}

			updatecache(array('plugin', 'setting', 'styles'));
			cpmsg('plugins_setting_succeed', 'action=plugins&operation=config&do='.$pluginid.'&anchor='.$anchor, 'succeed');

		}

	} else {

		$scriptlang[$plugin['identifier']] = lang('plugin/'.$plugin['identifier']);
		$modfile = '';
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $module) {
				if($module['type'] == 3 && $module['name'] == $_G['gp_pmod']) {
					$plugin['directory'] .= (!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '';
					$modfile = './source/plugin/'.$plugin['directory'].$module['name'].'.inc.php';
					break;
				}
			}
		}

		if($modfile) {
			shownav('plugin', $plugin['name']);
			showsubmenu($plugin['name'].' '.$plugin['version'].(!$plugin['available'] ? ' ('.$lang['plugins_unavailable'] : ''), $submenuitem);
			if(!@include(DISCUZ_ROOT.$modfile)) {
				cpmsg('plugins_setting_module_nonexistence', '', 'error', array('modfile' => $modfile));
			} else {
				exit();
			}
		} else {
			cpmsg('plugin_file_error', '', 'error');
		}

	}

} elseif($operation == 'add') {

	if(!$isplugindeveloper) {
		cpmsg('undefined_action', '', 'error');
	}

	if(!submitcheck('addsubmit')) {
		shownav('plugin');
		showsubmenu('nav_plugins', array(
			array('plugins_list', 'plugins', 0),
			array('plugins_install', 'plugins&operation=import', 0),
			array('plugins_add', 'plugins&operation=add', 1)
		));
		showtips('plugins_add_tips');

		showformheader("plugins&operation=add", '', 'configform');
		showtableheader();
		showsetting('plugins_edit_name', 'namenew', '', 'text');
		showsetting('plugins_edit_version', 'versionnew', '', 'text');
		showsetting('plugins_edit_copyright', 'copyrightnew', '', 'text');
		showsetting('plugins_edit_identifier', 'identifiernew', '', 'text');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();
	} else {
		$namenew	= dhtmlspecialchars(trim($_G['gp_namenew']));
		$versionnew	= strip_tags(trim($_G['gp_versionnew']));
		$identifiernew	= trim($_G['gp_identifiernew']);
		$copyrightnew	= dhtmlspecialchars($_G['gp_copyrightnew']);

		if(!$namenew) {
			cpmsg('plugins_edit_name_invalid', '', 'error');
		} else {
			$query = DB::query("SELECT pluginid FROM ".DB::table('common_plugin')." WHERE identifier='$identifiernew' LIMIT 1");
			if(DB::num_rows($query) || !ispluginkey($identifiernew)) {
				cpmsg('plugins_edit_identifier_invalid', '', 'error');
			}
		}
		$data = array(
			'name' => $namenew,
			'version' => $versionnew,
			'identifier' => $identifiernew,
			'directory' => $identifiernew.'/',
			'available' => 0,
			'copyright' => $copyrightnew,
		);
		$pluginid = DB::insert('common_plugin', $data, 1);
		updatecache(array('plugin', 'setting', 'styles'));
		cpmsg('plugins_add_succeed', "action=plugins&operation=edit&pluginid=$pluginid", 'succeed');
	}

} elseif($operation == 'edit') {

	if(!$isplugindeveloper) {
		cpmsg('undefined_action', '', 'error');
	}

	if(empty($pluginid) ) {
		$pluginlist = '<select name="pluginid">';
		$query = DB::query("SELECT pluginid, name FROM ".DB::table('common_plugin')."");
		while($plugin = DB::fetch($query)) {
			$pluginlist .= '<option value="'.$plugin['pluginid'].'">'.$plugin['name'].'</option>';
		}
		$pluginlist .= '</select>';
		cpmsg('plugins_nonexistence', 'action=plugins&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : ''), 'form', $pluginlist);
	} else {
		$condition = !empty($uid) ? "uid='$uid'" : "username='$username'";
	}

	$plugin = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin')." WHERE pluginid='$pluginid'");
	if(!$plugin) {
		cpmsg('plugin_not_found', '', 'error');
	}

	$plugin['modules'] = unserialize($plugin['modules']);

	if($plugin['modules']['system']) {
		cpmsg('plugin_donot_edit', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$adminidselect = array($plugin['adminid'] => 'selected');

		shownav('plugin');
		$anchor = in_array($_G['gp_anchor'], array('config', 'modules', 'vars')) ? $_G['gp_anchor'] : 'config';
		showsubmenuanchors($lang['plugins_edit'].' - '.$plugin['name'].($plugin['available'] ? cplang('plugins_edit_available') : ''), array(
			array('plugins_list', 'plugins', 0, 1),
			array('config', 'config', $anchor == 'config'),
			array('plugins_config_module', 'modules', $anchor == 'modules'),
			array('plugins_config_vars', 'vars', $anchor == 'vars'),
			array('export', 'plugins&operation=export&pluginid='.$plugin['pluginid'], 0, 1),
		));
		showtips('plugins_edit_tips');

		showtagheader('div', 'config', $anchor == 'config');
		showformheader("plugins&operation=edit&type=common&pluginid=$pluginid", '', 'configform');
		showtableheader();
		showsetting('plugins_edit_name', 'namenew', $plugin['name'], 'text');
		showsetting('plugins_edit_version', 'versionnew', $plugin['version'], 'text');
		if(!$plugin['copyright']) {
			showsetting('plugins_edit_copyright', 'copyrightnew', $plugin['copyright'], 'text');
		}
		showsetting('plugins_edit_identifier', 'identifiernew', $plugin['identifier'], 'text');
		showsetting('plugins_edit_directory', 'directorynew', $plugin['directory'], 'text');
		showsetting('plugins_edit_description', 'descriptionnew', $plugin['description'], 'textarea');
		showsetting('plugins_edit_langexists', 'langexists', $plugin['modules']['extra']['langexists'], 'radio');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'modules', $anchor == 'modules');
		showformheader("plugins&operation=edit&type=modules&pluginid=$pluginid", '', 'modulesform');
		showtableheader('plugins_edit_modules');
		showsubtitle(array('', 'plugins_edit_modules_type', 'plugins_edit_modules_name', 'plugins_edit_modules_menu', 'plugins_edit_modules_menu_url', 'plugins_edit_modules_adminid', 'display_order'));

		$moduleids = array();
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $moduleid => $module) {
				if($moduleid === 'extra' || $moduleid === 'system') {
					continue;
				}
				$adminidselect = array($module['adminid'] => 'selected');
				$includecheck = empty($val['include']) ? $lang['no'] : $lang['yes'];

				$typeselect = '<optgroup label="'.cplang('plugins_edit_modules_type_g1').'">'.
					'<option h="1100100" e="inc" value="1"'.($module['type'] == 1 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_1').'</option>'.
					'<option h="1111" e="inc" value="5"'.($module['type'] == 5 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_5').'</option>'.
					'<option h="1100100" e="inc" value="27"'.($module['type'] == 27 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_27').'</option>'.
					'<option h="1100100" e="inc" value="23"'.($module['type'] == 23 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_23').'</option>'.
					'<option h="1100110" e="inc" value="25"'.($module['type'] == 25 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_25').'</option>'.
					'<option h="1100111" e="inc" value="24"'.($module['type'] == 24 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_24').'</option>'.
					'</optgroup>'.
					'<optgroup label="'.cplang('plugins_edit_modules_type_g3').'">'.
					'<option h="1111" e="inc" value="7"'.($module['type'] == 7 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_7').'</option>'.
					'<option h="1111" e="inc" value="17"'.($module['type'] == 17 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_17').'</option>'.
					'<option h="1111" e="inc" value="19"'.($module['type'] == 19 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_19').'</option>'.
					'<option h="1001" e="inc" value="14"'.($module['type'] == 14 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_14').'</option>'.
					'<option h="1111" e="inc" value="26"'.($module['type'] == 26 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_26').'</option>'.
					'<option h="1111" e="inc" value="21"'.($module['type'] == 21 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_21').'</option>'.
					'<option h="1001" e="inc" value="15"'.($module['type'] == 15 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_15').'</option>'.
					'<option h="1001" e="inc" value="16"'.($module['type'] == 16 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_16').'</option>'.
					'<option h="1001" e="inc" value="3"'.($module['type'] == 3 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_3').'</option>'.
					'</optgroup>'.
					'<optgroup label="'.cplang('plugins_edit_modules_type_g2').'">'.
					'<option h="0011" e="class" value="11"'.($module['type'] == 11 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_11').'</option>'.
					'<option h="0011" e="class" value="28"'.($module['type'] == 28 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_28').'</option>'.
					'<option h="0001" e="class" value="12"'.($module['type'] == 12 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_12').'</option>'.
					'</optgroup>';
				showtablerow('', array('class="td25"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$moduleid]\">",
					"<select id=\"s_$moduleid\" onchange=\"shide(this, '$moduleid')\" name=\"typenew[$moduleid]\">$typeselect</select>",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$moduleid]\" value=\"$module[name]\"><span id=\"e_$moduleid\"></span>",
					"<span id=\"m_$moduleid\"><input type=\"text\" class=\"txt\" size=\"15\" name=\"menunew[$moduleid]\" value=\"$module[menu]\"></span>",
					"<span id=\"u_$moduleid\"><input type=\"text\" class=\"txt\" size=\"15\" id=\"url_$moduleid\" onchange=\"shide($('s_$moduleid'), '$moduleid')\" name=\"urlnew[$moduleid]\" value=\"".dhtmlspecialchars($module['url'])."\"></span>",
					"<span id=\"a_$moduleid\"><select name=\"adminidnew[$moduleid]\">\n".
					"<option value=\"0\" $adminidselect[0]>$lang[usergroups_system_0]</option>\n".
					"<option value=\"1\" $adminidselect[1]>$lang[usergroups_system_1]</option>\n".
					"<option value=\"2\" $adminidselect[2]>$lang[usergroups_system_2]</option>\n".
					"<option value=\"3\" $adminidselect[3]>$lang[usergroups_system_3]</option>\n".
					"</select></span>",
					"<span id=\"o_$moduleid\"><input type=\"text\" class=\"txt\" style=\"width:50px\" name=\"ordernew[$moduleid]\" value=\"$module[displayorder]\"></span>"
				));
				showtagheader('tbody', 'n_'.$moduleid);
				showtablerow('', array('', 'colspan="6"'), array(
				   '',
				   '&nbsp;&nbsp;&nbsp;<span id="nt_'.$moduleid.'">'.$lang['plugins_edit_modules_navtitle'].':<input type="text" class="txt" size="15" name="navtitlenew['.$moduleid.']" value="'.$module['navtitle'].'"></span>
					<span id="ni_'.$moduleid.'">'.$lang['plugins_edit_modules_navicon'].':<input type="text" class="txt" name="naviconnew['.$moduleid.']" value="'.$module['navicon'].'"></span>
					<span id="nsn_'.$moduleid.'">'.$lang['plugins_edit_modules_navsubname'].':<input type="text" class="txt" name="navsubnamenew['.$moduleid.']" value="'.$module['navsubname'].'"></span>
					<span id="nsu_'.$moduleid.'">'.$lang['plugins_edit_modules_navsuburl'].':<input type="text" class="txt" name="navsuburlnew['.$moduleid.']" value="'.$module['navsuburl'].'"></span>
					',
				));
				showtagfooter('tbody');

				$moduleids[] = $moduleid;
			}
		}
		showtablerow('', array('class="td25"', 'class="td28"'), array(
			cplang('add_new'),
			'<select id="s_n" onchange="shide(this, \'n\')" name="newtype">'.
				'<optgroup label="'.cplang('plugins_edit_modules_type_g1').'">'.
				'<option h="1100100" e="inc" value="1">'.cplang('plugins_edit_modules_type_1').'</option>'.
				'<option h="1111" e="inc" value="5">'.cplang('plugins_edit_modules_type_5').'</option>'.
				'<option h="1100100" e="inc" value="27">'.cplang('plugins_edit_modules_type_27').'</option>'.
				'<option h="1100100" e="inc" value="23">'.cplang('plugins_edit_modules_type_23').'</option>'.
				'<option h="1100110" e="inc" value="25">'.cplang('plugins_edit_modules_type_25').'</option>'.
				'<option h="1100111" e="inc" value="24">'.cplang('plugins_edit_modules_type_24').'</option>'.
				'</optgroup>'.
				'<optgroup label="'.cplang('plugins_edit_modules_type_g3').'">'.
				'<option h="1111" e="inc" value="7">'.cplang('plugins_edit_modules_type_7').'</option>'.
				'<option h="1111" e="inc" value="17">'.cplang('plugins_edit_modules_type_17').'</option>'.
				'<option h="1111" e="inc" value="19">'.cplang('plugins_edit_modules_type_19').'</option>'.
				'<option h="1001" e="inc" value="14">'.cplang('plugins_edit_modules_type_14').'</option>'.
				'<option h="1001" e="inc" value="26">'.cplang('plugins_edit_modules_type_26').'</option>'.
				'<option h="1001" e="inc" value="21">'.cplang('plugins_edit_modules_type_21').'</option>'.
				'<option h="1001" e="inc" value="15">'.cplang('plugins_edit_modules_type_15').'</option>'.
				'<option h="1001" e="inc" value="16">'.cplang('plugins_edit_modules_type_16').'</option>'.
				'<option h="1001" e="inc" value="3">'.cplang('plugins_edit_modules_type_3').'</option>'.
				'</optgroup>'.
				'<optgroup label="'.cplang('plugins_edit_modules_type_g2').'">'.
				'<option h="0011" e="class" value="11">'.cplang('plugins_edit_modules_type_11').'</option>'.
				'<option h="0011" e="class" value="28">'.cplang('plugins_edit_modules_type_28').'</option>'.
				'<option h="0001" e="class" value="12">'.cplang('plugins_edit_modules_type_12').'</option>'.
				'</optgroup>'.
			'</select>',
			'<input type="text" class="txt" size="15" name="newname"><span id="e_n"></span>',
			'<span id="m_n"><input type="text" class="txt" size="15" name="newmenu"></span>',
			'<span id="u_n"><input type="text" class="txt" size="15" id="url_n" onchange="shide($(\'s_n\'), \'n\')" name="newurl"></span>',
			'<span id="a_n"><select name="newadminid">'.
			'<option value="0" selected>'.cplang('usergroups_system_0').'</option>'.
			'<option value="1">'.cplang('usergroups_system_1').'</option>'.
			'<option value="2">'.cplang('usergroups_system_2').'</option>'.
			'<option value="3">'.cplang('usergroups_system_3').'</option>'.
			'</select></span>',
			'<span id="o_n"><input type="text" class="txt" style="width:50px"  name="neworder"></span>',
		));
		showtagheader('tbody', 'n_n');
		showtablerow('', array('', 'colspan="6"'), array(
		   '',
		   '&nbsp;&nbsp;&nbsp;<span id="nt_n">'.$lang['plugins_edit_modules_navtitle'].':<input type="text" class="txt" name="newnavtitle"></span>
			<span id="ni_n">'.$lang['plugins_edit_modules_navicon'].':<input type="text" class="txt" name="newnavicon"></span>
			<span id="nsn_n">'.$lang['plugins_edit_modules_navsubname'].':<input type="text" class="txt" name="newnavsubname"></span>
			<span id="nsu_n">'.$lang['plugins_edit_modules_navsuburl'].':<input type="text" class="txt" name="newnavsuburl"></span>
			',
		));
		showtagfooter('tbody');
		showsubmit('editsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
		$shideinit = '';
		foreach($moduleids as $moduleid) {
			$shideinit .= 'shide($("s_'.$moduleid.'"), \''.$moduleid.'\');';
		}
		echo '<script type="text/JavaScript">
			function shide(obj, id) {
				v = obj.options[obj.selectedIndex].getAttribute("h");
				$("m_" + id).style.display = v.substr(0,1) == "1" ? "" : "none";
				$("u_" + id).style.display = v.substr(1,1) == "1" ? "" : "none";
				$("a_" + id).style.display = v.substr(2,1) == "1" ? "" : "none";
				$("o_" + id).style.display = v.substr(3,1) == "1" ? "" : "none";
				if(v.substr(4,1)) {
					$("n_" + id).style.display = v.substr(4,1) == "1" ? "" : "none";
					$("nt_" + id).style.display = v.substr(4,1) == "1" ? "" : "none";
					$("ni_" + id).style.display = v.substr(5,1) == "1" ? "" : "none";
					$("nsn_" + id).style.display = v.substr(6,1) == "1" ? "" : "none";
					$("nsu_" + id).style.display = v.substr(6,1) == "1" ? "" : "none";
				} else {
					$("n_" + id).style.display = "none";
				}
				e = obj.options[obj.selectedIndex].getAttribute("e");
				$("e_" + id).innerHTML = e && ($("url_" + id).value == \'\' || $("u_" + id).style.display == "none") ? "." + e + ".php" : "";
			}
			shide($("s_n"), "n");'.$shideinit.'
		</script>';

		showtagheader('div', 'vars', $anchor == 'vars');
		showformheader("plugins&operation=edit&type=vars&pluginid=$pluginid", '', 'varsform');
		showtableheader('plugins_edit_vars');
		showsubtitle(array('', 'display_order', 'plugins_vars_title', 'plugins_vars_variable', 'plugins_vars_type', ''));
		$query = DB::query("SELECT * FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]' ORDER BY displayorder");
		while($var = DB::fetch($query)) {
			$var['type'] = $lang['plugins_edit_vars_type_'. $var['type']];
			$var['title'] .= isset($lang[$var['title']]) ? '<br />'.$lang[$var['title']] : '';
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$var[pluginvarid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$var[pluginvarid]]\" value=\"$var[displayorder]\">",
				$var['title'],
				$var['variable'],
				$var['type'],
				"<a href=\"".ADMINSCRIPT."?action=plugins&operation=vars&pluginid=$plugin[pluginid]&pluginvarid=$var[pluginvarid]\" class=\"act\">$lang[detail]</a>"
			));
		}
		showtablerow('', array('class="td25"', 'class="td28"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="2" name="newdisplayorder" value="0">',
			'<input type="text" class="txt" size="15" name="newtitle">',
			'<input type="text" class="txt" size="15" name="newvariable">',
			'<select name="newtype">
				<option value="number">'.cplang('plugins_edit_vars_type_number').'</option>
				<option value="text" selected>'.cplang('plugins_edit_vars_type_text').'</option>
				<option value="textarea">'.cplang('plugins_edit_vars_type_textarea').'</option>
				<option value="radio">'.cplang('plugins_edit_vars_type_radio').'</option>
				<option value="select">'.cplang('plugins_edit_vars_type_select').'</option>
				<option value="selects">'.cplang('plugins_edit_vars_type_selects').'</option>
				<option value="color">'.cplang('plugins_edit_vars_type_color').'</option>
				<option value="date">'.cplang('plugins_edit_vars_type_date').'</option>
				<option value="datetime">'.cplang('plugins_edit_vars_type_datetime').'</option>
				<option value="forum">'.cplang('plugins_edit_vars_type_forum').'</option>
				<option value="forums">'.cplang('plugins_edit_vars_type_forums').'</option>
				<option value="group">'.cplang('plugins_edit_vars_type_group').'</option>
				<option value="groups">'.cplang('plugins_edit_vars_type_groups').'</option>
				<option value="extcredit">'.cplang('plugins_edit_vars_type_extcredit').'</option>
				<option value="forum_text">'.cplang('plugins_edit_vars_type_forum_text').'</option>
				<option value="forum_textarea">'.cplang('plugins_edit_vars_type_forum_textarea').'</option>
				<option value="forum_radio">'.cplang('plugins_edit_vars_type_forum_radio').'</option>
				<option value="forum_select">'.cplang('plugins_edit_vars_type_forum_select').'</option>
				<option value="group_text">'.cplang('plugins_edit_vars_type_group_text').'</option>
				<option value="group_textarea">'.cplang('plugins_edit_vars_type_group_textarea').'</option>
				<option value="group_radio">'.cplang('plugins_edit_vars_type_group_radio').'</option>
				<option value="group_select">'.cplang('plugins_edit_vars_type_group_select').'</option>
			</seletc>',
			''
		));
		showsubmit('editsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		$type = $_G['gp_type'];
		$anchor = $_G['gp_anchor'];
		if($type == 'common') {

			$namenew	= dhtmlspecialchars(trim($_G['gp_namenew']));
			$versionnew	= strip_tags(trim($_G['gp_versionnew']));
			$directorynew	= dhtmlspecialchars($_G['gp_directorynew']);
			$identifiernew	= trim($_G['gp_identifiernew']);
			$descriptionnew	= dhtmlspecialchars($_G['gp_descriptionnew']);
			$copyrightnew	= $plugin['copyright'] ? addslashes($plugin['copyright']) : dhtmlspecialchars($_G['gp_copyrightnew']);
			$adminidnew	= ($_G['gp_adminidnew'] > 0 && $_G['gp_adminidnew'] <= 3) ? $_G['gp_adminidnew'] : 1;

			if(!$namenew) {
				cpmsg('plugins_edit_name_invalid', '', 'error');
			} elseif(!isplugindir($directorynew)) {
				cpmsg('plugins_edit_directory_invalid', '', 'error');
			} elseif($identifiernew != $plugin['identifier']) {
				$query = DB::query("SELECT pluginid FROM ".DB::table('common_plugin')." WHERE identifier='$identifiernew' LIMIT 1");
				if(DB::num_rows($query) || !ispluginkey($identifiernew)) {
					cpmsg('plugins_edit_identifier_invalid', '', 'error');
				}
			}
			if($_G['gp_langexists'] && !file_exists($langfile = DISCUZ_ROOT.'./data/plugindata/'.$identifiernew.'.lang.php')) {
				cpmsg('plugins_edit_language_invalid', '', 'error', array('langfile' => $langfile));
			}
			$plugin['modules']['extra']['langexists'] = $_G['gp_langexists'];
			DB::query("UPDATE ".DB::table('common_plugin')." SET adminid='$adminidnew', version='$versionnew', name='$namenew', modules='".addslashes(serialize($plugin['modules']))."', identifier='$identifiernew', description='$descriptionnew', directory='$directorynew', copyright='$copyrightnew' WHERE pluginid='$pluginid'");

		} elseif($type == 'modules') {

			$modulesnew = array();
			$newname = trim($_G['gp_newname']);
			$updatenav = false;
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $moduleid => $module) {
					if(!isset($_G['gp_delete'][$moduleid])) {
						if($moduleid === 'extra' || $moduleid === 'system') {
							continue;
						}
						$modulesnew[] = array(
							'name'		=> $_G['gp_namenew'][$moduleid],
							'menu'		=> $_G['gp_menunew'][$moduleid],
							'url'		=> $_G['gp_urlnew'][$moduleid],
							'type'		=> $_G['gp_typenew'][$moduleid],
							'adminid'	=> ($_G['gp_adminidnew'][$moduleid] >= 0 && $_G['gp_adminidnew'][$moduleid] <= 3) ? $_G['gp_adminidnew'][$moduleid] : $module['adminid'],
							'displayorder'	=> intval($_G['gp_ordernew'][$moduleid]),
							'navtitle'	=> $_G['gp_navtitlenew'][$moduleid],
							'navicon'	=> $_G['gp_naviconnew'][$moduleid],
							'navsubname'	=> $_G['gp_navsubnamenew'][$moduleid],
							'navsuburl'	=> $_G['gp_navsuburlnew'][$moduleid],
						);
						if(in_array($_G['gp_typenew'][$moduleid], array(1,23,24,25))) {
							$updatenav = true;
						}
					} elseif(in_array($_G['gp_typenew'][$moduleid], array(1,23,24,25))) {
						$updatenav = true;
					}
				}
			}

			if($updatenav) {
				DB::delete('common_nav', "type='3' AND identifier='$plugin[identifier]'");
			}

			$modulenew = array();
			if(!empty($_G['gp_newname'])) {
				$modulesnew[] = array(
					'name'		=> $_G['gp_newname'],
					'menu'		=> $_G['gp_newmenu'],
					'url'		=> $_G['gp_newurl'],
					'type'		=> $_G['gp_newtype'],
					'adminid'	=> $_G['gp_newadminid'],
					'displayorder'	=> intval($_G['gp_neworder']),
					'navtitle'	=> $_G['gp_newnavtitle'],
					'navicon'	=> $_G['gp_newnavicon'],
					'navsubname'	=> $_G['gp_newnavsubname'],
					'navsuburl'	=> $_G['gp_newnavsuburl'],
				);
			}

			usort($modulesnew, 'modulecmp');

			$namesarray = array();
			foreach($modulesnew as $key => $module) {
				$namekey = in_array($module['type'], array(11, 12)) ? 1 : 0;
				if(!ispluginkey($module['name'])) {
					cpmsg('plugins_edit_modules_name_invalid', '', 'error');
				} elseif(@in_array($module['name'], $namesarray[$namekey])) {
					cpmsg('plugins_edit_modules_duplicated', '', 'error');
				}
				$namesarray[$namekey][] = $module['name'];

				$module['menu'] = trim($module['menu']);
				$module['url'] = trim($module['url']);
				$module['adminid'] = $module['adminid'] >= 0 && $module['adminid'] <= 3 ? $module['adminid'] : 1 ;

				$modulesnew[$key] = $module;
			}
			if(!empty($plugin['modules']['extra'])) {
				$modulesnew['extra'] = $plugin['modules']['extra'];
			}

			if(!empty($plugin['modules']['system'])) {
				$modulesnew['system'] = $plugin['modules']['system'];
			}

			DB::query("UPDATE ".DB::table('common_plugin')." SET modules='".addslashes(serialize($modulesnew))."' WHERE pluginid='$pluginid'");

		} elseif($type == 'vars') {

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_pluginvar')." WHERE pluginid='$pluginid' AND pluginvarid IN ($ids)");
			}

			if(is_array($_G['gp_displayordernew'])) {
				foreach($_G['gp_displayordernew'] as $id => $displayorder) {
					DB::query("UPDATE ".DB::table('common_pluginvar')." SET displayorder='$displayorder' WHERE pluginid='$pluginid' AND pluginvarid='$id'");
				}
			}

			$newtitle = dhtmlspecialchars(trim($_G['gp_newtitle']));
			$newvariable = trim($_G['gp_newvariable']);
			if($newtitle && $newvariable) {
				$query = DB::query("SELECT pluginvarid FROM ".DB::table('common_pluginvar')." WHERE pluginid='$pluginid' AND variable='$newvariable' LIMIT 1");
				if(DB::num_rows($query) || strlen($newvariable) > 40 || !ispluginkey($newvariable)) {
					cpmsg('plugins_edit_var_invalid', '', 'error');
				}
				$data = array(
					'pluginid' => $pluginid,
					'displayorder' => $_G['gp_newdisplayorder'],
					'title' => $newtitle,
					'variable' => $newvariable,
					'type' => $_G['gp_newtype'],
				);
				DB::insert('common_pluginvar', $data);
			}

		}

		updatecache(array('plugin', 'setting', 'styles'));
		updatemenu('plugin');
		cpmsg('plugins_edit_succeed', "action=plugins&operation=edit&pluginid=$pluginid&anchor=$anchor", 'succeed');

	}

} elseif($operation == 'delete') {

	$plugin = DB::fetch_first("SELECT name, identifier, directory, modules, version, available FROM ".DB::table('common_plugin')." WHERE pluginid='$pluginid'");
	$dir = substr($plugin['directory'], 0, -1);
	$modules = unserialize($plugin['modules']);
	if($modules['system']) {
		cpmsg('plugins_delete_error');
	}

	if(!$_G['gp_confirmed']) {

		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			$filename = DISCUZ_ROOT.'./source/plugin/'.$plugin['identifier'].'/'.$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
				@include $filename;
			}
		}

		cpmsg('plugins_delete_confirm', 'action=plugins&operation=delete&pluginid='.$pluginid.'&confirmed=yes', 'form', array('pluginname' => $plugin['name'], 'toversion' => $plugin['version']));

	} else {

		$identifier = $plugin['identifier'];
		DB::query("DELETE FROM ".DB::table('common_plugin')." WHERE pluginid=$pluginid");
		DB::query("DELETE FROM ".DB::table('common_pluginvar')." WHERE pluginid=$pluginid");
		DB::delete('common_nav', "type='3' AND identifier='$identifier'");

		foreach(array('script', 'template') as $type) {
			loadcache('pluginlanguage_'.$type, 1);
			if(isset($_G['cache']['pluginlanguage_'.$type][$identifier])) {
				unset($_G['cache']['pluginlanguage_'.$type][$identifier]);
				save_syscache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
			}
		}

		updatecache(array('plugin', 'setting', 'styles'));
		updatemenu('plugin');

		if($dir) {
			$file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : '').'.xml';
			if(file_exists($file)) {
				$importtxt = @implode('', file($file));
				$pluginarray = getimportdata('Discuz! Plugin');
				if(!empty($pluginarray['uninstallfile']) && preg_match('/^[\w\.]+$/', $pluginarray['uninstallfile'])) {
					dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=pluginuninstall&dir='.$dir.'&installtype='.$modules['extra']['installtype']);
				}
			}
		}

		loadcache('pluginlanguage_install', 1);
		if(isset($_G['cache']['pluginlanguage_install'][$identifier])) {
			unset($_G['cache']['pluginlanguage_install'][$identifier]);
			save_syscache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
		}

		require_once libfile('function/cloudaddons');
		cloudaddons_uninstall($dir.'.plugin', DISCUZ_ROOT.'./source/plugin/'.$dir);
		cpmsg('plugins_delete_succeed', "action=plugins", 'succeed');
	}

} elseif($operation == 'vars') {

	$pluginvarid = $_G['gp_pluginvarid'];
	$pluginvar = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin')." p, ".DB::table('common_pluginvar')." pv WHERE p.pluginid='$pluginid' AND pv.pluginid=p.pluginid AND pv.pluginvarid='$pluginvarid'");
	if(!$pluginvar) {
		cpmsg('pluginvar_not_found', '', 'error');
	}

	if(!submitcheck('varsubmit')) {
		shownav('plugin');
		showsubmenu($lang['plugins_edit'].' - '.$pluginvar['name'], array(
			array('plugins_list', 'plugins', 0),
			array('config', 'plugins&operation=edit&pluginid='.$pluginid.'&anchor=config', 0),
			array('plugins_config_module', 'plugins&operation=edit&pluginid='.$pluginid.'&anchor=modules', 0),
			array('plugins_config_vars', 'plugins&operation=edit&pluginid='.$pluginid.'&anchor=vars', 1),
			array('export', 'plugins&operation=export&pluginid='.$pluginid, 0),
		));

		$typeselect = '<select name="typenew" onchange="if(this.value.indexOf(\'select\') != -1) $(\'extra\').style.display=\'\'; else $(\'extra\').style.display=\'none\';">';
		foreach(array('number', 'text', 'radio', 'textarea', 'select', 'selects', 'color', 'date', 'datetime', 'forum', 'forums', 'group', 'groups', 'extcredit',
				'forum_text', 'forum_textarea', 'forum_radio', 'forum_select', 'group_text', 'group_textarea', 'group_radio', 'group_select') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($pluginvar['type'] == $type ? 'selected' : '').'>'.$lang['plugins_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		showformheader("plugins&operation=vars&pluginid=$pluginid&pluginvarid=$pluginvarid");
		showtableheader();
		showtitle($lang['plugins_edit_vars'].' - '.$pluginvar['title']);
		showsetting('plugins_edit_vars_title', 'titlenew', $pluginvar['title'], 'text');
		showsetting('plugins_edit_vars_description', 'descriptionnew', $pluginvar['description'], 'textarea');
		showsetting('plugins_edit_vars_type', '', '', $typeselect);
		showsetting('plugins_edit_vars_variable', 'variablenew', $pluginvar['variable'], 'text');
		showtagheader('tbody', 'extra', $pluginvar['type'] == 'select' || $pluginvar['type'] == 'selects');
		showsetting('plugins_edit_vars_extra', 'extranew',  $pluginvar['extra'], 'textarea');
		showtagfooter('tbody');
		showsubmit('varsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$titlenew	= cutstr(dhtmlspecialchars(trim($_G['gp_titlenew'])), 25);
		$descriptionnew	= cutstr(dhtmlspecialchars(trim($_G['gp_descriptionnew'])), 255);
		$variablenew	= trim($_G['gp_variablenew']);
		$extranew	= dhtmlspecialchars(trim($_G['gp_extranew']));

		if(!$titlenew) {
			cpmsg('plugins_edit_var_title_invalid', '', 'error');
		} elseif($variablenew != $pluginvar['variable']) {
			$query = DB::query("SELECT pluginvarid FROM ".DB::table('common_pluginvar')." WHERE variable='$variablenew'");
			if(DB::num_rows($query) || !$variablenew || strlen($variablenew) > 40 || !ispluginkey($variablenew)) {
				cpmsg('plugins_edit_vars_invalid', '', 'error');
			}
		}

		DB::query("UPDATE ".DB::table('common_pluginvar')." SET title='$titlenew', description='$descriptionnew', type='$_G[gp_typenew]', variable='$variablenew', extra='$extranew' WHERE pluginid='$pluginid' AND pluginvarid='$pluginvarid'");

		updatecache(array('plugin', 'setting', 'styles'));
		cpmsg('plugins_edit_vars_succeed', "action=plugins&operation=edit&pluginid=$pluginid&anchor=vars", 'succeed');
	}

} elseif($operation == 'upgradecheck') {
	$where = !empty($_G['gp_identifier']) ? "identifier='$_G[gp_identifier]'" : "1";
	$query = DB::query("SELECT pluginid, identifier, name, version, directory, modules FROM ".DB::table('common_plugin')." WHERE $where");
	$plugins = $errarray = $newarray = $nowarray = array();
	if(!DB::num_rows($query)) {
		cpmsg('plugin_not_found', '', 'error');
	} else {
		$addonids = $pluginarray = array();
		while($row = DB::fetch($query)) {
			$pluginarray[] = $row;
			if(ispluginkey($row['identifier'])) {
				$addonids[] = $row['identifier'].'.plugin';
			}
		}
		$checkresult = unserialize(cloudaddons_upgradecheck($addonids));
		foreach($pluginarray as $row) {
			$addonid = $row['identifier'].'.plugin';
			if(isset($checkresult[$addonid])) {
				list($return, $newver) = explode(':', $checkresult[$addonid]);
				$result[$row['identifier']]['result'] = $return;
				if($newver) {
					$result[$row['identifier']]['newver'] = $newver;
				}
			}
			$plugins[$row['identifier']] = $row['name'].' '.$row['version'];
			$modules = unserialize($row['modules']);

			$file = DISCUZ_ROOT.'./source/plugin/'.$row['identifier'].'/discuz_plugin_'.$row['identifier'].($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : '').'.xml';
			$upgrade = false;
			if(file_exists($file)) {
				$importtxt = @implode('', file($file));
				$pluginarray = getimportdata('Discuz! Plugin');
				$newver = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
				if($newver > $row['version']) {
					$upgrade = true;
					$nowarray[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$row['pluginid'].'">'.$plugins[$row['identifier']].' -> '.$newver.'</a>';
				}
			}
			if(!$upgrade) {
				$entrydir = DISCUZ_ROOT.'./source/plugin/'.$row['identifier'];
				$upgradestr = '';
				if(file_exists($entrydir)) {
					$d = dir($entrydir);
					while($f = $d->read()) {
						if(preg_match('/^discuz\_plugin\_'.$row['identifier'].'(\_\w+)?\.xml$/', $f, $a)) {
							$extratxt = $extra = substr($a[1], 1);
							if(preg_match('/^SC\_GBK$/i', $extra)) {
								$extratxt = '&#31616;&#20307;&#20013;&#25991;&#29256;';
							} elseif(preg_match('/^SC\_UTF8$/i', $extra)) {
								$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
							} elseif(preg_match('/^TC\_BIG5$/i', $extra)) {
								$extratxt = '&#32321;&#39636;&#20013;&#25991;&#29256;';
							} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
								$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
							}
							if($modules['extra']['installtype'] == $extratxt) {
								continue;
							}
							$importtxt = @implode('', file($entrydir.'/'.$f));
							$pluginarray = getimportdata('Discuz! Plugin');
							$newverother = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
							if($newverother > $row['version']) {
								$nowarray[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$row['pluginid'].'&confirmed=yes&installtype='.rawurlencode($extra).'">'.$plugins[$row['identifier']].' -> '.$newverother.($extra ? ' ('.$extratxt.')' : '').'</a>';
							}
						}
					}
				}
			}
		}
	}
	foreach($result as $id => $row) {
		if($row['result'] == 0) {
			$errarray[] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$id.'.plugin" target="_blank">'.$plugins[$id].'</a>';
		} elseif($row['result'] == 2) {
			$newarray[] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$id.'.plugin" target="_blank">'.$plugins[$id].($row['newver'] ? ' -> '.$row['newver'] : '').'</a>';
		}
	}
	if(!$nowarray && !$newarray && !$errarray) {
		cpmsg('plugins_validator_noupdate', '', 'error');
	} else {
		cpmsg(
			($nowarray ? cplang('plugins_validator_nowupgrade').implode("<br />", $nowarray).'<br /><br />' : '').
			($newarray ? cplang('plugins_validator_newversion').implode("<br />", $newarray).'<br /><br />' : '').
			($errarray ? cplang('plugins_validator_error').implode("<br />", $errarray).'<br /><br />' : '')
		);
	}
}

?>