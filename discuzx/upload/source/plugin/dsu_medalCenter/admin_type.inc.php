<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: admin_type.inc.php 7 2010-11-10 01:51:23Z chuzhaowei@gmail.com $
*/
(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && exit('Access Denied');

if(submitcheck('typesubmit')){
	if(is_array($_G['gp_delete'])) {
		$ids = $comma = '';
		foreach($_G['gp_delete'] as $id) {
			$ids .= "$comma'$id'";
			$comma = ',';
		}
		DB::query("DELETE FROM ".DB::table('dsu_medaltype')." WHERE typeid IN ($ids)");
	}

	if(is_array($_G['gp_name'])) {
		foreach($_G['gp_name'] as $id => $val) {
			DB::query("UPDATE ".DB::table('dsu_medaltype')." SET name=".($_G['gp_name'][$id] ? '\''.dhtmlspecialchars($_G['gp_name'][$id]).'\'' : 'name').", displayorder='".intval($_G['gp_displayorder'][$id])."' WHERE typeid='$id'");
		}
	}
		
	if(is_array($_G['gp_newname'])) {
		foreach($_G['gp_newname'] as $key => $value) {
			if($value != ''){
				$data = array(
					'name' => dhtmlspecialchars($value),
					'displayorder' => intval($_G['gp_newdisplayorder'][$key]),
				);
				DB::insert('dsu_medaltype', $data);
			}
		}
	}
	
	cpmsg('分类更新成功！', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_type', 'succeed');
}else{
	showformheader("plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_type", '', 'configform');
	showtableheader('勋章分类');
	showtablerow('class="header"', array('class="td25"','class="td28"',''), array(
		cplang(''),
		cplang('显示顺序'),
		cplang('分类名称'),
	));
	$query = DB::query("SELECT * FROM ".DB::table('dsu_medaltype')." WHERE 1 ORDER BY displayorder");
	while($typeinfo = DB::fetch($query)) {
		$typeinfo['displayorder'] = intval($typeinfo['displayorder']);
		showtablerow('', array('class="td25"','class="td28"',''), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$typeinfo[typeid]\">",
			"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$typeinfo[typeid]]\" value=\"$typeinfo[displayorder]\">",
			"<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$typeinfo[typeid]]\" value=\"$typeinfo[name]\">",
		));
	}
		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
		[1,'<input type="text" class="txt" name="newname[]" size="15">'],
	]
];
</script>
EOT;
	echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">添加新分类</a></div></td></tr>';
	showsubmit('typesubmit', 'submit', 'del');
	showtablefooter();
	showformfooter();
}
?>