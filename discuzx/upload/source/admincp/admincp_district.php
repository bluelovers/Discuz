<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_block.php 7212 2010-03-30 13:05:47Z xupeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

shownav('global', 'district');

$values = array(intval($_GET['pid']), intval($_GET['cid']), intval($_GET['did']));
$elems = array($_GET['province'], $_GET['city'], $_GET['district']);
$level = 1;
$upids = array(0);
$theid = 0;
for($i=0;$i<3;$i++) {
	if(!empty($values[$i])) {
		$theid = intval($values[$i]);
		$upids[] = $theid;
		$level++;
	} else {
		for($j=$i; $j<3; $j++) {
			$values[$j] = '';
		}
		break;
	}
}

if(submitcheck('editsubmit')) {

	$delids = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_district')." WHERE upid ='$theid'");
	while($value = DB::fetch($query)) {
		if(!isset($_POST['district'][$value['id']])) {
			$delids[] = $value['id'];
		} elseif($_POST['district'][$value['id']] != $value['name']) {
			DB::update('common_district', array('name'=>$_POST['district'][$value['id']]), array('id'=>$value['id']));
		}
	}
	if($delids) {
		$ids = $delids;
		for($i=$level; $i<4; $i++) {
			$query = DB::query('SELECT id FROM '.DB::table('common_district')." WHERE upid IN (".dimplode($ids).')');
			$ids = array();
			while($value=DB::fetch($query)) {
				$value['id'] = intval($value['id']);
				$delids[] = $value['id'];
				$ids[] = $value['id'];
			}
			if(empty($ids)) {
				break;
			}
		}
		DB::query('DELETE FROM '.DB::table('common_district')." WHERE id IN (".dimplode($delids).')');
	}
	if(!empty($_POST['districtnew'])) {
		$inserts = array();
		foreach($_POST['districtnew'] as $value) {
			$value = trim($value);
			if(!empty($value)) {
				$inserts[] = "('$value', '$level',  '$theid')";
			}
		}
		if($inserts) {
			DB::query('INSERT INTO '.DB::table('common_district')."(`name`, level, upid) VALUES ".implode(',',$inserts));
		}
	}
	cpmsg('setting_district_edit_success', 'action=district&pid='.$values[0].'&cid='.$values[1].'&did='.$values[2], 'succeed');

} else {
	showsubmenu('district');
	showtips('district_tips');

	showformheader('district&pid='.$values[0].'&cid='.$values[1].'&did='.$values[2]);
	showtableheader();

	$options = array(1=>array(), 2=>array(), 3=>array());
	$thevalues = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_district')." WHERE upid IN (".dimplode($upids).')');
	while($value = DB::fetch($query)) {
		$options[$value['level']][] = array($value['id'], $value['name']);
		if($value['upid'] == $theid) {
			$thevalues[] = array($value['id'], $value['name']);
		}
	}

	$names = array('province', 'city', 'district');
	for($i=0; $i<3;$i++) {
		$elems[$i] = !empty($elems[$i]) ? $elems[$i] : $names[$i];
	}
	$html = '';
	for($i=0;$i<3;$i++) {
		$l = $i+1;
		$jscall = "refreshdistrict('$elems[0]', '$elems[1]', '$elems[2]')";
		$html .= '<select name="'.$elems[$i].'" id="'.$elems[$i].'" onchange="'.$jscall.'">';
		$html .= '<option value="">'.lang('spacecp', 'district_level_'.$l).'</option>';
		foreach($options[$l] as $option) {
			$selected = $option[0] == $values[$i] ? ' selected="selected"' : '';
			$html .= '<option value="'.$option[0].'"'.$selected.'>'.$option[1].'</option>';
		}
		$html .= '</select>&nbsp;&nbsp;';
	}
	showtablerow('id="districtbox"', array('colspan=2'), array(cplang('district_choose').' &nbsp; '.$html));
	foreach($thevalues as $value) {
		showtablerow('id="td_'.$value[0].'"', array('', ''), array(
			'<p id="p_'.$value[0].'">'
			.'<input type="text" id="input_'.$value[0].'" class="txt" name="district['.$value[0].']" value="'.$value[1].'" style="display: none;" />'
			.'<span id="span_'.$value[0].'">'.$value[1].'</span>'
			.'</p>',
			'<a href="javascript:;" onclick="editdistrict('.$value[0].');return false;">'.cplang('edit').'</a>&nbsp;&nbsp;'
			.'<a href="javascript:;" onclick="deletedistrict('.$value[0].');return false;">'.cplang('delete').'</a>'
		));
	}
	showtablerow('', array('colspan=2'), array(
			'<div><a href="javascript:;" onclick="addrow(this, 0, 1);return false;" class="addtr">'.cplang('add').'</a></div>'
		));
	showsubmit('editsubmit', 'submit');
	$adminurl = ADMINSCRIPT.'?action=district';
echo <<<SCRIPT
<script type="text/javascript">
var rowtypedata = [
	[[2,'<input type="text" class="txt" name="districtnew[]" value="" />', '']],
];

function refreshdistrict(province, city, district) {
	location.href = "$adminurl"
		+ "&province="+province+"&city="+city+"&district="+district
		+"&pid="+$(province).value + "&cid="+$(city).value+"&did="+$(district).value;
}

function editdistrict(did) {
	$('input_' + did).style.display = "block";
	$('span_' + did).style.display = "none";
}

function deletedistrict(did) {
	var elem = $('p_' + did);
	elem.parentNode.removeChild(elem);
	var elem = $('td_' + did);
	elem.parentNode.removeChild(elem);
}
</script>
SCRIPT;
	showtablefooter();
	showformfooter();
}

?>