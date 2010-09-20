<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache.func.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

//更新用户组CACHE
function updateshopgroupcache() {
	global $_G, $_SGLOBAL;

	$_SGLOBAL['shopgrouparr'] = array();
	$highest = true;
	$lower = '';
	$query = DB::query('SELECT * FROM '.tname('shopgroup').' ORDER BY id ASC');
	while ($group = DB::fetch($query)) {
		$_SGLOBAL['shopgrouparr'][$group['id']] = $group;
	}

	$cachefile = B_ROOT.'./data/system/shopgroup.cache.php';
	$cachetext = '$_SGLOBAL[\'shopgrouparr\']='.arrayeval($_SGLOBAL['shopgrouparr']);
	writefile($cachefile, $cachetext, 'php');
}

//更新基本配置CACHE
function updatesettingcache() {
	global $_G, $_SGLOBAL, $_SSCONFIG, $lang;
	$_SSCONFIG = array();

	$query = DB::query('SELECT * FROM '.tname('settings'));
	while ($set = DB::fetch($query)) {
		$_G['setting'][$set['variable']] = $set['value'];
	}

	// 附件设置
	$_G['setting']['attach'] = unserialize($_G['setting']['attach']);

	$_G['setting']['attachmenturlarr'] = explode("\r\n", trim($_G['setting']['attachmenturls']));

	//缩略图设置
	if(empty($_G['setting']['thumbarray'])) {
		$_G['setting']['thumbarray'] = array(
			'news' => array('400','300')
		);
	} else {
		$_G['setting']['thumbarray'] = unserialize($_G['setting']['thumbarray']);
	}

	//读取UC中论坛地址
	require_once(B_ROOT.'./uc_client/client.php');
	$ucapparray = uc_app_ls();
	if(count($ucapparray) > 0) {
		foreach($ucapparray as $apparray) {
			if($apparray['type'] == 'DISCUZ') {
				$_G['setting']['discuz_url'] = $apparray['url'];
				break;
			}
		}
	}

	// 读取导航
	$query = DB::query('SELECT name,flag,url,target,highlight FROM '.tname('nav').' WHERE (type=\'sys\' or type=\'site\') and shopid=0 and available=1 order by displayorder limit 7');
	while($value = DB::fetch($query)){
		$value['ext'] = (($value['target']==1)?' target=\'_blank\'':'').' style=\''.pktitlestyle($value['highlight']).'\'';
		$_G['setting']['site_nav'][$value['flag']]=$value;
	}

	// 会员卡商家导航
	if(empty($_G['setting']['enablecard'])) unset($_G['setting']['site_nav']['card']);

	// make cache
	$cachefile = B_ROOT.'./data/system/config.cache.php';
	$cachetext = '$_G[\'setting\'] = '.arrayeval($_G['setting']);
	writefile($cachefile, $cachetext, 'php');
}

/**
 * 更新cron列表
 */
function updatecronscache() {
	global $_G, $_SGLOBAL;

	$carr = array();
	$query = DB::query('SELECT * FROM '.tname('crons').' WHERE available>0');
	while ($cron = DB::fetch($query)) {
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cron['minute'] = explode("\t", $cron['minute']);
		$carr[$cron['cronid']] = $cron;
	}

	$cachefile = B_ROOT.'./data/system/crons.cache.php';
	$cachetext = '$_SGLOBAL[\'crons\']='.arrayeval($carr);
	writefile($cachefile, $cachetext, 'php');
}

/**
 * 更新计划任务的CACHE
 */
function updatecroncache($cronnextrun=0) {
	global $_G, $_SGLOBAL;

	if(empty($cronnextrun)) {
		$query = DB::query('SELECT nextrun FROM '.tname('crons').' WHERE available>0 AND nextrun>\''.$_G['timestamp'].'\' ORDER BY nextrun LIMIT 1');
		$cronnextrun = DB::result($query, 0);
	}
	if(empty($cronnextrun)) {
		$cronnextrun = $_G['timestamp'] + 2*3600;
	}

	$croncachefile = B_ROOT.'./data/system/cron.cache.php';
	$text = '$_SGLOBAL[\'cronnextrun\']='.$cronnextrun.';';
	writefile($croncachefile, $text, 'php');
}

//更新站点公告
function updateannouncementcache() {
	global $_G, $_SGLOBAL;

	$earr = array();
	$query = DB::query('SELECT * FROM '.tname('announcements').' WHERE starttime < \''.$_G['timestamp'].'\' AND (endtime > \''.$_G['timestamp'].'\' OR endtime = 0) ORDER BY displayorder, starttime DESC, id DESC LIMIT 0,10');
	while ($e = DB::fetch($query)) {
		$earr[] = $e;
	}

	$cachefile = B_ROOT.'./data/system/announcement.cache.php';
	$cachetext = '$_SGLOBAL[\'announcement\']='.arrayeval($earr);
	writefile($cachefile, $cachetext, 'php');
}

//更新分类缓存
function updatecategorycache() {
	global $_G, $_SGLOBAL;
	$types = array('region', 'shop', 'good', 'notice', 'consume', 'album', 'groupbuy');
	foreach($types as $type) {
		updatecategorysingle($type);
	}
}
//
function updatecategorysingle($type) {
	global $_G, $_SGLOBAL;
	$_SGLOBAL[$types.'cates'] = array();
	$query = DB::query('SELECT * FROM '.tname('categories').' WHERE type = \''.$type.'\' ORDER BY upid,displayorder ASC');
	while ($cat = DB::fetch($query)) {
		$_SGLOBAL[$types.'cates'][$cat['catid']] = $cat;
	}
	$cachefile = B_ROOT.'./data/system/'.$type.'category.cache.php';
	$cachetext = '$_SGLOBAL[\''.$type.'cates\']='.arrayeval($_SGLOBAL[$types.'cates']);
	writefile($cachefile, $cachetext, 'php');
}

//更新模型缓存
function updatemodel($type, $value) {
	global $_G, $_SGLOBAL;

	$tarr = $results = $fielddefaultarr = $columnarr = $columnidarr = $linkagedownarr = $categoryarr = $categoryallarr = array();

	$query = DB::query('SELECT * FROM '.tname('models').' WHERE '.$type.' = \''.$value.'\'');
	$results = DB::fetch($query);
	if(!empty($results['fielddefault'])) {
		$tmpvalue = strim(explode("\r\n", $results['fielddefault']));
		if(!empty($tmpvalue)) {
			foreach($tmpvalue as $skey => $svalue) {
				if(!empty($svalue)) {
					$svalue = trim(substr($svalue, strpos($svalue, '=')+1));
					$skey = trim(substr($tmpvalue[$skey], 0, strpos($tmpvalue[$skey], '=')));
					if(in_array($skey, array('subject', 'subjectimage', 'message', 'catid'))) {
						$fielddefaultarr[$skey] = $svalue;
					}
				}
			}
		}
	}
	if(!empty($results)) {
		$query = DB::query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = \''.$results['mid'].'\' ORDER BY displayorder');
		while ($values = DB::fetch($query)) {
			$columnidarr[$values['id']] = $values['fieldname'];
			$columnarr[$values['fieldname']] = $values;
			if($values['formtype'] == 'linkage') {
				$linkagedownarr['down'][$values['upid']] = $values['id'];
				if(!empty($values['fielddata'])) {
					$tmpfielddata = strim(explode("\r\n", $values['fielddata']));
					$tmpinfo = array();
					foreach($tmpfielddata as $skey => $svalue) {
						if(!empty($svalue)) {
							$skey = trim(substr($tmpfielddata[$skey], 0, strpos($tmpfielddata[$skey], '=')));
							$tmpinfo[$skey] = trim(substr($svalue, strpos($svalue, '=')+1));
						}
					}
					$linkagedownarr['info'][$values['fieldname']] = $tmpinfo;
				}
			}
		}

		$query = DB::query('SELECT * FROM '.tname('categories').' WHERE `type`=\''.$results['modelname'].'\' ORDER BY displayorder');
		while($values = DB::fetch($query)) {
			if($values['upid'] == 0) {
				$categoryarr[$values['catid']] = $values['name'];
			}
			$categoryallarr[$values['catid']] = $values;
		}

		$tarr = array(
			'models'	=>	$results,
			'fielddefault'	=>	$fielddefaultarr,
			'columnids'	=>	$columnidarr,
			'linkage'	=>	$linkagedownarr,
			'columns'	=>	$columnarr,
			'categories' => $categoryarr,
			'categoryarr' => $categoryallarr
		);

		$cachefile = B_ROOT.'./data/cache/model/model_'.$results['mid'].'.cache.php';
		$text = '$cacheinfo = '.arrayeval($tarr).';';
		writefile($cachefile, $text, 'php');
		$cachefile = B_ROOT.'./data/cache/model/model_'.$results['modelname'].'.cache.php';
		writefile($cachefile, $text, 'php');
		return $tarr;

	} else {
		return false;
	}
}

/**
 * 更新展示设置缓存
 */
function updatebrandadscache($force=true, $cachetime=43200) {
	global $_G, $_SGLOBAL;
	$cachefile = B_ROOT.'./data/system/brandads.cache.php';
	if($force==false) {
		$cachemtime = file_exists($cachefile)?filemtime($cachefile):0;
		if($_G['timestamp']-$cachemtime<$cachetime) {
			@include($cachefile);
			return false;
		}
	}
	$ads = array();
	$query = DB::query('SELECT * FROM '.tname('data').' ORDER BY variable LIMIT 0, 20;');
	while ($ad = DB::fetch($query)) {
		$tmp = array();
		switch($ad['variable']) {
			case 'banner':
				$ad['value'] = htmlspecialchars($ad['value']);
				break;
			case 'notice':
				$ad['value'] = unserialize($ad['value']);
				break;
			case 'topic':
				$ad['value'] = unserialize($ad['value']);
				break;
			case 'sitetheme':
				$ad['value'] = htmlspecialchars($ad['value']);
				$_G_['style'] = htmlspecialchars($ad['value']);
				break;
			case 'sidebarshop':
			case 'sidebarconsume':
			case 'consume':
			case 'groupbuy':
			case 'sidebargroupbuy':
			case 'discount':
			case 'hotgoods':
			case 'hotshop':
				$tmp = explode(',', $ad['value']);
				$itemarr = $temparr = array();
				foreach($tmp as $key=>$tmp1) {
					$tmp1 = intval(trim(strip_tags($tmp1)));
					if($tmp1>0) {
						$temparr[$key] = $tmp1;
						$itemarr[$key]['itemid'] = $tmp1;
						if(in_array($ad['variable'], array('sidebarconsume', 'consume'))) {
							$itemarr[$key]['shopid'] = DB::result_first('SELECT shopid FROM '.tname('consumeitems')." WHERE itemid='$tmp1' AND grade>2");
						} elseif(in_array($ad['variable'], array('groupbuy', 'sidebargroupbuy'))) {
							$itemarr[$key]['shopid'] = DB::result_first('SELECT shopid FROM '.tname('groupbuyitems')." WHERE itemid='$tmp1' AND grade>2");
						} elseif(in_array($ad['variable'], array('hotgoods'))) {
							$itemarr[$key]['shopid'] = DB::result_first('SELECT shopid FROM '.tname('gooditems')." WHERE itemid='$tmp1' AND grade>2");
						}
					}
				}
				$ads[$ad['variable'].'arr'] = $itemarr;
				$ad['value'] = implode(',', $temparr);
				//拼凑两类缓存，$ads['consume']给后台设置，$ads['consumearr']['itemid']、$ads['consumearr']['shopid']给前台展示
				break;
			case 'ads_show_type':
				break;
			default: unset($ad);
		}
		if($ad['value']) { $ads[$ad['variable']] = $ad['value'];} else { $ads[$ad['variable']] = '';}
	}
	if($ads) {
		$cachetext = '$_G[\'brandads\']='.arrayeval($ads);
	} else {
		$cachetext = '$_G[\'brandads\']='."Array ('ads_show_type' => 'topic','sitetheme' => 'default','banner' => 0,'consume' => 0,'discount' => 0,'hotshop' => 0,'notice' => Array (0 => Array ('title' => '','url' => ''),1 => Array ('title' => '','url' => ''),2 => Array ('title' => '','url' => ''),3 => Array ('title' => '','url' => ''),4 => Array ('title' => '','url' => ''),5 => Array ('title' => '','url' => ''),6 => Array ('title' => '','url' => '')),'sidebarconsume' => 0,'sidebarshop' => 0,'topic' => 0)";
	}
	writefile($cachefile, $cachetext, 'php');
	@include($cachefile);
}

/**
 * 更新过滤词设置缓存
 */
function updatecensorcache($force=true, $cachetime='43200', $censorvalue='') {
	global $_G, $_SGLOBAL;

	$cachefile = B_ROOT.'./data/system/censor.cache.php';
	if($force==false) {
		$cachemtime = file_exists($cachefile)?filemtime($cachefile):0;
		if($_G['timestamp']-$cachemtime<$cachetime) {
			@include($cachefile);
			return false;
		}
	}
	$_SGLOBAL['censor'] = $banned = $banwords = array();

	if(!$censorvalue) {
		$query = DB::query('SELECT * FROM '.tname('data').' WHERE variable=\'censor\' LIMIT 1');
		$censor = DB::fetch($query);
		$censorvalue = $censor['value'];
	}

	$censorarr = explode("\n", $censorvalue);
	foreach($censorarr as $censor) {
		$censor = trim($censor);
		if(empty($censor)) continue;

		list($find, $replace) = explode('=', $censor);
		$findword = $find;
		$find = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($find, '/'));
		switch($replace) {
			case '{BANNED}':
				$banwords[] = preg_replace("/\\\{(\d+)\\\}/", "*", preg_quote($findword, '/'));
				$banned[] = $find;
				break;
			default:
				$_SGLOBAL['censor']['filter']['find'][] = '/'.$find.'/i';
				$_SGLOBAL['censor']['filter']['replace'][] = $replace;
				break;
		}
	}
	if($banned) {
		$_SGLOBAL['censor']['banned'] = '/('.implode('|', $banned).')/i';
		$_SGLOBAL['censor']['banword'] = implode(', ', $banwords);
	}

	$cachetext = '$_SGLOBAL[\'censor\']='.arrayeval($_SGLOBAL['censor']);
	writefile($cachefile, $cachetext, 'php');
	@include($cachefile);
}

function updateattrext($force=true, $cachetime='53200') {
	global $_G, $_SGLOBAL;

	$cachefile = B_ROOT.'./data/system/attr_ext.cache.php';
	if($force == false) {
		$cachemtime = file_exists($cachefile) ? filemtime($cachefile) : 0;
		if($_G['timestamp'] - $cachemtime < $cachetime) {
			@include($cachefile);
			return false;
		}
	}
	$_SGLOBAL['brandlinks'] = $link = array();

	$query = DB::query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = 2 and available = 1 order by displayorder' );
	while($value = DB::fetch($query)) {
		if(!preg_match('/^ext_/',$value['fieldname'])){
			continue;
		}
		$_SGLOBAL['attr_ext'][] = $value;
	}
	$cachetext = '$_SGLOBAL[\'attr_ext\']='.arrayeval($_SGLOBAL['attr_ext']);
	writefile($cachefile, $cachetext, 'php');
	@include($cachefile);
}

?>