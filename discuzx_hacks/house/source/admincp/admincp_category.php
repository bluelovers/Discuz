<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threadtypes.php 7241 2010-03-31 08:13:42Z tiger $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
@set_time_limit(600);
cpheader();

@define('HOUSE_VERSION', '1.0RC');
@define('HOUSE_RELEASE', '20100727');

$doarray = array('house');
$do = in_array($_G['gp_do'], $doarray) ? $_G['gp_do'] : '';

if($do = 'house') {
	$cid = 1;
	$classid = 2;
	$modurl = 'house.php';
}

require_once libfile('function/category');

if($operation == 'channel') {

	if(!submitcheck('channelsubmit')) {

		$channel = DB::fetch_first("SELECT * FROM ".DB::table('category_channel')."");
		$channel['managegid'] = $channel['managegid'] ? unserialize($channel['managegid']) : array();
		$channel['mapinfo'] = $channel['mapinfo'] ? unserialize($channel['mapinfo']) : array();
		$channel['seoinfo'] = $channel['seoinfo'] ? unserialize($channel['seoinfo']) : array();
		if($channel['logo']) {
			$valueparse = parse_url($channel['logo']);
			if(isset($valueparse['host'])) {
				$groupbanner = $channel['logo'];
			} else {
				$groupbanner = $_G['setting']['attachurl'].'common/'.$channel['logo'].'?'.random(6);
			}
			$logohtml = '<img src="'.$groupbanner.'" />';
		}

		shownav('house', 'category_channel');
		showsubmenu('category_channel');

		showformheader("category&operation=channel&do=$do", 'enctype');
		showtableheader();
		showtitle($channel['title']);
		showsetting('category_channel_open', 'statusnew', $channel['status'], 'radio');
		showsetting('category_channel_title', 'titlenew', $channel['title'], 'text');
		showsetting('category_channel_logo', 'logonew', $channel['logo'], 'filetext', '', 0, $logohtml);
		showsetting('category_channel_listmode', array('listmodenew', array(
			array('text', cplang('category_channel_listmode_text')),
			array('pic', cplang('category_channel_listmode_pic')))), $channel['listmode'], 'mradio');
		showtitle(cplang('category_option_usergroup'));
		$varname = array('newmanagegid', array(), 'isfloat');
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." WHERE radminid IN('1','2') ORDER BY groupid");
		while($ugroup = DB::fetch($query)) {
			$varname[1][] = array($ugroup['groupid'], $ugroup['grouptitle'], '1');
		}
		showsetting('', $varname, $channel['managegid'], 'omcheckbox');
		showtitle('category_mapset');
		showsetting('category_channel_mapkey', 'mapinfo[key]', $channel['mapinfo']['key'], 'text');

		showtableheader();
		showtitle('setting_seo');
		showsetting('setting_seo_seotitle', 'seoinfo[seotitle]', $channel['seoinfo']['seotitle'], 'text');
		showsetting('setting_seo_seokeywords', 'seoinfo[seokeywords]', $channel['seoinfo']['seokeywords'], 'text');
		showsetting('setting_seo_seodescription', 'seoinfo[seodescription]', $channel['seoinfo']['seodescription'], 'text');
		showsetting('setting_seo_seohead', 'seoinfo[seohead]', $channel['seoinfo']['seohead'], 'textarea');

		showsubmit('channelsubmit');
		showtablefooter();
		showformfooter();
		updateinformation($cid, $do);

	} else {

		if($_FILES['logonew']) {
			$data = array('extid' => 'channel_'.$cid);
			$_G['gp_logonew'] = upload_icon_banner($data, $_FILES['logonew'], '');
		}

		$_G['gp_mapinfo']['key'] = dhtmlspecialchars(trim($_G['gp_mapinfo']['key']));
		$mapinfo = serialize($_G['gp_mapinfo']);

		$_G['gp_seoinfo']['seotitle'] = !empty($_G['gp_seoinfo']['seotitle']) ? dhtmlspecialchars(trim($_G['gp_seoinfo']['seotitle'])) : '';
		$_G['gp_seoinfo']['seokeywords'] = !empty($_G['gp_seoinfo']['seokeywords']) ? dhtmlspecialchars(trim($_G['gp_seoinfo']['seokeywords'])) : '';
		$_G['gp_seoinfo']['seodescription'] = !empty($_G['gp_seoinfo']['seodescription']) ? dhtmlspecialchars(trim($_G['gp_seoinfo']['seodescription'])) : '';
		$_G['gp_seoinfo']['seohead'] = !empty($_G['gp_seoinfo']['seohead']) ? dhtmlspecialchars(trim($_G['gp_seoinfo']['seohead'])) : '';
		$seoinfo = serialize($_G['gp_seoinfo']);

		DB::update('category_channel', array(
			'title' => dhtmlspecialchars(trim($_G['gp_titlenew'])),
			'status' => intval($_G['gp_statusnew']),
			'logo' => $_G['gp_logonew'],
			'mapinfo' => $mapinfo,
			'listmode' => $_G['gp_listmodenew'],
			'managegid' => serialize($_G['gp_newmanagegid']),
			'seoinfo' => $seoinfo
		), "cid='$cid'");

		categorycache('channellist');
		cpmsg('threadtype_infotypes_option_succeed', 'action=category&operation=channel&do='.$do, 'succeed');

	}

} elseif($operation == 'area') {

	if(!submitcheck('editsubmit')) {
?>
<script type="text/JavaScript">
var rowtypedata = [
	[[1,'',''], [1,'<input type="text" class="txt" name="newcityorder[]" value="0" />', 'td25'], [3, "<input name=newcity[] value='<?=cplang('city_name')?>' size='20' type='text' class='txt' />"]],
	[[1,'',''], [1,'<input type="text" class="txt" name="newdistrictorder[{1}][]" value="0" />', 'td25'], [3, "<div class='board'><input name='newdistrict[{1}][]' value='<?=cplang('province_name')?>' size='20' type='text' class='txt' /></div>"]],
	[[1,'',''], [1,'<input type="text" class="txt" name="newstreetorder[{1}][]" value="0" />', 'td25'], [3, "<div class='childboard'><input name='newstreet[{1}][]' value='<?=cplang('street_name')?>' size='20' type='text' class='txt' /></div>"]],
];
</script>
<?
		shownav('house', 'category_area');
		showsubmenu('category_area');
		showformheader('category&operation=area&do='.$do);
		showtableheader('');
		showsubtitle(array('del', 'display_order', cplang('class_name')));

		$citylist = $districtlist = $streetlist = array();
		$addcid = $_G['gp_cid'] ? "WHERE cid='$cid'" : '';
		$query = DB::query("SELECT aid, aup, cid, type, title, displayorder FROM ".DB::table('category_area')." $addcid ORDER BY displayorder");
		while($area = DB::fetch($query)) {
			if($area['type'] == 'city') {
				$citylist[$area['aid']] = $area;
			} elseif($area['type'] == 'district') {
				$districtlist[$area['aup']][] = $area;
			} elseif($area['type'] == 'street') {
				$streetlist[$area['aup']][] = $area;
			}
		}

		foreach($citylist as $aid => $city) {
			showcategory($city, 'city');
			if(!empty($districtlist[$aid])) {
				foreach ($districtlist[$aid] as $district) {
					showcategory($district);
					$lastaid = 0;
					if(!empty($streetlist[$district['aid']])) {
						foreach ($streetlist[$district['aid']] as $street) {
							showcategory($street, 'street');
							$lastaid = $street['aid'];
						}
					}
					showcategory($district, $lastaid, 'lastchildboard');
				}
			}
			showcategory($city, '', 'lastboard');
		}

		showcategory($city, '', 'last');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_delete']) {
			foreach($_G['gp_delete'] as $aid) {
				$subaid = DB::result_first("SELECT aid FROM ".DB::table('category_area')." WHERE aup='$aid'");
				if($subaid) {
					cpmsg(cplang('delete_tips'), '', 'error');
				} else {
					DB::query("DELETE FROM ".DB::table('category_area')." WHERE aid='$aid'");
				}
			}
		}

		if($_G['gp_name']) {
			foreach($_G['gp_name'] as $aid => $name) {
				DB::update('category_area', array(
					'title' => dhtmlspecialchars(trim($name)),
					'displayorder' => intval($_G['gp_order'][$aid])
				), "aid='$aid'");
			}
		}

		if($_G['gp_newcity']) {
			foreach($_G['gp_newcity'] as $aid => $city) {
				DB::insert('category_area', array('type' => 'city', 'aup' => '0', 'title' => dhtmlspecialchars(trim($city)), 'cid' => $cid, 'displayorder' => intval($_G['gp_newcityorder'][$aid])));
			}
		}

		if($_G['gp_newdistrict']) {
			foreach($_G['gp_newdistrict'] as $aup => $districts) {
				foreach($districts as $aid => $district) {
					DB::insert('category_area', array('type' => 'district', 'aup' => $aup, 'title' => dhtmlspecialchars(trim($district)), 'cid' => $cid, 'displayorder' => intval($_G['gp_newdistrictorder'][$aid])));
				}
			}
		}

		if($_G['gp_newstreet']) {
			foreach($_G['gp_newstreet'] as $aup => $streets) {
				foreach($streets as $aid => $street) {
					DB::insert('category_area', array('type' => 'street', 'aup' => $aup, 'title' => dhtmlspecialchars(trim($street)), 'cid' => $cid, 'displayorder' => intval($_G['gp_newstreetorder'][$aid])));
				}
			}
		}

		categorycache('arealist');
		cpmsg(cplang('region_update_success'), 'action=category&operation=area&do='.$do, 'succeed');
	}
} elseif($operation == 'sort') {

	if(!submitcheck('sortsubmit')) {

		$sorts = '';
		$query = DB::query("SELECT * FROM ".DB::table('category_sort')." WHERE cid='$cid' ORDER BY displayorder");
		while($sort = DB::fetch($query)) {
			$sorts .= showtablerow('', array('class="td25"', 'class="td28"', '', 'class="td29"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$sort[sortid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$sort[sortid]]\" value=\"$sort[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$sort[sortid]]\" value=\"".dhtmlspecialchars($sort['name'])."\">",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"descriptionnew[$sort[sortid]]\" value=\"$sort[description]\">",
				"<a href=\"".ADMINSCRIPT."?action=category&operation=sortdetail&do=$do&sortid=$sort[sortid]\" class=\"act nowrap\">$lang[detail]</a>"
			), TRUE);
		}

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1, '', 'td25'],
		[1, '<input type="text" class="txt" name="newdisplayorder[]" size="2" value="">', 'td28'],
		[1, '<input type="text" class="txt" name="newname[]" size="15">'],
		[1, '<input type="text" class="txt" name="newdescription[]" size="30" value="">', 'td29'],
		[2, '']
	],
];
</script>
<?
		shownav('house', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes');

		showformheader("category&operation=sort&do=$do");
		showtableheader('');
		showsubtitle(array('', 'display_order', 'name', 'description', ''));
		echo $sorts;
		echo '<tr><td class="td25"></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add'].'</a></div></td>';

		showsubmit('sortsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		$updatefids = $modifiedtypes = array();

		if(is_array($_G['gp_delete'])) {

			if($deleteids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('category_sortoptionvar')." WHERE sortid IN ($deleteids)");
				DB::query("DELETE FROM ".DB::table('category_sortvar')." WHERE sortid IN ($deleteids)");
				DB::query("DELETE FROM ".DB::table('category_sort')." WHERE sortid IN ($deleteids)");
			}

			foreach($_G['gp_delete'] as $sortid) {
				DB::query("DROP TABLE IF EXISTS ".DB::table('category_sortvalue')."{$sortid}");
			}

			/*
			if($deleteids && DB::affected_rows()) {
				//debug 下面这句 SQL 有待优化 可考虑删除之
				DB::query("UPDATE ".DB::table('forum_thread')." SET typeid='0' WHERE typeid IN ($deleteids)");
				foreach($_G['gp_delete'] as $id) {
					if(is_array($_G['gp_namenew']) && isset($_G['gp_namenew'][$id])) {
						unset($_G['gp_namenew'][$id]);
					}
					if(!empty($_G['gp_fids'][$id])) {
						foreach(explode(',', $_G['gp_fids'][$id]) as $fid) {
							if($fid = intval($fid)) {
								$updatefids[$fid]['deletedids'][] = intval($id);
							}
						}
					}
				}
			}
			*/
		}

		if(is_array($_G['gp_namenew']) && $_G['gp_namenew']) {
			foreach($_G['gp_namenew'] as $sortid => $val) {
				DB::update('category_sort', array(
					'name' => trim($_G['gp_namenew'][$sortid]),
					'description' => dhtmlspecialchars(trim($_G['gp_descriptionnew'][$sortid])),
					'displayorder' => $_G['gp_displayordernew'][$sortid],
					'cid' => $cid,
				), "sortid='$sortid'");
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($newname1 = trim($value)) {
					$query = DB::query("SELECT sortid FROM ".DB::table('category_sort')." WHERE name='$newname1'");
					if(DB::num_rows($query)) {
						cpmsg('forums_threadtypes_duplicate', '', 'error');
					}
					$data = array(
						'name' => $newname1,
						'description' => dhtmlspecialchars(trim($_G['gp_newdescription'][$key])),
						'displayorder' => $_G['gp_newdisplayorder'][$key],
						'cid' => $cid,
					);
					DB::insert('category_sort', $data);
				}
			}
		}

		categorycache('sortlist');
		cpmsg('forums_threadtypes_succeed', 'action=category&operation=sort&do='.$do, 'succeed');

	}

} elseif($operation == 'option') {//note 分类信息选项管理

	loadcache('category_channellist');
	$cidentifier = $do;
	if(!submitcheck('optionsubmit')) {
		if($classid) {
			if(!$typetitle = DB::result_first("SELECT title FROM ".DB::table('category_sortoption')." WHERE optionid IN ('$classid', 1)")) {
				cpmsg('threadtype_infotypes_noexist', 'action=threadtypes', 'error');
			}

			$sortoptions = '';
			$query = DB::query("SELECT * FROM ".DB::table('category_sortoption')." WHERE classid IN ('$classid', 1) ORDER BY displayorder");
			while($option = DB::fetch($query)) {
				$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$sortoptions .= showtablerow('', array('class="td25"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"title[$option[optionid]]\" value=\"".dhtmlspecialchars($option['title'])."\">",
					"$option[identifier]<input type=\"hidden\" name=\"identifier[$option[optionid]]\" value=\"$option[identifier]\">",
					$option['type'],
					"<a href=\"".ADMINSCRIPT."?action=category&operation=optiondetail&optionid=$option[optionid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
			}
		}

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1, '', 'td25'],
			[1, '<input type="text" class="txt" size="2" name="newdisplayorder[]" value="0">', 'td28'],
			[1, '<input type="text" class="txt" size="15" name="newtitle[]">'],
			[1, '{$cidentifier}_<input type="text" class="txt" size="15" name="newidentifier[]">'],
			[1, '<select name="newtype[]"><option value="number">$lang[threadtype_edit_vars_type_number]</option><option value="text" selected>$lang[threadtype_edit_vars_type_text]</option><option value="textarea">$lang[threadtype_edit_vars_type_textarea]</option><option value="radio">$lang[threadtype_edit_vars_type_radio]</option><option value="checkbox">$lang[threadtype_edit_vars_type_checkbox]</option><option value="select">$lang[threadtype_edit_vars_type_select]</option><option value="calendar">$lang[threadtype_edit_vars_type_calendar]</option><option value="email">$lang[threadtype_edit_vars_type_email]</option><option value="image">$lang[threadtype_edit_vars_type_image]</option><option value="url">$lang[threadtype_edit_vars_type_url]</option><option value="info">$lang[threadtype_edit_vars_type_info]</option></select>'],
			[1, '']
		],
	];
</script>
EOT;

		shownav('house', 'category_option');
		showsubmenu('category_option');
		showformheader("category&operation=option&typeid={$_G['gp_typeid']}&do=$do");
		showhiddenfields(array('classid' => $_G['gp_classid']));
		showtableheader();

		showsubtitle(array('', 'display_order', 'name', 'threadtype_variable', 'threadtype_type', ''));
		echo $sortoptions;
		echo '<tr><td></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add_option'].'</a></div></td></tr>';
		showsubmit('optionsubmit', 'submit', 'del');

		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('category_sortoption')." WHERE optionid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('category_sortvar')." WHERE optionid IN ($ids)");
		}

		if(is_array($_G['gp_title'])) {
			foreach($_G['gp_title'] as $id => $val) {
				DB::update('category_sortoption', array(
					'displayorder' => $_G['gp_displayorder'][$id],
					'title' => $_G['gp_title'][$id],
					'identifier' => $_G['gp_identifier'][$id],
				), "optionid='$id'");
			}
		}

		if(is_array($_G['gp_newtitle'])) {
			foreach($_G['gp_newtitle'] as $key => $value) {
				$newtitle1 = dhtmlspecialchars(trim($value));
				$newidentifier1 = trim($_G['gp_newidentifier'][$key]);
				if($newtitle1 && $newidentifier1) {
					$newidentifier1 = $cidentifier.'_'.$newidentifier1;
					$query = DB::query("SELECT optionid FROM ".DB::table('category_sortoption')." WHERE identifier='$newidentifier1' LIMIT 1");
					if(DB::num_rows($query) || strlen($newidentifier1) > 40  || !ispluginkey($newidentifier1)) {
						cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
					}
					$data = array(
						'classid' => $classid,
						'displayorder' => $_G['gp_newdisplayorder'][$key],
						'title' => $newtitle1,
						'identifier' => $newidentifier1,
						'type' => $_G['gp_newtype'][$key],
					);
					DB::insert('category_sortoption', $data);
				} elseif($newtitle1 && !$newidentifier1) {
					cpmsg('threadtype_infotypes_option_invalid', 'action=category&operation=option&classid='.$_G['gp_classid'], 'error');
				}
			}
		}
		categorycache('categorysort');
		cpmsg('threadtype_infotypes_succeed', 'action=category&operation=option&classid='.$_G['gp_classid'], 'succeed');

	}

} elseif($operation == 'optiondetail') {//note 分类信息选项详情

	$option = DB::fetch_first("SELECT * FROM ".DB::table('category_sortoption')." WHERE optionid='{$_G['gp_optionid']}'");
	if(!$option) {
		cpmsg('undefined_action', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		shownav('house', 'category_option');
		showsubmenu('category_option');

		$typeselect = '<select name="typenew" onchange="var styles, key;styles=new Array(\'number\',\'text\',\'radio\', \'checkbox\', \'textarea\', \'select\', \'image\', \'calendar\', \'range\', \'phone\', \'intermediary\'); for(key in styles) {var obj=$(\'style_\'+styles[key]); obj.style.display = styles[key] == this.options[this.selectedIndex].value ? \'\' : \'none\';}">';
		foreach(array('number', 'text', 'radio', 'checkbox', 'textarea', 'select', 'calendar', 'email', 'url', 'image', 'range', 'phone', 'intermediary') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($option['type'] == $type ? 'selected' : '').'>'.$lang['threadtype_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		$option['rules'] = unserialize($option['rules']);
		$option['protect'] = unserialize($option['protect']);

		$groups = array(array(0, cplang('no_limit')));
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')."");
		while($group = DB::fetch($query)) {
			$groups[] = array($group['groupid'], $group['grouptitle']);
		}

		$extcreditarray = array(array(0, cplang('select')));
		foreach($_G['setting']['extcredits'] as $creditid => $extcredit) {
			$extcreditarray[] = array($creditid, $extcredit['title']);
		}

		showformheader("category&operation=optiondetail&optionid=$_G[gp_optionid]&do=$do");
		showtableheader();
		showtitle('threadtype_infotypes_option_config');
		showsetting('name', 'titlenew', $option['title'], 'text');
		showsetting('threadtype_variable', 'identifiernew', $option['identifier'], 'text');
		showsetting('type', '', '', $typeselect);
		showsetting('threadtype_edit_desc', 'descriptionnew', $option['description'], 'textarea');
		showsetting('threadtype_unit', 'unitnew', $option['unit'], 'text');
		showsetting('threadtype_expiration', 'expirationnew', $option['expiration'], 'radio');
		if(in_array($option['type'], array('calendar', 'number', 'text', 'phone'))) {
			showsetting('threadtype_protect', 'protectnew[status]', $option['protect']['status'], 'radio', 0, 1);
			showsetting('threadtype_protect_mode', array('protectnew[mode]', array(
				array(1, $lang['threadtype_protect_mode_pic']),
				array(2, $lang['threadtype_protect_mode_html']),
				array(3, $lang['threadtype_protect_mode_usergroup']),
				array(4, $lang['threadtype_protect_mode_credits'])
			)), $option['protect']['mode'], 'mradio');
			showsetting('threadtype_add_extcredit', array('protectnew[credits][title]', $extcreditarray), $option['protect']['credits']['title'], 'select');
			showsetting('threadtype_price_extcredit', 'protectnew[credits][price]', $option['protect']['credits']['price'], 'text');
			showsetting('threadtype_protect_usergroup', array('protectnew[usergroup][]', $groups), explode("\t", $option['protect']['usergroup']), 'mselect');
		}

		showtagheader('tbody', "style_calendar", $option['type'] == 'calendar');
		showtitle('threadtype_edit_vars_type_calendar');
		showsetting('threadtype_edit_inputsize', 'rules[calendar][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_number", $option['type'] == 'number');
		showtitle('threadtype_edit_vars_type_number');
		showsetting('threadtype_edit_maxnum', 'rules[number][maxnum]', $option['rules']['maxnum'], 'text');
		showsetting('threadtype_edit_minnum', 'rules[number][minnum]', $option['rules']['minnum'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[number][inputsize]', $option['rules']['inputsize'], 'text');
		showsetting('threadtype_defaultvalue', 'rules[number][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_text", $option['type'] == 'text');
		showtitle('threadtype_edit_vars_type_text');
		showsetting('threadtype_edit_textmax', 'rules[text][maxlength]', $option['rules']['maxlength'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[text][inputsize]', $option['rules']['inputsize'], 'text');
		showsetting('threadtype_defaultvalue', 'rules[text][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_textarea", $option['type'] == 'textarea');
		showtitle('threadtype_edit_vars_type_textarea');
		showsetting('threadtype_edit_textmax', 'rules[textarea][maxlength]', $option['rules']['maxlength'], 'text');
		showsetting('threadtype_edit_colsize', 'rules[textarea][colsize]', $option['rules']['colsize'], 'text');
		showsetting('threadtype_edit_rowsize', 'rules[textarea][rowsize]', $option['rules']['rowsize'], 'text');
		showsetting('threadtype_defaultvalue', 'rules[textarea][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_select", $option['type'] == 'select');
		showtitle('threadtype_edit_vars_type_select');
		showsetting('threadtype_edit_choices', 'rules[select][choices]', $option['rules']['choices'], 'textarea');
		showsetting('threadtype_edit_inputsize', 'rules[select][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_radio", $option['type'] == 'radio');
		showtitle('threadtype_edit_vars_type_radio');
		showsetting('threadtype_edit_choices', 'rules[radio][choices]', $option['rules']['choices'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', "style_checkbox", $option['type'] == 'checkbox');
		showtitle('threadtype_edit_vars_type_checkbox');
		showsetting('threadtype_edit_choices', 'rules[checkbox][choices]', $option['rules']['choices'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', "style_image", $option['type'] == 'image');
		showtitle('threadtype_edit_vars_type_image');
		showsetting('threadtype_edit_images_weight', 'rules[image][maxwidth]', $option['rules']['maxwidth'], 'text');
		showsetting('threadtype_edit_images_height', 'rules[image][maxheight]', $option['rules']['maxheight'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[image][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_range", $option['type'] == 'range');
		showtitle('threadtype_edit_vars_type_range');
		showsetting('threadtype_edit_maxnum', 'rules[range][maxnum]', $option['rules']['maxnum'], 'text');
		showsetting('threadtype_edit_minnum', 'rules[range][minnum]', $option['rules']['minnum'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[range][inputsize]', $option['rules']['inputsize'], 'text');
		showsetting('threadtype_edit_searchtxt', 'rules[range][searchtxt]', $option['rules']['searchtxt'], 'text');
		showsetting('threadtype_defaultvalue', 'rules[range][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_phone", $option['type'] == 'phone');
		showtitle('threadtype_edit_vars_type_phone');
		showsetting('threadtype_edit_numbercheck', 'rules[phone][numbercheck]', $option['rules']['numbercheck'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', "style_intermediary", $option['type'] == 'intermediary');
		showtitle('threadtype_edit_vars_type_intermediary');
		showsetting('threadtype_edit_choices', 'rules[intermediary][choices]', $option['rules']['choices'], 'textarea');
		showsetting('threadtype_edit_inputsize', 'rules[intermediary][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$titlenew = trim($_G['gp_titlenew']);
		if(!$titlenew || !$_G['gp_identifiernew']) {
			cpmsg('threadtype_infotypes_option_invalid', '', 'error');
		}

		$query = DB::query("SELECT optionid FROM ".DB::table('category_sortoption')." WHERE identifier='{$_G['gp_identifiernew']}' AND optionid!='{$_G['gp_optionid']}' LIMIT 1");
		if(DB::num_rows($query) || strlen($_G['gp_identifiernew']) > 40  || !ispluginkey($_G['gp_identifiernew'])) {
			cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
		}

		$_G['gp_protectnew']['usergroup'] = $_G['gp_protectnew']['usergroup'] ? implode("\t", $_G['gp_protectnew']['usergroup']) : '';

		DB::update('category_sortoption', array(
			'title' => $titlenew,
			'description' => $_G['gp_descriptionnew'],
			'identifier' => $_G['gp_identifiernew'],
			'type' => $_G['gp_typenew'],
			'unit' => $_G['gp_unitnew'],
			'expiration' => $_G['gp_expirationnew'],
			'protect' => addslashes(serialize($_G['gp_protectnew'])),
			'rules' => addslashes(serialize($_G['gp_rules'][$_G['gp_typenew']])),
		), "optionid='{$_G['gp_optionid']}'");

		categorycache('categorysort');
		cpmsg('threadtype_infotypes_option_succeed', 'action=category&operation=option&classid='.$option['classid'], 'succeed');
	}

} elseif($operation == 'sortdetail') {//note 分类信息类别详情

	$_G['gp_template'] = $_G['gp_template'] ? $_G['gp_template'] : 'basic';
	$templateblock[$_G['gp_template']] = $_G['gp_template'] ? 1 : 0;

	if(!submitcheck('sortdetailsubmit') && !submitcheck('sortpreviewsubmit')) {
		$threadtype = DB::fetch_first("SELECT name, template, stemplate, sttemplate, ptemplate, btemplate, ntemplate, vtemplate, rtemplate, modelid, expiration, imgnum, perpage FROM ".DB::table('category_sort')." WHERE sortid='{$_G['gp_sortid']}'");
		$threadtype['btemplate'] = unserialize($threadtype['btemplate']);

		$sortoptions = $jsoptionids = '';
		$showoption = array();
		$query = DB::query("SELECT t.optionid, t.displayorder, t.available, t.required, t.unchangeable, t.search, t.subjectshow, t.visitedshow, t.orderbyshow, tt.title, tt.type, tt.identifier
			FROM ".DB::table('category_sortvar')." t, ".DB::table('category_sortoption')." tt
			WHERE t.sortid='{$_G['gp_sortid']}' AND t.optionid=tt.optionid ORDER BY t.displayorder");
		while($option = DB::fetch($query)) {
			$jsoptionids .= "optionids.push($option[optionid]);\r\n";
			$optiontitle[$option['identifier']] = $option['title'];
			$showoption[$option['optionid']]['optionid'] = $option['optionid'];
			$showoption[$option['optionid']]['title'] = $option['title'];
			$showoption[$option['optionid']]['type'] = $option['type'];
			$showoption[$option['optionid']]['identifier'] = $option['identifier'];
			$showoption[$option['optionid']]['displayorder'] = $option['displayorder'];
			$showoption[$option['optionid']]['available'] = $option['available'];
			$showoption[$option['optionid']]['required'] = $option['required'];
			$showoption[$option['optionid']]['unchangeable'] = $option['unchangeable'];
			$showoption[$option['optionid']]['search'] = $option['search'];
			$showoption[$option['optionid']]['subjectshow'] = $option['subjectshow'];
			$showoption[$option['optionid']]['visitedshow'] = $option['visitedshow'];
			$showoption[$option['optionid']]['orderbyshow'] = $option['orderbyshow'];
		}

		if($existoption && is_array($existoption)) {
			$optionids = $comma = '';
			foreach($existoption as $optionid => $val) {
				$optionids .= $comma.$optionid;
				$comma = '\',\'';
			}
			$query = DB::query("SELECT * FROM ".DB::table('category_sortoption')." WHERE optionid IN ('$optionids')");
			while($option = DB::fetch($query)) {
				$showoption[$option['optionid']]['optionid'] = $option['optionid'];
				$showoption[$option['optionid']]['title'] = $option['title'];
				$showoption[$option['optionid']]['type'] = $option['type'];
				$showoption[$option['optionid']]['identifier'] = $option['identifier'];
				$showoption[$option['optionid']]['required'] = $existoption[$option['optionid']];
				$showoption[$option['optionid']]['available'] = 1;
				$showoption[$option['optionid']]['unchangeable'] = 0;
				$showoption[$option['optionid']]['model'] = 1;
			}
		}

		$searchtitle = $searchvalue = $searchunit = array();
		foreach($showoption as $optionid => $option) {
			$sortoptions .= showtablerow('id="optionid'.$optionid.'"', array('class="td25"', 'class="td28 td23"', '', 'title="'.$lang['threadtype_edit_vars_type_'. $option['type']].'"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" id=\"available_$option[identifier]\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
				dhtmlspecialchars($option['title']),
				"<input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]]\" value=\"1\" ".($option['search'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" id=\"subject_$option[identifier]\" name=\"subjectshow[$option[optionid]]\" value=\"1\" ".($option['subjectshow'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"visitedshow[$option[optionid]]\" value=\"1\" ".($option['visitedshow'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"orderbyshow[$option[optionid]]\" value=\"1\" ".($option['orderbyshow'] ? 'checked' : '')." ".(!in_array($option['type'], array('number', 'range')) ? 'disabled' : '').">",
				($_G['gp_template'] == 'basic' ? "<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'typetemplate', 'message');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_template']."</a><a href=\"###\" onclick=\"insertvar('$option[identifier]', 'stypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_stemplate']."</a><a href=\"###\" onclick=\"insertvar('$option[identifier]', 'ptypetemplate', 'post');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_ptemplate']."</a><input type=\"\" value=\"[$option[identifier]title][$option[identifier]value][$option[identifier]unit]\" size=\"10\">" : ''),
				($_G['gp_template'] == 'block' ? "<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'btypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_btemplate']."</a><input type=\"\" value=\"[$option[identifier]title][$option[identifier]value][$option[identifier]unit]\" size=\"10\">" : ''),
				"<a href=\"".ADMINSCRIPT."?action=category&operation=optiondetail&optionid=$option[optionid]\" class=\"act\" target=\"_blank\">".$lang['edit']."</a>"
			), TRUE);
		}

		shownav('house', 'category_sort');
		showsubmenu('category_sort', array(
			array(cplang('base_tpl'), 'category&operation=sortdetail&sortid='.$_G['gp_sortid'], $templateblock['basic']),
			array(cplang('call_tpl'), 'category&operation=sortdetail&sortid='.$_G['gp_sortid'].'&template=block', $templateblock['block']),
			));
		showtips('forums_edit_threadsorts_tips');

		showformheader("category&operation=sortdetail&sortid={$_G['gp_sortid']}&template={$_G['gp_template']}");
		showtableheader('threadtype_infotypes_validity', 'nobottom');
		showsetting('threadtype_infotypes_validity', 'expiration', $threadtype['expiration'], 'radio');
		showsetting('threadtype_infotypes_imgnum', 'imgnum', $threadtype['imgnum'], 'text');
		showsetting('category_channel_perpage', 'perpage', $threadtype['perpage'], 'text');
		showtablefooter();

		showtableheader("$threadtype[name] - $lang[threadtype_infotypes_add_option]", 'noborder fixpadding');
		showtablerow('', 'id="classlist"', '');
		showtablerow('', 'id="optionlist"', '');
		showtablefooter();

		showtableheader("$threadtype[name] - $lang[threadtype_infotypes_exist_option]", 'noborder fixpadding', 'id="sortlist"');
		showsubtitle(array('<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form,\'delete\')" /><label for="chkall">'.cplang('del').'</label>', 'display_order', 'available', 'name', 'required', 'unchangeable', 'threadtype_infotypes_search', 'threadtype_infotypes_show', 'category_infotypes_visitshow', 'category_infotypes_orderbyshow',  'threadtype_infotypes_insert_template', '', ''));
		echo $sortoptions;
		showtablefooter();

?>

<a name="template"></a>
<div class="colorbox">
<?
	if($_G['gp_template'] == 'basic') {
?>
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_template']?></h4>
<textarea cols="100" rows="15" id="typetemplate" name="typetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['template']?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_ptemplate']?></h4>
<textarea cols="100" rows="15" id="ptypetemplate" name="ptypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['ptemplate']?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_stemplate']?>(<? echo cplang('img_version') ?>)</h4>
<textarea cols="100" rows="8" id="stypetemplate" name="stypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['stemplate']?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_stemplate']?>(<? echo cplang('char_version') ?>)</h4>
<textarea cols="100" rows="8" id="sttypetemplate" name="sttypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['sttemplate']?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo  cplang('recent_view_tpl') ?></h4>
<textarea cols="100" rows="8" id="vtypetemplate" name="vtypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['vtemplate']?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo  cplang('nearby_house_tpl') ?></h4>
<textarea cols="100" rows="8" id="ntypetemplate" name="ntypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['ntemplate']?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo  cplang('stick_tpl') ?></h4>
<textarea cols="100" rows="8" id="rtypetemplate" name="rtypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?=$threadtype['rtemplate']?></textarea>
<br /><br />
<?
	} elseif($_G['gp_template'] == 'block') {

?>
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo cplang('tpl_style1') ?></h4>
<textarea cols="100" rows="8" id="btypetemplate" name="btypetemplate[style1]" style="width: 95%;" onkeyup="textareasize(this)"><?=stripslashes($threadtype['btemplate']['style1'])?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo cplang('tpl_style2') ?></h4>
<textarea cols="100" rows="8" id="btypetemplate" name="btypetemplate[style2]" style="width: 95%;" onkeyup="textareasize(this)"><?=stripslashes($threadtype['btemplate']['style2'])?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo cplang('tpl_style3') ?></h4>
<textarea cols="100" rows="8" id="btypetemplate" name="btypetemplate[style3]" style="width: 95%;" onkeyup="textareasize(this)"><?=stripslashes($threadtype['btemplate']['style3'])?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo cplang('tpl_style4') ?></h4>
<textarea cols="100" rows="8" id="btypetemplate" name="btypetemplate[style4]" style="width: 95%;" onkeyup="textareasize(this)"><?=stripslashes($threadtype['btemplate']['style4'])?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?=$threadtype['name']?> - <? echo cplang('tpl_style5') ?></h4>
<textarea cols="100" rows="8" id="btypetemplate" name="btypetemplate[style5]" style="width: 95%;" onkeyup="textareasize(this)"><?=stripslashes($threadtype['btemplate']['style5'])?></textarea>
<br /><br />
<?
	}
?>
<b><?=$lang['threadtype_infotypes_template']?>:</b>
<ul class="tpllist"><?=$lang['threadtype_infotypes_template_tips']?></ul>
<input type="submit" class="btn" name="sortdetailsubmit" value="<?=$lang['submit']?>">
</div>

</form>
<script type="text/JavaScript">
	//note 初始化已有的选项
	var optionids = new Array();
	<?=$jsoptionids?>
	function insertvar(text, focusarea, location) {
		$(focusarea).focus();
		selection = document.selection;
		var commonfield = '[' + text + 'value] [' + text + 'unit]';
		if(location == 'post' || location == 'message') {
			var checktext = 'available_' + text;
			$(checktext).checked = true;
		} else {
			var checktext = 'subject_' + text;
			$(checktext).checked = true;
		}
		if(selection && selection.createRange) {
			var sel = selection.createRange();
			if(location == 'post') {
				sel.text = '<dt><strong class="rq">[' + text + 'required]</strong>{' + text + '}</dt><dd>' + commonfield + '[' + text + 'tips] [' + text + 'description]</dd>\r\n';
			} else {
				sel.text = location == 'message' ? '<dt>{' + text + '}:</dt><dd>' + commonfield + ' </dd>\r\n' : '<p><em>{' + text + '}:</em>' + commonfield + '</p>';
			}
			sel.moveStart('character', -strlen(text));
		} else {
			if(location == 'post') {
				$(focusarea).value += '<dt><strong class="rq">[' + text + 'required]</strong>{' + text + '}</dt><dd>' + commonfield + ' [' + text + 'tips] [' + text + 'description]</dd>\r\n';
			} else {
				$(focusarea).value += location == 'message' ? '<dt>{' + text + '}:</dt><dd>' + commonfield + '</dd>\r\n' : '<p><em>{' + text + '}:</em>' + commonfield + '</p>';
			}
		}
	}

	function checkedbox() {
		var tags = $('optionlist').getElementsByTagName('input');
		for(var i=0; i<tags.length; i++) {
			if(in_array(tags[i].value, optionids)) {
				tags[i].checked = true;
			}
		}
	}
	function insertoption(optionid) {
		var x = new Ajax();
		x.optionid = optionid;
		x.get('<?=ADMINSCRIPT?>?action=category&operation=sortlist&do=$do&inajax=1&optionid=' + optionid, function(s, x) {
			if(!in_array(x.optionid, optionids)) {
				var div = document.createElement('div');
				div.style.display = 'none';
				$('append_parent').appendChild(div);
				div.innerHTML = '<table>' + s + '</table>';
				var tr = div.getElementsByTagName('tr');
				var trs = $('sortlist').getElementsByTagName('tr');
				tr[0].id = 'optionid' + optionid;
				trs[trs.length - 1].parentNode.appendChild(tr[0]);
				$('append_parent').removeChild(div);
				optionids.push(x.optionid);
			} else {
				$('optionid' + x.optionid).parentNode.removeChild($('optionid' + x.optionid));
				for(var i=0; i<optionids.length; i++) {
					if(optionids[i] == x.optionid) {
						optionids[i] = 0;
					}
				}
			}
		});
	}

	function setCopy(text, msg){
		if(BROWSER.ie) {
			clipboardData.setData('Text', text);
			alert(msg);
		} else {
			var msg = '<div class="c"><div style="width: 200px; text-align: center; text-decoration:underline;">' + <?=cplang('category_click_copy')?> + '</div>' +
			AC_FL_RunContent('id', 'clipboardswf', 'name', 'clipboardswf', 'devicefont', 'false', 'width', '200', 'height', '40', 'src', STATICURL + 'image/common/clipboard.swf', 'menu', 'false',  'allowScriptAccess', 'sameDomain', 'swLiveConnect', 'true', 'wmode', 'transparent', 'style' , 'margin-top:-20px') + '</div>';
			showDialog(msg, 'info');
			text = text.replace(/[\xA0]/g, ' ');
			clipboardswfdata = text;
		}
	}
</script>
<script type="text/JavaScript">ajaxget('<?=ADMINSCRIPT?>?action=category&operation=classlist', 'classlist');</script>
<script type="text/JavaScript">ajaxget('<?=ADMINSCRIPT?>?action=category&operation=optionlist&sortid=<?=$_G['gp_sortid']?>', 'optionlist', '', '', '', checkedbox);</script>
<?

	} else {

		if($_G['gp_template'] == 'basic') {
			DB::update('category_sort', array(
				'template' => $_G['gp_typetemplate'],
				'stemplate' => $_G['gp_stypetemplate'],
				'sttemplate' => $_G['gp_sttypetemplate'],
				'ptemplate' => $_G['gp_ptypetemplate'],
				'vtemplate' => $_G['gp_vtypetemplate'],
				'ntemplate' => $_G['gp_ntypetemplate'],
				'rtemplate' => $_G['gp_rtypetemplate'],
				'expiration' => $_G['gp_expiration'],
				'imgnum' => intval($_G['gp_imgnum']),
				'perpage' => intval($_G['gp_perpage']),
			), "sortid='{$_G['gp_sortid']}'");
		} elseif($_G['gp_template'] == 'block') {
			DB::update('category_sort', array(
				'btemplate' => addslashes(serialize($_G['gp_btypetemplate'])),
				'expiration' => $_G['gp_expiration'],
				'imgnum' => intval($_G['gp_imgnum']),
				'perpage' => intval($_G['gp_perpage']),
			), "sortid='{$_G['gp_sortid']}'");
		}

		if(submitcheck('sortdetailsubmit')) {

			$orgoption = $orgoptions = $addoption = array();
			$query = DB::query("SELECT optionid FROM ".DB::table('category_sortvar')." WHERE sortid='{$_G['gp_sortid']}'");
			while($orgoption = DB::fetch($query)) {
				$orgoptions[] = $orgoption['optionid'];
			}

			$addoption = $addoption ? (array)$addoption + (array)$_G['gp_displayorder'] : (array)$_G['gp_displayorder'];

			@$newoptions = array_keys($addoption);

			if(empty($addoption)) {
				cpmsg('threadtype_infotypes_invalid', '', 'error');
			}

			@$delete = array_merge((array)$_G['gp_delete'], array_diff($orgoptions, $newoptions));

			if($delete) {
				if($ids = dimplode($delete)) {
					$deletefield = array();
					$query = DB::query("SELECT optionid, identifier FROM ".DB::table('category_sortoption')." WHERE optionid IN ($ids)");
					while($option = DB::fetch($query)) {
						$deletefield[$option['optionid']] = $option['identifier'];
					}

					foreach($deletefield as $identifier) {
						DB::query("ALTER TABLE ".DB::table('category_sortvalue')."{$_G['gp_sortid']} DROP $identifier");
					}

					DB::query("DELETE FROM ".DB::table('category_sortvar')." WHERE sortid='{$_G['gp_sortid']}' AND optionid IN ($ids)");
				}
				//note 删除
				foreach($delete as $id) {
					unset($addoption[$id]);
				}
			}

			$insertoptionid = $indexoption = array();
			$create_table_sql = $separator = $create_tableoption_sql = '';

			if(is_array($addoption) && !empty($addoption)) {
				$query = DB::query("SELECT optionid, type, identifier FROM ".DB::table('category_sortoption')." WHERE optionid IN (".dimplode(array_keys($addoption)).")");
				while($option = DB::fetch($query)) {
					$insertoptionid[$option['optionid']]['type'] = $option['type'];
					$insertoptionid[$option['optionid']]['identifier'] = $option['identifier'];
				}

				$query = DB::query("SHOW TABLES LIKE '".DB::table('category_sortvalue')."{$_G['gp_sortid']}'");
				if(DB::num_rows($query) != 1) {
					$create_table_sql = "CREATE TABLE ".DB::table('category_sortvalue')."{$_G['gp_sortid']} (";
					foreach($addoption as $optionid => $option) {
						$identifier = $insertoptionid[$optionid]['identifier'];
						if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select'))) {
							$create_tableoption_sql .= "$separator$identifier smallint(6) UNSIGNED NOT NULL DEFAULT '0'\r\n";
						} elseif(in_array($insertoptionid[$optionid]['type'], array('number', 'range'))) {
							$create_tableoption_sql .= "$separator$identifier int(10) UNSIGNED NOT NULL DEFAULT '0'\r\n";
						} else {
							$create_tableoption_sql .= "$separator$identifier mediumtext NOT NULL\r\n";
						}
						$separator = ' ,';
						if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select', 'number'))) {
							$indexoption[] = $identifier;
						}
					}
					$create_table_sql .= ($create_tableoption_sql ? $create_tableoption_sql.',' : '')."tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',attachid int(10) UNSIGNED NOT NULL DEFAULT '0',dateline int(10) UNSIGNED NOT NULL DEFAULT '0',expiration int(10) UNSIGNED NOT NULL DEFAULT '0',displayorder tinyint(3) NOT NULL DEFAULT '0',recommend tinyint(3) NOT NULL DEFAULT '0',attachnum tinyint(3) NOT NULL DEFAULT '0',highlight tinyint(3) NOT NULL DEFAULT '0',groupid smallint(6) UNSIGNED NOT NULL DEFAULT '0',city smallint(6) UNSIGNED NOT NULL DEFAULT '0',district smallint(6) UNSIGNED NOT NULL DEFAULT '0',street smallint(6) UNSIGNED NOT NULL DEFAULT '0', mapposition VARCHAR(50) NOT NULL DEFAULT '',";
					$create_table_sql .= "KEY (tid), KEY(groupid), KEY(dateline), KEY(city), KEY(district), KEY(street)";
					if($indexoption) {
						foreach($indexoption as $index) {
							$create_table_sql .= "$separator KEY $index ($index)\r\n";
							$separator = ' ,';
						}
					}
					$create_table_sql .= ") TYPE=MyISAM;";
					$dbcharset = empty($dbcharset) ? str_replace('-','',CHARSET) : $dbcharset;
					$db = DB::object();
					$create_table_sql = syntablestruct($create_table_sql, $db->version() > '4.1', $dbcharset);
					DB::query($create_table_sql);
				} else {
					$tables = array();
					$db = DB::object();
					if($db->version() > '4.1') {
						$query = DB::query("SHOW FULL COLUMNS FROM ".DB::table('category_sortvalue')."{$_G['gp_sortid']}", 'SILENT');
					} else {
						$query = DB::query("SHOW COLUMNS FROM ".DB::table('category_sortvalue')."{$_G['gp_sortid']}", 'SILENT');
					}
					while($field = @DB::fetch($query)) {
						$tables[$field['Field']] = 1;
					}

					foreach($addoption as $optionid => $option) {
						$identifier = $insertoptionid[$optionid]['identifier'];
						if(!$tables[$identifier]) {
							$fieldname = $identifier;
							if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select'))) {
								$fieldtype = 'smallint(6) UNSIGNED NOT NULL DEFAULT \'0\'';
							} elseif(in_array($insertoptionid[$optionid]['type'], array('number', 'range'))) {
								$fieldtype = 'int(10) UNSIGNED NOT NULL DEFAULT \'0\'';
							} else {
								$fieldtype = 'mediumtext NOT NULL';
							}
							DB::query("ALTER TABLE ".DB::table('category_sortvalue')."{$_G['gp_sortid']} ADD $fieldname $fieldtype");

							if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select', 'number'))) {
								DB::query("ALTER TABLE ".DB::table('category_sortvalue')."{$_G['gp_sortid']} ADD INDEX ($fieldname)");
							}
						}
					}
				}
				foreach($addoption as $id => $val) {
					$optionid = DB::fetch_first("SELECT optionid FROM ".DB::table('category_sortoption')." WHERE optionid='$id'");
					if($optionid) {
						$data = array(
							'sortid' => $_G['gp_sortid'],
							'optionid' => $id,
							'available' => 1,
							'required' => intval($val),
						);
						DB::insert('category_sortvar', $data, 0, 0, 1);
						DB::update('category_sortvar', array(
							'displayorder' => $_G['gp_displayorder'][$id],
							'available' => $_G['gp_available'][$id],
							'required' => $_G['gp_required'][$id],
							'unchangeable' => $_G['gp_unchangeable'][$id],
							'search' => $_G['gp_search'][$id],
							'subjectshow' => $_G['gp_subjectshow'][$id],
							'visitedshow' => $_G['gp_visitedshow'][$id],
							'orderbyshow' => $_G['gp_orderbyshow'][$id],
						), "sortid='{$_G['gp_sortid']}' AND optionid='$id'");
					} else {
						DB::query("DELETE FROM ".DB::table('category_sortvar')." WHERE sortid='{$_G['gp_sortid']}' AND optionid IN ($id)");
					}
				}
			}
			
			categorycache('categorysort');
			categorycache('sortlist');
			cpmsg('threadtype_infotypes_succeed', 'action=category&operation=sortdetail&sortid='.$_G['gp_sortid'].'&template='.$_G['gp_template'], 'succeed');

		}

	}

} elseif($operation == 'content') {

	loadcache(array('category_option_'.$_G['gp_sortid'], 'category_arealist_'.$do));
	$sortoptionarray = $_G['cache']['category_option_'.$_G['gp_sortid']];
	$sortarealist = $_G['cache']['category_arealist_'.$do];

	if(!submitcheck('searchsortsubmit', 1) && !submitcheck('delsortsubmit') && !submitcheck('sendpmsubmit')) {

		shownav('house', 'menu_category_content');

		$_G['gp_sortid'] = intval($_G['gp_sortid']);
		$threadtypes = '<select name="sortid" onchange="window.location.href = \'?action=category&operation=content&do='.$do.'&sortid=\'+ this.options[this.selectedIndex].value"><option value="0">'.cplang('none').'</option>';
		$query = DB::query("SELECT * FROM ".DB::table('category_sort')." WHERE cid='1' ORDER BY displayorder");
		while($type = DB::fetch($query)) {
			$threadtypes .= '<option value="'.$type['sortid'].'" '.($_G['gp_sortid'] == $type['sortid'] ? 'selected="selected"' : '').'>'.dhtmlspecialchars($type['name']).'</option>';
		}
		$threadtypes .= '</select>';

		showformheader('category&operation=content&sortid='.$_G['gp_sortid'].'&do='.$do);
		showtableheader(cplang('select_class'));
		showsetting(cplang('class_name'), '', '', $threadtypes);

		if($_G['gp_sortid']) {
			showtableheader(cplang('screening_conditions'));
			$arealist = '<select name="searchoption[0][value]"><option value="">'.cplang('all').'</option>';
			foreach($sortarealist['city'] as $cityid => $cityname) {
				$arealist .= "<option value=\"district|$cityid\">$cityname</option>";
				if(!empty($sortarealist['district'][$cityid])) {
					foreach($sortarealist['district'][$cityid] as $districtid => $districtname) {
						$arealist .= "<option value=\"district|$districtid\">&nbsp;&nbsp;$districtname</option>";
						if(!empty($sortarealist['street'][$districtid])) {
							foreach($sortarealist['street'][$districtid] as $streetid => $streetname) {
								$arealist .= "<option value=\"street|$streetid\">&nbsp;&nbsp;&nbsp;&nbsp;$streetname</option>";
							}
						}
					}
				}
				$arealist .= '</optgroup>';
			}
			$arealist .= '</select><input type="hidden" name="searchoption[0][type]" value="areaid">';

			showsetting(cplang('select_region'), '', '', $arealist);
			showsetting(cplang('post_user'), 'postusername', '', 'text');
			if(is_array($sortoptionarray)) foreach($sortoptionarray as $optionid => $option) {
				$optionshow = '';
				if($option['search']) {
					if(in_array($option['type'], array('radio', 'checkbox', 'select', 'intermediary'))){
						if($option['type'] == 'select' || $option['type'] == 'intermediary') {
							$optionshow .= '<select name="searchoption['.$optionid.'][value]"><option value="0">'.cplang('unlimited').'</option>';
							foreach($option['choices'] as $id => $value) {
								$optionshow .= '<option value="'.$id.'" '.($_G['gp_searchoption'][$optionid]['value'] == $id ? 'selected="selected"' : '').'>'.$value.'</option>';
							}
							$optionshow .= '</select><input type="hidden" name="searchoption['.$optionid.'][type]" value="select">';
						} elseif($option['type'] == 'radio') {
							$optionshow .= '<input type="radio" class="radio" name="searchoption['.$optionid.'][value]" value="0" checked="checked"]>'.cplang('unlimited').'&nbsp;';
							foreach($option['choices'] as $id => $value) {
								$optionshow .= '<input type="radio" class="radio" name="searchoption['.$optionid.'][value]" value="'.$id.'" '.($_G['gp_searchoption'][$optionid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.' &nbsp;';
							}
							$optionshow .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="radio">';
						} elseif($option['type'] == 'checkbox') {
							foreach($option['choices'] as $id => $value) {
								$optionshow .= '<input type="checkbox" class="checkbox" name="searchoption['.$optionid.'][value]['.$id.']" value="'.$id.'" '.($_G['gp_searchoption'][$optionid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.'';
							}
							$optionshow .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="checkbox">';
						}
					} elseif(in_array($option['type'], array('number', 'text', 'email', 'calendar', 'image', 'url', 'textarea', 'upload', 'range'))) {
						if ($option['type'] == 'calendar') {
							$optionshow .= '<script type="text/javascript" src="'.$_G['setting']['jspath'].'forum_calendar.js?'.VERHASH.'"></script><input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_G['gp_searchoption'][$optionid]['value'].'" onclick="showcalendar(event, this, false)" />';
						} elseif($option['type'] == 'number') {
							$optionshow .= '<select name="searchoption['.$optionid.'][condition]">
								<option value="0" '.($_G['gp_searchoption'][$optionid]['condition'] == 0 ? 'selected="selected"' : '').'>'.cplang('equal_to').'</option>
								<option value="1" '.($_G['gp_searchoption'][$optionid]['condition'] == 1 ? 'selected="selected"' : '').'>'.cplang('more_than').'</option>
								<option value="2" '.($_G['gp_searchoption'][$optionid]['condition'] == 2 ? 'selected="selected"' : '').'>'.cplang('lower_than').'</option>
							</select>&nbsp;&nbsp;
							<input type="text" class="txt" name="searchoption['.$optionid.'][value]" value="'.$_G['gp_searchoption'][$optionid]['value'].'" />
							<input type="hidden" name="searchoption['.$optionid.'][type]" value="number">';
						} elseif($option['type'] == 'range') {
							$optionshow .= '<input type="text" name="searchoption['.$optionid.'][value][min]" size="16" value="'.$_G['gp_searchoption'][$optionid]['value']['min'].'" /> -
							<input type="text" name="searchoption['.$optionid.'][value][max]" size="16" value="'.$_G['gp_searchoption'][$optionid]['value']['max'].'" />
							<input type="hidden" name="searchoption['.$optionid.'][type]" value="range">';
						} else {
							$optionshow .= '<input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_G['gp_searchoption'][$optionid]['value'].'" />';
						}
					}
					$optionshow .=  '&nbsp;'.$option['unit'];
					showsetting($option['title'], '', '', $optionshow);
				}
			}
		}

		showsubmit('searchsortsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		if(submitcheck('searchsortsubmit', 1)) {

			if(empty($_G['gp_searchoption']) && !$_G['gp_sortid']) {
				cpmsg(cplang('no_select_class'), 'action=category&operation=content&do='.$do, 'error');
			}
			$mpurl = 'admin.php?action=category&operation=content&do='.$do.'&sortid='.$_G['gp_sortid'].'&searchsortsubmit=true';

			if(!is_array($_G['gp_searchoption'])) {
				$mpurl .= '&searchoption='.$_G['gp_searchoption'];
				$_G['gp_searchoption'] = unserialize(base64_decode($_G['gp_searchoption']));
			} else {
				$mpurl .= '&searchoption='.base64_encode(serialize($_G['gp_searchoption']));
			}

			shownav('house', 'menu_category_content');
			$selectsql = $and = $sql = $multipage = '';
			foreach($_G['gp_searchoption'] as $optionid => $option) {
				$fieldname = $sortoptionarray[$optionid]['identifier'] ? $sortoptionarray[$optionid]['identifier'] : 1;
				if(!empty($option['value'])) {
					if(in_array($option['type'], array('number', 'radio', 'select'))) {
						$option['value'] = intval($option['value']);
						$exp = '=';
						if($option['condition']) {
							$exp = $option['condition'] == 1 ? '>' : '<';
						}
						$sql = "$fieldname$exp'$option[value]'";
					} elseif($option['type'] == 'checkbox') {
						$sql = "$fieldname LIKE '%".(implode("%", $option['value']))."%'";
					} elseif($option['type'] == 'range') {
						$sql = !empty($option['value']['min']) || !empty($option['value']['max']) ? "$fieldname BETWEEN ".intval($option['value']['min'])." AND ".intval($option['value']['max'])."" : '';
					} elseif($option['type'] == 'areaid') {
						$valuearray = explode('|', $option['value']);
						if(in_array($valuearray[0], array('city', 'district', 'street'))) {
							$sql = "$valuearray[0]='$valuearray[1]'";
						}
					} else {
						$sql = "$fieldname LIKE '%$option[value]%'";
					}

					if(!empty($sql)) {
						$selectsql .=  $and."$sql ";
						$and = 'AND ';
					}
				}
			}

			$selectsql = trim($selectsql);
			$searchtids = $searchthread = $datelinetids = array();
			$query = DB::query("SELECT tid, dateline FROM ".DB::table('category_sortvalue')."$_G[gp_sortid] ".($selectsql ? "WHERE $selectsql" : '')."");
			while($thread = DB::fetch($query)) {
				$searchtids[] = $thread['tid'];
				$datelinetids[$thread['tid']] = $thread['dateline'];
			}

			if($searchtids) {
				$authorsql = '';
				if($_G['gp_postusername']) {
					$manageuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_postusername]'");
					$authorsql = "AND authorid='$manageuid'";
					$mpurl .= '&authorid='.$manageuid;
				} elseif($_G['gp_authorid']) {
					$authorsql = "AND authorid='".intval($_G['gp_authorid'])."'";
					$mpurl .= '&authorid='.intval($_G['gp_authorid']);
				}

				$lpp = max(5, empty($_G['gp_lpp']) ? 50 : intval($_G['gp_lpp']));
				$start_limit = ($page - 1) * $lpp;

				$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table('category_'.$do.'_thread')." WHERE tid IN (".dimplode($searchtids).") $authorsql");
				$query = DB::query("SELECT tid, sortid, subject, authorid, author FROM ".DB::table('category_'.$do.'_thread')." WHERE tid IN (".dimplode($searchtids).") $authorsql LIMIT $start_limit, $lpp");
				while($thread = DB::fetch($query)) {
					$threads .= showtablerow('', array('class="td25"', '', '', 'class="td28"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"tidsarray[]\" value=\"$thread[tid]\"/>",
					"<a href=\"$modurl?mod=view&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
					"<a href=\"$modurl?mod=my&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
					dgmdate($datelinetids[$thread['tid']], 'd'),
					), TRUE);
				}

				$multipage = multi($threadcount, $lpp, $page, $mpurl, 0, 3);
			}

			showformheader('category&operation=content&sortid='.$_G['gp_sortid'].'&do='.$do);
			showtableheader('admin', 'fixpadding');
			showsubtitle(array('', 'subject', 'author', cplang('post_time')));
			echo $threads;
			echo $multipage;
			showsubmit('', '', '', '<input type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'tidsarray\')" name="chkall">'.$lang['select_all'].'&nbsp;&nbsp;&nbsp;<input type="submit" class="btn" name="delsortsubmit" value="'.cplang('delete_info').'"/>');
			showtablefooter();
			showformfooter();

		} elseif(submitcheck('delsortsubmit')) {
			$tidsadd = isset($_G['gp_tidsarray']) ? 'WHERE tid IN ('.dimplode($_G['gp_tidsarray']).')' : '';
			if($tidsadd) {
				DB::query("DELETE FROM ".DB::table('category_'.$do.'_thread')." $tidsadd");
				DB::query("DELETE FROM ".DB::table('category_sortoptionvar')." $tidsadd");
				DB::query("DELETE FROM ".DB::table('category_sortvalue'.$_G['gp_sortid'])." $tidsadd");
				$query = DB::query("SELECT * FROM ".DB::table('category_'.$do.'_pic')." $tidsadd");
				while($row = DB::fetch($query)) {
					@unlink($_G['setting']['attachdir'].'/category/'.$row['url']);
				}
				DB::query("DELETE FROM ".DB::table('category_'.$do.'_pic')." $tidsadd");
			}
			cpmsg(cplang('data_del_success'), 'action=category&operation=content&sortid='.$_G['gp_sortid'].'&do='.$do, 'succeed');

		}
	}

} elseif($operation == 'usergroup') {
	if(!submitcheck('groupsubmit')) {
		$query = DB::query("SELECT displayorder, gid, title, type, icon FROM ".DB::table('category_'.$do.'_usergroup')." ORDER BY displayorder");
	
		while($group = DB::fetch($query)) {
			$iconhtml = '';
			if($group['type'] == 'intermediary') {
				if($group['icon']) {
					$valueparse = parse_url($usergroup['icon']);
					if(isset($valueparse['host'])) {
						$groupicon = $group['icon'];
					} else {
						$groupicon = $_G['setting']['attachurl'].'common/'.$group['icon'].'?'.random(6);
					}
					$iconhtml = '<img src="'.$groupicon.'" />';
				}
				$intermediarygroup .= showtablerow('', array('', 'class="td28"', '', ''), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[gid]]\" value=\"$group[gid]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"group_displayorder[$group[gid]]\" value=\"$group[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[gid]]\" value=\"$group[title]\">",
					$iconhtml,
					"<a href=\"".ADMINSCRIPT."?action=category&operation=groupedit&groupid=$group[gid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
				
			}
		}

		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1, '<input type="text" class="txt" name="groupdisplayordernewadd[]" size="2" value="">', 'td28'],
		[1, '<input type="text" class="txt" size="12" name="grouptitlenewadd[]">'],
		[1,''],
		[2,'']
	]
];
</script>
EOT;
		shownav('house', 'menu_category_usergroup');
		showsubmenu('menu_category_usergroup', array(
			array('menu_category_usergroup', 'category&operation=usergroup&do=house', 1),
			array('menu_category_addusers', 'category&operation=addusers', 0),
		));
		
		showformheader('category&operation=usergroup&type=intermediary&do='.$do);
		showtableheader('usergroups_intermediary', 'fixpadding', 'id="intermediarygroups"');
		showsubtitle(array('', 'display_order', 'usergroups_title', 'usergroups_icon', ''));
		echo $intermediarygroup;
		echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_sepcial_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_type'] == 'intermediary') {
			if(is_array($_G['gp_grouptitlenewadd'])) {
				foreach($_G['gp_grouptitlenewadd'] as $k => $v) {
					if($v) {
						$data = array(
							'type' => 'intermediary',
							'title' => $_G['gp_grouptitlenewadd'][$k],
							'displayorder' => $_G['gp_groupdisplayordernewadd'][$k],
							'cid' => $cid,
						);
						$newgroupid = DB::insert('category_'.$do.'_usergroup', $data, true);
					}
				}
			}

			if(is_array($_G['gp_group_title'])) {
				foreach($_G['gp_group_title'] as $id => $title) {
					if(!$_G['gp_delete'][$id]) {
						DB::query("UPDATE ".DB::table('category_'.$do.'_usergroup')." SET 
						displayorder='{$_G['gp_group_displayorder'][$id]}', title='{$_G['gp_group_title'][$id]}' WHERE gid='$id'");
					}
				}
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('category_'.$do.'_usergroup')." WHERE gid IN ($ids) AND type='intermediary'");
				//$newgroupid = DB::result_first("SELECT gid FROM ".DB::table('category_usergroup')." WHERE type='personal' AND creditlower>'0' ORDER BY creditlower LIMIT 1");
				//DB::query("UPDATE ".DB::table('common_member')." SET groupid='$newgroupid', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
			}

		}

		categorycache('usergroup', $do);
		cpmsg(cplang('update_success'), 'action=category&operation=usergroup&do='.$do.'&type='.$_G['gp_type'], 'succeed');

	}

} elseif($operation == 'groupedit') {

	$_G['gp_type'] = empty($_G['gp_groupid']) ? 'personal' : '';
	if($_G['gp_type'] == 'personal') {
		$_G['gp_groupid'] = DB::result_first("SELECT gid FROM ".DB::table('category_'.$do.'_usergroup')." WHERE type='personal'");
	}

	$groupid = intval($_G['gp_groupid']);

	if(!empty($groupid)) {

		$usergroup = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$do.'_usergroup')." WHERE gid='$groupid'");

		if(!submitcheck('groupsubmit')) {

			if($_G['gp_type'] == 'personal') {
				$title = $usergroup['title'];
			} else {
				$title = $lang['menu_category_usergroup'] .' - '. $usergroup['title'];
			}

			shownav('house', 'menu_category_usergroup');
			showsubmenu($title);

			showformheader("category&operation=groupedit&do=$do&groupid=$groupid", 'enctype');
			showtableheader();
			showtitle('usergroups_basic');
			showsetting('name', 'titlenew', $usergroup['title'], 'text');
			if($usergroup['icon']) {
				$valueparse = parse_url($usergroup['icon']);
				if(isset($valueparse['host'])) {
					$groupicon = $usergroup['icon'];
				} else {
					$groupicon = $_G['setting']['attachurl'].'common/'.$usergroup['icon'].'?'.random(6);
				}
				$iconhtml = '<img src="'.$groupicon.'" />';
			}

			if($usergroup['banner']) {
				$valueparse = parse_url($usergroup['banner']);
				if(isset($valueparse['host'])) {
					$groupbanner = $usergroup['banner'];
				} else {
					$groupbanner = $_G['setting']['attachurl'].'common/'.$usergroup['banner'].'?'.random(6);
				}
				$bannerhtml = '<img src="'.$groupbanner.'" />';
			}

			$manager = array();
			if($usergroup['manageuid']) {
				$manager = DB::fetch_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$usergroup[manageuid]'");
			}

			showsetting('usergroups_icon', 'iconnew', $usergroup['icon'], 'filetext', '', 0, $iconhtml);
			showsetting('usergroups_banner', 'bannernew', $usergroup['banner'], 'filetext', '', 0, $bannerhtml);
			showsetting('category_usergroup_description', 'descriptionnew', $usergroup['description'], 'textarea');
			showtitle('usergroups_permissible');
			showsetting('category_usergroups_allowpost', 'allowpostnew', $usergroup['allowpost'], 'radio');
			showsetting('category_usergroups_postdayper', 'postdaypernew', $usergroup['postdayper'], 'text');
			if($usergroup['type'] == 'intermediary') {
				showtitle('usergroups_manage');
				showsetting('category_usergroups_manager', 'manageusernamenew', $manager['username'], 'text');
				showsetting('category_usergroups_allowpush', 'allowpushnew', $usergroup['allowpush'], 'radio');
				showsetting('category_usergroups_allowrecommend', 'allowrecommendnew', $usergroup['allowrecommend'], 'radio');
				showsetting('category_usergroups_allowhighlight', 'allowhighlightnew', $usergroup['allowhighlight'], 'radio');
				showsetting('category_usergroups_pushdayper', 'pushdaypernew', $usergroup['pushdayper'], 'text');
				showsetting('category_usergroups_recommenddayper', 'recommenddaypernew', $usergroup['recommenddayper'], 'text');
				showsetting('category_usergroups_highlightdayper', 'highlightdaypernew', $usergroup['highlightdayper'], 'text');
			}
			showsubmit('groupsubmit', 'submit');
			showtablefooter();
			showformfooter();

		} else {

			if($_FILES['iconnew']) {
				$data = array('extid' => 'category_'.$usergroup['gid']);
				$groupdata['icon'] = upload_icon_banner($data, $_FILES['iconnew'], '');
			} else {
				$groupdata['icon'] = $_G['gp_iconnew'];
			}

			if($_FILES['bannernew']) {
				$data = array('extid' => 'category_'.$usergroup['gid']);
				$groupdata['banner'] = upload_icon_banner($data, $_FILES['bannernew'], '');
			} else {
				$groupdata['banner'] = $_G['gp_bannernew'];
			}

			if($_G['gp_manageusernamenew']) {
				$manageuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_manageusernamenew]'");
				if(empty($manageuid)) {
					cpmsg(cplang('not_exists_manager'), '', 'error');
				}

				$usergroupid = DB::result_first("SELECT groupid FROM ".DB::table('category_'.$do.'_member')." WHERE uid='$manageuid'");
				if(!empty($usergroupid) && $usergroupid != $groupid) {
					cpmsg(cplang('manager_not_group'), '', 'error');
				}
			}

			$_G['gp_allowpostnew'] = $_G['gp_postdaypernew'] && empty($_G['gp_allowpostnew']) ? 1 : $_G['gp_allowpostnew'];
			$_G['gp_allowpushnew'] = $_G['gp_pushdaypernew'] && empty($_G['gp_allowpushnew']) ? 1 : $_G['gp_allowpushnew'];
			$_G['gp_allowrecommendnew'] = $_G['gp_recommenddaypernew'] && empty($_G['gp_allowrecommendnew']) ? 1 : $_G['gp_allowrecommendnew'];
			$_G['gp_allowhighlightnew'] = $_G['gp_highlightdaypernew'] && empty($_G['gp_allowhighlightnew']) ? 1 : $_G['gp_allowhighlightnew'];

			$groupdata = array(
				'title' => $_G['gp_titlenew'],
				'allowpost' => $_G['gp_allowpostnew'],
				'postdayper' => $_G['gp_postdaypernew'],
				'allowpush' => $_G['gp_allowpushnew'],
				'pushdayper' => $_G['gp_pushdaypernew'],
				'allowrecommend' => $_G['gp_allowrecommendnew'],
				'recommenddayper' => $_G['gp_recommenddaypernew'],
				'allowhighlight' => $_G['gp_allowhighlightnew'],
				'highlightdayper' => $_G['gp_highlightdaypernew'],
				'icon' => $groupdata['icon'],
				'banner' => $groupdata['banner'],
				'description' => trim($_G['gp_descriptionnew']),
				'manageuid' => $manageuid,
			);

			DB::update('category_'.$do.'_usergroup', $groupdata, "gid='$groupid'");

			categorycache('usergroup', $do, '', $modidentifier);
			cpmsg(cplang('update_success'), "action=category&operation=groupedit&do=$do&groupid=$groupid", 'succeed');

		}

	} else {
		cpmsg(cplang('please_select_usergroup'), "action=category&operation=usergroup&do=$do", 'error');
	}


} elseif($operation == 'membergroup') {

	if(!submitcheck('memberseachsubmit', 1)) {

		shownav('house', 'menu_category_member');
		showsubmenusteps('menu_category_member', array(
			array('nav_category_member_search', 1),
			array('nav_category_member_group', 0)
		));

		showformheader("category&operation=membergroup&do=$do", 'enctype');
		showtableheader();
		showtitle('nav_category_member_search');
		showsetting('username', 'username', '', 'text');
		showsubmit('memberseachsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$_G['setting']['memberperpage'] = 20;
		$page = max(1, $_G['page']);
		$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
		$urladd = !empty($_G['gp_username']) ? '&username='.$_G['gp_username'] : '';
		$where = '';

		if(!submitcheck('addgroupmember')) {

			if($_G['gp_username']) {
				$searchuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_username]'");
				$where = "WHERE m.uid='$searchuid'";
			}
			$membernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$do.'_member')." m $where");
			if($membernum > 0) {
				$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT."?action=category&operation=membergroup&do=$do&memberseachsubmit=yes".$urladd);
				$members = '';

				$query = DB::query("SELECT m.uid, m.username, mc.groupid FROM ".DB::table('common_member')." m
				LEFT JOIN ".DB::table('category_'.$do.'_member')." mc ON m.uid=mc.uid
				$where LIMIT $start_limit, ".$_G['setting']['memberperpage']);
				while($member = DB::fetch($query)) {
					$selectgroup = selectgroup($member['uid'], $member['groupid'], $cid, $do);
					$members .= showtablerow('', array('', ''), array(
						"<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
						$selectgroup,
					), TRUE);
				}
			}

			shownav('house', 'menu_category_member');
			showsubmenusteps('menu_category_member', array(
				array('nav_category_member_search', 0),
				array('nav_category_member_group', 1)
			));

			showformheader("category&operation=membergroup&do=$do&memberseachsubmit=yes", 'enctype');
			showtableheader();
			showtitle('nav_category_member_group');
			showsubtitle(array('username', 'usergroup'));
			echo $members;
			showsubmit('addgroupmember', 'submit', '', '', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			if($_G['gp_groupid']) {
				foreach($_G['gp_groupid'] as $uid => $groupid) {
					$uid = intval($uid);
					$groupid = intval($groupid);
					DB::update('category_'.$do.'_member', array('groupid' => $groupid), "uid='$uid'");
				}
			}

			cpmsg(cplang('update_success'), "action=category&operation=membergroup&do=$do", 'succeed');

		}

	}

} elseif($operation == 'cache') {

	$cachearray = array('categorysort', 'sortlist', 'channellist', 'arealist', 'usergroup');
	foreach($cachearray as $cachename) {
		categorycache($cachename, $do);
	}

	cpmsg(cplang('update_success'), '', 'succeed');

} elseif($operation == 'counter') {

	$pertask = isset($_G['gp_pertask']) ? intval($_G['gp_pertask']) : 100;
	$current = isset($_G['gp_current']) && $_G['gp_current'] > 0 ? intval($_G['gp_current']) : 0;
	$next = $current + $pertask;

	$nextlink = "action=category&operation=counter&current=$next&pertask=$pertask";
	$processed = 0;

	$queryc = DB::query("SELECT uid, threads FROM ".DB::table('category_'.$do.'_member')." LIMIT $current, $pertask");
	while($member = DB::fetch($queryc)) {
		$processed = 1;
		$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$do.'_thread')." WHERE authorid='$member[uid]'");
		if($member['threads'] != $threadcount) {
			DB::query("UPDATE LOW_PRIORITY ".DB::table('category_'.$do.'_member')." SET threads='$threadcount' WHERE uid='$member[uid]'", 'UNBUFFERED');
		}
	}

	if($processed) {
		cpmsg("$lang[counter_thread]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg(cplang('statistics_success'), '', 'succeed');
	}

} elseif($operation == 'classlist') {

	$classoptions = '';
	$classidarray = array();
	$classid = $_G['gp_classid'] ? $_G['gp_classid'] : 0;
	$query = DB::query("SELECT optionid, title FROM ".DB::table('category_sortoption')." WHERE classid='$classid' ORDER BY displayorder");
	while($option = DB::fetch($query)) {
		$classidarray[] = $option['optionid'];
		$classoptions .= "<a href=\"#ol\" onclick=\"ajaxget('".ADMINSCRIPT."?action=category&operation=optionlist&typeid={$_G['gp_typeid']}&classid=$option[optionid]', 'optionlist', 'optionlist', 'Loading...', '', checkedbox)\">$option[title]</a> &nbsp; ";
	}

	include template('common/header');
	echo $classoptions;
	include template('common/footer');
	exit;

} elseif($operation == 'optionlist') {
	$classid = $_G['gp_classid'];
	if(!$classid) {
		//note 取顶层分类
		$classid = DB::result_first("SELECT optionid FROM ".DB::table('category_sortoption')." WHERE classid='0' ORDER BY displayorder LIMIT 1");//note 小分类
	}
	$query = DB::query("SELECT optionid FROM ".DB::table('category_sortvar')." WHERE sortid='{$_G['gp_typeid']}'");
	$option = $options = array();
	while($option = DB::fetch($query)) {
		$options[] = $option['optionid'];
	}

	$optionlist = '';
	$query = DB::query("SELECT * FROM ".DB::table('category_sortoption')." WHERE classid='$classid' ORDER BY displayorder");
	while($option = DB::fetch($query)) {
		$optionlist .= "<input ".(in_array($option['optionid'], $options) ? ' checked="checked" ' : '')."class=\"checkbox\" type=\"checkbox\" name=\"typeselect[]\" id=\"typeselect_$option[optionid]\" value=\"$option[optionid]\" onclick=\"insertoption(this.value);\" /><label for=\"typeselect_$option[optionid]\">".dhtmlspecialchars($option['title'])."</label>&nbsp;&nbsp;";
	}
	include template('common/header');
	echo $optionlist;
	include template('common/footer');
	exit;

} elseif($operation == 'sortlist') {
	$optionid = $_G['gp_optionid'];
	$option = DB::fetch_first("SELECT * FROM ".DB::table('category_sortoption')." WHERE optionid='$optionid' LIMIT 1");
	include template('common/header');
	$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
	$option['available'] = 1;
	showtablerow('', array('class="td25"', 'class="td28 td23"', '', 'title="'.$lang['threadtype_edit_vars_type_'. $option['type']].'"'), array(
		"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\" ".($option['model'] ? 'disabled' : '').">",
		"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
		dhtmlspecialchars($option['title']),
		"<input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]]\" value=\"1\" ".($option['search'] ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"subjectshow[$option[optionid]]\" value=\"1\" ".($option['subjectshow'] ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"visitedshow[$option[optionid]]\" value=\"1\" ".($option['visitedshow'] ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"orderbyshow[$option[optionid]]\" value=\"1\" ".($option['orderbyshow'] ? 'checked' : '')." ".(!in_array($option['type'], array('number', 'range')) ? 'disabled' : '').">",
		"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'typetemplate', 'message');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_template']."</a><a href=\"###\" onclick=\"insertvar('$option[identifier]', 'stypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_stemplate']."</a><a href=\"###\" onclick=\"insertvar('$option[identifier]', 'ptypetemplate', 'post');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_ptemplate']."</a>",
		""
	));
	include template('common/footer');
	exit;

} elseif($operation == 'attach') {

	if(!submitcheck('attachsubmit')) {
		showformheader("category&operation=attach");
		showhiddenfields(array('operation' => $operation));
		$imageinfo = DB::result_first("SELECT imageinfo FROM ".DB::table('category_channel'));
		$channel = $imageinfo ? unserialize($imageinfo) : array();
		$checkwm = array($channel['watermarkstatus'] => 'checked');
		$checkmkdirfunc = !function_exists('mkdir') ? 'disabled' : '';
		$channel['watermarktext'] = unserialize($channel['watermarktext']);
		$channel['watermarktext']['fontpath'] = str_replace(array('ch/', 'en/'), '', $channel['watermarktext']['fontpath']);

		$fontlist = '<select name="channel[watermarktext][fontpath]">';
		$dir = opendir(DISCUZ_ROOT.'./static/image/seccode/font/en');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist .= '<option value="'.$entry.'"'.($entry == $channel['watermarktext']['fontpath'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$dir = opendir(DISCUZ_ROOT.'./static/image/seccode/font/ch');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist .= '<option value="'.$entry.'"'.($entry == $channel['watermarktext']['fontpath'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$fontlist .= '</select>';
		showsubmenu('category_channel');
		showtableheader('', '', 'id="basic"');
		showsetting('setting_attach_image_lib', array('channel[imagelib]', array(
		array(0, $lang['setting_attach_image_watermarktype_GD'], array('imagelibext' => 'none')),
		array(1, $lang['setting_attach_image_watermarktype_IM'], array('imagelibext' => ''))
		)), $channel['imagelib'], 'mradio');
		showtagheader('tbody', 'imagelibext', $channel['imagelib'], 'sub');
		showsetting('setting_attach_image_impath', 'channel[imageimpath]', $channel['imageimpath'], 'text');
		showtagfooter('tbody');
		showsetting('setting_attach_image_watermarkstatus', '', '', '<table style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="channel[watermarkstatus]" value="0" '.$checkwm[0].'>'.$lang['setting_attach_image_watermarkstatus_none'].'</td></tr><tr><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="1" '.$checkwm[1].'> #1</td><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="2" '.$checkwm[2].'> #2</td><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="3" '.$checkwm[3].'> #3</td></tr><tr><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="4" '.$checkwm[4].'> #4</td><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="5" '.$checkwm[5].'> #5</td><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="6" '.$checkwm[6].'> #6</td></tr><tr><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="7" '.$checkwm[7].'> #7</td><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="8" '.$checkwm[8].'> #8</td><td><input class="radio" type="radio" name="channel[watermarkstatus]" value="9" '.$checkwm[9].'> #9</td></tr></table>');
		showsetting('setting_attach_image_watermarkminwidthheight', array('channel[watermarkminwidth]', 'channel[watermarkminheight]'), array(intval($channel['watermarkminwidth']), intval($channel['watermarkminheight'])), 'multiply');
		showsetting('setting_house_attach_image_watermarktype', array('channel[watermarktype]', array(
		array('gif', $lang['setting_attach_image_watermarktype_gif'], array('watermarktypeext' => 'none')),
		array('png', $lang['setting_attach_image_watermarktype_png'], array('watermarktypeext' => 'none')),
		array('text', $lang['setting_attach_image_watermarktype_text'], array('watermarktypeext' => ''))
		)), $channel['watermarktype'], 'mradio');
		showsetting('setting_attach_image_watermarktrans', 'channel[watermarktrans]', $channel['watermarktrans'], 'text');
		showsetting('setting_attach_image_watermarkquality', 'channel[watermarkquality]', $channel['watermarkquality'], 'text');
		showtagheader('tbody', 'watermarktypeext', $channel['watermarktype'] == 'text', 'sub');
		showsetting('setting_attach_image_watermarktext_text', 'channel[watermarktext][text]', $channel['watermarktext']['text'], 'textarea');
		showsetting('setting_attach_image_watermarktext_fontpath', '', '', $fontlist);
		showsetting('setting_attach_image_watermarktext_size', 'channel[watermarktext][size]', $channel['watermarktext']['size'], 'text');
		showsetting('setting_attach_image_watermarktext_angle', 'channel[watermarktext][angle]', $channel['watermarktext']['angle'], 'text');
		showsetting('setting_attach_image_watermarktext_color', 'channel[watermarktext][color]', $channel['watermarktext']['color'], 'color');
		showsetting('setting_attach_image_watermarktext_shadowx', 'channel[watermarktext][shadowx]', $channel['watermarktext']['shadowx'], 'text');
		showsetting('setting_attach_image_watermarktext_shadowy', 'channel[watermarktext][shadowy]', $channel['watermarktext']['shadowy'], 'text');
		showsetting('setting_attach_image_watermarktext_shadowcolor', 'channel[watermarktext][shadowcolor]', $channel['watermarktext']['shadowcolor'], 'color');
		showsetting('setting_attach_image_watermarktext_imtranslatex', 'channel[watermarktext][translatex]', $channel['watermarktext']['translatex'], 'text');
		showsetting('setting_attach_image_watermarktext_imtranslatey', 'channel[watermarktext][translatey]', $channel['watermarktext']['translatey'], 'text');
		showsetting('setting_attach_image_watermarktext_imskewx', 'channel[watermarktext][skewx]', $channel['watermarktext']['skewx'], 'text');
		showsetting('setting_attach_image_watermarktext_imskewy', 'channel[watermarktext][skewy]', $channel['watermarktext']['skewy'], 'text');
		showtagfooter('tbody');
		showsubmit('attachsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} else {

		$channelnew = $_G['gp_channel'];
		if(isset($channelnew['watermarktext'])) {
			$channelnew['watermarktext']['size'] = intval($channelnew['watermarktext']['size']);
			$channelnew['watermarktext']['angle'] = intval($channelnew['watermarktext']['angle']);
			$channelnew['watermarktext']['shadowx'] = intval($channelnew['watermarktext']['shadowx']);
			$channelnew['watermarktext']['shadowy'] = intval($channelnew['watermarktext']['shadowy']);
			$channelnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $channelnew['watermarktext']['fontpath']);
			if($channelnew['watermarktype'] == 'text' && $channelnew['watermarktext']['fontpath']) {
				$fontpath = $channelnew['watermarktext']['fontpath'];
				$fontpathnew = 'ch/'.$fontpath;
				$channelnew['watermarktext']['fontpath'] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
				if(!$channelnew['watermarktext']['fontpath']) {
					$fontpathnew = 'en/'.$fontpath;
					$channelnew['watermarktext']['fontpath'] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
				}
				if(!$channelnew['watermarktext']['fontpath']) {
					cpmsg('watermarkpreview_fontpath_error', '', 'error');
				}
			}
		}

		DB::update('category_channel', array(
		'imageinfo' => addslashes(serialize($channelnew))
		), "cid='$cid'");

		categorycache('channellist');
		cpmsg('threadtype_infotypes_option_succeed', 'action=category&operation=attach&do='.$do, 'succeed');
	}

} elseif($operation == 'addusers') {	

	if(!submitcheck('adduserssubmit')) {

		shownav('house', 'menu_category_usergroup');
		showsubmenu('menu_category_usergroup', array(
			array('menu_category_usergroup', 'category&operation=usergroup&do=house', 0),
			array('menu_category_addusers', 'category&operation=addusers', 1),
		));

		$_G['gp_gid'] = intval($_G['gp_gid']);
		$threadtypes = '<select name="sortid" onchange="window.location.href = \'?action=category&operation=addusers&do='.$do.'&gid=\'+ this.options[this.selectedIndex].value"><option value="0">'.cplang('none').'</option>';

		$query = DB::query("SELECT * FROM ".DB::table('category_'.$do.'_usergroup')." WHERE type='intermediary' ORDER BY displayorder");
		while($usergroup = DB::fetch($query)) {
			$threadtypes .= '<option value="'.$usergroup['gid'].'" '.($_G['gp_gid'] == $usergroup['gid'] ? 'selected="selected"' : '').'>'.dhtmlspecialchars($usergroup['title']).'</option>';
		}
		$threadtypes .= '</select>';

		showformheader('category&operation=addusers&gid='.$_G['gp_gid'].'&do='.$do);
		showtableheader('category_select_usergroup');
		showsetting('category_usergroup_name', '', '', $threadtypes);

		if($_G['gp_gid']) {
			showtableheader('category_usergroup_manger');
			$_G['setting']['memberperpage'] = 20;
			$page = max(1, $_G['page']);
			$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
			$members = '';
			$membernum = DB::result_first("SELECT COUNT(*) 
				FROM ".DB::table('common_member')." m, ".DB::table('category_'.$do.'_member')." hm
				WHERE m.uid=hm.uid AND hm.groupid='$_G[gp_gid]'");
			if($membernum > 0) {
				$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT."?action=category&operation=addusers&do=$do&gid=$_G[gp_gid]");
				$query = DB::query("SELECT m.username, m.uid, m.email 
					FROM ".DB::table('common_member')." m, ".DB::table('category_'.$do.'_member')." hm 
					WHERE m.uid=hm.uid AND hm.groupid='$_G[gp_gid]' LIMIT $start_limit, ".$_G['setting']['memberperpage']);			
				while($member = DB::fetch($query)) {
					$members .= showtablerow('', array('class="td25"', 'class="td28"', 'class="td28"', 'class="td28"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$member[uid]\">",
						"<a href=\"home.php?mod=space&uid=$member[uid]\" class=\"act nowrap\" target=\"_blank\">".dhtmlspecialchars($member['username'])."</a>",
						"*******",
						"$member[email]"
					), TRUE);
				}
			}
?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1, '', 'td25'],
		[1, '<input type="text" class="txt" name="newusernames[]" size="15" value="">', 'td29'],
		[1, '<input type="text" class="txt" name="newpasswords[]" size="15">'],
		[1, '<input type="text" class="txt" name="newemails[]" size="30" value="">', 'td29'],
		[2, '']
	],
];
</script>
<?
			shownav('house', 'threadtype_infotypes');
			showformheader('category&operation=addusers&gid='.$_G['gp_gid'].'&do=$do');
			showtableheader('');
			showsubtitle(array('', 'username', 'password', 'email'));
			echo $members;
			echo '<tr><td></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['cplog_members_add'].'</a></div></td>';
			showsubmit('adduserssubmit', 'submit', 'del', '', $multipage);
			showtablefooter();
			showformfooter();
		}

	} else {

		$groupid = intval($_G['gp_gid']);
		$newusernames = $_G['gp_newusernames'];
		$newpasswords = $_G['gp_newpasswords'];
		$newemails = $_G['gp_newemails'];

		if($deletes = dimplode($_G['gp_delete'])) {			
			DB::update('category_'.$do.'_member', array('groupid' => '1'), "uid IN ($deletes)");
		}

		if($newusernames && $newpasswords && $newemails && $groupid) {
			foreach($newusernames as $key => $newusername) {

				if(!$newusername || !$newpasswords[$key] || !$newemails[$key]) {
					continue;
				} else {
					$useruid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$newusername'");
					if($useruid) {
						if($usergroupid = DB::result_first("SELECT groupid FROM ".DB::table('category_'.$do.'_member')." WHERE uid='$useruid'")) {
							if($usergroupid && $usergroupid != $groupid) {
								DB::update('category_'.$do.'_member', array('groupid' => $groupid), "uid='$useruid'");
							} else {
								continue;
							}
						} else {
      						DB::insert('category_'.$do.'_member', array('uid' => $useruid, 'groupid' => $groupid, 'cid' => 0, 'threads' => 0, 'todaythreads' => 0, 'todaypush' => 0, 'todayrecommend' => 0, 'todayhighlight' => 0, 'lastpost' => 0));
						}
					} else {
						loaducenter();
						$uid = uc_user_register($newusername, $newpasswords[$key], $newemails[$key]);
						if($uid <= 0) {
							continue;
						}
						$data = array(
							'uid' => $uid,
							'username' => $newusername,
							'password' => md5(random(10)),
							'email' => $newemails[$key],
							'adminid' => 0,
							'groupid' => 10,
							'regdate' => $_G['timestamp'],
							'credits' => 0,
						);
						DB::insert('common_member', $data);
						DB::insert('common_member_profile', array('uid' => $uid));
						DB::insert('common_member_field_forum', array('uid' => $uid));
						DB::insert('common_member_field_home', array('uid' => $uid));
						DB::insert('common_member_status', array('uid' => $uid, 'regip' => 'Manual Acting', 'lastvisit' => $_G['timestamp'], 'lastactivity' => $_G['timestamp']));
						$init_arr = explode(',', $_G['setting']['initcredits']);
						$count_data = array(
							'uid' => $uid,
							'extcredits1' => $init_arr[0],
							'extcredits2' => $init_arr[1],
							'extcredits3' => $init_arr[2],
							'extcredits4' => $init_arr[3],
							'extcredits5' => $init_arr[4],
							'extcredits6' => $init_arr[5],
							'extcredits7' => $init_arr[6],
							'extcredits8' => $init_arr[7]
							);
						DB::insert('common_member_count', $count_data);
						manyoulog('user', $uid, 'add');

						DB::insert('category_'.$do.'_member', array('uid' => $uid, 'groupid' => $groupid, 'cid' => 0, 'threads' => 0, 'todaythreads' => 0, 'todaypush' => 0, 'todayrecommend' => 0, 'todayhighlight' => 0, 'lastpost' => 0));
					}
				}
			}
		}
		updatecache('setting');
		cpmsg('category_user_add_success', 'action=category&operation=addusers&gid='.$_G['gp_gid'].'&do='.$do, 'succeed');
	}
}

function selectgroup($uid, $groupid, $cid, $identifier) {

	$usergroups = '<select name="groupid['.$uid.']">';
	$query = DB::query("SELECT gid, title FROM ".DB::table('category_'.$identifier.'_usergroup')."  ORDER BY gid");
	while($group = DB::fetch($query)) {
		$usergroups .= '<option value="'.$group['gid'].'" '.($group['gid'] == $groupid ? 'selected=selected' : '').'>'.$group['title'].'</option>';
	}
	$usergroups .= '</select>';

	return $usergroups;
}

function showoption($var, $type) {
	global $optiontitle, $lang;
	if($optiontitle[$var]) {
		$optiontitle[$var] = $type == 'title' ? $optiontitle[$var] : $optiontitle[$var].($type == 'value' ? $lang['value'] : $lang['unit']);
		return $optiontitle[$var];
	} else {
		return "!$var!";
	}
}

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function showcategory(&$cate, $type = '', $last = '') {
	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="checkbox" class="checkbox" name="delete[]" value="'.$cate['aid'].'" /></td><td class="td25"><input type="text" class="txt" name="order['.$cate['aid'].']" value="'.$cate['displayorder'].'" /></td><td>';
		if($type == 'city') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'street') {
			$return .= '<div id="cb_'.$cate['aid'].'" class="childboard">';
		}

		$return .= '<input type="text" name="name['.$cate['aid'].']" value="'.htmlspecialchars($cate['title']).'" class="txt" />';
		$return .= $type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$cate['aid'].')" class="addchildboard">'.cplang('add_street').'</a>' : '';
		$return .= '</div></td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$cate['aid'].')" class="addtr">'.cplang('add_province').'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('add_city').'</a></div></td></tr>';
		}
	}
	echo $return;
}

function updateinformation($cid, $do) {

	global $_G;
	$db = DB::object();
	$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
	$update = array('uniqueid' => $siteuniqueid, 'version' => HOUSE_VERSION, 'release' => HOUSE_RELEASE, 'bbname' => $_G['setting']['bbname']);

	$sortnum = array();
	$updatetime = @filemtime(DISCUZ_ROOT.'./data/categoryupdatetime.lock');
	if(empty($updatetime) || (TIMESTAMP - $updatetime > 3600 * 4)) {
		@touch(DISCUZ_ROOT.'./data/categoryupdatetime.lock');
		$update['members'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$do.'_member')."");
		$update['threads'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$do.'_thread')."");
		$update['usergroups'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$do.'_usergroup')."");
		$update['areas'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_area')." WHERE cid='$cid'");
		$update['sorts'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_sort')." WHERE cid='$cid'");
		$query = DB::query("SELECT sortid, name FROM ".DB::table('category_sort')." WHERE cid='$cid'");
		while($sort = DB::fetch($query)) {
			$sortnum[$sort['name']] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_sortvalue')."$sort[sortid]");
			$update['sortnum'] .= $sort['name'].'|'.$sortnum[$sort['name']]."\t";
		}
	}

	$data = '';
	foreach($update as $key => $value) {
		$data .= $key.'='.rawurlencode($value).'&';
	}

	echo '<div style="display:none;"><img src="ht'.'tp:/'.'/cus'.'tome'.'r.disc'.'uz.n'.'et/n'.'ews'.'.p'.'hp?os='.$do.'&update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).TIMESTAMP), 8, 8).'&timestamp='.TIMESTAMP.'"/></div>';

}

function countmembers($condition) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->getcount($condition);
}

function searchmembers($condition) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->search($condition, 1000);
}



?>