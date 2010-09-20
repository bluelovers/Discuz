<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: tool.inc.php 4337 2010-09-06 04:48:05Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

@set_time_limit(0);

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

$checkresults = array();
if(!empty($_GET['operation'])) {

	switch($_GET['operation']) {
		case 'updatesubcatid':
			shownav('tool', 'tool_updatesubcatid');
			cpmsg(updatesubcatid(), 'admin.php?action=tool&operation=updatecache');
			break;
		case 'updatememberstats':
			shownav('tool', 'tool_updatememberstats');
			cpmsg(updatememberstats(), 'admin.php?action=tool&operation=updatecache');
			break;
		case 'changeallowner':
			shownav('tool', 'tool_updateallowner');
			cpmsg(changeallowner(), 'admin.php?action=tool&operation=updatecache');
			break;
	}
	if($_GET['operation'] == 'updatecache') {
		$_GET['step'] = max(1, intval($_GET['step']));
		shownav('global', 'tool_updatecache');
		showsubmenu('tool_updatecache', array(
			array('tool_updatecache', 'tool&operation=updatecache', '1'),
			array('menu_tool_updatesubcatid', 'tool&operation=updatesubcatid', '0'),
			array('menu_tool_updatememberstats', 'tool&operation=updatememberstats', '0'),
			array('menu_tool_changeallowner', 'tool&operation=changeallowner', '0'),
			array('menu_tool_updateshopitemnum', 'tool&operation=updateshopitemnum', '0')
		));
		showsubmenusteps('', array(
					array('tool_updatecache_confirm', $_GET['step'] == 1),
					array('tool_updatecache_verify', $_GET['step'] == 2),
					array('tool_updatecache_completed', $_GET['step'] == 3)
					));

		showtips('tools_updatecache_tips');

		if($_GET['step'] == 1) {
			cpmsg("<input type=\"checkbox\" name=\"type[]\" value=\"data\" id=\"datacache\" class=\"checkbox\" checked /><label for=\"datacache\">".cplang('tool_updatecache_data')."</label><input type=\"checkbox\" name=\"type[]\" value=\"tpl\" id=\"tplcache\" class=\"checkbox\" checked /><label for=\"tplcache\">".cplang('tool_updatecache_tpl')."</label>", 'admin.php?action=tool&operation=updatecache&step=2', 'form', '', false);
		} elseif($_GET['step'] == 2) {
			$_REQUEST['type'] = implode('_', (array)$_REQUEST['type']);
			cpmsg(lang('tool_updatecache_waiting'), "admin.php?action=tool&operation=updatecache&step=3&type=$_REQUEST[type]", 'loading', '', false);
		} elseif($_GET['step'] == 3) {
			$_REQUEST['type'] = explode('_', $_REQUEST['type']);
			if(in_array('data', $_REQUEST['type'])) {
				//刪除數據緩存
				$_BCACHE->flush();

				//JS數據緩存
				$tpl = dir(B_ROOT.'./data/cache/js');
				$tpl->handle;
				while($entry = $tpl->read()) {
					if(preg_match("/.*\.html$/", $entry)) {
						@unlink(B_ROOT.'./data/cache/js/'.$entry);
					}
				}
				$tpl->close();
			}
			if(in_array('tpl', $_REQUEST['type'])) {
				//刪除設置緩存

				$tpl = dir(B_ROOT.'./data/cache/tpl');
				$tpl->handle;
				while($entry = $tpl->read()) {
					if(preg_match("/.*\.php$/", $entry)) {
						@unlink(B_ROOT.'./data/cache/tpl/'.$entry);
					}
				}
				$tpl->close();

				$tpl = dir(B_ROOT.'./data/cache/model');
				$tpl->handle;
				while($entry = $tpl->read()) {
					if(preg_match("/.*\.cache\.php$/", $entry)) {
						@unlink(B_ROOT.'./data/cache/model/'.$entry);
					}
				}
				$tpl->close();

				$query = DB::query('SELECT mid, modelname FROM '.tname('models'));
				while ($value = DB::fetch($query)) {
					$state = checkmodel($value['modelname']);
				}

				updatesettingcache();//更新設置緩存
				updatecensorcache();//更新過濾詞緩存
				updatebrandadscache();//更新首頁展示緩存
				updatecategorycache();//更新分類緩存
				updatecronscache();
			}
			cpmsg('update_cache_succeed', '', 'succeed', '', false);
		}
	} elseif($_GET['operation']=='updateshopitemnum') {
		if(submitcheck('updateshopalbumnum')) {
			updateshopitemnum('album');
		} elseif(submitcheck('updateshopgoodnum')) {
			updateshopitemnum('good');
		} elseif(submitcheck('updateshopconsumenum')) {
			updateshopitemnum('consume');
		} elseif(submitcheck('updateshopnoticenum')) {
			updateshopitemnum('notice');
		} elseif(submitcheck('updateshopbrandlinksnum')) {
			updateshopitemnum('brandlinks');
		} elseif(submitcheck('updateshopgroupbuynum')) {
			updateshopitemnum('groupbuy');
		}
		shownav('global', 'tool_updatecache');
		showsubmenu('menu_tool_updateshopitemnum', array(
			array('tool_updatecache', 'tool&operation=updatecache', '0'),
			array('menu_tool_updatesubcatid', 'tool&operation=updatesubcatid', '0'),
			array('menu_tool_updatememberstats', 'tool&operation=updatememberstats', '0'),
			array('menu_tool_changeallowner', 'tool&operation=changeallowner', '0'),
			array('menu_tool_updateshopitemnum', 'tool&operation=updateshopitemnum', '1')
		));
		showtips('tools_updateshopitemnum_tips');
		showformheader('tool&operation=updateshopitemnum');
		showtableheader('');
		showsubtitle(array('', 'counter_amount'));
		showtablerow('', array('class="td21"'), array(
			"$lang[album_updateshopitemnum]:",
			'<input name="album_updateshopitemnum" type="text" class="txt" value="15" /><input type="submit" class="btn" name="updateshopalbumnum" value="'.$lang['submit'].'" />'
		));
		showtablerow('', array('class="td21"'), array(
			"$lang[good_updateshopitemnum]:",
			'<input name="good_updateshopitemnum" type="text" class="txt" value="15" /><input type="submit" class="btn" name="updateshopgoodnum" value="'.$lang['submit'].'" />'
		));
		showtablerow('', array('class="td21"'), array(
			"$lang[consume_updateshopitemnum]:",
			'<input name="consume_updateshopitemnum" type="text" class="txt" value="15" /><input type="submit" class="btn" name="updateshopconsumenum" value="'.$lang['submit'].'" />'
		));
		showtablerow('', array('class="td21"'), array(
			"$lang[notice_updateshopitemnum]:",
			'<input name="notice_updateshopitemnum" type="text" class="txt" value="15" /><input type="submit" class="btn" name="updateshopnoticenum" value="'.$lang['submit'].'" />'
		));
		showtablerow('', array('class="td21"'), array(
			"$lang[brandlinks_updateshopitemnum]:",
			'<input name="brandlinks_updateshopitemnum" type="text" class="txt" value="15" /><input type="submit" class="btn" name="updateshopbrandlinksnum" value="'.$lang['submit'].'" />'
		));
		showtablerow('', array('class="td21"'), array(
			"$lang[groupbuy_updateshopitemnum]:",
			'<input name="groupbuy_updateshopitemnum" type="text" class="txt" value="15" /><input type="submit" class="btn" name="updateshopgroupbuynum" value="'.$lang['submit'].'" />'
		));
		showtablefooter();
		showformfooter();
	} elseif($_GET['operation']=='cron') {
		if($_GET['edit']) {
			if(!empty($_POST['editsubmit'])) {
				$daynew = $_POST['weekdaynew'] != -1 ? -1 : $_POST['daynew'];
				if(strpos($_POST['minutenew'], ',') !== false) {
					$minutenew = explode(',', $_POST['minutenew']);
					foreach($minutenew as $key => $val) {
						$minutenew[$key] = $val = intval($val);
						if($val < 0 || $var > 59) {
							unset($minutenew[$key]);
						}
					}
					$minutenew = array_slice(array_unique($minutenew), 0, 12);
					$minutenew = implode("\t", $minutenew);
				} else {
					$minutenew = intval($_POST['minutenew']);
					$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
				}
				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_POST['filenamenew'])) {
					array_push($checkresults, array('filenamenew'=>lang('crons_filename_illegal')));
				} elseif(!is_readable(B_ROOT.($cronfile = "./source/include/cron/{$_POST['filenamenew']}"))) {
					array_push($checkresults, array('filenamenew'=>lang('crons_filename_invalid')));
				} elseif($_POST['weekdaynew'] == -1 && $daynew == -1 && $_POST['hournew'] == -1 && $minutenew === '') {
					array_push($checkresults, array('weekdaynew'=>lang('crons_time_invalid'), 'daynew'=>lang('crons_time_invalid'), 'hournew'=>lang('crons_time_invalid'), 'minutenew'=>lang('crons_time_invalid')));
				}
				if(!empty($checkresults)) {
					cpmsg('add_error', '', 'error', '', true, true, $checkresults);
				}
				DB::query("UPDATE ".tname("crons")." SET name = '$_POST[name]', filename = '$_POST[filenamenew]', weekday = $_POST[weekdaynew], day = $daynew, hour = $_POST[hournew], minute = $minutenew WHERE cronid = $_POST[cronid]");
				updatecronscache();
				cpmsg('message_success', 'admin.php?action=tool&operation=cron');

			} else {
				$query = DB::query("SELECT * FROM ".tname("crons")." WHERE cronid = '{$_GET[edit]}'");
				$cron = DB::fetch($query);

				//沒有提交數據
				shownav('tool_cron', 'cron_edit');
				showsubmenu('cron_edit');
				showtips('cron_edit_tips');
				showformheader('tool&operation=cron&edit='.$_GET['edit']);
				showhiddenfields(array('valuesubmit' => 'yes'));
				showhiddenfields(array('cronid' => $cron['cronid']));
				showtableheader();
				showsetting('cron_edit_name', 'name', $cron['name'], 'text');

				$weekdaynew = array(
							array('-1', '*'),
							array('0', lang("tool_cron_week_day_0")),
							array('1', lang("tool_cron_week_day_1")),
							array('2', lang("tool_cron_week_day_2")),
							array('3', lang("tool_cron_week_day_3")),
							array('4', lang("tool_cron_week_day_4")),
							array('5', lang("tool_cron_week_day_5")),
							array('6', lang("tool_cron_week_day_6")),
					);
				showsetting('cron_edit_weekdaynew', array('weekdaynew', $weekdaynew), $cron['weekday'], 'select');

				$daynew = array(array('-1', '*'));
				for($i = 1; $i < 32; $i++) {
					$daynew[] = array($i, $i.' '.lang('tool_cron_day'));
				}
				showsetting('cron_edit_daynew', array('daynew', $daynew), $cron['day'], 'select');

				$hournew = array(array(-1, '*'));
				for($i = 0; $i < 24; $i++) {
					$hournew[] = array($i, $i.' '.lang('tool_cron_hour'));
				}
				showsetting('cron_edit_hournew', array('hournew', $hournew), $cron['hour'], 'select');
				showsetting('cron_edit_minutenew', 'minutenew', $cron['minute'], 'text');
				showsetting('cron_edit_filenamenew', 'filenamenew', $cron['filename'], 'text');
				showsubmit('editsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
				showtablefooter();
				showformfooter();
				bind_ajax_form();
			}
		} elseif($_GET['run']) {
				$query = DB::query("SELECT * FROM ".tname("crons")." WHERE cronid = $_GET[run]");
				$cron = DB::fetch($query);
				include_once(B_ROOT.'./source/function/cron.func.php');
				if(!@include B_ROOT.($cronfile = "./source/include/cron/$cron[filename]")) {
					errorlog('CRON', $cron['name']." : Cron script($cronfile) not found or syntax error", 0);
					cpmsg($cron['name']." : Cron script($cronfile) not found or syntax error");
				} else {
					cronnextrun(array($_GET['run']));
					updatecronscache();
					cpmsg('message_success', 'admin.php?action=tool&operation=cron');
				}

		} else {

			if(!empty($_POST['deletesubmit']) && $_POST['multiop'] != 'available' && (!empty($_POST['multiop']) || !empty($_POST['newname']) || !empty($_POST['cronid']))) {
				if(!empty($_POST['newname'])) {
					DB::query("INSERT INTO ".tname("crons")." (`available`,`type`,`name`, `weekday`, `day`, `hour`, `minute`) VALUES ( 0, 'user', '$_POST[newname]', -1, -1, -1, '')");

				}

				if(!empty($_POST['cronid'])) {
					foreach($_POST['cronid'] as $key => $cronid) {
						DB::query("UPDATE ".tname("crons")." SET available = ".($_POST['available'][$cronid]?1:0)." WHERE cronid = $cronid");
					}
					if(!empty($_POST['multiop'])) {
						$cronids = implode(",", $_POST['cronid']);
						DB::query("DELETE FROM ".tname("crons")." WHERE cronid IN (".$cronids.")");
					}

				}
				updatecronscache();
				cpmsg('message_success', 'admin.php?action=tool&operation=cron');
			} elseif(!empty($_POST['deletesubmit']) && !empty($_POST['multiop']) && $_POST['multiop'] == 'available') {
				if(empty($_POST['cronid'])) {
					cpmsg('notselect_item_toupdate', '', 'error', '', true, true);
				}
				$cronids = implode(",", $_POST['cronid']);
				DB::query("UPDATE ".tname("crons")." SET available = '".$_POST['available']."' WHERE cronid IN ($cronids)");
				updatecronscache();
				cpmsg('message_success', 'admin.php?action=tool&operation=cron');
			} else {
					shownav('global', 'tool_cron');
					$query = DB::query("SELECT * FROM ".tname("crons")." ORDER BY cronid ASC");
					while($cron = DB::fetch($query)) {
						if($cron['day'] > 0 && $cron['day'] < 32) {
							$cron['time'] = lang('tool_cron_permonth').$cron['day'].lang('tool_cron_day');
						} elseif($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
							$cron['time'] = lang('tool_cron_perweek').lang('tool_cron_week_day_'.$cron['weekday']);
						} elseif($cron['hour'] >= 0 && $cron['hour'] < 24) {
							$cron['time'] = lang('tool_cron_perday');
						} else {
							$cron['time'] = lang('tool_cron_perhour');
						}

						$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron[hour]).lang('tool_cron_hour') : '';

						if(!in_array($cron['minute'], array(-1, ''))) {
							foreach($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
								$cron['minute'][$k] = sprintf('%02d', $v);
							}
							$cron['minute'] = implode(',', $cron['minute']);
							$cron['time'] .= $cron['minute'].lang('tool_cron_minute');
						} else {
							$cron['time'] .= '00'.lang('tool_cron_minute');
						}
						$cron['lastrun'] = $cron['lastrun'] ? date("Y-m-d H:i", $cron['lastrun']) : 'N/A';
						$cron['nextrun'] = $cron['nextrun'] ? date("Y-m-d H:i", $cron['nextrun']) : 'N/A';
						$cronlist .= showcronrow($cron);
					}
					showcronlist($cronlist);
					showtableheader(lang('operation_form'), 'nobottom');
					showtablerow('', array('width="50px"', ''), array(
								'<input class="radio" type="radio" name="multiop" value="delete"><input type="hidden" name="page" value="'.$_GET['page'].'"><input type="hidden" name="buffurl" value="'.$buffurl.'">',
								lang('mod_delete'),
								));
					showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
								'<input class="radio" type="radio" name="multiop" value="available">',
								lang('cron_available'),
								'&nbsp; <input class="radio" type="radio" name="available" value="1"> '.lang('yes').' &nbsp; &nbsp; <input class="radio" type="radio" name="available" value="0"> '.lang('no')
								));
					showsubmit('deletesubmit', 'submit', '');
					showtablefooter();
					showformfooter();//批量操作的form結束
					bind_ajax_form();
			}
		}
	}
}

?>