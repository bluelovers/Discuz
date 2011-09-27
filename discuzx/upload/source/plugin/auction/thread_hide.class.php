<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-09-02 10:26:29 zhouxingming
 *	Description:额外的一些处理
 * */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class plugin_auction{
	/**
	 * 删除帖子的时候调用
	 */
	function deletethread($param) {

		if($param['step'] == 'delete') {
			$tid = $param['param'][0][0];
			if($tid) {
				$thread = get_thread_by_tid($result['tid'], 'tid');
				if(empty($thread)) {
					$result = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
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
		}
	}

}

class plugin_auction_forum extends plugin_auction {
	/**
	 * ajax调用竞拍记录
	 */
	function viewthread_postbottom_output() {
		global $_G,$postlist;
		reset($postlist);
		$first = current($postlist);

		if($first['first']) {
			$return = <<<ttt
<script type="text/javascript">var auc_list_tmp = $('auc_list_tmp');if(auc_list_tmp !== null){document.write(auc_list_tmp.innerHTML);auc_list_tmp.innerHTML='';}
function lalala(){
	ajaxget('plugin.php?id=auction:involve&operation=view&tid={$first[tid]}&page=1', 'list_ajax');
	$('list_ajax').style.display = 'block';
}
if($('list_ajax')){setTimeout('lalala()', 1000);}
</script>
ttt;
			return array($return);
		} else {
			return array();
		}

	}
}
class plugin_auction_home extends plugin_auction {
	/**
	 * 修改个人积分记录中的显示
	 */
	function spacecp_credit_bottom_output(){
		global $_G;
		lang('spacecp');
		$_G['lang']['spacecp']['logs_credit_update_AUC'] = lang('plugin/auction', 'm_join_clog');

	}
}
?>
