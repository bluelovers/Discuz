<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group.inc.php 4400 2010-09-13 02:36:34Z yumiao $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
$types = array('album', 'good', 'notice', 'consume', 'groupbuy');
$allowvars = array('title', 'album_field', 'good_field', 'notice_field', 'consume_field', 'groupbuy_field', 'enablegood', 'enablenotice', 'enableconsume', 'enablealbum', 'enablegroupbuy', 'enablebrandlinks', 'verifygood', 'verifynotice', 'verifyconsume', 'verifyalbum', 'verifyshop', 'verifygroupbuy', 'consumemaker', 'maxnumgood', 'maxnumnotice', 'maxnumalbum', 'maxnumconsume', 'maxnumgroupbuy', 'maxnumbrandlinks');//允許提交的settings變量名

// 處理提交過來的增加和修改操作
$checkresults = array();
if($_GET['op']=='add' && !empty($_POST['valuesubmit'])) {
	if(empty($_POST['title'])) {
		array_push($checkresults, array('title'=>lang('group_title_error')));
	}

	foreach($types as $k=>$v) {
		if(!empty($_POST[$v.'_field'])) {
			$categorylist = getmodelcategory($v);
			if(count($_POST[$v.'_field']) == count($categorylist)) {
				$_POST[$v.'_field'] = 'all';
			} else {
				$_POST[$v.'_field'] = implode(",", $_POST[$v.'_field']);
			}
		}
	}
	$key = $rpsql = $comma = '';
	foreach($_POST as $key=>$value) {
		if(in_array($key, $allowvars)) {
			$rpsql .= "$comma `$key` = '$value'";
			$comma = ', ';
		}
	}
	$setkeys = '(`'.implode("`, `", $allowvars).'`)';

	$setvalues = '(';
	$setdom ='';
	foreach($allowvars as $field) {
		$setvalues .= $setdom."'".$_POST[$field]."'";
		$setdom =',';
	}
	$setvalues .= ')';
	if(!empty($checkresults)) {
		cpmsg('group_info_error', '', '', '', true, true, $checkresults);
	}
	DB::query("INSERT INTO ".tname("shopgroup")." $setkeys VALUES $setvalues");
	include_once(B_ROOT.'./source/function/cache.func.php');
	updateshopgroupcache();

	cpmsg('message_success', 'admin.php?action=group');

} elseif($_GET['op']=='edit' && !empty($_POST['valuesubmit'])) {
	if(empty($_POST['title'])) {
		array_push($checkresults, array('title'=>lang('group_title_error')));
	}

	foreach($types as $k=>$v) {
		if(!empty($_POST[$v.'_field'])) {
			$categorylist = getmodelcategory($v);
			if(count($_POST[$v.'_field']) == count($categorylist)) {
				$_POST[$v.'_field'] = 'all';
			} else {
				$_POST[$v.'_field'] = implode(",", $_POST[$v.'_field']);
			}
		}
	}
	$key = $rpsql = $comma = '';
	foreach($_POST as $key=>$value) {
		if(in_array($key, $allowvars)) {
			$rpsql .= "$comma `$key` = '$value'";
			$comma = ', ';
		}
	}
	if(!empty($checkresults)) {
		cpmsg('group_info_error', '', '', '', true, true, $checkresults);
	}
	DB::query("UPDATE ".tname("shopgroup")." SET $rpsql WHERE id = '$_POST[id]'");
	include_once(B_ROOT.'./source/function/cache.func.php');
	updateshopgroupcache();
	cpmsg('message_success', 'admin.php?action=group');

} elseif($_GET['op']=='add' || $_GET['op']=='edit') {

	if($_GET['op']=='edit') {
		$group = DB::fetch(DB::query("SELECT * FROM ".tname("shopgroup")." WHERE id = '$_GET[id]' LIMIT 1"));

		foreach($types as $k=>$v) {
			$group[$v.'_field'] = explode(",", $group[$v.'_field']);
		}
	}
	// 添加或更改屬性的編輯頁面
	shownav('shop', 'group_'.$_GET['op']);
	showsubmenu('menu_group_add', array(
		array('nav_group', 'group', '0'),
		array('menu_group_'.$_GET['op'], 'group&op='.$_GET['op'], '1')
	));
	showtips('group_'.$_GET['op'].'_tips');
	showformheader('group&op='.$_GET['op']);
	showtableheader('');

	showsetting('grouptitle', 'title', $group['title'], 'text');
	foreach($types as $k=>$v) {
			$group['enable'.$v] = !isset($group['enable'.$v]) ? 1 : $group['enable'.$v];
			showsetting('enable'.$v, 'enable'.$v, $group['enable'.$v], 'radio');
			showfieldform($v);
	}
	echo '<tr><td class="td27" colspan="2">';
	showjscatefield();
	echo '</td></tr>';
	$group['enablebrandlinks'] = !isset($group['enablebrandlinks']) ? 1 : $group['enablebrandlinks'];
	showsetting('enablebrandlinks', 'enablebrandlinks', $group['enablebrandlinks'], 'radio');

	echo '<script type="text/javascript" charset="'.$_G['charset'].'">
		$(function() {

		});

		</script>';
	foreach($types as $k=>$v) {
			$group['verify'.$v] = !isset($group['verify'.$v]) ? 0 : $group['verify'.$v];
			showsetting('verify'.$v, 'verify'.$v, $group['verify'.$v], 'radio');
	}
	$group['verifyshop'] = !isset($group['verifyshop']) ? 0 : $group['verifyshop'];
	showsetting('verifyshop', 'verifyshop', $group['verifyshop'], 'radio');

	$maxnumtypes = $types;
	$maxnumtypes[] = 'brandlinks';
	foreach($maxnumtypes as $k=>$v) {
		$group['maxnum'.$v] = !isset($group['maxnum'.$v]) ? 0 : $group['maxnum'.$v];
		showsetting('maxnum'.$v, 'maxnum'.$v, $group['maxnum'.$v], 'text');
	}

	$group['consumemaker'] = !isset($group['consumemaker']) ? 1 : $group['consumemaker'];
	showsetting('consumemaker', 'consumemaker', $group['consumemaker'], 'radio');
	showhiddenfields(array('id' => $group['id']));

	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
	showmultiplejs();
} elseif(!empty($_POST['group_ids'])) {

	if(!empty($_POST['multiop'])) {
		$groupids = implode(",", $_POST['group_ids']);
		$shopnum = DB::result_first("SELECT count(*) FROM ".tname("shopitems")." WHERE groupid IN (".$groupids.")");
		if($shopnum > 0 ) {
			cpmsg('delete_group_havaitems', 'admin.php?action=group');
		}
		DB::query("DELETE FROM ".tname("shopgroup")." WHERE id IN (".$groupids.")");
	}
	updateshopgroupcache();
	cpmsg('message_success', 'admin.php?action=group');

} else {
	//讀取用戶組信息
	$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
	while($result = DB::fetch($query)) {
	    $result['album_field'] = explode(",", $result['album_field']);
	    $result['good_field'] = explode(",", $result['good_field']);
	    $result['notice_field'] = explode(",", $result['notice_field']);
	    $result['consume_field'] = explode(",", $result['consume_field']);
		$result['groupbuy_field'] = explode(",", $result['groupbuy_field']);
	    $grouplist[] = $result;
	}
	//添加或更改分類的編輯頁面
	shownav('shop', 'nav_group');
	showsubmenu('nav_group', array(
		array('nav_group', 'group', '1'),
		array('menu_group_add', 'group&op=add', '0')
	));
	showtips('nav_group_tips');
	showformheader('group');
	showtableheader('');
	showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'group_ids\')" name="chkall">', 'groupid', 'grouptitle', 'operation'));
	foreach($grouplist as $group) {
		showtablerow('', array(), array(
				'<input name="group_ids['.$group['id'].']" type="checkbox" value="'.$group['id'].'" />',
				$group['id'],
				$group['title'],
				'[<a href="admin.php?action=group&op=edit&id='.$group['id'].'">'.lang('group_edit').'</a>]',
				));
	}
	echo '<tr class="hover"><td></td><td><a href="?action=group&op=add" class="addtr">'.lang('group_add').'</a></td><td></td><td></td><td></td></tr>';
	showtableheader(lang('operation_form'), 'nobottom');
	showtablerow('', array('width="50px"', ''), array(
								'<input class="radio" type="radio" name="multiop" value="delete"><input type="hidden" name="page" value="'.$_GET['page'].'"><input type="hidden" name="buffurl" value="'.$buffurl.'">',
								lang('mod_delete'),
								));
	showsubmit('listsubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}

function showsettingmultiple($inputname, $cattype, $selected = array()) {
	$cats = getmodelcategory($cattype);
	echo '<tr><td class="td27" colspan="2">'.lang('group_'.$cattype).'</td></tr><tr><td>'.lang('group_allcats').'<br /><select multiple id="'.$inputname.'_s" style="width:380px;height:160px;">';
	foreach($cats as $k=>$v) {
		if(!$v['havechild'] && !in_array($k, $selected)) {
			echo '<option value="'.$v['catid'].'">'.$v['name'].'</option>';
		}
	}
	echo '</select><br /><a href="#" id="'.$inputname.'_add">'.lang('group_toright').'&gt;&gt;</a></td><td>'.lang('group_cats').'<br /><select multiple="multiple" id="'.$inputname.'" name="'.$inputname.'" style="width:380px;height:160px;">';
	if(count($selected) > 0) {
		foreach($selected as $kk=>$vv) {
			if(in_array($vv, array_keys($cats)) && !$cats[$vv]['havechild']) {
				echo '<option value="'.$vv.'">'.$cats[$vv]['name'].'</option>';
			}
		}
	}
	echo '</select><br /><a href="#" id="'.$inputname.'_remove">&lt;&lt;'.lang('group_toleft').'</a><input type="hidden" value="'.implode(",", $selected).'" name="'.$inputname.'_v" id="'.$inputname.'_v"></td></tr>';
}

function showmultiplejs() {
	$types = array('albumcats', 'goodcats', 'noticecats', 'consumecats', 'groupbuycats');
	echo '
<script type="text/javascript">
    $(function(){';
    foreach($types as $k=>$v) {
    	echo '
           //移到右邊
           $("#'.$v.'_add").click(function() {$("#'.$v.'_s option:selected").remove().appendTo("#'.$v.'");return false;});
           //移到左邊
           $("#'.$v.'_remove").click(function() {$("#'.$v.' option:selected").remove().appendTo("#'.$v.'_s");return false;});
           //雙擊選項
           $("#'.$v.'_s").dblclick(function(){$("option:selected",this).remove().appendTo("#'.$v.'");return false;});
           //雙擊選項
           $("#'.$v.'").dblclick(function(){$("option:selected",this).remove().appendTo("#'.$v.'_s");return false;});
    	';
    }
    echo '
    	   $("#submit_valuesubmit").click(function(){';
   	foreach($types as $k=> $v) {
   		echo '
    	       var sel = $("#'.$v.'")[0].options;
    	       var values = "";
    	       var dot = "";
    	       for(var i = 0; i < sel.length; i++) {
    	           var value = sel[i].value;
    	           values += dot+value;
    	           dot = ",";
    	       }
    	       //alert(values);alert($("#'.$v.'_v")[0].value);
    	       $("#'.$v.'_v")[0].value=values';

    }
    echo '
    	       return true;	   });
    });
</script>

	';
}


?>