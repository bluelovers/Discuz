<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_home.php 22638 2011-05-16 06:29:59Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getstr($string, $length, $in_slashes=0, $out_slashes=0, $bbcode=0, $html=0) {
	global $_G;

	$string = trim($string);
	if($in_slashes) {
		$string = dstripslashes($string);
	}
	$string = preg_replace("/\[hide=?\d*\](.*?)\[\/hide\]/is", '', $string);
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = dhtmlspecialchars($string);
	}

	if($length) {
		$string = cutstr($string, $length);
	}

	if($bbcode) {
		require_once DISCUZ_ROOT.'./source/class/class_bbcode.php';
		$bb = & bbcode::instance();
		$string = $bb->bbcode2html($string, $bbcode);
	}
	if($out_slashes) {
		$string = daddslashes($string);
	}
	return trim($string);
}

function addblogtag($tags, $itemid , $typeid = 'blogid') {
	global $_G;

	if($tags == '') {
		return;
	}

	$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
	if(strexists($tags, ',')) {
		$tagarray = array_unique(explode(',', $tags));
	} else {
		$langcore = lang('core');
		$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
		$tagarray = array_unique(explode(' ', $tags));
	}
	$tagcount = 0;
	foreach($tagarray as $tagname) {
		$tagname = trim($tagname);
		if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname)) {
			$result = DB::fetch_first("SELECT tagid, status FROM ".DB::table('common_tag')." WHERE tagname='$tagname'");
			if($result['tagid']) {
				if(!$result['status']) {
					$tagid = $result['tagid'];
				}
			} else {
				DB::query("INSERT INTO ".DB::table('common_tag')." (tagname, status) VALUES ('$tagname', '0')");
				$tagid = DB::insert_id();
			}
			if($tagid) {
				DB::query("INSERT INTO ".DB::table('common_tagitem')." (tagid, tagname, itemid, idtype) VALUES ('$tagid', '$tagname', '$itemid', '$typeid')");
				$tagcount++;
				$tagstr .= $tagid.','.$tagname.'\t';
			}
			if($tagcount > 4) {
				unset($tagarray);
				break;
			}
		}
	}
	return $tagstr;
}

function modblogtag($tags, $itemid) {

	$tagstr = DB::result_first("SELECT tag FROM ".DB::table('home_blogfield')." WHERE blogid='$itemid'");

	$blogtagarray = $blogtagidarray = $blogtagarraynew = array();
	$query = DB::query("SELECT tagid, tagname FROM ".DB::table('common_tagitem')." WHERE idtype='blogid' AND itemid='$itemid'");
	while($result = DB::fetch($query)) {
		$blogtagarray[] = $result['tagname'];
		$blogtagidarray[] = $result['tagid'];
	}

	$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
	if(strexists($tags, ',')) {
		$tagarray = array_unique(explode(',', $tags));
	} else {
		$langcore = lang('core');
		$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
		$tagarray = array_unique(explode(' ', $tags));
	}

	$tagcount = 0;
	foreach($tagarray as $tagname) {
		$tagname = trim($tagname);
		if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname)) {
			$blogtagarraynew[] = $tagname;
			if(!in_array($tagname, $blogtagarray)) {
				$result = DB::fetch_first("SELECT tagid, status FROM ".DB::table('common_tag')." WHERE tagname='$tagname'");
				if($result['tagid']) {
					if(!$result['status']) {
						$tagid = $result['tagid'];
					}
				} else {
					DB::query("INSERT INTO ".DB::table('common_tag')." (tagname, status) VALUES ('$tagname', '0')");
					$tagid = DB::insert_id();
				}
				if($tagid) {
					DB::query("INSERT INTO ".DB::table('common_tagitem')." (tagid, tagname, itemid, idtype) VALUES ('$tagid', '$tagname', '$itemid', 'blogid')");
					$tagstr = $tagstr.$tagid.','.$tagname.'\t';
				}
			}
		}
		$tagcount++;
		if($tagcount > 4) {
			unset($tagarray);
			break;
		}
	}
	foreach($blogtagarray as $key => $tagname) {
		if(!in_array($tagname, $blogtagarraynew)) {
			DB::query("DELETE FROM	".DB::table('common_tagitem')." WHERE idtype='blogid' AND itemid = '$itemid' AND tagname='$tagname'");
			$tagid = $blogtagidarray[$key];
			$tagstr = str_replace("$tagid,$tagname\t", '', $tagstr);
		}
	}
	return $tagstr;
}

function obclean() {
	ob_end_clean();
	if (getglobal('config/output/gzip') && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

function dreaddir($dir, $extarr=array()) {
	$dirs = array();
	if($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(!empty($extarr) && is_array($extarr)) {
				if(in_array(strtolower(fileext($file)), $extarr)) {
					$dirs[] = $file;
				}
			} else if($file != '.' && $file != '..') {
				$dirs[] = $file;
			}
		}
		closedir($dh);
	}
	return $dirs;
}

function url_implode($gets) {
	$arr = array();
	foreach ($gets as $key => $value) {
		if($value) {
			$arr[] = $key.'='.urlencode(dstripslashes($value));
		}
	}
	return implode('&', $arr);
}

function ckstart($start, $perpage) {
	global $_G;

	$_G['setting']['maxpage'] = $_G['setting']['maxpage'] ? $_G['setting']['maxpage'] : 100;
	$maxstart = $perpage*intval($_G['setting']['maxpage']);
	if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
		showmessage('length_is_not_within_the_scope_of');
	}
}


function get_my_app() {
	global $_G;

	if($_G['setting']['my_app_status']) {
		$query = DB::query("SELECT * FROM ".DB::table('common_myapp')." WHERE flag='1' ORDER BY displayorder DESC", 'SILENT');
		while ($value = DB::fetch($query)) {
			$_G['my_app'][$value['appid']] = $value;
		}
	}
}

function get_my_userapp() {
	global $_G;

	if($_G['setting']['my_app_status']) {
		$query = DB::query("SELECT * FROM ".DB::table('home_userapp')." WHERE uid='$_G[uid]' ORDER BY displayorder DESC", 'SILENT');
		while($value = DB::fetch($query)) {
			if(!empty($value['appname'])) {
				$_G['my_userapp'][$value['appid']] = $value;
			}
		}
	}
}

function getspace($uid) {
	global $_G;

	$var = "home_space_{$uid}";
	if(!isset($_G[$var])) {
		if($uid == $_G['uid'] && $_G['member']['uid']) {
			$_G[$var] = $_G['member'];
			$_G[$var]['self'] = 1;
		} else {
			$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid='$uid'");
			$_G[$var] = DB::fetch($query);
		}
	}
	return $_G[$var];
}

function ckprivacy($key, $privace_type) {
	global $_G, $space;

	$var = "home_ckprivacy_{$key}_{$privace_type}";
	if(isset($_G[$var])) {
		return $_G[$var];
	}
	space_merge($space, 'field_home');
	$result = false;
	if($_G['adminid'] == 1) {
		$result = true;
	} else {
		if($privace_type == 'feed') {
			if(!empty($space['privacy'][$privace_type][$key])) {
				$result = true;
			}
		} elseif($space['self']){
			$result = true;
		} else {
			if(empty($space['privacy'][$privace_type][$key])) {
				$result = true;
			} elseif ($space['privacy'][$privace_type][$key] == 1) {
				include_once libfile('function/friend');
				if(friend_check($space['uid'])) {
					$result = true;
				}
			} elseif ($space['privacy'][$privace_type][$key] == 3) {
				$result = in_array($_G['groupid'], array(4, 5, 6, 7)) ? false : true;
			}
		}
	}
	$_G[$var] = $result;
	return $result;
}

function app_ckprivacy($privacy) {
	global $_G, $space;

	$var = "home_app_ckprivacy_{$privacy}";
	if(isset($_G[$var])) {
		return $_G[$var];
	}
	$result = false;
	switch ($privacy) {
		case 0:
			$result = true;
			break;
		case 1:
			include_once libfile('function/friend');
			if(friend_check($space['uid'])) {
				$result = true;
			}
			break;
		case 2:
			break;
		case 3:
			if($space['self']) {
				$result = true;
			}
			break;
		case 4:
			break;
		case 5:
			break;
		default:
			$result = true;
			break;
	}
	$_G[$var] = $result;
	return $result;
}

function formatsize($size) {
	$prec=3;
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

function ckfriend($touid, $friend, $target_ids='') {
	global $_G;

	if(empty($_G['uid'])) return $friend?false:true;
	if($touid == $_G['uid'] || $_G['adminid'] == 1) return true;

	$var = 'home_ckfriend_'.md5($touid.'_'.$friend.'_'.$target_ids);
	if(isset($_G[$var])) return $_G[$var];

	$_G[$var] = false;
	switch ($friend) {
		case 0:
			$_G[$var] = true;
			break;
		case 1:
			include_once libfile('function/friend');
			if(friend_check($touid)) {
				$_G[$var] = true;
			}
			break;
		case 2:
			if($target_ids) {
				$target_ids = explode(',', $target_ids);
				if(in_array($_G['uid'], $target_ids)) $_G[$var] = true;
			}
			break;
		case 3:
			break;
		case 4:
			$_G[$var] = true;
			break;
		default:
			break;
	}
	return $_G[$var];
}

function sub_url($url, $length) {
	if(strlen($url) > $length) {
		$url = str_replace(array('%3A', '%2F'), array(':', '/'), rawurlencode($url));
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	return $url;
}

function space_domain($space) {
	global $_G;

	if($_G['setting']['allowspacedomain'] && $_G['setting']['domain']['root']['home']) {
		space_merge($space, 'field_home');
		if($space['domain']) {
			$space['domainurl'] = 'http://'.$space['domain'].'.'.$_G['setting']['domain']['root']['home'];
		}
	}
	if(!empty($_G['setting']['domain']['app']['home'])) {
		$space['domainurl'] = 'http://'.$_G['setting']['domain']['app']['home'].'/?'.$space['uid'];
	} elseif(empty($space['domainurl'])) {
		$space['domainurl'] = $_G['siteurl'].'?'.$space['uid'];
	}
	return $space['domainurl'];
}

function my_checkupdate() {
	global $_G;
	if($_G['setting']['my_app_status'] && empty($_G['setting']['my_closecheckupdate']) && $_G['group']['radminid'] == 1) {
		$sid = $_G['setting']['my_siteid'];
		$ts = $_G['timestamp'];
		$key = md5($sid.$ts.$_G['setting']['my_sitekey']);
		echo '<script type="text/javascript" src="http://notice.uchome.manyou.com/notice?sId='.$sid.'&ts='.$ts.'&key='.$key.'" charset="UTF-8"></script>';
	}
}

function g_name($groupid) {
	global $_G;
	echo $_G['cache']['usergroups'][$groupid]['grouptitle'];
}

function g_color($groupid) {
	global $_G;
	if(empty($_G['cache']['usergroups'][$groupid]['color'])) {
		echo '';
	} else {
		echo ' style="color:'.$_G['cache']['usergroups'][$groupid]['color'].';"';
	}
}

function mob_perpage($perpage) {
	global $_G;

	$newperpage = isset($_GET['perpage'])?intval($_GET['perpage']):0;
	if($_G['mobile'] && $newperpage>0 && $newperpage<500) {
		$perpage = $newperpage;
	}
	return $perpage;
}

function ckicon_uid($feed) {
	global $_G, $space;

	space_merge($space, 'field_home');
	$filter_icon = empty($space['privacy']['filter_icon'])?array():array_keys($space['privacy']['filter_icon']);
	if($filter_icon && (in_array($feed['icon'].'|0', $filter_icon) || in_array($feed['icon'].'|'.$feed['uid'], $filter_icon))) {
		return false;
	}
	return true;
}

function sarray_rand($arr, $num=1) {
	$r_values = array();
	if($arr && count($arr) > $num) {
		if($num > 1) {
			$r_keys = array_rand($arr, $num);
			foreach ($r_keys as $key) {
				$r_values[$key] = $arr[$key];
			}
		} else {
			$r_key = array_rand($arr, 1);
			$r_values[$r_key] = $arr[$r_key];
		}
	} else {
		$r_values = $arr;
	}
	return $r_values;
}

function my_showgift() {
	global $_G, $space;
	if($_G['setting']['my_showgift'] && $_G['my_userapp'][$_G['home_gift_appid']]) {
		echo '<script language="javascript" type="text/javascript" src="http://gift.manyou-apps.com/recommend.js"></script>';
	}
}

function getsiteurl() {
	global $_G;
	return $_G['siteurl'];
}

function pic_get($filepath, $type, $thumb, $remote, $return_thumb=1, $hastype = '') {
	global $_G;

	$url = $filepath;
	if($return_thumb && $thumb) $url = getimgthumbname($url);
	if($remote > 1 && $type == 'album') {
		$remote -= 2;
		$type = 'forum';
	}
	$type = $hastype ? '' : $type.'/';
	return ($remote?$_G['setting']['ftp']['attachurl']:$_G['setting']['attachurl']).$type.$url;
}

function pic_cover_get($pic, $picflag) {
	global $_G;

	$type = 'album';
	if($picflag > 2) {
		$picflag = $picflag - 2;
		$type = 'forum';
	}
	if($picflag == 1) {
		$url = $_G['setting']['attachurl'].$type.'/'.$pic;
	} elseif ($picflag == 2) {
		$url = $_G['setting']['ftp']['attachurl'].$type.'/'.$pic;
	} else {
		$url = $pic;
	}
	return $url;
}

function pic_delete($pic, $type, $thumb, $remote) {
	global $_G;

	if($remote > 1 && $type == 'album') {
		$remote -= 2;
		$type = 'forum';
		return true;
	}

	if($remote) {
		ftpcmd('delete', $type.'/'.$pic);
		if($thumb) {
			ftpcmd('delete', $type.'/'.getimgthumbname($pic));
		}
		ftpcmd('close');
	} else {
		@unlink($_G['setting']['attachdir'].'/'.$type.'/'.$pic);
		if($thumb) {
			@unlink($_G['setting']['attachdir'].'/'.$type.'/'.getimgthumbname($pic));
		}
	}
	return true;
}

function pic_upload($FILES, $type='album', $thumb_width=0, $thumb_height=0, $thumb_type=2) {
	require_once libfile('class/upload');
	$upload = new discuz_upload();

	$result = array('pic'=>'', 'thumb'=>0, 'remote'=>0);

	$upload->init($FILES, $type);
	if($upload->error()) {
		return array();
	}

	$upload->save();
	if($upload->error()) {
		return array();
	}

	$result['pic'] = $upload->attach['attachment'];

	if($thumb_width && $thumb_height) {
		require_once libfile('class/image');
		$image = new image();
		if($image->Thumb($upload->attach['target'], '', $thumb_width, $thumb_height, $thumb_type)) {
			$result['thumb'] = 1;
		}
	}

	if(getglobal('setting/ftp/on')) {
		if(ftpcmd('upload', $type.'/'.$upload->attach['attachment'])) {
			if($result['thumb']) {
				ftpcmd('upload', $type.'/'.getimgthumbname($upload->attach['attachment']));
			}
			ftpcmd('close');
			$result['remote'] = 1;
		} else {
			if(getglobal('setting/ftp/mirror')) {
				@unlink($upload->attach['target']);
				@unlink(getimgthumbname($upload->attach['target']));
				return array();
			}
		}
	}

	return $result;
}

function member_count_update($uid, $counts) {
	global $_G;

	$setsqls = array();
	foreach ($counts as $key => $value) {
		if($key == 'credit') {
			if($_G['setting']['creditstransextra'][6]) {
				$key = 'extcredits'.intval($_G['setting']['creditstransextra'][6]);
			} elseif ($_G['setting']['creditstrans']) {
				$key = 'extcredits'.intval($_G['setting']['creditstrans']);
			} else {
				continue;
			}
		}
		$setsqls[$key] = $value;
	}
	if($setsqls) {
		updatemembercount($uid, $setsqls);
	}
}


function member_status_update($uid, $counts) {
	global $_G;

	$setsqls = array();
	foreach ($counts as $key => $value) {
		$setsqls[] = "{$key}={$key}+'{$value}'";
	}
	if($setsqls) {
		$setsqls[] = "lastactivity='{$_G[timestamp]}'";
		DB::query("UPDATE ".DB::table('common_member_status')." SET ".implode(',', $setsqls)." WHERE uid='$uid'");
	}
}

function getdefaultdoing() {
	global $_G;

	$result = array();
	$key = 0;

	$result = DB::fetch_first("SELECT * FROM ".DB::table('common_setting')." WHERE skey='defaultdoing'");
	if(!empty($result['svalue'])) {
		$_G['setting']['defaultdoing'] = explode("\r\n", $result['svalue']);
		$key = rand(0, count($_G['setting']['defaultdoing'])-1);
	} else {
		$_G['setting']['defaultdoing'] = array(lang('space', 'doing_you_can'));
	}
	return $_G['setting']['defaultdoing'][$key];
}

function getuserdiydata($space) {
	global $_G;
	if(empty($_G['blockposition'])) {
		$userdiy = getuserdefaultdiy();
		if (!empty($space['blockposition'])) {
			$blockdata = unserialize($space['blockposition']);
			foreach ((array)$blockdata as $key => $value) {
				if ($key == 'parameters') {
					foreach ((array)$value as $k=>$v) {
						if (!empty($v)) $userdiy[$key][$k] = $v;
					}
				} else {
					if (!empty($value)) $userdiy[$key] = $value;
				}
			}
		}
		$_G['blockposition'] = $userdiy;
	}
	return $_G['blockposition'];
}


function getuserdefaultdiy() {
	$defaultdiy = array(
			'currentlayout' => '1:2:1',
			'block' => array(
					'frame`frame1' => array(
							'attr' => array('name'=>'frame1'),
							'column`frame1_left' => array(
									'block`profile' => array('attr' => array('name'=>'profile')),
									'block`statistic' => array('attr' => array('name'=>'statistic')),
									'block`album' => array('attr' => array('name'=>'album')),
									'block`doing' => array('attr' => array('name'=>'doing'))
							),
							'column`frame1_center' => array(
									'block`personalinfo' => array('attr' => array('name'=>'personalinfo')),
									'block`feed' => array('attr' => array('name'=>'feed')),
									'block`share' => array('attr' => array('name'=>'share')),
									'block`blog' => array('attr' => array('name'=>'blog')),
									'block`thread' => array('attr' => array('name'=>'thread')),
									'block`wall' => array('attr' => array('name'=>'wall'))
							),
							'column`frame1_right' => array(
									'block`myapp' => array('attr' => array('name'=>'myapp')),
									'block`friend' => array('attr' => array('name'=>'friend')),
									'block`visitor' => array('attr' => array('name'=>'visitor')),
									'block`group' => array('attr' => array('name'=>'group'))
							)
					)
			),
			'parameters' => array(
					'blog' => array('showmessage' => 150, 'shownum' => 6),
					'doing' => array('shownum' => 15),
					'album' => array('shownum' => 8),
					'thread' => array('shownum' => 10),
					'share' => array('shownum' => 10),
					'friend' => array('shownum' => 18),
					'group' => array('shownum' => 12),
					'visitor' => array('shownum' => 18),
					'wall' => array('shownum' => 16),
					'feed' => array('shownum' => 16),
					'myapp' => array('shownum' => 9, 'logotype'=> 'logo'),
			),
		'nv' => array(
			'nvhidden' => 0,
			'items' => array(),
			'banitems' => array(),
		),
	);
	return $defaultdiy;
}

function getonlinemember($uids) {
	global $_G;
	if ($uids && is_array($uids) && empty($_G['ols'])) {
		$_G['ols'] = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($uids).")");
		while ($value = DB::fetch($query)) {
			if(!$value['invisible']) {
				$_G['ols'][$value['uid']] = $value['lastactivity'];
			}
		}
	}
}

?>