<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_tasks.php 20616 2011-03-01 01:05:56Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$id = intval($_G['gp_id']);
$membervars = array('act', 'num', 'time');
$postvars = array('act', 'forumid', 'num', 'time', 'threadid', 'authorid');
$modvars = array();
$custom_types = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='tasktypes'");
$custom_types = $custom_types ? (array)unserialize($custom_types) : array();
$custom_scripts = array_keys($custom_types);

$submenus = array();
foreach($custom_types as $k => $v) {
	$submenus[] = array($v['name'], "tasks&operation=add&script=$k", $_G['gp_script'] == $k);
}

if(!($operation)) {

	if(!submitcheck('tasksubmit')) {

		shownav('extended', 'nav_tasks');
		showsubmenu('nav_tasks', array(
			array('admin', 'tasks', 1),
			$submenus ? array(array('menu' => 'add', 'submenu' => $submenus)) : array(),
			array('nav_task_type', 'tasks&operation=type', 0)
		));
		showformheader('tasks');
		showtableheader();
		showsetting('tasks_on', 'taskonnew', $_G['setting']['taskon'], 'radio');
		showtablefooter();
		showtableheader('tasks_list', 'fixpadding');
		showsubtitle(array('display_order', 'available', 'name', 'tasks_reward', 'time', ''));

		$starttasks = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_task')." ORDER BY displayorder, taskid DESC");
		while($task = DB::fetch($query)) {

			if($task['reward'] == 'credit') {
				$reward = cplang('credits').' '.$_G['setting']['extcredits'][$task['prize']]['title'].' '.$task['bonus'].' '.$_G['setting']['extcredits'][$task['prize']]['unit'];
			} elseif($task['reward'] == 'magic') {
				$magicname = DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE magicid='$task[prize]'");
				$reward = cplang('tasks_reward_magic').' '.$magicname.' '.$task['bonus'].' '.cplang('magic_unit');
			} elseif($task['reward'] == 'medal') {
				$medalname = DB::result_first("SELECT name FROM ".DB::table('forum_medal')." WHERE medalid='$task[prize]'");
				$reward = cplang('medals').' '.$medalname.($task['bonus'] ? ' '.cplang('validity').$task['bonus'].' '.cplang('days') : '');
			} elseif($task['reward'] == 'invite') {
				$reward = cplang('tasks_reward_invite').' '.$task['prize'].($task['bonus'] ? ' '.cplang('validity').$task['bonus'].' '.cplang('days') : '');
			} elseif($task['reward'] == 'group') {
				$grouptitle = DB::result_first("SELECT grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid='$task[prize]'");
				$reward = cplang('usergroup').' '.$grouptitle.($task['bonus'] ? ' '.cplang('validity').' '.$task['bonus'].' '.cplang('days') : '');
			} else {
				$reward = cplang('none');
			}
			if($task['available'] == '1' && (!$task['starttime'] || $task['starttime'] <= TIMESTAMP) && (!$task['endtime'] || $task['endtime'] > TIMESTAMP)) {
				$starttasks[] = $task['taskid'];
			}

			$checked = $task['available'] ? ' checked="checked"' : '';

			if($task['starttime'] && $task['endtime']) {
				$task['time'] = dgmdate($task['starttime'], 'y-m-d H:i').' ~ '.dgmdate($task['endtime'], 'y-m-d H:i');
			} elseif($task['starttime'] && !$task['endtime']) {
				$task['time'] = dgmdate($task['starttime'], 'y-m-d H:i').' '.cplang('tasks_online');
			} elseif(!$task['starttime'] && $task['endtime']) {
				$task['time'] = dgmdate($task['endtime'], 'y-m-d H:i').' '.cplang('tasks_offline');
			} else {
				$task['time'] = cplang('nolimit');
			}

			showtablerow('', array('class="td25"', 'class="td25"'), array(
				'<input type="text" class="txt" name="displayordernew['.$task['taskid'].']" value="'.$task['displayorder'].'" size="3" />',
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$task[taskid]]\" value=\"1\"$checked><input type=\"hidden\" name=\"availableold[$task[taskid]]\" value=\"$task[available]\">",
				"<input type=\"text\" class=\"txt\" name=\"namenew[$task[taskid]]\" size=\"20\" value=\"$task[name]\"><input type=\"hidden\" name=\"nameold[$task[taskid]]\" value=\"$task[name]\">",
				$reward,
				$task['time'].'<input type="hidden" name="scriptnamenew['.$task['taskid'].']" value="'.$task['scriptname'].'">',
				"<a href=\"".ADMINSCRIPT."?action=tasks&operation=edit&id=$task[taskid]\" class=\"act\">$lang[edit]</a>&nbsp;&nbsp;<a href=\"".ADMINSCRIPT."?action=tasks&operation=delete&id=$task[taskid]\" class=\"act\">$lang[delete]</a>"
			));

		}

		if($starttasks) {
			DB::query("UPDATE ".DB::table('common_task')." SET available='2' WHERE taskid IN (".dimplode($starttasks).")", 'UNBUFFERED');
		}

		showsubmit('tasksubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$checksettingsok = TRUE;
		if(is_array($_G['gp_namenew'])) {
			foreach($_G['gp_namenew'] as $id => $name) {
				$_G['gp_availablenew'][$id] = $_G['gp_availablenew'][$id] && (!$starttimenew[$id] || $starttimenew[$id] <= TIMESTAMP) && (!$endtimenew[$id] || $endtimenew[$id] > TIMESTAMP) ? 2 : $_G['gp_availablenew'][$id];
				$displayorderadd = isset($_G['gp_displayordernew'][$id]) ? ", displayorder='{$_G['gp_displayordernew'][$id]}'" : '';
				DB::query("UPDATE ".DB::table('common_task')." SET name='".dhtmlspecialchars($_G['gp_namenew'][$id])."', available='{$_G['gp_availablenew'][$id]}'$displayorderadd WHERE taskid='$id'");
			}
		}

		if($_G['gp_taskonnew'] != $_G['setting']['taskon']) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('taskon', '{$_G['gp_taskonnew']}')");
		}

		updatecache('setting');

		if($checksettingsok) {
			cpmsg('tasks_succeed', 'action=tasks', 'succeed');
		} else {
			cpmsg('tasks_setting_invalid', '', 'error');
		}

	}

} elseif($operation == 'add' && $_G['gp_script']) {

	$task_name = $task_description = $task_icon = $task_period = $task_periodtype = $task_conditions = '';
	if(in_array($_G['gp_script'], $custom_scripts)) {
		require_once libfile('task/'.$_G['gp_script'], 'class');
		$taskclass = 'task_'.$_G['gp_script'];
		$task = new $taskclass;
		$task_name = lang('task/'.$_G['gp_script'], $task->name);
		$task_description = lang('task/'.$_G['gp_script'], $task->description);
		$task_icon = $task->icon;
		$task_period = $task->period;
		$task_periodtype = $task->periodtype;
		$task_conditions = $task->conditions;
	} else {
		cpmsg('parameters_error', '', 'error');
	}

	if(!submitcheck('addsubmit')) {

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		shownav('extended', 'nav_tasks');
		showsubmenu('nav_tasks', array(
			array('admin', 'tasks', 0),
			array(array('menu' => 'add', 'submenu' => $submenus), 1),
			array('nav_task_type', 'tasks&operation=type', 0)
		));

		showformheader('tasks&operation=add&script='.$_G['gp_script']);
		showtableheader('tasks_add_basic', 'fixpadding');
		showsetting('tasks_add_name', 'name', $task_name, 'text');
		showsetting('tasks_add_desc', 'description', $task_description, 'textarea');
		showsetting('tasks_add_icon', 'iconnew', $task_icon, 'text');
		showsetting('tasks_add_starttime', 'starttime', '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_endtime', 'endtime', '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_periodtype', array('periodtype', array(
			array(0, cplang('tasks_add_periodtype_hour')),
			array(1, cplang('tasks_add_periodtype_day')),
			array(2, cplang('tasks_add_periodtype_week')),
			array(3, cplang('tasks_add_periodtype_month')),
		)), $task_periodtype, 'mradio');
		showsetting('tasks_add_period', 'period', $task_period, 'text');
		showsetting('tasks_add_reward', array('reward', array(
			array('', cplang('none'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => 'none')),
			array('credit', cplang('credits'), array('reward_credit' => '', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => 'none')),
			$_G['setting']['magicstatus'] ? array('magic', cplang('tasks_reward_magic'), array('reward_credit' => 'none', 'reward_magic' => '', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => 'none')) : '',
			$_G['setting']['medalstatus'] ? array('medal', cplang('medals'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => '', 'reward_group' => 'none', 'reward_invite' => 'none')) : '',
			$_G['setting']['regstatus'] > 1 ? array('invite', cplang('tasks_reward_invite'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => '')) : '',
			array('group', cplang('tasks_add_group'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => '', 'reward_invite' => 'none'))
		)), '', 'mradio');

		$extcreditarray = array(array(0, cplang('select')));
		foreach($_G['setting']['extcredits'] as $creditid => $extcredit) {
			$extcreditarray[] = array($creditid, $extcredit['title']);
		}

		showtagheader('tbody', 'reward_credit');
		showsetting('tasks_add_extcredit', array('prize_credit', $extcreditarray), 0, 'select');
		showsetting('tasks_add_credits', 'bonus_credit', '0', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_magic');
		showsetting('tasks_add_magicname', array('prize_magic', fetcharray('magicid', 'name', 'common_magic', "available='1' ORDER BY displayorder")), 0, 'select');
		showsetting('tasks_add_magicnum', 'bonus_magic', '0', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_medal');
		showsetting('tasks_add_medalname', array('prize_medal', fetcharray('medalid', 'name', 'forum_medal', "available='1' ORDER BY displayorder")), 0, 'select');
		showsetting('tasks_add_medalexp', 'bonus_medal', '', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_invite');
		showsetting('tasks_add_invitenum', 'prize_invite', '1', 'text');
		showsetting('tasks_add_inviteexp', 'bonus_invite', '10', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_group');
		showsetting('tasks_add_group', array('prize_group', fetcharray('groupid', 'grouptitle', 'common_usergroup', "type='special' AND radminid='0'")), 0, 'select');
		showsetting('tasks_add_groupexp', 'bonus_group', '', 'text');
		showtagfooter('tbody');

		showtitle('tasks_add_appyperm');
		showsetting('tasks_add_groupperm', array('grouplimit', array(
			array('all', cplang('tasks_add_group_all'), array('specialgroup' => 'none')),
			array('member', cplang('tasks_add_group_member'), array('specialgroup' => 'none')),
			array('admin', cplang('tasks_add_group_admin'), array('specialgroup' => 'none')),
			array('special', cplang('tasks_add_group_special'), array('specialgroup' => ''))
		)), 'all', 'mradio');
		showtagheader('tbody', 'specialgroup');
		showsetting('tasks_add_usergroup', array('applyperm[]', fetcharray('groupid', 'grouptitle', 'common_usergroup', '')), 0, 'mselect');
		showtagfooter('tbody');
		showsetting('tasks_add_maxnum', 'tasklimits', '', 'text');

		if(is_array($task_conditions)) {
			foreach($task_conditions as $taskvarkey => $taskvar) {
				if($taskvar['sort'] == 'apply' && $taskvar['title']) {
					if(!empty($taskvar['value']) && is_array($taskvar['value'])) {
						foreach($taskvar['value'] as $k => $v) {
							$taskvar['value'][$k][1] = lang('task/'.$_G['gp_script'], $taskvar['value'][$k][1]);
						}
					}
					$varname = in_array($taskvar['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
						($taskvar['type'] == 'mselect' ? array($taskvarkey.'[]', $taskvar['value']) : array($taskvarkey, $taskvar['value']))
						: $taskvarkey;
					$comment = lang('task/'.$_G['gp_script'], $taskvar['title'].'_comment');
					$comment = $comment != $taskvar['title'].'_comment' ? $comment : '';
					showsetting(lang('task/'.$_G['gp_script'], $taskvar['title']).':', $varname, $taskvar['value'], $taskvar['type'], '', 0, $comment);
				}
			}
		}

		showtitle('tasks_add_conditions');

		if(in_array($_G['gp_script'], $custom_scripts)) {

			$haveconditions = false;
			if(is_array($task_conditions)) {
				foreach($task_conditions as $taskvarkey => $taskvar) {
					if($taskvar['sort'] == 'complete' && $taskvar['title']) {
						if(!empty($taskvar['value']) && is_array($taskvar['value'])) {
							foreach($taskvar['value'] as $k => $v) {
								$taskvar['value'][$k][1] = lang('task/'.$_G['gp_script'], $taskvar['value'][$k][1]);
							}
						}
						$haveconditions = true;
						$varname = in_array($taskvar['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
							($taskvar['type'] == 'mselect' ? array($taskvarkey.'[]', $taskvar['value']) : array($taskvarkey, $taskvar['value']))
							: $taskvarkey;
						$comment = lang('task/'.$_G['gp_script'], $taskvar['title'].'_comment');
						$comment = $comment != $taskvar['title'].'_comment' ? $comment : '';
						showsetting(lang('task/'.$_G['gp_script'], $taskvar['title']).':', $varname, $taskvar['default'], $taskvar['type'], '', 0, $comment);
					}
				}
			}
			if(!$haveconditions) {
				showtablerow('', 'class="td27" colspan="2"', cplang('nolimit'));
			}
		}

		showsubmit('addsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$applyperm = $_G['gp_grouplimit'] == 'special' && is_array($_G['gp_applyperm']) ? implode("\t", $_G['gp_applyperm']) : $_G['gp_grouplimit'];
		$_G['gp_starttime'] = strtotime($_G['gp_starttime']);
		$_G['gp_endtime'] = strtotime($_G['gp_endtime']);
		$reward = $_G['gp_reward'];
		$prize = $_G['gp_prize_'.$reward];
		$bonus = $_G['gp_bonus_'.$reward];
		if(!$_G['gp_name'] || !$_G['gp_description']) {
			cpmsg('tasks_basic_invalid', '', 'error');
		} elseif(($_G['gp_endtime'] && $_G['gp_endtime'] <= TIMESTAMP) || ($_G['gp_starttime'] && $_G['gp_endtime'] && $_G['gp_endtime'] <= $_G['gp_starttime'])) {
			cpmsg('tasks_time_invalid', '', 'error');
		} elseif($reward && (!$prize || ($reward == 'credit' && !$bonus))) {
			cpmsg('tasks_reward_invalid', '', 'error');
		}
		$data = array(
			'relatedtaskid' => $_G['gp_relatedtaskid'],
			'available' => 0,
			'name' => $_G['gp_name'],
			'description' => $_G['gp_description'],
			'icon' => $_G['gp_iconnew'],
			'tasklimits' => $_G['gp_tasklimits'],
			'applyperm' => $applyperm,
			'scriptname' => $_G['gp_script'],
			'starttime' => $_G['gp_starttime'],
			'endtime' => $_G['gp_endtime'],
			'period' => $_G['gp_period'],
			'periodtype' => $_G['gp_periodtype'],
			'reward' => $reward,
			'prize' => $prize,
			'bonus' => $bonus,
		);
		$taskid = DB::insert('common_task', $data, 1);

		if(is_array($task_conditions)) {
			foreach($task_conditions as $taskvarkey => $taskvars) {
				if($taskvars['title']) {
					$comment = lang('task/'.$_G['gp_script'], $taskvars['title'].'_comment');
					$comment = $comment != $taskvars['title'].'_comment' ? $comment : '';
					$data = array(
						'taskid' => $taskid,
						'sort' => $taskvars['sort'],
						'name' => lang('task/'.$_G['gp_script'], $taskvars['title']),
						'description' => $comment,
						'variable' => $taskvarkey,
						'value' => is_array($_G['gp_'.$taskvarkey]) ? addslashes(serialize($_G['gp_'.$taskvarkey])) : $_G['gp_'.$taskvarkey],
						'type' => $taskvars['type'],
					);
					DB::insert('common_taskvar', $data);
				}
			}
		}

		cpmsg('tasks_succeed', "action=tasks", 'succeed');

	}

} elseif($operation == 'edit' && $id) {

	$task = DB::fetch_first("SELECT * FROM ".DB::table('common_task')." WHERE taskid='$id'");

	if(!submitcheck('editsubmit')) {

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		shownav('extended', 'nav_tasks');
		showsubmenu('nav_tasks', array(
			array('admin', 'tasks', 0),
			array(array('menu' => 'add', 'submenu' => $submenus)),
			array('nav_task_type', 'tasks&operation=type', 0)
		));

		showformheader('tasks&operation=edit&id='.$id);
		showtableheader(cplang('tasks_edit').' - '.$task['name'], 'fixpadding');
		showsetting('tasks_add_name', 'name', $task['name'], 'text');
		showsetting('tasks_add_desc', 'description', $task['description'], 'textarea');
		showsetting('tasks_add_icon', 'iconnew', $task['icon'], 'text');
		showsetting('tasks_add_starttime', 'starttime', $task['starttime'] ? dgmdate($task['starttime'], 'Y-m-d H:i') : '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_endtime', 'endtime', $task['endtime'] ? dgmdate($task['endtime'], 'Y-m-d H:i') : '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_periodtype', array('periodtype', array(
			array(0, cplang('tasks_add_periodtype_hour')),
			array(1, cplang('tasks_add_periodtype_day')),
			array(2, cplang('tasks_add_periodtype_week')),
			array(3, cplang('tasks_add_periodtype_month')),
		)), $task['periodtype'], 'mradio');
		showsetting('tasks_add_period', 'period', $task['period'], 'text');
		showsetting('tasks_add_reward', array('reward', array(
			array('', cplang('none'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none')),
			array('credit', cplang('credits'), array('reward_credit' => '', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none')),
			$_G['setting']['magicstatus'] ? array('magic', cplang('tasks_reward_magic'), array('reward_credit' => 'none', 'reward_magic' => '', 'reward_medal' => 'none', 'reward_group' => 'none')) : '',
			$_G['setting']['medalstatus'] ? array('medal', cplang('medals'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => '', 'reward_group' => 'none')) : '',
			$_G['setting']['regstatus'] > 1 ? array('invite', cplang('tasks_reward_invite'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => '')) : '',
			array('group', cplang('tasks_add_group'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => ''))
		)), $task['reward'], 'mradio');

		$extcreditarray = array(array(0, cplang('select')));
		foreach($_G['setting']['extcredits'] as $creditid => $extcredit) {
			$extcreditarray[] = array($creditid, $extcredit['title']);
		}

		showtagheader('tbody', 'reward_credit', $task['reward'] == 'credit');
		showsetting('tasks_add_extcredit', array('prize_credit', $extcreditarray), $task['prize'], 'select');
		showsetting('tasks_add_credits', 'bonus_credit', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_magic', $task['reward'] == 'magic');
		showsetting('tasks_add_magicname', array('prize_magic', fetcharray('magicid', 'name', 'common_magic', "available='1' ORDER BY displayorder")), $task['prize'], 'select');
		showsetting('tasks_add_magicnum', 'bonus_magic', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_medal', $task['reward'] == 'medal');
		showsetting('tasks_add_medalname', array('prize_medal', fetcharray('medalid', 'name', 'forum_medal', "available='1' ORDER BY displayorder")), $task['prize'], 'select');
		showsetting('tasks_add_medalexp', 'bonus_medal', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_invite', $task['reward'] == 'invite');
		showsetting('tasks_add_invitenum', 'prize_invite', $task['prize'], 'text');
		showsetting('tasks_add_inviteexp', 'bonus_invite', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_group', $task['reward'] == 'group');
		showsetting('tasks_add_group', array('prize_group', fetcharray('groupid', 'grouptitle', 'common_usergroup', "type='special' AND radminid='0'")), $task['prize'], 'select');
		showsetting('tasks_add_groupexp', 'bonus_group', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtitle('tasks_add_appyperm');
		if(!$task['applyperm']) {
			$task['applyperm'] = 'all';
		}
		$task['grouplimit'] = in_array($task['applyperm'], array('all', 'member', 'admin')) ? $task['applyperm'] : 'special';
		showsetting('tasks_add_groupperm', array('grouplimit', array(
			array('all', cplang('tasks_add_group_all'), array('specialgroup' => 'none')),
			array('member', cplang('tasks_add_group_member'), array('specialgroup' => 'none')),
			array('admin', cplang('tasks_add_group_admin'), array('specialgroup' => 'none')),
			array('special', cplang('tasks_add_group_special'), array('specialgroup' => ''))
		)), $task['grouplimit'], 'mradio');
		showtagheader('tbody', 'specialgroup', $task['grouplimit'] == 'special');
		showsetting('tasks_add_usergroup', array('applyperm[]', fetcharray('groupid', 'grouptitle', 'common_usergroup', '')), explode("\t", $task['applyperm']), 'mselect');
		showtagfooter('tbody');
		showsetting('tasks_add_relatedtask', array('relatedtaskid', fetcharray('taskid', 'name', 'common_task', "available='2' AND taskid!='$task[taskid]'")), $task['relatedtaskid'], 'select');
		showsetting('tasks_add_maxnum', 'tasklimits', $task['tasklimits'], 'text');

		$taskvars = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_taskvar')." WHERE taskid='$id'");
		while($taskvar = DB::fetch($query)) {
			if($taskvar['sort'] == 'apply') {
				$taskvars['apply'][] = $taskvar;
			} elseif($taskvar['sort'] == 'complete') {
				$taskvars['complete'][$taskvar['variable']] = $taskvar;
			} elseif($taskvar['sort'] == 'setting' && $taskvar['name']) {
				$taskvars['setting'][$taskvar['variable']] = $taskvar;
			}
		}

		if($taskvars['apply']) {
			foreach($taskvars['apply'] as $taskvar) {
				showsetting($taskvar['name'], $taskvar['variable'], $taskvar['value'], $taskvar['type'], '', 0, $taskvar['description']);
			}
		}

		showtitle('tasks_add_conditions');

		require_once libfile('task/'.$task['scriptname'], 'class');
		$taskclass = 'task_'.$task['scriptname'];
		$taskcv = new $taskclass;

		if($taskvars['complete']) {
			foreach($taskvars['complete'] as $taskvar) {
				$taskcvar = $taskcv->conditions[$taskvar['variable']];
				if(is_array($taskcvar['value'])) {
					foreach($taskcvar['value'] as $k => $v) {
						$taskcvar['value'][$k][1] = lang('task/'.$task['scriptname'], $taskcvar['value'][$k][1]);
					}
				}
				$varname = in_array($taskvar['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
					($taskvar['type'] == 'mselect' ? array($taskvar['variable'].'[]', $taskcvar['value']) : array($taskvar['variable'], $taskcvar['value']))
					: $taskvar['variable'];
				if(in_array($taskvar['type'], array('mcheckbox', 'mselect'))) {
					$taskvar['value'] = unserialize($taskvar['value']);
				}
				showsetting($taskvar['name'], $varname, $taskvar['value'], $taskvar['type'], '', 0, $taskvar['description']);
			}
		} else {
			showtablerow('', 'class="td27" colspan="2"', cplang('nolimit'));
		}

		showsubmit('editsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$applyperm = $_G['gp_grouplimit'] == 'special' && is_array($_G['gp_applyperm']) ? implode("\t", $_G['gp_applyperm']) : $_G['gp_grouplimit'];
		$_G['gp_starttime'] = strtotime($_G['gp_starttime']);
		$_G['gp_endtime'] = strtotime($_G['gp_endtime']);
		$reward = $_G['gp_reward'];
		$prize = $_G['gp_prize_'.$reward];
		$bonus = $_G['gp_bonus_'.$reward];

		if(!$_G['gp_name'] || !$_G['gp_description']) {
			cpmsg('tasks_basic_invalid', '', 'error');
		} elseif(($_G['gp_starttime'] != $task['starttime'] || $_G['gp_endtime'] != $task['endtime']) && (($_G['gp_endtime'] && $_G['gp_endtime'] <= TIMESTAMP) || ($_G['gp_starttime'] && $_G['gp_endtime'] && $_G['gp_endtime'] <= $_G['gp_starttime']))) {
			cpmsg('tasks_time_invalid', '', 'error');
		} elseif($reward && (!$prize || ($reward == 'credit' && !$bonus))) {
			cpmsg('tasks_reward_invalid', '', 'error');
		}

		if($task['available'] == '2' && ($_G['gp_starttime'] > TIMESTAMP || ($_G['gp_endtime'] && $_G['gp_endtime'] <= TIMESTAMP))) {
			DB::query("UPDATE ".DB::table('common_task')." SET available='1' WHERE taskid='$id'", 'UNBUFFERED');
		}
		if($task['available'] == '1' && (!$_G['gp_starttime'] || $_G['gp_starttime'] <= TIMESTAMP) && (!$_G['gp_endtime'] || $_G['gp_endtime'] > TIMESTAMP)) {
			DB::query("UPDATE ".DB::table('common_task')." SET available='2' WHERE taskid='$id'", 'UNBUFFERED');
		}

		$itemarray = array();
		$query = DB::query("SELECT variable FROM ".DB::table('common_taskvar')." WHERE taskid='$id' AND variable IS NOT NULL");
		while($taskvar = DB::fetch($query)) {
			$itemarray[] = $taskvar['variable'];
		}
		DB::update('common_task', array(
			'relatedtaskid' => $_G['gp_relatedtaskid'],
			'name' => $_G['gp_name'],
			'description' => $_G['gp_description'],
			'icon' => $_G['gp_iconnew'],
			'tasklimits' => $_G['gp_tasklimits'],
			'applyperm' => $applyperm,
			'starttime' => $_G['gp_starttime'],
			'endtime' => $_G['gp_endtime'],
			'period' => $_G['gp_period'],
			'periodtype' => $_G['gp_periodtype'],
			'reward' => $reward,
			'prize' => $prize,
			'bonus' => $bonus,
		), "taskid='$id'");

		foreach($itemarray as $item) {
			$value = $_G['gp_'.$item];
			if(in_array($item, array('num', 'time', 'threadid'))) {
				$value = intval($value);
			}
			if($value !== null) {
				$value = is_array($value) ? addslashes(serialize($value)) : $value;
				DB::query("UPDATE ".DB::table('common_taskvar')." SET value='".$value."' WHERE taskid='$id' AND variable='$item'");
			}
		}

		cpmsg('tasks_succeed', "action=tasks", 'succeed');

	}

} elseif($operation == 'delete' && $id) {

	if(!$_G['gp_confirmed']) {
		cpmsg('tasks_del_confirm', "action=tasks&operation=delete&id=$id", 'form');
	}

	DB::query("DELETE FROM ".DB::table('common_task')." WHERE taskid='$id'");
	DB::query("DELETE FROM ".DB::table('common_taskvar')." WHERE taskid='$id'");
	DB::query("DELETE FROM ".DB::table('common_mytask')." WHERE taskid='$id'");

	cpmsg('tasks_del', 'action=tasks', 'succeed');

} elseif($operation == 'type') {

	shownav('extended', 'nav_tasks');
	showsubmenu('nav_tasks', array(
		array('admin', 'tasks', 0),
		$submenus ? array(array('menu' => 'add', 'submenu' => $submenus)) : array(),
		array('nav_task_type', 'tasks&operation=type', 1)
	));
	showtips('tasks_tips_add_type');

	$tasks = gettasks();

	showtableheader('', 'fixpadding');

	if($tasks) {
		showsubtitle(array('name', 'tasks_version', 'copyright', ''));
		foreach($tasks as $task) {
			showtablerow('', '', array(
				$task['name'].($task['filemtime'] > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : ''),
				$task['version'],
				$task['copyright'],
				in_array($task['class'], $custom_scripts) ? "<a href=\"".ADMINSCRIPT."?action=tasks&operation=upgrade&script=$task[class]\" class=\"act\">$lang[tasks_upgrade]</a> <a href=\"".ADMINSCRIPT."?action=tasks&operation=uninstall&script=$task[class]\" class=\"act\">$lang[tasks_uninstall]</a><br />" : "<a href=\"".ADMINSCRIPT."?action=tasks&operation=install&script=$task[class]\" class=\"act\">$lang[tasks_install]</a>"
			));
		}
	} else {
		showtablerow('', '', $lang['task_module_nonexistence']);
	}

	showtablefooter();

} elseif($operation == 'install' && $_G['gp_script']) {

	if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_task')." WHERE scriptname='{$_G['gp_script']}'")) {
		cpmsg('tasks_install_duplicate', '', 'error');
	}

	require_once libfile('task/'.$_G['gp_script'], 'class');
	$taskclass = 'task_'.$_G['gp_script'];
	$task = new $taskclass;
	if(method_exists($task, 'install')) {
		$task->install();
	}

	$custom_types[$_G['gp_script']] = array('name' => lang('task/'.$_G['gp_script'], $task->name), 'version' => $task->version);
	DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('tasktypes', '".addslashes(serialize($custom_types))."')");

	cpmsg('tasks_installed', 'action=tasks&operation=type', 'succeed');

} elseif($operation == 'uninstall' && $_G['gp_script']) {

	if(!$_G['gp_confirmed']) {
		cpmsg('tasks_uninstall_confirm', "action=tasks&operation=uninstall&script={$_G['gp_script']}", 'form');
	}

	$ids = $comma = '';
	$query = DB::query("SELECT taskid FROM ".DB::table('common_task')." WHERE scriptname='{$_G['gp_script']}'");
	while($task = DB::fetch($query)) {
		$ids .= $comma.$task['taskid'];
		$comma = ',';
	}
	if($ids) {
		DB::query("DELETE FROM ".DB::table('common_task')." WHERE taskid IN ($ids)");
		DB::query("DELETE FROM ".DB::table('common_taskvar')." WHERE taskid IN ($ids)");
		DB::query("DELETE FROM ".DB::table('common_mytask')." WHERE taskid IN ($ids)");
	}

	require_once libfile('task/'.$_G['gp_script'], 'class');
	$taskclass = 'task_'.$_G['gp_script'];
	$task = new $taskclass;
	if(method_exists($task, 'uninstall')) {
		$task->uninstall();
	}

	unset($custom_types[$_G['gp_script']]);
	DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('tasktypes', '".addslashes(serialize($custom_types))."')");

	cpmsg('tasks_uninstalled', 'action=tasks&operation=type', 'succeed');

} elseif($operation == 'upgrade' && $_G['gp_script']) {

	require_once libfile('task/'.$_G['gp_script'], 'class');
	$taskclass = 'task_'.$_G['gp_script'];
	$task = new $taskclass;

	if($custom_types[$_G['gp_script']]['version'] >= $task->version) {
		cpmsg('tasks_newest', '', 'error');
	}

	if(method_exists($task, 'upgrade')) {
		$task->upgrade();
	}
	$task->name = lang('task/'.$_G['gp_script'], $task->name);
	$task->description = lang('task/'.$_G['gp_script'], $task->description);

	DB::query("UPDATE ".DB::table('common_task')." SET version='".$task->version."' WHERE scriptname='{$_G['gp_script']}'");
	$custom_types[$_G['gp_script']] = array('name' => $task->name, 'version' => $task->version);
	DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('tasktypes', '".addslashes(serialize($custom_types))."')");

	cpmsg('tasks_updated', 'action=tasks&operation=type', 'succeed');

}

function fetcharray($id, $name, $table, $conditions = '1') {
	$array = array(array(0, cplang('nolimit')));
	$wheresql = $conditions ? " WHERE $conditions" : '';
	$query = DB::query("SELECT $id, $name FROM ".DB::table($table).($wheresql));
	while($result = DB::fetch($query)) {
		$array[] = array($result[$id], $result[$name]);
	}
	return $array;
}

function runquery($sql) {
	global $dbcharset, $tablepre, $db;

	$sql = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(createtable($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function checksettings($id, $v) {
	$v = intval($v);
	if(!$v) {
		return FALSE;
	}
	switch($id) {
		case 'tid':
			$result = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE tid='$v' AND displayorder>='0'");
			break;
		case 'fid':
			$result = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE fid='$v'");
			break;
		case 'uid':
			$result = DB::query("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE uid='$v'");
			break;
		default:
			$result = 0;
			break;
	}
	return $result ? TRUE : FALSE;
}

function gettasks() {
	global $_G;
	$dir = DISCUZ_ROOT.'./source/class/task';
	$taskdir = dir($dir);
	$tasks = array();
	while($entry = $taskdir->read()) {
		if(!in_array($entry, array('.', '..')) && preg_match("/^task\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
			@include_once $dir.'/'.$entry;
			$taskclass = substr($entry, 0, -4);
			if(class_exists($taskclass)) {
				$task = new $taskclass();
				$_G['gp_script'] = substr($taskclass, 5);
				$tasks[$entry] = array(
					'class' => $_G['gp_script'],
					'name' => lang('task/'.$_G['gp_script'], $task->name),
					'version' => $task->version,
					'copyright' => lang('task/'.$_G['gp_script'], $task->copyright),
					'filemtime' => @filemtime($dir.'/'.$entry)
				);
			}
		}
	}
	uasort($tasks, 'filemtimesort');
	return $tasks;
}

?>