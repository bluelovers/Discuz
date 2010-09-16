<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_medals.php 12003 2010-06-23 07:41:55Z wangjinbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$query = DB::query("SELECT * FROM ".DB::table('common_module')." WHERE available='1' ORDER BY displayorder");
while($module = DB::fetch($query)) {
	if($module['type'] != 1) {
		$modulelist[$module['mid']] = $module['name'];
	}
}

$operation = in_array($operation, array('list', 'detail')) ? $operation : 'list';

if($operation == 'list') {

	if(!submitcheck('profilesubmit')) {
		$_G['gp_mid'] = intval($_G['gp_mid']);

		$allselected = empty($_G['gp_mid']) ? 1 : 0;
		$modulemenu[] = array($lang['all'], 'memberprofile&operation=list', $allselected);
		$moduleselect = '';

		if(!empty($modulelist)) {
			$moduleselect = '<select name="newmodule[]"><option value="0">'.$lang['all_module'].'</option>';
			foreach($modulelist as $mid => $modname) {
				$moduleselect .= '<option value="'.$mid.'">'.$modname.'</option>';
				$selected = $_G['gp_mid'] == $mid ? 1 : 0;
				$modulemenu[] = array($modname, 'memberprofile&operation=list&mid='.$mid, $selected);
			}
			$moduleselect .= '</select>';
		}

		shownav('global', 'nav_member_profile');
		showsubmenu('nav_member_profile', $modulemenu);
		showformheader('memberprofile');

echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1, '', 'td25'],
		[1, '<input type="text" class="txt" size="2" name="newdisplayorder[]" value="0">', 'td28'],
		[1, '<input type="text" class="txt" size="15" name="newtitle[]">'],
		[1, '<input type="text" class="txt" size="15" name="newidentifier[]">'],
		[1, '<input type="checkbox" class="checkbox" name="newavailable">'],
		[1, '<select name="newtype[]"><option value="number">$lang[vars_type_number]</option><option value="text" selected>$lang[vars_type_text]</option><option value="textarea">$lang[vars_type_textarea]</option><option value="radio">$lang[vars_type_radio]</option><option value="checkbox">$lang[vars_type_checkbox]</option><option value="select">$lang[vars_type_select]</option><option value="calendar">$lang[vars_type_calendar]</option><option value="email">$lang[vars_type_email]</option><option value="url">$lang[vars_type_url]</option></select>'],
		[1, '$moduleselect'],
		[1, '']
	]
];
</script>
EOT;

		showtableheader('member_profile_list', 'fixpadding');
		showsubtitle(array('', 'display_order', 'name', 'variable', 'available', 'type', 'module', ''));

		$where = $_G['gp_mid'] ? "WHERE mid='$_G[gp_mid]'" : '';
		$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." $where ORDER BY displayorder");
		while($profile = DB::fetch($query)) {
			$profile['type'] = $lang['vars_type_'. $profile['type']];
			$checkavailable = $profile['available'] ? 'checked' : '';
			showtablerow('', array('class="td25"', 'class="td28"', '', '', '', '', '', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$profile[fieldid]\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayordernew[$profile[fieldid]]\" value=\"$profile[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"titlenew[$profile[fieldid]]\" value=\"$profile[title]\">",
				$profile['identifier'],
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$profile[fieldid]]\" value=\"1\">",
				$profile['type'],
				($profile['mid'] ? $modulelist[$profile['mid']] : $lang['all_module']),
				"<a href=\"".ADMINSCRIPT."?action=memberprofile&operation=detail&fieldid=$profile[fieldid]\" class=\"act\">$lang[detail]</a>",
			));
		}
		echo '<tr><td></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['profile_add'].'</a></div></td></tr>';
		showsubmit('profilesubmit', 'submit', 'del');

		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_titlenew'])) {
			foreach($_G['gp_titlenew'] as $id => $val) {
				DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='".dhtmlspecialchars(trim($_G['gp_titlenew'][$id]))."', available='".intval($_G['gp_available'][$id])."', displayorder='".intval($_G['gp_displayorder'][$id])."' WHERE mid='$id'");
			}
		}

		if(is_array($_G['gp_newtitle'])) {
			foreach($_G['gp_newtitle'] as $id => $val) {
				$newidentifier = dhtmlspecialchars(trim($_G['gp_newidentifier'][$id]));
				$query = DB::query("SELECT fieldid FROM ".DB::table('common_member_profile_setting')." WHERE identifier='$newidentifier' LIMIT 1");
				if(DB::num_rows($query) || strlen($newidentifier) > 40  || !ispluginkey($newidentifier)) {
					cpmsg('profile_optionvariable_invalid', '', 'error');
				}
				$data = array(
					'title' => dhtmlspecialchars(trim($_G['gp_newtitle'][$id])),
					'displayorder' => intval($_G['gp_newdisplayorder'][$id]),
					'identifier' => $newidentifier,
					'available' => intval($_G['gp_newavailable'][$id]),
					'type' => $_G['gp_newtype'][$id],
					'mid' => intval($_G['gp_newmodule'][$id]),
				);
				DB::insert('common_member_profile_setting', $data, true);
			}
		}

		cpmsg('memberprofile_succeed', 'action=memberprofile', 'succeed');

	}

} elseif($operation == 'detail') {

	$fieldid = intval($_G['gp_fieldid']);
	$profile = DB::fetch_first("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE fieldid='$fieldid'");

	if(!submitcheck('detailsubmit')) {

		shownav('global', 'nav_member_profile');
		showsubmenu("$lang[nav_member_profile] - $profile[title]");
		showformheader('memberprofile&operation=detail&fieldid='.$profile['fieldid']);
		showtableheader('member_profile', 'fixpadding');

		$typeselect = '<select name="typenew" onchange="var styles, key;styles=new Array(\'number\',\'text\',\'radio\', \'checkbox\', \'textarea\', \'select\', \'calendar\'); for(key in styles) {var obj=$(\'style_\'+styles[key]); obj.style.display = styles[key] == this.options[this.selectedIndex].value ? \'\' : \'none\';}">';
		foreach(array('number', 'text', 'radio', 'checkbox', 'textarea', 'select', 'calendar', 'email', 'url') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($profile['type'] == $type ? 'selected' : '').'>'.$lang['vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		$moduleselect = '';
		if(!empty($modulelist)) {
			$moduleselect = '<select name="modulenew"><option value="0">'.$lang['all_module'].'</option>';
			foreach($modulelist as $mid => $modname) {
				$selected = $mid == $profile['mid'] ? 'selected="selected"' : '';
				$moduleselect .= '<option value="'.$mid.'" '.$selected.'>'.$modname.'</option>';
			}
			$moduleselect .= '</select>';
		}

		$profile['rules'] = unserialize($profile['rules']);

		showsetting('name', 'titlenew', $profile['title'], 'text');
		showsetting('variable', 'identifiernew', $profile['identifier'], 'text');
		showsetting('type', '', '', $typeselect);
		showsetting('module', '', '', $moduleselect);
		showsetting('required', 'requirednew', $profile['required'], 'radio');
		showsetting('description', 'descriptionnew', $profile['description'], 'textarea');

		showtagheader('tbody', "style_calendar", $profile['type'] == 'calendar');
		showtitle('vars_type_calendar');
		showsetting('profile_edit_inputsize', 'rules[calendar][inputsize]', $profile['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_number", $profile['type'] == 'number');
		showtitle('vars_type_number');
		showsetting('profile_edit_maxnum', 'rules[number][maxnum]', $profile['rules']['maxnum'], 'text');
		showsetting('profile_edit_minnum', 'rules[number][minnum]', $profile['rules']['minnum'], 'text');
		showsetting('profile_edit_inputsize', 'rules[number][inputsize]', $profile['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_text", $profile['type'] == 'text');
		showtitle('vars_type_text');
		showsetting('profile_edit_textmax', 'rules[text][maxlength]', $profile['rules']['maxlength'], 'text');
		showsetting('profile_edit_inputsize', 'rules[text][inputsize]', $profile['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_textarea", $profile['type'] == 'textarea');
		showtitle('vars_type_textarea');
		showsetting('profile_edit_textmax', 'rules[textarea][maxlength]', $profile['rules']['maxlength'], 'text');
		showsetting('profile_edit_colsize', 'rules[textarea][colsize]', $profile['rules']['colsize'], 'text');
		showsetting('profile_edit_rowsize', 'rules[textarea][rowsize]', $profile['rules']['rowsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_select", $profile['type'] == 'select');
		showtitle('vars_type_select');
		showsetting('profile_edit_choices', 'rules[select][choices]', $profile['rules']['choices'], 'textarea');
		showsetting('profile_edit_inputsize', 'rules[select][inputsize]', $profile['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_radio", $profile['type'] == 'radio');
		showtitle('vars_type_radio');
		showsetting('profile_edit_choices', 'rules[radio][choices]', $profile['rules']['choices'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', "style_checkbox", $profile['type'] == 'checkbox');
		showtitle('vars_type_checkbox');
		showsetting('profile_edit_choices', 'rules[checkbox][choices]', $profile['rules']['choices'], 'textarea');
		showtagfooter('tbody');

		showsubmit('detailsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$titlenew = trim($_G['gp_titlenew']);
		if(!$titlenew || !$_G['gp_identifiernew']) {
			cpmsg('profile_option_invalid', '', 'error');
		}

		$query = DB::query("SELECT fieldid FROM ".DB::table('common_member_profile_setting')." WHERE identifier='{$_G['gp_identifiernew']}' AND fieldid!='{$_G['gp_fieldid']}' LIMIT 1");
		if(DB::num_rows($query) || strlen($_G['gp_identifiernew']) > 40  || !ispluginkey($_G['gp_identifiernew'])) {
			cpmsg('profile_optionvariable_invalid', '', 'error');
		}

		DB::update('common_member_profile_setting', array(
			'title' => $titlenew,
			'description' => dhtmlspecialchars(trim($_G['gp_descriptionnew'])),
			'identifier' => dhtmlspecialchars(trim($_G['gp_identifiernew'])),
			'type' => $_G['gp_typenew'],
			'mid' => intval($_G['gp_modulenew']),
			'required' => intval($_G['gp_requirednew']),
			'rules' => addslashes(serialize($_G['gp_rules'][$_G['gp_typenew']])),
		), "fieldid='{$_G['gp_fieldid']}'");

		cpmsg('memberprofile_succeed', 'action=memberprofile&operation=detail&fieldid='.$_G['gp_fieldid'], 'succeed');

	}
}

?>