<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: groupbuy.class.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

class groupbuy {

	public $groupbuyinfo;

	function __construct() {
	}

	function groupbuy() {
		$this->__construct();
	}

	// 返回所有的參加團購的用戶
	function get_groupby_join_users($uid, $groupbuyid) {
		global $_G, $_SGLOBAL;

		$sql = 'select * from '.tname('groupbuyjoin').' where itemid = \''.$groupbuyid.'\' AND uid = \''.$uid.'\'';
		$user = DB::fetch(DB::query($sql));
		return $user;
	}

	// 斷用戶是否已經參加團購
	function exist_join_user($uid, $groupbuyid) {
		$userinfo = $this->get_my_join_info($uid, $groupbuyid);

		if(empty($userinfo)) {
			return false;
		} else {
			return true;
		}
	}

	// 得到用戶的參與信息
	function get_my_join_info($uid, $groupbuyid) {
		global $_G, $_SGLOBAL;

		$sql = 'select * from '.tname('groupbuyjoin').' where itemid = \''.$groupbuyid.'\' AND uid = \''.$uid.'\'';
		$user = DB::fetch(DB::query($sql));
		return $user;
	}

	// 團購信息
	function get_groupby_info($groupbuyid) {
		global $_G, $_SGLOBAL;

		$sql = "SELECT * FROM ".tname("groupbuyitems")." gi left join ".tname("groupbuymessage")." gm on gi.itemid = gm.itemid where gi.itemid=$groupbuyid";
		return DB::fetch(DB::query($sql));
	}

	// 更改參加人數
	function update_groupby_join_num($groupbuyid, $num = 1) {
		global $_G, $_SGLOBAL, $groupbuy, $_BCACHE;

		$this->groupbuyinfo = $groupbuy;

		$sql = "update ".tname('groupbuyitems')." set buyingnum = buyingnum + " .$num . " where itemid=".$groupbuyid;
		DB::query($sql);

		//是不是人要滿了
		if(!empty($this->groupbuyinfo['groupbuymaxnum']) && $this->groupbuyinfo['buyingnum'] >= ($this->groupbuyinfo['groupbuymaxnum'] - 1)) {
			$this->close_join($groupbuyid);
		}

	}

	// 關閉用戶報名
	function close_join($groupbuyid) {
		$GLOBALS['_SGLOBAL']['db']->query("UPDATE ".tname('groupbuyitems')." SET close = 1 WHERE itemid=".$groupbuyid);
	}

	// 舉行團購的商舖Id
	function get_groupby_shopid($uid, $groupbuyid) {
		global $_G, $_SGLOBAL, $groupbuy;

		$user = $this->get_groupby_join_users($uid, $groupbuyid);

		if(empty($user)) {
			return null;
		} else {
			return $groupbuy['shopid'];
		}

	}

}
?>