<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-09-02 10:26:07 zhouxingming Comsenz Inc.
 *	Description:插件钩子文件
 *
 * */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('AUCTION_NAME', lang('plugin/auction', 'auction'));
define('AUCTION_BUTTONTEXT', lang('plugin/auction', 'post_auction'));
define('AUCTION_ICON', 'source/plugin/auction/images/auction.gif');
loadcache('plugin');

class threadplugin_auction {
	var $name = AUCTION_NAME;
	var $iconfile = AUCTION_ICON;
	var $buttontext = AUCTION_BUTTONTEXT;
	var $auc;

	/**
	 * 发帖页面显示
	 */
	function newthread($fid) {
		global $_G;

		if($_G['cache']['plugin']['auction']['auc_type1'] || $_G['cache']['plugin']['auction']['auc_type2']) {
			include template('auction:auction_newthread');
			return $return;
		}

	}

	/**
	 * 发帖提交,在帖子插入之前进行相关的验证
	 */
	function newthread_submit($fid) {
		global $_G,$modnewthreads,$displayorder,$auc;

		if($_G['cache']['plugin']['auction']['auc_type1'] || $_G['cache']['plugin']['auction']['auc_type2'] || $_G['cache']['plugin']['auction']['auc_type3']) {

			$auc = $this->check_gpc();
			if(empty($_G['cache']['plugin']['auction']["auc_type{$auc[typeid]}"])) {
				ashowmessage('m_type_invalide');
			}
			$auc['name'] = cutstr($auc['name'], 50);
			$auc['name'] = censor(dhtmlspecialchars($auc['name']));
			if(empty($auc['auctionaid'])) {
				ashowmessage('m_no_pic');
			}
			$modnewthreads = censormod($auc['name']) ? 1 : 0;
			$displayorder = $modnewthreads ? -2 : 0;
		}
	}

	/**
	 * 发帖提交,在帖子插入之后进行相关处理
	 */
	function newthread_submit_end($fid, $tid) {
		global $_G,$pid,$auc;

		$aucaid = $extra = 0;
		$sql = '';
		if($auc['auctionaid']) {
			$attachtable = DB::result_first("SELECT tableid FROM ".DB::table('forum_attachment')." WHERE aid='$auc[auctionaid]'");

			!$attachtable && $attachment = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment_unused')." WHERE aid='$auc[auctionaid]' AND uid='$_G[uid]' AND isimage='1'");
			
			$attachtable = $attachtable == 127 ? 'unused' : $attachtable;
			($attachtable && empty($attachment)) && $attachment = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment_'.$attachtable)." WHERE aid='$auc[auctionaid]' AND uid='$_G[uid]' AND isimage='1'");
			if(empty($attachment)) {
				ashowmessage('m_no_pic');
			}
			if($attachtable == 'unused') {
				convertunusedattach($auc['auctionaid'], $tid, $pid);
			}
			$tableid = DB::result_first("SELECT posttableid FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
			if(!$tableid) {
				$tablename = 'forum_post';
			} else {
				$tablename = "forum_post_$tableid";
			}
			DB::query("UPDATE ".DB::table('forum_thread')." SET attachment=2 WHERE tid='$tid'");
			DB::query("UPDATE ".DB::table($tablename)." SET attachment=2 WHERE pid='$pid'");

			$aucaid = 1;
			$threadimage = DB::fetch_first("SELECT tid, pid, attachment, remote FROM ".DB::table(getattachtablebyaid($aid))." WHERE aid='$aid'");
			if(setthreadcover(0, 0, $auc['auctionaid'])) {
				$threadimage = daddslashes($threadimage);
				DB::delete('forum_threadimage', "tid='$threadimage[tid]'");
				DB::insert('forum_threadimage', array(
					'tid' => $threadimage['tid'],
					'attachment' => $threadimage['attachment'],
					'remote' => $threadimage['remote'],
				));
			}
		} else {
			ashowmessage('m_no_pic');
		}
		if($auc['typeid'] == 1) {
			$auc['typeid'] = 1;
			$extra = 1;
		} elseif($auc['typeid'] == 2) {
			$auc['typeid'] = 1;
			$extra = 0;
		} elseif($auc['typeid'] == 3) {
			$auc['typeid'] = 2;
			$extra = 0;
		}

		DB::insert('plugin_auction', array(
			'tid' => $tid,
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'aid' => ($aucaid ? $auc['auctionaid'] : 0),
			'status' => 0,
			'extid' => $_G['cache']['plugin']['auction']['auc_extcredit'],
			'typeid' => $auc['typeid'],
			'virtual' => $auc['virtual'],
			'name' => $auc['name'],
			'number' => $auc['virtual'] ? count($auc['message']) : $auc['number'],
			'ext_price' => $auc['ext_price'],
			'real_price' => $auc['real_price'],
			'base_price' => $auc['base_price'],
			'delta_price' => $auc['delta_price'],
			'starttimefrom' => $auc['starttimefrom']+rand(0,30),
			'starttimeto' => $auc['starttimeto'],
			'extra' => $extra,
		));
		if($auc['virtual']) {
			foreach($auc['message'] as $message) {
				$sql .= "(null,'{$tid}','".daddslashes($message)."',''),";
			}
			$sql = trim($sql, ',');
			DB::query("INSERT INTO ".DB::table('plugin_auction_message')." VALUES {$sql}");
		}

	}

	/**
	 * 编辑页面
	 */
	function editpost($fid, $tid) {
		global $_G;

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE tid='$tid'")) {
			$auction = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
			$auction['starttimefrom'] = dgmdate($auction['starttimefrom'], 'Y-m-d H:i');
			$auction['starttimeto'] = dgmdate($auction['starttimeto'], 'Y-m-d H:i');
			if($auction['aid']) {
				$auctionatt = DB::fetch_first("SELECT remote,attachment,thumb FROM ".DB::table(getattachtablebytid($tid))." WHERE aid='{$auction[aid]}'");
				if($auctionatt['remote']) {
					$auctionatt['attachment'] = $_G['setting']['ftp']['attachurl'].'forum/'.$auctionatt['attachment'];
					$auctionatt['attachment'] = substr($auctionatt['attachment'], 0, 7) != 'http://' ? 'http://'.$auctionatt['attachment'] : $auctionatt['attachment'];
				} else {
					$auctionatt['attachment'] = $_G['setting']['attachurl'].'forum/'.$auctionatt['attachment'];
				}
			}
		} else {
			return ' ';
		}

		include template('auction:auction_newthread');
		return $return;
	}

	/**
	 * 编辑提交
	 */
	function editpost_submit($fid, $tid) {
		global $_G,$modnewthreads,$displayorder,$auc;
		$this->getauc_gpc();
		$auc = $this->auc;
	}

	/**
	 * 编辑修改,仅允许修改到期时间
	 */
	function editpost_submit_end($fid, $tid) {
		global $_G,$auc;

		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE tid='$tid'")) {
			return ' ';
		} else {
		
			if($auc['auctionaid'] && DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_attachment_unused')." WHERE aid='{$auc[auctionaid]}' AND uid='{$_G[uid]}'")) {
				$aid = DB::result_first("SELECT aid FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
				if($aid) {
					$att = DB::fetch_first("SELECT aid,tid,tableid FROM ".DB::table('forum_attachment')." WHERE aid='$aid'");
					if($att['tableid']) {
						$attach = DB::fetch_first("SELECT tid, pid, attachment, thumb, remote, aid FROM ".DB::table('forum_attachment_'.$att['tableid'])." WHERE aid='$aid'");
						dunlink($attach);
						DB::query("DELETE FROM ".DB::table('forum_attachment_'.$att['tableid'])." WHERE aid='$aid'");

					}

				}
				DB::query("UPDATE ".DB::table('plugin_auction')." SET aid='{$auc[auctionaid]}' WHERE tid='$tid'");
				DB::query("UPDATE ".DB::table('forum_thread')." SET attachment=2 WHERE tid='$tid'");
				convertunusedattach($auc['auctionaid'], $tid, $_G['gp_pid']);
			}
			if($auc['starttimeto']) {
				$auction = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
				if($auc['starttimeto'] < $auction['starttimeto'] || $auc['starttimeto'] < $_G['timestamp'] || ($auc['starttimeto'] - $auction['starttimefrom'] > 7776000)) {
					showmessage(lang('plugin/auction', 'm_delay_time_error'), '', array('mintime' => dgmdate(max($auction['starttimeto'], $_G['timestamp'])),'maxtime' => dgmdate($auction['starttimefrom'] + 7776000)));
				} else {
					DB::query("UPDATE ".DB::table('plugin_auction')." SET starttimeto='{$auc[starttimeto]}' WHERE tid='$tid'");
				}
			}
		}
	}

	/**
	 * 看帖页面
	 */
	function viewthread($tid) {
		global $_G,$skipaids,$thread;

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE tid='$tid'")) {

			$auction = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
			$notstart = $auction['starttimefrom'] > $_G['timestamp'];
			$auction['js_timeto'] = $auction['status'] ? '01/01/1970 00:00' : dgmdate($auction['starttimeto'], 'm/d/Y H:i:s');
			$auction['js_timefrom'] = $auction['status'] ? '01/01/1970 00:01' : dgmdate($auction['starttimefrom'], 'm/d/Y H:i:s');
			$auction['js_timenow'] = TIMESTAMP;
			$auction['starttimefrom'] = dgmdate($auction['starttimefrom'], 'Y-m-d H:i:s');
			$auction['starttimeto_0'] = $auction['starttimeto'];
			$auction['starttimeto'] = dgmdate($auction['starttimeto'], 'Y-m-d H:i:s');
			$auction['typeid'] == 2 && $auction['top_price'] = !empty($auction['top_price']) ? $auction['top_price'] : $auction['base_price'];
			$auction['extid'] = empty($auction['extid']) ? $_G['cache']['plugin']['auction']['auc_extcredit'] : $auction['extid'];
			if($auction['aid']) {
				$auctionatt['attachment'] = getforumimg($auction['aid'], 0, 250, 300);
				$auctionatt['encodeaid'] = aidencode($auction['aid']);
				$skipaids[] = $auction['aid'];
			}
			DB::query("UPDATE ".DB::table('plugin_auction')." SET hot=hot+1 WHERE tid='$tid'");
			$showmobile = ($_G['uid'] == $auction['uid'] || in_array($_G['adminid'], array(1,2)));

			if($auction['typeid'] == 1 && $auction['extra'] == 1) {
				$auction['ctypeid'] = 1;
			} elseif($auction['typeid'] == 1 && $auction['extra'] == 0) {
				$auction['ctypeid'] = 2;
			} elseif($auction['typeid'] == 2) {
				$auction['ctypeid'] = 3;
			}

		} else {
			return ' ';
		}

		include template('auction:auction_viewthread');
		return $return;
	}
	/**
	 * 进行变量初始化
	 * @return array 提交数据中有关的变量
	 */
	function getauc_gpc() {

		$auc['typeid'] = getgpc('auc_type');
		$auc['name'] = cutstr(trim(getgpc('auc_name')), 40);
		$auc['auctionaid'] = intval(getgpc('auctionaid'));		
		$auc['auctionaid_url'] = getgpc('auctionaid_url');
		$auc['starttimefrom'] = strtotime(getgpc('auc_starttimefrom'));
		$auc['starttimeto'] = strtotime(getgpc('auc_starttimeto'));
		$auc['ext_price'] = intval(getgpc('auc_ext_price'));
		$auc['real_price'] = intval(getgpc('auc_real_price'));
		$auc['base_price'] = intval(getgpc('auc_base_price'));
		$auc['delta_price'] = intval(getgpc('auc_delta_price'));
		$auc['number'] = intval(getgpc('auc_number'));
		$auc['auc_type1_'] = intval(getgpc('auc_type1_'));
		$auc['virtual'] = intval(getgpc('auc_virtual')) ? 1 : 0;
		$auc['message'] = str_replace("\r", '', trim(getgpc('auc_message')));
		$auc['message'] = array_filter(explode("\n", $auc['message']));

		$this->auc = $auc;

	}

	/**
	 * 检测提交的变量
	 */
	function check_gpc() {
		$this->getauc_gpc();
		$auc = $this->auc;

		if(empty($auc['typeid']) || !in_array($auc['typeid'], array(1, 2, 3))) {
			ashowmessage('m_type_invalide');
		}
		if(empty($auc['starttimefrom']) || empty($auc['starttimeto'])) {
			ashowmessage('m_time_invalide');
		}
		if(empty($auc['name'])) {
			ashowmessage('m_name_invalide');
		}
		if(empty($auc['real_price']) || $auc['real_price'] <= 0) {
			ashowmessage('m_real_price_invalide');
		}


		if($auc['virtual']) {
			if(empty($auc['message']) || !count($auc['message'])) {
				ashowmessage('m_message_ivalide');
			}
		} else {
			if(empty($auc['number']) || $auc['number'] <= 0) {
				ashowmessage('m_number_invalide');
			}
		}
		if($auc['typeid'] == 1 || $auc['typeid'] == 2) {
			if(empty($auc['ext_price']) || $auc['ext_price'] <= 0) {
				ashowmessage('m_ext_price_invalide');
			}
		}

		if($auc['typeid'] == 3) {
			$auc['ext_price'] = NULL;
			if(empty($auc['base_price']) || $auc['base_price'] <= 0) {
				ashowmessage('m_base_price_invalide');
			}
			if(empty($auc['delta_price']) || $auc['delta_price'] <= 0) {
				ashowmessage('m_delta_price_invalide');
			}
		}

		//if($auc['starttimeto'] - $auc['starttimefrom'] <= 3600 || $auc['starttimeto'] - $auc['starttimefrom'] > 7776000) {
		if($auc['starttimeto'] - $auc['starttimefrom'] <= 120 || $auc['starttimeto'] - $auc['starttimefrom'] > 7776000) {
			ashowmessage('m_time_too_short');
		}
		return $auc;
	}

}

/**
 * 自定义showmessage函数
 */
function ashowmessage($str, $url = '') {
	showmessage(lang('plugin/auction', $str), $url);
}
?>
