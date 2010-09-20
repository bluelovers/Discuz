<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: nav.class.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

class nav {

	/**
	 * 系统默认自定义导航，实例化后会初始化数组
	 *
	 * @var array
	 */
	public $shop_nav_default = array();

	function __construct() {
		$this->shop_nav_default = $this->get_shop_nav_default();
	}

	function nav() {
		$this->__construct();
	}

	/**
	 * 店铺自定义导航是否已经入库
	 *
	 * @param int $shopid  商铺 ID
	 * @param array_or_string $nav_flag 导航标志，可以为数组,为空就用系统默认自定义导航
	 */
	function check_shop_nav($shopid = 0, $nav_flag = '') {
		global $_G, $_SGLOBAL;

		if(empty($nav_flag)) {
			if($shopid == 0) {
				$nav_flag = $this->shop_nav_default;
			} else {
				$nav_flag = $this->get_shop_nav_default($shopid);
			}
		}

		if(is_array($nav_flag)) {
			foreach($nav_flag as $_flag => $_flag_v) {
				$sql = "SELECT * FROM ".tname("nav")." WHERE type='SYS' AND shopid='$shopid' AND flag='$_flag'";

				if(!(DB::result_first($sql))) {
					$sql = "INSERT INTO ".tname("nav")." (type, shopid, available, flag, name) VALUES ('sys','$shopid', 1, '".$_flag_v['flag']."', '".$_flag_v['name']."');";
					DB::query($sql);
				}
			}

		} else {
			$nav_flag = 'flag=\''.$nav_flag.'\'';
		}
		return;
	}

	/**
	 * 商铺的自定义导航
	 *
	 * @param int $shopid 商铺 ID
	 */
	function get_shop_nav($shopid) {
		global $_G, $_SGLOBAL, $_BCACHE, $_SBLOCK;
		
		$nav_default = $this->get_shop_nav_default($shopid);
		$_SGLOBAL['shop_nav'] = array();
		$_BCACHE->cachesql('shopnav', 'SELECT * FROM '.tname('nav')." WHERE (type=\'sys\' OR type=\'shop\') AND shopid='$shopid' AND displayorder>-1 ORDER BY displayorder ASC", 0, 0, 10, 0, 'detail', 'nav', $shopid);
		foreach($_SBLOCK['shopnav'] as $value) {
			if($value['available'] == 1) {
				$value['target'] = $value['target']?' target=\'_blank\'':'';
				$value['style'] = ' style=\''.pktitlestyle($value['highlight']).'\'';
				$value['url'] = $nav_default[$value['flag']]['url'];
				$_SGLOBAL['shop_nav'][$value['flag']]=$value;
				unset($nav_default[$value['flag']]);
			} else {
				unset($nav_default[$value['flag']]);
			}
		}

		foreach($nav_default as $key => $value) {
			if(!isset($rowitems[$key])) {
				$_SGLOBAL['shop_nav'][$key] = $value;
			}
			$_SGLOBAL['shop_nav'][$key]['url'] = $value['url'];
		}
	
		return $_SGLOBAL['shop_nav'];
	}

	/**
	 * 系统默认自定义导航
	 *
	 * @param int $shopid 商铺 ID
	 */
	function get_shop_nav_default($shopid) {
		global $_G, $lang;
		
		if($shopid == 0) {
			$shopid = $GLOBALS['itemid'];
		}
		return array(
				'index'=>array('navid'=>'index','flag'=>'index','name'=>$lang['shopindex'],'type'=>'sys','shopid'=>0,'available'=>'1','url'=>'store.php?id='.$shopid),
				'notice'=>array('navid'=>'notice','flag'=>'notice','name'=>$lang['noticelist'],'type'=>'sys','shopid'=>0,'available'=>'1','url'=>'store.php?id='.$shopid.'&action=notice'),
				'album'=>array('navid'=>'album','flag'=>'album','name'=>$lang['album'],'type'=>'sys','shopid'=>0,'available'=>'1','url'=>'store.php?id='.$shopid.'&action=album'),
				'good'=>array('navid'=>'good','flag'=>'good','name'=>$lang['good'],'type'=>'sys','shopid'=>0,'available'=>'1','url'=>'store.php?id='.$shopid.'&action=good'),
				'consume'=>array('navid'=>'consume','flag'=>'consume','name'=>$lang['coupon'],'type'=>'sys','shopid'=>0,'available'=>'1','url'=>'store.php?id='.$shopid.'&action=consume'),
				'groupbuy'=>array('navid'=>'groupbuy','flag'=>'groupbuy','name'=>$lang['groupbuy'],'type'=>'sys','shopid'=>0,'available'=>'1','url'=>'store.php?id='.$shopid.'&action=groupbuy')
				);
	}
}
?>