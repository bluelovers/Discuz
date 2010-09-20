<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.inc.php 4229 2010-08-19 09:21:19Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}


$newgrade = $verifygrade = array();
foreach(array('shop', 'good', 'notice', 'consume', 'album', 'groupbuy') as $type) {
	$newgrade[$type] = DB::result_first('SELECT COUNT(*) AS count FROM '.tname($type.'items').' WHERE grade=0');
	$verifygrade[$type] = DB::result_first('SELECT COUNT(*) AS count FROM '.tname($type.'items').' WHERE updateverify=1');
}
shownav('pkadmin', 'index');


echo	'<div class="indextext">
			<div class="welcometitle">
				<span></span>
				<div class="welc">'.lang('admin_welc').'</div>
				<div id="brandnews"></div>
			</div>';

if(count($newgrade)) {
	echo	'	<div class="wel">
					<div class="welheader"><h1>'.lang('menu_home_waitmod').'</h1></div>
					<div class="welc">';
					$dom ='';
					foreach($newgrade as $type=>$num) {
						echo $dom.'<a href="admin.php?action=list&m='.$type.'&grade=0&filtersubmit=GO&optpass=1">'.$num.lang('admin_wait_mod_'.$type).'</a>';
						$dom = ' || ';
					}

	echo '</div>
				</div>';
}
if(count($verifygrade)) {
	echo	'	<div class="wel">
					<div class="welheader"><h1>'.lang('menu_home_waitverify').'</h1></div>
					<div class="welc">';
					$dom ='';
					foreach($verifygrade as $type=>$num) {
						echo $dom.'<a href="admin.php?action=list&m='.$type.'&filtersubmit=GO&updatepass=1">'.$num.lang('admin_wait_verify_'.$type).'</a>';
						$dom = ' || ';
					}
	echo '</div>
				</div>';
}
echo	'	<div class="wel">
				<div class="welheader"><h1>'.lang('admin_shotmenu').'</h1></div>
				<div class="indexpostmenu">
					<ul>
						<li><a href="admin.php?action=add&m=shop"><span>'.lang('menu_list_addshop').'</span></a></li>
						<li><a href="admin.php?action=list&m=good"><span>'.lang('menu_good').'</span></a></li>
						<li><a href="admin.php?action=list&m=notice"><span>'.lang('menu_notice').'</span></a></li>
						<li><a href="admin.php?action=list&m=consume"><span>'.lang('menu_consume').'</span></a></li>
						<li><a href="admin.php?action=list&m=album"><span>'.lang('menu_album').'</span></a></li>
						<li><a href="admin.php?action=list&m=groupbuy"><span>'.lang('menu_groupbuy').'</span></a></li>
					</ul>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>';

?>