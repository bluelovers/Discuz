<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: poll.php 661 2010-09-14 08:13:20Z yexinhao $
 */

include_once('../source/class/class_core.php');
include_once('../source/function/function_core.php');

$cachelist = array('setting');
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();

$data = $extracode = '';
$mod = !empty($_G['gp_mod']) && in_array($_G['gp_mod'], array('js')) ? $_G['gp_mod'] : 'js';
$action = !empty($_G['gp_action']) && in_array($_G['gp_action'], array('pollnum', 'toplist')) ? $_G['gp_action'] : '';
$itemid = !empty($_G['gp_itemid']) ? intval($_G['gp_itemid']) : 0;
$choiceid = !empty($_G['gp_choiceid']) ? intval($_G['gp_choiceid']) : 0;

if($action == 'pollnum') {

	$html = '<style>.pollbtn { clear:both; }.pollbtn a { float:left; padding-left: 74px; height: 34px; line-height:34px; background:url({siteurl}template/common/images/btn_js.jpg) no-repeat left center; font-size: 18px; color: #333; font-weight: 700; text-decoration: none; cursor: pointer; }.pollbtn a:hover { text-decoration: none; color:#900; }.pollbtn span { float:left; padding: 0 90px 0 0; height: 34px; line-height:34px; background:url({siteurl}template/common/images/btn_js.jpg) no-repeat right center; }</style>'.
		'<div class=\"pollbtn\"><a href=\"{siteurl}poll.php?action=choose&id={itemid}&choose_value={choiceid}&handlekey=polljs\" onclick=\"ajaxget(this.href, \'pollmsg_{choiceid}\');return false;\" class=\"pollbtn\"><span id=\"pollnum_{choiceid}\">{pollnum}</span></a></div>';
	if($itemid && $choiceid) {
		$pollnum = DB::result_first("SELECT pollnum FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid' AND choiceid='$choiceid'");
		$data = preg_replace(array("/\{siteurl\}/i", "/\{itemid\}/i", "/\{choiceid\}/i", "/\{pollnum\}/i"), array($_G['siteurl'], $itemid, $choiceid, $pollnum), stripslashes($html));
		$extracode = "function succeedhandle_polljs(url, msg){alert(msg);$('pollnum_$choiceid').innerHTML = parseInt($('pollnum_$choiceid').innerHTML) + 1;};function errorhandle_polljs(msg){alert(msg);}";
	}

} elseif($action == 'toplist') {

	$topnum = !empty($_G['gp_topnum']) ? intval($_G['gp_topnum']) : 10;
	$data = '<style>.polltop { font-size: 12px; color: #666;  }.polltop * { margin: 0px; padding: 0px; }.polltop dl { padding: 8px; height: 66px; border-bottom: 1px dashed #DCDCDC; border-top: 1px solid #FFF; clear: both; }.polltop dt { float: left; margin-right: 12px; width: 66px; height: 66px; text-align: center; overflow: hidden; }.polltop dt img { margin-left: -50%; border: 0px; }.polltop dd { overflow: hidden;  }.polltop dd p { line-height:20px; }.polltop dd p a { color: #73A2D0; text-decoration: underline; }.polltop dd .t a{ color: #369; font-weight: 700; text-decoration: none; }</style>';
	$data .= '<div class="polltop">';
	$html = '<dl><dt><a href="#"><img src="{image}" alt="{caption}"/></a></dt><dd><p class="t"><a href="#">{caption}</a></p><p>'.lang('poll/api', 'poll_num').' {pollnum} '.lang('poll/api', 'poll_numunit').'</p><p> {pollurl} <a href="{url}">'.lang('poll/api', 'detail').'</a></p></dd></dl>';
	if($itemid) {
		$query = DB::query("SELECT * FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid' ORDER BY pollnum DESC LIMIT $topnum");
		while($row = DB::fetch($query)) {
			$row['imagethumb'] = $row['imageurl'] ? $_G['siteurl'].$_G['setting']['attachurl'].'poll/'.$row['imageurl'].'.thumb.jpg' : $_G['siteurl'].'static/image/common/default.jpg.thumb.jpg';
			$row['pollnum'] = '<span id="pollnum_'.$row['choiceid'].'">'.$row['pollnum'].'</span>';
			$row['pollurl'] = '<a href="'.$_G['siteurl'].'poll.php?action=choose&id='.$row['itemid'].'&choose_value='.$row['choiceid'].'&handlekey=polljs" onclick="ajaxget(this.href, \'pollmsg_'.$row['choiceid'].'\');return false;">'.lang('poll/api', 'voteme').'</a>&nbsp;';
			$data .= preg_replace(array("/\{image\}/i", "/\{caption\}/i", "/\{pollnum\}/i", "/\{url\}/i", "/\{pollurl\}/i"), array($row['imagethumb'], $row['caption'], $row['pollnum'], $row['detailurl'], $row['pollurl']), stripslashes($html));
		}
		$extracode = "function succeedhandle_polljs(url, msg, values){var id = values['0'];alert(msg);$('pollnum_' + id).innerHTML = parseInt($('pollnum_' + id).innerHTML) + 1;};function errorhandle_polljs(msg){alert(msg);}";
	}
	$data .= '</div>';

}

if($mod == 'js') {
	exit($extracode.'document.write(\''.preg_replace("/\r\n|\n|\r/", '', addcslashes($data, "'\\")).'\');');
}

?>