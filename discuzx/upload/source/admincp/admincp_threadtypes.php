<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threadtypes.php 22864 2011-05-27 03:04:15Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$classoptionmenu = array();
$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE classid='0' ORDER BY displayorder");
$curclassname = '';
while($option = DB::fetch($query)) {
	if($_G['gp_classid'] == $option['optionid']) {
		$curclassname = $option['title'];
	}
	$classoptionmenu[] = array($option['title'], "threadtypes&operation=typeoption&classid=$option[optionid]", $_G['gp_classid'] == $option['optionid']);
}
$mysql_keywords = array( 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONNECTION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'ENCLOSED', 'ESCAPED', 'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GOTO', 'GRANT', 'GROUP', 'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IS', 'ITERATE', 'JOIN', 'KEY', 'KEYS', 'KILL', 'LABEL', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MATCH', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NOT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMERIC', 'ON', 'OPTIMIZE', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE', 'RAID0', 'RANGE', 'READ', 'READS', 'REAL', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE', 'REQUIRE', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SSL', 'STARTING', 'STRAIGHT_JOIN', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'WHEN', 'WHERE', 'WHILE', 'WITH', 'WRITE', 'X509', 'XOR', 'YEAR_MONTH', 'ZEROFILL', 'ACTION', 'BIT', 'DATE', 'ENUM', 'NO', 'TEXT', 'TIME');
if(!$operation) {

	$navlang = 'threadtype_infotypes';
	$operation = 'type';
	$changetype = 'threadsorts';

	if(!submitcheck('typesubmit')) {

		$forumsarray = $fidsarray = array();
		$query = DB::query("SELECT f.fid, f.name, ff.$changetype FROM ".DB::table('forum_forum')." f , ".DB::table('forum_forumfield')." ff WHERE ff.$changetype<>'' AND f.fid=ff.fid");
		while($forum = DB::fetch($query)) {
			$forum[$changetype] = unserialize($forum[$changetype]);
			if(is_array($forum[$changetype]['types'])) {
				foreach($forum[$changetype]['types'] as $typeid => $name) {
					$forumsarray[$typeid][] = '<a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'&anchor=threadtypes">'.$forum['name'].'</a>';
					$fidsarray[$typeid][] = $forum['fid'];
				}
			}
		}

		$threadtypes = '';
		$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." ORDER BY displayorder");
		while($type = DB::fetch($query)) {
			$threadtypes .= showtablerow('', array('class="td25"', 'class="td28"', '', 'class="td29"', 'title="'.cplang('forums_threadtypes_forums_comment').'"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$type[typeid]]\" value=\"".dhtmlspecialchars($type['name'])."\">",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"descriptionnew[$type[typeid]]\" value=\"$type[description]\">",
				is_array($forumsarray[$type['typeid']]) ? '<ul class="nowrap lineheight"><li>'.implode(',</li><li> ', $forumsarray[$type['typeid']])."</li></ul><input type=\"hidden\" name=\"fids[$type[typeid]]\" value=\"".implode(', ', $fidsarray[$type['typeid']])."\">" : '',
				"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=sortdetail&sortid=$type[typeid]\" class=\"act nowrap\">$lang[detail]</a>"
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
<?php
		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 1),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 0)
		));

		showformheader("threadtypes&");
		showtableheader('');
		showsubtitle(array('', 'display_order', 'name', 'description', 'forums_relation', ''));
		echo $threadtypes;
		echo '<tr><td class="td25"></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add'].'</a></div></td>';

		showsubmit('typesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		$updatefids = $modifiedtypes = array();

		if(is_array($_G['gp_delete'])) {

			if($deleteids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('forum_typeoptionvar')." WHERE sortid IN ($deleteids)");
				DB::query("DELETE FROM ".DB::table('forum_typevar')." WHERE sortid IN ($deleteids)");
				DB::query("DELETE FROM ".DB::table('forum_threadtype')." WHERE typeid IN ($deleteids)");
			}

			foreach($_G['gp_delete'] as $_G['gp_sortid']) {
				DB::query("DROP TABLE IF EXISTS ".DB::table('forum_optionvalue')."{$_G['gp_sortid']}");
			}

			if($deleteids && DB::affected_rows()) {
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
		}

		if(is_array($_G['gp_namenew']) && $_G['gp_namenew']) {
			foreach($_G['gp_namenew'] as $typeid => $val) {
				$_G['gp_descriptionnew'] = is_array($_G['gp_descriptionnew']) ? $_G['gp_descriptionnew'] : array();
				DB::update('forum_threadtype', array(
					'name' => trim($_G['gp_namenew'][$typeid]),
					'description' => dhtmlspecialchars(trim($_G['gp_descriptionnew'][$typeid])),
					'displayorder' => intval($_G['gp_displayordernew'][$typeid]),
					'special' => 1,
				), "typeid='$typeid'");
				if(DB::affected_rows()) {
					$modifiedtypes[] = $typeid;
				}
			}

			if($modifiedtypes = array_unique($modifiedtypes)) {
				foreach($modifiedtypes as $id) {
					if(!empty($_G['gp_fids'][$id])) {
						foreach(explode(',', $_G['gp_fids'][$id]) as $fid) {
							if($fid = intval($fid)) {
								$updatefids[$fid]['modifiedids'][] = $id;
							}
						}
					}
				}
			}
		}

		if($updatefids) {
			$query = DB::query("SELECT fid, $changetype FROM ".DB::table('forum_forumfield')." WHERE fid IN (".dimplode(array_keys($updatefids)).") AND $changetype<>''");
			while($forum = DB::fetch($query)) {
				$fid = $forum['fid'];
				$forum[$changetype] = unserialize($forum[$changetype]);
				if($updatefids[$fid]['deletedids']) {
					foreach($updatefids[$fid]['deletedids'] as $id) {
						unset($forum[$changetype]['types'][$id], $forum[$changetype]['flat'][$id], $forum[$changetype]['selectbox'][$id]);
					}
				}
				if($updatefids[$fid]['modifiedids']) {
					foreach($updatefids[$fid]['modifiedids'] as $id) {
						if(isset($forum[$changetype]['types'][$id])) {
							$_G['gp_namenew'][$id] = trim(strip_tags($_G['gp_namenew'][$id]));
							$forum[$changetype]['types'][$id] = $_G['gp_namenew'][$id];
							if(isset($forum[$changetype]['selectbox'][$id])) {
								$forum[$changetype]['selectbox'][$id] = $_G['gp_namenew'][$id];
							} else {
								$forum[$changetype]['flat'][$id] = $_G['gp_namenew'][$id];
							}
						}
					}
				}
				DB::update('forum_forumfield', array(
					$changetype => addslashes(serialize($forum[$changetype])),
				), "fid='$fid'");
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($newname1 = trim(strip_tags($value))) {
					$query = DB::query("SELECT typeid FROM ".DB::table('forum_threadtype')." WHERE name='$newname1'");
					if(DB::num_rows($query)) {
						cpmsg('forums_threadtypes_duplicate', '', 'error');
					}
					$data = array(
						'name' => $newname1,
						'description' => dhtmlspecialchars(trim($_G['gp_newdescription'][$key])),
						'displayorder' => $_G['gp_newdisplayorder'][$key],
						'special' => 1,
					);
					DB::insert('forum_threadtype', $data);
				}
			}
		}

		cpmsg('forums_threadtypes_succeed', 'action=threadtypes', 'succeed');

	}

} elseif($operation == 'typeoption') {

	if(!submitcheck('typeoptionsubmit')) {

		if($_G['gp_classid']) {
			if(!$typetitle = DB::result_first("SELECT title FROM ".DB::table('forum_typeoption')." WHERE optionid='{$_G['gp_classid']}'")) {
				cpmsg('threadtype_infotypes_noexist', 'action=threadtypes', 'error');
			}

			$typeoptions = '';
			$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE classid='{$_G['gp_classid']}' ORDER BY displayorder");
			while($option = DB::fetch($query)) {
				$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$typeoptions .= showtablerow('', array('class="td25"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"title[$option[optionid]]\" value=\"".dhtmlspecialchars($option['title'])."\">",
					"$option[identifier]<input type=\"hidden\" name=\"identifier[$option[optionid]]\" value=\"$option[identifier]\">",
					$option['type'],
					"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid=$option[optionid]\" class=\"act\">$lang[detail]</a>"
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
			[1, '<input type="text" class="txt" size="15" name="newidentifier[]">'],
			[1, '<select name="newtype[]"><option value="number">$lang[threadtype_edit_vars_type_number]</option><option value="text" selected>$lang[threadtype_edit_vars_type_text]</option><option value="textarea">$lang[threadtype_edit_vars_type_textarea]</option><option value="radio">$lang[threadtype_edit_vars_type_radio]</option><option value="checkbox">$lang[threadtype_edit_vars_type_checkbox]</option><option value="select">$lang[threadtype_edit_vars_type_select]</option><option value="calendar">$lang[threadtype_edit_vars_type_calendar]</option><option value="email">$lang[threadtype_edit_vars_type_email]</option><option value="image">$lang[threadtype_edit_vars_type_image]</option><option value="url">$lang[threadtype_edit_vars_type_url]</option><option value="range">$lang[threadtype_edit_vars_type_range]</option></select>'],
			[1, '']
		],
	];
</script>
EOT;

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 0),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), 1)
		));
		showformheader("threadtypes&operation=typeoption&typeid={$_G['gp_typeid']}");
		showhiddenfields(array('classid' => $_G['gp_classid']));
		showtableheader();

		showsubtitle(array('', 'display_order', 'name', 'threadtype_variable', 'threadtype_type', ''));
		echo $typeoptions;
		echo '<tr><td></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add_option'].'</a></div></td></tr>';
		showsubmit('typeoptionsubmit', 'submit', 'del');

		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('forum_typeoption')." WHERE optionid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('forum_typevar')." WHERE optionid IN ($ids)");
		}

		if(is_array($_G['gp_title'])) {
			foreach($_G['gp_title'] as $id => $val) {
				if(in_array(strtoupper($_G['gp_identifier'][$id]), $mysql_keywords)) {
					continue;
				}
				DB::update('forum_typeoption', array(
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
					if(in_array(strtoupper($newidentifier1), $mysql_keywords)) {
						cpmsg('threadtype_infotypes_optionvariable_iskeyword', '', 'error');
					}
					$query = DB::query("SELECT optionid FROM ".DB::table('forum_typeoption')." WHERE identifier='$newidentifier1' LIMIT 1");
					if(DB::num_rows($query) || strlen($newidentifier1) > 40  || !ispluginkey($newidentifier1)) {
						cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
					}
					$data = array(
						'classid' => $_G['gp_classid'],
						'displayorder' => $_G['gp_newdisplayorder'][$key],
						'title' => $newtitle1,
						'identifier' => $newidentifier1,
						'type' => $_G['gp_newtype'][$key],
					);
					DB::insert('forum_typeoption', $data);
				} elseif($newtitle1 && !$newidentifier1) {
					cpmsg('threadtype_infotypes_option_invalid', 'action=threadtypes&operation=typeoption&classid='.$_G['gp_classid'], 'error');
				}
			}
		}
		updatecache('threadsorts');
		cpmsg('threadtype_infotypes_succeed', 'action=threadtypes&operation=typeoption&classid='.$_G['gp_classid'], 'succeed');

	}

} elseif($operation == 'optiondetail') {

	$option = DB::fetch_first("SELECT * FROM ".DB::table('forum_typeoption')." WHERE optionid='{$_G['gp_optionid']}'");
	if(!$option) {
		cpmsg('typeoption_not_found', '', 'error');
	}

	if(!submitcheck('editsubmit')) {


		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 0),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 1)
		));

		$typeselect = '<select name="typenew" onchange="var styles, key;styles=new Array(\'number\',\'text\',\'radio\', \'checkbox\', \'textarea\', \'select\', \'image\', \'calendar\', \'range\', \'info\'); for(key in styles) {var obj=$(\'style_\'+styles[key]); if(obj) { obj.style.display=styles[key]==this.options[this.selectedIndex].value?\'\':\'none\';}}">';
		foreach(array('number', 'text', 'radio', 'checkbox', 'textarea', 'select', 'calendar', 'email', 'url', 'image', 'range') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($option['type'] == $type ? 'selected' : '').'>'.$lang['threadtype_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		$option['rules'] = unserialize($option['rules']);
		$option['protect'] = unserialize($option['protect']);

		$groups = $forums = array();
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')."");
		while($group = DB::fetch($query)) {
			$groups[] = array($group['groupid'], $group['grouptitle']);
		}

		$query = DB::query("SELECT fieldid, title FROM ".DB::table('common_member_profile_setting')." WHERE available = 1 AND formtype = 'text'");
		while($result = DB::fetch($query)) {
			$threadtype_profile = !$threadtype_profile ? "<select id='rules[text][profile]' name='rules[text][profile]'><option value=''></option>" : $threadtype_profile."<option value='{$result[fieldid]}' ".($option['rules']['profile'] == $result['fieldid'] ? "selected='selected'" : '').">{$result[title]}</option>";
		}
		$threadtype_profile .= "</select>";

		showformheader("threadtypes&operation=optiondetail&optionid=$_G[gp_optionid]");
		showtableheader();
		showtitle('threadtype_infotypes_option_config');
		showsetting('name', 'titlenew', $option['title'], 'text');
		showsetting('threadtype_variable', 'identifiernew', $option['identifier'], 'text');
		showsetting('type', '', '', $typeselect);
		showsetting('threadtype_edit_desc', 'descriptionnew', $option['description'], 'textarea');
		showsetting('threadtype_unit', 'unitnew', $option['unit'], 'text');
		showsetting('threadtype_expiration', 'expirationnew', $option['expiration'], 'radio');
		if(in_array($option['type'], array('calendar', 'number', 'text', 'email'))) {
			showsetting('threadtype_protect', 'protectnew[status]', $option['protect']['status'], 'radio', 0, 1);
			showsetting('threadtype_protect_mode', array('protectnew[mode]', array(
				array(1, $lang['threadtype_protect_mode_pic']),
				array(2, $lang['threadtype_protect_mode_html'])
			)), $option['protect']['mode'], 'mradio');
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
		showsetting('threadtype_edit_profile', '', '', $threadtype_profile);
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
		showsetting('threadtype_edit_select_choices', 'rules[select][choices]', $option['rules']['choices'], 'textarea');
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
		showtagfooter('tbody');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$titlenew = trim($_G['gp_titlenew']);
		$_G['gp_identifiernew'] = trim($_G['gp_identifiernew']);
		if(!$titlenew || !$_G['gp_identifiernew']) {
			cpmsg('threadtype_infotypes_option_invalid', '', 'error');
		}

		if(in_array(strtoupper($_G['gp_identifiernew']), $mysql_keywords)) {
			cpmsg('threadtype_infotypes_optionvariable_iskeyword', '', 'error');
		}

		$query = DB::query("SELECT optionid FROM ".DB::table('forum_typeoption')." WHERE identifier='{$_G['gp_identifiernew']}' AND optionid<>'{$_G['gp_optionid']}' LIMIT 1");
		if(DB::num_rows($query) || strlen($_G['gp_identifiernew']) > 40  || !ispluginkey($_G['gp_identifiernew'])) {
			cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
		}

		$_G['gp_protectnew']['usergroup'] = $_G['gp_protectnew']['usergroup'] ? implode("\t", $_G['gp_protectnew']['usergroup']) : '';

		DB::update('forum_typeoption', array(
			'title' => $titlenew,
			'description' => $_G['gp_descriptionnew'],
			'identifier' => $_G['gp_identifiernew'],
			'type' => $_G['gp_typenew'],
			'unit' => $_G['gp_unitnew'],
			'expiration' => $_G['gp_expirationnew'],
			'protect' => addslashes(serialize($_G['gp_protectnew'])),
			'rules' => addslashes(serialize($_G['gp_rules'][$_G['gp_typenew']])),
		), "optionid='{$_G['gp_optionid']}'");

		updatecache('threadsorts');
		cpmsg('threadtype_infotypes_option_succeed', 'action=threadtypes&operation=typeoption&classid='.$option['classid'], 'succeed');
	}

} elseif($operation == 'sortdetail') {

	if(!submitcheck('sortdetailsubmit') && !submitcheck('sortpreviewsubmit')) {
		$threadtype = DB::fetch_first("SELECT name, template, stemplate, ptemplate, btemplate, modelid, expiration FROM ".DB::table('forum_threadtype')." WHERE typeid='{$_G['gp_sortid']}'");
		$threadtype['modelid'] = isset($_G['gp_modelid']) ? intval($_G['gp_modelid']) : $threadtype['modelid'];

		$sortoptions = $jsoptionids = '';
		$showoption = array();
		$query = DB::query("SELECT t.optionid, t.displayorder, t.available, t.required, t.unchangeable, t.search, t.subjectshow, tt.title, tt.type, tt.identifier
			FROM ".DB::table('forum_typevar')." t, ".DB::table('forum_typeoption')." tt
			WHERE t.sortid='{$_G['gp_sortid']}' AND t.optionid=tt.optionid ORDER BY t.displayorder");
		while($option = DB::fetch($query)) {
			$jsoptionids .= "optionids.push($option[optionid]);\r\n";
			$optiontitle[$option['identifier']] = $option['title'];
			$showoption[$option['optionid']]['optionid'] = $option['optionid'];
			$showoption[$option['optionid']]['title'] = $option['title'];
			$showoption[$option['optionid']]['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
			$showoption[$option['optionid']]['identifier'] = $option['identifier'];
			$showoption[$option['optionid']]['displayorder'] = $option['displayorder'];
			$showoption[$option['optionid']]['available'] = $option['available'];
			$showoption[$option['optionid']]['required'] = $option['required'];
			$showoption[$option['optionid']]['unchangeable'] = $option['unchangeable'];
			$showoption[$option['optionid']]['search'] = $option['search'];
			$showoption[$option['optionid']]['subjectshow'] = $option['subjectshow'];
		}

		if($existoption && is_array($existoption)) {
			$optionids = $comma = '';
			foreach($existoption as $optionid => $val) {
				$optionids .= $comma.$optionid;
				$comma = '\',\'';
			}
			$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE optionid IN ('$optionids')");
			while($option = DB::fetch($query)) {
				$showoption[$option['optionid']]['optionid'] = $option['optionid'];
				$showoption[$option['optionid']]['title'] = $option['title'];
				$showoption[$option['optionid']]['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$showoption[$option['optionid']]['identifier'] = $option['identifier'];
				$showoption[$option['optionid']]['required'] = $existoption[$option['optionid']];
				$showoption[$option['optionid']]['available'] = 1;
				$showoption[$option['optionid']]['unchangeable'] = 0;
				$showoption[$option['optionid']]['model'] = 1;
			}
		}

		$searchtitle = $searchvalue = $searchunit = array();
		foreach($showoption as $optionid => $option) {
			$sortoptions .= showtablerow('id="optionid'.$optionid.'"', array('class="td25"', 'class="td28 td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
				dhtmlspecialchars($option['title']),
				$option['type'],
				"<input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][form]\" value=\"1\" ".(getstatus($option['search'], 1) == 1 ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][font]\" value=\"1\" ".(getstatus($option['search'], 2) == 1 ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"subjectshow[$option[optionid]]\" value=\"1\" ".($option['subjectshow'] ? 'checked' : '').">",
				"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'typetemplate', 'message');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_template']."</a>",
				"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'stypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_stemplate']."</a>",
				"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'ptypetemplate', 'post');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_ptemplate']."</a>",
				"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'btypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_btemplate']."</a>",
				"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid=$option[optionid]\" class=\"act\" target=\"_blank\">".$lang['edit']."</a>"
			), TRUE);
			$searchtitle[] = '/{('.$option['identifier'].')}/e';
			$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
			$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
		}

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 1),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 0)
		));
		showsubmenu('forums_edit_threadsorts');
		showtips('forums_edit_threadsorts_tips');

		showformheader("threadtypes&operation=sortdetail&sortid={$_G['gp_sortid']}");
		showtableheader('threadtype_infotypes_validity', 'nobottom');
		showsetting('threadtype_infotypes_validity', 'typeexpiration', $threadtype['expiration'], 'radio');
		showtablefooter();

		showtableheader("$threadtype[name] - $lang[threadtype_infotypes_add_option]", 'noborder fixpadding');
		showtablerow('', 'id="classlist"', '');
		showtablerow('', 'id="optionlist"', '');
		showtablefooter();

		showtableheader("$threadtype[name] - $lang[threadtype_infotypes_exist_option]", 'noborder fixpadding', 'id="sortlist"');
		showsubtitle(array('<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form,\'delete\')" /><label for="chkall">'.cplang('del').'</label>', 'display_order', 'available', 'name', 'type', 'required', 'unchangeable', 'threadtype_infotypes_formsearch', 'threadtype_infotypes_fontsearch', 'threadtype_infotypes_show', 'threadtype_infotypes_insert_template', '', ''));
		echo $sortoptions;
		showtablefooter();

?>

<a name="template"></a>
<div class="colorbox">
<h4 style="margin-bottom:15px;"><?php echo $threadtype['name'];?> - <?php echo $lang['threadtype_infotypes_template'];?></h4>
<textarea cols="100" rows="15" id="typetemplate" name="typetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?php echo $threadtype['template'];?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?php echo $threadtype['name'];?> - <?php echo $lang['threadtype_infotypes_stemplate'];?></h4>
<textarea cols="100" rows="15" id="stypetemplate" name="stypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?php echo $threadtype['stemplate'];?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?php echo $threadtype['name'];?> - <?php echo $lang['threadtype_infotypes_ptemplate'];?></h4>
<textarea cols="100" rows="15" id="ptypetemplate" name="ptypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?php echo $threadtype['ptemplate'];?></textarea>
<br /><br />
<h4 style="margin-bottom:15px;"><?php echo $threadtype['name'];?> - <?php echo $lang['threadtype_infotypes_btemplate'];?></h4>
<textarea cols="100" rows="15" id="btypetemplate" name="btypetemplate" style="width: 95%;" onkeyup="textareasize(this)"><?php echo $threadtype['btemplate'];?></textarea>
<br /><br />
<b><?php echo $lang['threadtype_infotypes_template'];?>:</b>
<ul class="tpllist"><?php echo $lang['threadtype_infotypes_template_tips'];?></ul>
<input type="submit" class="btn" name="sortdetailsubmit" value="<?php echo $lang['submit'];?>">
</div>

</form>
<script type="text/JavaScript">
	var optionids = new Array();
	<?php echo $jsoptionids;?>
	function insertvar(text, focusarea, location) {
		$(focusarea).focus();
		selection = document.selection;
		var commonfield = '[' + text + 'value] [' + text + 'unit]';
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
		x.get('<?php echo ADMINSCRIPT;?>?action=threadtypes&operation=sortlist&inajax=1&optionid=' + optionid, function(s, x) {
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
</script>
<script type="text/JavaScript">ajaxget('<?php echo ADMINSCRIPT;?>?action=threadtypes&operation=classlist', 'classlist');</script>
<script type="text/JavaScript">ajaxget('<?php echo ADMINSCRIPT;?>?action=threadtypes&operation=optionlist&sortid=<?php echo $_G['gp_sortid'];?>', 'optionlist', '', '', '', checkedbox);</script>
<?php

	} else {

		DB::update('forum_threadtype', array(
			'special' => 1,
			'modelid' => $_G['gp_modelid'],
			'template' => $_G['gp_typetemplate'],
			'stemplate' => $_G['gp_stypetemplate'],
			'ptemplate' => $_G['gp_ptypetemplate'],
			'btemplate' => $_G['gp_btypetemplate'],
			'expiration' => $_G['gp_typeexpiration'],
		), "typeid='{$_G['gp_sortid']}'");

		if(submitcheck('sortdetailsubmit')) {

			$orgoption = $orgoptions = $addoption = array();
			$query = DB::query("SELECT optionid FROM ".DB::table('forum_typevar')." WHERE sortid='{$_G['gp_sortid']}'");
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
					DB::query("DELETE FROM ".DB::table('forum_typevar')." WHERE sortid='{$_G['gp_sortid']}' AND optionid IN ($ids)");
				}
				foreach($delete as $id) {
					unset($addoption[$id]);
				}
			}

			$insertoptionid = $indexoption = array();
			$create_table_sql = $separator = $create_tableoption_sql = '';

			if(is_array($addoption) && !empty($addoption)) {
				$query = DB::query("SELECT optionid, type, identifier FROM ".DB::table('forum_typeoption')." WHERE optionid IN (".dimplode(array_keys($addoption)).")");
				while($option = DB::fetch($query)) {
					$insertoptionid[$option['optionid']]['type'] = $option['type'];
					$insertoptionid[$option['optionid']]['identifier'] = $option['identifier'];
				}

				$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_optionvalue')."{$_G['gp_sortid']}'");
				if(DB::num_rows($query) != 1) {
					$create_table_sql = "CREATE TABLE ".DB::table('forum_optionvalue')."{$_G['gp_sortid']} (";
					foreach($addoption as $optionid => $option) {
						$identifier = $insertoptionid[$optionid]['identifier'];
						if($identifier) {
							if(in_array($insertoptionid[$optionid]['type'], array('radio'))) {
								$create_tableoption_sql .= "$separator$identifier smallint(6) UNSIGNED NOT NULL DEFAULT '0'\r\n";
							} elseif(in_array($insertoptionid[$optionid]['type'], array('number', 'range'))) {
								$create_tableoption_sql .= "$separator$identifier int(10) UNSIGNED NOT NULL DEFAULT '0'\r\n";
							} elseif($insertoptionid[$optionid]['type'] == 'select') {
								$create_tableoption_sql .= "$separator$identifier varchar(50) NOT NULL\r\n";
							} else {
								$create_tableoption_sql .= "$separator$identifier mediumtext NOT NULL\r\n";
							}
							$separator = ' ,';
							if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select', 'number'))) {
								$indexoption[] = $identifier;
							}
						}
					}
					$create_table_sql .= ($create_tableoption_sql ? $create_tableoption_sql.',' : '')."tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',fid smallint(6) UNSIGNED NOT NULL DEFAULT '0',dateline int(10) UNSIGNED NOT NULL DEFAULT '0',expiration int(10) UNSIGNED NOT NULL DEFAULT '0',";
					$create_table_sql .= "KEY (fid), KEY(dateline)";
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
						$query = DB::query("SHOW FULL COLUMNS FROM ".DB::table('forum_optionvalue')."{$_G['gp_sortid']}", 'SILENT');
					} else {
						$query = DB::query("SHOW COLUMNS FROM ".DB::table('forum_optionvalue')."{$_G['gp_sortid']}", 'SILENT');
					}
					while($field = @DB::fetch($query)) {
						$tables[$field['Field']] = 1;
					}

					foreach($addoption as $optionid => $option) {
						$identifier = $insertoptionid[$optionid]['identifier'];
						if(!$tables[$identifier]) {
							$fieldname = $identifier;
							if(in_array($insertoptionid[$optionid]['type'], array('radio'))) {
								$fieldtype = 'smallint(6) UNSIGNED NOT NULL DEFAULT \'0\'';
							} elseif(in_array($insertoptionid[$optionid]['type'], array('number', 'range'))) {
								$fieldtype = 'int(10) UNSIGNED NOT NULL DEFAULT \'0\'';
							} elseif($insertoptionid[$optionid]['type'] == 'select') {
								$fieldtype = 'varchar(50) NOT NULL';
							} else {
								$fieldtype = 'mediumtext NOT NULL';
							}
							DB::query("ALTER TABLE ".DB::table('forum_optionvalue')."{$_G['gp_sortid']} ADD $fieldname $fieldtype");

							if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select', 'number'))) {
								DB::query("ALTER TABLE ".DB::table('forum_optionvalue')."{$_G['gp_sortid']} ADD INDEX ($fieldname)");
							}
						}
					}
				}
				foreach($addoption as $id => $val) {
					$optionid = DB::fetch_first("SELECT optionid FROM ".DB::table('forum_typeoption')." WHERE optionid='$id'");
					if($optionid) {
						$data = array(
							'sortid' => $_G['gp_sortid'],
							'optionid' => $id,
							'available' => 1,
							'required' => intval($val),
						);
						DB::insert('forum_typevar', $data, 0, 0, 1);
						$search_bit = 0;
						foreach($_G['gp_search'][$id] AS $key => $val) {
							if($val == 1) {
								if($key == 'font') {
									$search_bit = setstatus(2, 1, $search_bit);
								} elseif($key == 'form') {
									$search_bit = setstatus(1, 1, $search_bit);
								}
							}
						}

						DB::update('forum_typevar', array(
							'displayorder' => $_G['gp_displayorder'][$id],
							'available' => $_G['gp_available'][$id],
							'required' => $_G['gp_required'][$id],
							'unchangeable' => $_G['gp_unchangeable'][$id],
							'search' => $search_bit,
							'subjectshow' => $_G['gp_subjectshow'][$id],
						), "sortid='{$_G['gp_sortid']}' AND optionid='$id'");
					} else {
						DB::query("DELETE FROM ".DB::table('forum_typevar')." WHERE sortid='{$_G['gp_sortid']}' AND optionid IN ($id)");
					}
				}
			}

			updatecache('threadsorts');
			cpmsg('threadtype_infotypes_succeed', 'action=threadtypes&operation=sortdetail&sortid='.$_G['gp_sortid'], 'succeed');

		} elseif(submitcheck('sortpreviewsubmit')) {
			header("Location: $_G[siteurl]".ADMINSCRIPT."?action=threadtypes&operation=sortdetail&sortid={$_G['gp_sortid']}#template");
		}

	}

} elseif($operation == 'content') {

	if(!submitcheck('searchsortsubmit', 1) && !submitcheck('delsortsubmit') && !submitcheck('sendpmsubmit')) {

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 0),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 1),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu))
		));

		$_G['gp_sortid'] = intval($_G['gp_sortid']);
		$threadtypes = '<select name="sortid" onchange="window.location.href = \'?action=threadtypes&operation=content&sortid=\'+ this.options[this.selectedIndex].value"><option value="0">'.cplang('none').'</option>';
		$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." ORDER BY displayorder");
		while($type = DB::fetch($query)) {
			$threadtypes .= '<option value="'.$type['typeid'].'" '.($_G['gp_sortid'] == $type['typeid'] ? 'selected="selected"' : '').'>'.dhtmlspecialchars($type['name']).'</option>';
		}
		$threadtypes .= '</select>';

		showformheader('threadtypes&operation=content');
		showtableheader('threadtype_content_choose');
		showsetting('threadtype_content_name', '', '', $threadtypes);

		if($_G['gp_sortid']) {
			showtableheader('threadtype_content_sort_by_conditions');
			loadcache(array('threadsort_option_'.$_G['gp_sortid']));

			$sortoptionarray = $_G['cache']['threadsort_option_'.$_G['gp_sortid']];
			if(is_array($sortoptionarray)) foreach($sortoptionarray as $optionid => $option) {
				$optionshow = '';
				if($option['search']) {
					if(in_array($option['type'], array('radio', 'checkbox', 'select'))){
						if($option['type'] == 'select') {
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
							$optionshow .= '<script type="text/javascript" src="'.$_G['setting']['jspath'].'calendar.js?'.VERHASH.'"></script><input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_G['gp_searchoption'][$optionid]['value'].'" onclick="showcalendar(event, this, false)" />';
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
				cpmsg('threadtype_content_no_choice', 'action=threadtypes&operation=content', 'error');
			}
			$mpurl = ADMINSCRIPT.'?action=threadtypes&operation=content&sortid='.$_G['gp_sortid'].'&searchsortsubmit=true';
			if(!is_array($_G['gp_searchoption'])) {
				$mpurl .= '&searchoption='.$_G['gp_searchoption'];
				$_G['gp_searchoption'] = unserialize(base64_decode($_G['gp_searchoption']));
			} else {
				$mpurl .= '&searchoption='.base64_encode(serialize($_G['gp_searchoption']));
			}

			shownav('forum', 'threadtype_infotypes');
			showsubmenu('threadtype_infotypes', array(
				array('threadtype_infotypes_type', 'threadtypes', 0),
				array('threadtype_infotypes_content', 'threadtypes&operation=content', 1),
				array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu))
			));

			loadcache('forums');
			loadcache(array('threadsort_option_'.$_G['gp_sortid']));
			require_once libfile('function/threadsort');
			sortthreadsortselectoption($_G['gp_sortid']);
			$sortoptionarray = $_G['cache']['threadsort_option_'.$_G['gp_sortid']];
			$selectsql = '';
			if($_G['gp_searchoption']) {
				foreach($_G['gp_searchoption'] as $optionid => $option) {
					$fieldname = $sortoptionarray[$optionid]['identifier'] ? $sortoptionarray[$optionid]['identifier'] : 1;
					if($option['value']) {
						if(in_array($option['type'], array('number', 'radio'))) {
							$option['value'] = intval($option['value']);
							$exp = '=';
							if($option['condition']) {
								$exp = $option['condition'] == 1 ? '>' : '<';
							}
							$sql = "$fieldname$exp'$option[value]'";
						} elseif($option['type'] == 'select') {
							$subvalues = $currentchoices = array();
							if(!empty($sortoptionarray)) {
								foreach($sortoptionarray as $subkey => $subvalue) {
									if($subvalue['identifier'] == $fieldname) {
										$currentchoices = $subvalue['choices'];
										break;
									}
								}
							}
							if(!empty($currentchoices)) {
								foreach($currentchoices as $subkey => $subvalue) {
									if(preg_match('/^'.$option['value'].'/i', $subkey)) {
										$subvalues[] = $subkey;
									}
								}
							}
							$sql = "$fieldname IN (".dimplode($subvalues).")";
						} elseif($option['type'] == 'checkbox') {
							$sql = "$fieldname LIKE '%".(implode("%", $option['value']))."%'";
						} elseif($option['type'] == 'range') {
							$sql = $option['value']['min'] || $option['value']['max'] ? "$fieldname BETWEEN ".intval($option['value']['min'])." AND ".intval($option['value']['max'])."" : '';
						} else {
							$sql = "$fieldname LIKE '%$option[value]%'";
						}
						$selectsql .= $and."$sql ";
						$and = 'AND ';
					}
				}

				$selectsql = trim($selectsql);
				$searchtids = $searchthread = array();
				$query = DB::query("SELECT tid FROM ".DB::table('forum_optionvalue')."{$_G['gp_sortid']} ".($selectsql ? 'WHERE '.$selectsql : '')."");
				while($thread = DB::fetch($query)) {
					$searchtids[] = $thread['tid'];
				}
			}

			if($searchtids) {
				$lpp = max(5, empty($_G['gp_lpp']) ? 50 : intval($_G['gp_lpp']));
				$start_limit = ($page - 1) * $lpp;

				$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($searchtids).")");
				$query = DB::query("SELECT fid, tid, subject, authorid, author, views, replies, lastpost FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($searchtids).") LIMIT $start_limit, $lpp");
				while($thread = DB::fetch($query)) {
					$threads .= showtablerow('', array('class="td25"', '', '', 'class="td28"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"tidsarray[]\" value=\"$thread[tid]\"/>".
					"<input type=\"hidden\" name=\"fidsarray[]\" value=\"$thread[fid]\"/>",
					"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
					"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">{$_G['cache'][forums][$thread[fid]][name]}</a>",
					"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
					$thread['replies'],
					$thread['views'],
					dgmdate($thread['lastpost'], 'd'),
					), TRUE);
				}

				$multipage = multi($threadcount, $lpp, $page, $mpurl, 0, 3);
			}

			showformheader('threadtypes&operation=content');
			showtableheader('admin', 'fixpadding');
			showsubtitle(array('', 'subject', 'forum', 'author', 'threads_replies', 'threads_views', 'threads_lastpost'));
			echo $threads;
			echo $multipage;
			showsubmit('', '', '', "<input type=\"submit\" class=\"btn\" name=\"delsortsubmit\" value=\"{$lang[threadtype_content_delete]}\"/>");
			showtablefooter();
			showformfooter();

		} elseif(submitcheck('delsortsubmit')) {

			require_once libfile('function/post');

			if($_G['gp_tidsarray']) {
				require_once libfile('function/delete');
				deletethread($_G['gp_tidsarray']);

				if($_G['setting']['globalstick']) {
					updatecache('globalstick');
				}

				if($_G['gp_fidsarray']) {
					foreach(explode(',', $_G['gp_fidsarray']) as $fid) {
						updateforumcount(intval($fid));
					}
				}
			}
			cpmsg('threadtype_content_delete_succeed', 'action=threadtypes&operation=content', 'succeed');

		}
	}

} elseif($operation == 'classlist') {

	$classoptions = '';
	$classidarray = array();
	$classid = $_G['gp_classid'] ? $_G['gp_classid'] : 0;
	$query = DB::query("SELECT optionid, title FROM ".DB::table('forum_typeoption')." WHERE classid='$classid' ORDER BY displayorder");
	while($option = DB::fetch($query)) {
		$classidarray[] = $option['optionid'];
		$classoptions .= "<a href=\"#ol\" onclick=\"ajaxget('".ADMINSCRIPT."?action=threadtypes&operation=optionlist&typeid={$_G['gp_typeid']}&classid=$option[optionid]', 'optionlist', 'optionlist', 'Loading...', '', checkedbox)\">$option[title]</a> &nbsp; ";
	}

	include template('common/header');
	echo $classoptions;
	include template('common/footer');
	exit;

} elseif($operation == 'optionlist') {
	$classid = $_G['gp_classid'];
	if(!$classid) {
		$classid = DB::result_first("SELECT optionid FROM ".DB::table('forum_typeoption')." WHERE classid='0' ORDER BY displayorder LIMIT 1");
	}
	$query = DB::query("SELECT optionid FROM ".DB::table('forum_typevar')." WHERE sortid='{$_G['gp_typeid']}'");
	$option = $options = array();
	while($option = DB::fetch($query)) {
		$options[] = $option['optionid'];
	}

	$optionlist = '';
	$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE classid='$classid' ORDER BY displayorder");
	while($option = DB::fetch($query)) {
		$optionlist .= "<input ".(in_array($option['optionid'], $options) ? ' checked="checked" ' : '')."class=\"checkbox\" type=\"checkbox\" name=\"typeselect[]\" id=\"typeselect_$option[optionid]\" value=\"$option[optionid]\" onclick=\"insertoption(this.value);\" /><label for=\"typeselect_$option[optionid]\">".dhtmlspecialchars($option['title'])."</label>&nbsp;&nbsp;";
	}
	include template('common/header');
	echo $optionlist;
	include template('common/footer');
	exit;

} elseif($operation == 'sortlist') {
	$optionid = $_G['gp_optionid'];
	$option = DB::fetch_first("SELECT * FROM ".DB::table('forum_typeoption')." WHERE optionid='$optionid' LIMIT 1");
	include template('common/header');
	$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
	$option['available'] = 1;
	showtablerow('', array('class="td25"', 'class="td28 td23"'), array(
		"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\" ".($option['model'] ? 'disabled' : '').">",
		"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
		dhtmlspecialchars($option['title']),
		$option[type],
		"<input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][form]\" value=\"1\" ".(getstatus($option['search'], 1) == 1 ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][font]\" value=\"1\" ".(getstatus($option['search'], 2) == 1 ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"subjectshow[$option[optionid]]\" value=\"1\" ".($option['subjectshow'] ? 'checked' : '').">",
		"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'typetemplate', 'message');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_template']."</a>",
		"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'stypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_stemplate']."</a>",
		"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'ptypetemplate', 'post');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_ptemplate']."</a>",
		"<a href=\"###\" onclick=\"insertvar('$option[identifier]', 'btypetemplate', 'subject');doane(event);return false;\" class=\"act\">".$lang['threadtype_infotypes_add_btemplate']."</a>",
		"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid=$option[optionid]\" class=\"act\">".$lang['edit']."</a>"
	));
	include template('common/footer');
	exit;
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

?>