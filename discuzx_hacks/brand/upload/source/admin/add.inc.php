<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: add.inc.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

if($mname!='shop') {
	if(!empty($_GET['shopid'])) {
		ssetcookie('shopid', $_GET['shopid'], 3600 * 10);
		getpanelinfo($_GET['shopid']);
		if(!empty($_G['cookie']['i_referer'])) {
			header('Location: '.$_G['cookie']['i_referer']);
		}
	} elseif(!empty($_G['cookie']['shopid'])) {
		getpanelinfo(intval($_G['cookie']['shopid']));
	}

	if(!empty($_SGLOBAL['panelinfo'])) {
		echo '<script type="text/javascript" charset="'.$_G['charset'].'">var leftmenu = $(window.parent.document).find("#leftmenu");leftmenu.find("ul").css("display", "none");$(window.parent.document).find("#menu_paneladd").css("display", "");</script>';
		if($_SGLOBAL['panelinfo']['enable'.$mname] < 1) {
			cpmsg('noaccess');
		}
		if(in_array($mname, array('good', 'notice', 'consume', 'album', 'brandlinks'))) {
			if(!empty($_SGLOBAL['panelinfo']['group']['maxnum'.$mname]) && $_SGLOBAL['panelinfo']['itemnum_'.$mname] >= $_SGLOBAL['panelinfo']['group']['maxnum'.$mname]) {
				if($mname != 'album' || !empty($_POST['valuesubmit'])) {	
					cpmsg('toomuchitem');
				}
			}
		}
	}
}

if($mname=='photo') {
	showsubmenu('menu_album_add', array(
		array('menu_album_add', 'add&m=album', '0'),
		array('menu_list_addphoto', 'list&m=album&from=addphoto', '1')
	));
	require_once(B_ROOT.'./source/admininc/add_photo.inc.php');
} elseif($mname == 'album') {
	showsubmenu('menu_album_add', array(
		array('menu_album_add', 'add&m=album', '1'),
		array('menu_list_addphoto', 'list&m=album&from=addphoto', '0'),
		array('menu_photo_import', 'import&m=album', '0')
	));
	$shopid = intval($_G['cookie']['shopid']);
	require_once(B_ROOT.'./source/admininc/add_album.inc.php');
} elseif($mname == 'brandlinks') {
	require_once(B_ROOT.'./source/admin/brandlinks.inc.php');
} else {
	require_once(B_ROOT.'./source/admin/edit.inc.php');
}

?>