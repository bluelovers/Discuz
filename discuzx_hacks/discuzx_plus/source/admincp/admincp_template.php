<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id:  12003 2010-06-23 07:41:55Z  $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$operation = in_array($operation, array('nav', 'list')) ? $operation : 'list';

if($operation == 'list') {

	$modulelist = $modulemenu = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_module')." WHERE available='1' ORDER BY displayorder");
	while($module = DB::fetch($query)) {
		if($module['type'] != 1) {
			$modulelist[$module['mid']] = $module['name'];
		}
	}

	$_G['gp_mid'] = intval($_G['gp_mid']);

	$allselected = empty($_G['gp_mid']) ? 1 : 0;
	$modulemenu[] = array($lang['all'], 'template&operation=list', $allselected);
	$moduleselect = '';

	if(!empty($modulelist)) {
		$moduleselect = '<select name="newmodule[]"><option value="0">'.$lang['all_module'].'</option>';
		foreach($modulelist as $mid => $modname) {
			$moduleselect .= '<option value="'.$mid.'">'.$modname.'</option>';
			$selected = $_G['gp_mid'] == $mid ? 1 : 0;
			$modulemenu[] = array($modname, 'template&operation=list&mid='.$mid, $selected);
		}
		$moduleselect .= '</select>';
	}

	if(!submitcheck('listsubmit')) {

		shownav('global', 'nav_template');
		showsubmenu('nav_template', $modulemenu);

echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,''],
		[1,'<input type="text" class="text" name="newname[]" value="">'],
		[1,'<input type="text" class="text" name="newdirectory[]" value="">'],
		[1,'$moduleselect'],
		[1,'<input type="text" class="text" name="newcopyright[]">'],
		[1,'<input type="checkbox" class="checkbox" name="newavailable[]" value="1" checked="checked">']
	]
];
</script>
EOT;

		showformheader('template&operation=list&mid='.$_G['gp_mid']);
		showtableheader('template_list', 'fixpadding');
		showsubtitle(array('', 'template_title', 'template_identifier', 'module', 'copyright', 'available'));

		$addmodule = !empty($_G['gp_mid']) ?  "WHERE mid='$_G[gp_mid]'" : '';
		$query = DB::query("SELECT * FROM ".DB::table('common_template')." $addmodule ORDER BY templateid");
		while($template = DB::fetch($query)) {
			$checked = $template['available'] ? 'checked="checked"' : '';
			showtablerow('', array('', '', '', '', '', ''), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$template[templateid]\">",
				$template['name'],
				$template['directory'],
				$modulelist[$template['mid']],
				$template['copyright'],
				"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$template[templateid]]\" value=\"1\" $checked>",
			));
		}
		echo '<tr><td></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['template_add'].'</a></div></td></tr>';
		showsubmit('listsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			DB::query("DELETE FROM ".DB::table('common_template')." WHERE templateid IN ($ids)");
		}

		if(is_array($_G['gp_newname'])) {
			$_G['gp_newdirectory'] = is_array($_G['gp_newdirectory']) ? $_G['gp_newdirectory'] : array();
			$_G['gp_newcopyright'] = is_array($_G['gp_newcopyright']) ? $_G['gp_newcopyright'] : array();
			foreach($_G['gp_newname'] as $id => $val) {
				$data = array(
					'name' => dhtmlspecialchars(trim($_G['gp_newname'][$id])),
					'directory' => dhtmlspecialchars(trim($_G['gp_newdirectory'][$id])),
					'available' => intval($_G['gp_newavailable'][$id]),
					'copyright' => dhtmlspecialchars(trim($_G['gp_newcopyright'][$id])),
					'mid' => intval($_G['gp_newmodule'][$id]),
				);
				DB::insert('common_template', $data, true);
			}
		}

		if(is_array($_G['gp_available'])) {
			foreach($_G['gp_available'] as $id => $val) {
				DB::query("UPDATE ".DB::table('common_template')." SET available='".intval($_G['gp_available'][$id])."' WHERE templateid='$id'");
			}
		}

		updatecache('template');
		cpmsg('template_succeed', 'action=template&operation=list&mid='.$_G['gp_mid'], 'succeed');

	}

} elseif($operation == 'nav') {

	$type = $_G['gp_type'] ? intval($_G['gp_type']) : 1;
	$selecttype[$type] = 1;

	if(!submitcheck('navsubmit')) {

		shownav('global', 'nav_template');
		showsubmenu('nav_template', array(
			array('template_nav_top', 'template&operation=nav&type=1', $selecttype[1]),
			array('template_nav_bottom', 'template&operation=nav&type=2', $selecttype[2]),
		));

echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,''],
		[1,'<input type="text" class="text" name="newdisplayorder[]" size="3" value="">'],
		[1,'<input type="text" class="text" name="newtitle[]" value="">'],
		[1,'<input type="text" class="text" name="newurl[]">'],
		[1,'<input type="checkbox" class="checkbox" name="newtarget[]" value="1">'],
		[1,'<input type="checkbox" class="checkbox" name="newavailable[]" value="1" checked="checked">']
	]
];
</script>
EOT;

		showformheader('template&operation=nav&type='.$type);
		showtableheader('template_nav', 'fixpadding');
		showsubtitle(array('', 'displayorder', 'template_nav_title', 'template_nav_url', 'target', 'available'));

		$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE type='$type' ORDER BY displayorder");
		while($nav = DB::fetch($query)) {
			$availablechecked = $nav['available'] ? 'checked="checked"' : '';
			$targetchecked = $nav['target'] ? 'checked="checked"' : '';
			showtablerow('', array('', '', '', '', ''), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$nav[id]\">",
				"<input type=\"text\" class=\"text\" name=\"displayorder[$nav[id]]\" size=\"3\" value=\"$nav[displayorder]\">",
				"<input type=\"text\" class=\"text\" name=\"title[$nav[id]]\" value=\"$nav[title]\">",
				"<input type=\"text\" class=\"text\" name=\"url[$nav[id]]\" value=\"$nav[url]\">",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"target[$nav[id]]\" value=\"1\" $targetchecked>",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$nav[id]]\" value=\"1\" $availablechecked>"
			));
		}
		echo '<tr><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['template_nav_add'].'</a></div></td>';

		showsubmit('navsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_newtitle'])) {
			$_G['gp_newurl'] = is_array($_G['gp_newurl']) ? $_G['gp_newurl'] : array();
			foreach($_G['gp_newtitle'] as $id => $title) {
				$data = array(
					'title' => dhtmlspecialchars(trim($title)),
					'url' => dhtmlspecialchars(trim($_G['gp_newurl'][$id])),
					'target' => intval($_G['gp_newtarget'][$id]),
					'displayorder' => intval($_G['gp_newdisplayorder'][$id]),
					'type' => intval($type),
					'available' => intval($_G['gp_newavailable'][$id]),
				);
				DB::insert('common_nav', $data, true);
			}
		}

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			DB::query("DELETE FROM ".DB::table('common_nav')." WHERE id IN ($ids)");
		}

		if(is_array($_G['gp_title'])) {
			foreach($_G['gp_title'] as $id => $val) {
				DB::query("UPDATE ".DB::table('common_nav')." SET title='".dhtmlspecialchars($_G['gp_title'][$id])."', url='".dhtmlspecialchars($_G['gp_url'][$id])."', displayorder='".intval($_G['gp_displayorder'][$id])."', available='".intval($_G['gp_available'][$id])."', target='".intval($_G['gp_target'][$id])."' WHERE id='$id'");
			}
		}

		updatecache('navlist');
		cpmsg('template_nav_succeed', 'action=template&operation=nav&type='.$type, 'succeed');

	}
}

?>