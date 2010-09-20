<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: batch.attribute.php 4413 2010-09-13 09:10:48Z xuhui $
 */

if(!empty($_REQUEST['ajax'])) {
	require_once('./common.php');
	$_POST['valueid'] = intval($_POST['valueid']);
	$_GET['typeid'] = intval($_GET['typeid']);
	$_GET['itemid'] = intval($_GET['itemid']);
	if($_POST['op'] == 'delete' && pkperm('isadmin')) {
		DB::query("DELETE FROM ".tname('attrvalue')." WHERE `attr_valueid` = '{$_POST['valueid']}'");
		echo $_POST['valueid'];
	}
	$categorylist = getmodelcategory('good');
	empty($_GET['typeid'])? exit(''):'';
	$_GET['itemid'] = !empty($_GET['itemid']) ? $_GET['itemid'] : 0;
	echo $attrsettings = getattributesettings($_GET['typeid'], $_GET['itemid']);

}

/**
 * 读取筛选器
 * @param $var - 变量名   （最好标注下类型）
 * @param $type_id - 商品分类id
 * @param $life - 生命期
 * @param $prefix - 前缀
 * @return
 */
function getattribute($type_id) {
	global $_G, $_SGLOBAL, $lang, $_BCACHE, $_SBLOCK;
	$type_id = intval($type_id);
	if(!empty($_SGLOBAL['attributes_'.$type_id])) {
		return $_SGLOBAL['attributes_'.$type_id];
	}
	$query = DB::query("SELECT a.*,v.attr_valueid,v.attr_text FROM ".tname('attribute')." a LEFT JOIN ".tname('attrvalue')." v ON a.attr_id = v.attr_id WHERE a.cat_id = '$type_id' ORDER BY a.displayorder ASC, v.displayorder ASC, v.attr_valueid ASC");

	while($result = DB::fetch($query)) {
		if(empty($attributes[$result['attr_id']]))
		$attributes[$result['attr_id']] = array('attr_name'=>$result['attr_name'], 'attr_row'=> $result['attr_row'], 'attr_type'=>$result['attr_type']);
		if($result['attr_type'] == 0 ) {
			$attributes[$result['attr_id']]['attr_values'][$result['attr_valueid']] = $result;
		}
	}
	return $_SGLOBAL['attributes_'.$type_id] = $attributes;
}

/**
 * 格式化筛选器
 * @param $var - 变量名   （最好标注下类型）
 * @param $type_id - 商品分类id
 * @param $attrs - 已筛选属性值
 * @param $prefix - 前缀
 * @return
 */
function formatattrs($type_id, $attrvalues = array(), $keyword = '', $phpfile='goodsearch.php') {
	$type_id = intval($type_id);
	$attributes = getattribute($type_id);
	if(!empty($attributes)) {
		$attrform .= '<table>';
		foreach($attributes as $attr_id=>$attr) {
			if($attr['attr_type'] == 0) {
				$attrform .= '<tr><td>'.$attr['attr_name'].'</td><td><a'.(!array_key_exists($attr_id, $attrvalues)?' style="color:red;"':'').' href="'.getattrurl($type_id, $attr_id, null, $attrvalues, $keyword, $phpfile).'">'.$GLOBALS[lang]['nolimit'].'</a>';
				foreach($attr['attr_values'] as $key=>$value) {
					$attrform .= '<a'.($attrvalues[$attr_id]==$value['attr_valueid']?' style="color:red;"':'').' href="'.getattrurl($type_id, $attr_id, $value['attr_valueid'], $attrvalues, $keyword, $phpfile).'">'.$value['attr_text'].'</a>';
				}
				$attrform .= '</td></tr>';
			}
		}
		$attrform .= '</table>';
	}
	return $attrform;

}

/**
 * 格式化，并返回筛选器链接
 * @param $type_id - 商品分类id
 * @param $attr_id - 属性id
 * @param $attr_value - 属性值
 * @param $attrvalues - 当前筛选条件
 * @return
 */
function getattrurl($type_id, $attr_id, $attr_value = NULL, $attrvalues=array(), $keyword = '', $phpfile='goodsearch.php') {
	$type_id = intval($type_id);
	$attr_id = intval($attr_id);
	$params = $urlkey = array();
	if(count($attrvalues)==0) {
		if($attr_value != null) {
			$params[$attr_id] = intval($attr_id).'-'.intval($attr_value);
		}
	} else {
		foreach($attrvalues as $key=>$value) {
			$params[$key] = intval($key).'-'.intval($value);
		}
		if($attr_value == null) {
			unset($params[$attr_id]);
		} else {
			$params[$attr_id] = intval($attr_id).'-'.intval($attr_value);
		}
	}
	$keyword && $urlkey[] = 'keyword='.$keyword;
	$type_id && $urlkey[] = 'catid='.$type_id;
	$params && $urlkey[] = 'params='.implode('_', $params);
	return $phpfile.'?'.implode('&', $urlkey);
}

/**
 * 根据链接URL，返回当前已选筛选条件
 * @param $params - url中的参数
 * @return $attrvalues - 当前筛选条件
 */
function getattrvalues($params) {
	$params = trim(strip_tags($params));
	$params_arr = explode('_', $params);
	foreach($params_arr as $key=>$value) {
		$attr = explode('-', $value);
		if(count($attr) > 2) {
			$dom = '';
			foreach($attr as $kkey=>$vvalue) {
				$kkey = intval($kkey);
				$waule = intval($walue);
				if(empty($kkey) || empty($walue)) {
					unset($attr[$waule]);
				}
				if($kkey > 1) $attr[1] .= $dom.$vvalue;
				$dom = '-';
			}
		}
		$attrvalues[$attr[0]] = $attr[1];
	}
	return $attrvalues;
}

/**
 * 返回分类或者子分类列表
 * @param $catid - 分类id
 * @param $categorylist - 全部分类列表
 * @return $searchcats
 */
function getsearchcats($categorylist, $catid = 0) {
	$catid = intval($catid);
	$searchcats = NULL;
	foreach($categorylist as $key=>$value) {
		if($value['upid'] == $catid) {
			$searchcats[] = $value;
			$_searchcats = null;
			$_searchcats = getsearchcats($categorylist, $value['catid']);
			if(is_array($_searchcats)) {
				//print_r($_searchcats);
				foreach($_searchcats as $_key=>$_catid) {

					if($_catid['catid']) {
						$searchcats[] = $_catid;
					}
				}
			}
		}
	}
	return $searchcats;
}

/**
 * 审核信息追加属性设置
 * @param $type_id - 商品分类id
 * @param $life - 生命期
 * @param $prefix - 前缀
 * @return $attrsettings
 */
function getattributesettingsupdate($catid, $attrvalues) {
	global $_G, $_SGLOBAL, $lang, $_BCACHE,$_SBLOCK;

	$attributes = getattribute($catid);
	foreach($attrvalues as $attrid=>$value) {
		$itemattr['attr_id_'.$attributes[$attrid]['attr_row']] = $value;
	}
	$attrsettings = '<table class="sub" style="width:100%;">';
	//print_r($itemattr);
	foreach($attributes as $attr_id=>$attr) {
		if($attr['attr_type'] == 0) {
			$valuesops = '';
			if(!in_array($itemattr['attr_id_'.$attr['attr_row']], array_keys($attr['attr_values']))) {
				$noattrvalue = true;
			} else {
				$noattrvalue = false;
			}
			foreach($attr['attr_values'] as $key=>$value) {
				if($noattrvalue == true) {
					$itemattr['attr_id_'.$attr['attr_row']] = $value['attr_valueid'];
				}
				$valuesops.= '<option value="'.$value['attr_valueid'].'"'.($itemattr['attr_id_'.$attr['attr_row']] && $itemattr['attr_id_'.$attr['attr_row']] == $value['attr_valueid'] ? ' selected="selected"' : '').'>'.$value['attr_text'].'</option>';
				$noattrvalue = false;
			}
			$attrsettings .= '<tr><td class="td27" width="80px">'
			.$attr['attr_name'].$lang['colon']
			.'</td><td class="td27"><select name="attr_ids['.$attr_id.']">'.$valuesops.'</select>'
			.'</td></tr>';
		} else {
			$valuesops = '<input type="text" name="attr_ids['.$attr_id.']" '.($itemattr['attr_id_'.$attr['attr_row']]?'value="'.$itemattr['attr_id_'.$attr['attr_row']].'" ':'' ).'/>';
			$attrsettings .= '<tr><td class="td27" width="80px">'
			.$attr['attr_name'].$lang['colon']
			.'</td><td class="td27">'.$valuesops
			.'</td></tr>';
		}

	}
	$attrsettings .= '</table>';
	return $attrsettings;
}

/**
 * 追加属性设置
 * @param $type_id - 商品分类id
 * @param $life - 生命期
 * @param $prefix - 前缀
 * @return $attrsettings
 */
function getattributesettings($type_id, $itemid) {
	global $_G, $_SGLOBAL, $lang, $_BCACHE,$_SBLOCK;
	$type_id = intval($type_id);
	$itemid = intval($itemid);
	if($itemid) {
		$query = DB::query("SELECT * FROM ".tname("itemattribute")." WHERE itemid = '$itemid'");
		$itemattr = DB::fetch($query);
	}
	$attributes = getattribute($type_id);
	if(count($attributes) < 1) {
		return false;
	}
	if(!empty($itemattr)) {
		foreach($attributes as $attr_id=>$attribute) {
			if($attribute['attr_type'] == 1) {
				$attr_id_s[] = $attr_id;
			}
		}
		if(!empty($attr_id_s)) {
			$query = DB::query("SELECT * FROM ".tname("attrvalue_text")." WHERE attr_id in ('".implode("', '", $attr_id_s)."') AND item_id = '$itemid'", DB_FETCHMODE_ASSOC);
			while($row = DB::fetch($query)) {
				$itemattr['attr_id_'.$attributes[$row['attr_id']]['attr_row']] = $row['attr_text'];
			}
		}

	}
	$attrsettings = '<table class="sub" style="width:100%;">';
	foreach($attributes as $attr_id=>$attr) {
		if($attr['attr_type'] == 0) {
			$valuesops = '';
			foreach($attr['attr_values'] as $key=>$value) {
				$valuesops.= '<option value="'.$value['attr_valueid'].'"'.($itemattr['attr_id_'.$attr['attr_row']] && $itemattr['attr_id_'.$attr['attr_row']] == $value['attr_valueid'] ? ' selected="selected"' : '').'>'.$value['attr_text'].'</option>';
			}
			$attrsettings .= '<tr><td class="td27" width="80px">'
			.$attr['attr_name'].$lang['colon']
			.'</td><td class="td27"><select name="attr_ids['.$attr_id.']">'.$valuesops.'</select>'
			.'</td></tr>';
		} else {
			$valuesops = '<input type="text" name="attr_ids['.$attr_id.']" '.($itemattr['attr_id_'.$attr['attr_row']]?'value="'.$itemattr['attr_id_'.$attr['attr_row']].'" ':'' ).'/>';
			$attrsettings .= '<tr><td class="td27" width="80px">'
			.$attr['attr_name'].$lang['colon']
			.'</td><td class="td27">'.$valuesops
			.'</td></tr>';
		}

	}
	$attrsettings .= '</table>';
	return $attrsettings;
}

/**
 * 更新属性设置
 * @param $type_id - 商品分类id
 * @param $life - 生命期
 * @param $prefix - 前缀
 * @return $attrsettings
 */
function setattributesettings($type_id, $itemid, $attrvalues = NULL) {
	global $_G, $_SGLOBAL, $lang, $_BCACHE,$_SBLOCK;
	$type_id = intval($type_id);
	$itemid = intval($itemid);

	if(!empty($attrvalues)) {
		$attributes = getattribute($type_id);
		$itemattrvalue = array('itemid'=>$itemid, 'catid'=>$type_id);
		foreach($attrvalues as $attr_id=>$attr_value) {
			if($attributes[$attr_id]['attr_type'] == 0 && in_array($attr_value, array_keys($attributes[$attr_id]['attr_values']))) {
				$itemattrvalue['attr_id_'.$attributes[$attr_id]['attr_row']] = $attr_value;
			} elseif($attributes[$attr_id]['attr_type'] == 1) {
				DB::query("REPLACE INTO ".tname("attrvalue_text")." (`attr_id`,`item_id`,`attr_text`) values ( '$attr_id', '$itemid', '".$attr_value."')");
				$itemattrvalue['attr_id_'.$attributes[$attr_id]['attr_row']] = 0;
			}
		}
		if(count($itemattrvalue)>2) {
			inserttable("itemattribute", $itemattrvalue, 0,true);
		}
		return true;
	} else {
		return false;
	}

}

/**
 * 统计分类下结果数之和
 * @param $catid - 分类id
 * @param $catnums - 分类结果数
 * @return $catnums
 */
function getcatcount($catid=0, $catnums) {
	global $_G, $categorylist;
	$catid = intval($catid);
	$_cats = getsearchcats($categorylist, $catid);
	if(is_array($_cats)) {
		foreach($_cats as $k=>$vv) {
			$catnums[$catid] += $catnums[$vv['catid']];
		}
	}

	return $catnums;
}

/**
 * 属性搜索，获得使用当前筛选属性结果的itemid
 * @param $attrvalues - 分类id
 * @return $attr_in，NULL为没有筛选条件，0为无结果
 */
function getattr_in($catid, $attrvalues) {
	global $_G, $_SGLOBAL, $_BCACHE, $_SBLOCK;
	$attr_in = NULL;
	$attributes = getattribute($catid);
	if(count($attrvalues)>=1) {
		$where = array(" catid = $catid");
		$attr_num = 0;
		foreach($attrvalues as $key=>$value) {
			$key = intval($key);
			$value = intval($value);
			if(empty($key) || empty($value)) {
				unset($attrvalues[$key]);
			} else {
				$attr_num++;
				$where[] = " attr_id_".$attributes[$key]['attr_row']." = $value";
			}
		}
		$wheresql = implode(' AND ', $where);
		$wheresql && $wheresql = ' WHERE '.$wheresql;
		if ($attr_num > 0) {
			$sql = 'SELECT itemid FROM '.tname("itemattribute").$wheresql;
			$_BCACHE->cachesql('attr', $sql, 0, 0, 1000, 0, 'sitelist', 'attr');
			$itemids = $comma = '';
			foreach($_SBLOCK['attr'] as $result) {
				$itemids .= "$comma'$result[itemid]'";
				$comma = ',';
			}
			if($itemids) {
				$attr_in = 'i.itemid IN ('.$itemids.')';
			} else {
				$attr_in = '0';
			}
		}
	}
	return $attr_in;
}

/**
 * 读取单个对象的所有属性
 * @param $itemid - 对象id
 * @param $type_id - 分类id
 * @return $itemattr - 该对象的所有属性名和值
 */
function getattr($itemid, $type_id) {
	global $_G, $_SGLOBAL;

	$attr_value = '';
	$itemattr = array();
	$type_id = intval($type_id);
	$itemid = intval($itemid);
	$attributes = getattribute($type_id);
	if(!empty($attributes)) {
		$query = DB::query("SELECT * FROM ".tname("itemattribute")." WHERE itemid = '$itemid'");
		$itemattr = DB::fetch($query);
		if(!empty($itemattr)) {
			foreach($attributes as $attr_id=>$attribute) {
				if($attribute['attr_type'] == 1) {
					$attr_id_s[] = $attr_id;
				}
			}
			if(!empty($attr_id_s)) {
				$query = DB::query("SELECT * FROM ".tname("attrvalue_text")." WHERE attr_id in ('".implode("', '", $attr_id_s)."') AND item_id = '$itemid'");
				while($row = DB::fetch($query)) {
					$itemattr['attr_id_'.$attributes[$row['attr_id']]['attr_row']] = $row['attr_text'];
				}
			}

		}
		foreach($attributes as $key=>$attr) {

			$itemattrshow[$key]['attr_name'] = $attr['attr_name'];
			$itemattrshow[$key]['attr_valueid'] = ($attr['attr_type'] == 1?$itemattr['attr_id_'.$attr['attr_row']]:($attr['attr_values'][$itemattr['attr_id_'.$attr['attr_row']]]?$attr['attr_values'][$itemattr['attr_id_'.$attr['attr_row']]]['attr_text']:''));

		}
	}
	return $itemattrshow;
}

function getitemattributes($type_id, $itemid) {
	global $_G, $_SGLOBAL, $lang, $_BCACHE,$_SBLOCK;
	$type_id = intval($type_id);
	$itemid = intval($itemid);
	if($itemid) {
		$query = DB::query("SELECT * FROM ".tname("itemattribute")." WHERE itemid = '$itemid' AND catid = '$type_id'");
		$itemattr = DB::fetch($query);
	}
	$attributes = getattribute($type_id);
	if(count($attributes) < 1) {
		return false;
	}
	if(!empty($itemattr)) {
		foreach($attributes as $attr_id=>$attribute) {
			if($attribute['attr_type'] == 1) {
				$attr_id_s[] = $attr_id;
			}
		}
		if(!empty($attr_id_s)) {
			$query = DB::query("SELECT * FROM ".tname("attrvalue_text")." WHERE attr_id in ('".implode("', '", $attr_id_s)."') AND item_id = '$itemid'", DB_FETCHMODE_ASSOC);
			while($row = DB::fetch($query)) {
				$itemattr['attr_id_'.$attributes[$row['attr_id']]['attr_row']] = $row['attr_text'];
			}
		}
	}
	//print_r($itemattr);
	$itemattributestr = '';
	foreach($attributes as $attr_id=>$attr) {
		if($attr['attr_type'] == 0) {
			$valuesops = '';
			foreach($attr['attr_values'] as $key=>$value) {
				$valuesops.= '<option value="'.$value['attr_valueid'].'"'.($itemattr['attr_id_'.$attr['attr_row']] && $itemattr['attr_id_'.$attr['attr_row']] == $value['attr_valueid'] ? ' selected="selected"' : '').'>'.$value['attr_text'].'</option>';
				if($itemattr['attr_id_'.$attr['attr_row']] && $itemattr['attr_id_'.$attr['attr_row']] == $value['attr_valueid'])
					$itemattrvalue = $value['attr_text'];
			}
			$attrsettings .= '<tr><td class="td27" width="80px">'
			.$attr['attr_name'].$lang['colon']
			.'</td><td class="td27"><select name="attr_ids['.$attr_id.']">'.$valuesops.'</select>'
			.'</td></tr>';
			$itemattributestr .= $attr['attr_name'].$lang['colon'].$itemattrvalue."    ";
			
		} else {
			$valuesops = '<input type="text" name="attr_ids['.$attr_id.']" '.($itemattr['attr_id_'.$attr['attr_row']]?'value="'.$itemattr['attr_id_'.$attr['attr_row']].'" ':'' ).'/>';
			$attrsettings .= '<tr><td class="td27" width="80px">'
			.$attr['attr_name'].$lang['colon']
			.'</td><td class="td27">'.$valuesops
			.'</td></tr>';
			$itemattrvalue = $itemattr['attr_id_'.$attr['attr_row']];
			$itemattributestr .= $attr['attr_name'].$lang['colon'].$itemattrvalue."    ";
		}
	}
	return $itemattributestr;
}
?>