<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.
	This is NOT a freeware, use is subject to license terms

	$HeadURL: svn://localhost/trunk/discuz_x/upload/extensions/hooks/hooks_core.php $
	$Revision: 109 $
	$Author: bluelovers$
	$Date: 2010-08-02 06:22:26 +0800 (Mon, 02 Aug 2010) $
	$Id: hooks_core.php 109 2010-08-01 22:22:26Z user $
*/

/*
Scorpio_Hook::add('Func_libfile', '_eFunc_libfile');

function _eFunc_libfile(&$ret, $root, $force = 0) {
//	$root	= Scorpio_File::path($root);
//	$ret	= Scorpio_File::file($ret);
//
//	if (strpos($ret, $root) === 0) $file = substr($ret, strlen($root));

	$file = Scorpio_File::remove_root(&$ret, $root);

	static $list;

	if ($force || !isset($list[$file])) {
		if (!$force) $list[$file] = $ret;

		switch($file) {
			case 'source/function/function_cache.php':

				@include_once libfile('hooks/cache', '', 'extensions/');

				break;
			default:
//				dexit($file);

				break;
		}
	}
}
*/

Scorpio_Hook::add('Func_showmessage:Before_custom', '_eFunc_showmessage_Before_custom');

/*
 * 登入時檢查更新用戶是否有設定上傳頭像並更新狀態
 *
 * 由 http://www.discuz.net/thread-1908234-1-1.html 得知此BUG
 * 由於DiscuzX的版本中防灌水中引入了強制用戶上傳頭像
 * 結果造成論壇轉換過來的用戶標誌位有問題
 */
function _eFunc_showmessage_Before_custom($agv = array()) {
	if (in_array($agv['message'], array('login_succeed', 'login_succeed_inactive_member', 'login_activation'))) {
		if ($agv['values']['uid'] > 0) {
			$user = DB::query_first("SELECT avatarstatus, uid FROM ".DB::table('common_member')." WHERE uid='{$agv['values']['uid']}' LIMIT 1");

			if(!empty($user) && $user['uid'] && empty($user['avatarstatus']) && uc_check_avatar($user['uid'], 'middle')) {
				DB::update('common_member', array('avatarstatus'=>'1'), array('uid'=>$_G['uid']));

				updatecreditbyaction('setavatar');

				if($_G['setting']['my_app_status']) manyoulog('user', $user['uid'], 'update');
			}
		}
	}
}

?>