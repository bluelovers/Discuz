<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Manyou.php 17268 2010-09-28 03:36:38Z zhengqingpeng $
 */

define('MY_FRIEND_NUM_LIMIT', 2000);

class Manyou {

	var $siteId;
	var $siteKey;
	var $myVersion = '0.1';

	var $timezone;
	var $version;
	var $charset;
	var $language;

	var $myAppStatus;
	var $mySearchStatus;

	var $errno = 0;
	var $errmsg = '';

	function manyou($siteId, $siteKey, $timezone, $version, $charset, $language, $myAppStatus, $mySearchStatus) {
		$this->siteId = $siteId;
		$this->siteKey = $siteKey;
		$this->timezone = $timezone;
		$this->version = $version;
		$this->charset = $charset;
		$this->language = $language;
		$this->myAppStatus = $myAppStatus;
		$this->mySearchStatus = $mySearchStatus;
	}

	function run() {
		$response = $this->_processServerRequest();
		echo serialize($this->_formatLocalResponse($response));
	}

	function getApplicationIframe($aId, $uId) {
		$timestamp = time();
		$suffix = base64_decode(urldecode($_GET['suffix']));
		$extra = $_GET['my_extra'];
		$currentUrl = $this->_getCurrentUrl();
		$prefix = dirname($currentUrl) . '/';

		$url = 'http://apps.manyou.com/' . $aId;
		if ($suffix) {
			$url .= '/' . ltrim($suffix, '/');
		}
		$separator = strpos($suffix, '?') ? '&' : '?';
		$url .= $separator . 'my_uchId=' . $uId . '&my_sId=' . $this->siteId;
		$url .= '&my_prefix=' . urlencode($prefix) . '&my_suffix=' . urlencode($suffix);
		$url .= '&my_current=' . urlencode($currentUrl);
		$url .= '&my_extra=' . urlencode($extra);
		$url .= '&my_ts=' . $timestamp;
		$hash = md5($this->siteId . '|' . $uId . '|' . $aId . '|' . $currentUrl . '|' . $extra . '|' . $timestamp . '|' . $this->siteKey);
		$url .= '&my_sig=' . $hash;
		return <<<EOT
<script type="text/javascript" src="http://static.manyou.com/scripts/my_iframe.js"></script>
<script language="javascript">
var server = new MyXD.Server("ifm0");
server.registHandler("iframeHasLoaded");
server.start();
function iframeHasLoaded(ifm_id) {
	MyXD.Util.showIframe(ifm_id);
	document.getElementById("loading").style.display = "none";
}
</script>
<div id="loading" style="display:block; padding:100px 0; text-align:center; color:#999999; font-size:12px;">Loading...</div>
<iframe id="ifm0" frameborder="0" width="810" height="810" scrolling="no" style="position:absolute; top:-5000px; left:-5000px;" src="$url"></iframe>
EOT;
	}

	function getCpIframe($uId) {
		$prefix = 'http://uchome.manyou.com';
		$timestamp = time();
		$currentUrl = $this->_getCurrentUrl();
		$request = $_GET;
		unset($request['my_suffix']);
		$params = $request ? $request : array('ac' => 'userapp');
		$queryParams = array();
		foreach ($params as $key => $value) {
			$queryParams[] = $key . '=' . urlencode($value);
		}
		$pageUrl = dirname($currentUrl) . '/' . basename($_SERVER['SCRIPT_URL']) . '?' . join('&', $queryParams);
		if(!$_GET['my_suffix']) {
			$appId = intval($_GET['appid']);
			if ($appId) {
				$mode = $_GET['mode'];
				if ($mode == 'about') {
					$suffix = '/userapp/about?appId=' . $appId;
				} else {
					$suffix = '/userapp/privacy?appId=' . $appId;
				}
			} else {
				$suffix = '/userapp/list';
			}
		} else {
			$suffix = $_GET['my_suffix'];
		}
		$my_extra = $_GET['my_extra'] ? $_GET['my_extra'] : '';
		$delimiter = strrpos($suffix, '?') ? '&' : '?';
		$myUrl = $prefix . urldecode($suffix . $delimiter . 'my_extra=' . $my_extra);
		$hash = md5($this->siteId . '|' . $uId . '|' . $this->siteKey . '|' . $timestamp);
		$delimiter = strrpos($myUrl, '?') ? '&' : '?';
		$url = $myUrl . $delimiter . 's_id=' . $this->siteId . '&uch_id=' . $uId . '&uch_url=' . urlencode($pageUrl) . '&my_suffix=' . urlencode($suffix) . '&timestamp=' . $timestamp . '&my_sign=' . $hash;
		return <<<EOT
<script type="text/javascript" src="http://static.manyou.com/scripts/my_iframe.js"></script>
<script language="javascript">
var server = new MyXD.Server("ifm0");
server.registHandler("iframeHasLoaded");
server.start();
function iframeHasLoaded(ifm_id) {
	MyXD.Util.showIframe(ifm_id);
	document.getElementById("loading").style.display = "none";
}
</script>
<div id="loading" style="display:block; padding:100px 0; text-align:center; color:#999999; font-size:12px;">Loading...</div>
<iframe id="ifm0" frameborder="0" width="810" scrolling="no" height="810" style="position:absolute; top:-5000px; left:-5000px;" src="$url"></iframe>
EOT;
	}

	function _call($method, $params) {
		list($module, $method) = explode('.', $method);
		$response = $this->_callServerMethod($module, $method, $params);
		return $this->_formatServerResponse($response);
	}

	function _processServerRequest() {
		$request = $_POST;
		$module = $request['module'];
		$method = $request['method'];
		$params = $request['params'];

		if (!$module || !$method) {
			return new ErrorResponse('1', 'Invalid Method: ' . $method);
		}

		$params = stripslashes($params);
		$sign = $this->_generateSign($module, $method, $params, $this->siteKey);

		if ($sign != $request['sign']) {
			return new ErrorResponse('10', 'Error Sign');
		}

		$params = unserialize($params);
		$params = $this->_myAddslashes($params);

		return $this->_callLocalMethod($module, $method, $params);
	}

	function _formatLocalResponse($data) {
		$res = array(
					 'my_version' => $this->myVersion,
					 'timezone' => $this->timezone,
					 'version' => $this->version,
					 'charset' => $this->charset,
					 'language' => $this->language
					 );
		if (strtolower(get_class($data)) == 'response') {
			if (is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if (strtolower(get_class($result)) == 'response') {
						$res['result'][]  = $result->getResult();
					} else {
						$res['result'][] = array('errno' => $result->getErrno(),
												 'errmsg' =>  $result->getErrmsg()
												);
					}
				}
			} else {
				$res['result']  = $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getErrno();
			$res['errMessage'] = $data->getErrmsg();
		}
		return $res;
	}

	function _callLocalMethod($module, $method, $params) {
		if ($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				$response[] = $this->_callLocalMethod($param['module'], $param['method'], $param['params']);
			}
			return new Response($response, 'Batch');
		}

		$methodName = $this->_getMethodName($module, $method);
		if (method_exists($this, $methodName)) {
			$result = @call_user_func_array(array($this, $methodName), $params);
			if (is_object($result) && is_a($result, 'ErrorResponse')) {
				return $result;
			}
			return new Response($result);
		} else {
			return new ErrorResponse('2', 'Method not implemented: ' . $methodName);
		}
	}

	function _getMethodName($module, $method) {
		return 'on' . ucfirst($module) . ucfirst($method);
	}

	function _generateSign($module, $method, $params, $siteKey) {
		return md5($module . '|' . $method . '|' . $params . '|' . $siteKey);
	}

	function _getCurrentUrl() {
		$protocal = $_SERVER['HTTPS'] ? 'https' : 'http';
		$currentUrl = $protocal . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		if ($_SERVER['QUERY_STRING']) {
			$currentUrl .= '?' . $_SERVER['QUERY_STRING'];
		}
		return $currentUrl;
	}

	function _myAddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->_myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

	function onUsersGetInfo($uIds, $fields = array(), $isExtra = false) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUsersGetFriendInfo($uId, $num = MY_FRIEND_NUM_LIMIT, $isExtra = false) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUsersGetExtraInfo($uIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onFriendsGet($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onFriendsAreFriends($uId1, $uId2) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationAdd($uId, $appId, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink,  $defaultBoxType, $defaultMYML, $defaultProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null , $displayUserPanel = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationRemove($uId, $appIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationUpdate($uId, $appIds, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null, $displayUserPanel = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationGetInstalled($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationGet($uId, $appIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetUpdatedUsers($num) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetUpdatedFriends($num) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetAllUsers($from, $num, $friendNum = MY_FRIEND_NUM_LIMIT) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetStat($beginDate = null, $num = null, $orderType = 'ASC') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onFeedPublishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onNotificationsSend($uId, $recipientIds, $appId, $notification) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onNotificationsGet($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onApplicationUpdate($appId, $appName, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null, $displayUserPanel = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onApplicationRemove($appIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onApplicationSetFlag($applications, $flag) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onProfileSetMYML($uId, $appId, $markup, $actionMarkup) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onProfileSetActionLink($uId, $appId, $actionMarkup) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCreditGet($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCreditUpdate($uId, $credits, $appId, $note) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onRequestSend($uId, $recipientIds, $appId, $requestName, $myml, $type) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onVideoAuthSetAuthStatus($uId, $status) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onVideoAuthAuth($uId, $picData, $picExt = 'jpg', $isReward = false) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onMiniBlogPost($uId, $message, $clientIdentify, $ip = '') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onMiniBlogGet($uId, $num) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoCreateAlbum($uId, $name, $privacy, $passwd = null, $friendIds = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoUpdateAlbum($uId, $aId, $name = null, $privacy = null, $passwd = null, $friendIds = null, $coverId = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoRemoveAlbum($uId, $aId, $action = null , $targetAlbumId = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoGetAlbums($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoUpload($uId, $aId, $fileName, $fileType, $fileSize, $data, $caption = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoGet($uId, $aId, $pIds = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoUpdate($uId, $pId, $aId, $fileName = null, $fileType = null, $fileSize = null, $caption = null, $data = null ) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onPhotoRemove($uId, $pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onNewsFeedGet($uId, $num) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onImbotMsnSetBindStatus($uId, $op, $msn = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetUserGroupPermissions($userGroupIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetUpdatedPosts($num, $lastPostIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRemovePostLogs($pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetPosts($pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetNewPosts($num, $fromPostId = 0) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetAllPosts($num, $pId = 0, $orderType = 'ASC') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRemovePosts($pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetUpdatedThreads($num, $lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRemoveThreadLogs($lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetThreads($tIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetNewThreads($num, $tId = 0) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetAllThreads($num, $tId = 0, $orderType = 'ASC') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetForums($fIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCommonSetConfig($data = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCommonGetNav($type = '') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

}

class ErrorResponse {

	var $errno = 0;
	var $errmsg = '';

	function ErrorResponse($errno, $errmsg) {
		$this->errno = $errno;
		$this->errmsg = $errmsg;
	}

	function getErrno() {
		return $this->errno;
	}

	function getErrmsg() {
		return $this->errmsg;
	}

	function getResult() {
		return null;
	}

}

class Response {

	var $result;
	var $mode;

	function Response($res, $mode = null) {
		$this->result = $res;
		$this->mode = $mode;
	}

	function getResult() {
		return $this->result;
	}

	function getMode() {
		return $this->mode;
	}

}

class SearchHelper {

	function _convertForum($row) {
		$result = array();
		$map = array(
					'fid'	=> 'fId',
					'fup'	=> 'pId',
					'name'	=> 'fName',
					'type'	=> 'type',
					'displayorder'	=> 'displayOrder',
					);
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				$result[$map[$k]] = $v;
				continue;
			}

			if ($k == 'status') {
				$isGroup = false;
				switch ($v) {
					case '0' :
						$displayStatus = 'hidden';
						break;
					case '1' :
						$displayStatus = 'normal';
						break;
					case '2' :
						$displayStatus = 'some';
						break;
					case '3' :
						$displayStatus = 'normal';
						$isGroup = true;
						break;
					default :
						$displayStatus = 'unknown';
				}
				$result['displayStatus'] = $displayStatus;
				$result['isGroup'] = $isGroup;
			}
		}
		$result['sign'] = md5(serialize($result));
		return $result;
	}

	function getForums($fIds = array()) {

		if ($fIds) {
			$where = ' AND fid IN (' . implode(',', $fIds) . ')';
		}

		$result = array();
		$sql = sprintf("SELECT COUNT(*) FROM %s
				WHERE 1 %s", DB::table('forum_forum'), $where);

		$result['totalNum'] = DB::result_first($sql);

		$sql = sprintf("SELECT * FROM %s
				WHERE 1 %s
				ORDER BY fid",
				DB::table('forum_forum'), $where);
		$query = DB::query($sql);
		while($forum = DB::fetch($query)) {
			$result['data'][$forum['fid']] = self::_convertForum($forum);
		}

		if (!$fIds) {
			$result['sign'] = md5(serialize($result['data']));
		}
		return $result;
	}

	function getUserGroupPermissions($userGroupIds) {
		$fields = array(
						'groupid' => 'userGroupId',
						'grouptitle' => 'userGroupName',
						'readaccess'	=> 'readPermission',
						'allowvisit'	=> 'allowVisit',
						'allowsearch'	=> 'searchLevel',
						);
		$userGroups = array();
		$sql = sprintf("SELECT ug.groupid, ug.grouptitle, ug.allowvisit, ugf.readaccess, ugf.allowsearch
					   FROM %s ug
					  LEFT JOIN %s ugf ON ug.groupid = ugf.groupid
					  WHERE ug.groupid IN (%s)",
					  DB::table('common_usergroup'), DB::table('common_usergroup_field'), implode(',', $userGroupIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			foreach($row as $k => $v) {
				if (array_key_exists($k, $fields)) {
					if ($k == 'allowsearch') {
						$userGroups[$row['groupid']]['allowSearchAlbum'] = ($v & 8) ? true : false;
						$userGroups[$row['groupid']]['allowSearchBlog'] = ($v & 4) ? true : false;
						$userGroups[$row['groupid']]['allowSearchForum'] = ($v & 2) ? true : false;
						$userGroups[$row['groupid']]['allowSearchPortal'] = ($v & 1) ? true : false;
						$userGroups[$row['groupid']]['allowFulltextSearch'] = ($v & 32) ? true : false;
					} else {
						$userGroups[$row['groupid']][$fields[$k]] = $v;
					}
				}
				$userGroups[$row['groupid']]['forbidForumIds'] = array();
				$userGroups[$row['groupid']]['allowForumIds'] = array();
				$userGroups[$row['groupid']]['specifyAllowForumIds'] = array();
			}
		}

		$query = DB::query(sprintf('SELECT fid FROM %s where status IN (1, 2)', DB::table('forum_forum')));
		$fIds = array();
		while($row = DB::fetch($query)) {
			$fIds[$row['fid']] = $row['fid'];
		}

		$fieldForums = array();
		$query = DB::query(sprintf('SELECT * FROM %s where fid IN (%s)', DB::table('forum_forumfield'), implode($fIds, ', ')));
		while($row = DB::fetch($query)) {
			$fieldForums[$row['fid']] = $row;
		}

		foreach($fIds as $fId) {
			$row = $fieldForums[$fId];
			$allowViewGroupIds = array();
			if ($row['viewperm']) {
				$allowViewGroupIds = explode("\t", $row['viewperm']);
			}
			foreach($userGroups as $gid => $_v) {
				if ($row['password']) {
					$userGroups[$gid]['forbidForumIds'][] = $fId;
					continue;
				}
				$perm = unserialize($row['formulaperm']);
				if(is_array($perm)) {
					$spviewperm = explode("\t", $row['spviewperm']);
					if (in_array($gid, $spviewperm)) {
						$userGroups[$gid]['allowForumIds'][] = $fId;
						$userGroups[$gid]['specifyAllowForumIds'][] = $fId;
						continue;
					}
					if ($perm[0] || $perm[1] || $perm['users']) {
						$userGroups[$gid]['forbidForumIds'][] = $fId;
						continue;
					}
				}
				if (!$allowViewGroupIds) {
					$userGroups[$gid]['allowForumIds'][] = $fId;
				} elseif (!in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['forbidForumIds'][] = $fId;
				} elseif (in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['allowForumIds'][] = $fId;
					$userGroups[$gid]['specifyAllowForumIds'][] = $fId;
				}
			}
		}

		foreach($userGroups as $k => $v) {
			ksort($v);
			$userGroups[$k]['sign'] = md5(serialize($v));
		}
		return $userGroups;
	}


	function convertThread($row) {
		$result = array();
		$map = array(
					'tid'	=> 'tId',
					'fid'	=> 'fId',
					'authorid'	=> 'authorId',
					'author'	=> 'authorName',
					'special'	=> 'specialType',
					'price'	=> 'price',
					'subject'	=> 'subject',
					'readperm'	=> 'readPermission',
					'lastposter'	=> 'lastPoster',
					'views'	=> 'viewNum',
					'replies'	=> 'replyNum',
					'displayorder'	=> 'stickLevel',
					'highlight'	=> 'isHighlight',
					'digest'	=> 'digestLevel',
					'rate'	=> 'isRated',
					'attachment'	=> 'isAttached',
					'moderated'	=> 'isModerated',
					'closed'	=> 'isClosed',
					'supe_pushstatus'	=> 'supeSitePushStatus',
					'recommends'	=> 'recommendTimes',
					'recommend_add'	=> 'recommendSupportTimes',
					'recommend_sub'	=> 'recommendOpposeTimes',
					'heats'		=> 'heats',
					'pid'		=> 'pId',
					'isgroup' => 'isGroup',
					'posttableid' => 'postTableId',
					'favtimes'  => 'favoriteTimes',
					'sharetimes'=> 'shareTimes'
					);
		$map2 = array(
					'dateline'	=> 'createdTime',
					'lastpost'	=> 'lastPostedTime',
					);
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if ($k == 'special') {
					switch($v) {
						case 1:
							$v = 'poll';
							break;
						case 2:
							$v = 'trade';
							break;
						case 3:
							$v = 'reward';
							break;
						case 4:
							$v = 'activity';
							break;
						case 5:
							$v = 'debate';
							break;
						case 127:
							$v = 'plugin';
							break;
						default:
							$v = 'normal';
					}
				}

				if ($k == 'displayorder') {
					switch($v) {
						case 1:
							$v = 'board';
							break;
						case 2:
							$v = 'group';
							break;
						case 3:
							$v = 'global';
							break;
						case 0:
						default:
							$v = 'none';
					}
				}

				if (in_array($k, array('highlight', 'rate', 'attachment', 'moderated', 'closed', 'isgroup'))) {
					$v = $v ? true : false;
				}
				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = dgmdate($v, 'Y-m-d H:i:s', 8);
			}
		}
		return $result;
	}

	function preGetThreads($table, $tIds) {
		$tIds = implode($tIds, ', ');
		$result = array();
		if($tIds) {
			$sql = sprintf("SELECT * FROM %s WHERE tid IN (%s) AND displayorder >= 0", $table, $tIds);

			$query = DB::query($sql);
			while($thread = DB::fetch($query)) {
				$thread['pid'] = $threadPosts[$thread['tid']]['pId'];
				$result[$thread['tid']] = self::convertThread($thread);
			}
		}
		return $result;
	}

	function getThreadPosts($tIds) {
		$result = array();
		foreach($tIds as $postTableId => $_tIds) {
			$suffix = $postTableId ? "_$postTableId" : '';
			$sql = sprintf("SELECT * FROM %s
						   WHERE tid IN (%s) AND first = 1 AND invisible = '0'", DB::table('forum_post' . $suffix), implode($_tIds, ', ')
						  );
			$query = DB::query($sql);
			while($post = DB::fetch($query)) {
				$result[$post['tid']] = self::convertPost($post);
			}
		}
		return $result;
	}

	function getThreads($tIds, $isReturnPostId = true) {
		global $_G;
		$tables = array();
		$infos = unserialize($_G['setting']['threadtable_info']);
		if ($infos) {
			foreach($infos as $id => $row) {
				$suffix = $id ? "_$id" : '';
				$tables[] = 'forum_thread' . $suffix;
			}
		} else {
			$tables = array('forum_thread');
		}

		$tableNum = count($tables);
		$res = $data = $_tableInfo = array();
		for($i = 0; $i < $tableNum; $i++) {
			$_threads = self::preGetThreads(DB::table($tables[$i]), $tIds);
			if ($_threads) {
				if (!$data) {
					$data = $_threads;
				} else {
					$data = $data +  $_threads;
				}
				if (count($data) == count($tIds)) {
					break;
				}
			}
		}

		if ($isReturnPostId) {
			$threadIds = array();
			foreach($data as $tId => $thread) {
				$postTableId = $thread['postTableId'];
				$threadIds[$postTableId][] = $tId;
			}

			$threadPosts = self::getThreadPosts($threadIds);
			foreach($data as $tId => $thread) {
				$data[$tId]['pId'] = $threadPosts[$tId]['pId'];
			}
		}
		return $data;
	}

	function convertPost($row) {
		$result = array();
		$map = array('pid' => 'pId',
						'tid'	=> 'tId',
						'fid'	=> 'fId',
						'authorid'	=> 'authorId',
						'author'	=> 'authorName',
						'useip'	=> 'authorIp',
						'anonymous'	=> 'isAnonymous',
						'subject'	=> 'subject',
						'message'	=> 'content',
						'htmlon'	=> 'isHtml',
						'attachment'	=> 'isAttached',
						'rate'	=> 'rate',
						'ratetimes'	=> 'rateTimes',
						'dateline'	=> 'createdTime',
						'first'		=> 'isThread',
					   );
		$map2 = array(
					  'bbcodeoff'	=> 'isBbcode',
					  'smileyoff'	=> 'isSmiley',
					  'parseurloff'	=> 'isParseUrl',
					 );
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if ($k == 'dateline') {
					$result[$map[$k]] = dgmdate($v, 'Y-m-d H:i:s', 8);
					continue;
				}

				if (in_array($k, array('htmlon', 'attachment', 'first', 'anonymous'))) {
					$v = $v ? true : false;
				}

				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = $v ? false : true;
			}
		}
		$result['isWarned'] = $result['isBanned'] = false;
		if ($row['status'] & 1) {
			$result['isBanned'] = true;
		}
		if ($row['status'] & 2) {
			$result['isWarned'] = true;
		}
		return $result;
	}

	function convertNav($row) {
		$map = array(	'id' => 'id',
						'name' => 'name',
						'title' => 'title',
						'url' => 'url',
						'type' => 'provider',
						'navtype' => 'navType',
						'available' => 'available',
						'displayorder' => 'displayOrder',
						'target' => 'linkTarget',
						'highlight' => 'highlight',
						'level' => 'userGroupLevel',
						'subtype' => 'subLayout',
						'subcols' => 'subColNum',
						'subname' => 'subName',
						'suburl' => 'subUrl',
					   );

		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if (in_array($k, array('available'))) {
					$v = $v ? true : false;
				}
				if ($k == 'subtype') {
					if ($v == 1) {
						$v = 'parallel';
					} else {
						$v = 'menu';
					}
				}
				if ($k == 'type') {
					switch($v) {
						case '1':
							$v = 'user';
							break;
						case '0':
						default:
							$v = 'system';
							break;
					}
				}
				if ($k == 'navtype') {
					switch($v) {
						case 1:
							$v = 'footer';
							break;
						case 2:
							$v = 'space';
							break;
						case 3:
							$v = 'my';
							break;
						case 0:
							$v = 'header';
							break;
					}
				}
				$result[$map[$k]] = $v;
			}
		}
		return $result;
	}

}

?>