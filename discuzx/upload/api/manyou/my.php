<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: my.php 23925 2011-08-16 12:03:49Z yexinhao $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

require_once('../../source/class/class_core.php');
require_once('../../source/function/function_home.php');

$cachelist = array();
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = false;
$discuz->init_session = false;

$discuz->init();

require_once DISCUZ_ROOT . './api/manyou/Manyou.php';

class My extends Manyou {

	function onSiteGetAllUsers($from, $userNum, $friendNum = 2000, $isExtra) {
		$totalNum = getcount('common_member', '');

		$sql = 'SELECT s.*
				FROM %s s
				ORDER BY s.uid
				LIMIT %d, %d';
		$sql = sprintf($sql, DB::table('common_member'), $from, $userNum);
		$query = DB::query($sql);

		$spaces = $uIds = array();
		while($row = DB::fetch($query)) {
			$spaces[$row['uid']] = $row;
			$uIds[] = $row['uid'];
		}

		$users = $this->getUsers($uIds, $spaces, true, $isExtra, true, $friendNum, true);
		$result = array('totalNum' => $totalNum,
				'users' => $users
				);
		return $result;
	}

	function onSiteGetUpdatedUsers($num) {
		$totalNum = getcount('common_member_log', '');

		$users = array();
		if ($totalNum) {
			$sql = sprintf('SELECT uid, action FROM %s ORDER BY dateline LIMIT %d', DB::table('common_member_log'), $num);
			$query = DB::query($sql);
			$deletedUsers = $userLogs = $uIds = array();
			$undeletedUserIds = array();
			while($row = DB::fetch($query)) {
				$uIds[] = $row['uid'];
				if ($row['action'] == 'delete') {
					$deletedUsers[] = array('uId' => $row['uid'],
								'action' => $row['action'],
								);
				} else {
					$undeletedUserIds[] = $row['uid'];
				}
				$userLogs[$row['uid']] = $row;
			}

			$updatedUsers = $this->getUsers($undeletedUserIds, false, true, true, false);

			foreach($updatedUsers as $k => $v) {
				$updatedUsers[$k]['action'] = $userLogs[$v['uId']]['action'];
				$updatedUsers[$k]['updateType'] = 'all';
			}

			$users = array_merge($updatedUsers, $deletedUsers);

			if ($uIds) {
				$sql = sprintf('DELETE FROM %s WHERE uid IN (%s)', DB::table('common_member_log'), dimplode($uIds));
				DB::query($sql);
			}
		}

		$result = array('totalNum'	=> $totalNum, 'users'		=> $users);
		return $result;
	}

	function onSiteGetUpdatedFriends($num) {
		$friends = array();
		$totalNum = getcount('home_friendlog', '');

		if ($totalNum) {
			$sql = sprintf('SELECT * FROM %s ORDER BY dateline LIMIT %d', DB::table('home_friendlog'), $num);
			$query = DB::query($sql);
			while ($friend = DB::fetch($query)) {
				$friends[] = array('uId' => $friend['uid'],
							'uId2' => $friend['fuid'],
							'action' => $friend['action']
							);

				$sql = sprintf('DELETE FROM %s WHERE uid = %d AND fuid = %d', DB::table('home_friendlog'), $friend['uid'], $friend['fuid']);
				DB::query($sql);
			}
		}

		$result = array('totalNum' => $totalNum,
				'friends' => $friends
				);
		return $result;
	}

	function onSiteGetStat($beginDate = null, $num = null, $orderType = 'ASC') {
		$sql = 'SELECT * FROM ' . DB::table('common_stat');
		if ($beginDate) {
			$sql .= sprintf(' WHERE daytime >= %d', $beginDate);
		}
		$sql .= " ORDER BY daytime $orderType";
		if ($num) {
			$sql .= " LIMIT $num ";
		}
		$query = DB::query($sql);
		$result = array();
		$fields = array('login' => 'loginUserNum',
				'doing' => 'doingNum',
				'blog' => 'blogNum',
				'pic' => 'photoNum',
				'poll' => 'pollNum',
				'event' => 'eventNum',
				'share' => 'shareNum',
				'thread' => 'threadNum',
				'docomment' => 'doingCommentNum',
				'blogcomment' => 'blogCommentNum',
				'piccomment' => 'photoCommentNum',
				'pollcomment' => 'pollCommentNum',
				'eventcomment' => 'eventCommentNum',
				'sharecomment' => 'shareCommentNum',
				'pollvote' => 'pollUserNum',
				'eventjoin' => 'eventUserNum',
				'post' => 'postNum',
				'wall' => 'wallNum',
				'poke' => 'pokeNum',
				'click' => 'clickNum',
				);
		while($row = DB::fetch($query)) {
			$stat = array('date' => $row['daytime']);
			foreach($row as $k => $v) {
				if (array_key_exists($k, $fields)) {
					$stat[$fields[$k]] = $v;
				}
			}
			$result[] = $stat;
		}
		return $result;
	}

	function onUsersGetInfo($uIds, $fields = array(), $isExtra = false) {
		$users = $this->getUsers($uIds, false, true, $isExtra, false);
		$result = array();
		if ($users) {
			if ($fields) {
				foreach($users as $key => $user) {
					foreach($user as $k => $v) {
						if (in_array($k, $fields)) {
							$result[$key][$k] = $v;
						}
					}
				}
			}
		}

		if (!$result) {
			$result = $users;
		}

		return $result;
	}

	function onUsersGetFriendInfo($uId, $num = MY_FRIEND_NUM_LIMIT, $isExtra = false) {
		$users = $this->getUsers(array($uId), false, true, $isExtra, true, $num, false, true);

		$where = array('uid' => $uId);
		$totalNum = getcount('home_friend', $where);
		$friends = $users[0]['friends'];
		unset($users[0]['friends']);
		$result = array('totalNum' => $totalNum,
				'friends' => $friends,
				'me' => $users[0],
				);
		return $result;
	}

	function onUsersGetExtraInfo($uIds) {
		$result = $this->getExtraByUsers($uIds);
		return $result;
	}

	function onUsersGetFormHash($uId, $userAgent) {
		global $_G;
		$uId = intval($uId);
		if (!$uId) {
			return false;
		}
		$sql = sprintf('SELECT * FROM %s WHERE uid = %s', DB::table('common_member'), $uId);
		$member = DB::fetch_first($sql);
		$_G['username'] = $member['username'];
		$_G['uid'] = $member['uid'];
		$_G['authkey'] = md5($_G['config']['security']['authkey'] . $userAgent);
		return formhash();
	}

	function onFriendsGet($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		$result = array();
		if ($uIds) {
			foreach($uIds as $uId) {
				$result[$uId] = $this->_getFriends($uId, $friendNum);
			}
		}
		return $result;
	}

	function onFriendsAreFriends($uId1, $uId2) {
		$query = DB::query("SELECT uid FROM ".DB::table('home_friend')." WHERE uid='$uId1' AND fuid='$uId2'");
		$result = false;
		if($friend = DB::fetch($query)) {
			$result = true;
		}

		return $result;
	}

	function onUserApplicationAdd($uId, $appId, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $defaultBoxType, $defaultMYML, $defaultProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null, $isFullscreen = null , $displayUserPanel = null, $additionalStatus = null) {
		global $_G;

		$res = $this->getUserSpace($uId);
		if (!$res) {
			return new ErrorResponse('1', "User($uId) Not Exists");
		}

		$sql = sprintf('SELECT appid FROM %s WHERE uid = %d AND appid = %d', DB::table('home_userapp'), $uId, $appId);
		$query = DB::query($sql);
		$row = DB::fetch($query);

		if ($row['appid']) {
			$errCode = '170';
			$errMessage = 'Application has been already added';
			return new ErrorResponse($errCode, $errMessage);
		}

		switch($privacy) {
			case 'public':
				$privacy = 0;
				break;
			case 'friends':
				$privacy = 1;
				break;
			case 'me':
				$privacy = 3;
				break;
			case 'none':
				$privacy = 5;
				break;
			default:
				$privacy = 0;
		}

		$narrow = ($defaultBoxType == 'narrow') ? 1 : 0;

		$setarr = array('uid' => $uId,
				'appid' => $appId,
				'appname' => $appName,
				'privacy' => $privacy,
				'allowsidenav' => $allowSideNav,
				'allowfeed' => $allowFeed,
				'allowprofilelink' => $allowProfileLink,
				'narrow' => $narrow
				);
		if ($displayOrder !== null) {
			$setarr['displayorder'] = $displayOrder;
		}
		$maxMenuOrder = DB::result_first("SELECT MAX(menuorder) FROM ".DB::table('home_userapp')." WHERE uid='$uId'");
		$setarr['menuorder'] = ++$maxMenuOrder;

		DB::insert('home_userapp', $setarr);

		$fields = array('uid' => $uId,
				'appid' => $appId,
				'profilelink' => $defaultProfileLink,
				'myml' => $defaultMYML
				);
		$result = DB::insert('home_userappfield', $fields, 1);

		updatecreditbyaction('installapp', $uId, array(), $appId);

		require_once libfile('function/cache');
		updatecache('userapp');

		DB::query("UPDATE ".DB::table('common_member_status')." SET lastactivity='$_G[timestamp]' WHERE uid='$uId'");

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, $narrow, null, null, $additionalStatus);

		return 1;
	}

	function onUserApplicationRemove($uId, $appIds) {
		$sql = sprintf('DELETE FROM %s WHERE uid = %d AND appid IN (%s)', DB::table('home_userapp'), $uId, dimplode($appIds));
		$res = DB::query($sql);

		$result = DB::affected_rows();

		$sql = sprintf('DELETE FROM %s WHERE uid = %d AND appid IN (%s)', DB::table('home_userappfield'), $uId, dimplode($appIds));
		$res = DB::query($sql);

		updatecreditbyaction('installapp', $uId, array(), $appId, -1);

		require_once libfile('function/cache');
		updatecache('userapp');

		return $result;
	}

	function onUserApplicationUpdate($uId, $appIds, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null, $isFullscreen = null, $displayUserPanel = null) {
		switch($privacy) {
			case 'public':
				$privacy = 0;
				break;
			case 'friends':
				$privacy = 1;
				break;
			case 'me':
				$privacy = 3;
				break;
			case 'none':
				$privacy = 5;
				break;
			default:
				$privacy = 0;
		}

		$where = sprintf('uid = %d AND appid IN (%s)', $uId, dimplode($appIds));
		$setarr = array(
			'appname'	=> $appName,
			'privacy'	=> $privacy,
			'allowsidenav'	=> $allowSideNav,
			'allowfeed'		=> $allowFeed,
			'allowprofilelink'	=> $allowProfileLink
		);
		if ($displayOrder !== null) {
			$setarr['displayorder'] = $displayOrder;
		}
		DB::update('home_userapp', $setarr, $where);

		$result = DB::affected_rows();

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		if (is_array($appIds)) {
			foreach($appIds as $appId) {
				$this->refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, null, null, null, null);
			}
		}

		return $result;
	}

	function onUserApplicationGetInstalled($uId) {
		$sql = sprintf('SELECT appid FROM %s WHERE uid = %d', DB::table('home_userapp'), $uId);
		$query = DB::query($sql);
		$result = array();
		while ($userApp = DB::fetch($query)) {
			$result[] = $userApp['appid'];
		}
		return $result;
	}

	function onUserApplicationGet($uId, $appIds) {
		$sql = sprintf('SELECT * FROM %s WHERE uid = %d AND appid IN (%s)', DB::table('home_userapp'), $uId, dimplode($appIds));
		$query = DB::query($sql);

		$result = array();
		while($userApp = DB::fetch($query)) {
			switch($userApp['privacy']) {
				case 0:
					$privacy = 'public';
					break;
				case 1:
					$privacy = 'friends';
					break;
				case 3:
					$privacy = 'me';
					break;
				case 5:
					$privacy = 'none';
					break;
				default:
					$privacy = 'public';
			}
			$result[] = array(
						'appId'		=> $userApp['appid'],
						'privacy'	=> $privacy,
						'allowSideNav'		=> $userApp['allowsidenav'],
						'allowFeed'			=> $userApp['allowfeed'],
						'allowProfileLink'	=> $userApp['allowprofilelink'],
						'displayOrder'		=> $userApp['displayorder']
						);
		}
		return $result;
	}

	function onFeedPublishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		$res = $this->getUserSpace($uId);
		if (!$res) {
			return new ErrorResponse('1', "User($uId) Not Exists");
		}

		$friend = ($privacy == 'public') ? 0 : ($privacy == 'friends' ? 1 : 2);

		$images = array($image1, $image2, $image3, $image4);
		$image_links = array($image1Link, $image2Link, $image3Link, $image4Link);

		$titleTemplate = $this->_myStripslashes($titleTemplate);
		$titleData = $this->_myStripslashes($titleData);
		$bodyTemplate = $this->_myStripslashes($bodyTemplate);
		$bodyData = $this->_myStripslashes($bodyData);
		$bodyGeneral = $this->_myStripslashes($bodyGeneral);

		require_once libfile('function/feed');
		$result = feed_add($appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral, $images, $image_links, $targetIds, $friend, $specialAppid, 1);

		return $result;
	}

	function onNotificationsSend($uId, $recipientIds, $appId, $notification) {
		$this->getUserSpace($uId);

		$result = array();

		$notification = $this->_myStripslashes($notification);

		foreach($recipientIds as $recipientId) {
			$val = intval($recipientId);
			if($val) {
				if ($uId) {
					$result[$val] = notification_add($val, $appId, $notification) === null;
				} else {
					$result[$val] = notification_add($val, $appId, $notification, array(), 1) === null;
				}
			} else {
				$result[$recipientId] = null;
			}
		}
		return $result;
	}

	function onNotificationsGet($uId) {
		$notify = $result = array();
		$result = array(
			'message' => array(
				'unread' => 0,
				'mostRecent' => 0
			),
			'notification' => array(
				'unread' => 0 ,
				'mostRecent' => 0
			),
			'friendRequest' => array(
				'uIds' => array()
			)
		);

		$query = DB::query("SELECT * FROM ".DB::table('home_notification')." WHERE uid='$uId' AND new='1' ORDER BY id DESC");
		$i = 0;
		while($value = DB::fetch($query)) {
			$i++;
			if(!$result['notification']['mostRecent']) $result['notification']['mostRecent'] = $value['dateline'];
		}
		$result['notification']['unread'] = $i;

		loaducenter();
		$pmarr = uc_pm_list($uId, 1, 1, 'newbox', 'newpm');
		if($pmarr['count']) {
			$result['message']['unread'] = $pmarr['count'];
			$result['message']['mostRecent'] = $pmarr['data'][0]['dateline'];
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_friend_request')." WHERE uid='$uId' ORDER BY dateline DESC");
		$fIds = array();
		while($value = DB::fetch($query)) {
			if(!$result['friendRequest']['mostRecent']) {
				$result['friendRequest']['mostRecent'] = $value['dateline'];
			}
			$fIds[] = $value['uid'];
		}
		$result['friendRequest']['uIds'] = $fIds;

		return $result;
	}

	function onApplicationUpdate($appId, $appName, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null, $isFullscreen = null, $displayUserPanel = null, $additionalStatus = null) {
		$query = DB::query(sprintf('SELECT appname FROM %s WHERE appid=%d', DB::table('common_myapp'), $appId));
		$row = DB::fetch($query);
		$result = true;
		if ($row['appname'] != $appName) {
			$fields = array('appname' => $appName);
			$where = array('appid'	=> $appId);
			$result = DB::update('home_userapp', $fields, $where);

			require_once libfile('function/cache');
			updatecache('userapp');
		}

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, null, null, $displayOrder, $additionalStatus);
		return $result;
	}

	function onApplicationRemove($appIds) {
		$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', DB::table('home_userapp'), dimplode($appIds));
		$result = DB::query($sql);

		$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', DB::table('home_userappfield'), dimplode($appIds));
		$result = DB::query($sql);

		$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', DB::table('common_myapp'), dimplode($appIds));
		DB::query($sql);

		require_once libfile('function/cache');
		updatecache(array('userapp', 'myapp'));

		return $result;
	}

	function onApplicationSetFlag($applications, $flag) {
		$flag = ($flag == 'disabled') ? -1 : ($flag == 'default' ? 1 : 0);
		$appIds = array();
		if ($applications && is_array($applications)) {
			foreach($applications as $application) {
				$this->refreshApplication($application['appId'], $application['appName'], null, null, null, null, null, null, null, $flag, null, null);
				$appIds[] = $application['appId'];
			}
		}

		if ($flag == -1) {
			$sql = sprintf('DELETE FROM %s WHERE icon IN (%s)', DB::table('home_feed'), dimplode($appIds));
			DB::query($sql);

			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', DB::table('home_userapp'), dimplode($appIds));
			DB::query($sql);

			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', DB::table('home_userappfield'), dimplode($appIds));
			DB::query($sql);

			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', DB::table('common_myinvite'), dimplode($appIds));
			DB::query($sql);

			$sql = sprintf('DELETE FROM %s WHERE type IN (%s)', DB::table('home_notification'), dimplode($appIds));
			DB::query($sql);
		}

		require_once libfile('function/cache');
		updatecache('userapp');

		$result = true;
		return $result;
	}

	function onCreditGet($uId) {
		global $_G;

		$_G['setting']['myapp_credit'] = '';
		if($_G['setting']['creditstransextra'][7]) {
			$_G['setting']['myapp_credit'] = 'extcredits'.intval($_G['setting']['creditstransextra'][7]);
		} elseif ($_G['setting']['creditstrans']) {
			$_G['setting']['myapp_credit'] = 'extcredits'.intval($_G['setting']['creditstrans']);
		}

		if(empty($_G['setting']['myapp_credit'])) {
			return 0;
		}

		$query = DB::query('SELECT '.$_G['setting']['myapp_credit'].' AS credit FROM '
					. DB::table('common_member_count') . ' WHERE uid =' . $uId);
		$row = DB::fetch($query);
		return $row['credit'];
	}

	function onRequestSend($uId, $recipientIds, $appId, $requestName, $myml, $type) {
		$now = time();
		$result = array();
		$type = ($type == 'request') ? 1 : 0;

		$fields = array('typename' => $requestName,
				'appid' => $appId,
				'type' => $type,
				'fromuid' => $uId,
				'dateline' => $now
				);

		foreach($recipientIds as $key => $val) {
			$hash = crc32($appId . $val . $now . rand(0, 1000));
			$hash = sprintf('%u', $hash);
			$fields['touid'] = intval($val);
			$fields['hash'] = $hash;
			$fields['myml'] = str_replace('{{MyReqHash}}', $hash, $myml);
			$result[] = DB::insert('common_myinvite', $fields, 1);

			$note = array(
					'from_id' => $fields['touid'],
					'from_idtype' => 'myappquery'
				);
			notification_add($fields['touid'], 'myapp', 'myinvite_request', $note);
		}
		return $result;
	}

	function onVideoAuthSetAuthStatus($uId, $status) {
		if ($status == 'approved') {
			$status = 1;
			updatecreditbyaction('videophoto', $uId);
		} else if($status == 'refused') {
			$status = 0;
		} else {
			$errCode = '200';
			$errMessage = 'Error arguments';
			return new ErrorResponse($errCode, $errMessage);
		}

		DB::update('common_member', array('videophotostatus' => $status), array('uid' => $uId));
		$result = DB::affected_rows();
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_verify')." WHERE uid='$uId'"), 0);
		if(!$count) {
			DB::insert('common_member_verify', array('uid' => $uId, 'verify7' => $status));
		} else {
			DB::update('common_member_verify', array('verify7' => $status), array('uid' => $uId));
		}
		return $result;
	}

	function onVideoAuthAuth($uId, $picData, $picExt = 'jpg', $isReward = false) {
		global $_G;
		$res = $this->getUserSpace($uId);
		if (!$res) {
			return new ErrorResponse('1', "User($uId) Not Exists");
		}
		$allowPicType = array('jpg','jpeg','gif','png');
		if(in_array($picExt, $allowPicType)) {
			$pic = base64_decode($picData);
			if (!$pic || strlen($pic) == strlen($picData)) {
				$errCode = '200';
				$errMessage = 'Error argument';
				return new ErrorResponse($errCode, $errMessage);
			}

			$secret = md5($_G['timestamp']."\t".$_G['uid']);
			$picDir = DISCUZ_ROOT . './data/avatar/' . substr($secret, 0, 1);
			if (!is_dir($picDir)) {
				if (!mkdir($picDir, 0777)) {
					$errCode = '300';
					$errMessage = 'Cannot create directory';
					return new ErrorResponse($errCode, $errMessage);
				}
			}

			$picDir .= '/' . substr($secret, 1, 1);
			if (!is_dir($picDir)) {
				if (!@mkdir($picDir, 0777)) {
					$errCode = '300';
					$errMessage = 'Cannot create directory';
					return new ErrorResponse($errCode, $errMessage);
				}
			}

			$picPath = $picDir . '/' . $secret . '.' . $picExt;
			$fp = @fopen($picPath, 'wb');
			if ($fp) {
				if (fwrite($fp, $pic) !== FALSE) {
					fclose($fp);

					require_once libfile('class/upload');
					$upload = new discuz_upload();
					if(!$upload->get_image_info($picPath)) {
						@unlink($picPath);
					} else {
						DB::update('common_member', array('videophotostatus'=>1), array('uid' => $uId));
						$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_verify')." WHERE uid='$uId'"), 0);
						if(!$count) {
							DB::insert('common_member_verify', array('uid' => $uId, 'verify7' => 1));
						} else {
							DB::update('common_member_verify', array('verify7' => 1), array('uid' => $uId));
						}
						$fields = array('videophoto' => $secret);
						DB::update('common_member_field_home', $fields, array('uid' => $uId));
						$result = DB::affected_rows();

						if ($isReward) {
							updatecreditbyaction('videophoto', $uId);
						}
						return $result;
					}
				}
				fclose($fp);
			}
		}
		$errCode = '300';
		$errMessage = 'Video Auth Error';
		return new ErrorResponse($errCode, $errMessage);
	}

	function _convertPrivacy($privacy, $u2m = false) {
		$privacys = array(0=>'public', 1=>'friends', 2=>'someFriends', 3=>'me', 4=>'passwd');
		$privacys = ($u2m) ? $privacys : array_flip($privacys);
		return $privacys[$privacy];
	}

	function _spaceInfo2Extra($rows) {
		$privacy = unserialize($rows['privacy']);
		$profilePrivacy = $privacy['profile'];

		$res = array();
		$map = array(
					 'graduateschool' => array('edu', 'school', true),
					 'company' => array('work', 'company', true),
					 'lookingfor' => array('trainwith', 'value'),
					 'interest' => array('interest', 'value'),
					 'bio' => array('intro', 'value')
					 );

		foreach ($map as $dzKey => $myKeys) {
			if ($rows[$dzKey]) {
				$data = array('privacy' => $this->_convertPrivacy($profilePrivacy[$dzKey], true), $myKeys[1] => $rows[$dzKey]);
				if ($myKeys[2]) {
					$res[$myKeys[0]][] = $data;
				} else {
					$res[$myKeys[0]] = $data;
				}
			}
		}

		return $res;
	}

	function _friends2friends($friends , $num, $isOnlyReturnId = false, $isFriendIdKey = false) {
		$i = 1;
		$res = array();
		foreach($friends as $friend) {
			if ($num) {
				if ($i > $num) {
					continue;
				}
			}
			if ($isOnlyReturnId) {
				$row = $friend['fuid'];
			} else {
				$row = array('uId' => $friend['fuid'],
						'handle' => $friend['fusername']
						);
			}
			if ($isFriendIdKey) {
				$res[$friend['fuid']] = $row;
			} else {
				$res[] = $row;
			}
			$i++;
		}
		return $res;
	}

	function _space2user($space) {
		global $_G;

		if(!$space) {
			return array();
		}
		$founders = empty($_G['config']['admincp']['founder'])?array():explode(',', $_G['config']['admincp']['founder']);
		$adminLevel = 'none';
		if($space['groupid'] == 1 && $space['adminid'] == 1) {
			$adminLevel = 'manager';
			if($founders
			 && (in_array($space['uid'], $founders)
			 || (!is_numeric($space['username']) && in_array($space['username'], $founders)))) {
				$adminLevel = 'founder';
			}
		}

		$privacy = unserialize($space['privacy']);
		if (!$privacy) {
			$privacy = array();
		}

		$profilePrivacy = array();
		$map = array('affectivestatus' => 'relationshipStatus',
					 'birthday' => 'birthday',
					 'bloodtype' => 'bloodType',
					 'birthcity' => 'birthPlace',
					 'residecity' => 'residePlace',
					 'mobile' => 'mobile',
					 'qq' => 'qq',
					 'msn' => 'msn');
		$privacys = unserialize($space['privacy']);
		foreach ($map as $dzKey => $myKey) {
			$profilePrivacy[$myKey] = $this->_convertPrivacy($privacys['profile'][$dzKey], true);
		}

		$user = array(
			'uId'		=> $space['uid'],
			'handle'	=> $space['username'],
			'action'	=> $space['action'],
			'realName'	=> $space['realname'],
			'realNameChecked' => $space['realname'] ? true : false,
			'gender'	=> $space['gender'] == 1 ? 'male' : ($space['gender'] == 2 ? 'female' : 'unknown'),
			'email'		=> $space['email'],
			'qq'		=> $space['qq'],
			'msn'		=> $space['msn'],
			'birthday'	=> sprintf('%04d-%02d-%02d', $space['birthyear'], $space['birthmonth'], $space['birthday']),
			'bloodType'	=> empty($space['bloodtype']) ? 'unknown' : $space['bloodtype'],
			'relationshipStatus' => $space['affectivestatus'],
			'birthProvince' => $space['birthprovince'],
			'birthCity'	=> $space['birthcity'],
			'resideProvince' => $space['resideprovince'],
			'resideCity'	=> $space['residecity'],
			'viewNum'	=> $space['views'],
			'friendNum'	=> $space['friends'],
			'myStatus'	=> $space['spacenote'],
			'lastActivity' => $space['lastactivity'],
			'created'	=> $space['regdate'],
			'credit'	=> $space['credits'],
			'isUploadAvatar'	=> $space['avatarstatus'] ? true : false,
			'adminLevel'		=> $adminLevel,

			'homepagePrivacy'	=> $this->_convertPrivacy($privacy['view']['index'], true),
			'profilePrivacyList'	=> $profilePrivacy,
			'friendListPrivacy'	=> $this->_convertPrivacy($privacy['view']['friend'], true)
			);
		return $user;
	}

	function _getFriends($uId, $num = null) {
		global $_G;

		$sql = sprintf('SELECT fuid FROM %s WHERE uid = %d', DB::table('home_friend'), $uId);
		if ($num) {
			$sql .= ' LIMIT 0, ' . $num;
		}
		$fquery = DB::query($sql);
		$friends = array();
		while($friend = DB::fetch($fquery)) {
			$friends[] = $friend['fuid'];
		}
		return $friends;
	}

	function refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, $narrow, $flag, $displayOrder, $additionalStatus) {
		global $_G;

		$fields = array();
		if ($appName !== null && strlen($appName)>1) {
			$fields['appname'] = $appName;
		}
		if ($version !== null) {
			$fields['version'] = $version;
			$fields['iconstatus'] = 0;
			$fields['icondowntime'] = 0;
		}
		if ($displayMethod !== null) {
			$fields['displaymethod'] = $displayMethod;
		}
		if ($narrow !== null) {
			$fields['narrow'] = $narrow;
		}
		if ($flag !== null) {
			$fields['flag'] = $flag;
		}
		if ($displayOrder !== null) {
			$fields['displayorder'] = $displayOrder;
		}
		if ($userPanelArea !== null) {
			$fields['userpanelarea'] = $userPanelArea;
		}
		if ($canvasTitle !== null) {
			$fields['canvastitle'] = $canvasTitle;
		}
		if ($isFullscreen !== null) {
			$fields['fullscreen'] = $isFullscreen;
		}
		if ($displayUserPanel !== null) {
			$fields['displayuserpanel'] = $displayUserPanel;
		}
		if ($additionalStatus !== null) {
			$fields['appstatus'] = $additionalStatus == 'new' ? 1 : ($additionalStatus == 'none' ? 0 : 2);
		}

		$sql = sprintf('SELECT * FROM %s WHERE appid = %d', DB::table('common_myapp'), $appId);
		$query = DB::query($sql);
		$result = false;
		if($application = DB::fetch($query)) {
			$needUpdate = false;
			foreach ($fields as $key => $value) {
				if ($value != $application[$key]) {
					$needUpdate = true;
					break;
				}
			}
			if ($needUpdate) {
				$where = sprintf('appid = %d', $appId);
				DB::update('common_myapp', $fields, $where);
			}
			$result = true;
		} else {
			$fields['appid'] = $appId;
			$result = DB::insert('common_myapp', $fields, 1);
			$result = true;
		}
		require_once libfile('function/cache');
		updatecache(array('myapp', 'userapp'));

		return $result;
	}

	function getUsers($uIds, $spaces = array(), $isReturnSpaceField = true, $isExtra = true, $isReturnFriends = false, $friendNum = MY_FRIEND_NUM_LIMIT, $isOnlyReturnFriendId = false, $isFriendIdKey = false) {
		if (!$uIds) {
			return array();
		}

		if (!is_array($uIds)) {
			$uIds = (array)$uIds;
		}

		if (!$spaces) {
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode(', ', $uIds));
			$query = DB::query($sql);
			$users2 = array();
			while($row = DB::fetch($query)) {
				$spaces[$row['uid']] = $row;
			}
		}

		$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('common_member_count'), implode(', ', $uIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$spaces[$row['uid']] = array_merge($spaces[$row['uid']], $row);
		}

		$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('common_member_field_home'), implode(', ', $uIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$spaces[$row['uid']] = array_merge($spaces[$row['uid']], $row);
		}

		$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('common_member_status'), implode(', ', $uIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$spaces[$row['uid']] = array_merge($spaces[$row['uid']], $row);
		}

		$spaceFields = array();
		if ($isReturnSpaceField) {
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('common_member_profile'), implode(', ', $uIds));
			$query = DB::query($sql);
			while($row = DB::fetch($query)) {
				$spaceFields[$row['uid']] = $row;
			}
		}

		$sql = sprintf('SELECT uid, privacy FROM %s WHERE uid IN (%s)', DB::table('common_member_field_home'), implode(', ', $uIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$spaceFields[$row['uid']] = array_merge($spaceFields[$row['uid']], $row);
		}

		$friends = array();
		if ($isReturnFriends) {
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('home_friend'), implode(', ', $uIds));
			$query = DB::query($sql);
			while($row = DB::fetch($query)) {
				$friends[$row['uid']][] = $row;
			}
		}

		$users = array();
		foreach($uIds as $uId) {
			$space = $spaces[$uId];
			if ($isReturnSpaceField) {
				$space = array_merge($spaceFields[$uId], $space);
			}
			$user = $this->_space2user($space);
			if (!$user) {
				continue;
			}

			if ($isExtra) {
				$user['extra'] = $this->_spaceInfo2Extra($spaceFields[$uId]);
			}

			if ($isReturnFriends) {
				$user['friends'] = $this->_friends2friends($friends[$uId], $friendNum, $isOnlyReturnFriendId, $isFriendIdKey);
			}
			$users[] = $user;
		}
		return $users;
	}

	function getExtraByUsers($uIds) {
		if (!$uIds) {
			return array();
		}

		if (!is_array($uIds)) {
			$uIds = (array)$uIds;
		}

		$spaceFields = array();
		$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', DB::table('common_member_profile'), implode(', ', $uIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$spaceFields[$row['uid']] = $row;
		}

		$sql = sprintf('SELECT uid, privacy FROM %s WHERE uid IN (%s)', DB::table('common_member_field_home'), implode(', ', $uIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$spaceFields[$row['uid']] = array_merge($spaceFields[$row['uid']], $row);
		}

		$users = array();
		foreach($uIds as $uId) {
			$user = array('uId' => $uId,
					'extra' => $this->_spaceInfo2Extra($spaceFields[$uId]));
			$users[] = $user;
		}

		return $users;
	}

	function getUserSpace($uId) {
		global $_G;

		$space = getspace($uId);
		if (!$space['uid']) {
			return false;
		}

		$_G['uid'] = $space['uid'];
		$_G['username'] = $space['username'];

		return true;
	}

	function _myStripslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->_myStripslashes($val);
			}
		} else {
			$string = ($string === null) ? null : stripslashes($string);
		}
		return $string;
	}

	function onSearchGetUserGroupPermissions($userGroupIds) {
		if (!$userGroupIds) {
			return array();
		}
		$result = SearchHelper::getUserGroupPermissions($userGroupIds);
		return $result;
	}

	function onSearchGetUpdatedPosts($num, $lastPostIds = array()) {

		if ($lastPostIds) {
			$sql = sprintf("DELETE FROM %s WHERE pid IN (%s)", DB::table('forum_postlog'), implode($lastPostIds, ', '));
			DB::query($sql);
		}

		$result = array();

		$totalNum = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('forum_postlog'));
		if (!$totalNum) {
			return $result;
		}
		$result['totalNum'] = $totalNum;

		$sql = sprintf('SELECT * FROM %s
				 ORDER BY dateline
				LIMIT %d', DB::table('forum_postlog'), $num);
		$query = DB::query($sql);
		$pIds = $deletePosts = $updatePostIds = array();
		$unDeletePosts = array();
		$posts = array();
		while($post = DB::fetch($query)) {
			$pIds[] = $post['pid'];
			if ($post['action'] == 'delete') {
				$deletePosts[$post['pid']] = array('pId' => $post['pid'],
										 'action' => $post['action'],
										 'updated' => dgmdate($post['dateline'], 'Y-m-d H:i:s', 8),
										);
			} else {
				$unDeletePosts[$post['pid']] = array('pId' => $post['pid'],
													 'action' => $post['action'],
													'updated' => dgmdate($post['dateline'], 'Y-m-d H:i:s', 8),
													);
			}
		}

		if ($pIds) {
			if ($unDeletePosts) {
				$gfIds = array(); // groupForumIds
				$posts = $this->_getPosts(array_keys($unDeletePosts));
				foreach($unDeletePosts as $pId => $updatePost) {
					if ($posts[$pId]) {
						$unDeletePosts[$pId] = array_merge($updatePost, $posts[$pId]);
					} else {
						$unDeletePosts[$pId]['pId'] = 0;
					}
					if ($posts[$pId]['isGroup']) {
						$gfIds[$posts[$pId]['fId']] = $posts[$pId]['fId'];
					}
				}

			}
		}
		$result['data'] = $deletePosts + $unDeletePosts;
		$result['ids']['post'] = $pIds;
		return $result;
	}

	function onSearchRemovePostLogs($pIds) {
		if (!$pIds) {
			return false;
		}
		$sql = sprintf("DELETE FROM %s WHERE pid IN (%s)", DB::table('forum_postlog'), implode($pIds, ', '));
		DB::query($sql);
		return true;
	}

	function _preGetPosts($table, $pIds) {
		$sql = sprintf("SELECT * FROM %s WHERE pid IN (%s)",
					$table, implode(', ', $pIds));
		$query = DB::query($sql);
		$result = array();
		while($post = DB::fetch($query)) {
			$result[$post['pid']] = SearchHelper::convertPost($post);
		}

		return $result;
	}

	function _getPosts($pIds) {
		$tables = SearchHelper::getTables('post');
		$tableNum = count($tables);
		$posts = array();
		for($i = 0; $i< $tableNum; $i++) {
			$_posts = $this->_preGetPosts(DB::table($tables[$i]), $pIds);
			if ($_posts) {
				if (!$posts) {
					$posts = $_posts;
				} else {
					$posts = $posts + $_posts;
				}
				if (count($posts) == count($pIds)) {
					break;
				}
			}
		}

		if ($posts) {
			foreach($posts as $pId => $post) {
				$tIds[$post['pId']] = $post['tId'];
			}

			if ($tIds) {
				$gfIds = $vtIds = array(); // poll
				$threads = SearchHelper::getThreads($tIds);
				foreach($posts as $pId => $post) {
					$tId = $tIds[$pId];
					$posts[$pId]['isGroup'] = $threads[$tId]['isGroup'];
					if ($threads[$tId]['isGroup']) {
						$gfIds[$threads[$tId]['fId']] = $threads[$tId]['fId'];
					}
					if ($post['isThread']) {
						$posts[$pId]['threadInfo'] = $threads[$tId];
					}
					if ($threads[$tId]['specialType'] == 'poll') {
						$vtIds[$pId] = $tId;
					}
				}
				if ($vtIds) {
					$polls = SearchHelper::getPollInfo($vtIds);
					foreach($vtIds as $pId => $tId) {
						$posts[$pId]['threadInfo']['pollInfo'] = $polls[$tId];
					}
				}
				$guestPerm = SearchHelper::getGuestPerm($gfIds);
				foreach($posts as $pId => $post) {
					if (in_array($post['fId'], $guestPerm['allowForumIds'])) {
						$posts[$pId]['isPublic'] = true;
					} else {
						$posts[$pId]['isPublic'] = false;
					}
					if ($post['isThread']) {
						$posts[$pId]['threadInfo']['isPublic'] = $posts[$pId]['isPublic'];
					}
				}
			}
		}

		return $posts;
	}

	function onSearchGetPosts($pIds) {
		$authors = array();
		$posts = $this->_getPosts($pIds);
		if ($posts) {
			foreach($posts as $post) {
				$authors[$post['authorId']][] = $post['pId'];
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				$sql = sprintf('SELECT uid, username, groupid FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode($authorids, ', '));
				$query = DB::query($sql);
				while ($author = DB::fetch($query)) {
					$uids[$author['uid']] = $author['uid'];
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $pid) {
						$posts[$pid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $pid) {
						$posts[$pid]['authorStatus'] = 'ban';
					}
				}
			}
		}
		return $posts;
	}

	function _getNewPosts($table, $num, $fromPostId = 0) {

		$result = array();

		$sql = sprintf("SELECT * FROM %s
				WHERE pid > %d
				 ORDER BY pid ASC
				LIMIT %d", $table, $fromPostId, $num);
		$query = DB::query($sql);
		while($post = DB::fetch($query)) {
			$result['maxPid'] = $post['pid'];
			$result['data'][$post['pid']] = SearchHelper::convertPost($post);
		}

		return $result;
	}

	function onSearchGetNewPosts($num, $fromPostId = 0) {
		$res = $data = array();
		$tables = SearchHelper::getTables('post');
		$tableNum = count($tables);
		$maxPid = 0;
		for($i = 0; $i< $tableNum; $i++) {
			$_posts = $this->_getNewPosts(DB::table($tables[$i]), $num, $fromPostId);
			if ($_posts['data']) {
				if (!$data) {
					$data = $_posts['data'];
				} else {
					$data = $data + $_posts['data'];
				}
			}
			if ($maxPid < $_posts['maxPid']) {
				$maxPid = $_posts['maxPid'];
			}
		}

		$_postNum = 0;
		if ($maxPid) {
			for($j = $fromPostId + 1; $j <= $maxPid; $j++) {
				if (array_key_exists($j, $data)) {
					$_postNum++;
					$res['data'][$j] = $data[$j];
					$res['maxPid'] = $j;
					if ($_postNum == $num) {
						break;
					}
				}
			}
			if (!$res['maxPid']) {
				$res['maxPid'] = $maxPid;
			}
		}

		if ($res['data']) {
			$tIds = $autors = array();
			foreach($res['data'] as $pId => $post) {
				$authors[$post['authorId']][] = $post['pId'];
				$tIds[$pId] = $post['tId'];
			}

			if ($tIds) {
				$threads = SearchHelper::getThreads($tIds);
				foreach ($tIds as $pId => $tId) {
					$res['data'][$pId]['isGroup'] = $threads[$tId]['isGroup'];
					if ($res['data'][$pId]['isThread']) {
						$res['data'][$pId]['threadInfo'] = $threads[$tId];
					}
				}
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				$sql = sprintf('SELECT uid, username, groupid FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode($authorids, ', '));
				$query = DB::query($sql);
				while ($author = DB::fetch($query)) {
					$uids[$author['uid']] = $author['uid'];
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'ban';
					}
				}
			}
		}

		return $res;
	}

	function onSearchGetAllPosts($num, $pId = 0, $orderType = 'ASC') {
		$tables = SearchHelper::getTables('post');
		$tableNum = count($tables);
		$res = $data = $_tableInfo = array();
		$maxPid = $minPid = 0;
		for($i = 0; $i < $tableNum; $i++) {
			$_posts = $this->_getAllPosts(DB::table($tables[$i]), $num, $pId, $orderType);
			if ($_posts['data']) {
				if (!$data) {
					$data = $_posts['data'];
				} else {
					$data = $data + $_posts['data'];
				}
			}
			if ($orderType == 'DESC') {
				if (!$minPid) {
					$minPid = $_posts['minPid'];
				}
				if ($minPid > $_posts['minPid']) {
					$minPid = $_posts['minPid'];
				}
				$_tableInfo['minPids'][] = array('current_index' => $i,
												 'minPid' => $_posts['minPid'],
												);
			} else {
				if ($maxPid < $_posts['maxPid']) {
					$maxPid = $_posts['maxPid'];
				}
				$_tableInfo['maxPids'][] = array('current_index' => $i,
												 'maxPid' => $_posts['maxPid'],
												);
			}
		}
		$_postNum = 0;
		if ($orderType == 'DESC') {
			if ($minPid) {
				for($j = $pId - 1; $j >= $minPid; $j--) {
					if ($j == 0) {
						break;
					}
					if (array_key_exists($j, $data)) {
						$_postNum++;
						$res['minPid'] = $j;
						$res['data'][$j] = $data[$j];
						if ($_postNum == $num) {
							break;
						}
					}
				}
				if (!$res['minPid']) {
					$res['minPid'] = $minPid;
				}
			}
		} else {
			if ($maxPid) {
				for($j = $pId + 1; $j <= $maxPid; $j++) {
					if (array_key_exists($j, $data)) {
						$_postNum++;
						$res['data'][$j] = $data[$j];
						$res['maxPid'] = $j;
						if ($_postNum == $num) {
							break;
						}
					}
				}
				if (!$res['maxPid']) {
					$res['maxPid'] = $maxPid;
				}
			}
		}

		if ($res['data']) {
			$_tableInfo['tables'] = $tables;

			$tIds = $authors = $forums = array();
			foreach($res['data'] as $pId => $post) {
				$authors[$post['authorId']][] = $post['pId'];
				$tIds[$post['pId']] = $post['tId'];
			}

			if ($tIds) {
				$vtIds = $gfIds = array();
				$threads = SearchHelper::getThreads($tIds);
				foreach($tIds as $_pId => $tId) {
					$res['data'][$_pId]['isGroup'] = $threads[$tId]['isGroup'];
					$myPost = $res['data'][$_pId];

					if ($myPost['isGroup']) {
						$gfIds[$myPost['fId']] = $myPost['fId'];
					}

					if ($myPost['isThread']) {
						$res['data'][$_pId]['threadInfo'] = $threads[$tId];
						if ($threads[$tId]['specialType'] == 'poll') {
							$vtIds[$_pId] = $tId;
						}
					}
				}

				if ($vtIds) {
					$polls = SearchHelper::getPollInfo($vtIds);
					foreach($vtIds as $pId => $tId) {
						$res['data'][$pId]['threadInfo']['pollInfo'] = $polls[$tId];
					}
				}

				$guestPerm = SearchHelper::getGuestPerm($gfIds);
				foreach($res['data'] as $key => $row) {
					if (in_array($row['fId'], $guestPerm['allowForumIds'])) {
						$res['data'][$key]['isPublic'] = true;
					} else {
						$res['data'][$key]['isPublic'] = false;
					}
					if ($row['isThread']) {
						$res['data'][$key]['threadInfo']['isPublic'] = $res['data'][$key]['isPublic'];
					}
				}
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				$sql = sprintf('SELECT uid, username, groupid FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode($authorids, ', '));
				$query = DB::query($sql);
				while ($author = DB::fetch($query)) {
					$uids[$author['uid']] = $author['uid'];
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'ban';
					}
				}
			}

		}
		return $res;
	}

	function _getAllPosts($table, $num, $pId = 0, $orderType = 'ASC') {
		if ($orderType == 'DESC') {
			$op = '<';
			$key = 'minPid';
		} else {
			$op = '>';
			$key = 'maxPid';
		}
		$sql = sprintf("SELECT * FROM %s
				WHERE pid %s %d
				ORDER BY pid %s
				LIMIT %d", $table, $op, $pId, $orderType, $num);
		$query = DB::query($sql);
		$result = array();
		$tIds = $authors = array();
		while($post = DB::fetch($query)) {
			$result[$key] = $post['pid'];
			$result['data'][$post['pid']] = SearchHelper::convertPost($post);
		}
		return $result;
	}

	function _removeThreads($tIds, $isRecycle = false) {
		$tables = SearchHelper::getTables('thread');
		$tableThreads = array();
		foreach($tables as $table) {
			$_threads = SearchHelper::preGetThreads(DB::table($table), $tIds);
			$tableThreads[$table] = $_threads;
		}

		foreach($tableThreads as $table => $threads) {
			$_tids = $_threadIds = array();
			foreach($threads as $thread) {
				$_tids[] = $thread['tId'];
				$postTable = $thread['postTableId'] ? '_' . $thread['postTableId'] : '';
				$_threadIds[$postTable][] = $thread['tId'];
			}

			if ($_tids) {

				if ($isRecycle) {
					$sql = sprintf('UPDATE %s SET displayorder = -1 WHERE tid IN (%s)' , DB::table($table), implode(',', $_tids));
					DB::query($sql);
					continue;
				}

				$sql = sprintf('DELETE FROM %s WHERE tid IN (%s)' , DB::table($table), implode(',', $_tids));
				DB::query($sql);

				foreach($_threadIds as $postTable => $_tIds) {
					if ($_tIds) {
						$sql = sprintf('DELETE FROM %s WHERE tid IN (%s)' , DB::table('forum_post' . $postTable), implode(',', $_tIds));
						DB::query($sql);
					}
				}
			}
		}
		return true;
	}

	function onSearchRecyclePosts($pIds) {
		$tables = SearchHelper::getTables('post');

		$posts = array();
		foreach($tables as $table) {
			$_posts = $this->_preGetPosts(DB::table($table), $pIds);
			$posts[$table] = $_posts;
		}
		foreach($posts as $table => $rows) {
			$tids = $pids = array();
			foreach($rows as $row) {
				if ($row['isThread']) {
					$tids[] = $row['tId'];
				} else {
					$pids[] = $row['pId'];
				}
			}
			if ($pids) {
				$sql = sprintf('UPDATE %s SET invisible = -1 WHERE pid IN (%s)' , DB::table($table), implode(',', $pids));
				DB::query($sql);
			}

			if ($tids) {
				$this->_removeThreads($tids, true);
			}
		}
		return true;
	}

	function onSearchGetUpdatedThreads($num, $lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {

		if ($lastThreadIds) {
			DB::query('DELETE FROM ' . DB::table('forum_threadlog'). ' WHERE tid IN (' . implode($lastThreadIds, ', ') . ")");
		}
		if ($lastForumIds) {
			DB::query('DELETE FROM ' . DB::table('forum_threadlog') . ' WHERE fid IN (' . implode($lastForumIds, ', ') . ") AND tid = 0");
		}
		if ($lastUserIds) {
			DB::query('DELETE FROM ' . DB::table('forum_threadlog') . ' WHERE uid IN (' . implode($lastUserIds, ', ') . ") AND tid = 0");
		}

		$result = array();

		$totalNum = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('forum_threadlog'));
		if (!$totalNum) {
			return $result;
		}
		$result['totalNum'] = $totalNum;

		$tIds = $deleteThreads = $updateThreadIds = $otherLogs = $ids = array();
		$unDeleteThreads = array();
		$threads = array();
		$sql = sprintf('SELECT * FROM %s
				 ORDER BY dateline
				LIMIT %d', DB::table('forum_threadlog'), $num);
		$query = DB::query($sql);

		$otherActions = array('mergeforum', 'banuser', 'unbanuser', 'deluser', 'delforum');
		while($thread = DB::fetch($query)) {
			$tIds[] = $thread['tid'];
			if ($thread['action'] == 'delete') {
				$ids['thread'][] = $thread['tid'];
				$deleteThreads[$thread['tid']] = array('tId' => $thread['tid'],
										 'action' => 'delete',
										'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
										);
			} elseif (in_array($thread['action'], array('banuser', 'unbanuser', 'deluser'))) {
				$ids['user'][] = $thread['uid'];
				$expiry = 0;
				if ($thread['expiry']) {
					$expiry = dgmdate($thread['expiry'], 'Y-m-d H:i:s', 8);
				}
				$otherLogs[] = array('uId' => $thread['uid'],
									 'isDeletePost' => $thread['otherid'],
									 'action' => $thread['action'],
									 'expiry' => $expiry,
									 'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			} elseif (in_array($thread['action'], array('mergeforum', 'delforum'))) {
				$ids['forum'][] = $thread['fid'];
				$otherLogs[] = array('fId' => $thread['fid'],
									 'otherId' => $thread['otherid'],
									 'action' => $thread['action'],
									 'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			} elseif (in_array($thread['action'], array('merge'))) {
				$ids['thread'][] = $thread['tid'];
				$otherLogs[] = array('tId' => $thread['tid'],
									 'fId' => $thread['fId'],
									 'otherId' => $thread['otherid'],
									 'action' => $thread['action'],
									 'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			} else {
				$ids['thread'][] = $thread['tid'];
				$unDeleteThreads[$thread['tid']] = array('tId' => $thread['tid'],
									'action'  => $thread['action'],
									'otherId' => $thread['otherid'],
									'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			}
		}

		if ($tIds) {
			if ($unDeleteThreads) {
				$vtIds = $gfIds = array(); // poll, isPublic
				$threads = SearchHelper::getThreads(array_keys($unDeleteThreads));
				foreach($unDeleteThreads as $tId => $updateThread) {
					$vtIds[] = $tId;
					if ($threads[$tId]) {
						$unDeleteThreads[$tId] = array_merge($threads[$tId], $updateThread);
					} else {
						$unDeleteThreads[$tId]['tId'] = 0;
					}
					if ($threads[$tId]['isGroup']) {
						$gfIds[$threads[$tId]['fId']] = $threads[$tId]['fId'];
					}
				}
				$polls = SearchHelper::getPollInfo($vtIds);
				foreach($polls as $tId => $poll) {
					$unDeleteThreads[$tId]['pollInfo'] = $poll;
				}
				$guestPerm = SearchHelper::getGuestPerm($gfIds);
				foreach($unDeleteThreads as $tId => $row) {
					if (in_array($row['fId'], $guestPerm['allowForumIds'])) {
						$unDeleteThreads[$tId]['isPublic'] = true;
					} else {
						$unDeleteThreads[$tId]['isPublic'] = false;
					}
				}
			}
		}
		$result['data'] = $deleteThreads + $unDeleteThreads + $otherLogs;
		$result['ids'] = $ids;
		return $result;
	}

	function onSearchRemoveThreadLogs($lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {

		if ($lastThreadIds) {
			DB::query('DELETE FROM ' . DB::table('forum_threadlog') . ' WHERE tid IN (' . implode($lastThreadIds, ', ') . ')');
		}
		if ($lastForumIds) {
			DB::query('DELETE FROM ' . DB::table('forum_threadlog') . ' WHERE fid IN (' . implode($lastForumIds, ', ') . ') AND tid = 0');
		}
		if ($lastUserIds) {
			DB::query('DELETE FROM ' . DB::table('forum_threadlog') . ' WHERE uid IN (' . implode($lastUserIds, ', ') . ') AND tid = 0');
		}

		return true;
	}

	function _getThread($tId) {
		$result = SearchHelper::getThreads(array($tId));
		return $result[$tId];
	}

	function onSearchGetThreads($tIds) {
		$authors = $authorids = array();

		$result = SearchHelper::getThreads($tIds);
		if ($result) {
			$vtIds = $gfIds = array();
			foreach($result as $key => $thread) {
				$authors[$thread['authorId']][] = $thread['tId'];
				if ($thread['specialType'] == 'poll') {
					$vtIds[] = $thread['tId'];
				}
				if ($thread['isGroup'] ) {
					$gfIds[$thread['fId']] = $thread['fId'];
				}
			}
			$guestPerm = SearchHelper::getGuestPerm($gfIds);
			foreach($result as $key => $row) {
				if (in_array($row['fId'], $guestPerm['allowForumIds'])) {
					$result[$key]['isPublic'] = true;
				} else {
					$result[$key]['isPublic'] = false;
				}
			}
		}

		if ($vtIds) { // vote
			$polls = SearchHelper::getPollInfo($vtIds);
			foreach($polls as $tId => $poll) {
				$result[$tId]['pollInfo'] = $poll;
			}
		}

		$authorids = array_keys($authors);
		if ($authorids) {
			$banuids= $uids = array();
			$sql = sprintf('SELECT uid, username, groupid FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode($authorids, ', '));
			$query = DB::query($sql);
			while ($author = DB::fetch($query)) {
				$uids[$author['uid']] = $author['uid'];
				if ($author['groupid'] == 4 || $author['groupid'] == 5) {
					$banuids[] = $author['uid'];
				}
			}
			$deluids = array_diff($authorids, $uids);
			foreach($deluids as $deluid) {
				if (!$deluid) {
					continue;
				}
				foreach($authors[$deluid] as $tid) {
					$result[$tid]['authorStatus'] = 'delete';
				}
			}
			foreach($banuids as $banuid) {
				foreach($authors[$banuid] as $tid) {
					$result[$tid]['authorStatus'] = 'ban';
				}
			}
		}
		return $result;
	}

	function _getNewThreads($table, $num, $fromThreadId = 0) {
		$result = array();
		$fromThreadId = intval($fromThreadId);

		$sql = "SELECT * FROM $table
			WHERE tid > $fromThreadId
			ORDER BY tid ASC
			LIMIT $num";
		$query = DB::query($sql);
		while($thread = DB::fetch($query)) {
			$result['maxTid'] = $thread['tid'];
			$result['data'][$thread['tid']] = SearchHelper::convertThread($thread);
		}

		return $result;
	}

	function onSearchGetNewThreads($num, $tId = 0) {
		$tables = SearchHelper::getTables('thread');
		$tableNum = count($tables);
		$res = $data = $_tableInfo = array();
		$maxTid = 0;
		for($i = 0; $i < $tableNum; $i++) {
			$_threads = $this->_getNewThreads(DB::table($tables[$i]), $num, $tId);
			if ($_threads['data']) {
				if (!$data) {
					$data = $_threads['data'];
				} else {
					$data = $data + $_threads['data'];
				}
			}
			if ($maxTid < $_threads['maxTid']) {
				$maxTid = $_threads['maxTid'];
			}
			$_tableInfo['maxTids'][] = array('current_index' => $i,
											 'maxTid' => $_threads['maxTid'],
											);
		}
		$_threadNum = 0;
		if ($maxTid) {
			for($j = $tId + 1; $j <= $maxTid; $j++) {
				if (array_key_exists($j, $data)) {
					$_threadNum++;
					$res['maxTid'] = $j;
					$res['data'][$j] = $data[$j];
					if ($_threadNum == $num) {
						break;
					}
				}
			}
			if (!$res['maxTid']) {
				$res['maxTid'] = $maxTid;
			}
		}

		if ($res['data']) {
			$_tableInfo['tables'] = $tables;

			$postThreadIds = $authors = array();
			foreach($res['data'] as $tId => $thread) {
				$authors[$thread['authorId']][] = $thread['tId'];
				$postThreadIds[$thread['postTableId']][] = $thread['tId'];
			}


			$threadPosts = SearchHelper::getThreadPosts($postThreadIds);
			foreach($res['data'] as $tId => $v) {
				$res['data'][$tId]['pId'] = $threadPosts[$tId]['pId'];
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				$sql = sprintf('SELECT uid, username, groupid FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode($authorids, ', '));
				$query = DB::query($sql);
				while ($author = DB::fetch($query)) {
					$uids[$author['uid']] = $author['uid'];
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'ban';
					}
				}
			}
		}
		return $res;
	}

	function _getAllThreads($table, $num, $tid = 0, $orderType = 'ASC') {

		$result = array();

		$op = ($orderType == 'DESC') ? '<' : '>';
		$key = ($orderType == 'DESC') ? 'minTid' : 'maxTid';
		$sql = sprintf("SELECT * FROM %s
				WHERE tid %s %d
				ORDER BY tid %s
				LIMIT %d", $table, $op, $tid, $orderType, $num);
		$query = DB::query($sql);
		$tIds = $vtIds = array();
		while($thread = DB::fetch($query)) {
			$result[$key] = $thread['tid'];
			$result['data'][$thread['tid']] = SearchHelper::convertThread($thread);
			if ($result['data'][$thread['tid']]['specialType'] == 'poll') {
				$vtIds[] = $thread['tid'];
			}
		}
		$polls = SearchHelper::getPollInfo($vtIds);
		foreach($polls as $tId => $poll) {
			$result['data'][$tId]['pollInfo'] = $poll;
		}
		return $result;
	}

	function onSearchGetAllThreads($num, $tId = 0, $orderType = 'ASC') {
		$orderType = strtoupper($orderType);
		$tables = SearchHelper::getTables('thread');
		$tableNum = count($tables);
		$res = $data = $_tableInfo = array();
		$minTid = $maxTid = 0;
		for($i = 0; $i < $tableNum; $i++) {
			$_threads = $this->_getAllThreads(DB::table($tables[$i]), $num, $tId, $orderType);
			if ($_threads['data']) {
				if (!$data) {
					$data = $_threads['data'];
				} else {
					$data = $data + $_threads['data'];
				}
			}
			if ($orderType == 'DESC') {
				if (!$minTid) {
					$minTid = $_threads['minTid'];
				}
				if ($minTid > $_threads['minTid']) {
					$minTid = $_threads['minTid'];
				}
				$_tableInfo['minTids'][] = array('current_index' => $i,
												 'minTid' => $_threads['minTid'],
												);
			} else {
				if ($maxTid < $_threads['maxTid']) {
					$maxTid = $_threads['maxTid'];
				}
				$_tableInfo['maxTids'][] = array('current_index' => $i,
												 'maxTid' => $_threads['maxTid'],
												);
			}
		}
		$_threadNum = 0;
		if ($orderType == 'DESC') {
			if ($minTid) {
				for($j = $tId - 1; $j >= $minTid; $j--) {
					if ($j == 0) {
						break;
					}
					if (array_key_exists($j, $data)) {
						$_threadNum++;
						$res['minTid'] = $j;
						$res['data'][$j] = $data[$j];
						if ($_threadNum == $num) {
							break;
						}
					}
				}
				if (!$res['minTid']) {
					$res['minTid'] = $minTid;
				}
			}
		} else {
			if ($maxTid) {
				for($j = $tId + 1; $j <= $maxTid; $j++) {
					if (array_key_exists($j, $data)) {
						$_threadNum++;
						$res['data'][$j] = $data[$j];
						$res['maxTid'] = $j;
						if ($_threadNum == $num) {
							break;
						}
					}
				}
				if (!$res['maxTid']) {
					$res['maxTid'] = $maxTid;
				}
			}
		}

		if ($res['data']) {
			$_tableInfo['tables'] = $tables;

			$_tIds = array();
			$authors = $gfIds = array();
			foreach($res['data'] as $tId => $thread) {
				$_tIds[$thread['postTableId']][] = $tId;
				$authors[$thread['authorId']][] = $thread['tId'];
				if ($thread['isGroup']) {
					$gfIds[$thread['fId']] = $thread['fId'];
				}
			}

			if ($_tIds) {
				$guestPerm = SearchHelper::getGuestPerm($gfIds); // GuestPerm
				$threadPosts = SearchHelper::getThreadPosts($_tIds);
				foreach($res['data'] as $tId => $v) {
					$res['data'][$tId]['pId'] = $threadPosts[$tId]['pId'];
					if (in_array($v['fId'], $guestPerm['allowForumIds'])) {
						$res['data'][$tId]['isPublic'] = true;
					} else {
						$res['data'][$tId]['isPublic'] = false;
					}
				}
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				$sql = sprintf('SELECT uid, username, groupid FROM %s WHERE uid IN (%s)', DB::table('common_member'), implode($authorids, ', '));
				$query = DB::query($sql);
				while ($author = DB::fetch($query)) {
					$uids[$author['uid']] = $author['uid'];
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'ban';
					}
				}
			}
		}
		return $res;
	}

	function onSearchGetForums($fIds = array()) {
		return SearchHelper::getForums($fIds);
	}

	function onSearchSetConfig($data) {
		global $_G;
		$searchData = unserialize($_G['setting']['my_search_data']);
		if (!is_array($searchData)) {
			$searchData = array();
		}

		foreach($data as $k => $v) {
			$searchData[$k] = $v;
		}

		$searchData = addslashes(serialize(dstripslashes($searchData)));
		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ('my_search_data', '$searchData')");
		require_once DISCUZ_ROOT . './source/function/function_cache.php';
		updatecache('setting');
		return true;
	}

	function onSearchGetConfig($keys) {
		global $_G;
		$maps = array(
					'hotWords' => 'srchhotkeywords',
					);
		$confs = array();
		foreach($keys as $key) {
			if ($fieldName = $maps[$key]) {
				$confs[$key] = $_G['setting'][$fieldName];
			}
		}
		return $confs;
	}

	function onSearchSetHotWords($data, $method = 'append', $limit = 0) {
		global $_G;

		$srchhotkeywords = array();
		if ($_G['setting']['srchhotkeywords']) {
			$srchhotkeywords = $_G['setting']['srchhotkeywords'];
		}
		$newHotWords = array();
		foreach($data as $k => $v) {
			$newHotWords[] = addslashes(dstripslashes($v));
		}

		switch ($method) {
			case 'overwrite':
				$hotWords = $newHotWords;
				break;
			case 'prepend':
				$hotWords = array_merge($newHotWords, $srchhotkeywords);
				break;
			case 'append':
				$hotWords = array_merge($srchhotkeywords, $newHotWords);
				break;
		}

		if ($limit) {
			$hotWords = array_slice($hotWords, 0, $limit);
		}
		$hotWords = array_unique($hotWords);

		$hotWords = implode("\n", $hotWords);

		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ('srchhotkeywords', '$hotWords')");
		require_once DISCUZ_ROOT . './source/function/function_cache.php';
		updatecache('setting');
		return true;
	}

	function onCommonGetConfig($keys) {
		global $_G;
		$confs = array();

		foreach ($keys as $key) {
			if ($key && $_G['setting']) {
				$setting = $_G['setting'];
				if ($key == 'search' && is_array($setting['search'])) {
					$conf = array();
					foreach ($setting['search'] as $app => $v) {
						$conf[$app] = array(
							'status' => $v['status'] ? true : false,
							'interval' => $v['searchctrl'],
							'frequence' => $v['maxspm'],
							'maxResults' => $v['maxsearchresults']
						);
					}
					$confs[$key] = $conf;
					continue;
				}

				if ($key == 'rewrite') {
					$conf = array();
					if ($setting['rewritestatus'] && $setting['rewriterule']) {
						$conf['compatible'] = $setting['rewritecompatible'] ? true : false;
						foreach($setting['rewriterule'] as $mod => $rule) {
							$conf['modules'][$mod]['rule'] = $rule;
							if (in_array($mod, $setting['rewritestatus'])) {
								$conf['modules'][$mod]['status'] = true;
							} else {
								$conf['modules'][$mod]['status'] = false;
							}
						}
					}
					$confs[$key] = $conf;
					continue;
				}
			}
		}

		return $confs;
	}

	function onCommonGetNavs($type = '') {
		switch($type) {
			case 'footer':
				$navtype = 1;
				break;
			case 'space':
				$navtype = 2;
				break;
			case 'my':
				$navtype = 3;
				break;
			case 'header':
				$navtype = 0;
				break;
		}
		$navs = array();
		$sql = "SELECT * FROM ".DB::table('common_nav');
		if ($type) {
			$sql .= " WHERE navtype = '$navtype'";
		}
		$sql .= ' ORDER BY displayorder';
		$query = DB::query($sql);
		$navs = $subNavs = array();
		while ($nav = DB::fetch($query)) {
			if (!$nav['parentid']) {
				$navs[$nav['id']] = SearchHelper::convertNav($nav);
			} else {
				$subNavs[$nav['id']] = $nav;
			}
		}
		foreach($subNavs as $k => $v) {
			$navs[$v['parentid']]['navs'][$v['id']] = SearchHelper::convertNav($v);
		}
		return $navs;
	}

	function onCloudGetApps($appName = '') {

		require_once libfile('function/cloud');
		$apps = getcloudapps(false);

		if ($appName) {
			$apps = array($appName => $apps[$appName]);
		}

		$apps['apiVersion'] = cloud_get_api_version();

		$apps['siteInfo'] = $this->_getBaseInfo();

		return $apps;
	}

	function onCloudSetApps($apps) {

		if (!is_array($apps)) {
			return false;
		}

		require_once libfile('function/cloud');

		$res = array();
		$res['apiVersion'] = cloud_get_api_version();

		foreach ($apps as $appName => $status) {
			$res[$appName] = setcloudappstatus($appName, $status, false, false);
		}

		require_once libfile('function/cache');
		updatecache(array('plugin', 'setting', 'styles'));

		$res['siteInfo'] = $this->_getBaseInfo();

		return $res;
	}

	function onCloudOpenCloud() {
		require_once libfile('function/cloud');
		$openStatus = openCloud();

		$res = array();
		$res['status'] = $openStatus;
		$res['siteInfo'] = $this->_getBaseInfo();

		return $res;
	}


	function _getBaseInfo() {
		global $_G;
		$info = array();

		loadcache(array('userstats', 'historyposts'));
		$indexData = memory('get', 'forum_index_page_1');
		if(is_array($indexData) && $indexData) {
			$indexData = array();
			$info['threads'] = $indexData['threads'] ? $indexData['threads'] : 0;
			$info['todayPosts'] = $indexData['todayposts'] ? $indexData['todayposts'] : 0;
			$info['allPosts'] = $indexData['posts'] ? $indexData['posts'] : 0;
		} else {
			$threads = $posts = $todayposts = 0;
			$sql = 'SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.inheritedmod, f.domain,
			f.forumcolumns, f.simple
			FROM '.DB::table('forum_forum')." f
			WHERE f.status='1'";
			$query = DB::query($sql);
			while($forum = DB::fetch($query)) {
				if($forum['type'] != 'group') {
					$threads += $forum['threads'];
					$posts += $forum['posts'];
					$todayposts += $forum['todayposts'];
				}
			}
			$info['threads'] = $threads ? $threads : 0;
			$info['allPosts'] = $posts ? $posts : 0;
			$info['todayPosts'] = $todayposts ? $todayposts : 0;
		}

		$info['members'] = $_G['cache']['userstats']['totalmembers'] ? intval($_G['cache']['userstats']['totalmembers']) : 0;
		$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
		$info['yesterdayPosts'] = intval($postdata[0]);

		return $info;
	}

	function onCloudGetStats() {
		global $_G;

		$info = array();

		$db = DB::object();
		$tableprelen = strlen($db->tablepre);
		$table = array(
			'forum_thread' => 'threads',
			'forum_post' => 'allPosts',
			'common_member' => 'members'
		);
		$query = DB::query("SHOW TABLE STATUS");
		while($row = DB::fetch($query)) {
			$tablename = substr($row['Name'], $tableprelen);
			if(!isset($table[$tablename])) {
				continue;
			}
			$info[$table[$tablename]] = $row['Rows'];
		}

		loadcache(array('historyposts'));
		$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
		$info['yesterdayPosts'] = intval($postdata[0]);

		$postnum = 0;
		$postrow = 0;
		$avg_posts = DB::result_first("SELECT AVG(post) FROM ".DB::table('common_stat')." ORDER BY daytime DESC LIMIT 0,30");

		$info['avgPosts'] = intval($avg_posts);
		$info['statsCode'] = $_G['setting']['statcode'];

		return $info;
	}

	function onConnectSetConfig($data) {
		global $_G;

		$settingFields = array('connectappid', 'connectappkey');
		if (!$data) {
			return false;
		}

		$connectData = $_G['setting']['connect'];
		if (!is_array($connectData)) {
			$connectData = array();
		}

		$settings = array();
		foreach($data as $k => $v) {
			if (in_array($k, $settingFields)) {
				$settings[] = "('$k', '$v')";
			} else {
				$connectData[$k] = $v;
			}
		}
		if ($connectData) {
			$connectValue = addslashes(serialize(dstripslashes($connectData)));
			$settings[] = "('connect', '$connectValue')";
		}

		if ($settings) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ".implode(',', $settings));
			require_once DISCUZ_ROOT . './source/function/function_cache.php';
			updatecache('setting');
			return true;
		}
		return false;
	}

	function onUnionAddAdvs($advs) {
		$result = array();
		if (is_array($advs)) {
			foreach($advs as $advid => $adv) {
				$data = $this->_addAdv($adv);
				if($data === true) {
					$result['succeed'][$advid] = $advid;
				} else {
					$result['failed'][$advid] = $data;
				}
			}

			require_once libfile('function/cache');
			updatecache('advs');
			updatecache('setting');
		} else {
			$result['error'] = 'no adv';
		}

		return $result;
	}

	function _addAdv($adv) {
		global $_G;

		foreach($adv as $k => $v) {
			$_G['gp_'.$k] = $v;
		}

		$type = $_G['gp_type'];
		$advlibfile = libfile('adv/'.$type, 'class');
		if (file_exists($advlibfile)) {
			require_once $advlibfile;
		} else {
			return 'err_1';
		}
		$advclass = 'adv_'.$type;
		$advclass = new $advclass;
		$advnew = $_G['gp_advnew'];

		$parameters = !empty($_G['gp_parameters']) ? $_G['gp_parameters'] : array();
		if(@in_array('custom', $advnew['targets'])) {
			$targetcustom = explode(',', $advnew['targetcustom']);
			$advnew['targets'] = array_merge($advnew['targets'], $targetcustom);
		}
		$advclass->setsetting($advnew, $parameters);

		$advnew['starttime'] = $advnew['starttime'] ? strtotime($advnew['starttime']) : 0;
		$advnew['endtime'] = $advnew['endtime'] ? strtotime($advnew['endtime']) : 0;

		if(!$advnew['title']) {
			return 'err_2';
		} elseif(strlen($advnew['title']) > 50) {
			return 'err_3';
		} elseif(!$advnew['style']) {
			return 'err_4';
		} elseif(!$advnew['targets']) {
			return 'err_5';
		} elseif($advnew['endtime'] && ($advnew['endtime'] <= TIMESTAMP || $advnew['endtime'] <= $advnew['starttime'])) {
			return 'err_6';
		} elseif(($advnew['style'] == 'code' && !$advnew['code']['html'])
			|| ($advnew['style'] == 'text' && (!$advnew['text']['title'] || !$advnew['text']['link']))
			|| ($advnew['style'] == 'image' && (!$_FILES['advnewimage'] && !$_G['gp_advnewimage'] || !$advnew['image']['link']))
			|| ($advnew['style'] == 'flash' && (!$_FILES['advnewflash'] && !$_G['gp_advnewflash'] || !$advnew['flash']['width'] || !$advnew['flash']['height']))) {
			return 'err_7';
		}

		$advid = DB::insert('common_advertisement', array('available' => 1, 'type' => $type), 1);

		if($advnew['style'] == 'image' || $advnew['style'] == 'flash') {
			$advnew[$advnew['style']]['url'] = $_G['gp_advnew'.$advnew['style']];
		}

		foreach($advnew[$advnew['style']] as $key => $val) {
			$advnew[$advnew['style']][$key] = dstripslashes($val);
		}

		$advnew['displayorder'] = isset($advnew['displayorder']) ? implode("\t", $advnew['displayorder']) : '';
		$advnew['code'] = $this->_encodeadvcode($advnew);

		$advnew['parameters'] = addslashes(serialize(array_merge(is_array($parameters) ? $parameters : array(), array('style' => $advnew['style']), $advnew['style'] == 'code' ? array() : $advnew[$advnew['style']], array('html' => $advnew['code']), array('displayorder' => $advnew['displayorder']))));
		$advnew['code'] = addslashes($advnew['code']);

		$query = DB::query("UPDATE ".DB::table('common_advertisement')." SET title='$advnew[title]', targets='$advnew[targets]', parameters='$advnew[parameters]', code='$advnew[code]', starttime='$advnew[starttime]', endtime='$advnew[endtime]' WHERE advid='$advid'");

		return true;
	}

	function _encodeadvcode($advnew) {
		switch($advnew['style']) {
			case 'code':
				$advnew['code'] = $advnew['code']['html'];
				break;
			case 'text':
				$advnew['code'] = '<a href="'.$advnew['text']['link'].'" target="_blank" '.($advnew['text']['size'] ? 'style="font-size: '.$advnew['text']['size'].'"' : '').'>'.$advnew['text']['title'].'</a>';
				break;
			case 'image':
				$advnew['code'] = '<a href="'.$advnew['image']['link'].'" target="_blank"><img src="'.$advnew['image']['url'].'"'.($advnew['image']['height'] ? ' height="'.$advnew['image']['height'].'"' : '').($advnew['image']['width'] ? ' width="'.$advnew['image']['width'].'"' : '').($advnew['image']['alt'] ? ' alt="'.$advnew['image']['alt'].'"' : '').' border="0"></a>';
				break;
			case 'flash':
				$advnew['code'] = '<embed width="'.$advnew['flash']['width'].'" height="'.$advnew['flash']['height'].'" src="'.$advnew['flash']['url'].'" type="application/x-shockwave-flash" wmode="transparent"></embed>';
				break;
		}
		return $advnew['code'];
	}

}

$siteId = $_G['setting']['my_siteid'];
$siteKey = $_G['setting']['my_sitekey'];
$timezone = $_G['setting']['timeoffset'];
$language = $_SC['language'] ? $_SC['language'] : 'zh_CN';
$version = $_G['setting']['version'];
$myAppStatus = $_G['setting']['my_app_status'];
$mySearchStatus = $_G['setting']['my_search_status'];

$my = new My($siteId, $siteKey, $timezone, $version, CHARSET, $language, $myAppStatus, $mySearchStatus);
$my->run();

?>