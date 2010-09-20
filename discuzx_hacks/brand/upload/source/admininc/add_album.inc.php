<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: add_album.inc.php 4442 2010-09-14 09:43:34Z yumiao $
 */

if(!defined('IN_ADMIN') && !defined('IN_STORE')) {
	exit('Acess Denied');
}

if(empty($_SGLOBAL['panelinfo']['enablealbum'])) {
	cpmsg('no_perm');
}

require_once(B_ROOT.'./source/adminfunc/album.func.php');

if(!empty($_POST['valuesubmit'])){
	$albumid = createalbum($shopid, $_POST['catid'], $_G['uid'], $_G['username'], $_POST['album']['subject'], $_POST['album']['description']);

	if($albumid > 0) {
	    itemnumreset('album', $shopid);
		$_BCACHE->deltype('sitelist', 'album');
		$_BCACHE->deltype('storelist', 'album', $shopid);
		$_BCACHE->deltype('storelist', 'photo', $shopid, $albumid);
		cpmsg('message_success', $BASESCRIPT.'?action=add&m=photo&albumid='.$albumid);
	}
}

//添加或更改的页面
shownav('infomanage', 'nav_album_add', $_SGLOBAL['panelinfo']['subject']);
showformheader('add&m=album');
showtableheader('');

showsetting('album_subject', 'album[subject]', '', 'text');
showsetting('album_description', 'album[description]', '', 'textarea');
$mycats = mymodelcategory('album');
$please_select = '<select name="album[catid]" id="album_catid" style="width:140px;"><option value="0" selected="selected">'.lang('please_select').'</option>';

foreach($mycats as $value) {
	$please_select .= '<option value="'.$value['catid'].'" >'.$value['name'].'</option>';
}
$please_select .= '</select>';
//showsetting('album_catid', 'album[catid]', '',$please_select);
echo '<tr><td class="td27" colspan="2">'.lang('shop_album_catid').'</td></tr><tr><td class="vtop rowform" id="'.$showarr['name'].'div" colspan="2">';
echo InteractionCategoryMenu(mymodelcategory('album'),'catid',null,null);
echo '</td></tr>';

showalbumattr();

showsubmit('valuesubmit');
showtablefooter();
showformfooter();
?>