<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: data_cache.class.php 4443 2010-09-14 10:00:24Z fanshengshuai $
 */
if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

class bcache {

	public $MC; //Memcached
	public $TC; //TokyoCabinet
	protected $allowcache = 1;
	protected $cachegrade = 1;
	protected $cachemode = 'database';
	protected $subs = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	protected $mkcaches = array();
	protected $mkcachedirs = array();
	protected $mkcachetables = array();

	/**
	 * 初始化
	 * @param $cachemode，緩存類型，文件、數據庫、memcache等
	 * @param $cachegrade, 數據表緩存分表等級
	 * @param $allowcache，是否開啟緩存
	 * @return true or false
	 */
	public function __construct($cachemode='database', $cachegrade=1, $allowcache=1) {
		$this->allowcache = intval($allowcache);
		$this->cachemode = $cachemode;
		$this->cachegrade = intval($cachegrade)>0 && intval($cachegrade)<4?intval($cachegrade):1;
		if($this->allowcache) {
			switch($cachemode) {
				case 'file':
					$this->start_file();
					break;
				case 'database':
					$this->start_database();
					break;
				case 'memcache':
					$this->start_memcache();
					break;
				case 'tokyocabinet':
					$this->start_tokyocabinet();
					break;
				default:
					$this->cachemode = 'file';
					$this->start_database();
			}
			return true;
		} else {
			return false;
		}
	}

	public function getallowcache() {
		if($this->allowcache) {
			$allowcache = 'On';
		} else {
			$allowcache = 'Off';
		}
		return $allowcache;
	}

	public function getcachemode() {
		$cachemode = ucfirst($this->cachemode);
		return $cachemode;
	}

	/**
	 * 設置緩存，隊列模式，在類銷毀的時候才實際寫入緩存。默認情況緩存永久不更新，當後台發生操作時更新對應頁面，對應功能的部分緩存
	 * @param $cachekey，緩存key
	 * @param $cachevalue, 緩存值
	 * @param $updatetime, 過期到達時間（當前時間+緩存有效時間）
	 * @param $pagetype， 緩存所在頁面類型
	 * @param $usetype， 緩存所在模塊功能類型
	 * @param $shopid， 緩存所在店舖id
	 * @param $infoid， 緩存所在信息id
	 * @return 當前隊列中等待寫入的緩存數 or false
	 */
	public function set($cachekey, $cachevalue, $updatetime=0, $pagetype='other', $usetype='other', $shopid=0, $infoid=0) {
		global $_G, $_SGLOBAL;
		if($this->allowcache) {
			if(!in_array($pagetype, array('index', 'sidebar', 'sitelist', 'storelist', 'detail', 'other'))) {
				$pagetype = 'other';
			}
			if(!in_array($usetype, array('shop', 'attr', 'good', 'consume', 'notice', 'album', 'photo', 'score', 'comment', 'nav', 'other', 'groupbuy', 'brandlinks'))) {
				$usetype = 'other';
			}
			$shopid = intval($shopid);
			$infoid = intval($infoid);
			$updatetime = intval($updatetime)<=$_G['timestamp']?$_G['timestamp']+3600*24*30+rand(0, 1000):intval($updatetime);
			$cachesub = substr($cachekey, 0, $this->cachegrade);
			$this->mkcachetables[$cachesub] = tname('cache_'.$cachesub);
			$this->mkcachedirs[$cachesub] = B_ROOT.'./data/cache/block/'.$cachesub;
			$this->mkcaches[] = array(
				'cachekey' => $cachekey,
				'uid' => $_G['uid'],
				'cachevalue' => $cachevalue,
				'updatetime' => $updatetime,
				'pagetype' => $pagetype,
				'usetype' => $usetype,
				'shopid' => $shopid,
				'infoid' => $infoid,
			);
			return count($this->mkcaches);
		} else {
			return false;
		}
	}

	/**
	 * 獲取緩存，本函數不返回緩存值，不建議直接使用，盡量用cachesql
	 * @param $cachekey，緩存key
	 * @return cachekey，使用時請用$_SBLOCK['cachename']或者$_SBLOCK['cachekey']
	 */
	public function get($cachekey) {
		if($this->allowcache) {
			$func_name = 'get_'.$this->cachemode;
			return $this->$func_name($cachekey);
		} else {
			return false;
		}
	}

	/**
	 * 刪除緩存
	 * @param $cachekey，緩存key，或者key的數組
	 * @return cachekey，刪除個數
	 */
	public function delete($cachekey='') {
		if($this->allowcache) {
			$delcount = 0;
			$func_name = 'delete_'.$this->cachemode;
			if(is_array($cachekey)) {
				foreach($cachekey as $thiskey) {
					if(trim($cachekey)) {
						$delcount += $this->$func_name($thiskey);
					}
				}
			} elseif(trim($cachekey)) {
				$delcount += $this->$func_name(trim($cachekey));
			}
			return $delcount;
		} else {
			return false;
		}
	}

	/**
	 * 按分片緩存類型刪除緩存
	 * @param $pagetype，頁面類型
	 * @param $usetype，物件類型
	 * @param $shopid，店舖ID
	 * @param $infoid，信息ID
	 * @return cachekey，刪除個數
	 */
	public function deltype($pagetype='all', $usetype='all', $shopid=0, $infoid=0) {
		global $_G;
		if($this->allowcache) {
			$where = array();
			$delcount = 0;

			if(($pagetype=='all' && $usetype=='all' && !$shopid && !$infoid)) {
				//按類型刪除緩存需要一些必填參數組合
				return false;
			}

			$pagetype!='all' && $where[] = "pagetype='$pagetype'";
			$usetype!='all' && $where[] = "usetype='$usetype'";
			$shopid && $where[] = "shopid='$shopid'";
			$infoid && $where[] = "infoid='$infoid'";

			$wheresql = implode(' AND ', $where);

			$wheresql && $wheresql = ' WHERE '.$wheresql;

			$query = DB::query('SELECT cachekey FROM '.tname('cachenotes').$wheresql);
			while($result = DB::fetch($query)) {
				$delcount += $this->delete($result['cachekey']);
			}
			DB::query('DELETE FROM '.tname('cachenotes').$wheresql, 'UNBUFFERED');
			return $delcount;
		} else {
			return false;
		}
	}

	public function flush() {
		global $_G;
		if($this->allowcache) {
			$func_name = 'flush_'.$this->cachemode;
			DB::query('TRUNCATE TABLE '.tname('cachenotes'), 'UNBUFFERED');
			return $this->$func_name();
		} else {
			return false;
		}
	}

	public function cachesql($blockname, $sql, $cachetime=0, $is_perpage=0, $limit_or_perpage=10, $limit_start=0, $pagetype='other', $usetype='other', $shopid=0, $infoid=0) {
		if($blockname && $sql) {
			$blockcodearr = array();
			$limit_or_perpage = intval($limit_or_perpage)>0?intval($limit_or_perpage):10;
			$limit_start = intval($limit_start);
			$cachetime = intval($cachetime)>0?intval($cachetime):0;
			$sql = getblocksql($sql);
			$blockcodearr[] = 'sql/'.rawurlencode($sql);
			if($is_perpage) {
				$blockcodearr[] = 'perpage/'.$limit_or_perpage;
			} else {
				$blockcodearr[] = 'limit/'.$limit_start.','.$limit_or_perpage;
			}
			$cachetime && $blockcodearr[] = 'cachetime/'.$cachetime;
			$blockcodearr[] = 'cachename/'.rawurlencode($blockname);
			$blockcodearr[] = 'pagetype/'.rawurlencode($pagetype);
			$blockcodearr[] = 'usetype/'.rawurlencode($usetype);
			$blockcodearr[] = 'shopid/'.intval($shopid);
			$blockcodearr[] = 'infoid/'.intval($infoid);
			$returnst = block('sql', implode('/', $blockcodearr));
			if($this->allowcache) {
				return $returnst;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 取得店舖信息，分片緩存
	 * @param $shopid，店舖id
	 * @return 店舖信息以及評分信息
	 */
	public function getshopinfo($shopid) {
		global $_G, $_SBLOCK;
		$shopinfo = array();
		$shopid = intval($shopid);
		if($shopid>0) {
			//數據查詢，拆分SQL，分片緩存
			$sql = 'SELECT * FROM '.tname('shopitems').' i INNER JOIN '.tname('shopmessage')." m ON i.itemid=m.itemid WHERE i.itemid='$shopid' AND i.grade>2";
			$this->cachesql('shopinfo', $sql, 0, 0, 1, 0, 'detail', 'shop', $shopid, 0);
			$sql = 'SELECT * FROM '.tname('scorestats')." WHERE itemid='$shopid'";
			$this->cachesql('shopscore', $sql, 0, 0, 1, 0, 'detail', 'score', $shopid, 0);
			$shopinfo = array_merge((array)$_SBLOCK['shopinfo'][0], (array)$_SBLOCK['shopscore'][0]);
			$shopinfo['thumb'] = str_replace('static/image/nophoto.gif', 'static/image/shoplogo.gif', $shopinfo['thumb']);
			$shopinfo['subjectimage'] = str_replace('static/image/nophoto.gif', 'static/image/shoplogo.gif', $shopinfo['subjectimage']);
			if(!empty($shopinfo['score'])) {
				$shopinfo['roundingscore'] = round($shopinfo['score']/$shopinfo['remarknum']);
			}
		} else {
			$shopinfo = NULL;
		}
		return $shopinfo;
	}
	/**
	 * 取得店舖信息，分片緩存
	 * @param $type，信息類型，good/notice/consume等
	 * @param $itemid，信息id
	 * @param $shopid，店舖id
	 * @return 店舖信息以及評分信息
	 */
	public function getiteminfo($type, $itemid, $shopid) {
		global $_G, $_SBLOCK;
		$iteminfo = array();
		$itemid = intval($itemid);
		$shopid = intval($shopid);
		if($itemid>0 && $shopid>0 && in_array($type, array('good', 'notice', 'consume', 'album', 'photo', 'groupbuy'))) {
			//數據查詢，拆分SQL，分片緩存
			if(in_array($type, array('album', 'photo'))) {
				$joinsql = '';
			} else {
				$joinsql = 'INNER JOIN '.tname($type.'message').' m ON i.itemid=m.itemid';
			}
			$sql = 'SELECT * FROM '.tname($type.'items')." i $joinsql WHERE i.itemid='$itemid' AND i.shopid='$shopid'";
			$this->cachesql('iteminfo', $sql, 0, 0, 1, 0, 'detail', $type, $shopid, $itemid);
			$iteminfo = $_SBLOCK['iteminfo'][0];
			if ($type == "consume") {
				$select_filed = ",downnum";
			}
			$fileds_count = DB::fetch_first("select viewnum" . $select_filed . " from " . tname($type.'items') . " where itemid=".$itemid);
			$iteminfo['viewnum'] = $fileds_count['viewnum'];
			if ($type == "consume") {
				$iteminfo['downnum'] = $fileds_count['downnum'];
			}
		} else {
			$iteminfo = NULL;
		}
		return $iteminfo;
	}

	public function __destruct() {
		global $_G;
		foreach($this->mkcaches as $cachenote) {
			$insertsql = " ('$cachenote[cachekey]', '$cachenote[pagetype]', '$cachenote[usetype]', '$cachenote[shopid]', '$cachenote[infoid]')";
			DB::query('REPLACE INTO '.tname('cachenotes').' (cachekey, pagetype, usetype, shopid, infoid) VALUES '.$insertsql, 'UNBUFFERED');
		}
		if($this->mkcaches) {
			$func_name = 'close_'.$this->cachemode;
			$this->$func_name('cache');
		}
		$this->mkcachedirs = NULL;
		$this->mkcachetables = NULL;
		$this->mkcaches = NULL;
	}

	protected function start_file() {

	}
	protected function start_database() {

	}
	protected function start_memcache() {
		global $_G, $_SC;
		$halt = 1;
		if(class_exists('memcache')) {
			$this->MC = new memcache;
			@include_once(B_ROOT.'./config_extra.php');
			foreach($_SC['memcache'] as $mem) {
				if($halt && @$this->MC->addServer($mem['host'], $mem['port'])) {
					$halt = 0;
					continue;
				} else {
					@$this->MC->addServer($mem['host'], $mem['port']);
				}
			}
		}
		if($halt) {
			$this->cachemode = 'file';
			return false;
			$this->halt('MEMCACHE Connect Error.');
		}
	}
	protected function start_tokyocabinet() {
		global $_G, $_SC;
		$halt = 1;
		if(class_exists('memcache')) {
			$this->TC = new memcache;
			@include_once(B_ROOT.'./config_extra.php');
			foreach($_SC['tokyocabinet'] as $mem) {
				if($halt && @$this->TC->addServer($mem['host'], $mem['port'])) {
					$halt = 0;
					continue;
				} else {
					@$this->TC->addServer($mem['host'], $mem['port']);
				}
			}
		}
		if($halt) {
			$this->cachemode = 'file';
			return false;
			$this->halt('MEMCACHE Connect Error.');
		}
	}

	protected function get_file($cachekey) {
		global $_G, $_SBLOCK;
		$cachefile = B_ROOT.'./data/cache/block/'.substr($cachekey, 0, $this->cachegrade).'/'.$cachekey.'.cache.data';
		if(file_exists($cachefile)) {
			if(@$fp = fopen($cachefile, 'r')) {
				$data = fread($fp,filesize($cachefile));
				fclose($fp);
			}
			$_SBLOCK[$cachekey]['value'] = $data;
			$_SBLOCK[$cachekey]['filemtime'] = filemtime($cachefile);
		}
		return $cachekey;
	}
	protected function get_database($cachekey) {
		global $_G, $_SGLOBAL, $_SBLOCK;
		$thetable = tname('cache_'.substr($cachekey, 0, $this->cachegrade));
		$query = DB::query('SELECT * FROM `'.$thetable.'` WHERE `cachekey` = \''.$cachekey.'\'', 'SILENT');
		while($result = DB::fetch($query)) {
			$_SBLOCK[$result['cachekey']]['value'] = $result['value'];
			$_SBLOCK[$result['cachekey']]['updatetime'] = $result['updatetime'];
		}
		return $cachekey;
	}
	protected function get_memcache($cachekey) {
		global $_G, $_SBLOCK;
		$cachevalue = $this->MC->get($cachekey);
		if($cachevalue!==false) {
			$_SBLOCK[$cachekey]['value'] = $cachevalue;
			return $cachekey;
		}
		return false;
	}
	protected function get_tokyocabinet($cachekey) {
		global $_G, $_SBLOCK;
		$cachevalue = $this->TC->get($cachekey);
		if($cachevalue!==false) {
			$_SBLOCK[$cachekey]['value'] = $cachevalue;
			return $cachekey;
		}
		return false;
	}

	protected function delete_file($cachekey) {
		$cachefile = B_ROOT.'./data/cache/block/'.substr($cachekey, 0, $this->cachegrade).'/'.$cachekey.'.cache.data';
		return @unlink($cachefile);
	}
	protected function delete_database($cachekey) {
		global $_G, $_SGLOBAL;
		$thetable = tname('cache_'.substr($cachekey, 0, $this->cachegrade));
		return DB::query('DELETE FROM `'.$thetable.'` WHERE `cachekey` = \''.$cachekey.'\'', 'SILENT');
	}
	protected function delete_memcache($cachekey) {
		return $this->MC->delete($cachekey);
	}
	protected function delete_tokyocabinet($cachekey) {
		return $this->TC->delete($cachekey);
	}

	protected function flush_file() {
		//文件存儲模式=>全部刪除
		$cachedir = B_ROOT.'./data/cache/block';
		$dirs = sreaddir($cachedir);
		foreach ($dirs as $value) {
			if(is_dir($cachedir.'/'.$value)) {
				$filearr = sreaddir($cachedir.'/'.$value, 'data');
				foreach ($filearr as $subvalue) {
					@unlink($cachedir.'/'.$value.'/'.$subvalue);
				}
			}
		}
		return true;
	}
	protected function flush_database() {
		global $_G;
		DB::query('TRUNCATE TABLE '.tname('cache'));
		//分表
		foreach ($this->subs as $tbl) {
			DB::query('TRUNCATE TABLE '.tname('cache_'.$tbl), 'SILENT');
		}
		return true;
	}
	protected function flush_memcache() {
		return $this->MC->flush();
	}
	protected function flush_tokyocabinet() {
		return $this->TC->flush();
	}

	protected function close_file() {
		foreach ($this->mkcachedirs as $thisdir) {
			if(!is_dir($thisdir)) {
				if(@mkdir($thisdir)) {
					@fopen($thisdir.'/index.htm', 'w');
					fwrite($fp, '');
					fclose($fp);
				}
			}
		}
		foreach ($this->mkcaches as $thiscache) {
			$cachedir = B_ROOT.'./data/cache/block/'.substr($thiscache['cachekey'], 0, $this->cachegrade);
			$cachefile = $cachedir.'/'.$thiscache['cachekey'].'.cache.data';
			if(@$fp = fopen($cachefile, 'w')) {
				fwrite($fp, $thiscache['cachevalue']);
				fclose($fp);
			}
		}

	}
	protected function close_database() {
		global $_G, $dbcharset;

		foreach($this->mkcachetables as $thistable) {
			$enginetype = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(empty($dbcharset)?"":" DEFAULT CHARSET=$dbcharset" ): " TYPE=MYISAM";
			if(strexists($thistable, 'cache')) {
				//表名必須含有cache
				$creatsql = "CREATE TABLE `$thistable` (
					cachekey varchar(16) NOT NULL default '',
					uid mediumint(8) unsigned NOT NULL default '0',
					cachename varchar(20) NOT NULL default '',
					`value` mediumtext NOT NULL,
					updatetime int(10) unsigned NOT NULL default '0',
					PRIMARY KEY  (cachekey)
					) $enginetype";
				DB::query($creatsql, 'SILENT');//創建分表
			}
		}

		foreach($this->mkcaches as $thiscache) {
			$thetable = tname('cache_'.substr($thiscache['cachekey'], 0, $this->cachegrade));
			if(strexists($thetable, 'cache')) {
				//表名必須含有cache
				$insertsql = '(\''.addslashes($thiscache['cachekey']).'\', \''.$thiscache['uid'].'\', \'sql\', \''.addslashes($thiscache['cachevalue']).'\', \''.$thiscache['updatetime'].'\')';
				DB::query('REPLACE INTO '.$thetable.' (cachekey, uid, cachename, value, updatetime) VALUES '.$insertsql, 'UNBUFFERED');
			}
		}

	}
	protected function close_memcache() {
		foreach ($this->mkcaches as $thiscache) {
			$this->MC->set($thiscache['cachekey'], $thiscache['cachevalue'], 0, $thiscache['updatetime']);
		}
		$this->MC->close();
	}
	protected function close_tokyocabinet() {
		foreach ($this->mkcaches as $thiscache) {
			$this->TC->set($thiscache['cachekey'], $thiscache['cachevalue'], 0, $thiscache['updatetime']);
		}
		$this->TC->close();
	}

	protected function halt($message = '') {
		exit($message);
	}
}

?>