<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_credits.php 14802 2010-08-16 05:28:46Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation ? $operation : 'list';

if($operation == 'list') {
	$rules = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule'));
	while($value = DB::fetch($query)) {
		$rules[$value['rid']] = $value;
	}
	if(!submitcheck('rulesubmit')) {
		$lowerlimit = array(
			'rid' => 0,
			'rulename' => $lang['credits_edit_lowerlimit'],
		);

		$anchor = in_array($_G['gp_anchor'], array('base', 'policytable', 'edit')) ? $_G['gp_anchor'] : 'base';
		$current = array($anchor => 1);
		showsubmenu('setting_credits', array(
			array('setting_credits_base', 'setting&operation=credits&anchor=base', $current['base']),
			array('setting_credits_policy', 'credits&operation=list&anchor=policytable', $current['policytable']),
		));

		showformheader("credits&operation=list");
		showtableheader('setting_credits_policy', 'nobottom', 'id="policytable"'.($anchor != 'policytable' ? ' style="display: none"' : ''));
		echo '<tr><th class="td28 nowrap">'.$lang['setting_credits_policy_name'].'</th><th class="td28 nowrap">'.$lang['setting_credits_policy_cycletype'].'</th><th class="td28 nowrap">'.$lang['setting_credits_policy_rewardnum'].'</th>';
		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				echo "<th class=\"td25\" id=\"policy$i\" ".($_G['setting']['extcredits'][$i] ? '' : 'disabled')." valign=\"top\">".$_G['setting']['extcredits'][$i]['title']."</th>";
			}
			$lowerlimit['extcredits'.$i] = $_G['setting']['creditspolicy']['lowerlimit'][$i];
		}
		echo '<th class="td25">&nbsp;</th></tr>';
		array_push($rules, $lowerlimit);

		foreach($rules as $rid => $rule) {
			$tdarr = array($rule['rulename'], $rule['rid'] ? $lang['setting_credits_policy_cycletype_'.$rule['cycletype']] : 'N/A', $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A');
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					array_push($tdarr, '<input name="credit['.$rule['rid'].']['.$i.']" class="txt" value="'.$rule['extcredits'.$i].'" />');
				}
			}
			$opstr = '<a href="'.ADMINSCRIPT.'?action=credits&operation=edit&rid='.$rule['rid'].'" title="" class="act">'.$lang['edit'].'</a>';
			array_push($tdarr, $opstr);
			showtablerow('', array_fill(0, count($_G['setting']['extcredits']) + 4, 'class="td25"'), $tdarr);
		}
		showtablerow('', 'class="lineheight" colspan="9"', $lang['setting_credits_policy_comment']);
		showsubmit('rulesubmit');
		showtablefooter();
		showformfooter();
	} else {
		foreach($_G['gp_credit'] as $rid => $credits) {
			$rule = array();
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					$rule['extcredits'.$i] = $credits[$i];
				}
			}
			DB::update('common_credit_rule', $rule, array('rid' => $rid));
		}
		$lowerlimit['creditspolicy']['lowerlimit'] = array();
		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				$lowerlimit['creditspolicy']['lowerlimit'][$i] = (float)$_G['gp_credit'][0][$i];
			}
		}
		$setting = array(
			'skey' => 'creditspolicy',
			'svalue' => addslashes(serialize($lowerlimit['creditspolicy']))
		);
		DB::insert('common_setting', $setting, 0, true);
		updatecache(array('setting', 'creditrule'));
		cpmsg('credits_update_succeed', 'action=credits&operation=list&anchor=policytable', 'succeed');
	}
} elseif($operation == 'edit') {

	$rid = intval($_G['gp_rid']);
	$fid = intval($_G['gp_fid']);
	if($rid) {
		$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE rid='$rid'");
		$ruleinfo = DB::fetch($query);
		if($fid) {
			$query = DB::query("SELECT f.name AS forumname, ff.creditspolicy
				FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid
				WHERE f.fid='$fid'");
			$policy = DB::fetch($query);
			$forumname = $policy['forumname'];
			$policy = $policy ? unserialize($policy['creditspolicy']) : array();
			if(isset($policy[$ruleinfo['action']])) {
				$ruleinfo = $policy[$ruleinfo['action']];
			}
		}
	}
	if(!submitcheck('rulesubmit')) {
		if(!$rid) {
			$ruleinfo['rulename'] = $lang['credits_edit_lowerlimit'];
		}
		if(!$fid) {
			shownav('global', 'credits_edit');
			showsubmenu("$lang[credits_edit] - $ruleinfo[rulename]");
		} else {
			shownav('forum', 'forums_edit');
			showsubmenu("$forumname - $lang[credits_edit] - $ruleinfo[rulename]");
		}
		showformheader("credits&operation=edit&rid=$rid&".($fid ? "fid=$fid" : ''));

		showtableheader('', 'nobottom', 'id="edit"');
		if($rid) {
			showsetting('setting_credits_policy_cycletype', array('rule[cycletype]', array(
				array(0, $lang['setting_credits_policy_cycletype_0'], array('cycletimetd' => 'none', 'rewardnumtd' => 'none')),
				array(1, $lang['setting_credits_policy_cycletype_1'], array('cycletimetd' => 'none', 'rewardnumtd' => '')),
				array(2, $lang['setting_credits_policy_cycletype_2'], array('cycletimetd' => '', 'rewardnumtd' => '')),
				array(3, $lang['setting_credits_policy_cycletype_3'], array('cycletimetd' => '', 'rewardnumtd' => '')),
				array(4, $lang['setting_credits_policy_cycletype_4'], array('cycletimetd' => 'none', 'rewardnumtd' => '')),
			)), $ruleinfo['cycletype'], 'mradio');
			showtagheader('tbody', 'cycletimetd', in_array($ruleinfo['cycletype'], array(2, 3)), 'sub');
			showsetting('credits_edit_cycletime', 'rule[cycletime]', $ruleinfo['cycletime'], 'text');
			showtagfooter('tbody');
			showtagheader('tbody', 'rewardnumtd',  in_array($ruleinfo['cycletype'], array(1, 2, 3, 4)), 'sub');
			showsetting('credits_edit_rewardnum', 'rule[rewardnum]', $ruleinfo['rewardnum'], 'text');
			showtagfooter('tbody');
		}
		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				if($rid) {
					showsetting("extcredits{$i}(".$_G['setting']['extcredits'][$i]['title'].')', "rule[extcredits{$i}]", $ruleinfo['extcredits'.$i], 'text');
				} else {
					showsetting("extcredits{$i}(".$_G['setting']['extcredits'][$i]['title'].')', "rule[extcredits{$i}]", $_G['setting']['creditspolicy']['lowerlimit'][$i], 'text');
				}
			}
		}
		showsubmit('rulesubmit');
		showtablefooter();
		showformfooter();
	} else {
		$rid = $_G['gp_rid'];
		$rule = $_G['gp_rule'];
		if($rid) {
			if(!$rule['cycletype']) {
				$rule['cycletime'] = 0;
				$rule['rewardnum'] = 1;
			}
			foreach($rule as $key => $val) {
				$rule[$key] = (float)$val;
			}
			$havecredit = false;
			for($i = 1; $i <= 8; $i++) {
				if(!$_G['setting']['extcredits'][$i]) {
					$rule['extcredits'.$i] = 0;
				} elseif($fid && $rule['extcredits'.$i]) {
					$havecredit = true;
				}
			}
			if($fid) {
				$fids = $ruleinfo['fids'] ? explode(',', $ruleinfo['fids']) : array();
				if($havecredit) {
					$rule['rid'] = $rid;
					$rule['fid'] = $fid;
					$rule['rulename'] = $ruleinfo['rulename'];
					$rule['action'] = $ruleinfo['action'];
					$policy[$ruleinfo['action']] = $rule;
					if(!in_array($fid, $fids)) {
						$fids[] = $fid;
					}
				} else {
					if($rule['cycletype'] != 0 && ($rule['cycletype'] == 4 && !$rule['rewardnum'])) {
						require_once DISCUZ_ROOT.'./source/class/class_credit.php';
						credit::deletelogbyfid($rid, $fid);
					}
					unset($policy[$ruleinfo['action']]);
					if(in_array($fid, $fids)) {
						unset($fids[$fid]);
					}
				}

				DB::update('forum_forumfield', array('creditspolicy' => addslashes(serialize($policy))), array('fid' => $fid));
				DB::update('common_credit_rule', array('fids' => implode(',', $fids)), array('rid' => $rid));
				cpmsg('credits_update_succeed', 'action=forums&operation=edit&anchor=credits&fid='.$fid, 'succeed');
			} else {
				DB::update('common_credit_rule', $rule, array('rid' => $rid));
			}
			updatecache('creditrule');
		} else {
			$lowerlimit['creditspolicy']['lowerlimit'] = array();
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					$lowerlimit['creditspolicy']['lowerlimit'][$i] = (float)$rule['extcredits'.$i];
				}
			}
			$setting = array(
				'skey' => 'creditspolicy',
				'svalue' => addslashes(serialize($lowerlimit['creditspolicy']))
			);
			DB::insert('common_setting', $setting, 0, true);
			updatecache(array('setting', 'creditrule'));
		}
		cpmsg('credits_update_succeed', 'action=credits&operation=list&anchor=policytable', 'succeed');
	}
}
?>