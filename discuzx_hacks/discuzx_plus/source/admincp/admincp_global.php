<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_global.php 646 2010-09-13 03:37:40Z yexinhao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'index') {

	$setting = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_setting'));
	while($row = DB::fetch($query)) {
		$setting[$row['skey']] = $row['svalue'];
	}

	if(!submitcheck('settingsubmit')) {
		shownav('global', 'nav_basic');
		showsubmenu('nav_basic');
		showformheader('global&operation=index');
		showtableheader('basic_setting', 'fixpadding');
		showsetting('basic_bbname', 'settingnew[bbname]', $setting['bbname'], 'text');
		showsetting('basic_siteurl', 'settingnew[siteurl]', $setting['siteurl'], 'text');
		showsetting('basic_regurl', 'settingnew[regurl]', $setting['regurl'], 'text');
		showsetting('basic_autoactivationuser', 'settingnew[autoactivationuser]', $setting['autoactivationuser'], 'radio');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
	} else {
		if($_G['gp_settingnew']) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('bbname', '".$_G['gp_settingnew']['bbname']."')");
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('siteurl', '".$_G['gp_settingnew']['siteurl']."')");
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('regurl', '".$_G['gp_settingnew']['regurl']."')");
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('autoactivationuser', '".intval($_G['gp_settingnew']['autoactivationuser'])."')");
		}

		updatecache('setting');
		cpmsg('basic_succeed', 'action=global&operation=index', 'succeed');
	}

} elseif($operation == 'cache') {

	shownav('global', 'nav_other');
	showsubmenu('nav_other', array(
		array('other_database', 'global&operation=database', 0),
		array('other_cache', 'global&operation=cache', 1)
	));

	updatecache();
	cpmsg('cache_succeed', '', 'succeed');

} elseif($operation == 'database') {

	shownav('global', 'nav_other');
	showsubmenu('nav_other', array(
		array('other_database', 'global&operation=database', 1),
		array('other_cache', 'global&operation=cache', 0),
	));

	$checkperm = checkpermission('runquery', 0);

	if($checkperm) {

		$db = & DB::object();

		//note 兼容性判斷
		$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';
		$tablepre = $_G['config']['db'][1]['tablepre'];
		$dbcharset = $_G['config']['db'][1]['dbcharset'];

		if(!submitcheck('sqlsubmit')) {
			showtableheader('db_runquery_sql', 'fixpadding');
			showformheader('global&operation=database');
			showsetting('', '', '', '<textarea cols="85" rows="10" name="queries" style="width:500px;">'.(!empty($queries) ? $queries : '').'</textarea>');
			showsetting('', '', '', '<input type="checkbox" class="checkbox" name="createcompatible" value="1" checked="checked" />'.cplang('db_runquery_createcompatible'));
			showsubmit('sqlsubmit', 'submit', '', cplang('db_runquery_comment'));
			showformfooter();
			showtablefooter();
		} else {
			$queries = $_G['gp_queries'];
			$sqlquery = splitsql(str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre, ' '.$tablepre, ' `'.$tablepre), $queries));
			$affected_rows = 0;
			foreach($sqlquery as $sql) {
				if(trim($sql) != '') {
					$sql = !empty($_G['gp_createcompatible']) ? syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset) : $sql;

					DB::query(dstripslashes($sql), 'SILENT');
					if($sqlerror = DB::error()) {
						break;
					} else {
						$affected_rows += intval(DB::affected_rows());
					}
				}
			}
			$sqlerror ? cpmsg('database_run_query_invalid', '', 'error', array('sqlerror' => $sqlerror)) : cpmsg('database_run_query_succeed', '', 'succeed', array('affected_rows' => $affected_rows));
		}
	} else {
		 cpmsg('function_config', '', 'error');
	}
}

?>