<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_click.php 20982 2011-03-09 10:02:57Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$operation = $operation ? $operation : '';
cpheader();

if(empty($operation)) {
	$idtype = in_array($_G['gp_idtype'], array('blogid', 'picid', 'aid')) ? trim($_G['gp_idtype']) : 'blogid';
	if(!submitcheck('clicksubmit')) {

		shownav('style', 'click_edit');
		showsubmenu('nav_click', array(
			array('click_edit_blogid', 'click&idtype=blogid', $idtype == 'blogid' ? 1 : 0),
			array('click_edit_picid', 'click&idtype=picid', $idtype == 'picid' ? 1 : 0),
			array('click_edit_aid', 'click&idtype=aid', $idtype == 'aid' ? 1 : 0),
		));
		showtips('click_edit_tips');
		showformheader('click&idtype='.$idtype);
		showtableheader();
		showtablerow('', array('class="td25"', 'class="td28"', 'class="td25"', 'class="td25"', '', '', '', 'class="td23"', 'class="td25"'), array(
			'',
			cplang('display_order'),
			'',
			cplang('available'),
			cplang('name'),
			cplang('click_edit_image'),
			cplang('click_edit_type'),
		));
		print <<<EOF
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'', 'td25'],
			[1,'<input type="checkbox" name="newavailable[]" value="1">', 'td25'],
			[1,'<input type="text" class="txt" name="newname[]" size="10">'],
			[1,'<input type="text" class="txt" name="newicon[]" size="20">'],
			[1,'', 'td23']
		]
	];
</script>
EOF;
		$query = DB::query("SELECT * FROM ".DB::table('home_click')." WHERE idtype='$idtype' ORDER BY displayorder DESC");
		while($click = DB::fetch($query)) {
			$checkavailable = $click['available'] ? 'checked' : '';
			$click['idtype'] = cplang('click_edit_'.$click['idtype']);
			showtablerow('', array('class="td25"', 'class="td28"', 'class="td25"', 'class="td25"', '', '', '', 'class="td23"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$click[clickid]\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$click[clickid]]\" value=\"$click[displayorder]\">",
				"<img src=\"static/image/click/$click[icon]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$click[clickid]]\" value=\"1\" $checkavailable>",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$click[clickid]]\" value=\"$click[name]\">",
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"icon[$click[clickid]]\" value=\"$click[icon]\">",
				$click['idtype']
			));
		}
		echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['click_edit_addnew'].'</a></div></td></tr>';
		showsubmit('clicksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {
		$ids = array();
		if(is_array($_G['gp_delete'])) {
			foreach($_G['gp_delete'] as $id) {
				$ids[] = $id;
			}
			if($ids) {
				DB::query("DELETE FROM ".DB::table('home_click')." WHERE clickid IN (".dimplode($ids).")");
			}
		}

		if(is_array($_G['gp_name'])) {
			foreach($_G['gp_name'] as $id => $val) {
				$id = intval($id);
				$updatearr = array(
					'name' => dhtmlspecialchars($_G['gp_name'][$id]),
					'icon' => $_G['gp_icon'][$id],
					'idtype' => $idtype,
					'available' => intval($_G['gp_available'][$id]),
					'displayorder' => intval($_G['gp_displayorder'][$id]),
				);
				DB::update('home_click', $updatearr, array('clickid' => $id));
			}
		}

		if(is_array($_G['gp_newname'])) {
			foreach($_G['gp_newname'] as $key => $value) {
				if($value != '' && $_G['gp_newicon'][$key] != '') {
					$data = array(
						'name' => dhtmlspecialchars($value),
						'icon' => $_G['gp_newicon'][$key],
						'idtype' => $idtype,
						'available' => intval($_G['gp_newavailable'][$key]),
						'displayorder' => intval($_G['gp_newdisplayorder'][$key])
					);
					DB::insert('home_click', $data);
				}
			}
		}

		$keys = $ids = $_G['cache']['click'] = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_click')." WHERE available='1' ORDER BY displayorder DESC");
		while($value = DB::fetch($query)) {
			if(count($_G['cache']['click'][$value['idtype']]) < 8) {
				$keys[$value['idtype']] = $keys[$value['idtype']] ? ++$keys[$value['idtype']] : 1;
				$_G['cache']['click'][$value['idtype']][$keys[$value['idtype']]] = $value;
			} else {
				$ids[] = $value['clickid'];
			}
		}
		if($ids) {
			DB::query("UPDATE ".DB::table('home_click')." SET available='0' WHERE clickid IN (".dimplode($ids).")");
		}
		updatecache('click');
		cpmsg('click_edit_succeed', 'action=click&idtype='.$idtype, 'succeed');
	}

}
?>