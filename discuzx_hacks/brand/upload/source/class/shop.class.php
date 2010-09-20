<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: shop.class.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

class shop {

	private $db = null;
	var $shopid = null;
	var $uid = null;
	var $grade = null;
	var $status = null;
	var $cat_id = null;
	var $cat_name = null;
	var $validity_end = null;
	var $model_date = null;

	function __construct($shopid) {
		global $_G, $_SGLOBAL;
		if(!empty($shopid)){
			$shop = $this->get_shop_by_shopid($shopid);
		}elseif(!empty($_SGLOBAL['panelinfo'])){
		$shop = $this->set_model_data($_SGLOBAL['panelinfo']);
		return $_SGLOBAL['panelinfo'];
		}
		$this->check_status();
		return $shop;
	}

	function shop() {
		$this->__construct();
	}

	/**
	 * 重置店的屬性
	 * */
	function __init_status($grade=null){
		if(!empty($grade)){
			$this->grade = $grade;
		}

		if($this->grade < 2){
			$this->status = 'new';
		}elseif($this->grade > 2){
			$this->status = 'normal';
		}else{
			$this->status = 'close';
		}
	}

	/**
	 * 檢查店的狀態
	 */
	function check_status (){
		global $_G, $_SGLOBAL;
		if($this->status == 'close'){
			return false;
		}

		if(($this->shopid > 0) && ($this->validity_end != 0) && ($this->validity_end < $_G['timestamp'])){
			$this->close_shop();
			return false;
		}else{
			return true;
		}
	}

	/**
	 * 關閉一個店
	 */
	function close_shop($shopid=0){
		if($shopid != 0){
			$this->get_shop_by_shopid($shopid);
		}
		DB::query('update '.tname('shopitems').' set grade=2 where itemid='.$this->shopid);
	}

	function get_status (){
		return $this->status;
	}

	/**
	 * 查詢已經發佈的計數
	 *
	 * @param unknown_type $mname
	 * @param unknown_type $shopid
	 */
	function get_item_num($mname, $shopid) {
		return DB::result_first('select itemnum_'.$mname.' from '.DB::table('shopitems').' where itemid='.$shopid);
	}

	/**
	 * 更新商舖的某個模型發佈計數
	 *
	 * @param string $mname 模型
	 * @param int $shopid
	 * @param int $opt_num 要增加或者減少的數量，減少用負數
	 */
	function update_item_num($mname, $shopid, $opt_num = 1) {
		$opt_num = self::get_item_num($mname, $shopid) + $opt_num;
		// 因為操作數可以為負值，所以有為0的情況，這種情況說明現在的數據庫已經出現問題了
		if ($opt_num < 0) {
			$opt_num = 0;
		}

		$sql = "update ".DB::table("shopitems")." set itemnum_".$mname." = " . $opt_num . " where itemid=".$shopid;

		DB::query($sql);
	}

	/**
	 * 根據UID得到SHOP模型
	 * Param : $uid 用戶UID
	 * Return : shop 模型
	 */
	function get_shop_by_uid($uid) {
		return $this->get_shop('si.uid='.$uid);
	}

	/**
	 * 根據Itemid查詢商舖ID
	 *
	 * @param unknown_type $mname
	 * @param unknown_type $itemid
	 */
	function get_shopid_by_itemid($mname, $itemid) {
		$res = DB::result_first("select shopid from ".DB::table($mname.'items'). " where itemid=".$itemid);
		return $res;
	}

	// 得到shop信息
	function get_shop_by_shopid($shopid) {
		return $this->get_shop('si.itemid='.$shopid);
	}

	/**
	 * 列出用戶擁有的商舖列表
	 */
	function ls_myshops($uid = 0) {
		global $_G, $_BCACHE, $_SBLOCK;
		$shops = array();
		if ($uid == 0) $uid = $_G['uid'];
		$_BCACHE->cachesql('myshops', 'SELECT itemid, subject, grade FROM '.tname('shopitems').' WHERE uid=\''.$_G['uid'].'\' ORDER BY itemid ASC', 0, 0, 100, 0, 'sitelist', 'shop');
		foreach($_SBLOCK['myshops'] as $item) {
			$item['briefsubject'] = cutstr($item['subject'], 12);
			$shops[$item['itemid']] = $item;
		}
		return $shops;
	}

	function get_shop($where,$_get_item='si.*,sm.*'){
		if(!empty($where)){
			$where = ' where '.$where;
		}
		$sql = 'select ' . $_get_item . '
				from '.tname('shopitems').' si
				INNER JOIN  '.tname('shopmessage').' sm
				ON si.itemid = sm.itemid '.$where . ' limit 1';
		$shop_item = DB::fetch(DB::query($sql));
		if(empty($shop_item)){
			return null;
		}else{
			$this->set_model_data($shop_item);
			return $shop_item;
		}
	}

	/**
	 * 填充店模型數據
	 * */
	function set_model_data($data){
		$this->model_date = $data;
		$this->shopid = $data['itemid'];
		$this->uid  = $data['uid'];
		$this->grade = $data['grade'];
		$this->cat_id = $data['catid'];
		$this->validity_end = intval($data['validity_end']);
		$this->__init_status();
	}

	function set_shopid($shopid) {
		$this->shopid = $shopid;
	}

	function get_shopid($shopid) {
		$this->shopid = $shopid;
	}

	function set_uid($uid){
		$this->uid = $uid;
	}

	function get_uid($uid){
		$this->uid = $uid;
	}
}
?>