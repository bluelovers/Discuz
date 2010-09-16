<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_medals.php 12003 2010-06-23 07:41:55Z wangjinbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$modulelist = array();
$query = DB::query("SELECT * FROM ".DB::table('common_module')." WHERE available='1' ORDER BY displayorder");
while($module = DB::fetch($query)) {
	$modulelist[$module['mid']] = $module['name'];
}

$operation = in_array($operation, array('list', 'setting')) ? $operation : 'list';
$current = in_array($operation, array('list', 'setting')) ? array($operation => 1) : array();

if($operation == 'list') {

	if(!submitcheck('attachmentsubmit')) {
		shownav('global', 'nav_attachment');
		showsubmenu('nav_attachment', array(
			array('attachment_list', 'attachment&operation=list', $current['list']),
			array('attachment_setting', 'attachment&operation=setting', $current['setting'])
		));
		showformheader('attachment');
		showtableheader('attachment_list', 'fixpadding');
		showsubtitle(array('', 'attachment_type', 'attachment_filename', 'attachment_url', 'attachment_size', 'attachment_module'));

		$where = !empty($where) ? $where : 1;
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_attachment')." WHERE $where");

		$perpage = max(5, empty($_G['gp_perpage']) ? 50 : intval($_G['gp_perpage']));
		$start_limit = ($page - 1) * $perpage;
		$mpurl = ADMINSCRIPT."?action=attachment&operation=list";

		$multipage = multi($num, $perpage, $page, $mpurl);

		$query = DB::query("SELECT * FROM ".DB::table('common_attachment')." WHERE $where ORDER BY aid DESC LIMIT $start_limit, $perpage");
		while($attachment = DB::fetch($query)) {
			$attachment['type'] = 'attach_type_'.$attachment['type'];
			showtablerow('', array('', '', '', '', '', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$attachment[aid]\">",
				$lang[$attachment['type']],
				$attachment['filename'],
				$attachment['url'],
				sizecount($attachment['filesize']),
				(!empty($attachment['mid']) ? $modulelist[$attachment['mid']] : '')
			));
		}

		showsubmit('attachmentsubmit', 'submit', 'del', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		cpmsg('attachment_succeed', 'action=attachment&operation=list', 'succeed');

	}

} elseif($operation == 'setting') {

	$setting = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_setting'));
	while($row = DB::fetch($query)) {
		$setting[$row['skey']] = $row['svalue'];
	}

	if(!submitcheck('settingsubmit')) {
		shownav('global', 'nav_attachment');
		showsubmenu('nav_attachment', array(
			array('attachment_list', 'attachment&operation=list', $current['list']),
			array('attachment_setting', 'attachment&operation=setting', $current['setting'])
		));
		showformheader('attachment&operation=setting');
		showtableheader('attachment_setting', 'fixpadding');
		showsetting('attachment_basic_dir', 'settingnew[attachdir]', $setting['attachdir'], 'text');
		showsetting('attachment_basic_url', 'settingnew[attachurl]', $setting['attachurl'], 'text');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
	} else {
		if($_G['gp_settingnew']) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('attachdir', '".$_G['gp_settingnew']['attachdir']."')");
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('attachurl', '".$_G['gp_settingnew']['attachurl']."')");
		}

		updatecache('setting');
		cpmsg('attachment_setting_succeed', 'action=attachment&operation=setting', 'succeed');
	}


} elseif($operation == 'delete') {

}

?>