<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: admin_mod.inc.php 19 2010-12-31 19:12:45Z chuzhaowei@gmail.com $
*/
(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && exit('Access Denied');

if(submitcheck('delmedalsubmit')) {
	if (is_array($_G['gp_delete']) && !empty($_G['gp_delete'])) {
		$ids = $comma = '';
		foreach($_G['gp_delete'] as $id) {
			$ids .= "$comma'$id'";
			$comma = ',';
		}
		$query = DB::query("UPDATE ".DB::table('forum_medallog')." SET type='3' WHERE id IN ($ids)");
		cpmsg('medals_invalidate_succeed', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_mod', 'succeed');
	} else {
		cpmsg('medals_please_input', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_mod', 'error');
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
		cpmsg('medals_validate_succeed', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_mod', 'succeed');
	} else {
		cpmsg('medals_please_input', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_mod', 'error');
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
	showformheader('plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_mod');
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

?>