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

$operation = in_array($operation, array('list')) ? $operation : 'list';

if($operation == 'list') {

	if(!submitcheck('profilesubmit')) {
		
		$moduleselect = '';

		if(!empty($modulelist)) {
			$moduleselect = '<select name="newmodule[]">';
			foreach($modulelist as $mid => $modname) {
				$moduleselect .= '<option value="'.$mid.'">'.$modname.'</option>';				
			}
			$moduleselect .= '</select>';
		}

		shownav('global', 'nav_credit');
		showsubmenu('nav_credit');
		showformheader('credit');

echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1, '', 'td25'],
		[1, '<input type="text" class="txt" size="15" name="newtitle[]">'],
		[1, '<input type="text" class="txt" size="15" name="newunit[]">'],
		[1, '<input type="text" class="txt" size="15" name="newicon[]">'],
		[1, '<input type="text" class="txt" size="15" name="newinital[]">'],
		[1, '$moduleselect'],		
	]
];
</script>
EOT;

		showtableheader('credit_list', 'fixpadding');
		showsubtitle(array('', 'name', 'unit', 'icon', 'inital', 'module'));

		$query = DB::query("SELECT * FROM ".DB::table('common_credit')." ORDER BY creditid");
		while($credit = DB::fetch($query)) {
			showtablerow('', array('class="td25"', '', '', '', '', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$credit[creditid]\">",
				"<input type=\"text\" class=\"txt\" name=\"titlenew[$credit[creditid]]\" value=\"$credit[title]\">",
				"<input type=\"text\" class=\"txt\" name=\"unitnew[$credit[creditid]]\" value=\"$credit[unit]\">",
				"<input type=\"text\" class=\"txt\" name=\"iconnew[$credit[creditid]]\" value=\"$credit[icon]\">",
				"<input type=\"text\" class=\"txt\" name=\"initalnew[$credit[creditid]]\" value=\"$credit[inital]\">",
				$modulelist[$credit['mid']]				
			));
		}
		echo '<tr><td></td><td colspan="6"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['add_credit'].'</a></div></td></tr>';
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
				$query = DB::query("SELECT creditid FROM ".DB::table('common_credit')." WHERE mid='".$_G['gp_newmodule'][$id]."' LIMIT 1");
				if(DB::num_rows($query)) {
					cpmsg('credit_add_invalid', '', 'error');
				}
				$data = array(
					'title' => dhtmlspecialchars(trim($_G['gp_newtitle'][$id])),
					'unit' => dhtmlspecialchars(trim($_G['gp_newunit'][$id])),
					'icon' => dhtmlspecialchars(trim($_G['gp_newicon'][$id])),
					'inital' => intval($_G['gp_newinital'][$id]),
					'mid' => intval($_G['gp_newmodule'][$id]),
				);
				DB::insert('common_credit', $data, true);
			}
		}

		cpmsg('credit_succeed', 'action=credit', 'succeed');

	}

}

?>