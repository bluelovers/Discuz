<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: add.inc.php 4404 2010-09-13 06:30:51Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}
if(in_array($mname, array('good', 'notice', 'consume', 'album', 'brandlinks'))) {
    if($_SGLOBAL['panelinfo']['group']['maxnum'.$mname] > 0 && $_SGLOBAL['panelinfo']['itemnum_'.$mname] >= $_SGLOBAL['panelinfo']['group']['maxnum'.$mname])
        cpmsg('toomuchitem');
}
if($mname == 'shop') {
	cpmsg('no_perm', 'panel.php?action=list&m=good');
} elseif($mname == 'photo') {
	if(empty($_SGLOBAL['panelinfo']['enablealbum'])) {
		cpmsg('no_perm');
	}
	showsubmenu('menu_list_addphoto', array(
		array('menu_album', 'list&m=album', '0'),
		array('menu_album_add', 'add&m=album', '0'),
		array('menu_list_addphoto', '', '1')
	));
	require_once(B_ROOT.'./source/admininc/add_photo.inc.php');
} elseif($mname == 'album') {
	showsubmenu('menu_list_addphoto', array(
		array('menu_album', 'list&m=album', '0'),
		array('menu_album_add', 'add&m=album', '1'),
	));
	$shopid = $_G['myshopid'];
	require_once(B_ROOT.'./source/admininc/add_album.inc.php');
} else {
	if($_SGLOBAL['panelinfo']['enable'.$mname] < 1) {
		cpmsg('no_perm');
	}
	require_once(B_ROOT.'./source/panel/edit.inc.php');
}

?>