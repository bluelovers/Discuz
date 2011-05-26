<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp.inc.php 18582 2010-11-29 07:12:59Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$Plang = $scriptlang['myrepeats'];

if($_G['gp_op'] == 'lock') {
	$lock = DB::result_first("SELECT locked FROM ".DB::table('myrepeats')." WHERE uid='$_G[gp_uid]' AND username='$_G[gp_username]'");
	$locknew = $lock ? 0 : 1;
	DB::query("UPDATE ".DB::table('myrepeats')." SET locked='$locknew' WHERE uid='$_G[gp_uid]' AND username='$_G[gp_username]'");
	ajaxshowheader();
	echo $lock ? $Plang['normal'] : $Plang['lock'];
	ajaxshowfooter();
} elseif($_G['gp_op'] == 'delete') {
	DB::query("DELETE FROM ".DB::table('myrepeats')." WHERE uid='$_G[gp_uid]' AND username='$_G[gp_username]'");
	ajaxshowheader();
	echo $Plang['deleted'];
	ajaxshowfooter();
}

$ppp = 100;
$resultempty = FALSE;
$srchadd = $searchtext = $extra = $srchuid = '';
$page = max(1, intval($_G['gp_page']));
if(!empty($_G['gp_srchuid'])) {
	$srchuid = intval($_G['gp_srchuid']);
	$srchadd = "AND mr.uid='$srchuid'";
} elseif(!empty($_G['gp_srchusername'])) {
	$srchuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_srchusername]'");
	if($srchuid) {
		$srchadd = "AND mr.uid='$srchuid'";
	} else {
		$resultempty = TRUE;
	}
} elseif(!empty($_G['gp_srchrepeat'])) {
	$extra = '&srchrepeat='.rawurlencode(stripslashes($_G['gp_srchrepeat']));
	$srchadd = "AND mr.username='$_G[gp_srchrepeat]'";
	$searchtext = $Plang['search'].' "'.stripslashes($_G['gp_srchrepeat']).'" '.$Plang['repeats'].'&nbsp;';
}

if($srchuid) {
	$extra = '&srchuid='.$srchuid;
	$srchusername = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$srchuid'");
	$searchtext = $Plang['search'].' "'.$srchusername.'" '.$Plang['repeatusers'].'&nbsp;';
}

$statary = array(-1 => $Plang['status'], 0 => $Plang['normal'], 1 => $Plang['lock']);
$status = isset($_G['gp_status']) ? $_G['gp_status'] : -1;

if(isset($status) && $status >= 0) {
	$srchadd .= " AND mr.locked='$status'";
	$searchtext .= $Plang['search'].$statary[$status].$Plang['statuss'];
}

if($searchtext) {
	$searchtext = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp">'.$Plang['viewall'].'</a>&nbsp'.$searchtext;
}

loadcache('usergroups');

showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp', 'repeatsubmit');
showsubmit('repeatsubmit', $Plang['search'], $lang['username'].': <input name="srchusername" value="'.htmlspecialchars(stripslashes($_G['gp_srchusername'])).'" class="txt" />&nbsp;&nbsp;'.$Plang['repeat'].': <input name="srchrepeat" value="'.htmlspecialchars(stripslashes($_G['gp_srchrepeat'])).'" class="txt" />', $searchtext);
showformfooter();

$statselect = '<select onchange="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp'.$extra.'&status=\' + this.value">';
foreach($statary as $k => $v) {
	$statselect .= '<option value="'.$k.'"'.($k == $status ? ' selected' : '').'>'.$v.'</option>';
}
$statselect .= '</select>';

echo '<tr class="header"><th>'.$Plang['username'].'</th><th>'.$lang['usergroup'].'</th><th>'.$Plang['repeat'].'</th><th>'.$Plang['lastswitch'].'</th><th>'.$statselect.'</th><th></th></tr>';
if(!$resultempty) {
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('myrepeats')." mr WHERE 1 $srchadd");
	$query = DB::query("SELECT mr.*, m.username AS user,m.groupid FROM ".DB::table('myrepeats')." mr LEFT JOIN ".DB::table('common_member')." m ON m.uid=mr.uid WHERE 1 $srchadd ORDER BY mr.uid LIMIT ".(($page - 1) * $ppp).",$ppp");
	$i = 0;
	while($myrepeat = DB::fetch($query)) {
		$myrepeat['lastswitch'] = $myrepeat['lastswitch'] ? dgmdate($myrepeat['lastswitch']) : '';
		$myrepeat['usernameenc'] = rawurlencode($myrepeat['username']);
		$opstr = !$myrepeat['locked'] ? $Plang['normal'] : $Plang['lock'];
		$i++;
		echo '<tr><td><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&mod=admincp&srchuid='.$myrepeat['uid'].'">'.$myrepeat['user'].'</a></td>'.
			'<td>'.$_G['cache']['usergroups'][$myrepeat['groupid']]['grouptitle'].'</td>'.
			'<td><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&mod=admincp&srchrepeat='.rawurlencode($myrepeat['username']).'" title="'.htmlspecialchars($myrepeat['comment']).'">'.$myrepeat['username'].'</a>'.'</td>'.
			'<td>'.($myrepeat['lastswitch'] ? $myrepeat['lastswitch'] : '').'</td>'.
			'<td><a id="d'.$i.'" onclick="ajaxget(this.href, this.id, \'\');return false" href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp&uid='.$myrepeat['uid'].'&username='.$myrepeat['usernameenc'].'&op=lock">'.$opstr.'</a></td>'.
			'<td><a id="p'.$i.'" onclick="ajaxget(this.href, this.id, \'\');return false" href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=myrepeats&pmod=admincp&uid='.$myrepeat['uid'].'&username='.$myrepeat['usernameenc'].'&op=delete">['.$lang['delete'].']</a></td></tr>';
	}
}
showtablefooter();

echo multi($count, $ppp, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=myrepeats&pmod=admincp$extra");

?>