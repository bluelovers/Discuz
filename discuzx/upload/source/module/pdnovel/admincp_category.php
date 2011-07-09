<?php

pdnovelcache('pdnovelcategory', 'pdnovel');
loadcache('pdnovelcategory');
$pdnovelcategory = $_G['cache']['pdnovelcategory'];
shownav('pdnovel', 'category');

if($do=='show') {

	if(!submitcheck('categorysubmit')) {	

		showsubmenu('category',  array(array('list', 'pdnovel&operation=category', 1)));
		showformheader("pdnovel&operation=category");
		echo '<style>.txt170 .txt{min-width:170px;}</style><div><a href="javascript:;" onclick="show_all()">'.cplang('show_all').'</a> | <a href="javascript:;" onclick="hide_all()">'.cplang('hide_all').'</a></div>';
		showtableheader('', '', ' style="min-width:910px; _width:910px;"');
		showsubtitle(array('', 'category_displayorder', 'category_name', 'category_caption', 'category_keyword', 'category_summary', 'category_operation'));
		foreach ($pdnovelcategory as $key=>$value) {
			if($value['level'] == 0) {
				echo pdnovelcategoryrow($key, 0, '');
			}
		}
		echo '<tbody><tr><td>&nbsp;</td><td colspan="6"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('category_addcategory').'</a></div></td></tr></tbody>';
		showsubmit('categorysubmit');
		showtablefooter();
		showformfooter();
		echo <<<SCRIPT
<script type="text/Javascript">
var rowtypedata = [
[[1,'',''], [1,'<input type="text" class="txt" name="neworder[0][]" value="0" />', 'td25'], [1,'<div class="parentboard"><input type="text" class="txt" name="newname[0][]" value="$lang[category_newname]"/></div>'], [1,'<input type="text" class="txt" name="newcaption[0][]" value="$lang[category_caption]"/></div>','txt170'], [1,'<input type="text" class="txt" name="newkeyword[0][]" value="$lang[category_keyword]"/></div>','txt170'], [1,'<input type="text" class="txt" name="newsummary[0][]" value="$lang[category_summary]"/></div>','txt170'], [1,'','']],
[[1,'',''], [1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [1,'<div class="board"><input type="text" class="txt" name="newname[{1}][]" value="$lang[category_newname]"/></div>'], [1,'<input type="text" class="txt" name="newcaption[{1}][]" value="$lang[category_caption]"/></div>','txt170'], [1,'<input type="text" class="txt" name="newkeyword[{1}][]" value="$lang[category_keyword]"/></div>','txt170'], [1,'<input type="text" class="txt" name="newsummary[{1}][]" value="$lang[category_summary]"/></div>','txt170'], [1,'','']],
];
</script>
SCRIPT;
?>
<?php			
	} else {
		if(is_array($_G['gp_order'])) {
			foreach($_G['gp_order'] as $catid => $value) {
				DB::update('pdnovel_category', array(
					'catname' => $_G['gp_name'][$catid],
					'caption' => $_G['gp_caption'][$catid],
					'keyword' => $_G['gp_keyword'][$catid],
					'description' => $_G['gp_summary'][$catid],
					'displayorder' => $_G['gp_order'][$catid],
				), "catid='$catid'");
			}
		}
		if(is_array($_G['gp_neworder'])) {
			foreach($_G['gp_neworder'] as $upid => $cats) {
				foreach($cats as $key => $value) {
					DB::insert('pdnovel_category', array(
						'upid' => $upid,
						'catname' => $_G['gp_newname'][$upid][$key],
						'caption' => $_G['gp_newcaption'][$upid][$key],
						'keyword' => $_G['gp_newkeyword'][$upid][$key],
						'description' => $_G['gp_newsummary'][$upid][$key],
						'displayorder' => $_G['gp_neworder'][$upid][$key],
					), 1);
				}
			}
		}
		pdnovelcache('pdnovelcategory', 'pdnovel');
		cpmsg('category_update_succeed', 'action=pdnovel&operation=category', 'succeed');
	}

}elseif($do == 'delete'){

	$catid = $_GET['catid'];
	if(!$catid) {
		cpmsg('category_not_found', '', 'error');
	}	
	$upid = $pdnovelcategory[$catid]['upid'];
	if($upid==0){
		if($pdnovelcategory[$catid]['children']){
			cpmsg('category_chlidren_error', 'action=pdnovel&operation=category', 'error');
		}else{
			DB::query("DELETE FROM ".DB::table('pdnovel_category')." WHERE catid = $catid;");
			pdnovelcache('pdnovelcategory', 'pdnovel');
			cpmsg('category_delete_succeed', 'action=pdnovel&operation=category', 'succeed');
		}
	}else{
		$pdnovel_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('pdnovel_view')." WHERE catid = $catid");
		if($pdnovel_count){
			shownav('pdnovel', 'category');
			showsubmenu('category',  array(array('delete', 'pdnovel&operation=category&do=delete&catid='.$catid, 1)));
			showformheader('pdnovel&operation=category&do=mdelete&catid='.$catid);
			showtableheader();
			echo "<tr><td colspan=\"2\" class=\"td27\">".cplang('delete').$pdnovelcategory[$catid]['catname'].":</td></tr>
					<tr class=\"noborder\">
						<td class=\"vtop rowform\">
							<ul class=\"nofloat\" onmouseover=\"altStyle(this);\">
							<li class=\"checked\"><input class=\"radio\" type=\"radio\" name=\"pdnovelop\" value=\"move\" checked />&nbsp;".cplang('category_moveto')."&nbsp;&nbsp;&nbsp;".category_showselect('toid', $catid)."</li>
							<li><input class=\"radio\" type=\"radio\" name=\"pdnovelop\" value=\"delete\" />&nbsp;".cplang('category_delete')."</li>
							</ul></td>
						<td class=\"vtop tips2\"></td>
					</tr>";
			showsubmit('deletesubmit');
			showtablefooter();
			showformfooter();
		}else{
			DB::query("DELETE FROM ".DB::table('pdnovel_category')." WHERE catid = $catid;");
			pdnovelcache('pdnovelcategory', 'pdnovel');
			cpmsg('category_delete_succeed', 'action=pdnovel&operation=category', 'succeed');
		}
	}

}elseif($do='mdelete'){

	$catid = $_G['gp_catid'];
	$pdnovelop = $_G['gp_pdnovelop'];
	if($pdnovelop=='move'){
		$toid = $_G['gp_toid'];
		if(empty($toid)){
			cpmsg('category_notoid', 'action=pdnovel&operation=category&do=delete&catid='.$catid, 'error');
		}
		DB::query("UPDATE ".DB::table('pdnovel_view')." SET catid = $toid WHERE catid = $catid;");
		DB::query("DELETE FROM ".DB::table('pdnovel_category')." WHERE catid = $catid;");
		pdnovelcache('pdnovelcategory', 'pdnovel');
		cpmsg('category_delete_succeed', 'action=pdnovel&operation=category', 'succeed');
	}elseif($pdnovelop=='delete'){
		$query = DB::query("SELECT novelid FROM ".DB::table('pdnovel_view')." WHERE catid = $catid;");
		while($pdnovel = DB::fetch($query)){
			$pdnovelid = $pdnovel['pdnovelid'];
			DB::query("DELETE FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid;");
			removeDir('pdnovel/file/'.floor($pdnovelid/1000).'/'.$pdnovelid);
			DB::query("DELETE FROM ".DB::table('pdnovel_info')." WHERE pdnovelid=$pdnovelid;");
		}
		DB::query("DELETE FROM ".DB::table('category')." WHERE catid = $catid;");
		pdnovelcache('pdnovelcategory', 'pdnovel');
		cpmsg('category_delete_succeed', 'action=pdnovel&operation=category', 'succeed');
	}
}


?>