<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_luckypost {
	var $open = '';
	var $trigger = array();
	var $msgforward = array();
	var $groupon = false;
	var $forumon = false;
	var $probability = 0;
	var $rprobability = 0;
	var $iflucky = false;
	var $event = array();
	var $unsetkey = array();

	function plugin_luckypost() {
		global $_G;
		$luckgroups = $luckfids = $rewards = $punishs = array();

		$rewards = explode("\n", str_replace(array("\r\n", "\r"), "\n", $_G['cache']['plugin']['luckypost']['rewardevent']));
		foreach($rewards as $reward) {
			$this->event[1][] = explode('|', $reward);
		}
		$punishs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $_G['cache']['plugin']['luckypost']['punishevent']));
		foreach($punishs as $punish) {
			$this->event[0][] = explode('|', $punish);
		}
		$luckgroups = (array)unserialize($_G['cache']['plugin']['luckypost']['luckgroups']);
		$luckfids = (array)unserialize($_G['cache']['plugin']['luckypost']['luckfids']);
		$this->groupon = in_array('', $luckgroups) ? TRUE : (in_array($_G['member']['groupid'], $luckgroups) ? TRUE : FALSE);
		$this->forumon = in_array('', $luckfids) ? TRUE : (in_array($_G['fid'], $luckfids) ? TRUE : FALSE);
		$this->open = $_G['cache']['plugin']['luckypost']['isopen'];
		$this->probability = $_G['cache']['plugin']['luckypost']['probability'] - 0;
		$this->rprobability = $_G['cache']['plugin']['luckypost']['rprobability'] - 0;
		$this->trigger = $_G['cache']['plugin']['luckypost']['threadonly'] ? array('post_newthread_succeed') : array('post_newthread_succeed', 'post_reply_succeed');
	}
}

class plugin_luckypost_forum extends plugin_luckypost {

	function post_luckypost_message($a) {
		global $_G;

		if($this->open && $this->groupon && $this->forumon && in_array($a['param']['0'], $this->trigger)) {
			$this->iflucky = $this->lottery($this->probability);
			if($this->iflucky && $a['param']['2']['pid']) {
				$ifreward = $this->lottery($this->rprobability);
				$maxnum = count($this->event[$ifreward]) - 1;
				$eventid = $this->randomnum(0, $maxnum);
				$this->runthelottery($ifreward, $eventid, $a['param']['2']);
			}
		}
	}

	function viewthread_postbottom_output() {
		global $_G, $postlist;
		$pids = $luckylist = $luckyevent = array();

		foreach($postlist as $post) {
			$pids[] = $post['pid'];
		}
		$inpid = dimplode($pids);
		$query = DB::query("SELECT * FROM ".DB::table("common_plugin_luckypost")." WHERE pid IN ({$inpid})");
		while($result = DB::fetch($query)) {
			$member = getuserbyuid($result['uid']);
			$event = $result['credits'] > 0 ? 1 : 0;
			$extcredits = $_G['setting']['extcredits'][$this->event[$event][$result['eventid']][0]]['img'].$_G['setting']['extcredits'][$this->event[$event][$result['eventid']][0]]['title'];
			$result['credits'] = abs($result['credits']).' '.$_G['setting']['extcredits'][$this->event[$event][$result['eventid']][0]]['unit'];
			$getmsg = str_replace(array('{username}', '{credit}', '{extcredits}'), array($member['username'], $result['credits'], $extcredits), $this->event[$event][$result['eventid']][2]);
			$luckylist[$result['pid']] = $getmsg;
		}
		foreach($pids as $pid) {
			$luckyevent[] = $luckylist[$pid] ? lang('plugin/luckypost', 'luckyshow', array('$luckyevent' => $luckylist[$pid])) : '';
		}
		return $luckyevent;
	}

	function randomnum($min = 0, $max = 100) {
		PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
		$num = mt_rand($min, $max);
		return $num;
	}

	function lottery($probability) {
		$random = ($this->randomnum(1, 10000) / 10000);
		if($probability >= $random) {
			return true;
		} else {
			return false;
		}
	}

	function runthelottery($event, $eventid, $ids) {
		global $_G;
		$num = $data = array();
		$creditdata = '';

		$num = explode(',', $this->event[$event][$eventid][1]);
		$creditdata = $this->randomnum(abs($num[0]), abs($num[1]));
		$creditdata = $event ? $creditdata : '-'.$creditdata;
		if(intval($this->event[$event][$eventid][0])) {
			$dataarr = array('extcredits'.$this->event[$event][$eventid][0] => $creditdata);
			//$getmsg_utf8 = diconv($getmsg, CHARSET, 'utf-8');
			//updatemembercount($_G['uid'], $dataarr, 1, '', 0, $getmsg_utf8);
			updatemembercount($_G['uid'], $dataarr);
			$data = array(
				'tid' => $ids['tid'],
				'pid' => $ids['pid'],
				'uid' => $_G['uid'],
				'extcredit' => $this->event[$event][$eventid][0],
				'credits' => $creditdata,
				'dateline' => TIMESTAMP,
				'eventid' => $eventid,
			);
			DB::insert('common_plugin_luckypost', $data);
			$logisexist = DB::fetch_first("SELECT uid FROM ".DB::table('common_plugin_luckypostlog')." WHERE uid='{$_G['uid']}' LIMIT 1");
			if(!$logisexist) {
				DB::query("INSERT INTO ".DB::table('common_plugin_luckypostlog')." (uid, goodtimes, badtimes) VALUES ('{$_G['uid']}', '0', '0')");
			}
			$setsql = $event ? "goodtimes = goodtimes+1" : "badtimes = badtimes+1";
			DB::query("UPDATE ".DB::table('common_plugin_luckypostlog')." SET $setsql WHERE uid = '{$_G['uid']}' LIMIT 1");
		}
	}
}


?>