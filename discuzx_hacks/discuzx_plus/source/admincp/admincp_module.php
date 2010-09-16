<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_module.php 651 2010-09-13 07:28:43Z yexinhao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(!submitcheck('modulesubmit')) {
		shownav('global', 'nav_module');
		showsubmenu('nav_module');
		showformheader('module');
		showtableheader('module_list', 'fixpadding');
		showsubtitle(array('', 'display_order', 'available', 'name', 'version', 'apikey', ''));
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';

		$newmodule = getmodule();
		$query = DB::query("SELECT * FROM ".DB::table('common_module')." ORDER BY displayorder");
		while($module = DB::fetch($query)) {
			$version = $module['version'] ? $module['version'] : $newmodule[$module['identifier']]['version'];
			$checkavailable = $module['available'] ? 'checked' : '';
			$disabled = $module['type'] == 1 ? 'disabled="true"' : '';
			$appkey = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($module['identifier'].TIMESTAMP), 0, 4).random(4);
			$appkey = empty($module['appkey']) ? $appkey : $module['appkey'];
			showtablerow('', array('', '', '', '', '', '', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$module[mid]\" $disabled>",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$module[mid]]\" value=\"$module[displayorder]\">",
				($module['type'] != 1 ? "<input class=\"checkbox\" type=\"checkbox\" name=\"available[$module[mid]]\" value=\"1\" $disabled $checkavailable>" : "<input type=\"hidden\" name=\"available[$module[mid]]\" value=\"1\">"),
				"<input type=\"text\" class=\"txt\" name=\"name[$module[mid]]\" value=\"$module[name]\">",
				($module['type'] != 1 ? $version : ''),
				($module['type'] != 1 ? "<input type=\"text\" class=\"txt\" name=\"apikey[$module[mid]]\" value=\"$appkey\">" : ''),
				($module['type'] != 1 ? "<a href=\"".ADMINSCRIPT."?action=$module[identifier]&operation=setting\" class=\"act\">$lang[detail]</a>" : ''),
			));
			$enddisplayorder = $module['displayorder'];
		}

		if(!empty($newmodule)) {
			foreach($newmodule as $module) {
				$moduleidentifier = DB::result_first("SELECT identifier FROM ".DB::table('common_module')." WHERE identifier='$module[identifier]'");
				if(empty($moduleidentifier)) {
					$appkey = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($module['identifier'].TIMESTAMP), 0, 4).random(4);
					$appkey = empty($module['appkey']) ? $appkey : $module['appkey'];
					$available = $module['available'] ? 'checked="checked"' : '';
					$module['displayorder'] = $module['displayorder'] ? $module['displayorder'] : $enddisplayorder + 1;
					showtablerow('', array('', '', '', '', '', ''), array(
						'',
						"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayordernew[]\" value=\"$module[displayorder]\">",
						"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[]\" value=\"1\" $available>",
						$module['name'].'&nbsp;&nbsp;<font color="red">New!</font>',
						'<input type=\"text\" class=\"txt\" name="apikeynew[]" value="'.$appkey.'">',
						'<input type="hidden" name="versionnew[]" value="'.$module['version'].'"><input type="hidden" name="namenew[]" value="'.$module['name'].'"><input type="hidden" name="identifiernew[]" value="'.$module['identifier'].'">',
					));
				}
			}
		}

		showsubmit('modulesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			DB::query("DELETE FROM ".DB::table('common_module')." WHERE mid IN ($ids)");
		}

		if(is_array($_G['gp_displayorder'])) {
			foreach($_G['gp_displayorder'] as $id => $val) {
				DB::query("UPDATE ".DB::table('common_module')." SET name='".dhtmlspecialchars(trim($_G['gp_name'][$id]))."', available='".intval($_G['gp_available'][$id])."', displayorder='".intval($_G['gp_displayorder'][$id])."' WHERE mid='$id'");
			}
		}

		if(is_array($_G['gp_availablenew'])) {
			foreach($_G['gp_availablenew'] as $id => $val) {
				$data = array(
					'name' => dhtmlspecialchars(trim($_G['gp_namenew'][$id])),
					'displayorder' => intval($_G['gp_displayordernew'][$id]),
					'available' => intval($_G['gp_availablenew'][$id]),
					'identifier' => $_G['gp_identifiernew'][$id],
					'version' => $_G['gp_versionnew'][$id]
				);
				DB::insert('common_module', $data, true);
			}
		}

		updatecache('modulelist');
		cpmsg('module_succeed', 'action=module', 'succeed');

	}

}

function getmodule() {
	global $_G;
	$dir = DISCUZ_ROOT.'./source/module';
	$moduledir = dir($dir);
	$module = array();
	while($entry = $moduledir->read()) {
		if(!in_array($entry, array('.', '..')) && preg_match("/^module\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 100 && is_file($dir.'/'.$entry)) {
			@include_once $dir.'/'.$entry;
			$module[$moduleinfo['identifier']] = $moduleinfo;
		}
	}
	return $module;
}

?>