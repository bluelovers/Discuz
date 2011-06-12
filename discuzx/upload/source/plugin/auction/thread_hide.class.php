<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-03-17 10:36:18  zhouxingming
 *
 * */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class plugin_auction{
	function deletethread($param) {

		if($param['step'] == 'delete') {
		
		} elseif($param['step'] == 'check') {
		
		}
	}

}

class plugin_auction_forum extends plugin_auction {
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
	function spacecp_credit_bottom_output(){
		global $_G;
		lang('spacecp');
		$_G['lang']['spacecp']['logs_credit_update_AUC'] = lang('plugin/auction', 'm_join_clog');

	}
}
?>
