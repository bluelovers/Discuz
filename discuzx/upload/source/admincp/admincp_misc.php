<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_misc.php 25638 2011-11-16 09:26:19Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'onlinelist') {

	if(!submitcheck('onlinesubmit')) {

		shownav('style', 'misc_onlinelist');
		showsubmenu('nav_misc_onlinelist');
		showtips('misc_onlinelist_tips');
		showformheader('misc&operation=onlinelist&');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'display_order', 'usergroup', 'usergroups_title', 'misc_onlinelist_image'));

		$listarray = array();
		$query = DB::query("SELECT * FROM ".DB::table('forum_onlinelist'));
		while($list = DB::fetch($query)) {
			$list['title'] = dhtmlspecialchars($list['title']);
			$listarray[$list['groupid']] = $list;
		}

		$onlinelist = '';
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup'));
		$group = array('groupid' => 0, 'grouptitle' => 'Member');
		do {
			$id = $group['groupid'];
			showtablerow('', array('class="td25"', 'class="td23 td28"', 'class="td24"', 'class="td24"', 'class="td21 td26"'), array(
				$listarray[$id]['url'] ? " <img src=\"static/image/common/{$listarray[$id]['url']}\">" : '',
				'<input type="text" class="txt" name="displayordernew['.$id.']" value="'.$listarray[$id]['displayorder'].'" size="3" />',
				$group['groupid'] <= 8 ? cplang('usergroups_system_'.$id) : $group['grouptitle'],
				'<input type="text" class="txt" name="titlenew['.$id.']" value="'.($listarray[$id]['title'] ? $listarray[$id]['title'] : $group['grouptitle']).'" size="15" />',
				'<input type="text" class="txt" name="urlnew['.$id.']" value="'.$listarray[$id]['url'].'" size="20" />'
			));

		} while($group = DB::fetch($query));

		showsubmit('onlinesubmit', 'submit', 'td');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_urlnew'])) {
			DB::query("DELETE FROM ".DB::table('forum_onlinelist')."");
			foreach($_G['gp_urlnew'] as $id => $url) {
				$url = trim($url);
				if($id == 0 || $url) {
					$data = array(
						'groupid' => $id,
						'displayorder' => $_G['gp_displayordernew'][$id],
						'title' => $_G['gp_titlenew'][$id],
						'url' => $url,
					);
					DB::insert('forum_onlinelist', $data);
				}
			}
		}

		updatecache(array('onlinelist', 'groupicon'));
		cpmsg('onlinelist_succeed', 'action=misc&operation=onlinelist', 'succeed');

	}

} elseif($operation == 'link') {

	if(!submitcheck('linksubmit')) {

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
		[1,'<input type="text" class="txt" name="newname[]" size="15">'],
		[1,'<input type="text" class="txt" name="newurl[]" size="20">'],
		[1,'<input type="text" class="txt" name="newdescription[]" size="30">', 'td26'],
		[1,'<input type="text" class="txt" name="newlogo[]" size="20">']
	]
]
</script>
<?php

		shownav('extended', 'misc_link');
		showsubmenu('nav_misc_links');
		showtips('misc_link_tips');
		showformheader('misc&operation=link');
		showtableheader();
		showsubtitle(array('', 'display_order', 'misc_link_edit_name', 'misc_link_edit_url', 'misc_link_edit_description', 'misc_link_edit_logo', 'misc_link_group1', 'misc_link_group2', 'misc_link_group3','misc_link_group4'));
		showsubtitle(array('', '', '', '', '', '', '<input class="checkbox" type="checkbox" name="portalall" onclick="checkAll(\'prefix\', this.form, \'portal\', \'portalall\')">',
			'<input class="checkbox" type="checkbox" name="forumall" onclick="checkAll(\'prefix\', this.form, \'forum\', \'forumall\')">',
			'<input class="checkbox" type="checkbox" name="groupall" onclick="checkAll(\'prefix\', this.form, \'group\', \'groupall\')">',
			'<input class="checkbox" type="checkbox" name="homeall" onclick="checkAll(\'prefix\', this.form, \'home\', \'homeall\')">'));

		$query = DB::query("SELECT * FROM ".DB::table('common_friendlink')." ORDER BY displayorder");
		while($forumlink = DB::fetch($query)) {
			$type = sprintf('%04b', $forumlink['type']);
			showtablerow('', array('class="td25"', 'class="td28"', '', '', 'class="td26"'), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$forumlink['id'].'" />',
				'<input type="text" class="txt" name="displayorder['.$forumlink[id].']" value="'.$forumlink['displayorder'].'" size="3" />',
				'<input type="text" class="txt" name="name['.$forumlink[id].']" value="'.$forumlink['name'].'" size="15" />',
				'<input type="text" class="txt" name="url['.$forumlink[id].']" value="'.$forumlink['url'].'" size="20" />',
				'<input type="text" class="txt" name="description['.$forumlink[id].']" value="'.$forumlink['description'].'" size="30" />',
				'<input type="text" class="txt" name="logo['.$forumlink[id].']" value="'.$forumlink['logo'].'" size="20" />',
				'<input class="checkbox" type="checkbox" value="1" name="portal['.$forumlink[id].']" '.($type[0] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="forum['.$forumlink[id].']" '.($type[1] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="group['.$forumlink[id].']" '.($type[2] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="home['.$forumlink[id].']" '.($type[3] ? "checked" : '').'>',
			));
		}

		echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['misc_link_add'].'</a></div></td></tr>';
		showsubmit('linksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_delete']) {
			DB::delete('common_friendlink', "id IN (".dimplode($_G['gp_delete']).")");
		}

		if(is_array($_G['gp_name'])) {
			foreach($_G['gp_name'] as $id => $val) {
				$type_str = intval($_G['gp_portal'][$id]).intval($_G['gp_forum'][$id]).intval($_G['gp_group'][$id]).intval($_G['gp_home'][$id]);
				$type_str = intval($type_str, '2');
				DB::update('common_friendlink', array(
					'displayorder' => $_G['gp_displayorder'][$id],
					'name' => $_G['gp_name'][$id],
					'url' => $_G['gp_url'][$id],
					'description' => $_G['gp_description'][$id],
					'logo' => $_G['gp_logo'][$id],
					'type' => $type_str,
				), array(
					'id' => $id,
				));
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($value) {
					DB::insert('common_friendlink', array(
						'displayorder' => $_G['gp_newdisplayorder'][$key],
						'name' => $value,
						'url' => $_G['gp_newurl'][$key],
						'description' => $_G['gp_newdescription'][$key],
						'logo' => $_G['gp_newlogo'][$key],
						'type' => 0,
					));
				}
			}
		}

		updatecache('forumlinks');
		cpmsg('forumlinks_succeed', 'action=misc&operation=link', 'succeed');

	}

} elseif($operation == 'relatedlink') {

	if(!submitcheck('linksubmit')) {

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" name="newname[]" size="15">'],
		[1,'<input type="text" name="newurl[]" size="50">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newarticle[]">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newforum[]">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newgroup[]">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newblog[]">']
	]
]
</script>
<?php

		shownav('extended', 'misc_relatedlink');
		showsubmenu('nav_misc_relatedlink');
		showtips('misc_relatedlink_tips');
		showformheader('misc&operation=relatedlink');
		showtableheader();
		showsubtitle(array('', 'misc_relatedlink_edit_name', 'misc_relatedlink_edit_url', '<input class="checkbox" type="checkbox" name="articleall" onclick="checkAll(\'prefix\', this.form, \'article\', \'articleall\')">'.cplang('misc_relatedlink_extent_article'), '<input class="checkbox" type="checkbox" name="forumall" onclick="checkAll(\'prefix\', this.form, \'forum\', \'forumall\')">'.cplang('misc_relatedlink_extent_forum'), '<input class="checkbox" type="checkbox" name="groupall" onclick="checkAll(\'prefix\', this.form, \'group\', \'groupall\')">'.cplang('misc_relatedlink_extent_group'),'<input class="checkbox" type="checkbox" name="blogall" onclick="checkAll(\'prefix\', this.form, \'blog\', \'blogall\')">'.cplang('misc_relatedlink_extent_blog')));

		$query = DB::query("SELECT * FROM ".DB::table('common_relatedlink')." ORDER BY id DESC");
		while($link = DB::fetch($query)) {
			$extent = sprintf('%04b', $link['extent']);
			showtablerow('', array('class="td25"', '', '', 'class="td26"', 'class="td26"', 'class="td26"', ''), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$link['id'].'" />',
				'<input type="text" class="txt" name="name['.$link[id].']" value="'.$link['name'].'" size="15" />',
				'<input type="text" name="url['.$link[id].']" value="'.$link['url'].'" size="50" />',
				'<input class="checkbox" type="checkbox" value="1" name="article['.$link[id].']" '.($extent[0] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="forum['.$link[id].']" '.($extent[1] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="group['.$link[id].']" '.($extent[2] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="blog['.$link[id].']" '.($extent[3] ? "checked" : '').'>',
			));
		}

		echo '<tr><td></td><td colspan="6"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['misc_relatedlink_add'].'</a></div></td></tr>';
		showsubmit('linksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_delete']) {
			DB::delete('common_relatedlink', "id IN (".dimplode($_G['gp_delete']).")");
		}

		if(is_array($_G['gp_name'])) {
			foreach($_G['gp_name'] as $id => $val) {
				$extent_str = intval($_G['gp_article'][$id]).intval($_G['gp_forum'][$id]).intval($_G['gp_group'][$id]).intval($_G['gp_blog'][$id]);
				$extent_str = intval($extent_str, '2');
				DB::update('common_relatedlink', array(
					'name' => $_G['gp_name'][$id],
					'url' => $_G['gp_url'][$id],
					'extent' => $extent_str,
				), array(
					'id' => $id,
				));
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($value) {
					$extent_str = intval($_G['gp_newarticle'][$key]).intval($_G['gp_newforum'][$key]).intval($_G['gp_newgroup'][$key]).intval($_G['gp_newblog'][$key]);
					$extent_str = intval($extent_str, '2');
					DB::insert('common_relatedlink', array(
						'name' => $value,
						'url' => $_G['gp_newurl'][$key],
						'extent' => $extent_str,
					));
				}
			}
		}

		updatecache('relatedlink');
		cpmsg('relatedlink_succeed', 'action=misc&operation=relatedlink', 'succeed');

	}

} elseif($operation == 'bbcode') {

	$edit = $_G['gp_edit'];
	if(!submitcheck('bbcodessubmit') && !$edit) {
		shownav('style', 'setting_editor');

		showsubmenu('setting_editor', array(
			array('setting_editor_global', 'setting&operation=editor', 0),
			array('setting_editor_code', 'misc&operation=bbcode', 1),
		));

		showtips('misc_bbcode_edit_tips');
		showformheader('misc&operation=bbcode');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'misc_bbcode_tag', 'available', 'display', 'display_order', 'misc_bbcode_icon', 'misc_bbcode_icon_file', ''));
		$query = DB::query("SELECT * FROM ".DB::table('forum_bbcode')." ORDER BY displayorder");
		while($bbcode = DB::fetch($query)) {
			showtablerow('', array('class="td25"', 'class="td21"', 'class="td25"', 'class="td25"', 'class="td28 td24"', 'class="td25"', 'class="td21"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$bbcode[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"tagnew[$bbcode[id]]\" value=\"$bbcode[tag]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] ? 'checked="checked"' : NULL).">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"displaynew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] == '2' ? 'checked="checked"' : NULL).">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$bbcode[id]]\" value=\"$bbcode[displayorder]\">",
				$bbcode['icon'] ? "<em class=\"editor\"><a class=\"customedit\"><img src=\"static/image/common/$bbcode[icon]\" border=\"0\"></a></em>" : ' ',
				"<input type=\"text\" class=\"txt\" size=\"25\" name=\"iconnew[$bbcode[id]]\" value=\"$bbcode[icon]\">",
				"<a href=\"".ADMINSCRIPT."?action=misc&operation=bbcode&edit=$bbcode[id]\" class=\"act\">$lang[detail]</a>"
			));
		}
		showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td25"', 'class="td28 td24"', 'class="td25"', 'class="td21"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="15" name="newtag">',
			'',
			'',
			'<input type="text" class="txt" size="2" name="newdisplayorder">',
			'',
			'<input type="text" class="txt" size="25" name="newicon">',
			''
		));
		showsubmit('bbcodessubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} elseif(submitcheck('bbcodessubmit')) {

		$delete = $_G['gp_delete'];
		if(is_array($delete)) {
			$ids = '\''.implode('\',\'', $delete).'\'';
			DB::query("DELETE FROM	".DB::table('forum_bbcode')." WHERE id IN ($ids)");
		}

		$tagnew = $_G['gp_tagnew'];
		$displaynew = $_G['gp_displaynew'];
		$displayordernew = $_G['gp_displayordernew'];
		$iconnew = $_G['gp_iconnew'];
		if(is_array($tagnew)) {
			$custom_ids = array();
			$query = DB::query("SELECT id FROM ".DB::table('forum_bbcode'));
			while($bbcode = DB::fetch($query)) {
				$custom_ids[] = $bbcode['id'];
			}
			$availablenew = $_G['gp_availablenew'];
			foreach($tagnew as $id => $val) {
				if(in_array($id, $custom_ids) && !preg_match("/^[0-9a-z]+$/i", $tagnew[$id]) && strlen($tagnew[$id]) < 20) {
					cpmsg('dzcode_edit_tag_invalid', '', 'error');
				}
				$availablenew[$id] = in_array($id, $custom_ids) ? $availablenew[$id] : 1;
				$availablenew[$id] = $availablenew[$id] && $displaynew[$id] ? 2 : $availablenew[$id];
				$sqladd = in_array($id, $custom_ids) ? ", tag='$tagnew[$id]', icon='$iconnew[$id]'" : '';
				DB::query("UPDATE ".DB::table('forum_bbcode')." SET available='$availablenew[$id]', displayorder='$displayordernew[$id]' $sqladd WHERE id='$id'");
			}
		}

		$newtag = $_G['gp_newtag'];
		if($newtag != '') {
			if(!preg_match("/^[0-9a-z]+$/i", $newtag && strlen($newtag) < 20)) {
				cpmsg('dzcode_edit_tag_invalid', '', 'error');
			}
			$data = array(
				'tag' => $newtag,
				'icon' => $_G['gp_newicon'],
				'available' => 0,
				'displayorder' => $_G['gp_newdisplayorder'],
				'params' => 1,
				'nest' => 1,
			);
			DB::insert('forum_bbcode', $data);
		}

		updatecache(array('bbcodes', 'bbcodes_display'));
		cpmsg('dzcode_edit_succeed', 'action=misc&operation=bbcode', 'succeed');

	} elseif($edit) {

		$bbcode = DB::fetch_first("SELECT * FROM ".DB::table('forum_bbcode')." WHERE id='$edit'");
		if(!$bbcode) {
			cpmsg('bbcode_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$bbcode['perm'] = explode("\t", $bbcode['perm']);
			$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
			$groupselect = array();
			while($group = DB::fetch($query)) {
				$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
				$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $bbcode['perm']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
			}
			$select = '<select name="permnew[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $var['value']) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>'.
				'<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
				($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
				($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
				'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';

			$bbcode['prompt'] = str_replace("\t", "\n", $bbcode['prompt']);

			shownav('style', 'nav_posting_bbcode');
			showsubmenu($lang['misc_bbcode_edit'].' - '.$bbcode['tag']);
			showformheader("misc&operation=bbcode&edit=$edit");
			showtableheader();
			showsetting('misc_bbcode_edit_tag', 'tagnew', $bbcode['tag'], 'text');
			showsetting('misc_bbcode_edit_replacement', 'replacementnew', $bbcode['replacement'], 'textarea');
			showsetting('misc_bbcode_edit_example', 'examplenew', $bbcode['example'], 'text');
			showsetting('misc_bbcode_edit_explanation', 'explanationnew', $bbcode['explanation'], 'text');
			showsetting('misc_bbcode_edit_params', 'paramsnew', $bbcode['params'], 'text');
			showsetting('misc_bbcode_edit_prompt', 'promptnew', $bbcode['prompt'], 'textarea');
			showsetting('misc_bbcode_edit_nest', 'nestnew', $bbcode['nest'], 'text');
			showsetting('misc_bbcode_edit_usergroup', '', '', $select);
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$tagnew = trim($_G['gp_tagnew']);
			$paramsnew = $_G['gp_paramsnew'];
			$nestnew = $_G['gp_nestnew'];
			$replacementnew = $_G['gp_replacementnew'];
			$examplenew = $_G['gp_examplenew'];
			$explanationnew = $_G['gp_explanationnew'];
			$promptnew = $_G['gp_promptnew'];
			$permnew = implode("\t", $_G['gp_permnew']);

			if(!preg_match("/^[0-9a-z]+$/i", $tagnew)) {
				cpmsg('dzcode_edit_tag_invalid', '', 'error');
			} elseif($paramsnew < 1 || $paramsnew > 3 || $nestnew < 1 || $nestnew > 3) {
				cpmsg('dzcode_edit_range_invalid', '', 'error');
			}
			$promptnew = trim(str_replace(array("\t", "\r", "\n"), array('', '', "\t"), $promptnew));

			DB::query("UPDATE ".DB::table('forum_bbcode')." SET tag='$tagnew', replacement='$replacementnew', example='$examplenew', explanation='$explanationnew', params='$paramsnew', prompt='$promptnew', nest='$nestnew', perm='$permnew' WHERE id='$edit'");

			updatecache(array('bbcodes', 'bbcodes_display'));
			cpmsg('dzcode_edit_succeed', 'action=misc&operation=bbcode', 'succeed');

		}
	}

} elseif($operation == 'censor') {

	$ppp = 30;

	$addcensors = isset($_G['gp_addcensors']) ? trim($_G['gp_addcensors']) : '';

	if($do == 'export') {

		ob_end_clean();
		dheader('Cache-control: max-age=0');
		dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP - 31536000).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename=CensorWords.txt');
		dheader('Content-Type: text/plain');
		$query = DB::query("SELECT * FROM ".DB::table('common_word_type'));
		while($result = DB::fetch($query)) {
			$result['used'] = 0;
			$word_type[$result['id']] = $result;
		}
		$query = DB::query("SELECT find, replacement, type FROM ".DB::table('common_word')." ORDER BY type ASC, find ASC");
		while($censor = DB::fetch($query)) {
			$censor['replacement'] = str_replace('*', '', $censor['replacement']) <> '' ? $censor['replacement'] : '';
			if($word_type[$censor['type']]['used'] == 0 && $word_type[$censor['type']]) {
				if($temp_type == 1) {
					echo "[/type]\n";
				}
				echo "\n[type:".$word_type[$censor['type']]['typename']."]\n";
				$temp_type = 1;
				$word_type[$censor['type']]['used'] = 1;
			}
			echo $censor['find'].($censor['replacement'] != '' ? '='.dstripslashes($censor['replacement']) : '')."\n";
		}
		if($temp_type == 1) {
			echo "[/type]\n";
			unset($temp_type);
		}
		define('FOOTERDISABLED' , 1);
		exit();

	} elseif(submitcheck('addcensorsubmit') && $addcensors != '') {
		$oldwords = array();
		if($_G['adminid'] == 1 && $_G['gp_overwrite'] == 2) {
			DB::query("TRUNCATE ".DB::table('common_word')."");
		} else {
			$query = DB::query("SELECT find, admin FROM ".DB::table('common_word'));
			while($censor = DB::fetch($query)) {
				$oldwords[md5($censor['find'])] = $censor['admin'];
			}
			DB::free_result($query);
		}
		$typesearch = "\[type\:(.+?)\](.+?)\[\/type\]";
		preg_match_all("/($typesearch)/is", $addcensors, $wordmatch);
		$wordmatch[3][] = preg_replace("/($typesearch)/is", '', $addcensors);
		$updatecount = $newcount = $ignorecount = 0;
		foreach($wordmatch[3] AS $key => $val) {
			$word_type = 0;
			if($wordmatch[2][$key] && !$wordtype_used[$key]) {
				if(!$word_type = DB::result_first("SELECT id FROM ".DB::table('common_word_type')." WHERE typename = '{$wordmatch[2][$key]}'")) {
					$wordmatch[2][$key] = daddslashes($wordmatch[2][$key]);
					DB::insert('common_word_type', array('typename' => $wordmatch[2][$key]), true);
					$word_type = DB::insert_id();
				}
				$wordtype_used[$key] = 1;
			}
			$word_type = $word_type ? $word_type : 0 ;

			$censorarray = explode("\n", $val);
			foreach($censorarray as $censor) {
				list($newfind, $newreplace) = array_map('trim', explode('=', $censor));
				$newreplace = $newreplace <> '' ? daddslashes(str_replace("\\\'", '\'', $newreplace), 1) : '**';
				if(strlen($newfind) < 3) {
					if($newfind != '') {
						$ignorecount ++;
					}
					continue;
				} elseif(isset($oldwords[md5($newfind)])) {
					if($_G['gp_overwrite'] && ($_G['adminid'] == 1 || $oldwords[md5($newfind)] == $_G['member']['username'])) {
						$updatecount ++;
						DB::update('common_word', array(
							'replacement' => $newreplace,
							'type' => ($word_type ? $word_type : (intval($_G['gp_wordtype_select']) ? intval($_G['gp_wordtype_select']) : 0))
						), "`find`='$newfind'");
					} else {
						$ignorecount ++;
					}
				} else {
					$newcount ++;
					DB::insert('common_word', array(
						'admin' => $_G['username'],
						'find' => $newfind,
						'replacement' => $newreplace,
						'type' => ($word_type ? $word_type : (intval($_G['gp_wordtype_select']) ? intval($_G['gp_wordtype_select']) : 0))
					));
					$oldwords[md5($newfind)] = $_G['member']['username'];
				}
			}

		}


		updatecache('censor');
		cpmsg('censor_batch_add_succeed', "action=misc&operation=censor&anchor=import", 'succeed', array('newcount' => $newcount, 'updatecount' => $updatecount, 'ignorecount' => $ignorecount));

	} elseif(submitcheck('wordtypesubmit')) {
		if(is_array($_G['gp_delete'])) {
			$del_ids = dimplode($_G['gp_delete']);
			DB::query("DELETE FROM ".DB::table('common_word_type')." WHERE id IN (".$del_ids.")");
			DB::query("UPDATE ".DB::table('common_word')." SET type = 0 WHERE type IN (".$del_ids.")");
		}
		if(is_array($_G['gp_typename'])) {
			foreach($_G['gp_typename'] AS $key => $val) {
				if(!$_G['gp_delete'][$key] && !empty($val)) {
					DB::update("common_word_type", array('typename' => $val), "`id` = '$key'");
				}
			}
		}
		if($_G['gp_newtypename']) {
			foreach($_G['gp_newtypename'] AS $key => $val) {
				$val = trim($val);
				if(!empty($val)) {
					$sqladd .= $sqladd ? ",('{$val}')" : "('{$val}')";
				}
			}
			if($sqladd) {
				DB::query("INSERT INTO ".DB::table('common_word_type')."(typename)VALUES $sqladd");
			}
		}
		cpmsg('censor_wordtype_edit', 'action=misc&operation=censor&anchor=wordtype', 'succeed');
	} elseif(!submitcheck('censorsubmit')) {
		$sqladd = '1';
		if(!empty($_G['gp_censor_search_type'])) {
			$sqladd .= " AND type = '{$_G['gp_censor_search_type']}'";
		}

		if(!empty($_G['gp_censorkeyword'])) {
			$censorkeyword = str_replace(array('%', '_'), array('\%', '\_'), $_G['gp_censorkeyword']);
			$sqladd .= " AND find LIKE '%{$censorkeyword}%'";
			if($_POST['censorkeyword']) {
				$page = 1;
			}
		}

		$ppp = 50;
		$startlimit = ($page - 1) * $ppp;

		$query = DB::query("SELECT * FROM ".DB::table('common_word_type'));
		while($result = DB::fetch($query)) {
			$result['typename'] = dhtmlspecialchars($result['typename']);
			$word_type[$result['id']] = $result;
			$word_type_option .= "<option value=\"{$result['id']}\">{$result['typename']}</option>";
			if(!empty($_G['gp_censor_search_type'])) {
				$word_type_option_search .= "<option value=\"{$result['id']}\"".($_G['gp_censor_search_type'] == $result['id'] ? 'selected' : '' ).">{$result['typename']}</option>";
			}
		}

		shownav('topic', 'nav_posting_censor');
		$anchor = in_array($_G['gp_anchor'], array('list', 'import', 'wordtype', 'showanchor')) ? $_G['gp_anchor'] : 'list';
		showsubmenuanchors('nav_posting_censor', array(
			array('admin', 'list', $anchor == 'list'),
			array('misc_censor_batch_add', 'import', $anchor == 'import'),
			array('misc_censor_wordtype_edit', 'wordtype', $anchor == 'wordtype'),
		));
		showtips('misc_censor_tips', 'list_tips', $anchor == 'list');
		showtips('misc_censor_batch_add_tips', 'import_tips', $anchor == 'import');
		showtips('misc_censor_wordtype_tips', 'wordtype_tips', $anchor == 'wordtype');

		showtagheader('div', 'list', $anchor == 'list');
		showformheader("misc&operation=censor&page=$page", '', 'keywordsearch');
		showtableheader();
		echo '<br /><br /><form method="post">'. $lang['keywords'].': <input type="text" name="censorkeyword" value="'.$_G['gp_censorkeyword'].'" /> &nbsp; <select name="censor_search_type"><option value = "">'.cplang("misc_censor_wordtype_search").'</option><option value="0">'.cplang('misc_censor_word_default_typename').'</option>'.($word_type_option_search ? $word_type_option_search : $word_type_option).'</select> &nbsp;<input type="submit" name="censor_search" value="'.$lang[search].'" class="btn" /> </form>';
		showtablefooter();

		showformheader("misc&operation=censor&page=$page", '', 'listform');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'misc_censor_word', 'misc_censor_replacement', 'misc_censor_type', 'operator'));

		$multipage = '';
		$totalcount = DB::result_first("SELECT count(*) FROM ".DB::table('common_word')." WHERE $sqladd");
		if($totalcount) {
			$multipage = multi($totalcount, $ppp, $page, ADMINSCRIPT."?action=misc&operation=censor".($censorkeyword ? "&censorkeyword=".$censorkeyword : '' ));
			$query = DB::query("SELECT * FROM ".DB::table('common_word')." WHERE $sqladd ORDER BY find ASC LIMIT $startlimit, $ppp");
			while($censor =	DB::fetch($query)) {
				$censor['replacement'] = dstripslashes($censor['replacement']);
				$censor['replacement'] = dhtmlspecialchars($censor['replacement']);
				$censor['find'] = dhtmlspecialchars($censor['find']);
				$disabled = $_G['adminid'] != 1 && $censor['admin'] != $_G['member']['username'] ? 'disabled' : NULL;
				if(in_array($censor['replacement'], array('{BANNED}', '{MOD}'))) {
					$replacedisplay = 'style="display:none"';
					$optionselected = array();
					foreach(array('{BANNED}', '{MOD}') as $option) {
						$optionselected[$option] = $censor['replacement'] == $option ? 'selected' : '';
					}
				} else {
					$optionselected['{REPLACE}'] = 'selected';
					$replacedisplay = '';
				}
				$word_type_tmp = "<select name='wordtype_select[{$censor['id']}]' id='wordtype_select'><option value='0'>".cplang('misc_censor_word_default_typename')."</option>";
				foreach($word_type AS $key => $val) {
					if($censor['type'] == $val['id']) {
						$word_type_tmp .= "<option value='{$val['id']}' selected>{$val['typename']}</option>";
					} else {
						$word_type_tmp .= "<option value='{$val['id']}'>{$val['typename']}</option>";
					}
				}
				$word_type_tmp .= "</select>";
				showtablerow('', array('class="td25"', '', '', 'class="td26"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled>",
					"<input type=\"text\" class=\"txt\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled>",
					'<select name="replace['.$censor['id'].']" onchange="if(this.options[this.options.selectedIndex].value==\'{REPLACE}\'){$(\'divbanned'.$censor['id'].'\').style.display=\'\';$(\'divbanned'.$censor['id'].'\').value=\'\';}else{$(\'divbanned'.$censor['id'].'\').style.display=\'none\';}" '.$disabled.'>
					<option value="{BANNED}" '.$optionselected['{BANNED}'].'>'.cplang('misc_censor_word_banned').'</option><option value="{MOD}" '.$optionselected['{MOD}'].'>'.cplang('misc_censor_word_moderated').'</option><option value="{REPLACE}" '.$optionselected['{REPLACE}'].'>'.cplang('misc_censor_word_replaced').'</option></select>
					<input class="txt" type="text" size="10" name="replacecontent['.$censor['id'].']" value="'.$censor['replacement'].'" id="divbanned'.$censor['id'].'" '.$replacedisplay.' '.$disabled.'>',
					$word_type_tmp,
					$censor['admin']
				));
			}
		}
		$misc_censor_word_banned = cplang('misc_censor_word_banned');
		$misc_censor_word_moderated = cplang('misc_censor_word_moderated');
		$misc_censor_word_replaced = cplang('misc_censor_word_replaced');
		$misc_censor_word_newtypename = cplang('misc_censor_word_newtypename');
		$misc_censor_word_default_typename = cplang('misc_censor_word_default_typename');
		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,''],
			[1,'<input type="text" class="txt" size="30" name="newfind[]">'], [1, ' <select onchange="if(this.options[this.options.selectedIndex].value==\'{REPLACE}\'){this.nextSibling.style.display=\'\';}else{this.nextSibling.style.display=\'none\';}" name="newreplace[]" $disabled><option value="{BANNED}">$misc_censor_word_banned</option><option value="{MOD}">$misc_censor_word_moderated</option><option value="{REPLACE}">$misc_censor_word_replaced</option></select><input class="txt" type="text" size="15" name="newreplacecontent[]" style="display:none;">']
EOT;
		if($word_type_option) {
			echo ", [1,' <select onchange=\"if(this.options[this.options.selectedIndex].value==\'0\'){this.nextSibling.style.display=\'\';}else{this.nextSibling.style.display=\'none\';}\" name=\"newwordtype[]\" id=\"newwordtype[]\"><option value=\"0\" selected>{$misc_censor_word_default_typename}</option>{$word_type_option}</select><input class=\"txt\" type=\"text\" size=\"10\" name=\"newtypename[]\" >']";
		}
echo <<<EOT
			, [1,'']
		],
		[
			[1,''],
			[1,'<input type="text" class="txt" size="30" name="newtypename[]">']
		]
	];
	</script>
EOT;
		echo '<tr><td></td><td colspan="4"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';

		showsubmit('censorsubmit', 'submit', 'del', '', $multipage, false);
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'import', $anchor == 'import');
		showformheader("misc&operation=censor&page=$page", 'fixpadding');
		showtableheader('', 'fixpadding', 'importform');
		showtablerow('', 'class="vtop rowform"', "<select name=\"wordtype_select\"><option value='0'>".cplang('misc_censor_word_default_typename')."</option>$word_type_option</select>");
		showtablerow('', 'class="vtop rowform"', '<br /><textarea name="addcensors" class="tarea" rows="10" cols="80"></textarea><br /><br />'.mradio('overwrite', array(
				0 => cplang('misc_censor_batch_add_no_overwrite'),
				1 => cplang('misc_censor_batch_add_overwrite'),
				2 => cplang('misc_censor_batch_add_clear')
		), '', FALSE));

		showsubmit('addcensorsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');


		showtagheader('div', 'wordtype', $anchor == 'wordtype');
		showformheader("misc&operation=censor", 'fixpadding');
		showtableheader('', 'fixpadding', 'wordtypeform');
		showsubtitle(array('', 'misc_censor_wordtype_name'));
		if($wordtypecount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_word_type'))) {
			$query = DB::query("SELECT * FROM ".DB::table('common_word_type'));
			while($result = DB::fetch($query)) {
				showtablerow('', array('class="td25"', ''), array("<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$result['id']}\" $disabled>", "<input type=\"text\" class=\"txt\" size=\"10\" name=\"typename[{$result['id']}]\" value=\"{$result['typename']}\">"));
			}
		}

		echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';
		showsubmit('wordtypesubmit', 'submit', 'del', '', '', false);
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		if($ids = dimplode($_G['gp_delete'])) {
			DB::delete('common_word', "id IN ($ids) AND ('{$_G['adminid']}'='1' OR admin='{$_G['username']}')");
		}

		if(is_array($_G['gp_find'])) {
			foreach($_G['gp_find'] as $id => $val) {
				$_G['gp_find'][$id]  = $val = trim(str_replace('=', '', $_G['gp_find'][$id]));
				if(strlen($val) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				$_G['gp_replace'][$id] = $_G['gp_replace'][$id] == '{REPLACE}' ? $_G['gp_replacecontent'][$id] : $_G['gp_replace'][$id];
				$_G['gp_replace'][$id] = daddslashes(str_replace("\\\'", '\'', $_G['gp_replace'][$id]), 1);
				DB::update('common_word', array(
					'find' => $_G['gp_find'][$id],
					'replacement' => $_G['gp_replace'][$id],
					'type' => $_G['gp_wordtype_select'][$id],
				), "id='$id' AND ('{$_G['adminid']}'='1' OR admin='{$_G['username']}')");
			}
		}

		$newfind_array = !empty($_G['gp_newfind']) ? $_G['gp_newfind'] : array();
		$newreplace_array = !empty($_G['gp_newreplace']) ? $_G['gp_newreplace'] : array();
		$newreplacecontent_array = !empty($_G['gp_newreplacecontent']) ? $_G['gp_newreplacecontent'] : array();
		$newwordtype = !empty($_G['gp_newwordtype']) ? $_G['gp_newwordtype'] : array();
		$newtypename = !empty($_G['gp_newtypename']) ? $_G['gp_newtypename'] : array();

		foreach($newfind_array as $key => $value) {
			$newfind = trim(str_replace('=', '', $newfind_array[$key]));
			$newreplace  = trim($newreplace_array[$key]);

			if($newfind != '') {
				if(strlen($newfind) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				if($newreplace == '{REPLACE}') {
					$newreplace = daddslashes(str_replace("\\\'", '\'', $newreplacecontent_array[$key]), 1);
				}

				if($newtypename) {
					$newtypename = daddslashes($newtypename);
				}

				if($newwordtype) {
					$newwordtype[$key] = intval($newwordtype[$key]);
				}

				if($newwordtype[$key] == 0) {
					if(!empty($newtypename[$key])) {
						$newtypename[$key] = daddslashes($newtypename[$key]);
						$newwordtype[$key] = DB::insert('common_word_type', array(
							'typename' => $newtypename[$key]
						), true);
					}
				}

				if($oldcenser = DB::fetch_first("SELECT admin FROM ".DB::table('common_word')." WHERE find='$newfind'")) {
					cpmsg('censor_keywords_existence', '', 'error');
				} else {
					DB::insert('common_word', array(
						'admin' => $_G['username'],
						'find' => $newfind,
						'replacement' => $newreplace,
						'type' => $newwordtype[$key],
					));
				}
			}
		}

		updatecache('censor');
		cpmsg('censor_succeed', "action=misc&operation=censor&page=$page", 'succeed');

	}

} elseif($operation == 'stamp') {

	if(!submitcheck('stampsubmit')) {

		$anchor = in_array($_G['gp_anchor'], array('list', 'llist', 'add')) ? $_G['gp_anchor'] : 'list';
		shownav('style', 'nav_thread_stamp');
		showsubmenuanchors('nav_thread_stamp', array(
			array('misc_stamp_thread', 'list', $anchor == 'list'),
			array('misc_stamp_list', 'llist', $anchor == 'llist'),
			array('add', 'add', $anchor == 'add')
		));

		showtagheader('div', 'list', $anchor == 'list');
		showtips('misc_stamp_listtips');
		showformheader('misc&operation=stamp');
		showhiddenfields(array('anchor' => 'list'));
		showtableheader();
		showsubtitle(array('', 'misc_stamp_id', 'misc_stamp_name', 'smilies_edit_image', 'smilies_edit_filename', 'misc_stamp_icon', 'misc_stamp_option'));

		$imgfilter = $stamplist = $stamplistfiles = $stampicons = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_smiley')." WHERE type='stamplist' ORDER BY displayorder");
		while($smiley =	DB::fetch($query)) {
			$stamplistfiles[$smiley['url']] = $smiley['id'];
			$stampicons[$smiley['url']] = $smiley['typeid'];
			$stamplist[] = $smiley;
		}
		$tselect = '<select><option value="0">'.cplang('none').'</option><option value="1">'.cplang('misc_stamp_option_stick').'</option><option value="2">'.cplang('misc_stamp_option_digest').'</option><option value="3">'.cplang('misc_stamp_option_recommend').'</option><option value="4">'.cplang('misc_stamp_option_recommendto').'</option></select>';
		$query = DB::query("SELECT * FROM ".DB::table('common_smiley')." WHERE type='stamp' ORDER BY displayorder");
		while($smiley =	DB::fetch($query)) {
			$s = $r = array();
			$s[] = '<select>';
			$r[] = '<select name="typeidnew['.$smiley['id'].']">';
			if($smiley['typeid']) {
				$s[] = '<option value="'.$smiley['typeid'].'">';
				$r[] = '<option value="'.$smiley['typeid'].'" selected="selected">';
				$s[] = '<option value="0">';
				$r[] = '<option value="-1">';
			}
			$tselectrow = str_replace($s, $r, $tselect);
			$dot = strrpos($smiley['url'], '.');
			$fn = substr($smiley['url'], 0, $dot);
			$ext = substr($smiley['url'], $dot + 1);
			$stampicon = $fn.'.small.'.$ext;
			$small = array_key_exists($stampicon, $stamplistfiles);
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"code[$smiley[id]]\" value=\"$smiley[code]\">",
				"<img src=\"static/image/stamp/$smiley[url]\">",
				$smiley['url'],
				($small ? '<input class="checkbox" type="checkbox" name="stampicon['.$smiley['id'].']"'.($smiley['id'] == $stampicons[$stampicon] ? ' checked="checked"' : '').' value="'.$stamplistfiles[$stampicon].'" /><img class="vmiddle" src="static/image/stamp/'.$stampicon.'">': ''),
				$tselectrow,
			));
			$imgfilter[] = $smiley['url'];
		}

		showsubmit('stampsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'llist', $anchor == 'llist');
		showtips('misc_stamp_listtips');
		showformheader('misc&operation=stamp&type=list');
		showhiddenfields(array('anchor' => 'llist'));
		showtableheader();
		showsubtitle(array('', 'misc_stamp_id', 'misc_stamp_listname', 'smilies_edit_image', 'smilies_edit_filename'));

		foreach($stamplist as $smiley) {
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"code[$smiley[id]]\" value=\"$smiley[code]\">",
				"<img src=\"static/image/stamp/$smiley[url]\">",
				$smiley['url']
			));
			$imgfilter[] = $smiley['url'];
		}

		showsubmit('stampsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'add', $anchor == 'add');
		showformheader('misc&operation=stamp');
		showtips('misc_stamp_addtips');
		showtableheader();
		showsubtitle(array('add', 'misc_stamp_type', 'misc_stamp_id', 'misc_stamp_imagename', 'smilies_edit_image', 'smilies_edit_filename'));

		$newid = 0;
		$imgextarray = array('png', 'gif');
		$stampsdir = dir(DISCUZ_ROOT.'./static/image/stamp');
		while($entry = $stampsdir->read()) {
			if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && is_file(DISCUZ_ROOT.'./static/image/stamp/'.$entry)) {
				showtablerow('', array('class="td25"', 'class="td28 td24 rowform"', 'class="td23"'), array(
					"<input type=\"checkbox\" name=\"addcheck[$newid]\" id=\"addcheck_$newid\" class=\"checkbox\">",
					"<ul onmouseover=\"altStyle(this);\">".
					"<li class=\"checked\"><input type=\"radio\" name=\"addtype[$newid]\" value=\"0\" checked=\"checked\" class=\"radio\">".cplang('misc_stamp_thread')."</li>".
					"<li><input type=\"radio\" name=\"addtype[$newid]\" value=\"1\" class=\"radio\" onclick=\"$('addcheck_$newid').checked='true'\">".cplang('misc_stamp_list')."</li>".
					"</ul>",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"adddisplayorder[$newid]\" value=\"0\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"addcode[$newid]\" value=\"\">",
					"<img src=\"static/image/stamp/$entry\" />",
					"<input type=\"hidden\" class=\"txt\" size=\"35\" name=\"addurl[$newid]\" value=\"$entry\">$entry"
				));
				$newid ++;
			}
		}
		$stampsdir->close();
		if(!$newid) {
			showtablerow('', array('class="td25"', 'colspan="3"'), array('', cplang('misc_stamp_tips')));
		} else {
			showsubmit('stampsubmit', 'submit', '<input type="checkbox" class="checkbox" name="chkall2" id="chkall2" onclick="checkAll(\'prefix\', this.form, \'addcheck\', \'chkall2\')"><label for="chkall2">'.cplang('select_all').'</label>');
		}

		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		if($ids = dimplode($_G['gp_delete'])) {
			DB::delete('common_smiley', "id IN ($ids)");
		}

		if(is_array($_G['gp_displayorder'])) {
			$typeidset = array();
			foreach($_G['gp_displayorder'] as $id => $val) {
				$_G['gp_displayorder'][$id] = intval($_G['gp_displayorder'][$id]);
				if($_G['gp_displayorder'][$id] >= 0 && $_G['gp_displayorder'][$id] < 100) {
					$typeidadd = '';
					if($_G['gp_typeidnew'][$id]) {
						if(!isset($typeidset[$_G['gp_typeidnew'][$id]])) {
							$_G['gp_typeidnew'][$id] = $_G['gp_typeidnew'][$id] > 0 ? $_G['gp_typeidnew'][$id] : 0;
							$typeidadd = ",typeid='{$_G['gp_typeidnew'][$id]}'";
							$typeidset[$_G['gp_typeidnew'][$id]] = TRUE;
						} else {
							$_G['gp_typeidnew'][$id] = 0;
						}
					}
					DB::update('common_smiley', array(
						'displayorder' => $_G['gp_displayorder'][$id],
						'code' => $_G['gp_code'][$id],
						'typeid' => $_G['gp_typeidnew'][$id],
					), "id='$id'");
				}
			}
		}

		if(is_array($_G['gp_addurl'])) {
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_smiley')." WHERE type IN ('stamp','stamplist')");
			if($count < 100) {
				foreach($_G['gp_addurl'] as $k => $v) {
					if($_G['gp_addcheck'][$k] && $_G['gp_addcode'][$k]) {
						$count++;

						DB::insert('common_smiley', array(
							'displayorder' => '0',
							'type' => (!$_G['gp_addtype'][$k] ? 'stamp' : 'stamplist'),
							'url' => $_G['gp_addurl'][$k],
							'code' => $_G['gp_addcode'][$k],
						));
					}
				}
			}
		}

		DB::update('common_smiley', array('typeid' => 0), "type='stamplist'");
		if(is_array($_G['gp_stampicon'])) {
			foreach($_G['gp_stampicon'] as $k => $v) {
				if($_G['gp_typeidnew'][$k]) {
					$k = 0;
				}
				DB::update('common_smiley', array('typeid' => $k), "id='$v' AND type='stamplist'");
			}
		}

		updatecache('stamps');
		updatecache('stamptypeid');

		cpmsg('thread_stamp_succeed', "action=misc&operation=stamp&anchor=$_G[gp_anchor]", 'succeed');
	}

} elseif($operation == 'attachtype') {

	if(!submitcheck('typesubmit')) {

		$attachtypes = '';
		$query = DB::query("SELECT * FROM ".DB::table('forum_attachtype'));
		while($type = DB::fetch($query)) {
			$type['maxsize'] = round($type['maxsize'] / 1024);
			$attachtypes .= showtablerow('', array('class="td25"', 'class="td24"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\" />",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\" />",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\" />"
			), TRUE);
		}

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input name="newextension[]" type="text" class="txt" size="10">', 'td24'],
		[1,'<input name="newmaxsize[]" type="text" class="txt" size="15">']
	]
];
</script>
<?php

		shownav('global', 'nav_posting_attachtype');
		showsubmenu('nav_posting_attachtype');
		showtips('misc_attachtype_tips');
		showformheader('misc&operation=attachtype');
		showtableheader();
		showtablerow('class="partition"', array('class="td25"'), array('', cplang('misc_attachtype_ext'), cplang('misc_attachtype_maxsize')));
		echo $attachtypes;
		echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['misc_attachtype_add'].'</a></div></tr>';
		showsubmit('typesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_G['gp_delete'])) {
			DB::delete('forum_attachtype', "id IN ($ids)");
		}

		if(is_array($_G['gp_extension'])) {
			foreach($_G['gp_extension'] as $id => $val) {
				DB::update('forum_attachtype', array(
					'extension' => $_G['gp_extension'][$id],
					'maxsize' => $_G['gp_maxsize'][$id] * 1024,
				), "id='$id'");
			}
		}

		if(is_array($_G['gp_newextension'])) {
			foreach($_G['gp_newextension'] as $key => $value) {
				if($newextension1 = trim($value)) {
					if(DB::result_first("SELECT id FROM ".DB::table('forum_attachtype')." WHERE extension='$newextension1'")) {
						cpmsg('attachtypes_duplicate', '', 'error');
					}
					DB::insert('forum_attachtype', array(
						'extension' => $newextension1,
						'maxsize' => $_G['gp_newmaxsize'][$key] * 1024,
					));
				}
			}
		}

		cpmsg('attachtypes_succeed', 'action=misc&operation=attachtype', 'succeed');

	}

} elseif($operation == 'cron') {

	if(empty($_G['gp_edit']) && empty($_G['gp_run'])) {

		if(!submitcheck('cronssubmit')) {

			shownav('tools', 'misc_cron');
			showsubmenu('nav_misc_cron');
			showtips('misc_cron_tips');
			showformheader('misc&operation=cron');
			showtableheader('', 'fixpadding');
			showsubtitle(array('', 'name', 'available', 'type', 'time', 'misc_cron_last_run', 'misc_cron_next_run', ''));

			$query = DB::query("SELECT * FROM ".DB::table('common_cron')." ORDER BY type DESC");
			while($cron = DB::fetch($query)) {
				$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';

				if($cron['day'] > 0 && $cron['day'] < 32) {
					$cron['time'] = cplang('misc_cron_permonth').$cron['day'].cplang('misc_cron_day');
				} elseif($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
					$cron['time'] = cplang('misc_cron_perweek').cplang('misc_cron_week_day_'.$cron['weekday']);
				} elseif($cron['hour'] >= 0 && $cron['hour'] < 24) {
					$cron['time'] = cplang('misc_cron_perday');
				} else {
					$cron['time'] = cplang('misc_cron_perhour');
				}

				$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron[hour]).cplang('misc_cron_hour') : '';

				if(!in_array($cron['minute'], array(-1, ''))) {
					foreach($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
						$cron['minute'][$k] = sprintf('%02d', $v);
					}
					$cron['minute'] = implode(',', $cron['minute']);
					$cron['time'] .= $cron['minute'].cplang('misc_cron_minute');
				} else {
					$cron['time'] .= '00'.cplang('misc_cron_minute');
				}

				$cron['lastrun'] = $cron['lastrun'] ? dgmdate($cron['lastrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';
				$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_G['setting']['timeoffset'] * 3600 < TIMESTAMP ? 'style="color: #ff0000"' : '';
				$cron['nextrun'] = $cron['nextrun'] ? dgmdate($cron['nextrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';

				showtablerow('', array('class="td25"', 'class="crons"', 'class="td25"', 'class="td25"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td25"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$cron[cronid]\" ".($cron['type'] == 'system' ? 'disabled' : '').">",
					"<input type=\"text\" class=\"txt\" name=\"namenew[$cron[cronid]]\" size=\"20\" value=\"$cron[name]\"><br /><b>$cron[filename]</b>",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$cron[cronid]]\" value=\"1\" ".($cron['available'] ? 'checked' : '')." $disabled>",
					cplang($cron['type'] == 'system' ? 'inbuilt' : 'custom'),
					$cron[time],
					$cron[lastrun],
					$cron[nextrun],
					"<a href=\"".ADMINSCRIPT."?action=misc&operation=cron&edit=$cron[cronid]\" class=\"act\">$lang[edit]</a><br />".
					($cron['available'] ? " <a href=\"".ADMINSCRIPT."?action=misc&operation=cron&run=$cron[cronid]\" class=\"act\">$lang[misc_cron_run]</a>" : " <a href=\"###\" class=\"act\" disabled>$lang[misc_cron_run]</a>")
				));
			}

			showtablerow('', array('','colspan="10"'), array(
				cplang('add_new'),
				'<input type="text" class="txt" name="newname" value="" size="20" />'
			));
			showsubmit('cronssubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {

			if($ids = dimplode($_G['gp_delete'])) {
				DB::delete('common_cron', "cronid IN ($ids) AND type='user'");
			}

			if(is_array($_G['gp_namenew'])) {
				foreach($_G['gp_namenew'] as $id => $name) {
					$newcron = array(
						'name' => dhtmlspecialchars($_G['gp_namenew'][$id]),
						'available' => $_G['gp_availablenew'][$id]
					);
					if(empty($_G['gp_availablenew'][$id])) {
						$newcron['nextrun'] = '0';
					}
					DB::update('common_cron', $newcron, "cronid='$id'");
				}
			}

			if($newname = trim($_G['gp_newname'])) {
				DB::insert('common_cron', array(
					'name' => dhtmlspecialchars($newname),
					'type' => 'user',
					'available' => '0',
					'weekday' => '-1',
					'day' => '-1',
					'hour' => '-1',
					'minute' => '',
					'nextrun' => $_G['timestamp'],
				));
			}

			$query = DB::query("SELECT cronid, filename FROM ".DB::table('common_cron'));
			while($cron = DB::fetch($query)) {
				if(!file_exists(DISCUZ_ROOT.'./source/include/cron/'.$cron['filename'])) {
					DB::update('common_cron', array(
						'available' => '0',
						'nextrun' => '0',
					), "cronid='$cron[cronid]'");
				}
			}

			updatecache('setting');
			cpmsg('crons_succeed', 'action=misc&operation=cron', 'succeed');

		}

	} else {

		$cronid = empty($_G['gp_run']) ? $_G['gp_edit'] : $_G['gp_run'];
		$cron = DB::fetch_first("SELECT * FROM ".DB::table('common_cron')." WHERE cronid='$cronid'");
		if(!$cron) {
			cpmsg('cron_not_found', '', 'error');
		}
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cronminute = str_replace("\t", ',', $cron['minute']);
		$cron['minute'] = explode("\t", $cron['minute']);

		if(!empty($_G['gp_edit'])) {

			if(!submitcheck('editsubmit')) {

				shownav('tools', 'misc_cron');
				showsubmenu($lang['misc_cron_edit'].' - '.$cron['name']);
				showtips('misc_cron_edit_tips');

				$weekdayselect = $dayselect = $hourselect = '';

				for($i = 0; $i <= 6; $i++) {
					$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').">".$lang['misc_cron_week_day_'.$i]."</option>";
				}

				for($i = 1; $i <= 31; $i++) {
					$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i $lang[misc_cron_day]</option>";
				}

				for($i = 0; $i <= 23; $i++) {
					$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i $lang[misc_cron_hour]</option>";
				}

				shownav('tools', 'misc_cron');
				showformheader("misc&operation=cron&edit=$cronid");
				showtableheader();
				showsetting('misc_cron_edit_weekday', '', '', "<select name=\"weekdaynew\"><option value=\"-1\">*</option>$weekdayselect</select>");
				showsetting('misc_cron_edit_day', '', '', "<select name=\"daynew\"><option value=\"-1\">*</option>$dayselect</select>");
				showsetting('misc_cron_edit_hour', '', '', "<select name=\"hournew\"><option value=\"-1\">*</option>$hourselect</select>");
				showsetting('misc_cron_edit_minute', 'minutenew', $cronminute, 'text');
				showsetting('misc_cron_edit_filename', 'filenamenew', $cron['filename'], 'text');
				showsubmit('editsubmit');
				showtablefooter();
				showformfooter();

			} else {

				$daynew = $_G['gp_weekdaynew'] != -1 ? -1 : $_G['gp_daynew'];
				if(strpos($_G['gp_minutenew'], ',') !== FALSE) {
					$minutenew = explode(',', $_G['gp_minutenew']);
					foreach($minutenew as $key => $val) {
						$minutenew[$key] = $val = intval($val);
						if($val < 0 || $var > 59) {
							unset($minutenew[$key]);
						}
					}
					$minutenew = array_slice(array_unique($minutenew), 0, 12);
					$minutenew = implode("\t", $minutenew);
				} else {
					$minutenew = intval($_G['gp_minutenew']);
					$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
				}

				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_G['gp_filenamenew'])) {
					cpmsg('crons_filename_illegal', '', 'error');
				} elseif(!is_readable(DISCUZ_ROOT.($cronfile = "./source/include/cron/{$_G['gp_filenamenew']}"))) {
					cpmsg('crons_filename_invalid', '', 'error', array('cronfile' => $cronfile));
				} elseif($_G['gp_weekdaynew'] == -1 && $daynew == -1 && $_G['gp_hournew'] == -1 && $minutenew === '') {
					cpmsg('crons_time_invalid', '', 'error');
				}

				DB::update('common_cron', array(
					'weekday' => $_G['gp_weekdaynew'],
					'day' => $daynew,
					'hour' => $_G['gp_hournew'],
					'minute' => $minutenew,
					'filename' => trim($_G['gp_filenamenew']),
				), "cronid='$cronid'");

				updatecache('crons');
				require_once libfile('class/cron');
				discuz_cron::run($cronid);

				cpmsg('crons_succeed', 'action=misc&operation=cron', 'succeed');

			}

		} else {

			if(!file_exists(DISCUZ_ROOT.($cronfile = "./source/include/cron/$cron[filename]"))) {
				cpmsg('crons_run_invalid', '', 'error', array('cronfile' => $cronfile));
			} else {
				require_once libfile('class/cron');
				discuz_cron::run($cron['cronid']);
				cpmsg('crons_run_succeed', 'action=misc&operation=cron', 'succeed');
			}

		}

	}

} elseif($operation == 'focus') {

	require_once libfile('function/post');

	$focus = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='focus'");
	$focus = unserialize($focus);

	$focus_position_array = array(
		array('portal', cplang('misc_focus_position_portal')),
		array('home', cplang('misc_focus_position_home')),
		array('member', cplang('misc_focus_position_member')),
		array('forum', cplang('misc_focus_position_forum')),
		array('group', cplang('misc_focus_position_group')),
		array('search', cplang('misc_focus_position_search')),
		array('userapp', cplang('misc_focus_position_userapp')),
	);

	if(!$do) {

		if(!submitcheck('focussubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 0),
				array('admin', 'misc&operation=focus', 1),
				array('add', 'misc&operation=focus&do=add')
			));
			showtips('misc_focus_tips');
			showformheader('misc&operation=focus');
			showtableheader('admin', 'fixpadding');
			showsubtitle(array('', 'subject', 'available', ''));
			if(is_array($focus['data'])) {
				foreach($focus['data'] as $k => $v) {
					showtablerow('', array('class="td25"','', 'class="td25"', 'class="td25"'), array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$k\">",
						'<a href="'.$v['url'].'" target="_blank">'.$v[subject].'</a>',
						"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$k]\" value=\"1\" ".($v['available'] ? 'checked' : '').">",
						"<a href=\"".ADMINSCRIPT."?action=misc&operation=focus&do=edit&id=$k\" class=\"act\">$lang[edit]</a>",
					));
				}
			}

			showsubmit('focussubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {

			$newfocus = array();
			$newfocus['title'] = $focus['title'];
			$newfocus['data'] = array();
			if(isset($focus['data']) && is_array($focus['data'])) foreach($focus['data'] as $k => $v) {
				if(is_array($_G['gp_delete']) && in_array($k, $_G['gp_delete'])) {
					unset($focus['data'][$k]);
				} else {
					$v['available'] = $_G['gp_available'][$k] ? 1 : 0;
					$newfocus['data'][$k] = $v;
				}
			}
			$newfocus['cookie'] = $focus['cookie'];
			DB::insert('common_setting', array(
				'skey' => 'focus',
				'svalue' => addslashes(serialize(dstripslashes($newfocus))),
			), false, true);
			updatecache(array('setting', 'focus'));

			cpmsg('focus_update_succeed', 'action=misc&operation=focus', 'succeed');

		}

	} elseif($do == 'add') {

		if(count($focus['data']) >= 10) {
			cpmsg('focus_add_num_limit', 'action=misc&operation=focus', 'error');
		}

		if(!submitcheck('addsubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 0),
				array('admin', 'misc&operation=focus', 0),
				array('add', 'misc&operation=focus&do=add', 1)
			));
			showformheader('misc&operation=focus&do=add');
			showtableheader('misc_focus_handadd', 'fixpadding');
			showsetting('misc_focus_handurl', 'focus_url', '', 'text');
			showsetting('misc_focus_handsubject' , 'focus_subject', '', 'text');
			showsetting('misc_focus_handsummary', 'focus_summary', '', 'textarea');
			showsetting('misc_focus_handimg', 'focus_image', '', 'text');

			showsetting('misc_focus_position', array('focus_position', $focus_position_array), '', 'mcheckbox');
			showsubmit('addsubmit', 'submit', '', '');
			showtablefooter();
			showformfooter();

		} else {

			if($_G['gp_focus_url'] && $_G['gp_focus_subject'] && $_G['gp_focus_summary']) {

				if(is_array($focus['data'])) {
					foreach($focus['data'] as $item) {
						if($item['url'] == $_G['gp_focus_url']) {
							cpmsg('focus_topic_exists', 'action=misc&operation=focus', 'error');
						}
					}
				}
				$focus['data'][] = array(
					'url' => $_G['gp_focus_url'],
					'available' => '1',
					'subject' => cutstr($_G['gp_focus_subject'], 80),
					'summary' => $_G['gp_focus_summary'],
					'image' => $_G['gp_focus_image'],
					'aid' => 0,
					'filename' => basename($_G['gp_focus_image']),
					'position' => $_G['gp_focus_position'],
				);
				DB::insert('common_setting', array(
					'skey' => 'focus',
					'svalue' => addslashes(serialize(dstripslashes($focus))),
				), false, true);
				updatecache(array('setting', 'focus'));
			} else {
				cpmsg('focus_topic_addrequired', '', 'error');
			}

			cpmsg('focus_add_succeed', 'action=misc&operation=focus', 'succeed');

		}

	} elseif($do == 'edit') {
		$id = intval($_G['gp_id']);
		if(!$item = $focus['data'][$id]) {
			cpmsg('focus_topic_noexists', 'action=misc&operation=focus', 'error');
		}
		if(!submitcheck('editsubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 0),
				array('admin', 'misc&operation=focus', 0),
				array('add', 'misc&operation=focus&do=add', 0)
			));

			showformheader('misc&operation=focus&do=edit&id='.$id);
			showtableheader('misc_focus_edit', 'fixpadding');
			showsetting('misc_focus_handurl', 'focus_url', $item['url'], 'text');
			showsetting('misc_focus_handsubject' , 'focus_subject', $item['subject'], 'text');
			showsetting('misc_focus_handsummary', 'focus_summary', $item['summary'], 'textarea');
			showsetting('misc_focus_handimg', 'focus_image', $item['image'], 'text');
			showsetting('misc_focus_position', array('focus_position', $focus_position_array), $item['position'], 'mcheckbox');

			showsubmit('editsubmit', 'submit');
			showtablefooter();
			showformfooter();

		} else {

			if($_G['gp_focus_url'] && $_G['gp_focus_subject'] && $_G['gp_focus_summary']) {
				if($item['type'] == 'thread') {
					$_G['gp_focus_url'] = $item['url'];
				} else {
					$focus_filename = basename($_G['gp_focus_image']);
				}
				$item = array(
					'url' => $_G['gp_focus_url'],
					'tid' => $item['tid'],
					'available' => '1',
					'subject' => cutstr($_G['gp_focus_subject'], 80),
					'summary' => $_G['gp_focus_summary'],
					'image' => $_G['gp_focus_image'],
					'aid' => 0,
					'filename' => $focus_filename,
					'position' => $_G['gp_focus_position'],
				);
				$focus['data'][$id] = $item;
				DB::insert('common_setting', array(
					'skey' => 'focus',
					'svalue' => addslashes(serialize(dstripslashes($focus)))
				), false, true);
				updatecache(array('setting', 'focus'));
			}

			cpmsg('focus_edit_succeed', 'action=misc&operation=focus', 'succeed');

		}

	} elseif($do == 'config') {

		if(!submitcheck('confsubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 1),
				array('admin', 'misc&operation=focus', 0),
				array('add', 'misc&operation=focus&do=add', 0)
			));
			showformheader('misc&operation=focus&do=config');
			showtableheader('config', 'fixpadding');
			showsetting('misc_focus_area_title', 'focus_title', empty($focus['title']) ? cplang('misc_focus') : $focus['title'], 'text');
			showsetting('misc_focus_area_cookie', 'focus_cookie', empty($focus['cookie']) ? 0 : $focus['cookie'], 'text');
			showsubmit('confsubmit', 'submit');
			showtablefooter();
			showformfooter();

		} else {

			$focus['title'] = trim($_G['gp_focus_title']);
			$focus['title'] = empty($focus['title']) ? cplang('misc_focus') : $focus['title'];
			$focus['cookie'] = trim(intval($_G['gp_focus_cookie']));
			$focus['cookie'] = empty($focus['cookie']) ? 0 : $focus['cookie'];
			DB::insert('common_setting', array(
				'skey' => 'focus',
				'svalue' => addslashes(serialize(dstripslashes($focus)))
			), false, true);
			updatecache(array('setting', 'focus'));

			cpmsg('focus_conf_succeed', 'action=misc&operation=focus&do=config', 'succeed');

		}

	}

} elseif($operation == 'checkstat') {
	if($statid && $statkey) {
		$q = "statid=$statid&statkey=$statkey";
		$q=rawurlencode(base64_encode($q));
		$url = 'http://stat.discuz.com/stat_ins.php?action=checkstat&q='.$q;
		$key = dfsockopen($url);
		$newstatdisable = $key == $statkey ? 0 : 1;
		if($newstatdisable != $statdisable) {
			DB::query("REPLACE ".DB::table('common_setting')." SET skey='statdisable', svalue='$newstatdisable'");
			require_once libfile('function/cache');
			updatecache('setting');
		}
	}
} elseif($operation == 'custommenu') {

	if(!$do) {

		if(!submitcheck('optionsubmit')) {
			$mpp = 10;
			$startlimit = ($page - 1) * $mpp;
			$num = DB::result_first("SELECT count(*) FROM ".DB::table('common_admincp_cmenu')." WHERE uid='$_G[uid]' AND sort='1'");
			$multipage = multi($num, $mpp, $page, ADMINSCRIPT.'?action=misc&operation=custommenu');
			$optionlist = $ajaxoptionlist = '';
			$query = DB::query("SELECT id, title, displayorder, url FROM ".DB::table('common_admincp_cmenu')." WHERE uid='$_G[uid]' AND sort='1' ORDER BY displayorder, dateline DESC, clicks DESC LIMIT $startlimit, $mpp");
			while($custom = DB::fetch($query)) {
				$custom['url'] = rawurldecode($custom['url']);
				$optionlist .= showtablerow('', array('class="td25"', 'class="td28"', '', 'class="td26"'), array(
					"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$custom[id]\">",
					"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayordernew[$custom[id]]\" value=\"$custom[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"25\" name=\"titlenew[$custom[id]]\" value=\"".cplang($custom['title'])."\"><input type=\"hidden\" name=\"langnew[$custom[id]]\" value=\"$custom[title]\">",
					"<input type=\"text\" class=\"txt\" size=\"40\" name=\"urlnew[$custom[id]]\" value=\"$custom[url]\">"
				), TRUE);
				$ajaxoptionlist .= '<li><a href="'.$custom['url'].'" target="'.(substr(rawurldecode($custom['url']), 0, 17) == ADMINSCRIPT.'?action=' ? 'main' : '_blank').'">'.cplang($custom['title']).'</a></li>';
			}

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'<input type="text" class="txt" name="newtitle[]" size="25">'],
			[1,'<input type="text" class="txt" name="newurl[]" size="40">', 'td26']
		]
	];
</script>
EOT;
			shownav('tools', 'nav_custommenu');
			showsubmenu('nav_custommenu');
			showformheader('misc&operation=custommenu');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'URL'));
			echo $optionlist;
			echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['custommenu_add'].'</a></div></td></tr>';
			showsubmit('optionsubmit', 'submit', 'del', '', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_admincp_cmenu')." WHERE id IN ($ids) AND uid='$_G[uid]'");
			}

			if(is_array($_G['gp_titlenew'])) {
				foreach($_G['gp_titlenew'] as $id => $title) {
					$_G['gp_urlnew'][$id] = rawurlencode($_G['gp_urlnew'][$id]);
					$title = dhtmlspecialchars($_G['gp_langnew'][$id] && lang($_G['gp_langnew'][$id], false) ? $_G['gp_langnew'][$id] : $title);
					$ordernew = intval($_G['gp_displayordernew'][$id]);
					DB::query("UPDATE ".DB::table('common_admincp_cmenu')." SET title='$title', displayorder='$ordernew', url='".dhtmlspecialchars($_G['gp_urlnew'][$id])."' WHERE id='$id'");
				}
			}

			if(is_array($_G['gp_newtitle'])) {
				foreach($_G['gp_newtitle'] as $k => $v) {
					$_G['gp_urlnew'][$k] = rawurlencode($_G['gp_urlnew'][$k]);
					DB::query("INSERT INTO ".DB::table('common_admincp_cmenu')." (title, displayorder, url, sort, uid) VALUES ('".dhtmlspecialchars($v)."', '".intval($_G['gp_newdisplayorder'][$k])."', '".dhtmlspecialchars($_G['gp_newurl'][$k])."', '1', '$_G[uid]')");
				}
			}

			updatemenu('index');
			cpmsg('custommenu_edit_succeed', 'action=misc&operation=custommenu', 'succeed');

		}

	} elseif($do == 'add') {

		if($_G['gp_title'] && $_G['gp_url']) {
			admincustom($_G['gp_title'], dhtmlspecialchars($_G['gp_url']), 1);
			updatemenu('index');
			cpmsg('custommenu_add_succeed', rawurldecode($_G['gp_url']), 'succeed', array('title' => cplang($_G['gp_title'])));
		} else {
			cpmsg('parameters_error', '', 'error');
		}
	}

}

?>