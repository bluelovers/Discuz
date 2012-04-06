<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blockxml.php 22455 2011-05-09 07:57:07Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('add', 'edit', 'update', 'delete')) ? $operation : 'list';
$signtypearr = array(array('',cplang('blockxml_signtype_no')), array('MD5',cplang('blockxml_signtype_md5')));
shownav('portal', 'blockxml');

if($operation == 'add') {

	if(submitcheck('addsubmit')) {
		require_once libfile('function/importdata');
		import_block($_G['gp_xmlurl'], $_G['gp_clientid'], $_G['gp_key'], $_G['gp_signtype'], $_G['gp_ignoreversion']);
		require_once libfile('function/block');
		blockclass_cache();
		cpmsg('blockxml_xmlurl_add_succeed', 'action=blockxml', 'succeed');
	} else {
		showsubmenu('blockxml',  array(
			array('list', 'blockxml', 0),
			array('add', 'blockxml&operation=add', 1)
		));

		showtips('blockxml_tips');
		showformheader('blockxml&operation=add');
		showtableheader('blockxml_add');
		showsetting('blockxml_xmlurl', 'xmlurl', '', 'text');
		showsetting('blockxml_clientid', 'clientid', $blockxml['clientid'], 'text');
		showsetting('blockxml_signtype', array('signtype', $signtypearr), $blockxml['signtype'], 'select');
		showsetting('blockxml_xmlkey', 'key', $blockxml['key'], 'text');
		echo '<tr><td colspan="2"><input class="checkbox" type="checkbox" name="ignoreversion" id="ignoreversion" value="1" /><label for="ignoreversion"> '.cplang('blockxml_import_ignore_version').'</label></td></tr>';
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'edit' && !empty($_G['gp_id'])) {

	$id = intval($_G['gp_id']);
	$blockxml = DB::fetch_first("SELECT * FROM ".DB::table('common_block_xml')." WHERE `id`='$id'");
	if(!$blockxml) {
		cpmsg('blockxml_xmlurl_notfound', '', 'error');
	}
	if(submitcheck('editsubmit')) {
		require_once libfile('function/importdata');
		import_block($_G['gp_xmlurl'], $_G['gp_clientid'], $_G['gp_key'], $_G['gp_signtype'], 1, $id);

		require_once libfile('function/block');
		blockclass_cache();
		cpmsg('blockxml_xmlurl_update_succeed', 'action=blockxml', 'succeed');
	} else {
		showsubmenu('blockxml',  array(
			array('list', 'blockxml', 0),
			array('add', 'blockxml&operation=add', 1)
		));

		showformheader('blockxml&operation=edit&id='.$id);
		showtableheader(cplang('blockxml_edit').' - '.$blockxml['name']);
		showsetting('blockxml_xmlurl', 'xmlurl', $blockxml['url'], 'text');
		showsetting('blockxml_clientid', 'clientid', $blockxml['clientid'], 'text');
		showsetting('blockxml_signtype', array('signtype', $signtypearr), $blockxml['signtype'], 'select');
		showsetting('blockxml_xmlkey', 'key', $blockxml['key'], 'text');
		showtablerow('', '', '<input class="checkbox" type="checkbox" name="ignoreversion" id="ignoreversion" value="1" /><label for="ignoreversion"> '.cplang('blockxml_import_ignore_version').'</label>');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'update' && !empty($_G['gp_id'])) {

	$id = intval($_G['gp_id']);
	$blockxml = DB::fetch_first("SELECT * FROM ".DB::table('common_block_xml')." WHERE `id`='$id'");
	if(!$blockxml) {
		cpmsg('blockxml_xmlurl_notfound', '', 'error');
	}
	require_once libfile('function/importdata');
	import_block($blockxml['url'], $blockxml['clientid'], $blockxml['key'], $blockxml['signtype'], 1, $id);

	require_once libfile('function/block');
	blockclass_cache();

	cpmsg('blockxml_xmlurl_update_succeed', 'action=blockxml', 'succeed');

} elseif($operation == 'delete' && !empty($_G['gp_id'])) {

	$id = intval($_G['gp_id']);
	if(!empty($_G['gp_confirm'])) {
		DB::delete('common_block_xml', "`id`='$id'");

		require_once libfile('function/block');
		blockclass_cache();
		cpmsg('blockxml_xmlurl_delete_succeed', 'action=blockxml', 'succeed');
	} else {
		cpmsg('blockxml_xmlurl_delete_confirm', 'action=blockxml&operation=delete&id='.$id.'&confirm=yes', 'form');
	}

} else {

	showsubmenu('blockxml',  array(
		array('list', 'blockxml', 1),
		array('add', 'blockxml&operation=add', 0)
	));
	$query = DB::query("SELECT url, name, id FROM ".DB::table('common_block_xml'));

	showtableheader('blockxml_list');
	showsubtitle(array('blockxml_name', 'blockxml_xmlurl', 'operation'));
	while($row = DB::fetch($query)) {
		showtablerow('', array('class=""', 'class=""', 'class="td28"'), array(
			$row['name'],
			$row['url'],
			"<a href=\"".ADMINSCRIPT."?action=blockxml&operation=update&id=$row[id]\">".cplang('blockxml_update')."</a>&nbsp;&nbsp;".
			"<a href=\"".ADMINSCRIPT."?action=blockxml&operation=edit&id=$row[id]\">".cplang('edit')."</a>&nbsp;&nbsp;".
			"<a href=\"".ADMINSCRIPT."?action=blockxml&operation=delete&id=$row[id]\">".cplang('delete')."</a>&nbsp;&nbsp;"
		));
	}
	showtablefooter();
	showformfooter();

}

?>