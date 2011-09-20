<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_home.php 22540 2011-05-12 02:51:25Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid'] && $_G['setting']['privacy']['view']['home']) {
	showmessage('home_no_privilege', '', array(), array('login' => true));
}
require_once libfile('function/feed');

if(empty($_G['setting']['feedhotday'])) {
	$_G['setting']['feedhotday'] = 2;
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];

space_merge($space, 'count');

if(empty($_GET['view'])) {
	if($space['self']) {
		if($_G['setting']['showallfriendnum'] && $space['friends'] < $_G['setting']['showallfriendnum']) {
			$_GET['view'] = 'all';
		} else {
			$_GET['view'] = 'we';
		}
	} else {
		$_GET['view'] = 'all';
	}
}
if(empty($_GET['order'])) {
	$_GET['order'] = 'dateline';
}

$perpage = $_G['setting']['feedmaxnum']<20?20:$_G['setting']['feedmaxnum'];
$perpage = mob_perpage($perpage);

if($_GET['view'] == 'all' && $_GET['order'] == 'hot') {
	$perpage = 50;
}

$page = intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

//$_G['home_today'] = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;
$_G['home_today'] = $_G['timenow']['todayzero'];

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'home',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'appid' => $_GET['appid'],
	'type' => $_GET['type'],
	'icon' => $_GET['icon']
);
$theurl = 'home.php?'.url_implode($gets);
$hotlist = array();
if(!IS_ROBOT) {
	$feed_users = $feed_list = $user_list = $filter_list  = $list = $magic = array();
	if($_GET['view'] != 'app') {
		if($space['self'] && empty($start) && $_G['setting']['feedhotnum'] > 0 && ($_GET['view'] == 'we' || $_GET['view'] == 'all')) {
			$hotlist_all = array();
			$hotstarttime = $_G['timestamp'] - $_G['setting']['feedhotday']*3600*24;
			$query = DB::query("SELECT * FROM ".DB::table('home_feed')." USE INDEX(hot) WHERE dateline>='$hotstarttime' ORDER BY hot DESC LIMIT 0,10");
			while ($value = DB::fetch($query)) {
				if($value['hot']>0 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					if(empty($hotlist)) {
						$hotlist[$value['feedid']] = $value;
					} else {
						$hotlist_all[$value['feedid']] = $value;
					}
				}
			}
			$nexthotnum = $_G['setting']['feedhotnum'] - 1;
			if($nexthotnum > 0) {
				if(count($hotlist_all)> $nexthotnum) {
					$hotlist_key = array_rand($hotlist_all, $nexthotnum);
					if($nexthotnum == 1) {
						$hotlist[$hotlist_key] = $hotlist_all[$hotlist_key];
					} else {
						foreach ($hotlist_key as $key) {
							$hotlist[$key] = $hotlist_all[$key];
						}
					}
				} else {
					$hotlist = array_merge($hotlist, $hotlist_all);
				}
			}
		}
	}

	$need_count = true;
	$wheresql = array('1');

	if($_GET['view'] == 'all') {

		if($_GET['order'] == 'dateline') {
			$ordersql = "dateline DESC";
			$f_index = '';
			$orderactives = array('dateline' => ' class="a"');
		} else {
			$wheresql['hot'] = "hot>='$minhot'";
			$ordersql = "dateline DESC";
			$f_index = '';
			$orderactives = array('hot' => ' class="a"');
		}

	} elseif($_GET['view'] == 'me') {

		$wheresql['uid'] = "uid='$space[uid]'";
		$ordersql = "dateline DESC";
		$f_index = '';

		$diymode = 1;
		if($space['self'] && $_GET['from'] != 'space') $diymode = 0;

	} elseif($_GET['view'] == 'app' && $_G['setting']['my_app_status']) {

		if ($_G['gp_type'] == 'all') {

			$wheresql = "1";
			$ordersql = "dateline DESC";
			$f_index = '';

		} else {

			if(empty($space['feedfriend'])) $_G['gp_type'] = 'me';

			if($_G['gp_type'] == 'me') {
				$wheresql = "uid='$_G[uid]'";
				$ordersql = "dateline DESC";
				$f_index = '';

			} else {
				$wheresql = "uid IN ('0',$space[feedfriend])";
				$ordersql = "dateline DESC";
				$f_index = 'USE INDEX(dateline)';
				$_G['gp_type'] = 'we';
				$_G['home_tpl_hidden_time'] = 1;
			}
		}

		$icon = empty($_GET['icon'])?'':trim($_GET['icon']);
		if($icon) {
			$wheresql .= " AND icon='$icon'";
		}
		$multi = '';

		$feed_list = $appfeed_list = $hiddenfeed_list = $filter_list = $hiddenfeed_num = $icon_num = array();
		$count = $filtercount = 0;
		$query = DB::query("SELECT * FROM ".DB::table('home_feed_app')." $f_index
			WHERE $wheresql
			ORDER BY $ordersql
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$feed_list[$value['icon']][] = $value;
			$count++;
		}
		$multi = simplepage($count, $perpage, $page, $theurl);
		require_once libfile('function/feed');

		$list = array();
		foreach ($feed_list as $key => $values) {
			$nowcount = 0;
			foreach ($values as $value) {
				$value = mkfeed($value);
				$nowcount++;
				if($nowcount>5 && empty($icon)) {
					break;
				}
				$list[$key][] = $value;
			}
		}
		$need_count = false;
		$typeactives = array($_G['gp_type'] => ' class="a"');

	} else {

		space_merge($space, 'field_home');

		if(empty($space['feedfriend'])) {
			$need_count = false;
		} else {
			$wheresql['uid'] = "uid IN ('0',$space[feedfriend])";
			$ordersql = "dateline DESC";
			$f_index = 'USE INDEX(dateline)';
		}
	}

	$appid = empty($_GET['appid'])?0:intval($_GET['appid']);
	if($appid) {
		$wheresql['appid'] = "appid='$appid'";
	}
	$icon = empty($_GET['icon'])?'':trim($_GET['icon']);
	if($icon) {
		$wheresql['icon'] = "icon='$icon'";
	}
	$gid = !isset($_GET['gid'])?'-1':intval($_GET['gid']);
	if($gid>=0) {
		$fuids = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND gid='$gid' ORDER BY num DESC LIMIT 0,100");
		while ($value = DB::fetch($query)) {
			$fuids[] = $value['fuid'];
		}
		if(empty($fuids)) {
			$need_count = false;
		} else {
			$wheresql['uid'] = "uid IN (".dimplode($fuids).")";
		}
	}
	$gidactives[$gid] = ' class="a"';

	$count = $filtercount = 0;
	$multi = '';

	if($need_count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_feed')." $f_index
			WHERE ".implode(' AND ', $wheresql)."
			ORDER BY $ordersql
			LIMIT $start,$perpage");

		if($_GET['view'] == 'me') {
			while ($value = DB::fetch($query)) {
				if(!isset($hotlist[$value['feedid']]) && !isset($hotlist_all[$value['feedid']]) && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					$value = mkfeed($value);

					if($value['dateline']>=$_G['home_today']) {
						$list['today'][] = $value;
					} elseif ($value['dateline']>=$_G['home_today']-3600*24) {
						$list['yesterday'][] = $value;
					} else {
						$theday = dgmdate($value['dateline'], 'Y-m-d');
						$list[$theday][] = $value;
					}
				}
				$count++;
			}
		} else {
			$hash_datas = array();
			$more_list = array();
			$uid_feedcount = array();

			while ($value = DB::fetch($query)) {
				if(!isset($hotlist[$value['feedid']]) && !isset($hotlist_all[$value['feedid']]) && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					$value = mkfeed($value);
					if(ckicon_uid($value)) {

						if($value['dateline']>=$_G['home_today']) {
							$dkey = 'today';
						} elseif ($value['dateline']>=$_G['home_today']-3600*24) {
							$dkey = 'yesterday';
						} else {
							$dkey = dgmdate($value['dateline'], 'Y-m-d');
						}

						$maxshownum = 3;
						if(empty($value['uid'])) $maxshownum = 10;

						if(empty($value['hash_data'])) {
							if(empty($feed_users[$dkey][$value['uid']])) $feed_users[$dkey][$value['uid']] = $value;
							if(empty($uid_feedcount[$dkey][$value['uid']])) $uid_feedcount[$dkey][$value['uid']] = 0;

							$uid_feedcount[$dkey][$value['uid']]++;

							if($uid_feedcount[$dkey][$value['uid']]>$maxshownum) {
								$more_list[$dkey][$value['uid']][] = $value;
							} else {
								$feed_list[$dkey][$value['uid']][] = $value;
							}

						} elseif(empty($hash_datas[$value['hash_data']])) {
							$hash_datas[$value['hash_data']] = 1;
							if(empty($feed_users[$dkey][$value['uid']])) $feed_users[$dkey][$value['uid']] = $value;
							if(empty($uid_feedcount[$dkey][$value['uid']])) $uid_feedcount[$dkey][$value['uid']] = 0;


							$uid_feedcount[$dkey][$value['uid']] ++;

							if($uid_feedcount[$dkey][$value['uid']]>$maxshownum) {
								$more_list[$dkey][$value['uid']][] = $value;
							} else {
								$feed_list[$dkey][$value['uid']][$value['hash_data']] = $value;
							}

						} else {
							$user_list[$value['hash_data']][] = "<a href=\"home.php?mod=space&uid=$value[uid]\" target=\"_blank\">$value[username]</a>";
						}


					} else {
						$filtercount++;
						$filter_list[] = $value;
					}
				}
				$count++;
			}
		}

		$multi = simplepage($count, $perpage, $page, $theurl);
	}
}

$olfriendlist = $visitorlist = $task = $ols = $birthlist = $guidelist = array();
$oluids = array();
$groups = array();
$defaultusers = $newusers = $showusers = array();

if($space['self'] && empty($start)) {

	space_merge($space, 'field_home');
	if($_GET['view'] == 'we') {
		require_once libfile('function/friend');
		$groups = friend_group_list();
	}

	$isnewer = ($_G['timestamp']-$space['regdate'] > 3600*24*7) ?0:1;
	if($isnewer) {

		$friendlist = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]'");
		while ($value = DB::fetch($query)) {
			$friendlist[$value['fuid']] = 1;
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_specialuser')." WHERE status='1' ORDER BY displayorder");
		while ($value = DB::fetch($query)) {
			if(empty($friendlist[$value['uid']])) {
				$defaultusers[] = $value;
				$oluids[] = $value['uid'];
			}
		}
	}

	if($space['newprompt']) {
		space_merge($space, 'status');
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,12");
	while ($value = DB::fetch($query)) {
		$visitorlist[$value['vuid']] = $value;
		$oluids[] = $value['vuid'];
	}

	if($oluids) {
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($oluids).")");
		while ($value = DB::fetch($query)) {
			if(!$value['invisible']) {
				$ols[$value['uid']] = 1;
			} elseif ($visitorlist[$value['uid']]) {
				unset($visitorlist[$value['uid']]);
			}
		}
	}

	$oluids = array();
	$olfcount = 0;
	if($space['feedfriend']) {
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN ($space[feedfriend]) ORDER BY lastactivity DESC LIMIT 15");
		while ($value = DB::fetch($query)) {
			if($olfcount < 15 && !$value['invisible']) {
				$olfriendlist[$value['uid']] = $value;
				$ols[$value['uid']] = 1;
				$oluids[$value['uid']] = $value['uid'];
				$olfcount++;
			}
		}
	}
	if($olfcount < 15) {
		$query = DB::query("SELECT fuid AS uid, fusername AS username, num FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' ORDER BY num DESC, dateline DESC LIMIT 0,32");
		while ($value = DB::fetch($query)) {
			if(empty($oluids[$value['uid']])) {
				$olfriendlist[$value['uid']] = $value;
				$olfcount++;
				if($olfcount == 15) break;
			}
		}
	}

	if($space['feedfriend']) {
		//BUG:此處的好友生日名單將無視用戶隱私設定
		$birthdaycache = DB::fetch_first("SELECT variable, value, expiration FROM ".DB::table('forum_spacecache')." WHERE uid='$_G[uid]' AND variable='birthday'");
		if(empty($birthdaycache) || TIMESTAMP > $birthdaycache['expiration']) {
			/*
			list($s_month, $s_day) = explode('-', dgmdate($_G['timestamp']-3600*24*3, 'n-j'));
			*/
			list($s_month, $s_day) = explode('-', dgmdate($_G['timestamp']-3600*24*7, 'n-j'));
			list($n_month, $n_day) = explode('-', dgmdate($_G['timestamp'], 'n-j'));
			/*
			list($e_month, $e_day) = explode('-', dgmdate($_G['timestamp']+3600*24*7, 'n-j'));
			*/
			list($e_month, $e_day) = explode('-', dgmdate($_G['timestamp']+3600*24*45, 'n-j'));
			if($e_month == $s_month) {
				$wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";

			// bluelovers
			} elseif ($e_month < $s_month) {
				// 修正跨月跨年的問題

				$wheresql = "(
						(sf.birthmonth='$s_month' AND sf.birthday>='$s_day')
						OR (sf.birthmonth>'$s_month')
						OR (sf.birthmonth<='$e_month' AND sf.birthday<='$e_day')
						OR (sf.birthmonth>=1 AND sf.birthmonth<'$e_month')
					) AND sf.birthday > 0";

			// bluelovers

			} else {
				$wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
				// 修正少了 大於 起始月 並且小於 結束月 之間的生日
				$wheresql .= " OR (sf.birthmonth>'$s_month' AND sf.birthmonth<'$e_month')";
			}

			// bluelovers
			$birthlist_nextyear = $birthlist_last = array();
			$_b = $_bl = $_bn = 0;
			// bluelovers

			$query = DB::query("SELECT sf.uid,sf.birthyear,sf.birthmonth,sf.birthday,s.username
				FROM ".DB::table('common_member_profile')." sf
				LEFT JOIN ".DB::table('common_member')." s USING(uid)
				WHERE (sf.uid IN ($space[feedfriend])) AND ($wheresql)"

				// 修正排序判斷並且支援跨月跨年
				." ORDER BY"
				// 將小於這個月的排序推到後面
				." (sf.birthmonth < '$n_month') ASC,"
				." (sf.birthmonth < '$s_month') ASC,"
				." sf.birthmonth, sf.birthday, s.username"

				// 限制最大查詢數
				." LIMIT 10"

				);
			while ($value = DB::fetch($query)) {
				$value['istoday'] = 0;
				if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
					$value['istoday'] = 1;
				}
				$key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
				/*
				$birthlist[$key][] = $value;
				ksort($birthlist);
				*/

				// bluelovers
				$value['birthmonth'] = sprintf("%02d", $value['birthmonth']);
				$value['birthday'] = sprintf("%02d", $value['birthday']);

				if ($value['birthmonth'] >= $n_month) {
					$_b++;
					$birthlist[$key][] = $value;
				} elseif ($value['birthmonth'] >= $s_month && $value['birthmonth'] <= $n_month) {
					$_bl++;
					$birthlist_last[$key][] = $value;
				} else {
					$_bn++;
					$birthlist_nextyear[$key][] = $value;
				}
				// bluelovers
			}

			// bluelovers
			ksort($birthlist_last);
			ksort($birthlist);
			ksort($birthlist_nextyear);

			/**
			 * 當生日列表超過限定值時
			 * 則 $birthlist_last 除了最接近本日的資料以外，其餘刪除
			 **/
			if (($_b + $_bn) >= 4 && $_bl > 0) {
				end($birthlist_last);
				$birthlist_last = array(key($birthlist_last) => end($birthlist_last));

				$_bl = 1;
			}

			/**
			 * 當生日列表超過限定值時
			 * 則 $birthlist 只保留今日以及未來三個天次
			 * 並且清除 $birthlist_nextyear
			 **/
			if (($_b + $_bl) > 5 && count($birthlist) > 3) {
				$birthlist_new = array();
				$i = 0;
				foreach ($birthlist as $k => $v) {
					$birthlist_new[$k] = $v;
					if (++$i > 3) break;
				}
				$birthlist = $birthlist_new;
				$birthlist_nextyear = array();
				unset($birthlist_new);
			} elseif ($_bn > 0 && ($_b + $_bn) > 4) {
				/**
				 * 當生日列表超過限定值時並且跨年時
				 * 則 $birthlist_nextyear 只保留第一天，其餘刪除
				 **/
				reset($birthlist_nextyear);
				$birthlist_nextyear = array(key($birthlist_nextyear) => reset($birthlist_last));
			}

			$birthlist = array_merge($birthlist_last, $birthlist, $birthlist_nextyear);

			unset($birthlist_nextyear, $birthlist_last);
			// bluelovers

			DB::query("REPLACE INTO ".DB::table('forum_spacecache')." (uid, variable, value, expiration) VALUES ('$_G[uid]', 'birthday', '".addslashes(serialize($birthlist))."', '".getexpiration()."')");
		} else {
			$birthlist = unserialize($birthdaycache['value']);
		}
	}

	if($_G['setting']['taskon']) {
		require_once libfile('class/task');
		$tasklib = & task::instance();
		$taskarr = $tasklib->tasklist('canapply');
		$task = $taskarr[array_rand($taskarr)];
	}
	if($_G['setting']['magicstatus']) {
		loadcache('magics');
		if(!empty($_G['cache']['magics'])) {
			$magic = $_G['cache']['magics'][array_rand($_G['cache']['magics'])];
			$magic['description'] = cutstr($magic['description'], 34, '');
			$magic['pic'] = strtolower($magic['identifier']).'.gif';
		}
	}
} elseif(empty($_G['uid'])) {
	$query = DB::query("SELECT * FROM ".DB::table('home_specialuser')." WHERE status='1' ORDER BY displayorder LIMIT 0,12");
	while ($value = DB::fetch($query)) {
		$defaultusers[] = $value;
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_show')." ORDER BY credit DESC LIMIT 0,12");
	while ($value = DB::fetch($query)) {
		$showusers[] = $value;
	}

	$time = TIMESTAMP - (7 * 86400);
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE regdate>'$time' ORDER BY uid DESC LIMIT 0,12");
	while ($value = DB::fetch($query)) {
		$value['regdate'] = dgmdate($value['regdate'], 'u', 9999, 'm-d');
		$newusers[] = $value;
	}
}

dsetcookie('home_readfeed', $_G['timestamp'], 365*24*3600);
if($_G['uid']) {
	$defaultstr = getdefaultdoing();
	space_merge($space, 'status');
	if(!$space['profileprogress']) {
		include_once libfile('function/profile');
		$space['profileprogress'] = countprofileprogress();
	}
}
$actives = array($_GET['view'] => ' class="a"');
if($_G['gp_from'] == 'space') {
	if($_G['gp_do'] == 'home') {
		$navtitle = lang('space', 'sb_feed', array('who' => $space['username']));
		$metakeywords = lang('space', 'sb_feed', array('who' => $space['username']));
		$metadescription = lang('space', 'sb_feed', array('who' => $space['username']));
	}
} else {
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('home');
	if(!$navtitle) {
		$navtitle = $_G['setting']['navs'][4]['navname'];
		$nobbname = false;
	} else {
		$nobbname = true;
	}

	if(!$metakeywords) {
		$metakeywords = $_G['setting']['navs'][4]['navname'];
	}

	if(!$metadescription) {
		$metadescription = $_G['setting']['navs'][4]['navname'];
	}
}
if(empty($cp_mode)) include_once template("diy:home/space_home");

?>