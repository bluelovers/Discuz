<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Notification.php 29758 2012-04-27 01:35:41Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Notification extends Cloud_Service_Client_Restful {

	protected static $_instance;

	public static function getInstance($debug = false) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($debug);
		}

		return self::$_instance;
	}

	public function __construct($debug = false) {

		return parent::__construct($debug);
	}
	public function add($siteUid, $pkId, $type, $authorId, $author, $fromId, $fromIdType, $note, $fromNum, $dateline) {

		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'pkId' => $pkId,
				'type' => $type,
				'authorId' => $authorId,
				'author' => $author,
				'fromId' => $fromId,
				'fromIdType' => $fromIdType,
				'fromNum' => $fromNum,
				'content' => $note,
				'dateline' => $dateline,
				'deviceToken' => $this->getUserDeviceToken($siteUid)
			);
		return $this->_callMethod('connect.discuz.notification.add', $_params);
	}

	public function update($siteUid, $pkId, $fromNum, $dateline) {
		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'pkId' => $pkId,
				'fromNum' => $fromNum,
				'dateline' => $dateline
			);
		return $this->_callMethod('connect.discuz.notification.update', $_params);
	}

	public function setNoticeFlag($siteUid, $dateline) {

		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'dateline' => $dateline
			);
		return $this->_callMethod('connect.discuz.notification.read', $_params);
	}

	public function addSiteMasterUserNotify($siteUids, $subject, $content, $authorId, $author, $fromType, $dateline) {
		$toUids = array();
		if($siteUids) {
			foreach(C::t('#qqconnect#common_member_connect')->fetch_all((array)$siteUids) as $user) {
				$toUids[$user['conopenid']] = $user['uid'];
			}
			$_params = array(
					'openidData' => $toUids,
					'subject' => $subject,
					'content' => $content,
					'authorId' => $authorId,
					'author' => $author,
					'fromType' => $fromType == 1 ? 1 : 2,
					'dateline' => $dateline,
					'deviceToken' => $this->getUserDeviceToken($siteUids)
			);
			return parent::_callMethod('connect.discuz.notification.addSiteMasterUserNotify', $_params);
		}
		return false;
	}

	protected function _callMethod($method, $args) {
		try {
			return parent::_callMethod($method, $args);
		} catch (Exception $e) {

		}
	}
}