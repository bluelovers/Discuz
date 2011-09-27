<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-03-17 10:36:18  zhouxingming
 *
 * */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//待定功能
//1、清除无用数据
//2、
//
$op = empty($_G['gp_op']) ? 'view' : $_G['gp_op'];
$each = 10;
$start = ($page - 1)*$each;

$tid = intval($_G['gp_tid']);
$tid = $tid > 0 ? $tid : 0;

if($tid) {

	$exist = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
	if(!$exist) {
		cpmsg(lang('plugin/auction', 'undifine_op'));
	}
	$auction = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
	require_once DISCUZ_ROOT.'./source/plugin/auction/finish.func.php';
	$finished = finish($auction);
	if($finished) {
		DB::query("UPDATE ".DB::table('plugin_auction')." SET status=1 WHERE tid='$tid'");
		cpmsg(lang('plugin/auction', 'm_finish_succeed'), 'action=plugins&operation=config&identifier=auction&pmod=manage');
	} else {
		DB::query("UPDATE ".DB::table('plugin_auction')." SET status=1 WHERE tid='$tid'");
		cpmsg(lang('plugin/auction', 'm_finish_error'), 'action=plugins&operation=config&identifier=auction&pmod=manage');
	}
}
if($op == 'view') {
	if(!submitcheck('delete')) {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction'));
		showtips(lang('plugin/auction', 'm_manage_tips'));
		showtableheader(lang('plugin/auction', 'auction'));
		showformheader('plugins&operation=config&identifier=auction&pmod=manage');
		showtablerow('', array('width="5%"', 'width="10"', 'width="5%"', 'width="25%"', 'width="20%"', 'width="10%"', 'width="10%"', 'width="15%"'), array(
			'&nbsp;',
			lang ('plugin/auction', 'm_type'),
			'TID',
			lang('plugin/auction', 'm_title'),
			lang('plugin/auction', 'm_starttimeto'),
			lang('plugin/auction', 'm_username'),
			lang('plugin/auction', 'm_finished'),
			lang('plugin/auction', 'm_operation'),
		));
		if($count) {
			$query = DB::query("SELECT * FROM ".DB::table('plugin_auction')." ORDER BY status ASC,starttimefrom DESC LIMIT $start,$each");
			while($auction = DB::fetch($query)) {
				showtablerow('', array('width="5%"', 'width="10%"', 'width="5%"', 'width="25%"', 'width="20%"', 'width="10%"', 'width="10%"', 'width="15%"'), array(
					'<input type="checkbox" name="deleteids[]" value="'.$auction['tid'].'">',
					lang('plugin/auction', 'auc_type'.($auction['typeid'] == 2 ? '3' : ($auction['extra'] == 1 ? '1' : '2') )),
					$auction['tid'],
					'<a href="forum.php?mod=viewthread&tid='.$auction['tid'].'" target="_blank">'.$auction['name'].'</a>',
					dgmdate($auction['starttimeto']),
					'<a href="home.php?mod=space&uid='.$auction['uid'].'" target="_blank">'.$auction['username'].'</a>',
					$auction['status'] ? lang('plugin/auction', 'm_yes') : lang('plugin/auction', 'm_no'),
					/*lang('plugin/auction', 'm_edit').'&nbsp;'.*/((!$auction['status'] && $auction['starttimeto'] < $_G['timestamp']) ? ('<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&identifier=auction&pmod=manage&tid='.$auction['tid'].'">'.lang('plugin/auction', 'm_finish').'</a>') : ''),
				));
			}
	 		showsubmit('delete','delete');
		} else {
			showtablerow('', array('colspan="5"'), array(lang('plugin/auction', 'none')));
		}
		showtablerow('', array('colspan="5"'), array(multi($count, $each, $page, ADMINSCRIPT.'?action=plugins&operation=config&identifier=auction&pmod=manage')));
		showformfooter();
		showtablefooter();
	} else {

		$deleteids = $_G['gp_deleteids'];
		$dels = 0;
		foreach($deleteids as $id) {
			$id = intval($id);
			if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE tid='$id'")) {
				$auction = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$id'");

				if($auction['status']) {
					DB::query("DELETE FROM ".DB::table('plugin_auctionapply')." WHERE tid='{$id}'");
					DB::query("DELETE FROM ".DB::table('plugin_auction')." WHERE tid='{$id}'");
					$dels++;
				}
			}

		}
		cpmsg(lang('plugin/auction', 'm_delete_succeed')." $dels ".lang('plugin/auction', 'm_delete_end'), 'action=plugins&operation=config&identifier=auction&pmod=manage');
	}
} else {
	cpmsg(lang('plugin/auction', 'undifine_op'));
}

?>
