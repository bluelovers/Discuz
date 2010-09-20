<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attribute.inc.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}
$cid = $_REQUEST['cid'] ? $_REQUEST['cid'] : 0;

/**
*判斷本分類是否可編輯屬性列表
*/
$type = checkcid($cid);

if($_GET['op']=='add' && !empty($_POST['valuesubmit'])) {
	$newrow = DB::result(DB::query("SELECT count(*) FROM ".tname("attribute")." WHERE cat_id = $cid"), 0);
	if(!empty($newrow)) {
		$newrow = DB::result(DB::query("SELECT max(attr_row) FROM ".tname("attribute")." WHERE cat_id = $cid"), 0);
		$newrow++;
	}
	DB::query('INSERT INTO '.tname('attribute')." (attr_name, cat_id, attr_type, attr_row)
					VALUES ('$_POST[name]', '$cid', '$_POST[attr_type]', '$newrow');");
	//updatesubcatid(); //更新子分類關係
	$attr_id = DB::insert_id();
	if($_POST['attr_type'] == '0' && !empty($_POST['newvalues'])) {
		$newvalues = explode("\r\n", $_POST['newvalues']);
		$newvaluesql = '';
		foreach($newvalues as $newvalue) {
			if(!empty($newvalue)) {
				$newvaluesql[] = "('$attr_id', '$newvalue')";
			}
		}
		if(!empty($newvaluesql)) {
			DB::query('INSERT INTO '.tname('attrvalue')." (`attr_id`, `attr_text`) VALUES ".implode(", ", $newvaluesql).";");
		}
	}
	header('Location: admin.php?action=attribute&cid='.$cid.'&type='.$type);

} elseif($_GET['op']=='edit' && !empty($_POST['valuesubmit'])) {
	//print_r($_POST);exit();
	
	DB::query('UPDATE '.tname('attribute')."  SET attr_name = '$_POST[name]', attr_type = '$_POST[attr_type]' WHERE attr_id = $_POST[attr_id];");
	foreach($_POST['attr_valueid'] as $key=>$value) {
		$dislpay = $_POST['display'][$key];
		DB::query('UPDATE '.tname('attrvalue')."  SET attr_text = '$value',displayorder = '$dislpay' WHERE attr_valueid = $key;");
	}
	if($_POST['attr_type'] == '0' && !empty($_POST['newvalues'])) {
		$newvalues = explode("\r\n", $_POST['newvalues']);
		$newvaluesql = '';
		foreach($newvalues as $newvalue) {
			if(!empty($newvalue)) {
				$newvaluesql[] = "('$_POST[attr_id]', '$newvalue')";
			}
		}
		if(!empty($newvaluesql)) {
			DB::query('INSERT INTO '.tname('attrvalue')." (`attr_id`, `attr_text`) VALUES ".implode(", ", $newvaluesql).";");
		}
	}
	header('Location: admin.php?action=attribute&cid='.$cid.'&type='.$type);
} elseif($_GET['op']=='add' || $_GET['op']=='edit') {
	$attributes = array();
	$_GET['attr_id'] = intval($_GET['attr_id']);
	if($_GET['op']=='edit') {
		$attribute = DB::fetch(DB::query("SELECT * FROM ".tname("attribute")." WHERE attr_id = '$_GET[attr_id]' LIMIT 1"));
		$query= DB::query("SELECT * FROM ".tname("attrvalue")." WHERE attr_id = '$_GET[attr_id]' ORDER BY displayorder ASC, attr_valueid ASC");
		while($result = DB::fetch($query)) {
			$attrvalues[] = $result;
		}
	} else {
		$categorylist = getmodelcategory($type);
		$query = DB::query("SELECT a.*,v.attr_valueid,v.attr_text FROM ".tname("attribute")." a LEFT JOIN ".tname("attrvalue")." v on a.attr_id = v.attr_id WHERE a.cat_id IN (".implode(",", array_keys($categorylist)).") ORDER BY a.displayorder,v.displayorder ASC, v.attr_valueid ASC");
		while($result = DB::fetch($query)) {
			if(empty($attributes[$result['attr_id']])) {
				$attributes[$result['attr_id']] = array('attr_id' => $result['attr_id'], 'attr_name' => $result['attr_name'], 'attr_type' => $result['attr_type']);
			}
			if($result['attr_type'] == 0) {
				$attributes[$result['attr_id']]['attrvalues'][$result['attr_valueid']] = $result;
			}
		}
		foreach($attributes as $key=>$value) {
			if($value['attr_type'] == 0 && count($value['attrvalues']) > 0) {
				$values = array();
				foreach($value['attrvalues'] as $attrvalue) {
					$values[] = $attrvalue['attr_text'];
				}
			}
			$attributes[$key]['attr_values'] = implode("\r\n", $values);
		}
	}
	//添加或更改屬性的編輯頁面
	shownav('catmanage', 'attribute_'.$_GET['op']);
	showsubmenu('attribute_'.$_GET['op']);
	showtips('attribute_'.$_GET['op'].'_tips');
	showformheader('attribute&op='.$_GET['op'].'&type='.$type);
	showtableheader('');
	//$attribute['attr_type'] = $attribute ? $attribute['attr_type']: 1;
	if(count($attributes) > 0) {
		echo '<tr><td class="td27" colspan="2">'.lang('select_attribute').'</td></tr><tr><td class="vtop rowform" id="attributes" colspan="2"><select id="select_attribute" name="select_attribute"><option value="-1">'.lang('shop_region1').'</option>';
		foreach($attributes as $k=>$v) {

			if($v['upid'] == 0) {
				echo '<option value="'.$v['attr_id'].'">'.$v['attr_name'].'</option>';
			}
		}
		echo '<script>$(function(){var attributes = '.json_encode_region($attributes).';$("#select_attribute").change(function(){var select_attribute = $("#select_attribute").val();for(var i in attributes){if(attributes[i]["attr_id"] == select_attribute){$("#name").val(attributes[i]["attr_name"]);$("#newvalues").val(attributes[i]["attr_values"]);$("input[name=\'attr_type\'][value=\'"+attributes[i]["attr_type"]+"\']").click();return;}}});});</script></td></tr>';

	}
	showsetting('attribute_name', 'name', $attribute['attr_name'], 'text');
	showsetting('attribute_type', array('attr_type', array(
					array(1, lang('attr_model_input'), array('valuemodel' => 'none', 'newvaluemodel' => 'none')),
					array(0, lang('attr_model_select'), array('valuemodel' => '', 'newvaluemodel' => ''))
					), true), $attribute['attr_type'], 'mradio');
	showtagheader('tbody', 'valuemodel', (empty($attribute['attr_type']) || $attribute['attr_type']==0?true:false), 'sub');
	if(!empty($attrvalues)) {
		foreach($attrvalues as $attrvalue) {
			echo '<tr><td>'.($_GET['op']=='edit'?'<input style="width:30px;" value="'.$attrvalue['displayorder'].'" name="display['.$attrvalue['attr_valueid'].']" />&nbsp;&nbsp;':'').'<input type="text" name="attr_valueid['.$attrvalue['attr_valueid'].']" value="'.$attrvalue['attr_text'].'" />&nbsp;<a class="attrvaluedelete" id="valuedelete_'.$attrvalue['attr_valueid'].'" href="#" onclick="javascript:deletevalue('.$attrvalue['attr_valueid'].',\''.$attrvalue['attr_text'].'\');return false;">'.lang('delete').'</a></td></tr>';
		}
	}
	
	echo '<tr><td><script>function deletevalue(valueid,valuetext) { if(confirm("'.lang('confirm_delete_attrvalue').'"+valuetext)){jQuery.post("batch.attribute.php?ajax=1","ajax=1&op=delete&valueid="+valueid,function(data){$("#valuedelete_"+valueid).parent().hide();return false;});}return false; }</script></td></tr>';
	showtagfooter('tbody');

	showtagheader('tbody', 'newvaluemodel', (empty($attribute['attr_type']) || $attribute['attr_type']==0?true:false), 'sub');

	showsetting('attribute_values', 'newvalues', '', 'textarea');

	showtagfooter('tbody');
	showhiddenfields(array('attr_id' => $_GET['attr_id']));
	showhiddenfields(array('cid' => $_GET['cid']));

	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
} elseif(!empty($_POST['listsubmit'])) {

	foreach($_POST['display'] as $key=>$value) {
		$query = '';
		$key = intval($key);
		$value = intval($value);
		if($key>0 && $value>0) {
			$query = 'UPDATE '.tname('attribute').' SET displayorder=\''.$value.'\' WHERE attr_id=\''.$key.'\'; ';
			DB::query($query);
		}
	}
	if(!empty($_POST['multiop'])) {
		$maxrow = DB::result(DB::query("SELECT max(attr_row) FROM ".tname("attribute")." WHERE cat_id = $cid"), 0);
		
		$query = DB::query("SELECT * FROM ".tname("attribute")." WHERE cat_id = '$cid' ORDER BY attr_row ASC");
		while($result = DB::fetch($query)) {
			$attributes[$result['attr_id']] = $result;
		}
		foreach($attributes as $key=>$attribute) {
			if(in_array($key, $_POST['attr_ids'])) {
				if($attribute['attr_row'] == $maxrow) {
					DB::query("UPDATE ".tname("itemattribute")." SET `attr_id_".$attribute['attr_row']."` = 0 WHERE catid = '$cid'");
				}
			}
		}
		$attids = implode(",", $_POST['attr_ids']);
		DB::query("DELETE FROM ".tname("attribute")." WHERE attr_id IN (".$attids.")");
		DB::query("DELETE FROM ".tname("attrvalue")." WHERE attr_id IN (".$attids.")");
	}
	header('Location: admin.php?action=attribute&cid='.$cid.'&type='.$_GET['type']);

} else {

	//讀取屬性列表
	$query = DB::query("SELECT * FROM ".tname("attribute")." WHERE cat_id = $cid ORDER BY attr_id");
	while($result = DB::fetch($query)) {
		//屬性可選值列表
		//$result['attr_values'] = explode("\n", $result['attr_values']);
		$attrlist[] = $result;
	}
	//沒有提交數據的列表頁
	shownav('catmanage', 'attribute_list');
	showsubmenu('attribute_list');
	showtips('attribute_list_tips');

	showformheader('attribute&type='.$_GET['type']);
	showtableheader('');
	showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'attr_ids\')" name="chkall">' ,'display_order', 'catid', 'attrname', 'operation'));
	foreach($attrlist as $attr) {
		showtablerow('', array(), array(
				'<input name="attr_ids['.$attr['attr_id'].']" type="checkbox" value="'.$attr['attr_id'].'" />',
				'<input name="display['.$attr['attr_id'].']" type="text" size="2" value="'.$attr['displayorder'].'" />',
				$attr['attr_id'],
				$attr['attr_name'],
				'[<a href="admin.php?action=attribute&op=edit&cid='.$cid.'&attr_id='.$attr['attr_id'].'&type='.$type.'">'.lang('attribute_edit').'</a>]',
				));
	}
	echo '<tr class="hover"><td></td><td><a href="?action=attribute&op=add&cid='.$cid.'&type='.$type.'" class="addtr">'.lang('attr_add').'</a></td><td></td><td></td><td></td></tr>';
	showtablefooter();
	showtableheader(lang('operation_form'), 'nobottom');
	showtablerow('', array('width="50px"', ''), array(
								'<input class="radio" type="radio" name="multiop" value="delete"><input type="hidden" name="page" value="'.$_GET['page'].'"><input type="hidden" name="buffurl" value="'.$buffurl.'">',
								lang('mod_delete'),
								));

	showhiddenfields(array('cid' => $cid));
	showsubmit('listsubmit');

	showtablefooter();
	showformfooter();
}

/**
判斷本分類是否可編輯屬性列表
@param 類型 參數名  意義
@param int 分類id
@return false
xuhui 10-4-15 上午9:41
*/

function checkcid($cid) {
	global $_G, $_SGLOBAL;
	$childcheck = DB::result(DB::query("SELECT count(*) FROM ".tname("categories")." WHERE upid = $cid"), 0);
	if($childcheck) {
		cpmsg('childcheck_fail');
	}
	$typecheck = DB::result(DB::query("SELECT type FROM ".tname("categories")." WHERE catid = $cid"), 0);
	if(!in_array($typecheck, array('shop', 'region', 'good', 'album', 'consume', 'notice', 'groupbuy'))) {
		cpmsg('typecheck_fail');
	}
	return $typecheck;
}
?>