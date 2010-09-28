<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_diytemplate.php 17282 2010-09-28 09:04:15Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('edit', 'perm')) ? $operation : 'list';

shownav('portal', 'diytemplate');

if($operation == 'list') {
	$searchctrl = '<span style="float: right; padding-right: 40px;">'
					.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'\';$(\'a_search_show\').style.display=\'none\';$(\'a_search_hide\').style.display=\'\';" id="a_search_show" style="display:none">'.cplang('show_search').'</a>'
					.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'none\';$(\'a_search_show\').style.display=\'\';$(\'a_search_hide\').style.display=\'none\';" id="a_search_hide">'.cplang('hide_search').'</a>'
					.'</span>';
	showsubmenu('diytemplate',  array(
			array('list', 'diytemplate', 1),
		), $searchctrl);

	$intkeys = array('uid', 'closed');
	$strkeys = array();
	$randkeys = array();
	$likekeys = array('targettplname', 'primaltplname', 'username', 'name');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	foreach($likekeys as $k) {
		$_GET[$k] = htmlspecialchars(stripslashes($_GET[$k]));
	}
	$wherearr = $results['wherearr'];
	$mpurl = ADMINSCRIPT.'?action=diytemplate';
	$mpurl .= '&'.implode('&', $results['urls']);
	$wherearr[] = " primaltplname NOT LIKE 'portal/list%' ";
	$wherearr[] = " primaltplname NOT LIKE 'portal/portal_topic_content%' ";
	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

	$orders = getorders(array('dateline','targettplname'), 'dateline');
	$ordersql = $orders['sql'];
	if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
	$orderby = array($_GET['orderby']=>' selected');
	$ordersc = array($_GET['ordersc']=>' selected');

	$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
	if(!in_array($perpage, array(10,20,50,100))) $perpage = 20;
	$perpages = array($perpage=>' selected');

	$searchlang = array();
	$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
	'diytemplate_name', 'diytemplate_dateline', 'diytemplate_targettplname', 'diytemplate_primaltplname', 'diytemplate_uid', 'diytemplate_username', 'nolimit', 'no', 'yes');
	foreach ($keys as $key) {
		$searchlang[$key] = cplang($key);
	}

	$adminscript = ADMINSCRIPT;
	echo <<<SEARCH
	<form method="get" autocomplete="off" action="$adminscript" id="tb_search">
		<div style="margin-top:8px;">
			<table cellspacing="3" cellpadding="3">
				<tr>
					<th>$searchlang[diytemplate_name]*</th><td><input type="text" class="txt" name="name" value="$_GET[name]"></td>
					<th>$searchlang[diytemplate_targettplname]*</th><td><input type="text" class="txt" name="targettplname" value="$_GET[targettplname]"></td>
					<th>$searchlang[diytemplate_primaltplname]*</th><td><input type="text" class="txt" name="primaltplname" value="$_GET[primaltplname]">*$searchlang[likesupport]</td>
				</tr>
				<tr>
					<th>$searchlang[diytemplate_uid]</th><td><input type="text" class="txt" name="uid" value="$_GET[uid]"></td>
					<th>$searchlang[diytemplate_username]*</th><td><input type="text" class="txt" name="username" value="$_GET[username]" colspan=2></td>
				</tr>
				<tr>
					<th>$searchlang[resultsort]</th>
					<td colspan="4">
						<select name="orderby">
						<option value="">$searchlang[defaultsort]</option>
						<option value="dateline"$orderby[dateline]>$searchlang[diytemplate_dateline]</option>
						<option value="targettplname"$orderby[targettplname]>$searchlang[diytemplate_targettplname]</option>
						</select>
						<select name="ordersc">
						<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
						<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
						</select>
						<select name="perpage">
						<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
						<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
						<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
						<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
						</select>
						<input type="hidden" name="action" value="diytemplate">
						<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">
					</td>
				</tr>
			</table>
		</div>
	</form>
SEARCH;

	$start = ($page-1)*$perpage;

	$mpurl .= '&perpage='.$perpage;
	$perpages = array($perpage => ' selected');

	showformheader('diytemplate');
	showtableheader('diytemplate_list');
	showsubtitle(array('diytemplate_name', 'diytemplate_targettplname', 'diytemplate_primaltplname', 'username', 'diytemplate_dateline', 'operation'));

	$multipage = '';
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_diy_data')." WHERE $wheresql"), 0);
	if($count) {
		loadcache('diytemplatename');
		require_once libfile('function/block');
		$query = DB::query("SELECT * FROM ".DB::table('common_diy_data')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['name'] = $_G['cache']['diytemplatename'][$value['targettplname']];
			$value['dateline'] = $value['dateline'] ? dgmdate($value['dateline']) : '';
			$diyurl = block_getdiyurl($value['targettplname']);
			$diytitle = cplang($diyurl['flag'] ? 'diytemplate_share' : 'diytemplate_alone');
			showtablerow('', array('class=""', 'class=""', 'class="td28"'), array(
					"<a href=\"$diyurl[url]\" title=\"$diytitle\" target=\"_blank\">$value[name]</a>",
					'<span title="'.cplang('diytemplate_path').'./data/diy/'.$value['targettplname'].'.htm">'.$value['targettplname'].'</span>',
					'<span title="'.cplang('diytemplate_path').$_G['style']['tpldir'].'/'.$value['primaltplname'].'.htm">'.$value['primaltplname'].'</span>',
					"<a href=\"home.php?mod=space&uid=$value[uid]&do=profile\" target=\"_blank\">$value[username]</a>",
					$value[dateline],
					'<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=edit&targettplname='.$value['targettplname'].'">'.cplang('edit').'</a> '.
					'<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname='.$value['targettplname'].'">'.cplang('diytemplate_perm').'</a>',
				));
		}
		$multipage = multi($count, $perpage, $page, $mpurl);
	}

	showsubmit('', '', '', '', $multipage);
	showtablefooter();
	showformfooter();
} elseif($operation == 'edit') {
	loadcache('diytemplatename');
	$targettplname = $_G['gp_targettplname'];
	$diydata = DB::fetch_first('SELECT * FROM '.DB::table('common_diy_data')." WHERE targettplname='$targettplname'");
	if(empty($diydata)) { cpmsg_error('diytemplate_targettplname_error', dreferer());}
	if(!submitcheck('editsubmit')) {
		if(empty($diydata['name'])) $diydata['name'] = $_G['cache']['diytemplatename'][$diydata['targettplname']];
		shownav('portal', 'diytemplate', $diydata['name']);
		showsubmenu(cplang('diytemplate_edit').' - '.$diydata['name'], array(
					array('list', 'diytemplate', 0),
					array('edit', 'diytemplate&operation=edit&targettplname='.$_GET['targettplname'], 1)
				));

		showformheader("diytemplate&operation=edit&targettplname=$targettplname");
		showtableheader();
		showtitle('edit');

		showsetting('diytemplate_name', 'name', $diydata['name'],'text');
		showsetting('diytemplate_targettplname', '', '',cplang('diytemplate_path').'./data/diy/'.$diydata['targettplname'].'.htm');
		showsetting('diytemplate_primaltplname', '', '',cplang('diytemplate_path').$_G['style']['tpldir'].'/'.$diydata['primaltplname'].'.htm');
		showsetting('diytemplate_username', '', '',$diydata['username']);
		showsetting('diytemplate_dateline', '', '',$diydata['dateline'] ? dgmdate($diydata['dateline']) : '');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$editdiydata = array('name'=>$_G['gp_name']);
		DB::update('common_diy_data', $editdiydata, array('targettplname'=> $targettplname));

		include_once libfile('function/cache');
		updatecache('diytemplatename');

		cpmsg('diytemplate_edit_succeed', 'action=diytemplate', 'succeed');
	}
} elseif($operation=='perm') {
	loadcache('diytemplatename');
	$targettplname = $_G['gp_targettplname'];
	$diydata = DB::fetch_first('SELECT * FROM '.DB::table('common_diy_data')." WHERE targettplname='$targettplname'");
	if(empty($diydata)) { cpmsg_error('diytemplate_targettplname_error', dreferer());}
	if(!submitcheck('permsubmit')) {
		shownav('portal', 'diytemplate', 'diytemplate_perm');
		showsubmenu(cplang('diytemplate_perm_edit').' - '.($diydata['name'] ? cplang($diydata['name']) : $_G['cache']['diytemplatename'][$diydata['targettplname']]));
		showtips('diytemplate_perm_tips');
		showformheader("diytemplate&operation=perm&targettplname=$targettplname");
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'username',
		'<input class="checkbox" type="checkbox" name="chkallmanage" onclick="checkAll(\'prefix\', this.form, \'allowmanage\', \'chkallmanage\')" id="chkallmanage" /><label for="chkallmanage">'.cplang('block_perm_manage').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallrecommend" onclick="checkAll(\'prefix\', this.form, \'allowrecommend\', \'chkallrecommend\')" id="chkallrecommend" /><label for="chkallrecommend">'.cplang('block_perm_recommend').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallneedverify" onclick="checkAll(\'prefix\', this.form, \'needverify\', \'chkallneedverify\')" id="chkallneedverify" /><label for="chkallneedverify">'.cplang('block_perm_needverify').'</label>'
		));

		$query = DB::query("SELECT * FROM ".DB::table('common_member')." m ,".DB::table('common_template_permission')." cp WHERE cp.targettplname='$targettplname' AND cp.uid=m.uid");
		while($value = DB::fetch($query)) {
			showtablerow('', array('class="td25"'), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[$value[uid]]\" value=\"$value[uid]\" /><input type=\"hidden\" name=\"perm[$value[uid]]\" value=\"$value[bid]\" />",
				"$value[username]",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowmanage[$value[uid]]\" value=\"1\" ".($value['allowmanage'] ? 'checked' : '').' />',
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowrecommend[$value[uid]]\" value=\"1\" ".($value['allowrecommend'] ? 'checked' : '').' />',
				"<input type=\"checkbox\" class=\"checkbox\" name=\"needverify[$value[uid]]\" value=\"1\" ".($value['needverify'] ? 'checked' : '').' />',
			));
		}

		showtablerow('', array('class="td25"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newuser" value="" size="20" />',
			'<input type="checkbox" class="checkbox" name="newallowmanage" value="1" />',
			'<input type="checkbox" class="checkbox" name="newallowrecommend" value="1" />',
			'<input type="checkbox" class="checkbox" name="newneedverify" value="1" />',
		));

		showsubmit('permsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
	} else {

		if(!empty($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('common_template_permission')." WHERE targettplname='$targettplname' AND uid IN(".dimplode($_G['gp_delete']).")");
		}

		$perms = array();
		if(is_array($_G['gp_perm'])) {
			foreach($_G['gp_perm'] as $uid => $value) {
				if(empty($_G['gp_delete']) || !in_array($uid, $_G['gp_delete'])) {
					$uid = intval($uid);
					$allowmanage = $_G['gp_allowmanage'][$uid] ? 1 : 0;
					$allowrecommend = $_G['gp_allowrecommend'][$uid] ? 1 : 0;
					$needverify = $_G['gp_needverify'][$uid] ? 1 : 0;
					$perms[$uid] = "('$targettplname', '$uid', '$allowmanage', '$allowrecommend', '$needverify')";
				}
			}
		}
		if(!empty($_G['gp_newuser'])) {
			$value = DB::fetch_first("SELECT cag.allowauthorizedblock,cm.uid FROM ".DB::table('common_admingroup')." cag LEFT JOIN ".DB::table('common_member')." cm ON cm.groupid=cag.admingid WHERE cm.username='$_G[gp_newuser]' AND cag.allowauthorizedblock='1'");
			if($value) {
				$allowmanage = $_G['gp_newallowmanage'] ? 1 : 0;
				$allowrecommend = $_G['gp_newallowrecommend'] ? 1 : 0;
				$needverify = $_G['gp_newneedverify'] ? 1 : 0;
				$perms[$value['uid']] = "('$targettplname', '$value[uid]', '$allowmanage', '$allowrecommend', '$needverify')";
			} else {
				cpmsg_error($_G['gp_newuser'].cplang('block_has_no_allowauthorizedblock'), dreferer());
			}
		}
		if(!empty($perms)) {
			DB::query("REPLACE INTO ".DB::table('common_template_permission')." (`targettplname`, `uid`, `allowmanage`, `allowrecommend`, `needverify`) VALUES ".implode(',', $perms));
		}

		cpmsg('diytemplate_perm_update_succeed', "action=diytemplate&operation=perm&targettplname=$targettplname", 'succeed');
	}

}

?>