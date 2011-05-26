<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_verify.php 22493 2011-05-10 05:58:30Z maruitao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
$operation = $operation ? $operation : '';

$anchor = in_array($_G['gp_anchor'], array('base', 'edit', 'verify', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7', 'authstr', 'refusal', 'pass')) ? $_G['gp_anchor'] : 'base';
$current = array($anchor => 1);
$navmenu = array();

if($operation == 'verify') {
	loadcache('profilesetting');
	$vid = intval($_G['gp_do']);
	$anchor = in_array($_G['gp_anchor'], array('authstr', 'refusal', 'pass', 'add')) ? $_G['gp_anchor'] : 'authstr';
	$current = array($anchor => 1);
	if($anchor != 'pass') {
		$_GET['verifytype'] = $vid;
	} else {
		$_GET['verify'.$vid] = 1;
		$_GET['orderby'] = 'uid';
	}
	require_once libfile('function/profile');
	if(!submitcheck('verifysubmit', true)) {

		$menutitle = $vid ? $_G['setting']['verify'][$vid]['title'] : $lang['members_verify_profile'];
		$navmenu[0] = array('members_verify_nav_authstr', 'verify&operation=verify&anchor=authstr&do='.$vid, $current['authstr']);
		$navmenu[1] = array('members_verify_nav_refusal', 'verify&operation=verify&anchor=refusal&do='.$vid, $current['refusal']);
		if($vid) {
			$navmenu[2] = array('members_verify_nav_pass', 'verify&operation=verify&anchor=pass&do='.$vid, $current['pass']);
			$navmenu[3] = array('members_verify_nav_add', 'verify&operation=add&vid='.$vid, $current['add']);
		}
		$vid ? shownav('user', 'nav_members_verify', $menutitle) : shownav('user', $menutitle);
		showsubmenu($lang['members_verify_verify'].($vid ? '-'.$menutitle : ''), $navmenu);


		$searchlang = array();
		$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
		'members_verify_dateline', 'members_verify_uid', 'members_verify_username', 'members_verify_fieldid');
		foreach ($keys as $key) {
			$searchlang[$key] = cplang($key);
		}

		$orderby = isset($_G['gp_orderby']) ? $_G['gp_orderby'] : '';
		$datehtml = $orderbyhtml = '';
		if($anchor != 'pass') {
			$datehtml = "<tr><th>$searchlang[members_verify_dateline]</th><td colspan=\"3\">
				<input type=\"text\" name=\"dateline1\" value=\"$_GET[dateline1]\" size=\"10\" onclick=\"showcalendar(event, this)\"> ~
				<input type=\"text\" name=\"dateline2\" value=\"$_GET[dateline2]\" size=\"10\" onclick=\"showcalendar(event, this)\"> (YYYY-MM-DD)
				</td></tr>";
			$orderbyhtml = "<select name=\"orderby\"><option value=\"dateline\"$orderby[dateline]>$searchlang[members_verify_dateline]</option>	</select>";
		} else {
			$orderbyhtml = "<select name=\"orderby\"><option value=\"uid\"$orderby[dateline]>$searchlang[members_verify_uid]</option>	</select>";
		}


		$ordersc = isset($_G['gp_ordersc']) ? $_G['gp_ordersc'] : '';
		$perpages = isset($_G['gp_perpages']) ? $_G['gp_perpages'] : '';
		$adminscript = ADMINSCRIPT;
		$expertsearch = $vid ? '&nbsp;<a href="'.ADMINSCRIPT.'?action=members&operation=search&more=1&vid='.$vid.'" target="_top">'.cplang('search_higher').'</a>' : '';
echo <<<EOF
	<form method="get" autocomplete="off" action="$adminscript">
		<div class="block style4">
			<table cellspacing="3" cellpadding="3">
			<tr>
				<th>$searchlang[members_verify_username]* </th><td><input type="text" name="username" value="$_GET[username]"></td>
				<th>$searchlang[members_verify_uid]</th><td><input type="text" name="uid" value="$_GET[uid]"> *$searchlang[likesupport]</td>

			</tr>
			$datehtml
			<tr>
				<th>$searchlang[resultsort]</th>
				<td colspan="3">
					$orderbyhtml
					<select name="ordersc">
					<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
					<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
					</select>
					<select name="perpage">
					<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
					<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
					<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
					<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
					</select>
					<input type="hidden" name="action" value="verify">
					<input type="hidden" name="operation" value="verify">
					<input type="hidden" name="do" value="$vid">
					<input type="hidden" name="anchor" value="$anchor">
					<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">$expertsearch
				</td>
			</tr>
			</table>
		</div>
	</form>
	<iframe id="frame_profile" name="frame_profile" style="display: none"></iframe>
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/javascript">
		function showreason(vid, flag) {
			var reasonobj = $('reason_'+vid);
			if(reasonobj) {
				reasonobj.style.display = flag ? '' : 'none';
			}
			if(!flag && $('verifyitem_' + vid) != null) {
				var checkboxs = $('verifyitem_' + vid).getElementsByTagName('input');
				for(var i in checkboxs) {
					if(checkboxs[i].type == 'checkbox') {
						checkboxs[i].checked = '';
					}
				}
			}
		}
		function mod_setbg(vid, value) {
			$('mod_' + vid + '_row').className = 'mod_' + value;
		}
		function mod_setbg_all(value) {
			checkAll('option', $('cpform'), value);
			var trs = $('cpform').getElementsByTagName('TR');
			for(var i in trs) {
				if(trs[i].id && trs[i].id.substr(0, 4) == 'mod_') {
					trs[i].className = 'mod_' + value;
					showreason(trs[i].getAttribute('verifyid'), value == 'refusal' ? 1 : 0);
				}
			}
		}
		function mod_cancel_all() {
			var inputs = $('cpform').getElementsByTagName('input');
			for(var i in inputs) {
				if(inputs[i].type == 'radio') {
					inputs[i].checked = '';
				}
			}
			var trs = $('cpform').getElementsByTagName('TR');
			for(var i in trs) {
				if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row$/)) {
					trs[i].className = "mod_cancel";
					showreason(trs[i].getAttribute('verifyid'), 0)
				}
			}
		}
		function singleverify(vid) {
			var formobj = $('cpform');
			var oldaction = formobj.action;
			formobj.action = oldaction+'&frame=no&singleverify='+vid;
			formobj.target = "frame_profile";
			formobj.submit();
			formobj.action = oldaction;
			formobj.target = "";
		}

	</script>
EOF;

		$mpurl = ADMINSCRIPT.'?action=verify&operation=verify&anchor='.$anchor;

		if($anchor == 'refusal') {
			$_GET['flag'] = -1;
		} elseif ($anchor == 'authstr') {
			$_GET['flag'] = 0;
		}
		$intkeys = array('uid', 'verifytype', 'flag', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7');
		$strkeys = array();
		$randkeys = array();
		$likekeys = array('username');
		$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 'v.');
		foreach($likekeys as $k) {
			$_GET[$k] = htmlspecialchars($_GET[$k]);
		}
		$mpurl .= '&'.implode('&', $results['urls']);
		$wherearr = $results['wherearr'];
		if($_G['gp_dateline1']){
			$wherearr[] = "v.dateline >= '".strtotime($_G['gp_dateline1'])."'";
			$mpurl .= '&starttime='.$_G['gp_dateline1'];
		}
		if($_G['gp_dateline2']){
			$wherearr[] = "v.dateline <= '".strtotime($_G['gp_dateline2'])."'";
			$mpurl .= '&endtime='.$_G['gp_dateline2'];
		}

		$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

		$orders = getorders(array('dateline', 'uid'), 'dateline', 'v.');
		$ordersql = $orders['sql'];
		if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
		$orderby = array($_GET['orderby']=>' selected');
		$ordersc = array($_GET['ordersc']=>' selected');

		$perpage = empty($_G['gp_perpage']) ? 0 : intval($_G['gp_perpage']);
		if(!in_array($perpage, array(10, 20,50,100))) $perpage = 10;
		$perpages = array($perpage=>' selected');
		$mpurl .= '&perpage='.$perpage;

		$page = empty($_G['gp_page'])?1:intval($_G['gp_page']);
		if($page<1) $page = 1;
		$start = ($page-1)*$perpage;

		$multipage = '';

		showformheader("verify&operation=verify&do=".$vid.'&anchor='.$anchor);
		echo "<script>disallowfloat = '{$_G[setting][disallowfloat]}';</script><input type=\"hidden\" name=\"verifysubmit\" value=\"trun\" />";
		showtableheader('members_verify_manage', 'fixpadding');

		if($anchor != 'pass') {
			$cssarr = array('width="90"', 'width="120"', 'width="120"', '');
			$titlearr = array($lang['members_verify_username'], $lang['members_verify_type'], $lang['members_verify_dateline'], $lang['members_verify_info']);
			showtablerow('class="header"', $cssarr, $titlearr);
			$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_verify_info')." v WHERE $wheresql"), 0);
		} else {
			$cssarr = array('width="80"', 'width="90"', 'width="120"', '');
			$titlearr = array('', $lang['members_verify_username'], $lang['members_verify_type'], $lang['members_verify_info']);
			showtablerow('class="header"', $cssarr, $titlearr);
			$wheresql = (!empty($_G['gp_username']) ? str_replace('v.username', 'm.username', $wheresql) : $wheresql) . ' AND v.uid=m.uid ';
			$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_verify')." v, ".DB::table('common_member')." m WHERE $wheresql"), 0);
		}
		if($count) {

			if($anchor != 'pass') {
				$query = DB::query("SELECT * FROM ".DB::table('common_member_verify_info')." v WHERE $wheresql $ordersql LIMIT $start, $perpage");
			} else {
				$query = DB::query("SELECT v.*, f.*, m.username FROM ".DB::table('common_member_verify')." v LEFT JOIN ".DB::table('common_member_profile')." f USING(uid) LEFT JOIN ".DB::table('common_member')." m USING(uid) WHERE $wheresql $ordersql LIMIT $start, $perpage");
			}
			while($value = DB::fetch($query)) {
				$value['username'] = '<a href="home.php?mod=space&uid='.$value['uid'].'&do=profile" target="_blank">'.avatar($value['uid'], "small").'<br/>'.$value['username'].'</a>';
				if($anchor != 'pass') {
					$fields = $anchor != 'pass' ? unserialize($value['field']) : $_G['setting']['verify'][$vid]['field'];
					$verifytype = $value['verifytype'] ? $_G['setting']['verify'][$value['verifytype']]['title'] : $lang['members_verify_profile'];
					$fieldstr = '<table width="96%">';
					$i = 0;
					$fieldstr .= '<tr>'.($anchor == 'authstr' ? '<td width="26">'.$lang[members_verify_refusal].'</td>' : '').'<td width="100">'.$lang['members_verify_fieldid'].'</td><td>'.$lang['members_verify_newvalue'].'</td></tr><tbody id="verifyitem_'.$value[vid].'">';
					$i++;
					foreach($fields as $key => $field) {
						if(in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
							continue;
						}
						if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
							$field = '<a href="'.(getglobal('setting/attachurl').'./profile/'.$field).'" target="_blank"><img src="'.(getglobal('setting/attachurl').'./profile/'.$field).'" class="verifyimg" /></a>';
						} elseif(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
							$field = profile_show($key, $fields);
						}
						$fieldstr .= '<tr>'.($anchor == 'authstr' ? '<td><input type="checkbox" name="refusal['.$value['vid'].']['.$key.']" value="'.$key.'" onclick="$(\'refusal'.$value['vid'].'\').click();" /></td>' : '').'<td>'.$_G['cache']['profilesetting'][$key]['title'].':</td><td>'.$field.'</td></tr>';
						$i++;
					}
					$opstr = "";

					if($anchor == 'authstr') {
						$opstr .= "<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');showreason($value[vid], 0);\">$lang[validate]</label>&nbsp;<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"refusal\" id=\"refusal$value[vid]\" onclick=\"mod_setbg($value[vid], 'refusal');showreason($value[vid], 1);\">$lang[members_verify_refusal]</label>";
					} elseif ($anchor == 'refusal') {
						$opstr .= "<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');\">$lang[validate]</label>";
					}

					$fieldstr .= "</tbody><tr><td colspan=\"5\">$opstr &nbsp;<span id=\"reason_$value[vid]\" style=\"display: none;\">$lang[moderate_reasonpm]&nbsp; <input type=\"text\" class=\"txt\" name=\"reason[$value[vid]]\" style=\"margin: 0px;\"></span>&nbsp;<input type=\"button\" value=\"$lang[moderate]\" name=\"singleverifysubmit\" class=\"btn\" onclick=\"singleverify($value[vid]);\"></td></tr></table>";

					$valuearr = array($value['username'], $verifytype, dgmdate($value['dateline'], 'dt'), $fieldstr);
					showtablerow("id=\"mod_$value[vid]_row\" verifyid=\"$value[vid]\"", $cssarr, $valuearr);
				} else {
					$fields = $_G['setting']['verify'][$vid]['field'];
					$verifytype = $vid ? $_G['setting']['verify'][$vid]['title'] : $lang['members_verify_profile'];

					$fieldstr = '<table width="96%">';
					$fieldstr .= '<tr><td width="100">'.$lang['members_verify_fieldid'].'</td><td>'.$lang['members_verify_newvalue'].'</td></tr>';
					foreach($fields as $key => $field) {
						if(!in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
							if(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
								$value[$field] = profile_show($key, $value);
							}
							if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
								$value[$field] = '<a href="'.(getglobal('setting/attachurl').'./profile/'.$value[$field]).'" target="_blank"><img src="'.(getglobal('setting/attachurl').'./profile/'.$value[$field]).'" class="verifyimg" /></a>';
							}
							$fieldstr .= '<tr><td width="100">'.$_G['cache']['profilesetting'][$key]['title'].':</td><td>'.$value[$field].'</td></tr>';
						}
					}
					$fieldstr .= "</table>";
					$opstr = "<ul class=\"nofloat\"><li><label><input class=\"radio\" type=\"radio\" name=\"verify[$value[uid]]\" value=\"export\" onclick=\"mod_setbg($value[uid], 'validate');\">$lang[export]</label></li><li><label><input class=\"radio\" type=\"radio\" name=\"verify[$value[uid]]\" value=\"refusal\" onclick=\"mod_setbg($value[uid], 'refusal');\">$lang[members_verify_refusal]</label></li></ul>";
					$valuearr = array($opstr, $value['username'], $verifytype, $fieldstr);
					showtablerow("id=\"mod_$value[uid]_row\"", $cssarr, $valuearr);
				}
			}
			$multipage = multi($count, $perpage, $page, ADMINSCRIPT."?action=verify&operation=verify&do=$vid&anchor=$anchor");
			if($anchor != 'pass') {
				showsubmit('batchverifysubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a>'. ($anchor == 'authstr' ? ' &nbsp;<a href="#all" onclick="mod_setbg_all(\'refusal\')">'.cplang('moderate_refusal_all').'</a>' : '').' &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_cancel_all').'</a>', $multipage, false);
			} else {
				showsubmit('batchverifysubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'export\')">'.cplang('moderate_export_all').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'refusal\')">'.cplang('moderate_refusal_all').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_cancel_all').'</a> &nbsp;|&nbsp;<a href="admin.php?action=verify&operation=verify&do=1&anchor=pass&verifysubmit=true">'.cplang('moderate_export_getall').'</a>', $multipage, false);
			}
		} else {
			showtablerow('', 'colspan="'.count($cssarr).'"', '<strong>'.cplang('moderate_nodata').'</strong>');
		}

		showtablefooter();
		showformfooter();

	} else {

		if($anchor == 'pass') {
			$verifyuids = array();
			$note_values = array(
					'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$_G['setting']['verify'][$vid]['title'].'</a>' : ''
				);
			foreach($_G['gp_verify'] as $uid => $type) {
				if($type == 'export') {
					$verifyuids['export'][] = $uid;
				} elseif($type == 'refusal') {
					$verifyuids['refusal'][] = $uid;
					notification_add($uid, 'verify', 'profile_verify_pass_refusal', $note_values, 1);
				}
			}
			if(is_array($verifyuids['refusal']) && !empty($verifyuids['refusal'])) {
				DB::update('common_member_verify', array("verify$vid" => '0'), "uid IN(".dimplode($verifyuids['refusal']).")");
			}
			if(is_array($verifyuids['export']) && !empty($verifyuids['export']) || empty($verifyuids['refusal'])) {
				if(is_array($verifyuids['export']) && !empty($verifyuids['export'])) {
					$wherearr[] = ' v.uid IN('.dimplode($verifyuids['export']).')';
				}
				$wherearr[] = "v.verify$vid = '1'";
				$wheresql = implode(' AND ', $wherearr);
				$fields = $_G['setting']['verify'][$vid]['field'];
				$fields = array_reverse($fields);
				$fields['username'] = 'username';
				$fields = array_reverse($fields);
				$title = $verifylist = '';
				$showtitle = true;
				$query = DB::query("SELECT m.username, v.*, f.* FROM ".DB::table('common_member_verify')." v LEFT JOIN ".DB::table('common_member_profile')." f USING(uid) LEFT JOIN ".DB::table('common_member')." m USING(uid) WHERE $wheresql");
				while($value = DB::fetch($query)) {
					$str = $common = '';
					foreach($fields as $key => $field) {
						if(in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
							continue;
						}
						if($showtitle) {
							$title .= $common.($key == 'username' ? $lang['username'] : $_G['cache']['profilesetting'][$key]['title']);
						}
						if(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
							$value[$field] = profile_show($key, $value);
						}
						$str .= $common.$value[$field];
						$common = "\t";
					}
					$verifylist .= $str."\n";
					$showtitle = false;
				}
				$verifylist = $title."\n".$verifylist;
				$filename = date('Ymd', TIMESTAMP).'.xls';

				define('FOOTERDISABLED', true);
				ob_end_clean();
				header("Content-type:application/vnd.ms-excel");
				header('Content-Encoding: none');
				header('Content-Disposition: attachment; filename='.$filename);
				header('Pragma: no-cache');
				header('Expires: 0');
				echo $verifylist;
				exit();
			} else {
				cpmsg('members_verify_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor=pass', 'succeed');
			}
		} else {
			$vids = array();
			$single = intval($_G['gp_singleverify']);
			$verifyflag = empty($_G['gp_verify']) ? false : true;
			if($verifyflag) {
				if($single) {
					$_G['gp_verify'] = array($single => $_G['gp_verify'][$single]);
				}
				foreach($_G['gp_verify'] as $id => $type) {
					$vids[] = $id;
				}

				$verifysetting = $_G['setting']['verify'];
				$verify = $refusal = array();
				$query = DB::query("SELECT * FROM ".DB::table('common_member_verify_info')." WHERE  vid IN(".dimplode($vids).")");
				while($value = DB::fetch($query)) {
					if(in_array($_G['gp_verify'][$value['vid']], array('refusal', 'validate'))) {
						$fields = unserialize($value['field']);
						$verifysetting = $_G['setting']['verify'][$value['verifytype']];

						if($_G['gp_verify'][$value['vid']] == 'refusal') {
							$refusalfields = !empty($_G['gp_refusal'][$value['vid']]) ? $_G['gp_refusal'][$value['vid']] : $verifysetting['field'];
							$fieldtitle = $common = '';
							$deleteverifyimg = false;
							foreach($refusalfields as $key => $field) {
								$fieldtitle .= $common.$_G['cache']['profilesetting'][$field]['title'];
								$common = ',';
								if($_G['cache']['profilesetting'][$field]['formtype'] == 'file') {
									$deleteverifyimg = true;
									@unlink(getglobal('setting/attachdir').'./profile/'.$fields[$key]);
									$fields[$field] = '';
								}
							}
							if($deleteverifyimg) {
								$newfields = daddslashes(serialize($fields));
								DB::update('common_member_verify_info', array('field' => $newfields), array('vid' => $value['vid']));
							}
							if($value['verifytype']) {
								$verify["verify"]['-1'][] = $value['uid'];
							}
							$verify['flag'][] = $value['vid'];
							$note_values = array(
									'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$verifysetting['title'].'</a>' : '',
									'profile' => $fieldtitle,
									'reason' => $_G['gp_reason'][$value['vid']],
								);
							$note_lang = 'profile_verify_error';
						} else {
							$profile = daddslashes($fields);
							DB::update('common_member_profile', $profile, array('uid' => intval($value['uid'])));
							$verify['delete'][] = $value['vid'];
							if($value['verifytype']) {
								$verify["verify"]['1'][] = $value['uid'];
							}
							$note_values = array(
									'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$verifysetting['title'].'</a>' : ''
								);
							$note_lang = 'profile_verify_pass';
						}
						notification_add($value['uid'], 'verify', $note_lang, $note_values, 1);
					}
				}
				if($vid && !empty($verify["verify"])) {
					foreach($verify["verify"] as $flag => $uids) {
						$flag = intval($flag);
						DB::update('common_member_verify', array("verify$vid" => $flag), "uid IN(".dimplode($uids).")");
					}
				}

				if(!empty($verify['delete'])) {
					DB::delete('common_member_verify_info', "vid IN(".dimplode($verify['delete']).")");
				}

				if(!empty($verify['flag'])) {
					DB::update('common_member_verify_info', array('flag' => '-1'), "vid IN(".dimplode($verify['flag']).")");
				}
			}
			if($single && $_G['gp_frame'] == 'no') {
				echo "<script type=\"text/javascript\">var trObj = parent.$('mod_{$single}_row');trObj.parentNode.removeChild(trObj);</script>";
			} else {
				cpmsg('members_verify_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor='.$_G['gp_anchor'], 'succeed');
			}
		}
	}
} elseif($operation == 'add') {

	$vid = intval($_G['gp_vid']);
	if(!submitcheck('addverifysubmit') || $vid < 0 || $vid > 7) {
		$navmenu[0] = array('members_verify_nav_authstr', 'verify&operation=verify&anchor=authstr&do='.$vid, 0);
		$navmenu[1] = array('members_verify_nav_refusal', 'verify&operation=verify&anchor=refusal&do='.$vid, 0);
		$navmenu[2] = array('members_verify_nav_pass', 'verify&operation=verify&anchor=pass&do='.$vid, 0);
		$navmenu[3] = array('members_verify_nav_add', 'verify&operation=add&vid='.$vid, 1);
		$vid ? shownav('user', 'nav_members_verify', $_G['setting']['verify'][$vid]['title']) : shownav('user', $_G['setting']['verify'][$vid]['title']);
		showsubmenu($lang['members_verify_add'].'-'.$_G['setting']['verify'][$vid]['title'], $navmenu);
		showformheader("verify&operation=add&vid=$vid", 'enctype');
		showtableheader();
		showsetting('members_verify_userlist', 'users', $member['users'], 'textarea');
		showsubmit('addverifysubmit');
		showtablefooter();
		showformfooter();
	} else {
		$userlist = daddslashes(explode("\r\n", dstripslashes($_G['gp_users'])));
		$query = DB::query("SELECT m.username, m.uid as muid, v.* FROM ".DB::table('common_member')." m	LEFT JOIN ".DB::table('common_member_verify')." v USING(uid) WHERE m.username IN(".dimplode($userlist).")");
		$insert = array();
		$haveuser = false;
		while($member = DB::fetch($query)) {
			if($member['uid']) {
				DB::update('common_member_verify', array("verify$vid" => 1), array('uid' => $member['muid']));
			} else {
				$insert[] = "('$member[muid]', '1')";
			}
			$haveuser = true;
		}
		if(!empty($insert)) {
			DB::query("INSERT INTO ".DB::table('common_member_verify')." (`uid`, `verify$vid`) VALUES ".implode(',', $insert));
		}
		if($haveuser) {
			cpmsg('members_verify_add_user_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor=pass', 'succeed');
		} else {
			cpmsg_error('members_verify_add_user_failure', 'action=verify&operation=add&vid='.$vid);
		}
	}

} elseif($operation == 'edit') {

	shownav('user', 'nav_members_verify');
	$vid = $_G['gp_vid'] < 8 ? intval($_G['gp_vid']) : 0;
	$verifyarr = $_G['setting']['verify'][$vid];
	if(!submitcheck('verifysubmit')) {
		if($vid == 7) {
			showtips('members_verify_setting_tips');
		}
		showformheader("verify&operation=edit&vid=$vid", 'enctype');
		showtableheader();
		$readonly = $vid == 6 || $vid == 7 ? 'readonly' : '';
		showsetting('members_verify_title', "verify[title]", $verifyarr['title'], 'text', $readonly);
		showsetting('members_verify_enable', "verify[available]", $verifyarr['available'], 'radio');
		$verificonhtml = '';
		if($verifyarr['icon']) {
			$verificonhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon['.$vid.']" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$verifyarr['icon'].'" />';
		}
		if($verifyarr['icon']) {
			$icon_url = parse_url($verifyarr['icon']);
		}
		showsetting('members_verify_icon', 'iconnew', (!$icon_url['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $verifyarr['icon']) : $verifyarr['icon']), 'filetext', '', 0, $verificonhtml);
		showsetting('members_verify_showicon', "verify[showicon]", $verifyarr['showicon'], 'radio');
		if($vid == 6) {
			showsetting('members_verify_view_real_name', "verify[viewrealname]", $verifyarr['viewrealname'], 'radio');
		} elseif($vid == 7) {
			showsetting('members_verify_view_video_photo', "verify[viewvideophoto]", $verifyarr['viewvideophoto'], 'radio');
		}
		if($vid != 7) {
			$varname = array('verify[field]', array(), 'isfloat');
			$query = DB::query("SELECT title, fieldid, available FROM ".DB::table('common_member_profile_setting')." WHERE available='1' ORDER BY available DESC, displayorder");
			while($value = DB::fetch($query)) {
				if(!in_array($value['fieldid'], array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
					$varname[1][] = array($value['fieldid'], $value['title'], $value['fieldid']);
				}
			}

			showsetting('members_verify_setting_field', $varname, $verifyarr['field'], 'omcheckbox');
		}
		showsubmit('verifysubmit');
		showtablefooter();
		showformfooter();
	} else {
		foreach( $_G['setting']['verify'] AS $key => $value) {
			$_G['setting']['verify'][$key]['icon'] = str_replace($_G['setting']['attachurl'].'common/', '', $value['icon']);
		}
		$verifynew = getgpc('verify');
		if($verifynew['available'] == 1 && !trim($verifynew['title'])) {
			cpmsg('members_verify_update_title_error', '', 'error');
		}
		if($_FILES['iconnew']) {
			$data = array('extid' => "$vid");
			$iconnew = upload_icon_banner($data, $_FILES['iconnew'], 'verify_icon');
		} else {
			$iconnew = $_G['gp_iconnew'];
		}
		$verifynew['icon'] = $iconnew;
		if($_G['gp_deleteicon']) {
			$valueparse = parse_url($verifyarr['icon']);
			if(!isset($valueparse['host']) && preg_match('/^'.preg_quote($_G['setting']['attachurl'].'common/', '/').'/', $verifyarr['icon'])) {
				@unlink($verifyarr['icon']);
			}
			$verifynew['icon'] = '';
		}
		if(!empty($verifynew['field']['residecity'])) {
			$verifynew['field']['resideprovince'] = 'resideprovince';
			$verifynew['field']['residedist'] = 'residedist';
			$verifynew['field']['residecommunity'] = 'residecommunity';
		}
		if(!empty($verifynew['field']['birthday'])) {
			$verifynew['field']['birthyear'] = 'birthyear';
			$verifynew['field']['birthmonth'] = 'birthmonth';
		}
		if(!empty($verifynew['field']['birthcity'])) {
			$verifynew['field']['birthprovince'] = 'birthprovince';
			$verifynew['field']['birthdist'] = 'birthdist';
			$verifynew['field']['birthcommunity'] = 'birthcommunity';
		}
		$_G['setting']['verify'][$vid] = $verifynew;
		$_G['setting']['verify']['enabled'] = false;
		for($i = 1; $i < 8; $i++) {
			if($_G['setting']['verify'][$i]['available'] && !$_G['setting']['verify']['enabled']) {
				$_G['setting']['verify']['enabled'] = true;
			}
			if($_G['setting']['verify'][$i]['icon']) {
				$icon_url = parse_url($_G['setting']['verify'][$i]['icon']);
			}
			$_G['setting']['verify'][$i]['icon'] = !$icon_url['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $_G['setting']['verify'][$i]['icon']) : $_G['setting']['verify'][$i]['icon'] ;
		}
		$setting = array(
			'skey' => 'verify',
			'svalue' => addslashes(serialize($_G['setting']['verify']))
		);

		DB::insert('common_setting', $setting, 0, true);
		if(isset($verifynew['viewrealname']) && !$verifynew['viewrealname']) {
			DB::update('common_member_profile_setting', array('showinthread' => 0), array('fieldid' => 'realname'));
			$result = DB::fetch_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='customauthorinfo'");
			$custominfo = unserialize($result['svalue']);
			if(isset($custominfo[0]['field_realname'])) {
				unset($custominfo[0]['field_realname']);
				$setting = array(
					'skey' => 'customauthorinfo',
					'svalue' => addslashes(serialize($custominfo))
				);
				DB::insert('common_setting', $setting, 0, true);
				updatecache(array('custominfo'));
			}
		}
		updatecache(array('setting'));
		cpmsg('members_verify_update_succeed', 'action=verify', 'succeed');
	}


} else {

	$current = array($anchor => 1);
	shownav('user', 'nav_members_verify');

	showsubmenu('members_verify_setting');

	if(!submitcheck('verifysubmit')) {
		showtips('members_verify_setting_tips');
		showformheader("verify");
		showtableheader('members_verify_setting', 'fixpadding');
		showsubtitle(array('members_verify_available', 'members_verify_id', 'members_verify_title', ''), 'header');
		for($i = 1; $i < 8; $i++) {
			$readonly = $i == 6 || $i == 7 ? true : false;
			showtablerow('', array('class="td25"', '', '', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[verify][$i][available]\" value=\"1\" ".($_G['setting']['verify'][$i]['available'] ? 'checked' : '')." />",
				'verify'.$i,
				($readonly ? $_G['setting']['verify'][$i]['title']."<input type=\"hidden\" name=\"settingnew[verify][$i][title]\" value=\"{$_G['setting']['verify'][$i]['title']}\" readonly>&nbsp;":"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[verify][$i][title]\" value=\"{$_G['setting']['verify'][$i]['title']}\">").
					($_G['setting']['verify'][$i]['icon'] ? '<img src="'.$_G['setting']['verify'][$i]['icon'].'" />' : ''),
				"<a href=\"".ADMINSCRIPT."?action=verify&operation=edit&anchor=base&vid=$i\">".$lang['edit']."</a>"
			));
		}
		showsubmit('verifysubmit');
		showtablefooter();
		showformfooter();

	} else {
		$settingnew = getgpc('settingnew');
		$enabled = false;
		foreach($settingnew['verify'] as $key => $value) {
			if($value['available'] && !$value['title']) {
				cpmsg('members_verify_title_invalid', '', 'error');
			}
			if($value['available']) {
				$enabled = true;
			}
			$_G['setting']['verify'][$key]['available'] = intval($value['available']);
			$_G['setting']['verify'][$key]['title'] = $value['title'];
		}
		$_G['setting']['verify']['enabled'] = $enabled;
		$setting = array(
			'skey' => 'verify',
			'svalue' => addslashes(serialize($_G['setting']['verify']))
		);
		DB::insert('common_setting', $setting, 0, true);
		updatecache(array('setting'));
		updatemenu('user');
		cpmsg('members_verify_update_succeed', 'action=verify', 'succeed');
	}
}

?>