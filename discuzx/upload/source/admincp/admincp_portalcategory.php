<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_portalcategory.php 17392 2010-10-18 01:47:45Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('delete', 'move', 'perm', 'add', 'edit')) ? $operation : 'list';

loadcache('portalcategory');
$portalcategory = $_G['cache']['portalcategory'];

if($operation == 'list') {

	if(!submitcheck('editsubmit')) {

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
					array('list', 'portalcategory', 1)
				));

		showformheader('portalcategory');
		echo '<div style="height:30px;line-height:30px;"><a href="javascript:;" onclick="show_all()">'.cplang('show_all').'</a> | <a href="javascript:;" onclick="hide_all()">'.cplang('hide_all').'</a> <input type="text" id="srchforumipt" class="txt" /> <input type="submit" class="btn" value="'.cplang('search').'" onclick="return srchforum()" /></div>';
		showtableheader('', '', ' style="min-width:910px; _width:910px;"');
		showsubtitle(array('', '', 'portalcategory_name', 'portalcategory_articles', 'portalcategory_allowpublish', 'portalcategory_allowcomment', 'portalcategory_is_closed', 'setindex', 'operation', 'portalcategory_article_op'));
		foreach ($portalcategory as $key=>$value) {
			if($value['level'] == 0) {
				echo showcategoryrow($key, 0, '');
			}
		}
		echo '<tbody><tr><td>&nbsp;</td><td colspan="6"><div><a class="addtr" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid=0">'.cplang('portalcategory_addcategory').'</a></div></td><td colspan="3">&nbsp;</td></tr></tbody>';
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

		$langs = array();
		$keys = array('portalcategory_addcategory', 'portalcategory_addsubcategory', 'portalcategory_addthirdcategory');
		foreach ($keys as $key) {
			$langs[$key] = cplang($key);
		}
		echo <<<SCRIPT
<script type="text/Javascript">
var rowtypedata = [
	[[1,'', ''], [4, '<div class="parentboard"><input type="text" class="txt" value="$lang[portalcategory_addcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [4, '<div class="board"><input type="text" class="txt" value="$lang[portalcategory_addsubcategory]" name="newname[{1}][]"/>  <input type="checkbox" name="newinheritance[{1}][]" value="1" checked>$lang[portalcategory_inheritance]</div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [4, '<div class="childboard"><input type="text" class="txt" value="$lang[portalcategory_addthirdcategory]" name="newname[{1}][]"/> <input type="checkbox" name="newinheritance[{1}][]" value="1" checked>$lang[portalcategory_inheritance]</div>']],
];
</script>
SCRIPT;

	} else {

		if($_POST['name']) {
			$openarr = $closearr = array();
			foreach($_POST['name'] as $key=>$value) {
				$sets = array();
				$value = trim($value);
				if($portalcategory[$key] && $portalcategory[$key]['catname'] != $value) {
					$sets[] = "catname='$value'";
				}
				if($portalcategory[$key] && $portalcategory[$key]['displayorder'] != $_POST['neworder'][$key]) {
					$sets[] = "displayorder='{$_POST['neworder'][$key]}'";
				}
				if($sets) {
					DB::query('UPDATE '.DB::table('portal_category')." SET ".implode(',',$sets)." WHERE catid = '$key'");
					DB::update('common_diy_data',array('name'=>$value),array('targettplname'=>'portal/list_'.$key));
				}
			}
		}

		if($_G['gp_newsetindex']) {
			DB::insert('common_setting', array('skey' => 'defaultindex', 'svalue' => $portalcategory[$_G['gp_newsetindex']]['caturl']), 0, 1);
		}
		include_once libfile('function/cache');
		updatecache(array('portalcategory','diytemplatename'));

		cpmsg('portalcategory_update_succeed', 'action=portalcategory', 'succeed');
	}

} elseif($operation == 'perm') {

	$catid = intval($_G['gp_catid']);
	if(!submitcheck('permsubmit')) {
		$category = DB::fetch_first('SELECT * FROM '.DB::table('portal_category')." WHERE catid='$catid'");
		shownav('portal', 'portalcategory');
		$upcat = $category['upid'] ? ' - <a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$category['upid'].'">'.$portalcategory[$category['upid']]['catname'].'</a> ' : '';
		showsubmenu('<a href="'.ADMINSCRIPT.'?action=portalcategory">'.cplang('portalcategory_perm_edit').'</a>'.$upcat.' - '.$category['catname']);
		showtips('portalcategory_article_perm_tips');
		showformheader("portalcategory&operation=perm&catid=$catid");

		showtableheader('', 'fixpadding');

		$inherited_checked = !$category['notinheritedarticle'] ? 'checked' : '';
		if($portalcategory[$catid]['level'])showsubtitle(array('','<input class="checkbox" type="checkbox" name="inherited" value="1" '.$inherited_checked.'/>'.cplang('portalcategory_inheritance'),'',''));
		showsubtitle(array('', 'username',
		'<input class="checkbox" type="checkbox" name="chkallpublish" onclick="checkAll(\'prefix\', this.form, \'publish\', \'chkallpublish\')" id="chkallpublish" /><label for="chkallpublish">'.cplang('portalcategory_perm_publish').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallmanage" onclick="checkAll(\'prefix\', this.form, \'manage\', \'chkallmanage\')" id="chkallmanage" /><label for="chkallmanage">'.cplang('portalcategory_perm_manage').'</label>'
		));

		$query = DB::query("SELECT m.*, cp.* FROM ".DB::table('common_member')." m ,".DB::table('portal_category_permission')." cp WHERE cp.catid='$catid' AND cp.uid=m.uid");
		while($value = DB::fetch($query)) {
			showtablerow('', array('class="td25"'), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[$value[uid]]\" value=\"$value[uid]\" /><input type=\"hidden\" name=\"perm[$value[uid]]\" value=\"$value[catid]\" />",
				"$value[username]",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowpublish[$value[uid]]\" value=\"1\" ".($value['allowpublish'] ? 'checked' : '').' />',
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowmanage[$value[uid]]\" value=\"1\" ".($value['allowmanage'] ? 'checked' : '').' />',
			));
		}
		showtablerow('', array('class="td25"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newuser" value="" size="20" />',
			'<input type="checkbox" class="checkbox" name="newpublish" value="1" />',
			'<input type="checkbox" class="checkbox" name="newmanage" value="1" />',
		));

		showsubmit('permsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
	} else {

		if(!empty($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('portal_category_permission')." WHERE catid='$catid' AND uid IN(".dimplode($_G['gp_delete']).")");
		} else {

		}
		$perms = array();
		if(is_array($_G['gp_perm'])) {
			foreach($_G['gp_perm'] as $uid => $value) {
				if(empty($_G['gp_delete']) || !in_array($uid, $_G['gp_delete'])) {
					$uid = intval($uid);
					$publish = $_G['gp_allowpublish'][$uid] ? 1 : 0;
					$manage = $_G['gp_allowmanage'][$uid] ? 1 : 0;
					$perms[] = "('$catid', '$uid', '$publish', '$manage')";
				}
			}
		}
		if(!empty($_G['gp_newuser'])) {
			$value = DB::fetch_first("SELECT cag.allowauthorizedarticle,cm.uid FROM ".DB::table('common_admingroup')." cag LEFT JOIN ".DB::table('common_member')." cm ON cm.groupid=cag.admingid WHERE cm.username='$_G[gp_newuser]' AND cag.allowauthorizedarticle='1'");
			if($value) {
				$publish = $_G['gp_newpublish'] ? 1 : 0;
				$manage = $_G['gp_newmanage'] ? 1 : 0;
				$perms[] = "('$catid', '$value[uid]', '$publish', '$manage')";
			} else {
				cpmsg_error($_G['gp_newuser'].cplang('portalcategory_has_no_allowauthorizedarticle'));
			}
		}
		if(!empty($perms)) {
			DB::query("REPLACE INTO ".DB::table('portal_category_permission')." (`catid`, `uid`, `allowpublish`, `allowmanage`) VALUES ".implode(',', $perms));
		}

		DB::update('portal_category', array('notinheritedarticle'=>$_POST['inherited'] ? '0' : '1'), array('catid'=>$catid));

		include_once libfile('function/cache');
		updatecache('portalcategory');

		cpmsg('portalcategory_perm_update_succeed', "action=portalcategory&operation=perm&catid=$catid", 'succeed');
	}

} elseif($operation == 'delete') {

	$_GET['catid'] = max(0, intval($_GET['catid']));
	if(!$_GET['catid'] || !$portalcategory[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}
	$catechildren = $portalcategory[$_GET['catid']]['children'];
	include_once libfile('function/cache');
	if(!submitcheck('deletesubmit')) {
		$article_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_GET[catid]'");
		if(!$article_count && empty($catechildren)) {

			if($portalcategory[$_GET['catid']]['foldername']) delportalcategoryfolder($_GET['catid']);

			deleteportalcategory($_GET['catid']);
			updatecache(array('portalcategory','diytemplatename'));
			cpmsg('portalcategory_delete_succeed', 'action=portalcategory', 'succeed');
		}

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
					array('list', 'portalcategory', 0),
					array('delete', 'portalcategory&operation=delete&catid='.$_GET['catid'], 1)
				));

		showformheader('portalcategory&operation=delete&catid='.$_GET['catid']);
		showtableheader();
		if($portalcategory[$_GET[catid]]['children']) {
			showsetting('portalcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('portalcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('portalcategory_subcategory_moveto_parent').'</label>'
			);
		}
		include_once libfile('function/portalcp');
		echo "<tr><td colspan=\"2\" class=\"td27\">".cplang('portalcategory_article').":</td></tr>
				<tr class=\"noborder\">
					<td class=\"vtop rowform\">
						<ul class=\"nofloat\" onmouseover=\"altStyle(this);\">
						<li class=\"checked\"><input class=\"radio\" type=\"radio\" name=\"article_op\" value=\"move\" checked />&nbsp;".cplang('portalcategory_article_moveto')."&nbsp;&nbsp;&nbsp;".category_showselect('portal', 'tocatid', false, $portalcategory[$_GET['catid']]['upid'])."</li>
						<li><input class=\"radio\" type=\"radio\" name=\"article_op\" value=\"delete\" />&nbsp;".cplang('portalcategory_article_delete')."</li>
						</ul></td>
					<td class=\"vtop tips2\"></td>
				</tr>";

		showsubmit('deletesubmit', 'portalcategory_delete');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['article_op'] == 'delete') {
			if(!$_G['gp_confirmed']) {
				cpmsg('portal_delete_confirm', "action=portalcategory&operation=delete&catid=$_GET[catid]", 'form', array(),
				'<input type="hidden" class="btn" id="deletesubmit" name="deletesubmit" value="1" /><input type="hidden" class="btn" id="subcat_op" name="subcat_op" value="'.$_POST[subcat_op].'" />
					<input type="hidden" class="btn" id="article_op" name="article_op" value="delete" /><input type="hidden" class="btn" id="tocatid" name="tocatid" value="'.$_POST[tocatid].'" />');
			}
		}

		if($_POST['article_op'] == 'move') {
			if($_POST['tocatid'] == $_GET['catid'] || empty($portalcategory[$_POST['tocatid']])) {
				cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
			}
		}

		$delids = array($_GET['catid']);
		$updatecategoryfile = array();
		if($catechildren) {
			if($_POST['subcat_op'] == 'parent') {
				$upid = intval($portalcategory[$_GET['catid']]['upid']);
				if(!empty($portalcategory[$upid]['foldername']) || ($portalcategory[$_GET['catid']]['level'] == '0' && $portalcategory[$_GET['catid']]['foldername'])) {
					$parentdir = DISCUZ_ROOT.'/'.getportalcategoryfulldir($upid);
					foreach($catechildren as $subcatid) {
						if($portalcategory[$subcatid]['foldername']) {
							$olddir = DISCUZ_ROOT.'/'.getportalcategoryfulldir($subcatid);
							rename($olddir, $parentdir.$portalcategory[$subcatid]['foldername']);
							$updatecategoryfile[] = $subcatid;
						}
					}
				}
				DB::query('UPDATE '.DB::table('portal_category')." SET upid = '$upid' WHERE catid IN (".dimplode($catechildren).')');

			} else {
				$delids = array_merge($delids, $catechildren);
				foreach ($catechildren as $id) {
					$value = $portalcategory[$id];
					if($value['children']) {
						$delids = array_merge($delids, $value['children']);
					}
				}
				if($_POST['article_op'] == 'move') {
					if(!$portalcategory[$_POST['tocatid']] || in_array($_POST['tocatid'], $delids)) {
						cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
					}
				}
			}
		}

		if($delids) {
			deleteportalcategory($delids);
			if($_POST['article_op'] == 'delete') {
				require_once libfile('function/delete');
				$aidarr = array();
				$query = DB::query("SELECT aid FROM ".DB::table('portal_article_title')." WHERE catid IN (".dimplode($delids).")");
				while($value = DB::fetch($query)) {
					$aidarr[] = $value['aid'];
				}
				if($aidarr) {
					deletearticle($aidarr, '0');
				}
			} else {
				DB::update('portal_article_title', array('catid'=>$_POST['tocatid']), 'catid IN ('.dimplode($delids).')');
				$num = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_POST[tocatid]'");
				DB::update('portal_category', array('articles'=>$num), array('catid'=>$_POST['tocatid']));
			}
		}

		if($portalcategory[$_GET['catid']]['foldername']) delportalcategoryfolder($_GET['catid']);
		updatecache(array('portalcategory','diytemplatename'));
		loadcache('portalcategory', true);
		remakecategoryfile($updatecategoryfile);
		cpmsg('portalcategory_delete_succeed', 'action=portalcategory', 'succeed');
	}

} elseif($operation == 'move') {

	if(!$_GET['catid'] || !$portalcategory[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('movesubmit')) {
		$article_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_GET[catid]'");
		if(!$article_count) {
			cpmsg('portalcategory_move_empty_error', 'action=portalcategory', 'succeed');
		}

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
					array('list', 'portalcategory', 0),
					array('portalcategory_move', 'portalcategory&operation=move&catid='.$_GET['catid'], 1)
				));

		showformheader('portalcategory&operation=move&catid='.$_GET['catid']);
		showtableheader();
		include_once libfile('function/portalcp');
		showsetting('portalcategory_article_moveto', '', '', category_showselect('portal', 'tocatid', false, $portalcategory[$_GET['catid']]['upid']));
		showsubmit('movesubmit', 'portalcategory_move');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid'] || empty($portalcategory[$_POST['tocatid']])) {
			cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
		}

		DB::query('UPDATE '.DB::table('portal_article_title')." SET catid = '$_POST[tocatid]' WHERE catid ='$_GET[catid]'");
		DB::update('portal_category', array('articles'=>0), array('catid'=>$_GET['catid']));
		$num = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_POST[tocatid]'");
		DB::update('portal_category', array('articles'=>$num), array('catid'=>$_POST['tocatid']));

		cpmsg('portalcategory_move_succeed', 'action=portalcategory', 'succeed');
	}
} elseif($operation == 'edit' || $operation == 'add') {

	if($_GET['catid'] && !$portalcategory[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}

	$cate = $_GET['catid'] ? $portalcategory[$_GET['catid']] : array();
	if($operation == 'add') {
		if($_G['gp_upid']) {
			$cate['level'] = $portalcategory[$_G['gp_upid']] ? $portalcategory[$_G['gp_upid']]['level']+1 : 0;
			$cate['upid'] = intval($_G['gp_upid']);
		} else {
			$cate['level'] = 0;
			$cate['upid'] = 0;
		}
		$cate['displayorder'] = 0;
		$cate['closed'] = 1;
	}
	@include_once DISCUZ_ROOT.'./data/cache/cache_domain.php';
	$channeldomain = isset($rootdomain['channel']) && $rootdomain['channel'] ? $rootdomain['channel'] : array();

	if(!submitcheck('detailsubmit')) {
		shownav('portal', 'portalcategory');
		$url = 'portalcategory&operation='.$operation.($operation == 'add' ? '&upid='.$_G['gp_upid'] : '&catid='.$_GET['catid']);
		showsubmenu(cplang('portalcategory_detail').($cate['catname'] ? ' - '.$cate['catname'] : ''), array(
					array('list', 'portalcategory', 0),
					array('edit', $url, 1)
				));

		showformheader($url);
		showtableheader();
		$catemsg = '';
		if($cate['username']) $catemsg .= $lang['portalcategory_username'].' '.$cate['username'];
		if($cate['dateline']) $catemsg .= ' '.$lang['portalcategory_dateline'].' '.dgmdate($cate['dateline'],'Y-m-d m:i:s');
		if($cate['upid']) $catemsg .= ' '.$lang['portalcategory_upname'].': <a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$cate['upid'].'">'.$portalcategory[$cate['upid']]['catname'].'</a>';
		if($catemsg) showtitle($catemsg);
		showsetting('portalcategory_catname', 'catname', html_entity_decode($cate['catname']), 'text');
		showsetting('display_order', 'displayorder', $cate['displayorder'], 'text');
		showsetting('portalcategory_foldername', 'foldername', $cate['foldername'], 'text');
		showsetting('portalcategory_url', 'url', $cate['url'], 'text');

		$tpls = array('list'=>getprimaltplname('list.htm'));
		if (($dh = opendir(DISCUZ_ROOT.'./template/default/portal'))) {
			while(($file = readdir($dh)) !== false) {
				$file = strtolower($file);
				if (fileext($file) == 'htm' && substr($file, 0, 5) == 'list_') {
					$tpls[str_replace('.htm','',$file)] = getprimaltplname($file);
				}
			}
			closedir($dh);
		}
		asort($tpls);

		$pritplvalue = '';
		if(empty($cate['primaltplname'])) {
			$pritplhide = '';
			$cate['primaltplname'] = 'portal/list';
			$pritplvalue = ' style="display:none;"';
		} else {
			$pritplhide = ' style="display:none;"';
		}
		$catetplselect = '<span id="pritplselect"'.$pritplhide.'><select name="primaltplname">';
		foreach($tpls as $k => $v){
			$selected = $cate['primaltplname'] == 'portal/'.$k ? ' selected' : '';
			$catetplselect .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
		}
		$pritplophide = !empty($cate['primaltplname']) ? '' : ' style="display:none;"';
		$catetplselect .= '</select> <a href="javascript:;"'.$pritplophide.' onclick="$(\'pritplselect\').style.display=\'none\';$(\'pritplvalue\').style.display=\'\';">'.cplang('cancel').'</a></span>';

		if(empty($cate['primaltplname'])) {
			showsetting('portalcategory_primaltplname', '', '', $catetplselect);
		} else {
			$tplname = getprimaltplname(str_replace('portal/', '', $cate['primaltplname'].'.htm'));
			$html = '<span id="pritplvalue" '.$pritplvalue.'> '.$tplname.'<a href="javascript:;" onclick="$(\'pritplselect\').style.display=\'\';$(\'pritplvalue\').style.display=\'none\';"> '.cplang('modify').'</a></span>';
			showsetting('portalcategory_primaltplname', '', '', $catetplselect.$html);
		}

		showsetting('portalcategory_allowpublish', 'allowpublish', $cate['disallowpublish'] ? 0 : 1, 'radio');
		showsetting('portalcategory_allowcomment', 'allowcomment', $cate['allowcomment'], 'radio');
		if($cate['level']) {
			showsetting('portalcategory_inheritancearticle', 'inheritancearticle', !$cate['notinheritedarticle'] ? '1' : '0', 'radio');
			showsetting('portalcategory_inheritanceblock', 'inheritanceblock', !$cate['notinheritedblock'] ? '1' : '0', 'radio');
		}
		showsetting('portalcategory_is_closed', 'closed', $cate['closed'] ? 0 : 1, 'radio');
		if($cate['level'] != 2) showsetting('portalcategory_shownav', 'shownav', $cate['shownav'], 'radio');
		$setindex = !empty($_G['setting']['defaultindex']) && $_G['setting']['defaultindex'] == $cate['caturl'] ? 1 : 0;
		showsetting('setindex', 'setindex', $setindex, 'radio');
		if($cate['level'] == 0 && !empty($_G['setting']['domain']['root']['channel'])) {
			showsetting('forums_edit_extend_domain', '', '', 'http://<input type="text" class="txt" name="domain" class="txt" value="'.$cate['domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['channel']);
		}

		showsetting('portalcategory_summary', 'description', $cate['description'], 'textarea');
		showsetting('portalcategory_keyword', 'keyword', $cate['keyword'], 'textarea');

		showsubmit('detailsubmit');
		if($operation == 'add') showsetting('', '', '', '<input type="hidden" name="level" value="'.$cate['level'].'" />');
		showtablefooter();
		showformfooter();

	} else {
		require_once libfile('function/portalcp');
		$domain = $_G['gp_domain'] ? $_G['gp_domain'] : '';
		$_G['gp_closed'] = intval($_G['gp_closed']) ? 0 : 1;
		$_G['gp_catname'] = trim($_G['gp_catname']);
		$foldername = trim($_G['gp_foldername']);
		$oldsetindex = !empty($_G['setting']['defaultindex']) && $_G['setting']['defaultindex'] == $cate['caturl'] ? 1 : 0;

		if($_G['gp_catid'] && !empty($cate['domain'])) {
			require_once libfile('function/delete');
			deletedomain($_G['gp_catid'], 'channel');
		}
		if(!empty($domain)) {
			require_once libfile('function/domain');
			domaincheck($domain, $_G['setting']['domain']['root']['channel'], 1);
		}

		$updatecategoryfile = array();

		$editcat = array(
			'catname' => $_G['gp_catname'],
			'allowcomment'=>$_G['gp_allowcomment'],
			'url' => $_G['gp_url'],
			'closed' => $_G['gp_closed'],
			'keyword' => $_G['gp_keyword'],
			'description' => $_G['gp_description'],
			'keyword' => $_G['gp_keyword'],
			'displayorder' => intval($_G['gp_displayorder']),
			'notinheritedarticle' => $_G['gp_inheritancearticle'] ? '0' : '1',
			'notinheritedblock' => $_G['gp_inheritanceblock'] ? '0' : '1',
			'disallowpublish' => $_G['gp_allowpublish'] ? '0' : '1',
		);

		$dir = '';
		if(!empty($foldername)) {
			preg_match_all('/[^\w\d\_]/',$foldername,$re);
			if(!empty($re[0])) {
				cpmsg(cplang('portalcategory_foldername_rename_error').','.cplang('return'), NULL, 'error');
			}
			$parentdir = getportalcategoryfulldir($cate['upid']);
			if($parentdir === false) cpmsg(cplang('portalcategory_parentfoldername_empty').','.cplang('return'), NULL, 'error');
			$isexists = DB::fetch_first("SELECT catid, upid FROM ".DB::table('portal_category')." WHERE foldername='$foldername'");
			$_GET['upid'] = isset($_GET['upid']) ? $_GET['upid'] : $cate['upid'];
			if($isexists && $isexists['upid'] == $_GET['upid']) {
				cpmsg(cplang('portalcategory_foldername_duplicate').','.cplang('return'), NULL, 'error');
			} elseif(!$isexists && is_dir(DISCUZ_ROOT.'./'.$parentdir.$foldername)) {
				cpmsg(cplang('portalcategory_foldername_exists').','.cplang('return'), NULL, 'error');
			} elseif (!$isexists && $portalcategory[$_GET['catid']]['foldername']) {
				$r = rename(DISCUZ_ROOT.'./'.$parentdir.$portalcategory[$_GET['catid']]['foldername'], DISCUZ_ROOT.'./'.$parentdir.$foldername);
				if($r) {
					$updatecategoryfile[] = $_GET['catid'];
					$editcat['foldername'] = $foldername;
				} else {
					cpmsg(cplang('portalcategory_foldername_rename_error').','.cplang('return'), NULL, 'error');
				}
			} elseif (!$isexists && empty($portalcategory[$_GET['catid']]['foldername'])) {
				$dir = $parentdir.$foldername;
				$editcat['foldername'] = $foldername;
			} elseif ($isexists && $isexists['catid'] == $_GET['catid']) {
				$dir = $parentdir.$foldername;
			}
		} elseif(empty($foldername) && $portalcategory[$_GET['catid']]['foldername']) {
			delportalcategoryfolder($_GET['catid']);
			$editcat['foldername'] = '';
		}

		$primaltplname = $_G['gp_primaltplname'];
		if(!$primaltplname || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $primaltplname)) {
			return 'portalcategory_filename_invalid';
		}
		$primaltplname = 'portal/'.str_replace(array('/', '\\', '.'), '', $primaltplname);
		checkprimaltpl($primaltplname);
		$editcat['primaltplname'] = $primaltplname;

		if($_G['gp_catid']) {
			if($portalcategory[$_G['catid']]['level'] < 2) $editcat['shownav'] = intval($_G['gp_shownav']);
			if($domain && $portalcategory[$_G['catid']]['level'] == 0) {
				$editcat['domain'] = $domain;
			} else {
				$editcat['domain'] = '';
			}
		} else {
			if($portalcategory[$cate['upid']]) {
				if($portalcategory[$cate['upid']]['level'] == 0) $editcat['shownav'] = intval($_G['gp_shownav']);
			} else {
				$editcat['shownav'] = intval($_G['gp_shownav']);
				$editcat['domain'] = $domain;
			}
		}
		$cachearr = array();
		if($_G['gp_catid']) {
			DB::update('portal_category', $editcat, array('catid'=>$cate['catid']));
			if($cate['catname'] != $_G['gp_catname']) {
				DB::update('common_diy_data',array('name'=>$_G['gp_catname']),array('targettplname'=>'portal/list_'.$cate['catid']));
				$cachearr[] = 'diytemplatename';
			}
		} else {
			$editcat['upid'] = $cate['upid'];
			$editcat['dateline'] = TIMESTAMP;
			$editcat['uid'] = $_G['uid'];
			$editcat['username'] = $_G['username'];
			$_G['gp_catid'] = DB::insert('portal_category', $editcat, TRUE);
			$cachearr[] = 'diytemplatename';
		}

		if(!empty($domain)) {
			DB::insert('common_domain', array('domain' => $domain, 'domainroot' => addslashes($_G['setting']['domain']['root']['channel']), 'id' => $_G['gp_catid'], 'idtype' => 'channel'));
		}
		if($_G['gp_primaltplname'] && (empty($cate['primaltplname']) || $cate['primaltplname'] != 'portal/'.$_G['gp_primaltplname'])) {
			$targettplname = 'portal/list_'.$_G['gp_catid'];
			$diydata = DB::fetch_first("SELECT diycontent FROM ".DB::table('common_diy_data')." WHERE targettplname='portal/list_{$_G['gp_catid']}'");
			$diycontent = empty($diydata['diycontent']) ? '' : $diydata['diycontent'];
			if($diydata) {
				DB::update('common_diy_data',array('primaltplname'=>$primaltplname),array('targettplname'=>$targettplname));
			} else {
				$diycontent = '';
				if($primaltplname == 'portal/list') {
					$diydata = DB::fetch_first("SELECT diycontent FROM ".DB::table('common_diy_data')." WHERE targettplname='portal/list'");
					$diycontent = empty($diydata['diycontent']) ? '' : $diydata['diycontent'];
				}
				$diyarr = array(
					'primaltplname' => $primaltplname,
					'targettplname' => $targettplname,
					'diycontent' => addslashes($diycontent),
					'name' => $_G['gp_catname'],
					'uid' => $_G['uid'],
					'username' => $_G['username'],
					'dateline' => TIMESTAMP,
					);
				DB::insert('common_diy_data',$diyarr);
			}
			if(empty($diycontent)) {
				$file = ($_G['cache']['style_default']['tpldir'] ? $_G['cache']['style_default']['tpldir'] : './template/default').'/'.$primaltplname.'.htm';
				if (!file_exists($file)) {
					$file = './template/default/'.$primaltplname.'.htm';
				}
				$content = @file_get_contents(DISCUZ_ROOT.$file);
				if(!$content) $content = '';
				$content = preg_replace("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>/i", '', $content);
				file_put_contents(DISCUZ_ROOT.'./data/diy/'.$targettplname.'.htm', $content);
			} else {
				updatediytemplate($targettplname);
			}
		}

		include_once libfile('function/cache');
		updatecache('portalcategory');
		loadcache('portalcategory',true);
		$portalcategory = $_G['cache']['portalcategory'];

		if(!empty($updatecategoryfile)) {
			remakecategoryfile($updatecategoryfile);
		}

		if($dir) {
			if(!makecategoryfile($dir, $_G['gp_catid'], $domain)) {
				cpmsg(cplang('portalcategory_filewrite_error').','.cplang('return'), NULL, 'error');
			}
			remakecategoryfile($portalcategory[$_G['gp_catid']]['children']);
		}

		if(($_G['gp_catid'] && $cate['level'] < 2) || empty($_G['gp_upid']) || ($_G['gp_upid'] && $portalcategory[$_G['gp_upid']]['level'] == 0)) {
			$nav = DB::fetch_first("SELECT * FROM ".DB::table('common_nav')." WHERE `type`='4' AND identifier='$_G[gp_catid]'");
			if($editcat['shownav']) {
				if(empty($nav)) {
					$navparentid = 0;
					if($_G['gp_catid'] && $cate['level'] > 0 || !empty($_G['gp_upid'])) {
						$identifier = !empty($cate['upid']) ? $cate['upid'] : ($_G['gp_upid'] ? $_G['gp_upid'] : 0);
						$navparentid = DB::result_first('SELECT id FROM '.DB::table('common_nav')." WHERE `type`='4' AND identifier='$identifier'");
						if(empty($navparentid)) {
							cpmsg(cplang('portalcategory_parentcategory_no_shownav').','.cplang('return'), NULL, 'error');
						}
					}
					$setarr = array(
						'parentid' => $navparentid,
						'name' => $editcat['catname'],
						'url' => $portalcategory[$_G['gp_catid']]['caturl'],
						'type' => '4',
						'available' => '1',
						'identifier' => $_G['gp_catid'],
					);
					if($_G['gp_catid'] && $cate['level'] == 0 || empty($_G['gp_upid']) && empty($_G['gp_catid'])) {
						$setarr['subtype'] = '1';
					}
					$navid = DB::insert('common_nav', $setarr, true);

					if($_G['gp_catid'] && $cate['level'] == 0) {
						if(!empty($cate['children'])) {
							foreach($cate['children'] as $subcatid) {
								if($portalcategory[$subcatid]['shownav']) {
									$setarr = array(
										'parentid' => $navid,
										'name' => $portalcategory[$subcatid]['catname'],
										'url' => $portalcategory[$subcatid]['caturl'],
										'type' => '4',
										'available' => '1',
										'identifier' => $subcatid,
									);
									DB::insert('common_nav', $setarr);
								}
							}
						}
					}

				} else {
					$setarr = array('available'=>'1','url' => $portalcategory[$_G['gp_catid']]['caturl']);
					DB::update('common_nav', $setarr, array('type' => '4','identifier' => $_G['gp_catid']));
					if($portalcategory[$_G['gp_catid']]['level'] == 0 && $portalcategory[$_G['gp_catid']]['children']) {
						foreach($portalcategory[$_G['gp_catid']]['children'] as $subcatid) {
							DB::update('common_nav', array('url' => $portalcategory[$subcatid]['caturl']), array('type' => '4','identifier' => $subcatid));
						}
					}
				}
			} else {
				if(!empty($nav)) {
					DB::delete('common_nav', array('id'=>$nav['id']));
					if($portalcategory[$_G['gp_catid']]['level'] == 0 && !empty($portalcategory[$_G['gp_catid']]['children'])) {
						DB::delete('common_nav', array('parentid'=>$nav['id']));
						DB::update('portal_category', array('shownav'=>'0'), ' catid IN ('.dimplode($portalcategory[$_G['gp_catid']]['children']).')');
					}
				}
			}
		}

		if($_G['gp_setindex']) {
			DB::insert('common_setting', array('skey' => 'defaultindex', 'svalue' => $portalcategory[$_G['gp_catid']]['caturl']), 0, 1);
		} elseif($oldsetindex) {
			DB::insert('common_setting', array('skey' => 'defaultindex', 'svalue' => ''), 0, 1);
		}

		updatecache($cachearr);

		cpmsg('portalcategory_edit_succeed', 'action=portalcategory#cat'.$_G['gp_catid'], 'succeed');
	}
}

function showcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('portalcategory');
	$value = $_G['cache']['portalcategory'][$key];
	$return = '';

	include_once libfile('function/portalcp');
	$value['articles'] = category_get_num('portal', $key);
	$publish = '';
	if(empty($_G['cache']['portalcategory'][$key]['disallowpublish'])) {
		$publish = '&nbsp;<a href="portal.php?mod=portalcp&ac=article&catid='.$key.'" target="_blank">'.cplang('portalcategory_publish').'</a>';
	}
	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover" id="cat'.$value['catid'].'"><td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
		'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
		'</div>'.
		'</td><td>'.$value['articles'].'</td>'.
		'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"':'').' /></td>'.
		'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="admin.php?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover" id="cat'.$value['catid'].'"><td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
		'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
		'<a class="addchildboard" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid='.$value['catid'].'">'.cplang('portalcategory_addthirdcategory').'</a></div>'.
		'</td><td>'.$value['articles'].'</td>'.
		'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"':'').' /></td>'.
		'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="admin.php?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i==$L-1);
		}
	} else {
		$childrennum = count($_G['cache']['portalcategory'][$key]['children']);
		$toggle = $childrennum > 25 ? ' style="display:none"' : '';
		$return = '<tbody><tr class="hover" id="cat'.$value['catid'].'"><td onclick="toggle_group(\'group_'.$value['catid'].'\')"><a id="a_group_'.$value['catid'].'" href="javascript:;">'.($toggle ? '[+]' : '[-]').'</a></td>'
		.'<td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
		'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
		'</div>'.
		'</td><td>'.$value['articles'].'</td>'.
		'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"':'').' /></td>'.
		'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="admin.php?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr></tbody>
		<tbody id="group_'.$value['catid'].'"'.$toggle.'>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '</tdoby><tr><td>&nbsp;</td><td colspan="9"><div class="lastboard"><a class="addtr" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid='.$value['catid'].'">'.cplang('portalcategory_addsubcategory').'</a></td></div>';
	}
	return $return;
}

function deleteportalcategory($ids) {
	global $_G;

	if(empty($ids)) return false;
	if(!is_array($ids) && $_G['cache']['portalcategory'][$ids]['upid'] == 0) {
		@require_once libfile('function/delete');
		deletedomain(intval($ids), 'channel');
	}
	if(!is_array($ids)) $ids = array($ids);

	DB::delete('portal_category', "catid IN (".dimplode($ids).")");
	DB::delete('common_nav', "`type`='4' AND identifier IN (".dimplode($ids).")");

	$tpls = $defaultindex = array();
	foreach($ids as $id) {
		$defaultindex[] = $_G['cache']['portalcategory'][$id]['caturl'];
		$tpls[] = 'portal/list_'.$id;
		@unlink(DISCUZ_ROOT.'./data/diy/portal/list_'.$id.'.htm');
		@unlink(DISCUZ_ROOT.'./data/diy/portal/list_'.$id.'.htm.bak');
		@unlink(DISCUZ_ROOT.'./data/diy/portal/list_'.$id.'_diy_preview.htm');
	}
	if(in_array($_G['setting']['defaultindex'], $defaultindex)) {
		DB::insert('common_setting', array('skey' => 'defaultindex', 'svalue' => ''), 0, 1);
	}
	DB::delete('common_diy_data', "targettplname IN (".dimplode($tpls).")");

}

function getprimaltplname($filename) {
	global $lang;
	$content = @file_get_contents(DISCUZ_ROOT.'./template/default/portal/'.$filename);
	$name = $filename;
	if($content) {
		preg_match("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>/i", trim($content), $mathes);
		if(!empty($mathes[1])) {
			preg_match("/^\{lang (.+?)\}$/", $mathes[1], $langs);
			if(!empty($langs[1])) {
				$name = !$lang[$langs[1]] ? $langs[1] : $lang[$langs[1]];
			} else {
				$name = dhtmlspecialchars($mathes[1]);
			}
		}
	}
	return $name;
}

function makecategoryfile($dir, $catid, $domain) {
	dmkdir(DISCUZ_ROOT.'./'.$dir, 0777, FALSE);
	$portalcategory = getglobal('cache/portalcategory');
	$prepath = str_repeat('../', $portalcategory[$catid]['level']+1);
	if($portalcategory[$catid]['level']) {
		$upid = $portalcategory[$catid]['upid'];
		while($portalcategory[$upid]['upid']) {
			$upid = $portalcategory[$upid]['upid'];
		}
		$domain = $portalcategory[$upid]['domain'];
	}

	$sub_dir = $dir;
	if($sub_dir) {
		$sub_dir = substr($sub_dir, -1, 1) == '/' ? '/'.$sub_dir : '/'.$sub_dir.'/';
	}
	$code = "<?php
chdir('$prepath');
define('SUB_DIR', '$sub_dir');
\$_GET['mod'] = 'list';
\$_GET['catid'] = '$catid';
require_once './portal.php';
?>";
	$r = file_put_contents($dir.'/index.php', $code);
	return $r;
}
function getportalcategoryfulldir($catid) {
	if(empty($catid)) return '';
	$portalcategory = getglobal('cache/portalcategory');
	$curdir = $portalcategory[$catid]['foldername'];
	$curdir = $curdir ? $curdir : '';
	if($catid && empty($curdir)) return FALSE;
	$upid = $portalcategory[$catid]['upid'];
	while($upid) {
		$updir = $portalcategory[$upid]['foldername'];
		if(!empty($updir)) {
			$curdir = $updir.'/'.$curdir;
		} else {
			return FALSE;
		}
		$upid = $portalcategory[$upid]['upid'];
	}
	return $curdir ? $curdir.'/' : '';
}

function delportalcategoryfolder($catid) {
	if(empty($catid)) return FALSE;
	$updatearr = array();
	$portalcategory = getglobal('cache/portalcategory');
	$children = $portalcategory[$catid]['children'];
	if($children) {
		foreach($children as $subcatid) {
			if($portalcategory[$subcatid]['foldername']) {
				$arr = delportalcategorysubfolder($subcatid);
				$updatearr = array_merge($updatearr, $arr);
			}
		}
	}

	$dir = getportalcategoryfulldir($catid);
	if(!empty($dir)) {
		unlink(DISCUZ_ROOT.$dir.'index.html');
		unlink(DISCUZ_ROOT.$dir.'index.php');
		rmdir(DISCUZ_ROOT.$dir);
		$updatearr[] = $catid;
	}
	if(dimplode($updatearr)) {
		DB::update('portal_category',array('foldername'=>''), 'catid IN('.dimplode($updatearr).')');
	}
}

function delportalcategorysubfolder($catid) {
	if(empty($catid)) return FALSE;
	$updatearr = array();
	$portalcategory = getglobal('cache/portalcategory');
	$children = $portalcategory[$catid]['children'];
	if($children) {
		foreach($children as $subcatid) {
			if($portalcategory[$subcatid]['foldername']) {
				$arr = delportalcategorysubfolder($subcatid);
				$updatearr = array_merge($updatearr, $arr);
			}
		}
	}

	$dir = getportalcategoryfulldir($catid);
	if(!empty($dir)) {
		unlink(DISCUZ_ROOT.$dir.'index.html');
		unlink(DISCUZ_ROOT.$dir.'index.php');
		rmdir(DISCUZ_ROOT.$dir);
		$updatearr[] = $catid;
	}
	return $updatearr;
}

function remakecategoryfile($categorys) {
	if(is_array($categorys)) {
		$portalcategory = getglobal('cache/portalcategory');
		foreach($categorys as $subcatid) {
			$dir = getportalcategoryfulldir($subcatid);
			makecategoryfile($dir, $subcatid, $portalcategory[$subcatid]['domain']);
			if($portalcategory[$subcatid]['children']) {
				remakecategoryfile($portalcategory[$subcatid]['children']);
			}
		}
	}
}
?>