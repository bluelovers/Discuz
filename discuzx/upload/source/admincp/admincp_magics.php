<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_magics.php 22793 2011-05-23 01:00:07Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation ? $operation : 'admin';

if($operation == 'admin') {

	if(!submitcheck('magicsubmit')) {

		shownav('extended', 'magics', 'admin');
		showsubmenu('nav_magics', array(
			array('admin', 'magics&operation=admin', 1),
			array('nav_magics_confer', 'members&operation=confermagic', 0)
		));
		showtips('magics_tips');

		$settings = array();
		$query = DB::query("SELECT skey, svalue FROM ".DB::table('common_setting')." WHERE skey IN ('magicstatus', 'magicdiscount')");
		while($setting = DB::fetch($query)) {
			$settings[$setting['skey']] = $setting['svalue'];
		}
		showformheader('magics&operation=admin');
		showtableheader();
		showsetting('magics_config_open', 'settingsnew[magicstatus]', $settings['magicstatus'], 'radio');
		showsetting('magics_config_discount', 'settingsnew[magicdiscount]', $settings['magicdiscount'], 'text');
		showtablefooter();

		showtableheader('magics_list', 'fixpadding');
		$newmagics = getmagics();
		showsubtitle(array('', 'display_order', '<input type="checkbox" onclick="checkAll(\'prefix\', this.form, \'available\', \'availablechk\')" class="checkbox" id="availablechk" name="availablechk">'.cplang('available'), 'name', $lang['price'], $lang['magics_num'], 'weight'));

		$query = DB::query("SELECT * FROM ".DB::table('common_magic')." ORDER BY displayorder");
		while($magic = DB::fetch($query)) {
			$magic['credit'] = $magic['credit'] ? $magic['credit'] : $_G['setting']['creditstransextra'][3];
			$credits = '<select name="credit['.$magic['magicid'].']">';
			foreach($_G['setting']['extcredits'] as $i => $extcredit) {
				$credits .= '<option value="'.$i.'" '.($i == $magic['credit'] ? 'selected' : '').'>'.$extcredit['title'].'</option>';
			}
			$credits .= '</select>';
			$magictype = $lang['magics_type_'.$magic['type']];
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', '', ''), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$magic[magicid]\">",
				"<input type=\"text\" class=\"txt\" name=\"displayorder[$magic[magicid]]\" value=\"$magic[displayorder]\">",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$magic[magicid]]\" value=\"1\" ".($magic['available'] ? 'checked' : '').">",
				"<input type=\"text\" class=\"txt\" style=\"width:80px\" name=\"name[$magic[magicid]]\" value=\"$magic[name]\">".
					(file_exists(DISCUZ_ROOT.'./static/image/magic/'.$magic['identifier'].'.gif') ? '<img class="vmiddle" src="static/image/magic/'.$magic['identifier'].'.gif" />' : ''),
				"<input type=\"text\" class=\"txt\" name=\"price[$magic[magicid]]\" value=\"$magic[price]\">".$credits,
				"<input type=\"text\" class=\"txt\" name=\"num[$magic[magicid]]\" value=\"$magic[num]\">".
					($magic['supplytype'] ? '/ '.$magic['supplynum'].' / '.$lang['magic_suppytype_'.$magic['supplytype']] : ''),
				"<input type=\"text\" class=\"txt\" name=\"weight[$magic[magicid]]\" value=\"$magic[weight]\"><input type=\"hidden\" name=\"identifier[$magic[magicid]]\" value=\"$magic[identifier]\">",
				"<a href=\"".ADMINSCRIPT."?action=magics&operation=edit&magicid=$magic[magicid]\" class=\"act\">$lang[detail]</a>"
			));
			unset($newmagics[$magic[identifier]]);
		}
		foreach($newmagics as $newmagic) {
			$credits = '<select name="newcredit['.$newmagic['class'].']">';
			foreach($_G['setting']['extcredits'] as $i => $extcredit) {
				$credits .= '<option value="'.$i.'">'.$extcredit['title'].'</option>';
			}
			$credits .= '</select>';
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', '', ''), array(
				'',
				"<input type=\"text\" class=\"txt\" name=\"newdisplayorder[$newmagic[class]]\" value=\"0\">",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"newavailable[$newmagic[class]]\" value=\"1\">",
				"<input type=\"text\" class=\"txt\" style=\"width:80px\" name=\"newname[$newmagic[class]]\" value=\"$newmagic[name]\">".
					(file_exists(DISCUZ_ROOT.'./static/image/magic/'.$newmagic['class'].'.gif') ? '<img class="vmiddle" src="static/image/magic/'.$newmagic['class'].'.small.gif" />' : '').
					"<input type=\"hidden\" name=\"newdesc[$newmagic[class]]\" value=\"$newmagic[desc]\" />".
					"<input type=\"hidden\" name=\"newuseevent[$newmagic[class]]\" value=\"$newmagic[useevent]\" />",
				"<input type=\"text\" class=\"txt\" name=\"newprice[$newmagic[class]]\" value=\"$newmagic[price]\">".$credits,
				"<input type=\"text\" class=\"txt\" name=\"newnum[$newmagic[class]]\" value=\"0\">",
				"<input type=\"text\" class=\"txt\" name=\"newweight[$newmagic[class]]\" value=\"$newmagic[weight]\">",
				'<font color="#F00">New!</font>'
			));
		}
		showsubmit('magicsubmit', 'submit', 'del', '&nbsp;&nbsp;<input type="checkbox" onclick="checkAll(\'prefix\', this.form, \'available\', \'availablechk1\')" class="checkbox" id="availablechk1" name="availablechk1">'.cplang('available'));
		showtablefooter();
		showformfooter();

	} else {
		if(is_array($_G['gp_settingsnew'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('magicstatus', '{$_G[gp_settingsnew][magicstatus]}')");
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('magicdiscount', '{$_G[gp_settingsnew][magicdiscount]}')");
		}

		if($ids = dimplode($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('common_magic')." WHERE magicid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE magicid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('common_magiclog')." WHERE magicid IN ($ids)");
		}

		if(is_array($_G['gp_name'])) {
			foreach($_G['gp_name'] as $id => $val) {
				if(!is_array($_G['gp_identifier']) ||
					!is_array($_G['gp_displayorder']) || !is_array($_G['gp_credit']) ||
					!is_array($_G['gp_price']) || !is_array($_G['gp_num']) ||
					!is_array($_G['gp_weight']) || !preg_match('/^\w+$/', $_G['gp_identifier'][$id])) {
					continue;
				}
				DB::query("UPDATE ".DB::table('common_magic')." SET available='".$_G['gp_available'][$id]."', name='$val', identifier='".$_G['gp_identifier'][$id]."', displayorder='".$_G['gp_displayorder'][$id]."', credit='".$_G['gp_credit'][$id]."', price='".$_G['gp_price'][$id]."', num='".$_G['gp_num'][$id]."', weight='".$_G['gp_weight'][$id]."' WHERE magicid='$id'");
			}
		}

		if(is_array($_G['gp_newname'])) {

			foreach($_G['gp_newname'] as $identifier => $name) {
				$data = array(
					'name' => $name,
					'useevent' => $_G['gp_newuseevent'][$identifier],
					'identifier' => $identifier,
					'available' => $_G['gp_newavailable'][$identifier],
					'description' => $_G['gp_newdesc'][$identifier],
					'displayorder' => $_G['gp_newdisplayorder'][$identifier],
					'credit' => $_G['gp_newcredit'][$identifier],
					'price' => $_G['gp_newprice'][$identifier],
					'num' => $_G['gp_newnum'][$identifier],
					'weight' => $_G['gp_newweight'][$identifier],
				);
				DB::insert('common_magic', $data);
			}
		}

		updatecache(array('setting', 'magics'));
		cpmsg('magics_data_succeed', 'action=magics&operation=admin', 'succeed');

	}

} elseif($operation == 'edit') {

	$magicid = intval($_G['gp_magicid']);
	$magic = DB::fetch_first("SELECT * FROM ".DB::table('common_magic')." WHERE magicid='$magicid'");

	if(!submitcheck('magiceditsubmit')) {

		$magicperm = unserialize($magic['magicperm']);

		$groups = $forums = array();
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup'));
		while($group = DB::fetch($query)) {
			$groups[$group['groupid']] = $group['grouptitle'];
		}

		$typeselect = array($magic['type'] => 'selected');

		shownav('extended', 'magics', 'admin');
		showsubmenu('nav_magics', array(
			array('admin', 'magics&operation=admin', 0),
			array('nav_magics_confer', 'members&operation=confermagic', 0)
		));
		echo '<br />';

		require_once libfile('magic/'.$magic['identifier'], 'class');
		$magicclass = 'magic_'.$magic['identifier'];
		$magicclass = new $magicclass;
		$magicsetting = $magicclass->getsetting($magicperm);
		echo '<div class="colorbox"><h4>'.lang('magic/'.$magic['identifier'], $magicclass->name).'</h4>'.
			'<table cellspacing="0" cellpadding="3"><tr><td>'.
			(file_exists(DISCUZ_ROOT.'./static/image/magic/'.$magic['identifier'].'.gif') ? '<img src="static/image/magic/'.$magic['identifier'].'.gif" />' : '').
			'</td><td valign="top">'.lang('magic/'.$magic['identifier'], $magicclass->description).'</td></tr></table>'.
			'<div style="width:95%" align="right">'.lang('magic/'.$magic['identifier'], $magicclass->copyright).'</div></div>';
		$credits = array();
		foreach($_G['setting']['extcredits'] as $i => $extcredit) {
			$credits[] = array($i, $extcredit['title']);
		}

		showformheader('magics&operation=edit&magicid='.$magicid);
		showtableheader();
		showtitle($lang['magics_edit'].' - '.$magic['name'].'('.$magic['identifier'].')');
		showsetting('magics_edit_name', 'namenew', $magic['name'], 'text');
		showsetting('magics_edit_credit', array('creditnew', $credits), $magic['credit'], 'select');
		showsetting('magics_edit_price', 'pricenew', $magic['price'], 'text');
		showsetting('magics_edit_num', 'numnew', $magic['num'], 'text');
		showsetting('magics_edit_supplynum', 'supplynumnew', $magic['supplynum'], 'text');
		showsetting('magics_edit_weight', 'weightnew', $magic['weight'], 'text');
		showsetting('magics_edit_supplytype', array('supplytypenew', array(
			array(0, $lang['magics_goods_stack_none']),
			array(1, $lang['magics_goods_stack_day']),
			array(2, $lang['magics_goods_stack_week']),
			array(3, $lang['magics_goods_stack_month']),
		)), $magic['supplytype'], 'mradio');
		showsetting('magics_edit_useperoid', array('useperoidnew', array(
			array(0, $lang['magics_edit_useperoid_none']),
			array(1, $lang['magics_edit_useperoid_day']),
			array(4, $lang['magics_edit_useperoid_24hr']),
			array(2, $lang['magics_edit_useperoid_week']),
			array(3, $lang['magics_edit_useperoid_month']),
		)), $magic['useperoid'], 'mradio');
		showsetting('magics_edit_usenum', 'usenumnew', $magic['usenum'], 'text');
		showsetting('magics_edit_description', 'descriptionnew', $magic['description'], 'textarea');

		if(is_array($magicsetting)) {
			foreach($magicsetting as $settingvar => $setting) {
				if(!empty($setting['value']) && is_array($setting['value'])) {
					foreach($setting['value'] as $k => $v) {
						$setting['value'][$k][1] = lang('magic/'.$magic['identifier'], $setting['value'][$k][1]);
					}
				}
				$varname = in_array($setting['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
					($setting['type'] == 'mselect' ? array('perm['.$settingvar.'][]', $setting['value']) : array('perm['.$settingvar.']', $setting['value']))
					: 'perm['.$settingvar.']';
				$value = $magicperm[$settingvar] != '' ? dstripslashes($magicperm[$settingvar]) : $setting['default'];
				$comment = lang('magic/'.$magic['identifier'], $setting['title'].'_comment');
				$comment = $comment != $setting['title'].'_comment' ? $comment : '';
				showsetting(lang('magic/'.$magic['identifier'], $setting['title']).':', $varname, $value, $setting['type'], '', 0, $comment);
			}
		}

		showtitle('magics_edit_perm');
		showtablerow('', 'colspan="2" class="td27"', $lang['magics_edit_usergroupperm'].':<input class="checkbox" type="checkbox" name="chkall1" onclick="checkAll(\'prefix\', this.form, \'usergroupsperm\', \'chkall1\', true)" id="chkall1" /><label for="chkall1"> '.cplang('select_all').'</label>');
		showtablerow('', 'colspan="2"', mcheckbox('usergroupsperm', $groups, explode("\t", $magicperm['usergroups'])));

		if(!empty($magicclass->targetgroupperm)) {
			showtablerow('', 'colspan="2" class="td27"', $lang['magics_edit_targetgroupperm'].':<input class="checkbox" type="checkbox" name="chkall2" onclick="checkAll(\'prefix\', this.form, \'targetgroupsperm\', \'chkall2\', true)" id="chkall2" /><label for="chkall2"> '.cplang('select_all').'</label>');
			showtablerow('', 'colspan="2"', mcheckbox('targetgroupsperm', $groups, explode("\t", $magicperm['targetgroups'])));
		}
		showsubmit('magiceditsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$namenew	= dhtmlspecialchars(trim($_G['gp_namenew']));
		$identifiernew	= dhtmlspecialchars(trim(strtoupper($_G['gp_identifiernew'])));
		$descriptionnew	= dhtmlspecialchars($_G['gp_descriptionnew']);
		$availablenew   = !$identifiernew ? 0 : 1;

		$magicperm['usergroups'] = is_array($_G['gp_usergroupsperm']) && !empty($_G['gp_usergroupsperm']) ? "\t".implode("\t",$_G['gp_usergroupsperm'])."\t" : '';
		$magicperm['targetgroups'] = is_array($_G['gp_targetgroupsperm']) && !empty($_G['gp_targetgroupsperm']) ? "\t".implode("\t",$_G['gp_targetgroupsperm'])."\t" : '';
		require_once libfile('magic/'.$magic['identifier'], 'class');
		$magicclass = 'magic_'.$magic['identifier'];
		$magicclass = new $magicclass;
		$magicclass->setsetting($magicperm, $_G['gp_perm']);
		$magicpermnew = addslashes(serialize($magicperm));

		$supplytypenew = intval($_G['gp_supplytypenew']);
		$supplynumnew = $_G['gp_supplytypenew'] ? intval($_G['gp_supplynumnew']) : 0;
		$usenumnew = intval($_G['gp_usenumnew']);
		$useperoidnew = $_G['gp_useperoidnew'] ? intval($_G['gp_useperoidnew']) : 0;
		$creditnew = intval($_G['gp_creditnew']);

		if(!$namenew) {
			cpmsg('magics_parameter_invalid', '', 'error');
		}

		$query = DB::query("SELECT magicid FROM ".DB::table('common_magic')." WHERE identifier='$identifiernew' AND magicid!='$magicid'");
		if(DB::num_rows($query)) {
			cpmsg('magics_identifier_invalid', '', 'error');
		}

		DB::query("UPDATE ".DB::table('common_magic')." SET name='$namenew', description='$descriptionnew', price='$_G[gp_pricenew]', num='$_G[gp_numnew]', supplytype='$supplytypenew', supplynum='$supplynumnew', useperoid='$useperoidnew', usenum='$usenumnew', weight='$_G[gp_weightnew]', magicperm='$magicpermnew', credit='$creditnew' WHERE magicid='$magicid'");

		updatecache(array('setting', 'magics'));
		cpmsg('magics_data_succeed', 'action=magics&operation=admin', 'succeed');

	}

}

function getmagics() {
	global $_G;
	$dir = DISCUZ_ROOT.'./source/class/magic';
	$magicdir = dir($dir);
	$magics = array();
	while($entry = $magicdir->read()) {
		if(!in_array($entry, array('.', '..')) && preg_match("/^magic\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
			@include_once $dir.'/'.$entry;
			$magicclass = substr($entry, 0, -4);
			if(class_exists($magicclass)) {
				$magic = new $magicclass();
				$script = substr($magicclass, 6);
				$magics[$script] = array(
					'class' => $script,
					'name' => lang('magic/'.$script, $magic->name),
					'desc' => lang('magic/'.$script, $magic->description),
					'price' => $magic->price,
					'weight' => $magic->weight,
					'useevent' => !empty($magic->useevent) ? $magic->useevent : 0,
					'version' => $adv->version,
					'copyright' => lang('magic/'.$script, $magic->copyright),
					'filemtime' => @filemtime($dir.'/'.$entry)
				);
			}
		}
	}
	uasort($magics, 'filemtimesort');
	return $magics;
}

?>