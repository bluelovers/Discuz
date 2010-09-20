<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: add_photo.inc.php 3775 2010-07-16 07:46:52Z yexinhao $
 */

if(!defined('IN_ADMIN') && !defined('IN_STORE')) {
	exit('Acess Denied');
}

shownav('infomanage', 'photo_add');
showtips('photo_add_tips');
$hrefurl = $BASESCRIPT.(!empty($_GET['intask'])?'?action=index':'?action=list&m=photo');

showtableheader('');
showtablerow('', 'colspan="2" class="td27"', lang('please_select_photo'));
echo '<tr><td><div class="swfup" style="float:left;">
			<div id="swfup">
				<h1>Alternative content</h1>
				<p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
			</div>';
echo '<script type="text/javascript" src="static/image/admin/swfobject.js"></script>';
echo '<script type="text/javascript">
	swfobject.embedSWF("static/image/admin/upload.swf?config='.urlencode('misc.php?ac=swfupload&op=config&albumid='.$_GET['albumid']).'", "swfup", "100%", "400", "9.0.0", "static/image/admin/expressInstall.swf");
	function swfHandler(albumid, albumurl) {
		window.location.href = albumurl + albumid;
	}
</script>';
echo '</div></td></tr>';
showtablefooter();


?>