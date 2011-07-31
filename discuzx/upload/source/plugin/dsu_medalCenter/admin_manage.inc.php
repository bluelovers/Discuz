<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: admin_manage.inc.php 60 2011-07-20 13:04:22Z chuzhaowei@gmail.com $
*/
(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && exit('Access Denied');

loadcache('plugin');
$cvars = &$_G['cache']['plugin']['dsu_medalCenter'];
require_once DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/function_common.php';

if(empty($_G['gp_pdo']) || $_G['gp_pdo'] == 'list'){ //列表页面
	if(!submitcheck('medalsubmit')) {
		showtips('<li>本功能用于设置可以颁发给用户的勋章信息，勋章图片中请填写图片文件名，并将相应图片文件上传到 static/image/common/ 目录中。</li>
		<li>如果您想实现一个勋章的多种领取方式，您可以采用复制功能来简化您的操作。</li>');
		showformheader('plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage');
		showtableheader('medals_list', 'fixpadding');
		showsubtitle(array('', 'display_order', 'available', 'name', 'description', 'medals_image', 'medals_type', ''));

?>
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newname[]" size="10">'],
			[1,'<input type="text" class="txt" name="newdescription[]" size="30">'],
			[1,'<input type="text" class="txt" name="newimage[]" size="20">'],
			[1,'', 'td23'],
			[1,'', 'td25']
		]
	];
</script>
<?
		$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." ORDER BY displayorder");
		while($medal = DB::fetch($query)) {
			$checkavailable = $medal['available'] ? 'checked' : '';
			switch($medal['type']) {
				case 0:
					$medal['type'] = cplang('medals_adminadd');
					break;
				case 1:
					$medal['type'] = cplang('medals_register');
					break;
				case 2:
					$medal['type'] = cplang('modals_moderate');
					break;
				case 5:
					$medal['type'] = '积分购买';
					break;
			}
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', '', '', '', 'class="td23"', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$medal[medalid]\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$medal[medalid]]\" value=\"$medal[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$medal[medalid]]\" value=\"1\" $checkavailable>",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$medal[medalid]]\" value=\"$medal[name]\">",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"description[$medal[medalid]]\" value=\"$medal[description]\">",
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"image[$medal[medalid]]\" value=\"$medal[image]\"><img style=\"vertical-align:middle\" src=\"static/image/common/$medal[image]\">",
				$medal[type],
				"<a href=\"".ADMINSCRIPT."?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage&pdo=edit&medalid=$medal[medalid]\" class=\"act\">详情</a> | ".
				"<a href=\"".ADMINSCRIPT."?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage&pdo=copy&medalid=$medal[medalid]\" class=\"act\">复制</a>",
				//"<a href=\"".ADMINSCRIPT."?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage&pdo=view&medalid=$medal[medalid]\" class=\"act\">查看</a> | ",
			));
		}

		echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['medals_addnew'].'</a></div></td></tr>';
		showsubmit('medalsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			DB::query("DELETE FROM ".DB::table('forum_medal')." WHERE medalid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('dsu_medalfield')." WHERE medalid IN ($ids)");
		}

		if(is_array($_G['gp_name'])) {
			foreach($_G['gp_name'] as $id => $val) {
				DB::query("UPDATE ".DB::table('forum_medal')." SET name=".($_G['gp_name'][$id] ? '\''.dhtmlspecialchars($_G['gp_name'][$id]).'\'' : 'name').", available='{$_G['gp_available'][$id]}', description=".($_G['gp_description'][$id] ? '\''.dhtmlspecialchars($_G['gp_description'][$id]).'\'' : 'name').", displayorder='".intval($_G['gp_displayorder'][$id])."', image=".($_G['gp_image'][$id] ? '\''.$_G['gp_image'][$id].'\'' : 'image')." WHERE medalid='$id'");
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($value != '' && $_G['gp_newimage'][$key] != '') {
					$data = array('name' => dhtmlspecialchars($value),
						'available' => $_G['gp_newavailable'][$key],
						'image' => $_G['gp_newimage'][$key],
						'displayorder' => intval($_G['gp_newdisplayorder'][$key]),
						'description' => dhtmlspecialchars($_G['gp_newdescription'][$key]),
					);
					DB::insert('forum_medal', $data);
				}
			}
		}

		updatecache('setting');
		updatecache('medals');
		cpmsg('medals_succeed', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage', 'succeed');
	}
}elseif($_G['gp_pdo'] == 'edit' || $_G['gp_pdo'] == 'copy'){ //勋章编辑页面
	
	$medalid = intval($_G['gp_medalid']);

	if(!submitcheck('medaleditsubmit')) {
		echo '<script type="text/javascript" src="static/js/calendar.js"></script>'; //引入日历的JS
		$medal = DB::fetch_first("SELECT m.*, mf.* FROM ".DB::table('forum_medal')." m LEFT JOIN ".DB::table('dsu_medalfield')." mf ON m.medalid = mf.medalid WHERE m.medalid='$medalid'");

		$medalfieldSetting = (array)unserialize($medal['setting']);
		$checkmedaltype = array($medal['type'] => 'checked');
		$query = DB::query("SELECT typeid, name FROM ".DB::table('dsu_medaltype'));
		$typevar = array('typeidnew', array());
		$typevar[1][] = array(0, '未分类');
		while($tinfo = DB::fetch($query)){
			$typevar[1][] = array($tinfo['typeid'], $tinfo['name']);
		}
		showformheader("plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage&pdo={$_G[gp_pdo]}&medalid=$medalid");
		showtableheader(($_G['gp_pdo'] == 'edit' ? '编辑勋章' : '复制勋章').' - '.$medal['name'], 'nobottom');
		showsetting('medals_name1', 'namenew', $medal['name'], 'text');
		showsetting('medals_img', '', '', '<input type="text" class="txt" size="30" name="imagenew" value="'.$medal['image'].'" ><img src="static/image/common/'.$medal['image'].'">');
		showsetting('medals_type1', '', '', '<ul class="nofloat" onmouseover="altStyle(this);">
			<li'.($checkmedaltype[0] ? ' class="checked"' : '').'><input name="typenew" onclick="$(\'creditbody\').style.display = this.checked ? \'none\' : \'\'" type="radio" class="radio" value="0" '.$checkmedaltype[0].'>&nbsp;'.$lang['medals_adminadd'].'</li>
			<li'.($checkmedaltype[1] ? ' class="checked"' : '').'><input name="typenew" onclick="$(\'creditbody\').style.display = this.checked ? \'none\' : \'\'" type="radio" class="radio" value="1" '.$checkmedaltype[1].'>&nbsp;'.$lang['medals_apply_auto'].'</li>
			<li'.($checkmedaltype[2] ? ' class="checked"' : '').'><input name="typenew" onclick="$(\'creditbody\').style.display = this.checked ? \'none\' : \'\'" type="radio" class="radio" value="2" '.$checkmedaltype[2].'>&nbsp;'.$lang['medals_apply_noauto'].'</li>
			<li'.($checkmedaltype[5] ? ' class="checked"' : '').'><input name="typenew" onclick="$(\'creditbody\').style.display = this.checked ? \'\' : \'none\'" type="radio" class="radio" value="5" '.$checkmedaltype[5].'>&nbsp;积分购买</li></ul>'
		);
		showsetting('medals_expr1', 'expirationnew', $medal['expiration'], 'number');
		showsetting('勋章分类', $typevar, $medal['typeid'], 'select');
		showsetting('medals_memo', 'descriptionnew', $medal['description'], 'textarea');
		foreach(getMedalExtendClass() as $classname => $newclass){ //扩展：显示设置页面（简单项）
			if(method_exists($newclass, 'admincp_show_simple')) $newclass->admincp_show_simple($medalfieldSetting[$classname]);
		}
		showtablefooter();
		foreach(getMedalExtendClass() as $classname => $newclass){ //扩展：显示设置页面
			if(method_exists($newclass, 'admincp_show')) $newclass->admincp_show($medalfieldSetting[$classname]);
		}
		showtableheader('', 'notop');
		showsubmit('medaleditsubmit');
		showtablefooter();
		showformfooter();
	} else {
		if($_G['gp_pdo'] == 'copy'){
			DB::insert('forum_medal', array('type' => $_G['gp_typenew']));
			$medalid = DB::insert_id();
		}
		$formulapermary = array();

		foreach(getMedalExtendClass() as $classname => $newclass){ //扩展：检查提交信息
			if(method_exists($newclass, 'admincp_check')) $newclass->admincp_check();
		}

		$medalfieldSetting = array(); //扩展：保存提交信息
		foreach(getMedalExtendClass() as $classname => $newclass){
			if(method_exists($newclass, 'admincp_save')) $medalfieldSetting[$classname] = $newclass->admincp_save();
		}
		$medalfieldSetting = addslashes(serialize($medalfieldSetting));
		DB::insert('dsu_medalfield',array(
			'medalid' => $medalid,
			'typeid' => intval($_G['gp_typeidnew']),
			'setting' => $medalfieldSetting,
		), false, true);
		
		DB::update('forum_medal', array(
			'name' => $_G['gp_namenew'] ? dhtmlspecialchars($_G['gp_namenew']) : 'name',
			'type' => $_G['gp_typenew'],
			'description' => dhtmlspecialchars($_G['gp_descriptionnew']),
			'expiration' => intval($_G['gp_expirationnew']),
			'permission' => addslashes(serialize($formulapermary)),
			'image' => $_G['gp_imagenew'],
		), "medalid='$medalid'");

		updatecache('medals');
		$msg = $_G['gp_pdo'] == 'edit' ? 'medals_succeed' : '勋章成功复制。';
		cpmsg($msg, 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage', 'succeed');
	}

}elseif($_G['gp_pdo'] == 'view'){ //勋章查看页面
	
}
?>