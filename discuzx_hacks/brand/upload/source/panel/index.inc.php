<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.inc.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}

shownav('store', 'index');

if($shop->grade == 0){
	// 待审核
	echo
	'<div class="indextext">
		<div class="welcometitle">
			<span></span>
		</div>
		<div class="wel">
			<div class="welheader"><h1>'.lang('panel_tips').'</h1></div>
			<div class="indexpostmenu"><br />
				'.lang('panel_waitmod').'
				<div style="clear:both;"></div>
			</div>
		</div>
	</div>';
}elseif($shop->grade == 1){
	// 驳回
	echo
		'<div class="indextext">
			<div class="welcometitle">
				<span></span>
			</div>
			<div class="wel">
				<div class="welheader"><h1>'.lang('panel_tips').'</h1></div>
				<div class="indexpostmenu"><br />
					'.lang('panel_fail').'
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>';
	exit;
}elseif($shop->grade == 2){
	// 关闭
	echo
	'<div class="indextext">
		<div class="welcometitle">
			<span></span>
		</div>
		<div class="wel">
			<div class="welheader"><h1>'.lang('panel_tips').'</h1></div>
			<div class="indexpostmenu"><br />
				'.lang('panel_close').'
				<div style="clear:both;"></div>
			</div>
		</div>
	</div>';
	exit;
}else{
	if(!$_G['member']['taskstatus']) {
		if(!cktask()) {
			//新手任务
			echo
			'<div class="indextext">
				<div class="welcometitle">
					<span></span>
				</div>
				<div class="wel" style="width: 500px;">
					<div class="welheader"><h1>'.lang('nav_runwizard').'</h1></div>
					<div class="welc indexpostmenu" style="height:70px;">
						'.lang('panel_intask').'
						<ul style="margin-left:180px;"><li>
						<a class="totask" href="panel.php?action=edit&m=shop&intask=1"><span>'.lang('task_start').'</span></a>
						</li></ul>

					</div>
				</div>
			</div>';
		} else {
			//完成任务
			echo
			'<div class="indextext">
				<div class="welcometitle">
					<span></span>
				</div>
				<div class="wel">
					<div class="welheader"><h1>'.lang('panel_taskdone').'</h1></div>
					<div class="indexpostmenu">
						<ul>
							<li><a href="panel.php?action=add&m=good"><span>'.lang('menu_list_addgood').'</span></a></li>
							<li><a href="panel.php?action=add&m=notice"><span>'.lang('menu_list_addnotice').'</span></a></li>
							<li><a href="panel.php?action=add&m=photo"><span>'.lang('menu_list_addphoto').'</span></a></li>
							<li><a href="panel.php?action=add&m=consume"><span>'.lang('menu_list_addconsume').'</span></a></li>
							<li><a href="panel.php?action=add&m=groupbuy"><span>'.lang('menu_list_addgroupbuy').'</span></a></li>
						</ul>
						<div style="clear:both;"></div>
					</div>
				</div>
			</div>';
		}
	} else {
		//正常页面
		echo
		'<div class="indextext">
			<div class="welcometitle">
				<span></span>
				<div class="welc">'.lang('panel_welc').'</div>
			</div>
			<div class="wel">
				<div class="welheader"><h1>'.lang('admin_shotmenu').'</h1></div>
				<div class="indexpostmenu">
					<ul>';
					foreach(array('good','notice','consume','album','groupbuy') as $contype) {
						if($_SGLOBAL['panelinfo']['enable'.$contype] > 0) {
							echo '<li><a href="panel.php?action=add&m='.$contype.'"><span>'.lang('menu_list_add'.$contype).'</span></a></li>';
						}
					}
					if($_SGLOBAL['panelinfo']['enablebrandlinks'] > 0) {
						echo '<li><a href="panel.php?action=brandlinks&op=add"><span>'.lang('menu_list_addbrandlinks').'</span></a></li>';
					}
		echo       '</ul>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>';
	}
}

function cktask() {
	global $_G, $_SGLOBAL;
	if(!empty($_SGLOBAL['panelinfo']['subjectimage'])) {
		if(!empty($_SGLOBAL['panelinfo']['banner']) && !empty($_SGLOBAL['panelinfo']['windowsimg'])) {
			$query = DB::query("UPDATE ".tname("members")." SET taskstatus = 1 WHERE uid = $_G[uid] ");
			return true;
		}
	} else {
		return false;
	}
}
?>