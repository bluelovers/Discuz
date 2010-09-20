<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz.inc.php 4463 2010-09-15 02:00:55Z bihuizi $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

// 處理提交過來的增加和修改操作
if(submitcheck('valuesubmit')){
	$conf = file_get_contents(B_ROOT.'config.php');
	$arr_data = array();
	$arr_data = $_POST['discuz'];

	foreach($arr_data as $k=>$v){
		if(preg_match('/^db(.+?)$/',$k,$parem)){
			// 如果是密碼，判斷是不是含有＊，如果有六個，不修改
			if($k == 'dbpw') {
				if(strpos($v, '******') === false) {
					$conf = preg_replace('/\$_SC\[\'bbs_dbpw\'\].+;/','$_SC[\'bbs_dbpw\'] = \''.$v.'\';',$conf);
				}
				continue;
			}
			$conf = preg_replace('/\$_SC\[\'bbs_db'.$parem[1].'\'\].+;/','$_SC[\'bbs_db'.$parem[1].'\'] = \''.$v.'\';',$conf);
			continue;
		}
		if($k == 'url') {
			$conf = preg_replace('/\$_SC\[\'bbs_url\'\].+;/','$_SC[\'bbs_url\'] = \''.$v.'\';',$conf);
		}
		if($k == 'version') {
			$conf = preg_replace('/\$_SC\[\'bbs_version\'\].+;/','$_SC[\'bbs_version\'] = \''.$v.'\';',$conf);
		}
	}

	if(@fopen(B_ROOT.'config.php', 'w')) {
		file_put_contents(B_ROOT.'config.php', $conf);
		cpmsg('update_success', '?action=discuz');
	}else{
		cpmsg('Error : Config.php is not writeable . please repair it .');
	}
}

// 數據庫密碼顯示為＊
if(strlen($_SC['bbs_dbpw']) > 3) {
	$_SC['bbs_dbpw'] = substr($_SC['bbs_dbpw'], 0, 1) . '******' . substr($_SC['bbs_dbpw'], strlen($_SC['bbs_dbpw']) - 1);
} else {
	$_SC['bbs_dbpw'] = '******';
}

//添加或更改分類的編輯頁面
shownav('global', 'nav_discuz');
showsubmenu('nav_discuz');
showtips('nav_discuz_tips');
showformheader('discuz');
showtableheader('');
showsetting('discuz_version', array('discuz[version]', array(
				array('discuz', lang('Discuz!')),
				array('discuzx', lang('Discuz!X'))
			)), $_SC['bbs_version'], 'select');
showsetting('discuz_url', 'discuz[url]', $_SC['bbs_url'], 'text');
showsetting('discuz_dbhost', 'discuz[dbhost]', $_SC['bbs_dbhost'], 'text');
showsetting('discuz_dbuser', 'discuz[dbuser]', $_SC['bbs_dbuser'], 'text');
showsetting('discuz_dbpw', 'discuz[dbpw]', $_SC['bbs_dbpw'], 'text');
showsetting('discuz_dbname', 'discuz[dbname]', $_SC['bbs_dbname'], 'text');
showsetting('discuz_dbcharset', 'discuz[dbcharset]', $_SC['bbs_dbcharset'], 'text');
showsetting('discuz_dbpre', 'discuz[dbpre]', $_SC['bbs_dbpre'], 'text');
showsubmit('valuesubmit');
showtablefooter();
showformfooter();
bind_ajax_form();

?>