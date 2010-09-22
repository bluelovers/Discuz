<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_poll.php 661 2010-09-14 08:13:20Z yexinhao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'setting') {

	$setting = array();
	$query = DB::query("SELECT * FROM ".DB::table('poll_setting'));
	while($row = DB::fetch($query)) {
		$setting[$row['skey']] = $row['svalue'];
	}

	$customlang = array('poll_vote_error', 'poll_per_once', 'poll_limittime_short', 'poll_vote_succeed');
	if(!submitcheck('settingsubmit')) {
		$avaliable = DB::result_first("SELECT available FROM ".DB::table('common_module')." WHERE identifier='poll'");
		shownav('poll', 'nav_poll_setting');
		showsubmenu('nav_poll_setting');
		showformheader('poll&operation=setting');
		showtableheader('nav_basic', 'fixpadding');
		showsetting('poll_module_available', 'settingnew[available]', $avaliable, 'radio');
		showtablefooter();
		showtableheader('customlang', 'fixpadding');
		foreach($customlang as $langvar) {
			showsetting($langvar, "settingnew[$langvar]", customlang($langvar, null, $setting, 'poll/message'), 'text');
		}
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
	} else {
		if($_G['gp_settingnew']) {
			$available = !empty($_G['gp_settingnew']['available']) ? 1 : 0;
			DB::update('common_module', array('available' => $available), array('identifier' => 'poll'));
			foreach($customlang as $langvar) {
				$langnew = $_G['gp_settingnew'][$langvar];
				DB::query("REPLACE INTO ".DB::table('poll_setting')." (skey, svalue) VALUES ('{$langvar}', '{$langnew}')");
			}
		}

		updatecache('modulelist');
		updatecache('poll_setting', 'poll');
		cpmsg('basic_succeed', 'action=poll&operation=setting', 'succeed');
	}

} else {

	$itemid = !empty($_G['gp_itemid']) ? intval($_G['gp_itemid']) : 0;
	$new = !empty($_G['gp_new']) ? 1 : 0;
	$flashupload =  !empty($_G['gp_flashupload']) ? 1 : 0;

	if(!empty($itemid)) {
		$poll_setting = DB::fetch_first("SELECT i.*, f.*, i.itemid AS itemid FROM ".DB::table('poll_item')." i LEFT JOIN ".DB::table('poll_item_field')." f ON i.itemid = f.itemid WHERE i.itemid = '$itemid' ");
		$itemid = intval($poll_setting['itemid']);
	}

	if($operation == 'create') {

		if(!submitcheck('creatsubmit')) {

			$title = !empty($itemid) ? $poll_setting['title'] : '';

			shownav('poll', 'nav_poll_creat');
			showsubmenu('nav_poll_creat', array(
				array('poll_creat_step_1', "poll&operation=create&itemid=$itemid", 1),
				array('poll_creat_step_2', "poll&operation=manage&itemid=$itemid&new=1", 0),
				array('poll_creat_step_3', "poll&operation=choiceedit&itemid=$itemid&new=1", 0)
			));
			showformheader('poll&operation=create');
			showtableheader('nav_poll_creat', 'fixpadding');
			showsetting('poll_insert_title', 'newpolltitle', $title, 'text');
			showsubmit('creatsubmit', 'next_step');
			showtablefooter();
			showformfooter();

		} else {

			$newpoll = dhtmlspecialchars(trim($_G['gp_newpolltitle']));

			if(!$newpoll) {
				cpmsg('poll_add_invalid', '', 'error');
			}

			DB::insert('poll_item', array('title' => $newpoll, 'username' => $_G['username'], 'dateline' => $_G['timestamp'] ));
			$itemid = DB::insert_id();

			cpmsg('poll_add_succeed', 'action=poll&operation=manage&itemid='.$itemid.'&new=1', 'succeed');

		}

	} elseif($operation == 'list'){

		if(!submitcheck('polllistsubmit')) {

			$liststr = '';
			$query = DB::query("SELECT * FROM ".DB::table('poll_item'));
			while($row = DB::fetch($query)) {
				$checked = $row['available'] == '1' ? 'checked' : '';
				$liststr .= showtablerow('', array('', '', '', ''), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$row[itemid]]\" value=\"$row[itemid]\"  />",
					"<input class=\"txt mtxt\" type=\"text\" name=\"polltitle[$row[itemid]]\" value=\"$row[title]\">",
					$row['username'], date('Y-m-d', $row['dateline']),
					"<input class=\"checkbox\" type=\"checkbox\" name=\"pollstatus[$row[itemid]]\" value=\"$row[itemid]\"  $checked >",
					"<a href=\"".ADMINSCRIPT."?action=poll&operation=manage&itemid=$row[itemid]\">".cplang('poll_set')."</a>
					<a href=\"".ADMINSCRIPT."?action=poll&operation=choiceedit&itemid=$row[itemid]\">".cplang('poll_choice_edit')."</a>
					<a href=\"".ADMINSCRIPT."?action=poll&operation=rlist&itemid=$row[itemid]\">".cplang('menu_poll_state')."</a>
					<a href=\"misc.php?mod=getcode&type=poll&itemid={$row['itemid']}\" target=\"_blank\">".cplang('getcode')."</a>
					<a href=\"".ADMINSCRIPT."?action=poll&operation=analysis&itemid=$row[itemid]\">".cplang('menu_poll_analysis')."</a>
					<a href=\"".ADMINSCRIPT."?action=poll&operation=calling&itemid=$row[itemid]\">".cplang('menu_poll_calling')."</a>
					<a href=\"poll.php?id=$row[itemid]\" target=\"_blank\">".cplang('preview')."</a>"
					), TRUE);
			}

			shownav('poll', 'nav_poll_manage', 'nav_poll_list');
			showsubmenu('nav_poll_manage');
			showformheader('poll&operation=list');
			showtableheader('nav_poll_list', 'fixpadding');
			showsubtitle(array('', 'poll_title', 'poll_founder', 'poll_found_date', 'poll_status', 'poll_manage'));
			echo $liststr;
			showsubmit('polllistsubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {
			$poll_newtitle = $_G['gp_polltitle'];
			$poll_newstatus = $_G['gp_pollstatus'];

			if(!$poll_newtitle) {
				cpmsg('poll_add_invalid', '', 'error');
			}

			foreach($poll_newtitle as $itemid => $newtitle) {
				$status = $poll_newstatus[$itemid] ? '1' : '0';
				DB::update('poll_item', array(
					'title' => $newtitle,
					'available' => $status
				), "itemid='$itemid'");
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::delete('poll_item', "itemid IN ($ids)");
				DB::delete('poll_item_field', "itemid IN ($ids)");
				DB::delete('poll_choice', "itemid IN ($ids)");
				DB::delete('poll_value', "itemid IN ($ids)");
			}

			cpmsg('poll_update_succeed', 'action=poll&operation=list', 'succeed');
		}

	} elseif($operation == 'manage'){

		if(!submitcheck('managesubmit')) {

			if(!$itemid) {
				cpmsg('poll_manage_invalid', '', 'error');
			}

			$mid = DB::result_first("SELECT mid FROM ".DB::table('common_module')." WHERE identifier = 'poll'");
			$poll_setting['resultview_time'] = !empty($poll_setting['resultview_time']) ? $poll_setting['resultview_time'] : 0;

			$templateselect = '<select name="template">';
			$query = DB::query("SELECT * FROM ".DB::table('common_template')." WHERE available  = '1' AND mid = '$mid'");
			while($row = DB::fetch($query)) {
				$selected = $row['templateid'] == $poll_setting['templateid'] ? 'selected="selected"' : '';
				$templateselect .= '<option value="'.$row['templateid'].'" '.$selected.'>'.$row['name'].'</option>';
			}
			$templateselect .= '</select>';

			shownav('poll', 'nav_poll_manage', 'nav_poll_setting');

			if($new) {
				showsubmenu('nav_poll_setting', array(
					array('poll_creat_step_1', 'poll&operation=create&itemid='.$itemid, 0),
					array('poll_creat_step_2', 'poll&operation=manage&itemid='.$itemid.($new ? '&new=1' : ''), 1),
					array('poll_creat_step_3', 'poll&operation=choiceedit&itemid='.$itemid.($new ? '&new=1' : ''), 0)
				));
			} else {
				$poll_setting['title'] = $poll_setting['title'].'(<a href="poll.php?id='.$itemid.'" target="_blank">'.cplang('preview').'</a>)';
				showsubmenu($poll_setting['title'], array(
					array('nav_poll_setting', "poll&operation=manage&itemid=$itemid", 1),
					array('nav_poll_choiceedit', "poll&operation=choiceedit&itemid=$itemid", 0),
					array('menu_poll_state', "poll&operation=rlist&itemid=$itemid", 0),
					array('menu_poll_analysis', "poll&operation=analysis&itemid=$itemid", 0),
					array('nav_poll_calling', "poll&operation=calling&itemid=$itemid", 0),
				));
			}

			showformheader('poll&operation=manage&itemid='.$itemid.($new ? '&new=1' : ''));
			showtableheader('nav_poll_setting', 'fixpadding');
			showsetting('poll_available', 'available', $poll_setting['available'], 'radio');

			showsetting('poll_with_img', array('contenttype', array(
				array(0, cplang('poll_type_common'), array('page_tag' => 'none', 'text_choice_tag' => '')),
				array(1, cplang('poll_type_image'), array('page_tag' => '', 'text_choice_tag' => 'none', 'choice_tag'=>'none'))
				)),$poll_setting['contenttype'], 'mradio');

			showtagheader('tbody', 'text_choice_tag', empty($poll_setting['contenttype']), 'sub');
			showsetting('poll_choice_type', array( 'polltype', array(
				array(0, cplang('poll_type_radio'), array('choice_tag' => 'none')),
				array(1, cplang('poll_type_checkbox'), array('choice_tag' => ''))
			)), $poll_setting['type'], 'mradio');
			showtagheader('tbody', 'choice_tag', empty($poll_setting['contenttype']) && $poll_setting['type'], 'sub');
			showsetting('poll_choice_num', 'choicenum', $poll_setting['choicenum'], 'text');
			showtagfooter('tbody');
			showtagfooter('tbody');

			showtagheader('tbody', 'page_tag', $poll_setting['contenttype'], 'sub');
			showsetting('poll_page', 'pagenum', $poll_setting['numperpage'], 'text');
			showsetting('poll_lazyload', 'lazyload', $poll_setting['lazyload'], 'radio');
			showtagfooter('tbody');

			showsetting('poll_repeattype', array('repeattype', array(
				cplang('poll_repeattype_cookie'),
				cplang('poll_repeattype_username'),
				cplang('poll_repeattype_ip'),
				cplang('poll_repeattype_so'),
			)), $poll_setting['repeattype'], 'binmcheckbox');
			showsetting('poll_limittime', 'limittime', $poll_setting['limittime'], 'text');

			showsetting('poll_result_view_2', array('rvmod', array(
				array(1, cplang('poll_remode_1')),
				array(2, cplang('poll_remode_2'))
			)), $poll_setting['resultview_mod'], 'mradio');
			showsetting('poll_result_view_1', 'rvtime', $poll_setting['resultview_time'], 'radio');
			showsetting('poll_result_error_detail', 'errordetail', $poll_setting['errordetail'], 'radio');

			echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
			$starttime = $poll_setting['starttime'] > 0 ? date('Y-m-d H:i', $poll_setting['starttime']) : '';
			$endtime = $poll_setting['endtime'] > 0 ? date('Y-m-d H:i', $poll_setting['endtime']) : '';
			showsetting('poll_starttime', 'starttime', $starttime, 'calendar', '', 0, '', 1);
			showsetting('poll_endtime', 'endtime', $endtime, 'calendar', '', 0, '', 1);

			showsetting('poll_manage_tpl', '', '', $templateselect);
			showsetting('poll_description', 'description', $poll_setting['description'], 'textarea');
			showsetting('poll_seokeyword', 'seokeyword', $poll_setting['seokeywords'], 'text');
			showsetting('poll_seodesc', 'seodesc', $poll_setting['seodesc'], 'textarea');
			$new && showsubmit('managesubmit', 'next_step');
			!$new&& showsubmit('managesubmit');
			showtablefooter();
			showformfooter();

		} else {

			if(!$itemid) {
				cpmsg('poll_manage_invalid', '', 'error');
			}

			$havefield = DB::result_first("SELECT itemid FROM ".DB::table('poll_item_field')." WHERE itemid = '$itemid' ");
			$data_filed = array(
				'description' => trim($_G['gp_description']),
				'seokeywords' => dhtmlspecialchars(trim($_G['gp_seokeyword'])),
				'seodesc' => dhtmlspecialchars(trim($_G['gp_seodesc'])),
				'lazyload' => intval($_G['gp_lazyload']),
			);

			if(!$havefield) {
				$data_filed['itemid'] = $itemid;
				DB::insert('poll_item_field', $data_filed);
			} else {
				DB::update('poll_item_field', $data_filed, "itemid = '$itemid' ");
			}

			$starttime = strtotime($_G['gp_starttime']);
			$endtime = strtotime($_G['gp_endtime']);
			if($starttime > $endtime) {
				cpmsg('poll_error_daterange', '', 'error');
			}
			//debug 1 cookie, 2 用戶名, 3 ip, 4 share object
			$_G['gp_repeattype'] = bindec(intval($_G['gp_repeattype'][4]).intval($_G['gp_repeattype'][3]).intval($_G['gp_repeattype'][2]).intval($_G['gp_repeattype'][1]));
			$data = array(
				'available' => intval($_G['gp_available']),
				'numperpage' => intval($_G['gp_pagenum']),
				'repeattype' => intval($_G['gp_repeattype']),
				'limittime' => intval($_G['gp_limittime']),
				'type' => intval($_G['gp_polltype']),
				'templateid' => intval($_G['gp_template']),
				'choicenum' => intval($_G['gp_choicenum']),
				'contenttype' => intval($_G['gp_contenttype']),
				'resultview_mod' => intval($_G['gp_rvmod']),
				'resultview_time' => intval($_G['gp_rvtime']),
				'errordetail' => intval($_G['gp_errordetail']),
				'starttime' => intval($starttime),
				'endtime' => intval($endtime),
			);

			DB::update('poll_item', $data, "itemid = '$itemid'");

			$new && intval($_G['gp_contenttype']) && cpmsg('poll_update_succeed_2', "action=poll&operation=choiceedit&itemid=$itemid&new=1&flashupload=1", 'succeed');
			$new && cpmsg('poll_update_succeed_2', "action=poll&operation=choiceedit&itemid=$itemid&new=1", 'succeed');
			!$new && cpmsg('poll_update_succeed', 'action=poll&operation=list', 'succeed');

		}

	//選項設置
	} elseif($operation == 'choiceedit') {

		if(!$itemid) {
			cpmsg('poll_manage_invalid', '', 'error');
		}

		if(!submitcheck('choicesubmit')) {

			shownav('poll', 'nav_poll_manage', 'nav_poll_choiceedit');

			if($new) {
				showsubmenu('nav_poll_setting', array(
					array('poll_creat_step_1', "poll&operation=create&itemid=$itemid", 0),
					array('poll_creat_step_2', "poll&operation=manage&itemid=$itemid&new=$new", 0),
					array('poll_creat_step_3', "poll&operation=choiceedit&itemid=$itemid&new=$new&flashupload=$poll_setting[contenttype]", 1)
				));
			} else {
				$poll_setting['title'] = $poll_setting['title'].'(<a href="poll.php?id='.$itemid.'" target="_blank">'.cplang('preview').'</a>)';
				showsubmenu($poll_setting['title'], array(
					array('nav_poll_setting', "poll&operation=manage&itemid=$itemid", 0),
					array('nav_poll_choiceedit', "poll&operation=choiceedit&itemid=$itemid", 1),
					array('menu_poll_state', "poll&operation=rlist&itemid=$itemid", 0),
					array('menu_poll_analysis', "poll&operation=analysis&itemid=$itemid", 0),
					array('nav_poll_calling', "poll&operation=calling&itemid=$itemid", 0),
				));
			}

			//Flash上傳選項
			if($flashupload) {

				showtableheader('nav_poll_choiceedit', 'fixpadding');
				showtablefooter();

				echo "
				<div class=\"fswf\" id=\"multiimg\">
					<script type=\"text/javascript\">
						$('multiimg').innerHTML = AC_FL_RunContent(
							'width', '470', 'height', '268',
							'src', '".STATICURL."image/common/upload.swf?site={$_G['siteurl']}misc.php%3fmod=swfupload%26type=image&pollid=$itemid&random=".random(4)."',
							'quality', 'high',
							'id', 'swfupload',
							'menu', 'false',
							'allowScriptAccess', 'always',
							'wmode', 'transparent'
						);
						function swfHandler(action, pollid) {
							if(action==2) {
								url_forward = 'admin.php?action=poll&operation=choiceedit&itemid=' + pollid;
								location.href=url_forward;
							}
						}
					</script>
				</div>
				";

			//普通投票選項
			} else {

				//批量新增選項
				if($new || $_G['gp_multioptions']) {
					showtableheader('poll_choice_multi_add', 'fixpadding');
					showformheader('poll&operation=choiceedit', 'enctype');
					showhiddenfields(array('itemid' => "$itemid"));
					showhiddenfields(array('ismultioptions' => "1"));
					showsetting('', '', '', '<textarea cols="85" rows="10" name="newchoiceoptions" style="width:500px; height:260px;"></textarea>');
					showsubmit('choicesubmit', 'submit', '', cplang('multioptions_comment'));
					showformfooter();
					showtablefooter();

				//編輯頁面
				} else {
					$choice_setting = array();
					$query = DB::query("SELECT * FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid' ORDER BY displayorder");
					while($row = DB::fetch($query)) {
						$choice_setting[$row['choiceid']] = array(
							'caption' => $row['caption'],
							'displayorder' => $row['displayorder'],
							'imageurl' => $row['imageurl'],
							'detailurl' => $row['detailurl']
						);
					}

					showformheader('poll&operation=choiceedit', 'enctype');
					showhiddenfields(array('itemid' => "$itemid"));
					showtableheader('nav_poll_choiceedit', 'fixpadding');

					$tdtitle = array('','displayorder', 'option', '', 'detail');
					if($poll_setting['contenttype']) {
						$tdtitle = array('','displayorder', 'option', 'poll_option_detailurl', 'detail');
					}

					echo "<script type=\"text/JavaScript\">
					var rowtypedata = [
						[
							[1, '', 'td25'],
							[1, '<input type=\"text\" class=\"txt\" size=\"2\" name=\"newdisplayorder[]\">', 'td28'],
							[1, '<input type=\"text\" class=\"txt mtxt\" size=\"18\" name=\"newchoicetitle[]\">'],
							[1, ''],
							[1, '']
						]
					];
					</script>";
					showsubtitle($tdtitle);

					foreach($choice_setting as $choiceid => $choice) {
						$imgstr = $poll_setting['contenttype'] ? '<div id="img_'.$choiceid.'" style="z-index:1; position:absolute; margin: -20px 0 0 250px; margin: 0\9; display:none; border:5px solid #CECEF6;"><img class="vmiddle" src="'.$_G['setting']['attachurl'].'poll/'.$choice['imageurl'].'.thumb.jpg'.'" /></div>'  : '';
						$imgmouse = $poll_setting['contenttype'] ? "onmouseover=\"display('img_{$choiceid}')\" onmouseout=\"display('img_{$choiceid}')\"":'';
						showtablerow('', array('', 'class="td25"', '', '', '', ''), array(
							"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$choiceid]\"  value=\"$choiceid\"/>",
							"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$choiceid]\" value=\"$choice[displayorder]\">",
							"<input type=\"text\" class=\"txt mtxt\" size=\"18\" name=\"choiceoption[$choiceid]\" value=\"$choice[caption]\" $imgmouse /> $imgstr",
							($poll_setting['contenttype'] ? "<input type=\"text\" class=\"txt mtxt\" size=\"18\" name=\"detailurl[$choiceid]\" value=\"$choice[detailurl]\">" : ''),
							"<a href=\"".ADMINSCRIPT."?action=poll&operation=rlist&itemid=$itemid&choiceid=$choiceid\">$lang[poll_state_rlist]</a> &nbsp;
							<a href=\"".ADMINSCRIPT."?action=poll&operation=optiondetail&itemid=$itemid&choiceid=$choiceid\">$lang[edit]</a>",
						));
					}
					if(!empty($poll_setting['contenttype'])) {
						echo '<tr><td colspan="5"><a href="admin.php?action=poll&operation=choiceedit&itemid='.$itemid.'&flashupload=1" class="addtr" >'.cplang('poll_choice_add').'</a></td></tr>';
					} else {
						echo '<tr><td colspan="5"><div><a href="javascript:;" onclick="addrow(this, 0);" class="addtr" >'.cplang('poll_choice_add').'</a> &nbsp; <a href="admin.php?action=poll&operation=choiceedit&itemid='.$itemid.'&multioptions=1" class="addtr" >'.cplang('poll_choice_multi_add').'</a></div></td></tr>';
					}
					showsubmit('choicesubmit', 'submit', 'del');
					showtablefooter();
					showformfooter();
				}
			}

		} else {
			$newchoiceoption = is_array($_G['gp_newchoicetitle']) ? $_G['gp_newchoicetitle'] : array();
			$choiceoption = is_array($_G['gp_choiceoption']) ?  $_G['gp_choiceoption']  : array();

			//單個input修改
			if($choiceoption) {
				foreach($choiceoption as $choiceid => $value) {
					$value = trim(strip_tags($value));
					if(!empty($value)) {
						$data = array(
							'caption' => dhtmlspecialchars(trim($value)),
							'displayorder' => intval($_G['gp_displayorder'][$choiceid]),
							'detailurl' => dhtmlspecialchars(trim($_G['gp_detailurl'][$choiceid]))
						);
						DB::update('poll_choice', $data, "choiceid = '$choiceid'");
					} else {
						cpmsg('poll_add_invalid', '', 'error');
					}
				}
			}

			//多個textarea
			$newchoiceoption = $_G['gp_ismultioptions'] ? explode("\n", $_G['gp_newchoiceoptions']) : $newchoiceoption;
			//單個input增加
			if($newchoiceoption) {
				foreach($newchoiceoption as $key => $value) {
					$caption = dhtmlspecialchars(trim($value));
					if($caption !== '') {
						$data = array(
							'itemid' => $itemid,
							'caption' => $caption,
							'displayorder' => intval($_G['gp_newdisplayorder'][$key]),
						);
						DB::insert('poll_choice', $data);
					}
				}
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::delete('poll_choice', "choiceid IN ($ids)");
				DB::delete('poll_value', "choiceid IN ($ids)");
			}

			cpmsg('poll_update_succeed', 'action=poll&operation=choiceedit&itemid='.$itemid, 'succeed');

		}

	} elseif($operation == 'optiondetail') {

		$choiceid = intval($_G['gp_choiceid']);
		$_G['gp_returnurl'] = $_G['gp_returnurl'] ? 'list' : '';
		$returnurl = $_G['gp_returnurl'] == 'list' ? "action=poll&operation=rlist&itemid=$itemid" : "action=poll&operation=optiondetail&itemid=$itemid&choiceid=$choiceid";
		if(empty($choiceid)) {
			cpmsg('poll_manage_invalid', '', 'error');
		}

		$optiondata = DB::fetch_first("SELECT caption, pollnum, imageurl, detailurl, aid FROM ".DB::table('poll_choice')." WHERE choiceid='$choiceid'");
		$attach = array();

		if(!submitcheck('optiondetailsubmit')) {

			if(empty($optiondata)) {
				cpmsg('poll_manage_invalid', '', 'error');
			}

			$attachimg = '';
			if($optiondata['imageurl']) {
				$optiondata['imageurl'] = $_G['setting']['attachurl'].'poll/'.$optiondata['imageurl'].'.thumb.jpg?'.random(6);
				$attachimg = cplang('poll_option_pic').':<br /><img src="'.$optiondata['imageurl'].'" />';
			}

			shownav('poll', 'nav_poll_manage', 'nav_poll_choiceedit');
			$poll_setting['title'] = $poll_setting['title'].'(<a href="poll.php?id='.$itemid.'" target="_blank">'.cplang('preview').'</a>)';
			showsubmenu($poll_setting['title'], array(
				array('nav_poll_setting', 'poll&operation=manage&itemid='.$itemid, 0),
				array('nav_poll_choiceedit', 'poll&operation=choiceedit&itemid='.$itemid, 1),
				array('menu_poll_state', 'poll&operation=rlist&itemid='.$itemid, 0),
				array('menu_poll_analysis', 'poll&operation=analysis&itemid='.$itemid, 0),
				array('nav_poll_calling', "poll&operation=calling&itemid=$itemid", 0),
			));

			showformheader('poll&operation=optiondetail&itemid='.$itemid.'&choiceid='.$choiceid.'&returnurl='.$_G['gp_returnurl'], 'enctype');
			showtableheader("$lang[nav_poll_choiceedit] - $optiondata[caption]", 'fixpadding');
			showsetting('poll_option_title', 'captionnew', $optiondata['caption'], 'text');
			showsetting('poll_option_pollnum', 'pollnumnew', $optiondata['pollnum'], 'text');
			if($poll_setting['contenttype']) {
				showsetting('poll_option_detailurl', 'detailurlnew', $optiondata['detailurl'], 'text');
				showsetting('poll_option_pic', 'urlnew', '', 'filetext', '', 0, $attachimg);
			}

			showsubmit('optiondetailsubmit', 'submit');
			showtablefooter();
			showformfooter();
			$bbcode = '[poll='.$itemid.','.$choiceid.']'.$_G['siteurl'].'[/poll]';
			$jscode = '<script type="text/javascript" src="'.$_G['siteurl'].'api/poll.php?action=pollnum&itemid='.$itemid.'&choiceid='.$choiceid.'" charset="'.CHARSET.'"></script>';
			showtableheader('poll_option_code', 'fixpadding');
			showsetting('poll_option_bbcode', '', $bbcode, 'textarea');
			showsetting('poll_option_jscode', '', $jscode, 'textarea');
			showtablefooter();

		} else {

			if(empty($_G['gp_captionnew'])) {
				cpmsg('poll_add_invalid', '', 'error');
			}

			if($_FILES['urlnew']['name']) {
				delete_images($optiondata['aid']);
				$attach = upload_images($_FILES['urlnew'], 'poll', 176, 176);
			} else {
				$attach['attachment'] = $optiondata['imageurl'];
				$attach['aid'] =  $optiondata['aid'];
			}

			$optiondata = array(
				'caption' => dhtmlspecialchars(trim($_G['gp_captionnew'])),
				'pollnum' => intval($_G['gp_pollnumnew']),
				'imageurl' => $attach['attachment'],
				'detailurl' => dhtmlspecialchars(trim($_G['gp_detailurlnew'])),
				'aid' => $attach['aid']
			);

			DB::update('poll_choice', $optiondata, "choiceid='$choiceid'");

			$pollnum = DB::result_first("SELECT SUM(pollnum) FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid'");
			DB::update('poll_item', array('totalnum' => $pollnum), "itemid = '$itemid'");

			cpmsg('poll_option_succeed', $returnurl, 'succeed');

		}

	} elseif($operation == 'rlist'){

		$choiceid = intval($_G['gp_choiceid']);
		$adminurl = ADMINSCRIPT.'?action=poll&operation=rlist';

		shownav('poll', 'nav_poll_manage', 'nav_poll_choose_rlist');
		$poll_setting['title'] = $poll_setting['title'].'(<a href="poll.php?id='.$itemid.'" target="_blank">'.cplang('preview').'</a>)';
		showsubmenu($poll_setting['title'], array(
			array('nav_poll_setting', 'poll&operation=manage&itemid='.$itemid, 0),
			array('nav_poll_choiceedit', 'poll&operation=choiceedit&itemid='.$itemid, 0),
			array('menu_poll_state', 'poll&operation=rlist&itemid='.$itemid, 1),
			array('menu_poll_analysis', 'poll&operation=analysis&itemid='.$itemid, 0),
			array('nav_poll_calling', "poll&operation=calling&itemid=$itemid", 0),
		));

		$multipage = '';
		if(!$choiceid) {

			$totalpollnum = DB::result_first("SELECT SUM(pollnum) FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid'");
			showtableheader("$lang[nav_poll_state]".cplang('poll_pollnum_total', array('totalpollnum' => $totalpollnum)), 'fixpadding');
			showsubtitle(array('poll_choice_option', 'poll_vote_num', 'poll_manage'));

			$total = $poll_setting['totalnum'];
			$query = DB::query("SELECT * FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid' ORDER BY pollnum DESC");
			while($row = DB::fetch($query)) {
				$percent = round(($row['pollnum'] / $total) * 100, 2);
				showtablerow('', array('', '', ''), array(
					$row['caption'],
					$row['pollnum'].'&nbsp;('.$percent.'%)',
					'<a href="'.$adminurl.'&itemid='.$itemid.'&choiceid='.$row['choiceid'].'">'.cplang('poll_rlist_choice_details').'</a> &nbsp;
					<a href="'.ADMINSCRIPT.'?action=poll&operation=optiondetail&itemid='.$itemid.'&choiceid='.$row['choiceid'].'&returnurl=list">'.cplang('edit').'</a>'
				));
			}

		} else {

			$coption = DB::result_first("SELECT caption FROM ".DB::table('poll_choice')." WHERE choiceid='$choiceid'");

			showtableheader("$lang[nav_poll_option]-$coption", 'fixpadding');
			showsubtitle(array('poll_rlist_username', 'poll_rlist_dateline', 'poll_rlist_ip'));

			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('poll_value')." WHERE itemid = '$itemid' AND choiceid = '$choiceid'");

			$perpage = max(5, empty($_G['gp_perpage']) ? 50 : intval($_G['gp_perpage']));
			$start_limit = ($page - 1) * $perpage;
			$mpurl = ADMINSCRIPT."?action=poll&operation=rlist&itemid=$itemid&choiceid=$choiceid";

			$multipage = multi($num, $perpage, $page, $mpurl);

			$query = DB::query("SELECT p.dateline, p.ip, m.username FROM ".DB::table('poll_value')." p
				LEFT JOIN ".DB::table('common_member')." m ON p.uid=m.uid
				WHERE p.itemid = '$itemid' AND p.choiceid = '$choiceid' ORDER BY p.dateline LIMIT $start_limit, $perpage");
			while($row = DB::fetch($query)) {
				$dateline = dgmdate($row['dateline'], 'Y-m-d H:i');
				showtablerow('', array('', '', ''), array(
					(!empty($row['username']) ? $row['username'] : cplang('guest')) ,
					$dateline,
					$row['ip'],
				));
			}
		}

		showsubmit('', '', '', $multipage);
		showtablefooter();

	} elseif($operation == 'counter') {

		$pertask = isset($_G['gp_pertask']) ? intval($_G['gp_pertask']) : 500;
		$current = isset($_G['gp_current']) && $_G['gp_current'] > 0 ? intval($_G['gp_current']) : 0;
		$next = $current + $pertask;
		require_once libfile('function/misc');

		$itemurl = $additem = $referurl = '';
		$formid = !empty($_G['gp_formid']) ? intval($_G['gp_formid']) : '';

		if(!empty($formid)) {
			$formip = !empty($_G['gp_formip']) ? dhtmlspecialchars($_G['gp_formip']) : '';
			$itemurl = '&formid='.$formid.($formip ? '&formip='.$formip : '');
			$additem = "WHERE itemid='$formid'";
			$referurl = empty($formip) ? 'action=poll&operation=analysis&itemid='.$formid : 'action=poll&operation=analysis&itemid='.$formid.'&ip='.$formip;
		}

		if(!submitcheck('countpollsubmit', 1) && !submitcheck('countoptionsubmit', 1) && !submitcheck('countpollnumsubmit', 1)) {
			showtableheader('nav_poll_counter', 'fixpadding');
			showformheader('poll&operation=counter');
			showsubmit('countpollsubmit', 'poll_count_pollip');
			showsubmit('countoptionsubmit', 'poll_count_optionip');
			showsubmit('countpollnumsubmit', 'poll_count_pollnum');
			showformfooter();
			showtablefooter();
		} elseif(submitcheck('countpollsubmit', 1)) {
			if(empty($current)) {
				DB::query("TRUNCATE TABLE ".DB::table('poll_item_count')."");
			}

			$nextlink = "action=poll&operation=counter&current=$next&pertask=$pertask&countpollsubmit=yes$itemurl";
			$processed = 0;

			$query = DB::query("SELECT choiceid, itemid, ip FROM ".DB::table('poll_value')." $additem LIMIT $current, $pertask");
			while($item = DB::fetch($query)) {
				$processed = 1;
				$itemip = DB::result_first("SELECT ip FROM ".DB::table('poll_item_count')." WHERE itemid='$item[itemid]' AND ip='$item[ip]'");
				if(!empty($itemip)) {
					DB::query("UPDATE ".DB::table('poll_item_count')." SET count=count+1 WHERE itemid='$item[itemid]' AND ip='$item[ip]'");
				} else {
					$data = array('itemid' => $item['itemid'], 'ip' => $item['ip'], 'area' => convertip($item['ip']), 'count' => 1);
					DB::insert('poll_item_count', $data);
				}
			}
			if($processed) {
				cpmsg(cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
			} else {
				cpmsg('counter_succeed', $referurl, 'succeed');
			}
		} elseif(submitcheck('countoptionsubmit', 1)) {
			if(empty($current)) {
				DB::query("TRUNCATE TABLE ".DB::table('poll_choice_count')."");
			}

			$nextlink = "action=poll&operation=counter&current=$next&pertask=$pertask&countoptionsubmit=yes$itemurl";
			$processed = 0;

			$query = DB::query("SELECT choiceid, itemid, ip FROM ".DB::table('poll_value')." $additem LIMIT $current, $pertask");
			while($item = DB::fetch($query)) {
				$processed = 1;
				$choiceip = DB::result_first("SELECT ip FROM ".DB::table('poll_choice_count')." WHERE choiceid='$item[choiceid]' AND ip='$item[ip]'");
				if(!empty($choiceip)) {
					DB::query("UPDATE ".DB::table('poll_choice_count')." SET count=count+1 WHERE choiceid='$item[choiceid]' AND ip='$item[ip]'");
				} else {
					$data = array('choiceid' => $item['choiceid'], 'itemid' => $item['itemid'], 'ip' => $item['ip'], 'area' => convertip($item['ip']), 'count' => 1);
					DB::insert('poll_choice_count', $data);
				}
			}
			if($processed) {
				cpmsg(cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
			} else {
				cpmsg('counter_succeed', $referurl, 'succeed');
			}
		} elseif(submitcheck('countpollnumsubmit', 1)) {

			$nextlink = "action=poll&operation=counter&current=$next&pertask=$pertask&countpollnumsubmit=yes";
			$processed = 0;

			$query = DB::query("SELECT itemid FROM ".DB::table('poll_item')." LIMIT $current, $pertask");
			while($item = DB::fetch($query)) {
				$processed = 1;
				$pollnum = DB::result_first("SELECT SUM(pollnum) FROM ".DB::table('poll_choice')." WHERE itemid = '$item[itemid]'");
				DB::update('poll_item', array('totalnum' => $pollnum), "itemid = '$item[itemid]'");
			}

			if($processed) {
				cpmsg(cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
			} else {
				cpmsg('counter_succeed', '', 'succeed');
			}
		}

	} elseif($operation == 'analysis') {

		$perpage = max(5, empty($_G['gp_perpage']) ? 30 : intval($_G['gp_perpage']));
		$start_limit = ($page - 1) * $perpage;

		shownav('poll', 'nav_poll_analysis');
		$poll_setting['title'] = $poll_setting['title'].'(<a href="poll.php?id='.$itemid.'" target="_blank">'.cplang('preview').'</a>)';
		showsubmenu($poll_setting['title'], array(
			array('nav_poll_setting', 'poll&operation=manage&itemid='.$itemid, 0),
			array('nav_poll_choiceedit', 'poll&operation=choiceedit&itemid='.$itemid, 0),
			array('menu_poll_state', 'poll&operation=rlist&itemid='.$itemid, 0),
			array('menu_poll_analysis', 'poll&operation=analysis&itemid='.$itemid, 1),
			array('nav_poll_calling', "poll&operation=calling&itemid=$itemid", 0),
		));

		if($itemid && empty($_G['gp_ip'])) {

			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('poll_item_count')." WHERE itemid = '$itemid'");
			$mpurl = ADMINSCRIPT."?action=poll&operation=analysis&itemid=$itemid";

			$multipage = multi($num, $perpage, $page, $mpurl);

			showtableheader('poll_analysis_pollip', 'fixpadding');
			showsubtitle(array('poll_vote_ip', 'poll_vote_area', 'poll_vote_num', 'operation'));

			$itemcountlist = array();
			$query = DB::query("SELECT * FROM ".DB::table('poll_item_count')." WHERE itemid = '$itemid' ORDER BY count DESC LIMIT $start_limit, $perpage");
			while($itemcount = DB::fetch($query)) {
				$itemcountlist[] = $itemcount;
			}

			if(!empty($itemcountlist)) {
				foreach($itemcountlist as $itemcount) {
					showtablerow('', array('', '', '', ''), array(
						$itemcount['ip'],
						$itemcount['area'],
						$itemcount['count'],
						'<a href="'.ADMINSCRIPT.'?action=poll&operation=analysis&itemid='.$itemcount['itemid'].'&ip='.$itemcount['ip'].'">'.cplang('poll_vote_view').'</a>',
					));
				}
			} else {
				showsubmit('', '', '', '<a href="'.ADMINSCRIPT.'?action=poll&operation=counter&countpollsubmit=yes&formid='.$itemid.'">還沒有進行過數據分析，點擊進行分析</a>');
			}

			showsubmit('', '', '', $multipage);
			showtablefooter();

		} else {

			$_G['gp_ip'] = dhtmlspecialchars($_G['gp_ip']);
			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('poll_choice_count')." WHERE itemid = '$itemid' AND ip='$_G[gp_ip]'");
			$mpurl = ADMINSCRIPT."?action=poll&operation=analysis&itemid=$itemid&ip=$_G[gp_ip]";

			showtableheader("$_G[gp_ip] $lang[poll_analysis_optionip]", 'fixpadding');
			showsubtitle(array('option', 'poll_vote_area', 'poll_vote_num'));

			$choicelist = array();
			$query = DB::query("SELECT pcc.ip, pcc.area, pcc.count, pc.caption FROM ".DB::table('poll_choice_count')." pcc
			LEFT JOIN ".DB::table('poll_choice')." pc ON pcc.choiceid=pc.choiceid
			WHERE pcc.itemid = '$itemid' AND pcc.ip='$_G[gp_ip]' ORDER BY pcc.count DESC LIMIT $start_limit, $perpage");
			while($choicecount = DB::fetch($query)) {
				$choicelist[] = $choicecount;
			}

			if(!empty($choicelist)) {
				foreach($choicelist as $choicecount) {
					showtablerow('', array('', '', '', ''), array(
						$choicecount['caption'],
						$choicecount['area'],
						$choicecount['count'],
					));
				}
			} else {
				showsubmit('', '', '', '<a href="'.ADMINSCRIPT.'?action=poll&operation=counter&countoptionsubmit=yes&formid='.$itemid.'&formip='.$_G['gp_ip'].'">還沒有進行過數據分析，點擊進行分析</a>');
			}

			showtablefooter();

		}
	} elseif($operation == 'calling') {
		shownav('poll', 'nav_poll_calling');
		$poll_setting['title'] = $poll_setting['title'].'(<a href="poll.php?id='.$itemid.'" target="_blank">'.cplang('preview').'</a>)';
		showsubmenu($poll_setting['title'], array(
			array('nav_poll_setting', "poll&operation=manage&itemid=$itemid", 0),
			array('nav_poll_choiceedit', "poll&operation=choiceedit&itemid=$itemid", 0),
			array('menu_poll_state', "poll&operation=rlist&itemid=$itemid", 0),
			array('menu_poll_analysis', "poll&operation=analysis&itemid=$itemid", 0),
			array('nav_poll_calling', "poll&operation=calling&itemid=$itemid", 1),
		));

		$topcode = '<script type="text/javascript" src="'.$_G['siteurl'].'api/poll.php?action=toplist&itemid='.$itemid.'" charset="'.CHARSET.'"></script>';
		showtableheader('poll_calling_code', 'fixpadding');
		showsetting('poll_calling_code_toplist', '', $topcode, 'textarea');
		showtablefooter();
	}

} //endif setting

?>