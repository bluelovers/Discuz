<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: pm.class.php 4067 2010-07-30 08:38:14Z fanshengshuai $
 */

class pm {

	/**
	 * 新邮件数组
	 */
	public $pm_new = '';


	function __construct() {
		include_once B_ROOT."uc_client/client.php";
		$this->pm_new = $this->check_new();
	}

	function pm() {
		$this->__construct();
	}
	
	/**
	 * 检测是短消息数量
	 */
	function check_new() {
		global $_G;

		// 用户没有登录，返回空
		if($_G['uid'] < 1) return '';
		return uc_pm_checknew($_G['uid'] ,2);
	}

	/**
	 * 查看短消息
	 *
	 * @param string $filter 短消息类型
	 */
	function view_pm($filter) {
		global $_G, $_SC;

		if($_SC['bbs_version'] == 'discuz') {
			$pm_url = $_SC['bbs_url'] . "/pm.php?filter=".$filter;
		} else {
			$pm_url = $_SC['bbs_url'] . "/home.php?mod=space&do=pm&filter=".$filter;
		}
		header("location: ".$pm_url);
	}

}