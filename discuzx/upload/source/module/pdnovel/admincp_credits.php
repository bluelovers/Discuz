<?php

if($do == 'show') {

	$rules = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE action LIKE 'pdnovel%';");
	while($value = DB::fetch($query)) {
		$rules[$value['rid']] = $value;
	}
	if(!submitcheck('rulesubmit')) {
		$lowerlimit = array(
			'rid' => 0,
			'rulename' => $lang['credits_edit_lowerlimit'],
		);
		
		showsubmenu('setting_credits');

		showformheader("pdnovel&operation=credits");
		showtableheader('setting_credits_policy', 'nobottom');
		echo '<tr><th class="td28 nowrap">'.$lang['setting_credits_policy_name'].'</th><th class="td28 nowrap">'.$lang['setting_credits_policy_cycletype'].'</th><th class="td28 nowrap">'.$lang['setting_credits_policy_rewardnum'].'</th>';
		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				echo "<th class=\"td25\" id=\"policy$i\" ".($_G['setting']['extcredits'][$i] ? '' : 'disabled')." valign=\"top\">".$_G['setting']['extcredits'][$i]['title']."</th>";
			}
			$lowerlimit['extcredits'.$i] = $_G['setting']['creditspolicy']['lowerlimit'][$i];
		}
		echo '<th class="td25">&nbsp;</th></tr>';

		foreach($rules as $rid => $rule) {
			$tdarr = array($rule['rulename'], $rule['rid'] ? $lang['setting_credits_policy_cycletype_'.$rule['cycletype']] : 'N/A', $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A');
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					array_push($tdarr, '<input name="credit['.$rule['rid'].']['.$i.']" class="txt" value="'.$rule['extcredits'.$i].'" />');
				}
			}
			$opstr = '<a href="'.ADMINSCRIPT.'?action=pdnovel&operation=credits&do=edit&rid='.$rule['rid'].'" title="" class="act">'.$lang['edit'].'</a>';
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
		cpmsg('credits_update_succeed', 'action=pdnovel&operation=credits&do=show', 'succeed');
	}
} elseif($do == 'edit') {

	$rid = intval($_G['gp_rid']);
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE rid='$rid'");
	$globalrule = $ruleinfo = DB::fetch($query);
	
	if(!submitcheck('rulesubmit')) {
	
		shownav('global', 'credits_edit');
		showsubmenu("$lang[credits_edit] - $ruleinfo[rulename]");
		showformheader("pdnovel&operation=credits&do=edit&rid=$rid");

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
					showsetting("extcredits{$i}(".$_G['setting']['extcredits'][$i]['title'].')', "rule[extcredits{$i}]", $ruleinfo['extcredits'.$i], 'text', '', 0, $fid ? '('.$lang['credits_edit_globalrule'].':'.$globalrule['extcredits'.$i].')' : '');
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
			$havecredit = false;
			for($i = 1; $i <= 8; $i++) {
				if(!$_G['setting']['extcredits'][$i]) {
					$rule['extcredits'.$i] = 0;
				} elseif($fid && is_numeric($rule['extcredits'.$i])) {
					$havecredit = true;
				}
			}
			foreach($rule as $key => $val) {
				$rule[$key] = (float)$val;
			}
			DB::update('common_credit_rule', $rule, array('rid' => $rid));
			updatecache('creditrule');
		}
		cpmsg('credits_update_succeed', 'action=pdnovel&operation=credits&do=show', 'succeed');
	}
}
?>