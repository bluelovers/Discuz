<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$r_group = (array)unserialize($_G['cache']['plugin']['reminder_dzx']['group']);
$r_uids = explode(',', $_G['cache']['plugin']['reminder_dzx']['uids']);
$r_perm = in_array('', $r_group) ? TRUE : (in_array($_G['groupid'], $r_group) ? TRUE : (in_array($_G['uid'], $r_uids) ? TRUE : FALSE));

if(!$r_perm || !$_G['uid']) {
	showmessage('reminder_dzx:permissions_invalid');
}

$cookie_reminder = explode('D', getcookie('reminder'));
$cookie_reminder['0'] == $_G['uid'] && $_G['gp_type'] = $cookie_reminder['2'];
$rtype = array_combine(array('newprompt', 'newpm', 'newthread'), explode('_', $_G['gp_type']));

if($_G['gp_action'] == 'checknew') {

	$list = array();
	$time = intval($_G['gp_time']);

	foreach($rtype as $key => $value) {
		if(!empty($list)) {
			continue;
		} else {
			switch($key) {
				case 'newprompt':
					if($rtype['newprompt'] && $_G['member']['newprompt']) {
						$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' AND `new`='1'"), 0);
						if($count) {
							$query = DB::query("SELECT * FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' AND `new`='1' ORDER BY new DESC, dateline DESC LIMIT 1");
							while($value = DB::fetch($query)) {
								$list['newprompt'][$value['id']] = $value;
							}
						}
					}
				break;
				case 'newpm':
					if(!$_G['member']['newpm'] && $_G['cache']['plugin']['reminder_dzx']['checkpm']) {
						if($_G['uid'] && !getstatus($_G['member']['newpm'], 1)) {
							loaducenter();
							$ucnewpm = intval(uc_pm_checknew($_G['uid']));
							$newpm = setstatus(1, $ucnewpm ? 1 : 0, $_G['member']['newpm']);
							if($_G['member']['newpm'] != $newpm) {
								DB::query("UPDATE ".DB::table('common_member')." SET newpm='$newpm' WHERE uid='$_G[uid]'");
							}
						}
					}
					if($rtype['newpm'] && $_G['member']['newpm']) {
						loaducenter();
						$newpmarr = uc_pm_checknew($_G['uid'], 1);
						$newpm = $newpmarr['newpm'];
						if($newpm) {
							$result = uc_pm_list($_G['uid'], 1, 10, 'inbox', 'newpm', 200);
							if(is_array($result['data'])) {
								foreach($result['data'] as $value) {
									$list['newpm'][$value['plid']] = $value;
								}
							}
						}
					}
				break;
				case 'newthread':
					if($rtype['newthread'] && $_G['gp_fid']) {
						if($lastpost_str = DB::result_first("SELECT lastpost FROM ".DB::table('forum_forum')." WHERE fid = '{$_G['gp_fid']}' LIMIT 1")) {
							$lastpost = explode("\t", $lastpost_str);
							unset($lastpost_str);
						}
						$last = array();
						if($lastpost['2'] > $time && $lastpost['3'] != $_G['username']) {
							$last['tid'] = $lastpost['0'];
							$last['author'] = $lastpost['3'];
							$last['pid'] = $lastpost['0'];
							$last['dateline'] = $lastpost['2'];
							$last['subject'] = $lastpost['1'];
							$list['newthread'][$lastpost['0']] = $last;
						}
					}
				break;
			}
		}
	}

	$read = array();
	$return = 'dataempty';
	if(!empty($list)) {
		$returnlist = array();
		foreach($list as $key => $remind) {
			$read[$key] = array_keys($remind);
			$returnlist[$key] = $remind;
			continue;
		}
		foreach($rtype as $key => $val) {
			if($val == 1 && $read[$key]) {
				clearnew(array($key => $read[$key]));
			}
		}

		$returnlist['lasttime'] = TIMESTAMP;
		$return = json_encode(strtolower($_G['config']['db']['1']['dbcharset']) != 'utf8' ? dziconv($returnlist, $_G['config']['db']['1']['dbcharset'], 'UTF-8') : $returnlist);
	}

	include template('common/header_ajax');
	echo $return;
	include template('common/footer_ajax');

} elseif($_G['gp_ac'] == 'plugin' && $_G['gp_id'] == 'reminder_dzx:reminder_dzx') {
	$reminder = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin_reminder')." WHERE uid='{$_G['uid']}' LIMIT 1");
	if(submitcheck('settingsubmit')) {
		$readtype = array();
		$types = array('newprompt', 'newpm', 'newthread');
		foreach($types as $type) {
			$readtype[$type] = intval($_G['gp_readtype'][$type]);
		}

		$_G['gp_remind'] = !$readtype['newprompt'] && !$readtype['newpm'] && !$readtype['newthread'] ? 0 : intval($_G['gp_remind']);
		$setarr = array(
			'remind' => $_G['gp_remind'],
			'readtype' => addslashes(implode('_', $readtype)),
		);
		if($reminder) {
			DB::update('common_plugin_reminder', $setarr, array('uid' => $_G['uid']));
		} else {
			$setarr['uid'] = $_G['uid'];
			DB::insert('common_plugin_reminder', $setarr);
		}

		array_unshift($setarr, $_G['uid']);
		dsetcookie('reminder', ($_G['cookie']['reminder'] = implode('D', dstripslashes($setarr))), 31536000);
		showsuccess(lang('plugin/reminder_dzx', 'success'), dreferer());
	}

	if($readtype) {
		$remind[$_G['gp_remind']] = ' checked';
		foreach($readtype as $key => $value) {
			$readtype[$key] = array($value => ' selected');
		}
	} elseif($reminder) {
		$readtype = array();
		$reminder['readtype'] = array_combine(array('newprompt', 'newpm', 'newthread'), explode('_', $reminder['readtype']));
		if($reminder['readtype']) {
			foreach($reminder['readtype'] as $key => $value) {
				$readtype[$key] = array($value => ' selected');
			}
		}
		$remind[$reminder['remind']] = ' checked';
	}

	if($_G['gp_infloat']) {
		include template('common/header_ajax');
		include template('reminder_dzx:reminder_dzx');
		include template('common/footer_ajax');
		exit;
	}
} elseif($_G['gp_action'] == 'clearnew') {
	if(isset($_G['gp_new'])) {
		$new = array();
		$gp_new = explode('_', $_G['gp_new']);
		$new[$gp_new['0']] = (array)$gp_new['1'];
		clearnew($new);
	}
}

function dziconv($str, $in_charset = 'GBK', $out_charset = 'UTF-8') {
	$c = array();
	if(is_array($str)) {
		foreach($str as $key => $val) {
			$c[$key] = dziconv($val, $in_charset, $out_charset);
		}
		return $c;
	} else {
		return iconv($in_charset, $out_charset, $str);
	}
}


/*
** 修改已读状态的id
** $new：数组 键名为类型 newprompt、newpm、newthread 键值为id数组
*/
function clearnew($new = array()) {
	global $_G;
	foreach($new as $key => $value) {
		switch($key) {
			case 'newprompt':
				$count = count($new['newprompt']);
				DB::query("UPDATE ".DB::table('home_notification')." SET new='0' WHERE id IN (".dimplode($value).") AND new='1'");
				DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt-'$count' WHERE uid='$_G[uid]'");
				break;
			case 'newpm':
				DB::update('common_member', array('newpm' => 0), array('uid' => $_G['uid']));
				loaducenter();
				uc_pm_ignore($_G['uid']);
				break;
			case 'newthread':
				break;
		}
	}
}

function showsuccess($message = '', $location = '') {
	echo '<script type="text/javascript">';
	echo "parent.show_success('$message', '$location');";
	echo '</script>';
	exit();
}
?>