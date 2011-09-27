<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-13 14:32
 *	Filename: manage.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('delete')) {
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base'));
	$each = 15;
	$page = intval($_G['gp_page']);
	$page = max($page, 1);
	$start = ($page - 1) * $each;

	showtips(lang('plugin/threadlink', 'tips'), 'threadlink_tips');
	showtableheader(lang('plugin/threadlink', 'threadlink'));
	showformheader('plugins&operation=config&identifier=threadlink&pmod=manage');
	showtablerow('', array('class="td25"', ''), array(
				'&nbsp;',
				lang ('plugin/threadlink', 'thread')
			));
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('threadlink_base')." ORDER BY dateline DESC LIMIT $start,$each");
		while($thread = DB::fetch($query)) {
			showtablerow('', array('class="td25"', ''), array(
				'<input type="checkbox" name="tid[]" value="'.$thread['tid'].'" />',
				'<a href="forum.php?mod=viewthread&tid='.$thread['tid'].'" target="_blank">'.$thread['subject'].'</a>'
				));
		}
		$multi = multi($count, $each, $page, ADMINSCRIPT.'?action=plugins&operation=config&identifier=threadlink&pmod=manage');
		showsubmit('delete','delete', '', '', $multi);
	} else {
		showtablerow('', array('class="td25"', ''), array(
			'', lang('plugin/threadlink', 'no_threadlink')
			));
	}
	showtablefooter();
	showformfooter();

} else {
	$tids = $_G['gp_tid'];
	foreach($tids as $key => $tid) {
		$tids[$key] = intval($tid);
	}
	$tids = array_filter($tids);
	if(!empty($tids)) {
		DB::query("DELETE FROM ".DB::table('threadlink_base')." WHERE tid IN (".dimplode($tids).")");
		DB::query("DELETE FROM ".DB::table('threadlink_link')." WHERE tid IN (".dimplode($tids).")");
	}
	cpmsg(lang('plugin/threadlink', 'm_delete_s'), 'action=plugins&operation=config&identifier=threadlink&pmod=manage');
}
?>
