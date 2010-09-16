<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: pm_client.php 820 2008-12-03 09:18:59Z zhaoxiongfei $
*/

!defined('IN_UC') && exit('Access Denied');

class pm_clientcontrol extends base {

	function __construct() {
		$this->pm_clientcontrol();
	}

	function pm_clientcontrol() {
		parent::__construct();
		if(!$this->settings['pmcenter']) {
			exit('PMCenter closed');
		}
		$this->load('user');
		$this->load('pm', NULL, UC_SERVER_RELEASE);
	}

	function _auth() {
		$input = getgpc('input');
		if(!$this->user['uid'] || isset($input)) {
			$this->init_input();
			header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
			if($this->input['uid']) {
				$this->setcookie('uc_auth', @$this->authcode($this->input['uid']."||".md5($_SERVER['HTTP_USER_AGENT']), 'ENCODE', UC_KEY), 1800);
				@$this->user['uid'] = $this->input['uid'];
			} else {
				$this->setcookie('uc_auth', '');
				$this->message('please_login', '', 1);
			}
		}
	}

	function onls() {
		$folder = getgpc('folder');
		$page = getgpc('page');
		$filter = getgpc('filter');
		$a = getgpc('a');
		$this->_auth();
		$uid = $this->user['uid'];
		$folder = !empty($folder) ? $folder : 'inbox';
		$_ENV['pm']->lang = &$this->lang;
		$pmnum = $_ENV['pm']->get_pm_num($uid, $folder, $filter, $a);
		$pmnumindex = 0;
		switch($_GET['filter']) {
			case 'systempm' : $pmnumindex = 3;break;
			case 'announcepm' : $pmnumindex = 4;break;
		}
		$unreadpmnum = $pmnum[1] + $pmnum[2];
		$this->view->assign('user', $this->user);
		$this->view->assign('folder', $folder);
		$this->view->assign('filter', $filter);
		$this->view->assign('pmnum', $pmnum);
		$this->view->assign('unreadpmnum', $unreadpmnum);
		$this->view->assign('pmnumindex', $pmnumindex);
		if($folder == 'blackls') {
			$blackls = htmlspecialchars($_ENV['pm']->get_blackls($uid));
			$this->view->assign('blackls', $blackls);
			$this->view->display('pm_blackls');
		} else {
			$pmlist = $_ENV['pm']->get_pm_list($uid, $pmnum[$pmnumindex], $folder, $filter, $page);
			$extra = 'extra='.rawurlencode('page='.$page);
			$multipage = $this->page($pmnum[$pmnumindex], 10, $page, 'index.php?m=pm_client&a=ls&folder='.$folder.'&filter='.$filter);
			$this->view->assign('extra', $extra);
			$this->view->assign('pmlist', $pmlist);
			$this->view->assign('multipage', $multipage);
			$this->view->display('pm_ls');
		}
	}

	function onblackls() {
		$blackls = getgpc('blackls', 'P');
		$this->_auth();
		$uid = $this->user['uid'];
		if($this->submitcheck()) {
			$_ENV['pm']->set_blackls($uid, $blackls);
		}
		$this->message('blackls_updated', 'index.php?m=pm_client&a=ls&folder=blackls', 1);
	}

	function onsend() {
		$folder = getgpc('folder');;
		$filter = getgpc('filter');
		$a = getgpc('a');
		$do = getgpc('do');
		$this->_auth();
		$uid = $this->user['uid'];

		if($this->settings['pmsendregdays']) {
			$fromuser = $_ENV['user']->get_user_by_uid($uid);
			if($fromuser['regdate'] > $this->time - $this->settings['pmsendregdays'] * 86400) {
				$this->message('pm_send_regdays_error', 'BACK', 1, array('$pmsendregdays' => $this->settings['pmsendregdays']));
			}
		}

		$this->load('friend');
		$totalnum = $_ENV['friend']->get_totalnum_by_uid($this->user['uid'], 3);
		$friends = $totalnum ? $_ENV['friend']->get_list($this->user['uid'], 1, $totalnum, $totalnum, 3) : array();
		if(!$this->submitcheck()) {
			$touser = '';
			$pmid = @is_numeric($_GET['pmid']) ? $_GET['pmid'] : 0;
			if($pmid) {
				$tmp = $_ENV['pm']->get_pm_by_pmid($uid, $pmid);$tmp = $tmp[0];
			} else {
				$tmp = array();
			}
			$pmnum = $_ENV['pm']->get_pm_num($uid, $folder, $filter, $a);
			if(!empty($pmid)) {
				if($do == 'forward') {
					$tmp['subject'] = 'Fw: '.$tmp['subject'];
					$user = $_ENV['user']->get_user_by_uid($tmp['msgtoid']);
					$tmp['message'] = ($tmp['msgfromid'] ? $this->lang['pm_from'].': '.$tmp['msgfrom'] : $this->lang['pm_system'])."\n".
						$this->lang['pm_to'].': '.htmlspecialchars($user['username'])."\n".
						$this->lang['pm_date'].': '.$this->date($tmp['dateline'])."\n\n".
						'[quote]'.trim(preg_replace("/(\[quote])(.*)(\[\/quote])/siU", '', $tmp['message'])).'[/quote]'."\n";
				}
 				require_once UC_ROOT.'lib/uccode.class.php';
				$this->uccode = new uccode();
				$tmp['messagepreview'] = $this->uccode->complie($tmp['message']);
			} else {
				!empty($_GET['msgto']) && $touser = htmlspecialchars($_GET['msgto']);
				!empty($_GET['subject']) && $tmp['subject'] = $_GET['subject'];
				!empty($_GET['message']) && $tmp['message'] = $_GET['message'];
			}
			$related = $a == 'reply' ? $tmp['related'] : 0;
			$folder = 'send';
			$extra = 'extra='.rawurlencode($_GET['extra']);
			$type = !empty($_GET['type']) ? $_GET['type'] : '';
			$unreadpmnum = $pmnum[1] + $pmnum[2];
			if($this->settings['sendpmseccode']) {
				$authkey = md5(UC_KEY.$_SERVER['HTTP_USER_AGENT'].$this->onlineip);
				$rand = rand(100000, 999999);
				$seccodeinit = rawurlencode($this->authcode($rand, 'ENCODE', $authkey, 720));
				$this->view->assign('seccodeinit', $seccodeinit);
			}

			$this->view->assign('sendpmseccode', $this->settings['sendpmseccode']);
			$this->view->assign('touser', $touser);
			$this->view->assign('related', $related);
			$this->view->assign('user', $this->user);
			$this->view->assign('pmnum', $pmnum);
			$this->view->assign('unreadpmnum', $unreadpmnum);
			$this->view->assign('friends', $friends);
			$this->view->assign('extra', $extra);
			$extra = getgpc('extra');
			$this->view->assign('extraret', $extra);
			$this->view->assign('pmid', $pmid);
			$this->view->assign('a', $a);
			$this->view->assign('do', $do);
			$this->view->assign('folder', $folder);
			$tmp['subject'] = htmlspecialchars($tmp['subject']);
			$tmp['message'] = htmlspecialchars($tmp['message']);
			$this->view->assign('subject', $tmp['subject']);
			$this->view->assign('message', $tmp['message']);
			$this->view->assign('messagepreview', $tmp['messagepreview']);
			$this->view->assign('type', $type);
			$this->view->display('pm_send');

		} else {
			if($this->settings['sendpmseccode']) {
				$authkey = md5(UC_KEY.$_SERVER['HTTP_USER_AGENT'].$this->onlineip);
				$seccodehidden = urldecode(getgpc('seccodehidden', 'P'));
				$seccode = strtoupper(getgpc('seccode', 'P'));
				$seccodehidden = $this->authcode($seccodehidden, 'DECODE', $authkey);
				require UC_ROOT.'./lib/seccode.class.php';
				seccode::seccodeconvert($seccodehidden);
				if(empty($seccodehidden) || $seccodehidden != $seccode) {
					$this->message('pm_send_seccode_error', 'BACK', 1);
				}
			}
			$user = $_ENV['user']->get_user_by_uid($this->user['uid']);
			$this->user['username'] = daddslashes($user['username'], 1);
			if($_POST['saveoutbox']) {
				$pmid = $_ENV['pm']->sendpm($_POST['subject'], $_POST['message'], $this->user, $uid, $_GET['pmid'], 1, $_POST['related']);
				$this->message('pm_save_succeed', 'index.php?m=pm_client&a=send&do=savebox&folder=savebox&pmid='.$_GET['pmid'].'&extra='.rawurlencode($_GET['extra']), 1);
			} else {
				$msgto = array();
				$tmp = $_ENV['user']->get_user_by_username($_POST['msgto']);
				$tmp && $msgto[] = $tmp['uid'];

				if(isset($_POST['friend'])) {
					$frienduids = array();
					foreach($friends as $friend) {
						$frienduids[] = $friend['friendid'];
					}
					foreach($_POST['friend'] as $friendid) {
						if(in_array($friendid, $frienduids)) {
							$msgto[] = $friendid;
						}
					}
				}
				if(!$msgto) {
					$this->message('receiver_no_exists', 'BACK', 1);
				}

				$pmlimit1day = $this->settings['pmlimit1day'] && $_ENV['pm']->count_pm_by_fromuid($this->user['uid'], 86400) > $this->settings['pmlimit1day'];
				if($pmlimit1day || ($this->settings['pmfloodctrl'] && $_ENV['pm']->count_pm_by_fromuid($this->user['uid'], $this->settings['pmfloodctrl']))) {
					if(!$_ENV['friend']->is_friend($this->user['uid'], $msgto, 3)) {
						if(!$_ENV['pm']->is_reply_pm($this->user['uid'], $msgto)) {
							if($pmlimit1day) {
								$this->message('pm_send_limit1day_error', 'BACK', 1);
							} else {
								$this->message('pm_send_floodctrl_error', 'BACK', 1);
							}
						}
					}
				}

				$msgto = array_unique($msgto);
				$blackls = $_ENV['pm']->get_blackls($uid, $msgto);
				$sent = 0;
				foreach($msgto as $uid) {
					if(!in_array('{ALL}', $blackls[$uid])) {
						$blackls[$uid] = $_ENV['user']->name2id($blackls[$uid]);
						if(isset($blackls[$uid]) && !in_array($this->user['uid'], $blackls[$uid]) &&
							$_ENV['pm']->sendpm($_POST['subject'], $_POST['message'], $this->user, $uid, 0, 0, $_POST['related'])) {;
							$sent++;
						}
					}
				}
				if($sent) {
					$this->message('pm_send_succeed', 'index.php?m=pm_client&a=ls&folder='.($_POST['folder'] ? $_POST['folder'] : 'inbox'), 1, array('$sent' => $sent));
				} else {
					$this->message('pm_send_ignore', 'BACK', 1);
				}
			}
		}
	}

	function ondelete() {
		$this->_auth();
		$uid = $this->user['uid'];
		$touids = !empty($_GET['delete']) ? array($_ENV['pm']->pmintval($_GET['delete'])) :
			(!empty($_GET['pmid']) ? array($_ENV['pm']->pmintval($_GET['pmid'])) : @$_POST['delete']);
		if($_ENV['pm']->deleteuidpm($uid, $touids, $_GET['folder'], $_GET['filter'])) {
			$this->message('pm_delete_succeed', 'index.php?m=pm_client&a=ls&folder='.$_GET['folder'].'&filter='.$_GET['filter'].'&'.$_GET['extra'], 1);
		} else {
			$this->message('pm_delete_invalid', 'index.php?m=pm_client&a=ls&folder='.$_GET['folder'].'&filter='.$_GET['filter'].'&'.$_GET['extra'], 1);
		}
	}

 	function onview() {
 		$folder = getgpc('folder');
		$filter = getgpc('filter');
		$touid = getgpc('touid');
		$daterange = getgpc('daterange');
		$a = getgpc('a');
 		$this->_auth();
 		$uid = $this->user['uid'];
 		$pmid = $_ENV['pm']->pmintval($_GET['pmid']);
 		$related = $_ENV['pm']->pmintval($_GET['related']);
 		$pmnum = $_ENV['pm']->get_pm_num($uid, $folder, $filter, $a);
 		if(empty($pmid)) {
	 		$daterange = empty($daterange) ? 1 : $daterange;
	 		$today = $this->time - ($this->time + $this->settings['timeoffset']) % 86400;
	 		if($daterange == 1) {
	 			$starttime = $today;
	 		} elseif($daterange == 2) {
	 			$starttime = $today - 86400;
	 		} elseif($daterange == 3) {
	 			$starttime = $today - 172800;
	 		} elseif($daterange == 4) {
	 			$starttime = $today - 604800;
	 		} elseif($daterange == 5) {
	 			$starttime = 0;
	 		}
	 		$endtime = $this->time;
	 		$pms = $_ENV['pm']->get_pm_by_touid($uid, $touid, $starttime, $endtime);
	 	} else {
	 		$pms = $_ENV['pm']->get_pm_by_pmid($uid, $pmid);
	 	}
 		$unreadpmnum = $pmnum[1] + $pmnum[2];
 	 	require_once UC_ROOT.'lib/uccode.class.php';
		$this->uccode = new uccode();
		$users = array();$status = FALSE;
		foreach($pms as $key => $pm) {
			if($pm['delstatus']) {
				unset($pms[$key]);
				continue;
			}
			if(!$users[$pms[$key]['msgtoid']]) {
				$pms[$key]['msgto'] = $_ENV['user']->get_user_by_uid($pms[$key]['msgtoid']);
	 			$users[$pms[$key]['msgtoid']] = $pms[$key]['msgto']['username'];
			}
			$pms[$key]['msgto'] = $users[$pms[$key]['msgtoid']];
			$pms[$key]['message'] = $this->uccode->complie($pms[$key]['message']);
			$pms[$key]['dateline'] = $this->date($pms[$key]['dateline']);
			$pms[$key]['subject'] = $pms[$key]['subject'] != '' ? htmlspecialchars($pms[$key]['subject']) : ($pm['pmid'] == $pm['relate'] ? $this->lang['pm_notitle'] : '');
			!$status && $status = $pm['msgtoid'] && $pm['new'];
		}
		$status && $_ENV['pm']->set_pm_status($uid, $touid);
		$msgto = $pms[0]['msgtoid'] == $this->user['uid'] ? $pms[0]['msgfrom'] : $pms[0]['msgto'];
		if(!$msgto) {
			if($touid > 0) {
				$msgto = $_ENV['user']->get_user_by_uid($touid);
				$msgto = $msgto['username'];
			} else {
				$msgto = '';
			}
		}

		if($this->settings['sendpmseccode']) {
			$authkey = md5(UC_KEY.$_SERVER['HTTP_USER_AGENT'].$this->onlineip);
			$rand = rand(100000, 999999);
			$seccodeinit = rawurlencode($this->authcode($rand, 'ENCODE', $authkey, 720));
			$this->view->assign('seccodeinit', $seccodeinit);
		}

		$this->view->assign('sendpmseccode', $this->settings['sendpmseccode']);

		$extra = 'extra='.rawurlencode(getgpc('extra'));
		$scroll = getgpc('scroll');
		$from = getgpc('from');
		$extra = getgpc('extra');
		$this->view->assign('scroll', $scroll);
		$this->view->assign('user', $this->user);
		$this->view->assign('from', $from);
		$this->view->assign('msgto', $msgto);
		$this->view->assign('pmnum', $pmnum);
		$this->view->assign('unreadpmnum', $unreadpmnum);
		$this->view->assign('msgto', $msgto);
		$this->view->assign('related', $related);
		$this->view->assign('daterange', $daterange);
		$this->view->assign('pmid', $pmid);
		$this->view->assign('touid', $touid);
		$this->view->assign('folder', $folder);
		$this->view->assign('filter', $filter);
		$this->view->assign('extra', $extra);
		$this->view->assign('extraret', $extra);
 		$this->view->assign('pms', $pms);
 		$this->view->display('pm_view');
 	}

}

?>