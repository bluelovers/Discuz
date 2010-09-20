<?php

/**
 *      [Æ·ÅÆ¿Õ¼ä] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: perm.inc.php 4117 2010-08-05 07:57:47Z xuhui $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}
$do = $_GET['do'];
//if($operation == 'perm') {

	$do = !in_array($do, array('group', 'member', 'gperm')) ? 'member' : $do;
	shownav('admintools', 'menu_founder_perm');

	if($do == 'group') {
		$id = intval($_GET['id']);

		if(!$id) {
			$query = DB::query("SELECT * FROM ".DB::table('admincp_group')." ORDER BY cpgroupid");
			$groups = array();
			while($group = DB::fetch($query)) {
				$groups[$group['cpgroupid']] = $group['cpgroupname'];
			}
			if(!submitcheck('submit')) {
				showsubmenu('menu_founder_perm', array(
					array('nav_founder_perm_member', 'perm&do=member',  0),
					array('nav_founder_perm_group', 'perm&do=group', 1),
				));
				showformheader('perm&do=group');
				showtableheader();
				showsubtitle(array('', 'founder_cpgroupname', ''));
				foreach($groups as $id => $group) {
					showtablerow('style="height:20px"', array('class="td25"', 'class="td24"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$id]\">",
						"<input type=\"text\" class=\"txtnobd\" onblur=\"this.className='txtnobd'\" onfocus=\"this.className='txt'\" size=\"15\" name=\"name[$id]\" value=\"$group\">",
						'<a href="'.$_SERVER[SCRIPT_NAME].'?action=perm&do=group&id='.$id.'">'.lang('edit').'</a>'
						));
				}
				showtablerow('style="height:20px"', array(), array(lang('add_new'), '<input class="txt" type="text" name="newcpgroupname" value="" />', ''));
				showsubmit('submit', 'submit', 'del');
				showtablefooter();
				showformfooter();
			} else {
				if(!empty($_POST['newcpgroupname'])) {
					if(DB::result_first("SELECT count(*) FROM ".DB::table('admincp_group')." WHERE cpgroupname='$_POST[newcpgroupname]'")) {
						cpmsg('founder_perm_group_name_duplicate', '', 'error', array('name' => $_POST['newcpgroupname']));
					}
					DB::insert('admincp_group', array('cpgroupname' => strip_tags($_POST['newcpgroupname'])));
				}
				if(!empty($_POST['delete'])) {
					DB::delete('admincp_perm', 'cpgroupid IN (\''.implode("', '", $_POST['delete']).'\')');
					DB::update('admincp_member', array('cpgroupid' => 0), 'cpgroupid IN (\''.implode("', '", $_POST['delete']).'\')');
					DB::delete('admincp_group', 'cpgroupid IN (\''.implode("', '", $_POST['delete']).'\')');
				}
				if(!empty($_POST['name'])) {
					foreach($_POST['name'] as $id => $name) {
						if($groups[$id] != $name) {
							if(($cpgroupid = DB::result_first("SELECT cpgroupid FROM ".DB::table('admincp_group')." WHERE cpgroupname='$name'")) && $_POST['name'][$cpgroupid] == $groups[$cpgroupid]) {
								cpmsg('founder_perm_group_name_duplicate', '', 'error', array('name' => $name));
							}
							DB::update('admincp_group', array('cpgroupname' => $name), "cpgroupid='$id'");
						}
					}
				}
				cpmsg('founder_perm_group_update_succeed', 'admin.php?action=perm&do=group', 'succeed');
			}
		} else {
			if(!submitcheck('submit')) {

				showpermstyle();
				$query = DB::query("SELECT * FROM ".DB::table('admincp_perm')." WHERE cpgroupid='$id'");
				$perms = array();
				while($perm = DB::fetch($query)) {
					$perms[] = $perm['perm'];
				}
				$cpgroup = DB::fetch_first("SELECT * FROM ".DB::table('admincp_group')." WHERE cpgroupid='$id'");
				$data = getactionarray();
				$query = DB::query("SELECT * FROM ".DB::table('admincp_group')." ORDER BY cpgroupid");
				$grouplist = '';
				while($ggroup = DB::fetch($query)) {
					$grouplist .= '<a href="###" onclick="location.href=\''.$_SERVER[SCRIPT_NAME].'?action=perm&do=group&switch=yes&id='.$ggroup['cpgroupid'].'&scrolltop=\'+document.documentElement.scrollTop"'.($_GET['id'] == $ggroup['cpgroupid'] ? ' class="current"' : '').'>'.$ggroup['cpgroupname'].'</a>';
				}
				$grouplist = '<span id="cpgselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'cpgselect_menu\').style.top=(parseInt($(\'cpgselect_menu\').style.top)-document.documentElement.scrollTop)+\'px\'">'.lang('founder_group_switch').'<em>&nbsp;&nbsp;</em></span>'.
					'<div id="cpgselect_menu" class="popupmenu_popup" style="display:none">'.$grouplist.'</div>';

				showsubmenu('menu_founder_groupperm', array(array()), $grouplist, array('group' => $cpgroup['cpgroupname']));
				showformheader('perm&do=group&id='.$id);
				showtableheader();
				foreach($data['cats'] as $topkey) {
					if(!$data['actions'][$topkey]) {
						continue;
					}
					$checkedall = true;
					$row = '<tr><td class="vtop" id="perms_'.$topkey.'">';
					foreach($data['actions'][$topkey] as $k => $item) {
						if(!$item) {
							continue;
						}
						$checked = @in_array($item[1], $perms);
						if(!$checked) {
							$checkedall = false;
						}
						$row .= '<div class="item'.($checked ? ' checked' : '').'"><a class="right" title="'.lang('config').'" href="'.$_SERVER[SCRIPT_NAME].'?frames=yes&action=perm&do=gperm&gset='.$topkey.'_'.$k.'" target="_blank">&nbsp;</a><label class="txt"><input name="permnew[]" value="'.$item[1].'" class="checkbox" type="checkbox" '.($checked ? 'checked="checked" ' : '').' onclick="checkclk(this)" />'.lang($item[0]).'</label></div>';
					}
					$row .= '</td></tr>';
					if($topkey != 'setting') {
						showtitle('<label><input class="checkbox" type="checkbox" onclick="permcheckall(this, \'perms_'.$topkey.'\')" '.($checkedall ? 'checked="checked" ' : '').'/> '.lang('header_'.$topkey).'</label>');
					} else {
						showtitle('founder_perm_setting');
					}
					echo $row;
				}
				if(!empty($cpgroup['cpgroupshopcats'])) {
				    $group['shop_field'] = explode(",", $cpgroup['cpgroupshopcats']);
				}
				showfieldform('shop');
                showjscatefield();
				showsubmit('submit');
				showtablefooter();
				showformfooter();
				if(!empty($_GET['switch'])) {
					echo '<script type="text/javascript">showMenu({\'ctrlid\':\'cpgselect\',\'pos\':\'34\'});</script>';
				}

			} else {
				DB::delete('admincp_perm', "cpgroupid='$id'");
				if($_POST['permnew']) {
					foreach($_POST['permnew'] as $perm) {
						DB::insert('admincp_perm', array('cpgroupid' => $id, 'perm' => $perm));
					}
				}
                //if(!empty($_POST['shop_field'])) {
					DB::update('admincp_group', array('cpgroupshopcats' => implode(",", $_POST['shop_field'])), "cpgroupid=".$id);
                //}
				cpmsg('founder_perm_groupperm_update_succeed', 'admin.php?action=perm&do=group', 'succeed');
			}
		}

	} elseif($do == 'member') {

		$founders = $_SC['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes($_SC['founder']))) : array();
		//print_r($founders);
		if($founders) {
			$founderexists = true;
			$fuid = $fuser = array();
			foreach($founders as $founder) {
				if(is_numeric($founder)) {
					$fuid[] = $founder;
				} else {
					$fuser[] = $founder;
				}
			}
			$query = DB::query("SELECT uid, username FROM ".DB::table('members')." WHERE ".($fuid ? "uid IN ('".implode("', '", $fuid)."')" : '0')." OR ".($fuser ? "username IN ('".implode("', '", $fuser)."')" : '0'));
			$founders = array();
			while($founder = DB::fetch($query)) {
				$founders[$founder['uid']] = $founder['username'];
			}
		} else {
			$founderexists = false;
			$query = DB::query("SELECT uid, username FROM ".DB::table('members')." WHERE adminid='1'");
			$founders = array();
			while($founder = DB::fetch($query)) {
				$founders[$founder['uid']] = $founder['username'];
			}
		}
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
        //print_r($id);exit();
		if(!$id) {
			if(!submitcheck('submit')) {
				showsubmenu('menu_founder_perm', array(
					array('nav_founder_perm_member', 'perm&do=member',  1),
					array('nav_founder_perm_group', 'perm&do=group', 0),
				));
				$query = DB::query("SELECT * FROM ".DB::table('admincp_group')." ORDER BY cpgroupid");
				$groupselect = '<select name="newcpgroupid">';
				$groups = array();
				while($group = DB::fetch($query)) {
					$groupselect .= '<option value="'.$group['cpgroupid'].'">'.$group['cpgroupname'].'</option>';
					$groups[$group['cpgroupid']] = $group['cpgroupname'];
				}
				$groupselect .= '</select>';
				$query = DB::query("SELECT * FROM ".DB::table('admincp_member'));
				$members = $adminmembers = array();
				while($adminmember = DB::fetch($query)) {
					$adminmembers[$adminmember['uid']] = $adminmember;
				}
				foreach($founders as $uid => $founder) {
					$members[$uid] = array('uid' => $uid, 'username' => $founder, 'cpgroupname' => lang('founder_admin'));
				}
				if($adminmembers) {
					$query = DB::query("SELECT uid, username FROM ".DB::table('members')." WHERE uid IN ('".implode("', '", array_keys($adminmembers))."')");
					while($member = DB::fetch($query)) {
						if(isset($members[$member['uid']])) {
							DB::delete('admincp_member', array('uid' => $member['uid']));
							continue;
						}
						$member['cpgroupname'] = !empty($adminmembers[$member['uid']]['cpgroupid']) ? $groups[$adminmembers[$member['uid']]['cpgroupid']] : lang('founder_master');
						if(!$founderexists && in_array($member['uid'], array_keys($founders))) {
							$member['cpgroupname'] = lang('founder_admin');
						}
						$members[$member['uid']] = $member;
					}
				}
				if(!$founderexists) {
					showtips(lang('home_security_nofounder').lang('home_security_founder'));
				} else {
					showtips('home_security_founder');
				}
				showformheader('perm&do=member');
				showtableheader();
				showsubtitle(array('', 'founder_username', 'founder_usergname', ''));
				foreach($members as $id => $member) {
					$isfounder = array_key_exists($id, $founders);
					showtablerow('style="height:20px"', array('class="td25"', 'class="td24"', 'class="td24"'), array(
						!$isfounder || isset($adminmembers[$member['uid']]['cpgroupid']) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$id]\">" : '',
						"<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
						$member['cpgroupname'],
						!$isfounder && $adminmembers[$member['uid']]['cpgroupid'] ? '<a href="'.$_SERVER[SCRIPT_NAME].'?action=perm&do=member&id='.$id.'">'.lang('edit').'</a>' : ''
						));
				}
				showtablerow('style="height:20px"', array('class="td25"', 'class="td24"', 'class="td24"'), array(lang('add_new'), '<input class="txt" type="text" name="newcpusername" value="" />', $groupselect, ''));
				showsubmit('submit', 'submit', 'del');
				showtablefooter();
				showformfooter();
			} else {
				if(!empty($_POST['newcpusername'])) {
					$newadmin = DB::fetch_first("SELECT uid, myshopid FROM ".DB::table("members")." WHERE username='$_POST[newcpusername]'");
					
					//$newcpuid = DB::result_first("SELECT uid FROM ".DB::table("members")." WHERE username='$_POST[newcpusername]'");
					
					if(!$newadmin['uid']) {
						cpmsg('founder_perm_member_noexists', '', 'error');
					}
					if($newadmin['myshopid'] != 0) {
						cpmsg('founder_perm_member_haveshop', '', 'error');
					}
					$newcpuid = $newadmin['uid'];
					if(DB::result_first("SELECT count(*) FROM ".DB::table('admincp_member')." WHERE uid='$newcpuid'") || array_key_exists($newcpuid, $founders)) {
						cpmsg('founder_perm_member_duplicate', '', 'error');
					}
					DB::insert('admincp_member', array('uid' => $newcpuid, 'cpgroupid' => $_POST['newcpgroupid']));
					DB::update('members', array('allowadmincp' => 1), "uid='$newcpuid'");
				}
				if(!empty($_POST['delete'])) {
					DB::delete('admincp_member', 'uid IN (\''.implode("', '", $_POST['delete']).'\')');
					DB::update('members', array('allowadmincp' => 0), 'uid IN (\''.implode("', '", $_POST['delete']).'\')');
				}
				cpmsg('founder_perm_member_update_succeed', 'admin.php?action=perm&do=member', 'succeed');
			}
		} else {
			if(!submitcheck('submit')) {
				$member = DB::fetch_first("SELECT * FROM ".DB::table('admincp_member')." WHERE uid='$id'");
				if(!$member) {
					cpmsg('founder_perm_member_noexists', '', 'error');
				}
				$username = DB::result_first("SELECT username FROM ".DB::table("members")." WHERE uid='$id'");
				$cpgroupid = empty($_GET['cpgroupid']) ? $member['cpgroupid'] : $_GET['cpgroupid'];
				$member['customperm'] = empty($_GET['cpgroupid']) || $_GET['cpgroupid'] == $member['cpgroupid'] ? unserialize($member['customperm']) : array();
				$query = DB::query("SELECT * FROM ".DB::table('admincp_perm')." WHERE cpgroupid='$cpgroupid'");
				$perms = array();
				while($perm = DB::fetch($query)) {
					$perms[] = $perm['perm'];
				}
				$data = getactionarray();

				$query = DB::query("SELECT * FROM ".DB::table('admincp_group')." ORDER BY cpgroupid");
				$groupselect = '<select name="cpgroupidnew" onchange="location.href=\''.$_SERVER[SCRIPT_NAME].'?action=perm&do=member&id='.$id.'&cpgroupid=\' + this.value">';
				while($group = DB::fetch($query)) {
					$groupselect .= '<option value="'.$group['cpgroupid'].'"'.($group['cpgroupid'] == $cpgroupid ? ' selected="selected"' : '').'>'.$group['cpgroupname'].'</option>';
				}
				$groupselect .= '</select>';

				showpermstyle();
				showsubmenu('menu_founder_memberperm', array(array()), '', array('username' => $username));

				showformheader('perm&do=member&id='.$id);
				showtableheader();
				showsetting('founder_usergname', '', '', $groupselect);
				showtablefooter();
				showtableheader();
				foreach($data['cats'] as $topkey) {
					if(!$data['actions'][$topkey]) {
						continue;
					}
					$checkedall = true;
					$row = '<tr><td class="vtop" id="perms_'.$topkey.'">';
					foreach($data['actions'][$topkey] as $item) {
						if(!$item) {
							continue;
						}
						$checked = @in_array($item[1], $perms);
						$customchecked = @in_array($item[1], $member['customperm']);
						$extra = $checked ? ($customchecked ? '' : 'checked="checked" ').' onclick="checkclk(this)"' : 'disabled="disabled" ';
						if(!$checked || $customchecked) {
							$checkedall = false;
						}
						$row .= '<div class="item'.($checked && !$customchecked ? ' checked' : '').'"><label class="txt"><input name="permnew[]" value="'.$item[1].'" class="checkbox" type="checkbox" '.$extra.'/>'.lang($item[0]).'</label></div>';
					}
					$row .= '</td></tr>';
					if($topkey != 'setting') {
						showtitle('<input class="checkbox" type="checkbox" onclick="permcheckall(this, \'perms_'.$topkey.'\')" '.($checkedall ? 'checked="checked" ' : '').'/> '.lang('nav_'.$topkey).'</label>');
					} else {
						showtitle('founder_perm_setting');
					}
					echo $row;
				}
				showsubmit('submit');
				showtablefooter();
				showformfooter();
			} else {
				$gp_permnew = !empty($_POST['permnew']) ? $_POST['permnew'] : array();
				$cpgroupidnew = $_POST['cpgroupidnew'];
				$query = DB::query("SELECT * FROM ".DB::table('admincp_perm')." WHERE cpgroupid='$cpgroupidnew'");
				$perms = array();
				while($perm = DB::fetch($query)) {
					$perms[] = $perm['perm'];
				}
				$customperm = addslashes(serialize(array_diff($perms, $gp_permnew)));
				DB::update('admincp_member', array('cpgroupid' => $cpgroupidnew, 'customperm' => $customperm), "uid='$id'");
				cpmsg('founder_perm_member_update_succeed', 'admin.php?action=perm&do=member', 'succeed');
			}
		}

	} elseif($do == 'gperm' && !empty($_GET['gset'])) {

		$gset = $_GET['gset'];
		list($topkey, $k) = explode('_', $gset);
		$data = getactionarray();
		$gset = $data['actions'][$topkey][$k];
		if(!$gset) {
			cpmsg('undefined_action', '', 'error');
		}
		if(!submitcheck('submit')) {
			$query = DB::query("SELECT cpg.*,cpp.perm FROM ".DB::table('admincp_group')." cpg LEFT JOIN ".DB::table('admincp_perm')." cpp ON cpg.cpgroupid=cpp.cpgroupid AND cpp.perm='$gset[1]' ORDER BY cpg.cpgroupid");
			$groups = array();
			while($group = DB::fetch($query)) {
				$groups[$group['cpgroupid']] = $group;
			}
			showsubmenu('menu_founder_permgrouplist', array(array()), '', array('perm' => lang($gset[0])));

			showformheader('perm&do=gperm&gset='.$_GET['gset']);
			showtableheader();
			showsubtitle(array('', 'founder_usergname'));
			foreach($groups as $id => $group) {
				showtablerow('style="height:20px"', array('class="td25"', ''), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"permnew[]\" ".($group['perm'] ? 'checked="checked"' : '')." value=\"$id\">",
					$group['cpgroupname']
					));
			}
			showsubmit('submit');
			showtablefooter();
			showformfooter();
		} else {
			$query = DB::query("SELECT * FROM ".DB::table('admincp_group'));
			while($group = DB::fetch($query)) {
				if(in_array($group['cpgroupid'], $_POST['permnew'])) {
					DB::insert('admincp_perm', array('cpgroupid' => $group['cpgroupid'], 'perm' => $gset[1]), false, true);
				} else {
					DB::delete('admincp_perm', "cpgroupid='$group[cpgroupid]' AND perm='$gset[1]'");
				}
			}
			cpmsg('founder_perm_gperm_update_succeed', 'admin.php?action=perm', 'succeed');
		}

	}
//}

function getactionarray() {
	$isfounder = false;
	require_once(B_ROOT.'./source/admininc/perm.inc.php');
	unset($topmenu['index'], $menu['index']);
	$actioncat = $actionarray = array();
	$actioncat[] = 'setting';
	$actioncat = array_merge($actioncat, array_keys($topmenu));
	$actionarray['setting'][] = array('founder_perm_allowpost', '_allowpost');
	
	foreach($menu as $tkey => $items) {
		foreach($items as $item) {
			$actionarray[$tkey][] = $item;
		}
	}
	return array('actions' => $actionarray, 'cats' => $actioncat);
}

function showpermstyle() {
	echo <<<EOF
	<style>
.item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
.vtop .right, .item .right{ padding: 0 10px; line-height: 22px; background: url('static/image/admincp/bg_repno.gif') no-repeat -286px -145px; font-weight: normal;margin-right:10px; }
.vtop a:hover.right, .item a:hover.right { text-decoration:none; }
</style>
<script type="text/JavaScript">
function permcheckall(obj, perms, t) {
	var t = !t ? 0 : t;
	var checkboxs = $("#"+perms+" :input");
	for(var i = 0; i < checkboxs.length; i++) {
		var e = checkboxs[i];
		if(e.type == 'checkbox') {
			if(!t) {
				if(!e.disabled) {
					e.checked = obj.checked;
				}
			} else {
				if(obj != e) {
					e.style.visibility = obj.checked ? 'hidden' : 'visible';
				}
			}
		}
	}
}
function checkclk(obj) {
	var obj = obj.parentNode.parentNode;
	obj.className = obj.className == 'item' ? 'item checked' : 'item';
}
</script>
EOF;
}

?>