<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ads.inc.php 4379 2010-09-09 03:00:50Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$editvalue = array();
updatebrandadscache(false);
$editvalue = $_G['brandads'];
$editvalue['banner'] = htmlspecialchars_decode($editvalue['banner']);
$editvalue['sitetheme'] = $_G['setting']['sitetheme'];

$arr_allowed_var = array('ads_show_type','banner', 'consume', 'discount', 'sidebarshop', 'sidebarconsume',
	'hotgoods', 'hotshop', 'groupbuy', 'sidebargroupbuy');

if(!empty($_POST['valuesubmit'])) {
	$item = array();
	$key = $rpsql = $comma = '';
	foreach($_POST as $key=>$value) {
		if($_POST[$key]!=$editvalue[$key] || empty($_POST[$key])) {
			if(in_array($key, $arr_allowed_var)) {
				$rpsql .= "$comma ('$key', '$value') ";
				$comma = ', ';
			} elseif($key == 'notice') {
				foreach($value as $k=>$v) {
					$value[$k]['title']=trim(strip_tags($v['title']));
					$value[$k]['url']=trim(strip_tags($v['url']));
					$style = array();
					$style[0] = empty($_POST['fontcolornotice'.$k])?'      ':$_POST['fontcolornotice'.$k];
					$style[1] = !trim(strip_tags($_POST['emnotice'.$k])) ? '' : 1;
					$style[2] = !trim(strip_tags($_POST['strongnotice'.$k])) ? '' : 1;
					$style[3] = !trim(strip_tags($_POST['underlinenotice'.$k])) ? '' : 1;
					$highlight = sprintf("#%6s%1s%1s%1s",substr($style[0], -6),$style[1],$style[2],$style[3]);
					if (trim($highlight) != "") {
						$value[$k]['style'] = $highlight;
					} else {
						$value[$k]['style'] = "";
					}
					unset($style);
				}
				$value = addslashes(serialize($value));
				$rpsql .= "$comma ('$key', '$value') ";
				$comma = ', ';
			} elseif($key=='topic') {
				foreach($value as $k=>$v) {
					if($v['image'] == '' && $v['url'] == '') {
						unset($value[$k]);
					} else {
						$value[$k]['image']=trim(strip_tags($v['image']));
						$value[$k]['url']=trim(strip_tags($v['url']));
					}
				}
				$value = addslashes(serialize($value));
				$rpsql .= "$comma ('$key', '$value') ";
				$comma = ', ';
			}

		}
	}
	if(!empty($rpsql)) {
		DB::query('REPLACE INTO '.tname('data').' (`variable`, `value`) VALUES '.$rpsql);
	}
	$sitetheme = saddslashes($_POST['sitetheme']);
	DB::query('REPLACE INTO '.tname('settings').' (`variable`, `value`) VALUES (\'sitetheme\', \''.$sitetheme.'\')');
	updatesettingcache();
	updatebrandadscache();//生成緩存
	$_BCACHE->deltype('index');
	$_BCACHE->deltype('sidebar');
	cpmsg('message_success', 'admin.php?action=ads');

} else {

	$sitethemearr = array();
	$dir = opendir(B_ROOT.'./templates/site');
	while($entry = readdir($dir)) {
		if(strpos($entry, '.') === false) {
			$sitethemearr[] = array($entry, 'templates/site/'.$entry);
		}
	}
	shownav('global', 'ads_basic');
	showsubmenu('ads_basic');
	showtips('ads_tips');
	showformheader('ads');
	showhiddenfields(array('valuesubmit' => 'yes'));
	echo '<div style="border-top:1px dotted #DEEFFB;margin:5px 0 10px 0;padding:10px 0 0 5px;"><strong style="line-height:30px;">'.$lang['ads_sitetheme'].'</strong><br/>';
	showsetting('', array('sitetheme', $sitethemearr), $editvalue['sitetheme'], 'select');
	echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$lang['ads_sitetheme_comment'].'</div>';
	echo '<div style="border-top:1px dotted #DEEFFB;margin:5px 0 10px 0;padding:10px 0 0 5px;"><strong style="line-height:30px;">'.$lang['ads_index_show_type'].'</strong><br/>
			<input name="ads_show_type" id="ads_show_type_topic" type="radio" style="border:none;" value="topic" onclick="show_ad_topic();" />'.lang('ads_topic').'
			<input checked name="ads_show_type" id="ads_show_type_banner" type="radio" style="border:none;" value="banner" onclick="show_ad_banner();" />'.lang('ads_banner').'
		</div>';
	showtableheader();
	echo '<tbody id="_ads_banner">';
	showsetting('ads_banner', 'banner', $editvalue['banner'], 'textarea');
	echo '</tbody>';
	echo '<tbody id="_ads_topic" style="display:none;"><tr><td class="td27" colspan="2">'.lang('ads_topic').'&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:100">'.lang('ads_topic_comment').'</td></tr>';
	echo '<tr><td style="border:none;"><table>';
	for ($i=0; $i<7; $i++) {
		echo '<tr class="noborder"><td class="vtop rowform">
		<input type="text" class="txt" value="'.(!empty($editvalue['topic'][$i])?$editvalue['topic'][$i]['image']:'').'" name="topic['.$i.'][image]">
	</td>
	<td align="left" class="vtop rowform">
	<input type="text" class="txt" value="'.(!empty($editvalue['topic'][$i]) ?$editvalue['topic'][$i]['url']:'').'" name="topic['.$i.'][url]"></td></tr>';
	}
	echo '</table></td></tr>';
	showtablefooter();
	showtableheader();
	echo '<tr><td class="td27">'.lang('ads_notice').'<span style="margin-left:80px; font-weight:normal;">'.lang('ads_notice_comment').'</span></td></tr>';
	echo '<tr><td style="border:none;"><table width="100%">';
	for($i=0;$i<7;$i++) {	
		echo '<tr class="noborder"><td class="vtop rowform"><input type="text" class="txt" style="'.pktitlestyle($editvalue['notice'][$i]['style']).'" value="'
		.(!empty($editvalue['notice'][$i]) ? $editvalue['notice'][$i]['title']:'')
		.'" name="notice['.$i.'][title]" id="notice'.$i.'"></td><td align="left" class="vtop rowform" style="width:auto;">'
		.'<input type="text" class="txt" style="float:left;" value="'.(!empty($editvalue['notice'][$i]) ? $editvalue['notice'][$i]['url']:'').'" name="notice['.$i.'][url]" />'
		.'<div style="float:left; width:200px;">'.show_style_picker('notice'.$i, substr($editvalue['notice'][$i]['style'], 0, 7)).'</div>'
		.'</td></tr>';
	}
	echo "</table>";
	showtablefooter();
	showtableheader();
	showsetting('ads_hotgoods', 'hotgoods', $editvalue['hotgoods'], 'text');
	showsetting('ads_consume', 'consume', $editvalue['consume'], 'text');
	showsetting('ads_groupbuy', 'groupbuy', $editvalue['groupbuy'], 'text');
	showsetting('ads_discount', 'discount', $editvalue['discount'], 'text');
	showsetting('ads_hotshop', 'hotshop', $editvalue['hotshop'], 'text');
	showsetting('ads_sidebarconsume', 'sidebarconsume', $editvalue['sidebarconsume'], 'text');
	showsetting('ads_sidebargroupbuy', 'sidebargroupbuy', $editvalue['sidebargroupbuy'], 'text');
	showsetting('ads_sidebarshop', 'sidebarshop', $editvalue['sidebarshop'], 'text');
	showsubmit('settingsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	echo '
		<script tyle="text/javascript" charset="'.$_G['charset'].'">
		function show_ad_banner() {
			document.getElementById(\'_ads_banner\').style.display=\'\';
			document.getElementById(\'_ads_topic\').style.display=\'none\';
		}
		function show_ad_topic() {
			document.getElementById(\'_ads_banner\').style.display=\'none\';
			document.getElementById(\'_ads_topic\').style.display=\'\';
		}
			if("'.$editvalue['ads_show_type'].'"=="banner"){
					$("#ads_show_type_banner").attr("checked",true);
					show_ad_banner();
			}else{
					$("#ads_show_type_topic").attr("checked",true);
					show_ad_topic();
			}
		</script>';
	showformfooter();
	bind_ajax_form();
}

?>