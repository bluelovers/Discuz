<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_templates.php 22547 2011-05-12 04:28:26Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

$operation = empty($operation) ? 'admin' : $operation;

if($operation == 'admin') {

	if(!submitcheck('tplsubmit')) {

		$templates = '';
		$query = DB::query("SELECT * FROM ".DB::table('common_template')."");
		while($tpl = DB::fetch($query)) {
			$basedir = basename($tpl[directory]);
			$templates .= showtablerow('', array('class="td25"', '', 'class="td29"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" ".($tpl['templateid'] == 1 ? 'disabled ' : '')."value=\"$tpl[templateid]\">",
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"namenew[$tpl[templateid]]\" value=\"$tpl[name]\">",
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"directorynew[$tpl[templateid]]\" value=\"$tpl[directory]\">",
				!empty($tpl['copyright']) ?
					($basedir != 'default' ? '<a href="http://addons.discuz.com/?tid='.urlencode($basedir).'" target="_blank">'.$tpl['copyright'].'</a>' : $tpl['copyright']) :
					"<input type=\"text\" class=\"txt\" size=\"8\" name=\"copyrightnew[$tpl[templateid]]\" value=>"
			), TRUE);
		}

		shownav('style', 'templates_admin');
		showsubmenu('templates_admin');
		showformheader('templates');
		showtableheader();
		showsubtitle(array('', 'templates_admin_name', 'dir', 'copyright', ''));
		echo $templates;
		echo '<tr><td>'.$lang['add_new'].'</td><td><input type="text" class="txt" size="8" name="newname"></td><td class="td29"><input type="text" class="txt" size="20" name="newdirectory"></td><td><input type="text" class="txt" size="25" name="newcopyright"></td><td>&nbsp;</td></tr>';
		showsubmit('tplsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_newname']) {
			if(!$_G['gp_newdirectory']) {
				cpmsg('tpl_new_directory_invalid', '', 'error');
			} elseif(!istpldir($_G['gp_newdirectory'])) {
				$directory = $_G['gp_newdirectory'];
				cpmsg('tpl_directory_invalid', '', 'error', array('directory' => $directory));
			}
			DB::insert('common_template', array('name' => $_G['gp_newname'], 'directory' => $_G['gp_newdirectory'], 'copyright' => $_G['gp_newcopyright']));
		}

		foreach($_G['gp_directorynew'] as $id => $directory) {
			if(!$_G['gp_delete'] || ($_G['gp_delete'] && !in_array($id, $_G['gp_delete']))) {
				if(!istpldir($directory)) {
					cpmsg('tpl_directory_invalid', '', 'error', array('directory' => $directory));
				} elseif($id == 1 && $directory != './template/default') {
					cpmsg('tpl_default_directory_invalid', '', 'error');
				}
				DB::query("UPDATE ".DB::table('common_template')." SET name='{$_G['gp_namenew'][$id]}', directory='{$_G['gp_directorynew'][$id]}' WHERE templateid='$id'", 'UNBUFFERED');
				if(!empty($_G['gp_copyrightnew'][$id])) {
					DB::query("UPDATE ".DB::table('common_template')." SET copyright='{$_G['gp_copyrightnew'][$id]}' WHERE templateid='$id' AND copyright=''", 'UNBUFFERED');
				}
			}
		}

		if(is_array($_G['gp_delete'])) {
			if(in_array('1', $_G['gp_delete'])) {
				cpmsg('tpl_delete_invalid', '', 'error');
			}
			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_template')." WHERE templateid IN ($ids) AND templateid<>'1'", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('common_style')." SET templateid='1' WHERE templateid IN ($ids)", 'UNBUFFERED');
			}
		}

		updatecache('styles');
		cpmsg('tpl_update_succeed', 'action=templates', 'succeed');

	}

}
?>