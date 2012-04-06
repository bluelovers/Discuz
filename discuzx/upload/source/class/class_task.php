<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_task.php 23482 2011-07-19 09:47:58Z zhengqingpeng $
 */

class task {

	var $task;
	var $taskvars;
	var $message;
	var $multipage;
	var $listdata;

	function task() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new task();
		}
		return $object;
	}

	function tasklist($item) {
		global $_G;

		$multipage = '';
		$page = max(1, intval($_G['gp_page']));
		$start_limit = ($page - 1) * $_G['tpp'];
		$tasklist = $endtaskids = $magicids = $medalids = $groupids = array();

		switch($item) {
			case 'doing':
				$sql = "mt.status='0'";
				break;
			case 'done':
				$sql = "mt.status='1'";
				break;
			case 'failed':
				$sql = "mt.status='-1'";
				break;
			case 'canapply':
			case 'new':
			default:
				$todaytimestamp = TIMESTAMP - (TIMESTAMP + $_G['setting']['timeoffset'] * 3600) % 86400 + $_G['setting']['timeoffset'] * 3600;
				$sql = "'$_G[timestamp]' > starttime AND (mt.taskid IS NULL OR (ABS(mt.status)='1' AND t.period>0))";
				break;
		}

		$updated = FALSE;
		$num = 0;
		$query = DB::query("SELECT t.*, mt.csc, mt.dateline FROM ".DB::table('common_task')." t
			LEFT JOIN ".DB::table('common_mytask')." mt ON mt.taskid=t.taskid AND mt.uid='$_G[uid]'
			WHERE $sql AND t.available='2' ORDER BY t.displayorder, t.taskid DESC");
		while($task = DB::fetch($query)) {
			if($item == 'new' || $item == 'canapply') {
				list($task['allowapply'], $task['t']) = $this->checknextperiod($task);
				if($task['allowapply'] < 0) {
					continue;
				}
				$task['noperm'] = $task['applyperm'] && $task['applyperm'] != 'all' && !(($task['applyperm'] == 'member'&& $_G['adminid'] == '0') || ($task['applyperm'] == 'admin' && $_G['adminid'] > '0') || forumperm($task['applyperm']));
				$task['appliesfull'] = $task['tasklimits'] && $task['achievers'] >= $task['tasklimits'];
				if($item == 'canapply' && ($task['noperm'] || $task['appliesfull'])) {
					continue;
				}
			}
			$num++;
			if($task['reward'] == 'magic') {
				$magicids[] = $task['prize'];
			} elseif($task['reward'] == 'medal') {
				$medalids[] = $task['prize'];
			} elseif($task['reward'] == 'invite') {
				$invitenum = $task['prize'];
			} elseif($task['reward'] == 'group') {
				$groupids[] = $task['prize'];
			}
			if($task['available'] == '2' && ($task['starttime'] > TIMESTAMP || ($task['endtime'] && $task['endtime'] <= TIMESTAMP))) {
				$endtaskids[] = $task['taskid'];
			}
			$csc = explode("\t", $task['csc']);
			$task['csc'] = floatval($csc[0]);
			$task['lastupdate'] = intval($csc[1]);
			if(!$updated && $item == 'doing' && $task['csc'] < 100) {
				$updated = TRUE;
				require_once libfile('task/'.$task['scriptname'], 'class');
				$taskclassname = 'task_'.$task['scriptname'];
				$taskclass = new $taskclassname;
				$task['applytime'] = $task['dateline'];
				if(method_exists($taskclass, 'csc')) {
					$result = $taskclass->csc($task);
				} else {
					showmessage('task_not_found', '', array('taskclassname' => $taskclassname));
				}
				if($result === TRUE) {
					$task['csc'] = '100';
					DB::query("UPDATE ".DB::table('common_mytask')." SET csc='100' WHERE uid='$_G[uid]' AND taskid='$task[taskid]'");
				} elseif($result === FALSE) {
					DB::query("UPDATE ".DB::table('common_mytask')." SET status='-1' WHERE uid='$_G[uid]' AND taskid='$task[taskid]'", 'UNBUFFERED');
				} else {
					$task['csc'] = floatval($result['csc']);
					DB::query("UPDATE ".DB::table('common_mytask')." SET csc='$task[csc]\t$_G[timestamp]' WHERE uid='$_G[uid]' AND taskid='$task[taskid]'", 'UNBUFFERED');
				}
			}
			if(in_array($item, array('done', 'failed')) && $task['period']) {
				list($task['allowapply'], $task['t']) = $this->checknextperiod($task);
				$task['allowapply'] = $task['allowapply'] > 0 ? 1 : 0;
			}
			$task['icon'] = $task['icon'] ? $task['icon'] : 'task.gif';
			$task['icon'] = strtolower(substr($task['icon'], 0, 7)) == 'http://' ? $task['icon'] : "static/image/task/$task[icon]";
			$task['dateline'] = $task['dateline'] ? dgmdate($task['dateline'], 'u') : '';
			$tasklist[] = $task;
		}

		if($magicids) {
			$query = DB::query("SELECT magicid, name FROM ".DB::table('common_magic')." WHERE magicid IN (".dimplode($magicids).")");
			while($magic = DB::fetch($query)) {
				$this->listdata[$magic['magicid']] = $magic['name'];
			}
		}

		if($medalids) {
			$query = DB::query("SELECT medalid, name FROM ".DB::table('forum_medal')." WHERE medalid IN (".dimplode($medalids).")");
			while($medal = DB::fetch($query)) {
				$this->listdata[$medal['medalid']] = $medal['name'];
			}
		}

		if($groupids) {
			$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid IN (".dimplode($groupids).")");
			while($group = DB::fetch($query)) {
				$this->listdata[$group['groupid']] = $group['grouptitle'];
			}
		}

		if($invitenum) {
			$this->listdata[$invitenum] = $_G['lang']['invite_code'];
		}

		if($endtaskids) {
		}

		return $tasklist;
	}
	function view($id) {
		global $_G;

		$this->task = DB::fetch_first("SELECT t.*, mt.status, mt.csc, mt.dateline, mt.dateline AS applytime FROM ".DB::table('common_task')." t LEFT JOIN ".DB::table('common_mytask')." mt ON mt.uid='$_G[uid]' AND mt.taskid=t.taskid WHERE t.taskid='$id' AND t.available='2'");
		if(!$this->task) {
			showmessage('task_nonexistence');
		}
		switch($this->task['reward']) {
			case 'magic':
				$this->task['rewardtext'] = DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE magicid='".$this->task['prize']."'");
				break;
			case 'medal':
				$this->task['rewardtext'] = DB::result_first("SELECT name FROM ".DB::table('forum_medal')." WHERE medalid='".$this->task['prize']."'");
				break;
			case 'group':
				$this->task['rewardtext'] = DB::result_first("SELECT grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid='".$this->task['prize']."'");
				break;
		}
		$this->task['icon'] = $this->task['icon'] ? $this->task['icon'] : 'task.gif';
		$this->task['icon'] = strtolower(substr($this->task['icon'], 0, 7)) == 'http://' ? $this->task['icon'] : 'static/image/task/'.$this->task['icon'];
		$this->task['endtime'] = $this->task['endtime'] ? dgmdate($this->task['endtime'], 'u') : '';
		$this->task['description'] = nl2br($this->task['description']);

		$this->taskvars = array();
		$query = DB::query("SELECT sort, name, description, variable, value FROM ".DB::table('common_taskvar')." WHERE taskid='$id'");
		while($taskvar = DB::fetch($query)) {
			if(!$taskvar['variable'] || $taskvar['value']) {
				if(!$taskvar['variable']) {
					$taskvar['value'] = $taskvar['description'];
				}
				if($taskvar['sort'] == 'apply') {
					$this->taskvars['apply'][] = $taskvar;
				} elseif($taskvar['sort'] == 'complete') {
					$this->taskvars['complete'][$taskvar['variable']] = $taskvar;
				} elseif($taskvar['sort'] == 'setting') {
					$this->taskvars['setting'][$taskvar['variable']] = $taskvar;
				}
			}
		}

		$this->task['grouprequired'] = $comma = '';
		$this->task['applyperm'] = $this->task['applyperm'] == 'all' ? '' : $this->task['applyperm'];
		if(!in_array($this->task['applyperm'], array('', 'member', 'admin'))) {
			$query = DB::query("SELECT grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid IN (".str_replace("\t", ',', $this->task['applyperm']).")");
			while($group = DB::fetch($query)) {
				$this->task['grouprequired'] .= $comma.$group[grouptitle];
				$comma = ', ';
			}
		}

		if($this->task['relatedtaskid']) {
			$_G['taskrequired'] = DB::result_first("SELECT name FROM ".DB::table('common_task')." WHERE taskid='".$this->task['relatedtaskid']."'");
		}

		require_once libfile('task/'.$this->task['scriptname'], 'class');
		$taskclassname = 'task_'.$this->task['scriptname'];
		$taskclass = new $taskclassname;
		if($this->task['status'] == '-1') {
			if($this->task['period']) {
				list($allowapply, $this->task['t']) = $this->checknextperiod($this->task);
			} else {
				$allowapply = -4;
			}
		} elseif($this->task['status'] == '0') {
			$allowapply = -1;
			$csc = explode("\t", $this->task['csc']);
			$this->task['csc'] = floatval($csc[0]);
			$this->task['lastupdate'] = intval($csc[1]);
			if($this->task['csc'] < 100) {
				if(method_exists($taskclass, 'csc')) {
					$result = $taskclass->csc($this->task);
				}
				if($result === TRUE) {
					$this->task['csc'] = '100';
					DB::query("UPDATE ".DB::table('common_mytask')." SET csc='100' WHERE uid='$_G[uid]' AND taskid='$id'");
				} elseif($result === FALSE) {
					DB::query("UPDATE ".DB::table('common_mytask')." SET status='-1' WHERE uid='$_G[uid]' AND taskid='$id'", 'UNBUFFERED');
					dheader("Location: home.php?mod=task&do=view&id=$id");
				} else {
					$this->task['csc'] = floatval($result['csc']);
					DB::query("UPDATE ".DB::table('common_mytask')." SET csc='".$this->task['csc']."\t$_G[timestamp]' WHERE uid='$_G[uid]' AND taskid='$id'", 'UNBUFFERED');
				}
			}
		} elseif($this->task['status'] == '1') {
			if($this->task['period']) {
				list($allowapply, $this->task['t']) = $this->checknextperiod($this->task);
			} else {
				$allowapply = -5;
			}
		} else {
			$allowapply = 1;
		}
		if(method_exists($taskclass, 'view')) {
			$this->task['viewmessage'] = $taskclass->view($this->task, $this->taskvars);
		} else {
			$this->task['viewmessage'] = '';
		}

		if($allowapply > 0) {
			if($this->task['applyperm'] && $this->task['applyperm'] != 'all' && !(($this->task['applyperm'] == 'member' && $_G['adminid'] == '0') || ($this->task['applyperm'] == 'admin' && $_G['adminid'] > '0') || preg_match("/(^|\t)(".$_G['groupid'].")(\t|$)/", $this->task['applyperm']))) {
				$allowapply = -2;
			} elseif($this->task['tasklimits'] && $this->task['achievers'] >= $this->task['tasklimits']) {
				$allowapply = -3;
			}
		}

		$this->task['dateline'] = dgmdate($this->task['dateline'], 'u');
		return $allowapply;

	}

	function checknextperiod($task) {
		global $_G;

		$allowapply = false;
		$nextapplytime = '';
		if($task['periodtype'] == 0) {
			$allowapply = TIMESTAMP - $task['dateline'] >= $task['period'] * 3600 ? 2 : -6;
			$nextapplytime = tasktimeformat($task['period'] * 3600 - TIMESTAMP + $task['dateline']);
		} elseif($task['periodtype'] == 1) {
			$todaytimestamp = TIMESTAMP - (TIMESTAMP + $_G['setting']['timeoffset'] * 3600) % 86400;
			$allowapply = $task['dateline'] < $todaytimestamp - ($task['period'] - 1) * 86400 ? 2 : -6;
			$nextapplytime = ($task['dateline'] - ($task['dateline'] + $_G['setting']['timeoffset'] * 3600) % 86400) + $task['period'] * 86400;
			$nextapplytime = dgmdate($nextapplytime);
		} elseif($task['periodtype'] == 2 && $task['period'] > 0 && $task['period'] <= 7) {
			$task['period'] = $task['period'] != 7 ? $task['period'] : 0;
			$todayweek = dgmdate(TIMESTAMP, 'w');
			$weektimestamp = TIMESTAMP - ($todayweek - $task['period']) * 86400;
			$weekstart = $weektimestamp - ($weektimestamp + $_G['setting']['timeoffset'] * 3600) % 86400;
			$weekfirstday = $weekstart - $task['period'] * 86400;
			if($task['dateline'] && ($task['dateline'] > $weekstart || $task['dateline'] > $weekfirstday)) {
				$allowapply = -6;
				if($task['dateline'] > $weekfirstday) {
					$weekstart += 604800;
				}
				$nextapplytime = dgmdate($weekstart);
			} else {
				$allowapply = 2;
			}
		} elseif($task['periodtype'] == 3 && $task['period'] > 0) {
			list($year, $month) = explode('/', dgmdate(TIMESTAMP, 'Y/n'));
			$monthstart = mktime(0, 0, 0, $month, $task['period'], $year);
			$monthfirstday = mktime(0, 0, 0, $month, 1, $year);
			if($task['dateline'] && ($task['dateline'] > $monthstart || $task['dateline'] > $monthfirstday)) {
				$allowapply = -6;
				if($task['dateline'] > $monthfirstday) {
					$monthstart = mktime(0, 0, 0, $month + 1, $task['period'], $year);
				}
				$nextapplytime = dgmdate($monthstart);
			} else {
				$allowapply = 2;
			}
		}
		return array($allowapply, $nextapplytime);
	}

	function apply($id) {
		global $_G;

		if(!$this->task = DB::fetch_first("SELECT * FROM ".DB::table('common_task')." WHERE taskid='$id' AND available='2'")) {
			showmessage('task_nonexistence');
		} elseif(($this->task['starttime'] && $this->task['starttime'] > TIMESTAMP) || ($this->task['endtime'] && $this->task['endtime'] <= TIMESTAMP)) {
			showmessage('task_offline');
		} elseif($this->task['tasklimits'] && $this->task['achievers'] >= $this->task['tasklimits']) {
			showmessage('task_full');
		}

		if($this->task['relatedtaskid'] && !DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_mytask')." WHERE uid='$_G[uid]' AND taskid='".$this->task['relatedtaskid']."' AND status='1'")) {
			return -1;
		} elseif($this->task['applyperm'] && $this->task['applyperm'] != 'all' && !(($this->task['applyperm'] == 'member' && $_G['adminid'] == '0') || ($this->task['applyperm'] == 'admin' && $_G['adminid'] > '0') || preg_match("/(^|\t)(".$_G['groupid'].")(\t|$)/", $this->task['applyperm']))) {
			return -2;
		} elseif(!$this->task['period'] && DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_mytask')." WHERE uid='$_G[uid]' AND taskid='$id'")) {
			return -3;
		} elseif($this->task['period']) {
			$mytask = DB::fetch_first("SELECT mt.*, t.period, t.periodtype FROM ".DB::table('common_mytask')." mt
				INNER JOIN ".DB::table('common_task')." t USING(taskid)
				WHERE mt.uid='$_G[uid]' AND mt.taskid='$id' ORDER BY mt.dateline DESC");
			list($allowapply) = $this->checknextperiod($mytask);
			if($allowapply < 0) {
				return -4;
			}
		}

		require_once libfile('task/'.$this->task['scriptname'], 'class');
		$taskclassname = 'task_'.$this->task['scriptname'];
		$taskclass = new $taskclassname;
		if(method_exists($taskclass, 'condition')) {
			$taskclass->condition();
		}
		DB::query("REPLACE INTO ".DB::table('common_mytask')." (uid, username, taskid, csc, dateline)
			VALUES ('$_G[uid]', '$_G[username]', '".$this->task['taskid']."', '0\t$_G[timestamp]', '$_G[timestamp]')");
		DB::query("UPDATE ".DB::table('common_task')." SET applicants=applicants+1 WHERE taskid='".$this->task['taskid']."'", 'UNBUFFERED');
		if(method_exists($taskclass, 'preprocess')) {
			$taskclass->preprocess($this->task);
		}
		return true;
	}

	function draw($id) {
		global $_G;

		if(!($this->task = DB::fetch_first("SELECT t.*, mt.dateline AS applytime, mt.status FROM ".DB::table('common_task')." t, ".DB::table('common_mytask')." mt WHERE mt.uid='$_G[uid]' AND mt.taskid=t.taskid AND t.taskid='$id' AND t.available='2'"))) {
			showmessage('task_nonexistence');
		} elseif($this->task['status'] != 0) {
			showmessage('task_not_underway');
		} elseif($this->task['tasklimits'] && $this->task['achievers'] >= $this->task['tasklimits']) {
			return -1;
		}

		require_once libfile('task/'.$this->task['scriptname'], 'class');
		$taskclassname = 'task_'.$this->task['scriptname'];
		$taskclass = new $taskclassname;
		if(method_exists($taskclass, 'csc')) {
			$result = $taskclass->csc($this->task);
		} else {
			showmessage('task_not_found', '', array('taskclassname' => $taskclassname));
		}

		if($result === TRUE) {

			if($this->task['reward']) {
				$rewards = $this->reward();
				$notification = $this->task['reward'];
				if($this->task['reward'] == 'magic') {
					$rewardtext = DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE magicid='".$this->task['prize']."'");
				} elseif($this->task['reward'] == 'medal') {
					$rewardtext = DB::result_first("SELECT name FROM ".DB::table('forum_medal')." WHERE medalid='".$this->task['prize']."'");
					if(!$this->task['bonus']) {
						$notification = 'medal_forever';
					}
				} elseif($this->task['reward'] == 'group') {
					$rewardtext = DB::result_first("SELECT grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid='".$this->task['prize']."'");
				} elseif($this->task['reward'] == 'invite') {
					$rewardtext = $this->task['prize'];
				}
				notification_add($_G[uid], 'task', 'task_reward_'.$notification, array(
					'taskid' => $this->task['taskid'],
					'name' => $this->task['name'],
					'creditbonus' => $_G['setting']['extcredits'][$this->task['prize']]['title'].' '.$this->task['bonus'].' '.$_G['setting']['extcredits'][$this->task['prize']]['unit'],
					'rewardtext' => $rewardtext,
					'bonus' => $this->task['bonus'],
					'prize' => $this->task['prize'],
				));
			}

			if(method_exists($taskclass, 'sufprocess')) {
				$taskclass->sufprocess($this->task);
			}

			DB::query("UPDATE ".DB::table('common_mytask')." SET status='1', csc='100', dateline='$_G[timestamp]' WHERE uid='$_G[uid]' AND taskid='$id'");
			DB::query("UPDATE ".DB::table('common_task')." SET achievers=achievers+1 WHERE taskid='$id'", 'UNBUFFERED');

			if($_G['inajax']) {
				$this->message('100', $this->task['reward'] ? 'task_reward_'.$this->task['reward'] : 'task_completed', array(
						'creditbonus' => $_G['setting']['extcredits'][$this->task['prize']]['title'].' '.$this->task['bonus'].' '.$_G['setting']['extcredits'][$this->task['prize']]['unit'],
						'rewardtext' => $rewardtext,
						'bonus' => $this->task['bonus'],
						'prize' => $this->task['prize']
					)
				);
			} else {
				return true;
			}

		} elseif($result === FALSE) {

			DB::query("UPDATE ".DB::table('common_mytask')." SET status='-1' WHERE uid='$_G[uid]' AND taskid='$id'", 'UNBUFFERED');
			if($_G['inajax']) {
				$this->message('-1', 'task_failed');
			} else {
				return -2;
			}

		} else {

			$result['t'] = $this->timeformat($result['remaintime']);
			$this->messagevalues['values'] = array('csc' => $result['csc'], 't' => $result['t']);
			if($result['csc']) {
				DB::query("UPDATE ".DB::table('common_mytask')." SET csc='$result[csc]\t$_G[timestamp]' WHERE uid='$_G[uid]' AND taskid='$id'", 'UNBUFFERED');
				$this->messagevalues['msg'] = $result['t'] ? 'task_doing_rt' : 'task_doing';
			} else {
				$this->messagevalues['msg'] = $result['t'] ? 'task_waiting_rt' : 'task_waiting';
			}
			if($_G['inajax']) {
				$this->message($result['csc'], $this->messagevalues['msg'], $this->messagevalues['values']);
			} else {
				return -3;
			}

		}
	}

	function giveup($id) {
		global $_G;

		if($_G['gp_formhash'] != FORMHASH) {
			showmessage('undefined_action');
		} elseif(!($this->task = DB::fetch_first("SELECT t.taskid, mt.status FROM ".DB::table('common_task')." t LEFT JOIN ".DB::table('common_mytask')." mt ON mt.taskid=t.taskid AND mt.uid='$_G[uid]' WHERE t.taskid='$id' AND t.available='2'"))) {
			showmessage('task_nonexistence');
		} elseif($this->task['status'] != '0') {
			showmessage('task_not_underway');
		}

		DB::query("DELETE FROM ".DB::table('common_mytask')." WHERE uid='$_G[uid]' AND taskid='$id'", 'UNBUFFERED');
		DB::query("UPDATE ".DB::table('common_task')." SET applicants=applicants-1 WHERE taskid='$id'", 'UNBUFFERED');
	}

	function parter($id) {
		$query = DB::query("SELECT * FROM ".DB::table('common_mytask')." WHERE taskid='$id' ORDER BY dateline DESC LIMIT 0, 8");
		$parterlist = array();
		while($parter = DB::fetch($query)) {
			$parter['avatar'] = avatar($parter['uid'], 'small');
			$csc = explode("\t", $parter['csc']);
			$parter['csc'] = floatval($csc[0]);
			$parterlist[] = $parter;
		}
		return $parterlist;
	}

	function delete($id) {
		global $_G;
		$mytask = DB::fetch_first("SELECT * FROM ".DB::table('common_mytask')." WHERE uid='$_G[uid]' AND taskid='$id'");
		if(!($this->task = DB::fetch_first("SELECT * FROM ".DB::table('common_task')." WHERE taskid='$id' AND available='2'")) || empty($mytask) || $mytask['status'] == 1) {
			showmessage('task_nonexistence');
		}

		if(method_exists($taskclass, 'delete')) {
			$taskclass->delete($this->task);
		}

		DB::delete('common_mytask', "uid='$_G[uid]' AND taskid='$id'");
		DB::query("UPDATE ".DB::table('common_task')." SET applicants=applicants+'-1' WHERE taskid='$id'", 'UNBUFFERED');
		return true;
	}

	function reward() {
		switch($this->task['reward']) {
			case 'credit': return $this->reward_credit($this->task['prize'], $this->task['bonus']); break;
			case 'magic': return $this->reward_magic($this->task['prize'], $this->task['bonus']); break;
			case 'medal': return $this->reward_medal($this->task['prize'], $this->task['bonus']); break;
			case 'invite': return $this->reward_invite($this->task['prize'], $this->task['bonus']); break;
			case 'group': return $this->reward_group($this->task['prize'], $this->task['bonus']); break;
		}
	}

	function reward_credit($extcreditid, $credits) {
		global $_G;

		$creditsarray[$extcreditid] = $credits;
		updatemembercount($_G['uid'], $creditsarray, 1, 'TRC', $this->task['taskid']);
	}

	function reward_magic($magicid, $num) {
		global $_G;

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_magic')." WHERE uid='$_G[uid]' AND magicid='$magicid'")) {
			DB::query("UPDATE ".DB::table('common_member_magic')." SET num=num+'$num' WHERE magicid='$magicid' AND uid='$_G[uid]'", 'UNBUFFERED');
		} else {
			DB::query("INSERT INTO ".DB::table('common_member_magic')." (uid, magicid, num) VALUES ('$_G[uid]', '$magicid', '$num')");
		}
	}

	function reward_medal($medalid, $day) {
		global $_G;

		$medals = DB::result_first("SELECT medals FROM ".DB::table('common_member_field_forum')." WHERE uid='$_G[uid]'");
		if(empty($medals) || !in_array($medalid, explode("\t", $medals))) {
			$medalsnew = $medals ? $medals."\t".$medalid : $medalid;
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='$medalsnew' WHERE uid='$_G[uid]'", 'UNBUFFERED');
			DB::query("INSERT INTO ".DB::table('forum_medallog')." (uid, medalid, type, dateline, expiration, status) VALUES ('$_G[uid]', '$medalid', '0', '$_G[timestamp]', '".($day ? TIMESTAMP + $day * 86400 : '')."', '1')");
		}
	}

	function reward_invite($num, $day) {
		global $_G;
		$day = empty($day) ? 5 : $day;
		$expiration = $_G['timestamp'] + $day * 86400;
		$codes = array();
		for ($i=0; $i < $num; $i++) {
			$code = strtolower(random(6));
			$codes[] = "('$_G[uid]', '$code', '$_G[timestamp]', '$expiration', '$_G[clientip]')";
		}

		if($codes) {
			DB::query("INSERT INTO ".DB::table('common_invite')." (uid, code, dateline, endtime, inviteip) VALUES ".implode(',', $codes));
		}
	}

	function reward_group($gid, $day = 0) {
		global $_G;

		$exists = FALSE;
		if($_G['forum_extgroupids']) {
			$_G['forum_extgroupids'] = explode("\t", $_G['forum_extgroupids']);
			if(in_array($gid, $_G['forum_extgroupids'])) {
				$exists = TRUE;
			} else {
				$_G['forum_extgroupids'][] = $gid;
			}
			$_G['forum_extgroupids'] = implode("\t", $_G['forum_extgroupids']);
		} else {
			$_G['forum_extgroupids'] = $gid;
		}

		DB::query("UPDATE ".DB::table('common_member')." SET extgroupids='".$_G['forum_extgroupids']."' WHERE uid='$_G[uid]'", 'UNBUFFERED');

		if($day) {
			$groupterms = DB::result_first("SELECT groupterms FROM ".DB::table('common_member_field_forum')." WHERE uid='$_G[uid]'");
			$groupterms = $groupterms ? unserialize($groupterms) : array();
			$groupterms['ext'][$gid] = $exists && $groupterms['ext'][$gid] ? max($groupterms['ext'][$gid], TIMESTAMP + $day * 86400) : TIMESTAMP + $day * 86400;
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".addslashes(serialize($groupterms))."' WHERE uid='$_G[uid]'", 'UNBUFFERED');
		}
	}

	function message($csc, $msg, $values = array()) {
		include template('common/header_ajax');
		$msg = lang('message', $msg, $values);
		echo "$csc|$msg";
		include template('common/footer_ajax');
		exit;
	}

	function timeformat($t) {
		global $_G;

		if($t) {
			$h = floor($t / 3600);
			$m = floor(($t - $h * 3600) / 60);
			$s = floor($t - $h * 3600 - $m * 60);
			return ($h ? "$h{$_G['setting']['dlang'][date][4]}" : '').($m ? "$m{$_G[setting][dlang][date][6]}" : '').($h || !$s ? '' : "$s{$_G[setting][dlang][date][7]}");
		}
		return '';
	}

}

function tasktimeformat($t) {
	global $_G;

	if($t) {
		$h = floor($t / 3600);
		$m = floor(($t - $h * 3600) / 60);
		$s = floor($t - $h * 3600 - $m * 60);
		return ($h ? "$h{$_G['lang']['core']['date']['hour']}" : '').($m ? "$m{$_G['lang']['core']['date']['min']}" : '').($h || !$s ? '' : "$s{$_G['lang']['core']['date']['sec']}");
	}
	return '';
}

?>