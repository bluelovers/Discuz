<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-09 11:25
 *	Filename: xml.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('addsubmit')) {
	$sign = daddslashes(trim($_G['gp_signnew']));
	if(!empty($sign)) {
		DB::query("INSERT INTO ".DB::table('plugin_auction_xml')." (clientid,sign) VALUES (null,'{$sign}')");
		cpmsg(lang('plugin/auction', 'xml_add_succeed'), 'action=plugins&operation=config&identifier=auction&pmod=xml', 'succeed');
	} else {
		cpmsg(lang('plugin/auction', 'xml_empty_sign'), '', 'error');
	}
} elseif(submitcheck('delsubmit')) {
	$delclientid = $_G['gp_delclientid'];
	foreach($delclientid as $key => $clientid) {
		$clientid = intval($clientid);
		if($clientid) {
			$delclientid[$key] = $clientid;
		} else {
			unset($delclientid[$key]);
		}
	}
	if($delclientid) {
		DB::query("DELETE FROM ".DB::table('plugin_auction_xml')." WHERE clientid IN(".dimplode($delclientid).")");
	}
	cpmsg(lang('plugin/auction', 'xml_delete_succeed'), 'action=plugins&operation=config&identifier=auction&pmod=xml', 'succeed');
} else {
	showtips(str_replace(array('{adminscript}', '{url}'), array(ADMINSCRIPT, $_G['siteurl'].'plugin.php?id=auction:block_xml'), lang('plugin/auction', 'auction_xml_tips')));
	showtableheader(lang('plugin/auction', 'auction_xml'));
	showformheader('plugins&operation=config&identifier=auction&pmod=xml');
	showtablerow('', array('width="5%"', 'width="10%"', 'width="85%"'), array(
		'&nbsp;',
		lang ('plugin/auction', 'xml_clientid'),
		lang('plugin/auction', 'xml_sign'),
	));
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction_xml'));
	if($count) {
		$page = intval($_G['gp_page']);
		$page = max(1, $page);
		$each = 15;
		$start = ($page - 1) * $each;
	
		$xml_query = DB::query("SELECT * FROM ".DB::table('plugin_auction_xml')." LIMIT $start,$each");
		while($xml = DB::fetch($xml_query)) {
			showtablerow('', array('width="5%"', 'width="10%"', 'width="85%"'), array(
				'<input type="checkbox" name="delclientid[]" value="'.$xml['clientid'].'" />',
				$xml['clientid'],
				$xml['sign']
				));
		}
		$multi = multi($count, $each, $page, ADMINSCRIPT.'?action=plugins&operation=config&identifier=auction&pmod=xml');
		showsubmit('delsubmit', 'delete', '', '', $multi);
	} else {
		showtablerow('', array('width="5%"', 'colspan="2"'), array('&nbsp;', lang('plugin/auction', 'no_xml')));
	}

	showformfooter();
	showtablefooter();

	showtableheader(lang('plugin/auction', 'auction_xml_add'));
	showformheader('plugins&operation=config&identifier=auction&pmod=xml');
	showtablerow('', array('width="10%"', 'width="85%"'), array(
		lang ('plugin/auction', 'xml_sign'),
		'<input type="text" class="txt" name="signnew" style="width:200px;"/>',
	));
	showsubmit('addsubmit', 'add');
	showformfooter();
	showtablefooter();
}
?>
