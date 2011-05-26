<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_medals.php 19745 2011-01-18 05:39:21Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(!submitcheck('medalsubmit')) {
		shownav('extended', 'nav_medals', 'admin');
		showsubmenu('nav_medals', array(
			array('admin', 'medals', 1),
			array('nav_medals_confer', 'members&operation=confermedal', 0),
			array('nav_medals_mod', 'medals&operation=mod', 0)
		));
		showtips('medals_tips');
		showformheader('medals');
		showtableheader('medals_list', 'fixpadding');
		showsubtitle(array('', 'display_order', 'available', 'name', 'description', 'medals_image', 'medals_type', ''));

?>
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newname[]" size="10">'],
			[1,'<input type="text" class="txt" name="newdescription[]" size="30">'],
			[1,'<input type="text" class="txt" name="newimage[]" size="20">'],
			[1,'', 'td23'],
			[1,'', 'td25']
		]
	];
</script>
<?php
		$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." ORDER BY displayorder");
		while($medal = DB::fetch($query)) {
			$checkavailable = $medal['available'] ? 'checked' : '';
			switch($medal['type']) {
				case 0:
					$medal['type'] = cplang('medals_adminadd');
					break;
				case 1:
					$medal['type'] = cplang('medals_register');
					break;
				case 2:
					$medal['type'] = cplang('modals_moderate');
					break;
			}
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', '', '', '', 'class="td23"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$medal[medalid]\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$medal[medalid]]\" value=\"$medal[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$medal[medalid]]\" value=\"1\" $checkavailable>",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$medal[medalid]]\" value=\"$medal[name]\">",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"description[$medal[medalid]]\" value=\"$medal[description]\">",
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"image[$medal[medalid]]\" value=\"$medal[image]\"><img style=\"vertical-align:middle\" src=\"static/image/common/$medal[image]\">",
				$medal[type],
				"<a href=\"".ADMINSCRIPT."?action=medals&operation=edit&medalid=$medal[medalid]\" class=\"act\">$lang[detail]</a>"
			));
		}

		echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['medals_addnew'].'</a></div></td></tr>';
		showsubmit('medalsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			DB::query("DELETE FROM ".DB::table('forum_medal')." WHERE medalid IN ($ids)");
		}

		if(is_array($_G['gp_name'])) {
			foreach($_G['gp_name'] as $id => $val) {
				DB::query("UPDATE ".DB::table('forum_medal')." SET name=".($_G['gp_name'][$id] ? '\''.dhtmlspecialchars($_G['gp_name'][$id]).'\'' : 'name').", available='{$_G['gp_available'][$id]}', description=".($_G['gp_description'][$id] ? '\''.dhtmlspecialchars($_G['gp_description'][$id]).'\'' : 'name').", displayorder='".intval($_G['gp_displayorder'][$id])."', image=".($_G['gp_image'][$id] ? '\''.$_G['gp_image'][$id].'\'' : 'image')." WHERE medalid='$id'");
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($value != '' && $_G['gp_newimage'][$key] != '') {
					$data = array('name' => dhtmlspecialchars($value),
						'available' => $_G['gp_newavailable'][$key],
						'image' => $_G['gp_newimage'][$key],
						'displayorder' => intval($_G['gp_newdisplayorder'][$key]),
						'description' => dhtmlspecialchars($_G['gp_newdescription'][$key]),
					);
					DB::insert('forum_medal', $data);
				}
			}
		}

		updatecache('setting');
		updatecache('medals');
		cpmsg('medals_succeed', 'action=medals', 'succeed');

	}

} elseif($operation == 'mod') {

	if(submitcheck('delmedalsubmit')) {
		if (is_array($_G['gp_delete']) && !empty($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			$query = DB::query("UPDATE ".DB::table('forum_medallog')." SET type='3' WHERE id IN ($ids)");
			cpmsg('medals_invalidate_succeed', 'action=medals&operation=mod', 'succeed');
		} else {
			cpmsg('medals_please_input', 'action=medals&operation=mod', 'error');
		}
	} elseif(submitcheck('modmedalsubmit')) {

		if(is_array($_G['gp_delete']) && !empty($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}

			$query = DB::query("SELECT me.id, me.uid, me.medalid, me.dateline, me.expiration, mf.medals
					FROM ".DB::table('forum_medallog')." me
					LEFT JOIN ".DB::table('common_member_field_forum')." mf USING (uid)
					WHERE id IN ($ids)");

			loadcache('medals');
			while($modmedal = DB::fetch($query)) {
				$modmedal['medals'] = empty($medalsnew[$modmedal['uid']]) ? $modmedal['medals'] : $medalsnew[$modmedal['uid']];

				foreach($modmedal['medals'] = explode("\t", $modmedal['medals']) as $key => $modmedalid) {
					list($medalid, $medalexpiration) = explode("|", $modmedalid);
					if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
						$medalsnew[$modmedal['uid']][$key] = $modmedalid;
					}
				}
				$medalstatus = empty($modmedal['expiration']) ? 0 : 1;
				$modmedal['expiration'] = $modmedal['expiration'] ? (TIMESTAMP + $modmedal['expiration'] - $modmedal['dateline']) : '';
				$medalsnew[$modmedal['uid']][] = $modmedal['medalid'].(empty($modmedal['expiration']) ? '' : '|'.$modmedal['expiration']);
				DB::query("UPDATE ".DB::table('forum_medallog')." SET type=1, status='$medalstatus', expiration='$modmedal[expiration]' WHERE id='$modmedal[id]'");
			}

			foreach ($medalsnew as $key => $medalnew) {
				$medalnew = array_unique($medalnew);
				$medalnew = implode("\t", $medalnew);
				DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='$medalnew' WHERE uid='$key'");
			}
			cpmsg('medals_validate_succeed', 'action=medals&operation=mod', 'succeed');
		} else {
			cpmsg('medals_please_input', 'action=medals&operation=mod', 'error');
		}
	} else {

		$medals = '';
		$query = DB::query("SELECT mel.*, m.username, me.name FROM ".DB::table('forum_medallog')." mel
				LEFT JOIN ".DB::table('forum_medal')." me ON me.medalid = mel.medalid
				LEFT JOIN ".DB::table('common_member')." m ON m.uid = mel.uid
				WHERE mel.type='2' ORDER BY dateline");
		while($medal = DB::fetch($query)) {
			$medal['dateline'] =  dgmdate($medal['dateline'], 'Y-m-d H:i');
			$medal['expiration'] =  empty($medal['expiration']) ? $lang['medals_forever'] : dgmdate($medal['expiration'], 'Y-m-d H:i');
			$medals .= showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$medal[id]\">",
				"<a href=\"home.php?mod=space&username=".rawurlencode($medal['username'])."\" target=\"_blank\">$medal[username]</a>",
				$medal['name'],
				$medal['dateline'],
				$medal['expiration']
			), TRUE);
		}

		shownav('extended', 'nav_medals', 'nav_medals_mod');
		showsubmenu('nav_medals', array(
			array('admin', 'medals', 0),
			array('nav_medals_confer', 'members&operation=confermedal', 0),
			array('nav_medals_mod', 'medals&operation=mod', 1)
		));
		showformheader('medals&operation=mod');
		showtableheader('medals_mod');
		showtablerow('', '', array(
			'',
			cplang('medals_user'),
			cplang('medals_name'),
			cplang('medals_date'),
			cplang('medals_expr'),
		));
		echo $medals;
		showsubmit('modmedalsubmit', 'medals_modpass', 'select_all', '<input type="submit" class="btn" value="'.cplang('medals_modnopass').'" name="delmedalsubmit"> ');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'edit') {

	$medalid = intval($_G['gp_medalid']);

	if(!submitcheck('medaleditsubmit')) {

		$medal = DB::fetch_first("SELECT * FROM ".DB::table('forum_medal')." WHERE medalid='$medalid'");

		$medal['permission'] = unserialize($medal['permission']);
		$medal['usergroupallow'] = $medal['permission']['usergroupallow'];
		$medal['usergroups'] = (array)$medal['permission']['usergroups'];
		$medal['permission'] = $medal['permission'][0];

		$checkmedaltype = array($medal['type'] => 'checked');

		$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		$groupselect = array();
		while($group = DB::fetch($query)) {
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $medal['usergroups']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
		}
		$usergroups = '<select name="usergroupsnew[]" size="10" multiple="multiple">'.
			'<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';

		shownav('extended', 'nav_medals', 'admin');
		showsubmenu('nav_medals', array(
			array('admin', 'medals', 1),
			array('nav_medals_confer', 'members&operation=confermedal', 0),
			array('nav_medals_mod', 'medals&operation=mod', 0)
		));
		showformheader("medals&operation=edit&medalid=$medalid");
		showtableheader(cplang('medals_edit').' - '.$medal['name'], 'nobottom');
		showsetting('medals_name1', 'namenew', $medal['name'], 'text');
		showsetting('medals_img', '', '', '<input type="text" class="txt" size="30" name="imagenew" value="'.$medal['image'].'" ><img src="static/image/common/'.$medal['image'].'">');
		showsetting('medals_type1', '', '', '<ul class="nofloat" onmouseover="altStyle(this);">
			<li'.($checkmedaltype[0] ? ' class="checked"' : '').'><input name="typenew" type="radio" class="radio" value="0" '.$checkmedaltype[0].'>&nbsp;'.$lang['medals_adminadd'].'</li>
			<li'.($checkmedaltype[1] ? ' class="checked"' : '').'><input name="typenew" type="radio" class="radio" value="1" '.$checkmedaltype[1].'>&nbsp;'.$lang['medals_apply_auto'].'</li>
			<li'.($checkmedaltype[2] ? ' class="checked"' : '').'><input name="typenew" type="radio" class="radio" value="2" '.$checkmedaltype[2].'>&nbsp;'.$lang['medals_apply_noauto'].'</li></ul>'
		);
		showsetting('medals_usergroups_allow', 'usergroupallow', $medal['usergroupallow'], 'radio', 0, 1);
		showsetting('medals_usergroups', '', '', $usergroups);
		showtagfooter('tbody');
		showsetting('medals_expr1', 'expirationnew', $medal['expiration'], 'text');
		showsetting('medals_memo', 'descriptionnew', $medal['description'], 'text');
		showtablefooter();

		showtableheader('medals_perm', 'notop');

			$formulareplace .= '\'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\',\'<u>'.$lang['setting_credits_formula_posts'].'</u>\',\'<u>'.$lang['setting_credits_formula_oltime'].'</u>\',\'<u>'.$lang['setting_credits_formula_pageviews'].'</u>\'';

?>
<script type="text/JavaScript">

	function isUndefined(variable) {
		return typeof variable == 'undefined' ? true : false;
	}

	function insertunit(text, textend) {
		$('formulapermnew').focus();
		textend = isUndefined(textend) ? '' : textend;
		if(!isUndefined($('formulapermnew').selectionStart)) {
			var opn = $('formulapermnew').selectionStart + 0;
			if(textend != '') {
				text = text + $('formulapermnew').value.substring($('formulapermnew').selectionStart, $('formulapermnew').selectionEnd) + textend;
			}
			$('formulapermnew').value = $('formulapermnew').value.substr(0, $('formulapermnew').selectionStart) + text + $('formulapermnew').value.substr($('formulapermnew').selectionEnd);
		} else if(document.selection && document.selection.createRange) {
			var sel = document.selection.createRange();
			if(textend != '') {
				text = text + sel.text + textend;
			}
			sel.text = text.replace(/\r?\n/g, '\r\n');
			sel.moveStart('character', -strlen(text));
		} else {
			$('formulapermnew').value += text;
		}
		formulaexp();
	}

	var formulafind = new Array('digestposts', 'posts', 'threads');
	var formulareplace = new Array(<?php echo $formulareplace;?>);
	function formulaexp() {
		var result = $('formulapermnew').value;
<?php

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : $lang['setting_credits_formula_extcredits'].$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.$extcredittitle.'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="insertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		echo 'result = result.replace(/regdate/g, \'<u>'.cplang('forums_edit_perm_formula_regdate').'</u>\');';
		echo 'result = result.replace(/regday/g, \'<u>'.cplang('forums_edit_perm_formula_regday').'</u>\');';
		echo 'result = result.replace(/regip/g, \'<u>'.cplang('forums_edit_perm_formula_regip').'</u>\');';
		echo 'result = result.replace(/lastip/g, \'<u>'.cplang('forums_edit_perm_formula_lastip').'</u>\');';
		echo 'result = result.replace(/buyercredit/g, \'<u>'.cplang('forums_edit_perm_formula_buyercredit').'</u>\');';
		echo 'result = result.replace(/sellercredit/g, \'<u>'.cplang('forums_edit_perm_formula_sellercredit').'</u>\');';
		echo 'result = result.replace(/digestposts/g, \'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.$lang['setting_credits_formula_posts'].'</u>\');';
		echo 'result = result.replace(/threads/g, \'<u>'.$lang['setting_credits_formula_threads'].'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.$lang['setting_credits_formula_oltime'].'</u>\');';
		echo 'result = result.replace(/and/g, \'&nbsp;&nbsp;'.$lang['setting_credits_formulaperm_and'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/or/g, \'&nbsp;&nbsp;'.$lang['setting_credits_formulaperm_or'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/>=/g, \'&ge;\');';
		echo 'result = result.replace(/<=/g, \'&le;\');';

?>
		$('formulapermexp').innerHTML = result;
	}
</script>
<tr><td colspan="2"><div class="extcredits">
<?php echo $extcreditsbtn;?>
<a href="###" onclick="insertunit(' regdate ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regdate')?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' regday ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regday')?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' regip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regip')?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' lastip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_lastip')?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' buyercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_buyercredit')?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' sellercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_sellercredit')?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' digestposts ')"><?php echo $lang['setting_credits_formula_digestposts'];?></a>&nbsp;
<a href="###" onclick="insertunit(' posts ')"><?php echo $lang['setting_credits_formula_posts'];?></a>&nbsp;
<a href="###" onclick="insertunit(' threads ')"><?php echo $lang['setting_credits_formula_threads'];?></a>&nbsp;
<a href="###" onclick="insertunit(' oltime ')"><?php echo $lang['setting_credits_formula_oltime'];?></a>&nbsp;
<a href="###" onclick="insertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' > ')">&nbsp;>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' >= ')">&nbsp;>=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' < ')">&nbsp;<&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' <= ')">&nbsp;<=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' = ')">&nbsp;=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' and ')">&nbsp;<?php echo $lang['setting_credits_formulaperm_and'];?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' or ')">&nbsp;<?php echo $lang['setting_credits_formulaperm_or'];?>&nbsp;</a>&nbsp;<br />
</div><div id="formulapermexp" class="marginbot diffcolor2"><?php echo $formulapermexp;?></div>
<textarea name="formulapermnew" id="formulapermnew" style="width: 80%" rows="3" onkeyup="formulaexp()"><?php echo dhtmlspecialchars($medal['permission']);?></textarea>
<br /><span class="smalltxt"><?php echo $lang['medals_permformula'];?></span>
<br /><?php echo $lang['creditwizard_current_formula_notice'];?>
<script type="text/JavaScript">formulaexp()</script>
</td></tr>
<?php
			showsubmit('medaleditsubmit');
			showtablefooter();
			showformfooter();

	} else {
		if(!checkformulaperm($_G['gp_formulapermnew'])) {
			cpmsg('forums_formulaperm_error', '', 'error');
		}

		$formulapermary[0] = $_G['gp_formulapermnew'];
		$formulapermary[1] = preg_replace(
				array("/(digestposts|posts|threads|oltime|extcredits[1-8])/", "/(regdate|regday|regip|lastip|buyercredit|sellercredit|field\d+)/"),
				array("getuserprofile('\\1')", "\$memberformula['\\1']"),
				$_G['gp_formulapermnew']);
		$formulapermary['usergroupallow'] = $_G['gp_usergroupallow'];
		$formulapermary['usergroups'] = (array)$_G['gp_usergroupsnew'];
		$formulapermnew = addslashes(serialize($formulapermary));

		DB::update('forum_medal', array(
			'name' => $_G['gp_namenew'] ? dhtmlspecialchars($_G['gp_namenew']) : 'name',
			'type' => $_G['gp_typenew'],
			'description' => dhtmlspecialchars($_G['gp_descriptionnew']),
			'expiration' => intval($_G['gp_expirationnew']),
			'permission' => $formulapermnew,
			'image' => $_G['gp_imagenew'],
		), "medalid='$medalid'");

		updatecache('medals');
		cpmsg('medals_succeed', 'action=medals&do=editmedals', 'succeed');
	}

}

?>