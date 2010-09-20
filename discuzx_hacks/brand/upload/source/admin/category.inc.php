<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: category.inc.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');
$commentmodel = $commentmodelarr = array();
if($_POST['commtmodel'] != 1) {
	unset($_POST['cmid']);
}
$type = $_REQUEST['type'] ? $_REQUEST['type'] : 'good';

if(!in_array($type, array('shop', 'region', 'good', 'album', 'consume', 'notice', 'groupbuy'))) {
	$type = 'shop';
}

$_POST['cmid'] = intval($_POST['cmid']);
$_POST['commtmodel'] = intval($_POST['commtmodel']);

if($type != 'shop') {
	$_POST['cmid'] = 0;
	$_POST['commtmodel'] = 0;
	//读取地区分类、商品分类、图库分类
	$categorylist = getmodelcategory($type);
}
$checkresults = array();
if($_GET['op']=='add' && !empty($_POST['valuesubmit'])) {
	if(empty($_POST['subject'])) {
		cpmsg('category_subject_error');
		//array_push($checkresults, array('subject'=>lang('category_subject_error')));
	}
	//提交了添加数据
	$subjects = explode("\r\n", $_POST['subject']);
	foreach($subjects as $subject) {
		if(!empty($subject))
			$insertstr[] =  "('$_POST[upid]', '$subject', '$_POST[note]', '$type', '$_POST[displayorder]', '$_POST[cmid]', '$_POST[commtmodel]')";
	}
	if(!empty($checkresults)) {
		cpmsg('category_info_error', '', '', '', true, true, $checkresults);
	}
	DB::query('INSERT INTO '.tname('categories')." (upid, name, note, type, displayorder, cmid, commtmodel)
							VALUES ".implode(",", $insertstr).";");
	updatesubcatid(); //更新子分类关系
	include_once(B_ROOT.'./source/function/cache.func.php');
	updatecategorycache();
	header('Location: admin.php?action=category&type='.$type);

} elseif($_GET['op']=='edit' && !empty($_POST['valuesubmit'])) {

	if(empty($_POST['subject'])) {
		cpmsg('category_subject_error');
		//array_push($checkresults, array('subject'=>lang('category_subject_error')));
	}
	if(!empty($checkresults)) {
		cpmsg('category_info_error', '', '', '', true, true, $checkresults);
	}

	//提交了修改数据
	DB::query('UPDATE '.tname('categories')." SET upid='$_POST[upid]', name='$_POST[subject]', note='$_POST[note]', displayorder='$_POST[displayorder]', cmid='$_POST[cmid]', commtmodel='$_POST[commtmodel]' WHERE catid='$_POST[catid]';");
	updatesubcatid(); //更新子分类关系
	include_once(B_ROOT.'./source/function/cache.func.php');
	updatecategorycache();
	header('Location: admin.php?action=category&type='.$type);

} elseif($_GET['op']=='del' && !empty($_GET['catid'])) {
	//提交了删除数据
	if($categorylist[$_GET['catid']]['havechild']) { cpmsg('category_delete_had_subcat', 'admin.php?action=category&type='.$type);}
	if(in_array($type, array('shop', 'good', 'notice', 'consume', 'album'))) {
		$itemtname = $type.'items';
		$catname = 'catid';
		$cdhit = 'category_delete_had_item_'.$type;
	} else {
		$itemtname = 'shopitems';
		$catname = 'region';
		$cdhit = 'category_delete_had_item_shop';
	}
	$query = DB::query('SELECT COUNT(itemid) AS count FROM '.tname($itemtname)." WHERE $catname='$_GET[catid]' LIMIT 1;");
	$result = DB::fetch($query);
	if($result['count']>0) { cpmsg($cdhit, 'admin.php?action=category&type='.$type);}
	DB::query('DELETE FROM '.tname('categories')." WHERE catid='$_GET[catid]' LIMIT 1;");
	updatesubcatid($type); //更新子分类关系
	if(in_array($type, array('good', 'notice', 'album', 'consume', 'groupbuy'))) {
		synscategroiesforgroup($type, $_GET['catid']); //更新店铺组设置	
	}
	include_once(B_ROOT.'./source/function/cache.func.php');
	updatecategorycache();
	header('Location: admin.php?action=category&type='.$type);
} elseif(!empty($_POST['listsubmit'])) {
	//提交了数据
	$item = array();
	foreach($_POST['display'] as $key=>$value) {
		$query = '';
		$key = intval($key);
		$value = intval($value);
		if($key>0) {
			$query = 'UPDATE '.tname('categories').' SET displayorder=\''.$value.'\' WHERE catid=\''.$key.'\'; ';
			DB::query($query);
		}
	}
	updatesubcatid(); //更新子分类关系
	include_once(B_ROOT.'./source/function/cache.func.php');
	updatecategorycache();
	header('Location: admin.php?action=category&type='.$type);
} elseif($_GET['op'] == 'add' || $_GET['op'] == 'edit') {
	//添加或更改分类的编辑页面
	shownav('catmanage', 'category_'.$_GET['op']);
	showsubmenu('category_'.$_GET['op']);
	showtips('category_'.$_GET['op'].'_tips');
	showformheader('category&op='.$_GET['op'].'&type='.$_GET['type']);
	showtableheader('');
	if($_GET['upid'] > 0) {
		showsetting('category_upname', 'upname', $categorylist[$_GET['upid']]['name'], 'text', true);
	}
	showsetting($_GET['op'].'_category_name', 'subject', $categorylist[$_GET['catid']]['name'], ($_GET['op'] == 'edit'?'text':'textarea'));
	if($_GET['op'] == 'edit')
		showsetting('category_note', 'note', $categorylist[$_GET['catid']]['note'], 'text');
	if($_GET['op'] == 'edit')
		showsetting('category_displayorder', 'displayorder', $categorylist[$_GET['catid']]['displayorder'], 'number');
	if($type == 'shop' && $_GET['upid'] == 0 && $_G['setting']['commentmodel'] == 1 ) {
		$model = !empty($categorylist[$_GET['catid']]['cmid']) ? 1 : 0;
		$query = DB::query('SELECT * FROM '.tname('commentmodels').' ORDER BY cmid ASC');
		while($commentmodel = DB::fetch($query)) {
			foreach(unserialize($commentmodel['scorename']) as $scorename) {
				$commentmodel['scorenamestr'] .= '['.$scorename.']';
			}
			$commentmodelarr[] = $commentmodel;
		}
		showsetting('category_model', array('commtmodel', array(
						array(1, lang('yes'), array('catmodel' => '')),
						array(0, lang('no'), array('catmodel' => 'none'))
						), true), $model, 'mradio');
		showtagheader('tbody', 'catmodel', $model, 'sub');
		echo "<tr><td colspan='2'>";
		if(!empty($commentmodelarr)) {
			echo "<table width='800' style='margin:10px;'>";
			showsubtitle(array('', 'modelname', 'scorename', 'cmdateline'));
			foreach($commentmodelarr as $value) {
				$checked = $value['cmid'] == $categorylist[$_GET['catid']]['cmid'] ? 'checked' : '';
				showtablerow('', array('class="td27" style="width:30px;"'), array("<input class='radio' type='radio' name='cmid' value='$value[cmid]' $checked/>", $value['modelname'], $value['scorenamestr'], date('Y-m-d', $value['dateline'])));
			}
			echo "</table>";
		} else {
			showtablerow('', array('class="td27"'), array(lang('catmodel_add')));
		}
		echo "</td></tr>";
		showtagfooter('tbody');

	}
	showhiddenfields(array('upid' => $_GET['upid']));
	showhiddenfields(array('catid' => $_GET['catid']));
	showhiddenfields(array('type' => $type));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
} else {
	//没有提交数据的列表页
	shownav('catmanage', 'category_'.$type.'_list');
	showsubmenu('menu_category_'.$type);
	showtips('category_list_tips_'.$type);
	showformheader('category&type='.$type);
	showtableheader('');
	showsubtitle(array('display_order', 'catid', 'catname', 'operation'));
	foreach($categorylist as $value) {
		showtablerow('', array(), array(
				'<input name="display['.$value['catid'].']" type="text" size="2" value="'.$value['displayorder'].'" />',
				$value['catid'],
				empty($value['url'])?($value['pre'].' '.$value['name']):('<a href="'.$value['url'].'" target="_blank">'.$value['pre'].' '.$value['name'].' </a>'),
				'[<a href="admin.php?action=category&op=add&type='.$type.'&upid='.$value['catid'].'">'.lang('category_add_sub').'</a>]'.('[<a href="admin.php?action=category&op=edit&type='.$type.'&upid='.$value['upid'].'&catid='.$value['catid'].'">'.lang('category_edit').'</a>] '.(($type!='shop' && $type!='region') && !$value['havechild']?'[<a href="admin.php?action=attribute&cid='.$value['catid'].'&type='.$type.'">'.lang('attribute_list').'</a>]':'').' [<a href="admin.php?action=category&op=del&catid='.$value['catid'].'&type='.$type.'">'.lang('category_del').'</a>]'),
				));
	}
	echo '<tr class="hover"><td></td><td><a href="?action=category&op=add&type='.$type.'" class="addtr">'.lang('category_add_'.$type).'</a></td><td></td><td></td><td></td></tr>';
	showsubmit('listsubmit');
	showtablefooter();
	showformfooter();

}

?>