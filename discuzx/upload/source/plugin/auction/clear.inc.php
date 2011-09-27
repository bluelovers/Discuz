<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-09-02 10:31:40 zhouxingming
 *	Description: 删除冗余数据
 * */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$start = intval($_G['gp_start']);
$start = $start > 0 ? $start : 0;
loadcache('plugin');
if(!$_G['gp_confirm']) {
	echo lang('plugin/auction', 'clear_tips');exit;
}
$each = 500;

$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction'));

if($start > $count) {
	cpmsg(lang('plugin/auction', 'm_clear_finish'), 'action=plugins&operation=config&identifier=auction&pmod=manage');

} else {
	$query = DB::query("SELECT * FROM ".DB::table('plugin_auction')." ORDER BY tid ASC LIMIT $start,$each");
	
	while($result = DB::fetch($query)) {
		$thread = get_thread_by_tid($result['tid'], 'tid');
		if(empty($thread)) {
			include_once libfile('function/delete');
			if(!$result['status']) {
				$query = DB::query("SELECT * FROM ".DB::table('plugin_auctionapply')." WHERE tid='{$result[tid]}'");
				while($apply = DB::fetch($query)) {
					updatemembercount($apply['uid'], array('extcredits'.($result['extid'] ? $result['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit']) => $apply['cur_price']), false, 'AUC', $result['tid']);
					notification_add(
						$apply['uid'],
						'system',
						lang('plugin/auction', 'n_auction_clear'),
							array(
							'auctionname' => $result['name'],
							'auctiontid' => $result['tid'],
							),
							1
						);
				}
			}
			DB::query("DELETE FROM ".DB::table('plugin_auctionapply')." WHERE tid='{$reuslt[tid]}'");
			DB::query("DELETE FROM ".DB::table('plugin_auction')." WHERE tid='{$result[tid]}'");
		}
	}
	$start += $each;

	cpmsg(lang('plugin/auction', 'm_clear_redirect').' '.$start, 'action=plugins&operation=config&identifier=auction&pmod=clear&start='.$start);
	
}
?>
