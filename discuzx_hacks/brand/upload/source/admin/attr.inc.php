<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attr.inc.php 4300 2010-09-02 10:21:06Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$attrid = empty($_REQUEST['attrid'])?null:intval($_REQUEST['attrid']);
!in_array($_GET['type'],array('shop', 'groupby')) && $_GET['type'] = 'shop';
$mid = $_GET['type'] == 'shop' ? 2:6;

$attr_pre = 'ext_';
$table_handle = 'shopmessage';
if($mid == 6) {
	$attr_pre = 'user_';
	$table_handle = 'groupbuyjoin';
}

// 更新排序
if(submitcheck('listsubmit')) {
	$update_item = array('allowshow'=>'allowshow','isrequired'=>'isrequired','allowpost'=>'allowpost');

	// 如果不可用，就不必填，也不为注册项目
	foreach($_POST['display'] as $key=>$value) {
		if($_POST['allowpost'][$key] == 0){
			$_POST['allowshow'][$key] = 0;
			$_POST['isrequired'][$key] = 0;
		}
	}

	foreach($_POST['display'] as $key=>$value) {
		$query = '';
		$key = intval($key);
		$value = intval($value);

		$sql_set = '';

		foreach($update_item as $k=>$v){
			if($_POST[$update_item[$k]][$key] ==1){
				$sql_set .= ', '.$v.'=1';
			}else{
				$sql_set .= ', '. $v .'=0';
			}
		}

		if($key>0 && $value>0) {
			$query = 'UPDATE '.tname('modelcolumns').' SET displayorder=\''.$value.'\' ' . $sql_set .' WHERE id=\''.intval($key).'\'; ';
			DB::query($query);
		}
	}

	updatemodel('modelname','shop');
	updatemodel('modelname','groupbuy');
	cpmsg('update_success', '?action=attr&type='.$_GET['type']);
}

// 处理提交的删除操作
if($_GET['op']=='del' && !empty($attrid)) {

	$fieldname = DB::result_first('select fieldname from '.tname('modelcolumns').' where id='.$attrid);
	if(!preg_match('/^ext_',$fieldname)){
		DB::query('DELETE FROM '.tname('modelcolumns')." WHERE id='".$attrid."';");
		updatemodel('modelname','shop');
		updatemodel('modelname','groupbuy');
	}else{
		cpmsg('The SYS item cannot be deleted .');
	}

	cpmsg('attr_has_deleted','?action=attr&type='.$_GET['type']);
}

// 处理提交过来的增加和修改操作
if(submitcheck('valuesubmit')){
	$arr_data = array();
	$arr_data['fieldtitle'] = $_POST['fieldtitle'];
	$arr_data['formtype'] = $_POST['formtype'];
	$arr_data['fieldlength'] = $_POST['fieldlength'];
	$arr_data['fielddefault'] = $_POST['fielddefault'];
	$arr_data['fieldcomment'] = $_POST['fieldcomment'];
	$arr_data['isrequired'] = $_POST['isrequired'];
	$arr_data['allowshow'] = $_POST['allowshow'];
	$arr_data['allowpost'] = $_POST['allowpost'];
	$arr_data['thumbsize'] = $_POST['thumb']['width'].",".$_POST['thumb']['height'];

	if(empty($arr_data['formtype'])) {
		$arr_data['formtype'] = "text";
	}
	if(!empty($arr_data['allowshow'])) {
		$arr_data['allowshow'] = 1;
	}
	if(empty($arr_data['isrequired'])) {
		$arr_data['isrequired'] = 0;
	}


	if(empty($arr_data['fieldtitle'])) {
		$arr_errors[] = array('fieldtitle'=>$lang['fieldtitle_comment']);
		cpmsg('fieldtitle_comment','', '','' ,'', '', $arr_errors);
	}


	$arr_data['mid']=$mid;
	$arr_data['fieldtype'] = 'VARCHAR';
	if($arr_data['allowpost'] != '1'){
		$arr_data['isrequired']=0;
		$arr_data['allowshow'] = 0;
	}

	if($arr_data['formtype'] != 'img'){
		$arr_data['formtype'] = 'text';
		$arr_data['isimage'] = 0;
	}else{
		$arr_data['fieldlength'] = 200;
		$arr_data['isimage'] = 1;
	}

	if(intval($arr_data['fieldlength']) < 1){
		$arr_data['fieldlength'] = 200;
	}elseif(intval($arr_data['fieldlength']) > 255){
		cpmsg('ext_field_length_error');
	}

	if($_GET['op']=='add') {
		$attrid = inserttable('modelcolumns',$arr_data,1);
		$query = DB::fetch(DB::query('SELECT max(displayorder) as displayorder FROM '.tname('modelcolumns').''));
		updatetable('modelcolumns',array('fieldname'=>$attr_pre.$attrid,'displayorder'=>(intval($query['displayorder'])+1)),'id='.$attrid);
		DB::query('ALTER TABLE `'.tname($table_handle).'` ADD `'.$attr_pre.$attrid.'` '.$arr_data['fieldtype'].'( '.$arr_data['fieldlength'].' ) ');

	} elseif($_GET['op']=='edit') {
		$field_len = DB::result_first('select fieldlength from '.tname('modelcolumns').' where id='.$attrid);
		if(intval($field_len) > intval($_POST['fieldlength'])){
			cpmsg('ext_field_length_error');
		}
		updatetable('modelcolumns',$arr_data,'id='.$attrid);
		DB::query('ALTER TABLE `'.tname($table_handle).'` CHANGE `'.$attr_pre.$attrid.'` `'.$attr_pre.$attrid.'` VARCHAR( '.$arr_data['fieldlength'].' ) ');
	}

	updatemodel('modelname','shop');
	updatemodel('modelname','groupbuy');
	if($_GET['op'] == 'add') {
		cpmsg('message_success', '?action=attr&type='.$_GET['type']);
	} else {
		cpmsg('update_success', '?action=attr&type='.$_GET['type']);
	}
}

if($_GET['op'] == 'add' || $_GET['op'] == 'edit') {

	shownav('global', 'attr_'.$_GET['op']);
	showsubmenu('attr_'.$_GET['op']);
	showformheader('attr&type='.$_GET['type'].'&op='.$_GET['op']);
	showtableheader('');
	if($_GET['op']=='edit'){
		if($attrid > 0){
			$query = DB::query('select * from '.tname('modelcolumns').' where id='.$attrid);
			$attritem = DB::fetch($query);
			list($attritem['thumb']['width'],$attritem['thumb']['height']) = explode(',',$attritem['thumbsize']);
		}else{
			cpmsg('ID ERROR .');
		}
	}else{
		$attritem['allowpost']=1;
		$attritem['allowshow']=1;
	}

	showsetting('fieldtitle', 'fieldtitle', $attritem['fieldtitle'], 'text', '', '', '', '', '<span style="color:red">*</span>');
	showsetting('fieldcomment', 'fieldcomment', $attritem['fieldcomment'], 'text');

	showsetting('fieldlength', 'fieldlength', $attritem['fieldlength'], 'text');
	showsetting('fielddefault', 'fielddefault', $attritem['fielddefault'], 'text');

	if($_GET['type'] == 'shop') {
		showsetting('attr_type',
				array('formtype',
					array(
						array('text', $lang['attr_text'], array('attrext' => 'none')),
						array('img', $lang['attr_img'], array('attrext' => ''))
						)
					), $attritem['formtype'], 'mradio'
				);

		showtagheader('tbody', 'attrext', ($attritem['formtype'] == 'img'), 'sub');
		showsetting('thumb_size',
				array('thumb[width]', 'thumb[height]'),
				array(intval($attritem['thumb']['width']), intval($attritem['thumb']['height'])), 'multiply'
				);
		showtagfooter('tbody');

		showsetting('allowpost', 'allowpost', $attritem['allowpost'], 'radio');
		showsetting('isrequired', 'isrequired', $attritem['isrequired'], 'radio');
		showsetting('allowshow', 'allowshow', $attritem['allowshow'], 'radio');
	} elseif ($_GET['type'] == 'groupby') {
		showsetting('allowpost', 'allowpost', $attritem['allowpost'], 'radio');
	}

	showhiddenfields(array('attrid' => $_GET['attrid']));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
} else {
	// 列表页
	shownav('global', 'menu_attr');
	showsubmenu('menu_attr', array(
		array('attr_shop', 'attr&type=shop', $_GET['type'] == 'shop' ? 1:0),
		array('attr_groupby', 'attr&type=groupby', $_GET['type'] == 'groupby' ? 1:0)
	));
	showformheader('attr&type='.$_GET['type']);
	showtableheader('');

	if($_GET['type'] == 'shop') {
		showsubtitle(array('display_order', 'ext_attr_name','allowpost','isrequired','allowshow','operation'));
	} elseif($_GET['type'] == 'groupby') {
		showsubtitle(array('display_order', 'ext_attr_name','allowpost','operation'));
	}


	$query = DB::query('SELECT * FROM '.tname('modelcolumns').' WHERE mid = '.$mid.' order by displayorder' );
	while($value = DB::fetch($query)){

		if(($_GET['type'] == 'shop') && !preg_match('/^ext_|^applicant/',$value['fieldname'])){
			continue;
		}

		if(($_GET['type'] == 'groupby') && !preg_match('/^user_/',$value['fieldname'])){
			continue;
		}

		$rowItem = array();
		$rowItem[] = '<input name="display['.$value['id'].']" type="text" size="2" value="'.$value['displayorder'].'" />';
		$rowItem[] = $value['fieldtitle'];
		$rowItem[] = '<input name="allowpost['.$value['id'].']" value="1" type="checkbox" '.(($value['allowpost'])?'checked':'').' />';
		if($_GET['type'] == 'shop') {
			$rowItem[] = '<input name="isrequired['.$value['id'].']" value="1" type="checkbox" '.(($value['isrequired'])?'checked':'').' />';
			$rowItem[] = '<input name="allowshow['.$value['id'].']" value="1" type="checkbox" '.(($value['allowshow'])?'checked':'').' />';
		}

		if(preg_match('/^ext_|^user_/',$value['fieldname'])){
			$rowItem[] = '[<a href="?action=attr&type='.$_GET['type'].'&op=edit&attrid='.$value['id'].'">'.lang('edit').'</a>] [<a href="?action=attr&type='.$_GET['type'].'&op=del&attrid='.$value['id'].'">'.lang('nav_del').'</a>]';
		}else{
			$rowItem[] = '-- / --';
		}

		showtablerow('', array(), $rowItem);
	}
	showtablerow('', array(), array('','<a class="addtr" href="?action=attr&op=add&type='.$_GET['type'].'">'.lang('attr_add').'</a>','','',''));
	showsubmit('listsubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}

function ls_shop_attr() {

}
?>