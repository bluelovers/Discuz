<?php

if(empty($_G['gp_edit']) && empty($_G['gp_run'])) {

	if(!submitcheck('cronssubmit')) {

		shownav('pdnovel', 'misc_cron');
		showsubmenu('nav_misc_cron');
		showtips('misc_cron_tips');
		showformheader('pdnovel&operation=cron');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'name', 'available', 'type', 'time', 'misc_cron_last_run', 'misc_cron_next_run', ''));

		$query = DB::query("SELECT * FROM ".DB::table('common_cron')." WHERE filename LIKE 'cron_pdnovel_%' ORDER BY type DESC");
		while($cron = DB::fetch($query)) {
			$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';

			if($cron['day'] > 0 && $cron['day'] < 32) {
				$cron['time'] = cplang('misc_cron_permonth').$cron['day'].cplang('misc_cron_day');
			} elseif($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
				$cron['time'] = cplang('misc_cron_perweek').cplang('misc_cron_week_day_'.$cron['weekday']);
			} elseif($cron['hour'] >= 0 && $cron['hour'] < 24) {
				$cron['time'] = cplang('misc_cron_perday');
			} else {
				$cron['time'] = cplang('misc_cron_perhour');
			}

			$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron[hour]).cplang('misc_cron_hour') : '';

			if(!in_array($cron['minute'], array(-1, ''))) {
				foreach($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
					$cron['minute'][$k] = sprintf('%02d', $v);
				}
				$cron['minute'] = implode(',', $cron['minute']);
				$cron['time'] .= $cron['minute'].cplang('misc_cron_minute');
			} else {
				$cron['time'] .= '00'.cplang('misc_cron_minute');
			}

			$cron['lastrun'] = $cron['lastrun'] ? dgmdate($cron['lastrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';
			$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_G['setting']['timeoffset'] * 3600 < TIMESTAMP ? 'style="color: #ff0000"' : '';
			$cron['nextrun'] = $cron['nextrun'] ? dgmdate($cron['nextrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';

			showtablerow('', array('class="td25"', 'class="crons"', 'class="td25"', 'class="td25"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$cron[cronid]\" ".($cron['type'] == 'system' ? 'disabled' : '').">",
				"<input type=\"text\" class=\"txt\" name=\"namenew[$cron[cronid]]\" size=\"20\" value=\"$cron[name]\"><br /><b>$cron[filename]</b>",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$cron[cronid]]\" value=\"1\" ".($cron['available'] ? 'checked' : '')." $disabled>",
				cplang($cron['type'] == 'system' ? 'inbuilt' : 'custom'),
				$cron[time],
				$cron[lastrun],
				$cron[nextrun],
				"<a href=\"".ADMINSCRIPT."?action=pdnovel&operation=cron&edit=$cron[cronid]\" class=\"act\">$lang[edit]</a><br />".
				($cron['available'] ? " <a href=\"".ADMINSCRIPT."?action=pdnovel&operation=cron&run=$cron[cronid]\" class=\"act\">$lang[misc_cron_run]</a>" : " <a href=\"###\" class=\"act\" disabled>$lang[misc_cron_run]</a>")
			));
		}

		showtablerow('', array('','colspan="10"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newname" value="" size="20" />'
		));
		showsubmit('cronssubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_G['gp_delete'])) {
			DB::delete('common_cron', "cronid IN ($ids) AND type='user'");
		}

		if(is_array($_G['gp_namenew'])) {
			foreach($_G['gp_namenew'] as $id => $name) {
				$newcron = array(
					'name' => dhtmlspecialchars($_G['gp_namenew'][$id]),
					'available' => $_G['gp_availablenew'][$id]
				);
				if(empty($_G['gp_availablenew'][$id])) {
					$newcron['nextrun'] = '0';
				}
				DB::update('common_cron', $newcron, "cronid='$id'");
			}
		}

		if($newname = trim($_G['gp_newname'])) {
			DB::insert('common_cron', array(
				'name' => dhtmlspecialchars($newname),
				'filename' => 'cron_pdnovel_default.php',
				'type' => 'user',
				'available' => '0',
				'weekday' => '-1',
				'day' => '-1',
				'hour' => '-1',
				'minute' => '',
				'nextrun' => $_G['timestamp'],
			));
		}

		$query = DB::query("SELECT cronid, filename FROM ".DB::table('common_cron'));
		while($cron = DB::fetch($query)) {
			if(!file_exists(DISCUZ_ROOT.'./'.$action.'/cron/'.$cron['filename'])) {
				DB::update('common_cron', array(
					'available' => '0',
					'nextrun' => '0',
				), "cronid='$cron[cronid]'");
			}
		}

		updatecache('setting');
		cpmsg('crons_succeed', 'action=pdnovel&operation=cron', 'succeed');

	}

} else {

	$cronid = empty($_G['gp_run']) ? $_G['gp_edit'] : $_G['gp_run'];
	$cron = DB::fetch_first("SELECT * FROM ".DB::table('common_cron')." WHERE cronid='$cronid'");
	if(!$cron) {
		cpmsg('undefined_action', '', 'error');
	}
	$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
	$cronminute = str_replace("\t", ',', $cron['minute']);
	$cron['minute'] = explode("\t", $cron['minute']);

	if(!empty($_G['gp_edit'])) {

		if(!submitcheck('editsubmit')) {

			shownav('tools', 'misc_cron');
			showsubmenu($lang['misc_cron_edit'].' - '.$cron['name']);
			showtips('misc_cron_edit_tips');

			$weekdayselect = $dayselect = $hourselect = '';

			for($i = 0; $i <= 6; $i++) {
				$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').">".$lang['misc_cron_week_day_'.$i]."</option>";
			}

			for($i = 1; $i <= 31; $i++) {
				$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i $lang[misc_cron_day]</option>";
			}

			for($i = 0; $i <= 23; $i++) {
				$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i $lang[misc_cron_hour]</option>";
			}

			shownav('tools', 'misc_cron');
			showformheader("pdnovel&operation=cron&edit=$cronid");
			showtableheader();
			showsetting('misc_cron_edit_weekday', '', '', "<select name=\"weekdaynew\"><option value=\"-1\">*</option>$weekdayselect</select>");
			showsetting('misc_cron_edit_day', '', '', "<select name=\"daynew\"><option value=\"-1\">*</option>$dayselect</select>");
			showsetting('misc_cron_edit_hour', '', '', "<select name=\"hournew\"><option value=\"-1\">*</option>$hourselect</select>");
			showsetting('misc_cron_edit_minute', 'minutenew', $cronminute, 'text');
			showsetting('pdnovel_cron_edit_filename', 'filenamenew', $cron['filename'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$daynew = $_G['gp_weekdaynew'] != -1 ? -1 : $_G['gp_daynew'];
			if(strpos($_G['gp_minutenew'], ',') !== FALSE) {
				$minutenew = explode(',', $_G['gp_minutenew']);
				foreach($minutenew as $key => $val) {
					$minutenew[$key] = $val = intval($val);
					if($val < 0 || $var > 59) {
						unset($minutenew[$key]);
					}
				}
				$minutenew = array_slice(array_unique($minutenew), 0, 12);
				$minutenew = implode("\t", $minutenew);
			} else {
				$minutenew = intval($_G['gp_minutenew']);
				$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
			}

			if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_G['gp_filenamenew'])) {
				cpmsg('crons_filename_illegal', '', 'error');
			} elseif(!is_readable(DISCUZ_ROOT.($cronfile = "./source/include/cron/{$_G['gp_filenamenew']}"))) {
				cpmsg('crons_filename_invalid', '', 'error', array('cronfile' => $cronfile));
			} elseif($_G['gp_weekdaynew'] == -1 && $daynew == -1 && $_G['gp_hournew'] == -1 && $minutenew === '') {
				cpmsg('crons_time_invalid', '', 'error');
			}

			DB::update('common_cron', array(
				'weekday' => $_G['gp_weekdaynew'],
				'day' => $daynew,
				'hour' => $_G['gp_hournew'],
				'minute' => $minutenew,
				'filename' => trim($_G['gp_filenamenew']),
			), "cronid='$cronid'");

			updatecache('crons');

			discuz_cron::run($cronid);

			cpmsg('crons_succeed', 'action=pdnovel&operation=cron', 'succeed');

		}

	} else {
	
		if(!file_exists(DISCUZ_ROOT.($cronfile = "./".$action."/include/cron/".$cron[filename]))) {
			cpmsg('crons_run_invalid', '', 'error', array('cronfile' => $cronfile));
		} else {
			discuz_cron::run($cron['cronid']);
			cpmsg('crons_run_succeed', 'action=pdnovel&operation=cron', 'succeed');
		}

	}

}

?>