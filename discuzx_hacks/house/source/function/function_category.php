<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_forumsort.php 7183 2010-03-30 07:58:28Z tiger $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


function categorycache($cachename, $identifier = '') {
	global $_G;

	$cachearray = array('categorysort', 'sortlist', 'channellist', 'arealist', 'usergroup');
	$cachename = in_array($cachename, $cachearray) ? $cachename : '';
	$sortdatalist = $areadatalist = $channeldatalist = array();

	if($cachename == 'categorysort') {
		$sortlist = $templatedata = $stemplatedata = $ptemplatedata = $btemplatedata = $template = array();
		$query = DB::query("SELECT t.sortid AS sortid, tt.optionid, tt.title, tt.type, tt.unit, tt.rules, tt.identifier, tt.description, tv.required, tv.unchangeable, tv.search, tv.subjectshow, tv.visitedshow, tv.orderbyshow, tt.expiration, tt.protect
			FROM ".DB::table('category_sort')." t
			LEFT JOIN ".DB::table('category_sortvar')." tv ON t.sortid=tv.sortid
			LEFT JOIN ".DB::table('category_sortoption')." tt ON tv.optionid=tt.optionid
			WHERE tv.available='1'
			ORDER BY tv.displayorder");
		while($data = DB::fetch($query)) {
			$data['rules'] = unserialize($data['rules']);
			$sortid = $data['sortid'];
			$optionid = $data['optionid'];
			$sortlist[$sortid][$optionid] = array(
				'title' => dhtmlspecialchars($data['title']),
				'type' => dhtmlspecialchars($data['type']),
				'unit' => dhtmlspecialchars($data['unit']),
				'identifier' => dhtmlspecialchars($data['identifier']),
				'description' => dhtmlspecialchars($data['description']),
				'required' => intval($data['required']),
				'unchangeable' => intval($data['unchangeable']),
				'search' => intval($data['search']),
				'subjectshow' => intval($data['subjectshow']),
				'visitedshow' => intval($data['visitedshow']),
				'orderbyshow' => intval($data['orderbyshow']),
				'expiration' => intval($data['expiration']),
				'protect' => unserialize($data['protect']),
				);

			if(in_array($data['type'], array('select', 'checkbox', 'radio', 'intermediary'))) {
				if($data['rules']['choices']) {
					$choices = array();
					foreach(explode("\n", $data['rules']['choices']) as $item) {
						list($index, $choice) = explode('=', $item);
						$choices[trim($index)] = trim($choice);
					}
					$sortlist[$sortid][$optionid]['choices'] = $choices;
				} else {
					$sortlist[$sortid][$optionid]['choices'] = array();
				}
				if($data['type'] == 'select') {
					$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : 108;
				}
			} elseif(in_array($data['type'], array('text', 'textarea', 'calendar'))) {
				$sortlist[$sortid][$optionid]['maxlength'] = intval($data['rules']['maxlength']);
				if($data['type'] == 'textarea') {
					$sortlist[$sortid][$optionid]['rowsize'] = $data['rules']['rowsize'] ? intval($data['rules']['rowsize']) : 20;
					$sortlist[$sortid][$optionid]['colsize'] = $data['rules']['colsize'] ? intval($data['rules']['colsize']) : 10;
				} else {
					$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
				}
				if(in_array($data['type'], array('text', 'textarea'))) {
					$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
				}
			} elseif($data['type'] == 'image') {
				$sortlist[$sortid][$optionid]['maxwidth'] = intval($data['rules']['maxwidth']);
				$sortlist[$sortid][$optionid]['maxheight'] = intval($data['rules']['maxheight']);
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			} elseif(in_array($data['type'], array('number', 'range'))) {
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
				$sortlist[$sortid][$optionid]['maxnum'] = intval($data['rules']['maxnum']);
				$sortlist[$sortid][$optionid]['minnum'] = intval($data['rules']['minnum']);
				if($data['rules']['searchtxt']) {
					$sortlist[$sortid][$optionid]['searchtxt'] = explode(',', $data['rules']['searchtxt']);
				}
				$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
			} elseif($data['type'] == 'phone') {
				$sortlist[$sortid][$optionid]['numbercheck'] = $data['rules']['numbercheck'] ? intval($data['rules']['numbercheck']) : 0;
				if($data['rules']['numberrange']) {
					foreach(explode("\n", $data['rules']['numberrange']) as $num) {
						$numchoices[] = $num;
					}
					$sortlist[$sortid][$optionid]['numberrange'] = $numchoices;
				}
			}
		}
		$query = DB::query("SELECT sortid, description, template, stemplate, sttemplate, ptemplate, btemplate, vtemplate, ntemplate, rtemplate, perpage FROM ".DB::table('category_sort')."");
		while($data = DB::fetch($query)) {
			$templatedata[$data['sortid']] = str_replace('"', '\"', $data['template']);
			$stemplatedata[$data['sortid']] = str_replace('"', '\"', $data['stemplate']);
			$sttemplatedata[$data['sortid']] = str_replace('"', '\"', $data['sttemplate']);
			$ptemplatedata[$data['sortid']] = str_replace('"', '\"', $data['ptemplate']);
			$btemplatedata[$data['sortid']] = str_replace('"', '\"', $data['btemplate']);
			$vtemplatedata[$data['sortid']] = str_replace('"', '\"', $data['vtemplate']);
			$ntemplatedata[$data['sortid']] = str_replace('"', '\"', $data['ntemplate']);
			$rtemplatedata[$data['sortid']] = str_replace('"', '\"', $data['rtemplate']);
			$perpage[$data['sortid']] = $data['perpage'];
		}

		$data['sortoption'] = $data['template'] = array();

		foreach($sortlist as $sortid => $option) {
			$template['viewthread'] =  $templatedata[$sortid];
			$template['subject'] = $stemplatedata[$sortid];
			$template['subjecttext'] = $sttemplatedata[$sortid];
			$template['post'] = $ptemplatedata[$sortid];
			$template['visit'] = $vtemplatedata[$sortid];
			$template['neighborhood'] = $ntemplatedata[$sortid];
			$template['recommend'] = $rtemplatedata[$sortid];
			$template['perpage'] = $perpage[$sortid];
			$blocktemplate = unserialize(stripslashes($btemplatedata[$sortid]));
			$templateblock = array();
			if($blocktemplate) {
				foreach($blocktemplate as $stylename => $style) {
					if(preg_match('/^(.*?)(\[loop)/is', $style, $match)) {
						$templateblock[$stylename]['header'] = trim($match[1]);
					}
					if(strrpos($style, '[/loop]')) {
						$templateblock[$stylename]['footer'] = substr($style, strrpos($style, '[/loop]') + 8);
					}
					$match = array();
					if(preg_match('/\[loop\](.*?)\[\/loop]/is', $style, $match)) {
						$templateblock[$stylename]['loop'] = trim($match[1]);
					} else {
						$templateblock[$stylename]['loop'] = $style;
					}
				}
			}

			save_syscache('category_option_'.$sortid, $option);
			save_syscache('category_template_'.$sortid, $template);
			save_syscache('category_template_block_'.$sortid, $templateblock);
		}
	} elseif($cachename == 'sortlist') {
		$query = DB::query("SELECT sortid, cid, name, expiration, imgnum, threads FROM ".DB::table('category_sort')." ORDER BY displayorder");
		while($data = DB::fetch($query)) {
			$sortdatalist[$data['cid']][$data['sortid']] = array('name' => $data['name'], 'expiration' => $data['expiration'], 'imgnum' => $data['imgnum'], 'cid' => $data['cid'], 'threads' => $data['threads']);
		}

		$query = DB::query("SELECT cid, identifier FROM ".DB::table('category_channel')." ORDER BY displayorder");
		while($data = DB::fetch($query)) {
			save_syscache('category_sortlist_'.$data['identifier'], $sortdatalist[$data['cid']]);
		}
	} elseif($cachename == 'arealist') {
		$query = DB::query("SELECT aid, aup, cid, type, title FROM ".DB::table('category_area')." ORDER BY displayorder");
		while($data = DB::fetch($query)) {
			if($data['type'] == 'city') {
				$areadatalist[$data['cid']][$data['type']][$data['aid']] = $data['title'];
			} else {
				$areadatalist[$data['cid']][$data['type']][$data['aup']][$data['aid']] = $data['title'];
			}
		}
		$query = DB::query("SELECT cid, identifier FROM ".DB::table('category_channel')." ORDER BY displayorder");
		while($data = DB::fetch($query)) {
			save_syscache('category_arealist_'.$data['identifier'], $areadatalist[$data['cid']]);
		}
	} elseif($cachename == 'channellist') {
		$query = DB::query("SELECT cid, title, identifier, logo, status, managegid, listmode, mapinfo, imageinfo, seoinfo FROM ".DB::table('category_channel')." ORDER BY displayorder");
		while($data = DB::fetch($query)) {
			$mapinfo = unserialize($data['mapinfo']);
			$seoinfo = unserialize($data['seoinfo']);
			$channeldatalist[$data['identifier']] = array('title' => $data['title'], 'cid' => $data['cid'], 'logo' => get_logoimg($data['logo']), 'status' => $data['status'], 'listmode' => $data['listmode'], 'mapkey' => $mapinfo['key'], 'managegid' => unserialize($data['managegid']), 'imageinfo' => unserialize($data['imageinfo']), 'seoinfo' => $seoinfo);
		}
		save_syscache('category_channellist', $channeldatalist);
	} elseif($cachename == 'usergroup') {
		$query = DB::query("SELECT gid, title, type, icon, allowpost, postdayper, allowpush, pushdayper, allowrecommend, recommenddayper, allowhighlight, highlightdayper FROM ".DB::table('category_'.$identifier.'_usergroup')." ORDER BY gid");
		while($data = DB::fetch($query)) {
			$usergrouplist[$data['gid']] = $data;
			save_syscache('category_group_'.$identifier.'_'.$data['gid'], $data);
		}
		save_syscache('category_usergrouplist_'.$identifier, $usergrouplist);
	}
}

function checkphonenum($num, $mode = 'post') {
	if($mode == 'post') {
		if(DB::result_first("SELECT count FROM ".DB::table('category_phonecount')." WHERE number='$num'")) {
			DB::query("UPDATE ".DB::table('category_phonecount')." SET count=count+1 WHERE number='$num'");
		} else {
			DB::query("INSERT INTO ".DB::table('category_phonecount')." (number, count) VALUES ('$num', 1)");
		}
	} else {
		$count = DB::result_first("SELECT count FROM ".DB::table('category_phonecount')." WHERE number='$num'");
		return $count;
	}
}

function get_logoimg($imgname) {
	global $_G;
	if($imgname) {
		$parse = parse_url($imgname);
		if(isset($parse['host'])) {
			$imgpath = $imgname;
		} else {
			$imgpath = $_G['setting']['attachurl'].'common/'.$imgname;
		}
		return $imgpath;
	}
}

function getcateimg($aid, $nocache = 0, $w = 140, $h = 140, $type = '', $modidentifier = '') {
	global $_G;
	$key = authcode("$aid\t$w\t$h", 'ENCODE', $_G['config']['security']['authkey']);
	return 'category.php?mod=misc&action=thumb&aid='.$aid.'&size='.$w.'x'.$h.'&identifier='.$modidentifier.'&key='.rawurlencode($key).($nocache ? '&nocache=yes' : '').($type ? '&type='.$type : '');
}

function gettypetemplate($option, $optionvalue, $groupid) {
	global $_G;

	if(empty($optionvalue['unchangeable'])) $optionvalue['unchangeable'] = '';
	if(in_array($option['type'], array('number', 'text', 'email', 'calendar', 'image', 'url', 'range', 'upload', 'range', 'phone'))) {
		if($option['type'] == 'calendar') {
			$showoption[$option['identifier']]['value'] = '<script type="text/javascript" src="'.$_G['setting']['jspath'].'forum_calendar.js?'.VERHASH.'"></script><input type="text" name="typeoption['.$option['identifier'].']" tabindex="1" id="typeoption_'.$option['identifier'].'" style="width:'.$option['inputsize'].'px;" onBlur="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" value="'.$optionvalue['value'].'" onclick="showcalendar(event, this, false)" '.$optionvalue['unchangeable'].' class="px"/>';
		} else {
			$showoption[$option['identifier']]['value'] = '<input type="text" name="typeoption['.$option['identifier'].']" tabindex="1" id="typeoption_'.$option['identifier'].'" style="width:'.$option['inputsize'].'px;" onBlur="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\', \''.intval($option['maxnum']).'\', \''.intval($option['minnum']).'\', \''.intval($option['maxlength']).'\')" value="'.$optionvalue['value'].'" '.$optionvalue['unchangeable'].' class="px"/>';
		}
	} elseif(in_array($option['type'], array('radio', 'checkbox', 'select'))) {
		if($option['type'] == 'select') {
			$showoption[$option['identifier']]['value'] = '<span class="ftid"><select name="typeoption['.$option['identifier'].']" id="typeoption_'.$option['identifier'].'" tabindex="1" '.$optionvalue['unchangeable'].' class="ps">';
			foreach($option['choices'] as $id => $value) {
				$showoption[$option['identifier']]['value'] .= '<option value="'.$id.'" '.$optionvalue['value'][$id].'>'.$value.'</option>';
			}
			$showoption[$option['identifier']]['value'] .= '</select></span>';
		} elseif($option['type'] == 'radio') {
			foreach($option['choices'] as $id => $value) {
				$showoption[$option['identifier']]['value'] .= '<span class="fb"><input type="radio" class="pr" name="typeoption['.$option['identifier'].']" tabindex="1" id="typeoption_'.$option['identifier'].'" onclick="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" value="'.$id.'" '.$optionvalue['value'][$id].' '.$optionvalue['unchangeable'].' class="pr">'.$value.'</span>';
			}
		} elseif($option['type'] == 'checkbox') {
			foreach($option['choices'] as $id => $value) {
				$showoption[$option['identifier']]['value'] .= '<span class="fb"><input type="checkbox" class="pc" name="typeoption['.$option['identifier'].'][]" tabindex="1" id="typeoption_'.$option['identifier'].'" onclick="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" value="'.$id.'" '.$optionvalue['value'][$id][$id].' '.$optionvalue['unchangeable'].' class="pc"> '.$value.'</span>';
			}
		}
	} elseif(in_array($option['type'], array('textarea'))) {
		$showoption[$option['identifier']]['value'] = '<span><textarea name="typeoption['.$option['identifier'].']" tabindex="1" id="typeoption_'.$option['identifier'].'" rows="$option[rowsize]" cols="'.$option['colsize'].'" onBlur="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\', 0, 0{if $option[maxlength]}, \'$option[maxlength]\'{/if})" '.$optionvalue['unchangeable'].' class="pt">'.$optionvalue['value'].'</textarea><span>';
	} elseif($option['type'] == 'intermediary') {
		$showoption[$option['identifier']]['value'] = '<span class="ftid"><select name="typeoption['.$option['identifier'].']" id="typeoption_'.$option['identifier'].'" tabindex="1" '.$optionvalue['unchangeable'].' class="ps">';
		if($groupid == 1) {
			foreach($option['choices'] as $id => $value) {
				$showoption[$option['identifier']]['value'] .= '<option value="'.$id.'" '.$optionvalue['value'][$id].'>'.$value.'</option>';
			}
		} else {
			$showoption[$option['identifier']]['value'] .= '<option value="'.$groupid.'" '.$optionvalue['value'][$id].'>'.$_G['category_usergrouplist'][$groupid]['title'].'</option>';
		}
		$showoption[$option['identifier']]['value'] .= '</select></span>';
	}

	return $showoption;

}

function quicksearch($sortoptionarray) {
	global $_G;

	$quicksearch = array();
	if($sortoptionarray) {
		foreach($sortoptionarray as $optionid => $option) {
			if($option['search']) {
				$quicksearch[$optionid]['title'] = $option['title'];
				$quicksearch[$optionid]['identifier'] = $option['identifier'];
				$quicksearch[$optionid]['unit'] = $option['unit'];
				$quicksearch[$optionid]['type'] = $option['type'];
				if(in_array($option['type'], array('radio', 'select'))) {
					$quicksearch[$optionid]['choices'] = $option['choices'];
				} elseif(!empty($option['searchtxt'])) {
					$choices = array();
					$prevs = 'd';
					foreach($option['searchtxt'] as $choice) {
						$value = "$prevs|$choice";
						if($choice) {
							$quicksearch[$optionid]['choices'][$value] = $prevs == 'd' ? $choice.$option['unit'].lang('category/template', 'house_less') : $prevs.'-'.$choice.$option['unit'];
							$prevs = $choice;
						}
						$max = $choice;
					}
					$value = "u|$choice";
					$quicksearch[$optionid]['choices'][$value] .= $max.$option['unit'].lang('category/template', 'house_above');
				}
			}
		}
	}

	return $quicksearch;
}

function recommendsort($sortid, $sortoptionarray, $groupid, $template, $district, $modurl) {
	global $_G;
	$optionlist = $data = $datalist = $searchvalue = $searchunit = $stemplate = $imagelist = $districtlist = $_G['optionvaluelist'] = array();
	$valuefield = '';
	foreach($sortoptionarray as $optionid => $option) {
		if($option['visitedshow']) {
			$valuefield .= ','.$option['identifier'];
			$optionlist[$option['identifier']]['unit'] = $option['unit'];
			$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
			$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
			$optionlist['attachid'] = $optionlist['district'] = '';
		}
	}

	$query = DB::query("SELECT tid, attachid, district $valuefield FROM ".DB::table('category_sortvalue')."$sortid WHERE groupid='$groupid' AND recommend='1' ORDER BY dateline DESC LIMIT 4");
	while($thread = DB::fetch($query)) {
		foreach($optionlist as $identifier => $option) {
			$_G['optionvaluelist'][$thread['tid']][$identifier]['unit'] = $option['unit'];
			$_G['optionvaluelist'][$thread['tid']][$identifier]['value'] = $thread[$identifier];
			if($identifier == 'attachid') {
				$imagelist[$thread['tid']] = $thread['attachid'] ? '<img src="'.getcateimg($thread['attachid'], 0, 120, 120).'">' : '<img src="static/image/common/nophotosmall.gif">';
			} elseif($identifier == 'district') {
				$districtlist[$thread['tid']] = $district[$thread['district']];
			} else {
				$data[$thread['tid']] = $thread['tid'];
			}
		}
	}

	foreach($data as $tid => $option) {
		$datalist[$tid] = preg_replace(array("/\{district\}/i", "/\{image\}/i", "/\[url\](.+?)\[\/url\]/i"),
						array($districtlist[$tid], $imagelist[$tid], "<a href=\"$modurl?mod=view&tid=$tid\">\\1</a>"
						), stripslashes($template));
		$datalist[$tid] = preg_replace($searchvalue, "showlistoption('\\1', 'value', '$tid')", $datalist[$tid]);
		$datalist[$tid] = preg_replace($searchunit, "showlistoption('\\1', 'unit', '$tid')", $datalist[$tid]);
	}

	return $datalist;
}

function sortsearch($sortid, $sortoptionarray, $searchoption = array(), $selecturladd = array(), $sortcondition = '', $limit, $tpp) {

	$sortid = intval($sortid);
	$limit = intval($limit);
	$tpp = intval($tpp);

	$and = $selectsql = '';
	$optionide = $sortdata = array();
	$colorarray = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');

	if($selecturladd) {
		foreach($sortoptionarray as $optionid => $option) {
			if(in_array($option['type'], array('radio', 'select', 'range'))) {
				$optionide[$option['identifier']] = $option['type'];
			}
		}

		$optionide['city'] = $optionide['district'] = $optionide['street'] = $optionide['recommend'] = $optionide['groupid'] = 'num';
		$optionide['attachid'] = 'attachid';

		foreach($selecturladd as $fieldname => $value) {
			if($optionide[$fieldname] && $value != 'all') {
				if($optionide[$fieldname] == 'range') {
					$value = explode('|', $value);
					if($value[0] == 'd') {
						$selectsql .= $and."$fieldname<'$value[1]'";
					} elseif($value[0] == 'u') {
						$selectsql .= $and."$fieldname>'$value[1]'";
					} else {
						$selectsql .= $and."($fieldname BETWEEN ".intval($value[0])." AND ".intval($value[1]).")";
					}
				} elseif($optionide[$fieldname] == 'attachid') {
					$selectsql .= $and."attachnum>'$value'";
				} else {
					$selectsql .= $and."$fieldname='$value'";
				}
				$and = ' AND ';
			}
		}
	}

	if(!empty($searchoption) && is_array($searchoption)) {
		foreach($searchoption as $optionid => $option) {
			$fieldname = $sortoptionarray[$optionid]['identifier'] ? $sortoptionarray[$optionid]['identifier'] : 1;
			if($option['value']) {
				if(in_array($option['type'], array('number', 'radio', 'select'))) {
					$option['value'] = intval($option['value']);
					$exp = '=';
					if($option['condition']) {
						$exp = $option['condition'] == 1 ? '>' : '<';
					}
					$sql = "$fieldname$exp'$option[value]'";
				} elseif($option['type'] == 'checkbox') {
					$sql = "$fieldname LIKE '%".(implode("%", $option['value']))."%'";
				} elseif($option['type'] == 'range') {
					$value = explode('|', $option['value']);
					if($value[0] == 'd') {
						$sql = "$fieldname<'$value[1]'";
					} elseif($value[0] == 'u') {
						$sql = "$fieldname>'$value[1]'";
					} else {
						$sql = $value[0] || $value[1] ? "$fieldname BETWEEN ".intval($value[0])." AND ".intval($value[1])."" : '';
					}
				} else {
					$sql = "$fieldname LIKE '%$option[value]%'";
				}
				$selectsql .= $and."$sql ";
				$and = 'AND ';
			}
		}
	}

	$sortdata['count'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_sortvalue')."$sortid ".($selectsql ? 'WHERE '.$selectsql : '')."");

	$query = DB::query("SELECT tid, attachid, dateline, expiration, displayorder, recommend, attachnum, highlight, groupid, city, district, street FROM ".DB::table('category_sortvalue')."$sortid ".($selectsql ? 'WHERE '.$selectsql : '')." ORDER BY displayorder DESC, $sortcondition[orderby] $sortcondition[ascdesc] LIMIT $limit, $tpp");
	while($thread = DB::fetch($query)) {
		if($thread['highlight']) {
			$string = sprintf('%02d', $thread['highlight']);
			$stylestr = sprintf('%03b', $string[0]);

			$thread['highlight'] = ' style="';
			$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$thread['highlight'] .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
			$thread['highlight'] .= '"';
		} else {
			$thread['highlight'] = '';
		}
		$sortdata['tids'][]= $thread['tid'];
		$sortdata['datalist'][$thread['tid']]= $thread;
	}

	return $sortdata;

}

function showsorttemplate($sortid, $sortoptionarray, $templatearray, $threadlist, $threadids = array(), $arealist = array(), $modurl) {
	global $_G;

	$searchtitle = $searchvalue = $searchunit = $stemplate = $searchtids = $sortlistarray = $skipaids = $sortdata = $_G['optionvaluelist'] = array();

	$addthreadid = !empty($threadids) ? "AND tid IN (".dimplode($threadids).")" : '';
	$query = DB::query("SELECT sortid, tid, optionid, value, expiration FROM ".DB::table('category_sortoptionvar')." WHERE sortid='$sortid' $addthreadid");
	while($sortthread = DB::fetch($query)) {
		$optionid = $sortthread['optionid'];
		$tid = $sortthread['tid'];
		$arrayoption = $sortoptionarray[$optionid];
		if($sortoptionarray[$optionid]['subjectshow']) {
			$_G['optionvaluelist'][$tid][$arrayoption['identifier']]['title'] = $arrayoption['title'];
			$_G['optionvaluelist'][$tid][$arrayoption['identifier']]['unit'] = $arrayoption['unit'];
			if(in_array($arrayoption['type'], array('radio', 'checkbox', 'select'))) {
				if($arrayoption['type'] == 'checkbox') {
					foreach(explode("\t", $sortthread['value']) as $choiceid) {
						$sortthreadlist[$tid][$arrayoption['title']] .= $arrayoption['choices'][$choiceid].'&nbsp;';
						$_G['optionvaluelist'][$tid][$arrayoption['identifier']]['value'] .= $arrayoption['choices'][$choiceid].'&nbsp;';
					}
				} else {
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']];
				}
			} else {
				if($sortthread['value']) {
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'];
				} else {
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$tid][$arrayoption['identifier']]['value'] = $arrayoption['defaultvalue'];
					$_G['optionvaluelist'][$tid][$arrayoption['identifier']]['unit'] = '';
				}
			}
			$sortthreadlist[$tid]['sortid'] = $sortid;
		}
	}

	if($templatearray && $sortthreadlist) {
		foreach($threadlist as $thread) {
			$sortdata[$thread['tid']]['recommend'] = $thread['recommend'] ? '<span class="rec">'.lang('category/template', 'house_stick').'</span>' : '';
			$sortdata[$thread['tid']]['displayorder'] = $thread['displayorder'] ? '<span class="pin">'.lang('category/template', 'totop').'</span>' : '';
			$sortdata[$thread['tid']]['attach'] = $thread['attachnum'] > 1 ? '<span class="pic">'.lang('category/template', 'house_imgs').'</span>' : '';
			$sortdata[$thread['tid']]['subjecturl'] = '<a href="'.$modurl.'?mod=view&tid='.$thread['tid'].'" '.$thread['highlight'].'>'.$thread['subject'].'</a>';
			$sortdata[$thread['tid']]['subject'] = $thread['subject'];
			$sortdata[$thread['tid']]['author'] = '<a href="'.$modurl.'?mod=my&uid='.$thread['authorid'].'&sortid='.$sortid.'" target="_blank">'.$thread['author'].'</a>';
			$sortdata[$thread['tid']]['image'] = $thread['attachid'] ? '<img src="'.getcateimg($thread['attachid']).'">' : '<img src="static/image/common/nophotosmall.gif">';
			$sortdata[$thread['tid']]['dateline'] = $thread['dateline'] ? dgmdate($thread['dateline'], 'u') : '';
			$sortdata[$thread['tid']]['city'] = $thread['city'] ? $arealist['city'][$thread['city']] : '';
			$sortdata[$thread['tid']]['district'] = $thread['district'] ? $arealist['district'][$thread['city']][$thread['district']] : '';
			$sortdata[$thread['tid']]['street'] = $thread['street'] ? $arealist['street'][$thread['district']][$thread['street']] : '';
			$sortdata[$thread['tid']]['expiration'] = $thread['expiration'] && $thread['expiration'] < TIMESTAMP ? '<span class="over">'.lang('category/template', 'house_overdue').'</span>' : '';
		}

		foreach($sortoptionarray as $sortid => $option) {
			if($option['subjectshow']) {
				$searchtitle[] = '/{('.$option['identifier'].')}/e';
				$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
				$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
			}
		}

		foreach($sortthreadlist as $tid => $option) {
			$stemplate[$tid] = preg_replace(array("/\{city\}/i", "/\{district\}/i", "/\{street\}/i", "/\{image\}/i", "/\{attach\}/i", "/\{recommend\}/i", "/\{displayorder\}/i", "/\{dateline\}/i", "/\{author\}/i", "/\{subjecturl\}/i", "/\{subject\}/i", "/\{expiration\}/i", "/\[url\](.+?)\[\/url\]/i"),
							array(
								$sortdata[$tid]['city'],
								$sortdata[$tid]['district'],
								$sortdata[$tid]['street'],
								$sortdata[$tid]['image'],
								$sortdata[$tid]['attach'],
								$sortdata[$tid]['recommend'],
								$sortdata[$tid]['displayorder'],
								$sortdata[$tid]['dateline'],
								$sortdata[$tid]['author'],
								$sortdata[$tid]['subjecturl'],
								$sortdata[$tid]['subject'],
								$sortdata[$tid]['expiration'],
								"<a href=\"$modurl?mod=view&tid=$tid\">\\1</a>"
							), stripslashes($templatearray));
			$stemplate[$tid] = preg_replace($searchtitle, "showlistoption('\\1', 'title', '$tid')", $stemplate[$tid]);
			$stemplate[$tid] = preg_replace($searchvalue, "showlistoption('\\1', 'value', '$tid')", $stemplate[$tid]);
			$stemplate[$tid] = preg_replace($searchunit, "showlistoption('\\1', 'unit', '$tid')", $stemplate[$tid]);
		}
	}

	$sortlistarray['template'] = $stemplate;

	return $sortlistarray;
}

function showlistoption($var, $type, $tid) {
	global $_G;
	if($_G['optionvaluelist'][$tid][$var][$type]) {
		return $_G['optionvaluelist'][$tid][$var][$type];
	} else {
		return '';
	}
}

function showvisitlistoption($var, $type, $tid) {
	global $_G;
	if($_G['optionvisitlist'][$tid][$var][$type]) {
		return $_G['optionvisitlist'][$tid][$var][$type];
	} else {
		return '';
	}
}

function neighborhood($tid, $sortid, $cityid, $districtid, $streetid, $sortoptionarray, $template, $modurl) {
	global $_G;

	$optionlist = $data = $datalist = $searchvalue = $searchunit = $stemplate = $imagelist = $_G['optionvaluelist'] = array();
	$valuefield = '';
	foreach($sortoptionarray as $optionid => $option) {
		if($option['visitedshow']) {
			$valuefield .= ','.$option['identifier'];
			$optionlist[$option['identifier']]['unit'] = $option['unit'];
			$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
			$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
			$optionlist['attachid'] = '';
		}
	}

	$query = DB::query("SELECT tid, attachid $valuefield FROM ".DB::table('category_sortvalue')."$sortid WHERE city='$cityid' AND district='$districtid' AND street='$streetid' AND tid!='$tid' ORDER BY dateline DESC LIMIT 5");
	while($thread = DB::fetch($query)) {
		foreach($optionlist as $identifier => $option) {
			$_G['optionvaluelist'][$thread['tid']][$identifier]['unit'] = $option['unit'];
			$_G['optionvaluelist'][$thread['tid']][$identifier]['value'] = $thread[$identifier];
			if($identifier == 'attachid') {
				$imagelist[$thread['tid']] = $thread['attachid'] ? '<img src="'.getcateimg($thread['attachid'], 0, 48, 48).'">' : '<img src="static/image/common/nophotosmall.gif">';
			} else {
				$data[$thread['tid']] = $thread['tid'];
			}
		}
	}

	foreach($data as $tid => $option) {
		$datalist[$tid] = preg_replace(array("/\{image\}/i", "/\[url\](.+?)\[\/url\]/i"),
						array($imagelist[$tid], "<a href=\"$modurl?mod=view&tid=$tid\">\\1</a>"
						), stripslashes($template));
		$datalist[$tid] = preg_replace($searchvalue, "showlistoption('\\1', 'value', '$tid')", $datalist[$tid]);
		$datalist[$tid] = preg_replace($searchunit, "showlistoption('\\1', 'unit', '$tid')", $datalist[$tid]);
	}

	return $datalist;
}

function threadsortshow($tid, $sortoptionarray, $templatearray, $authorid, $groupid) {
	global $_G;

	$optiondata = $searchtitle = $searchvalue = $searchunit = $memberinfofield = $_G['category_option'] = array();
	$intermediary = '';

	if($sortoptionarray) {
		$query = DB::query("SELECT optionid, value, expiration FROM ".DB::table('category_sortoptionvar')." WHERE tid='$tid'");
		while($option = DB::fetch($query)) {
			$optiondata[$option['optionid']]['value'] = $option['value'];
			$optiondata[$option['optionid']]['expiration'] = $option['expiration'] && $option['expiration'] <= TIMESTAMP ? 1 : 0;
			$sortdataexpiration = $option['expiration'];
		}

		foreach($sortoptionarray as $optionid => $option) {
			$_G['category_option'][$option['identifier']]['title'] = $option['title'];
			$_G['category_option'][$option['identifier']]['unit'] = $option['unit'];
			$_G['category_option'][$option['identifier']]['type'] = $option['type'];

			if(($option['expiration'] && !$optiondata[$optionid]['expiration']) || empty($option['expiration'])) {
				if(($option['protect']['usergroup'] && strstr("\t".$option['protect']['usergroup']."\t", "\t$_G[groupid]\t")) || empty($option['protect']['usergroup']) || $authorid == $_G['uid']) {
					if($option['type'] == 'checkbox') {
						$_G['category_option'][$option['identifier']]['value'] = '';
						foreach(explode("\t", $optiondata[$optionid]['value']) as $choiceid) {
							$_G['category_option'][$option['identifier']]['value'] .= $option['choices'][$choiceid].'&nbsp;';
						}
					} elseif(in_array($option['type'], array('radio', 'select', 'intermediary'))) {
						if($option['type'] == 'intermediary' && $groupid != 1) {
							$_G['category_option'][$option['identifier']]['value'] = $_G['category_usergrouplist'][$groupid]['title'];
						} else {
							$_G['category_option'][$option['identifier']]['value'] = $option['choices'][$optiondata[$optionid]['value']];
						}
					} elseif($option['type'] == 'url') {
						$_G['category_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? "<a href=\"".$optiondata[$optionid]['value']."\" target=\"_blank\">".$optiondata[$optionid]."</a>" : '';
					} elseif($option['type'] == 'textarea') {
						$_G['category_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? nl2br($optiondata[$optionid]['value']) : '';
					} elseif($option['type'] == 'phone') {
						if($option['numbercheck'] && $groupid == 1 && $optiondata[$optionid]['value']) {
							$intermediary = checkphonenum($optiondata[$optionid]['value'], 'check') >= 5 ? '<div class="intermediary">'.lang('category/template', 'house_friend_tips').'</div>' : '';
						}
						$_G['category_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? $optiondata[$optionid]['value'] : $option['defaultvalue'];
					} else {
						$_G['category_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'];
					}

					if($option['protect']['status'] && $optiondata[$optionid]['value'] && $_G['uid'] != $authorid) {
						if($option['protect']['mode'] == 1) {
							$_G['category_option'][$option['identifier']]['value'] = '<image src="category.php?mod=misc&action=protectsort&sortvalue='.$optiondata[$optionid]['value'].'">';
						} elseif($option['protect']['mode'] == 2) {
							$_G['category_option'][$option['identifier']]['value'] = '<span id="sortmessage_'.$option['identifier'].'"><a href="###" onclick="ajaxget(\'category.php?mod=misc&action=protectsort&tid='.$tid.'&optionid='.$optionid.'\', \'sortmessage_'.$option['identifier'].'\')">'.lang('category/template', 'house_click').'</a></span>';
						} elseif($option['protect']['mode'] == 4) {
							$exist = DB::result_first('SELECT tid FROM '.DB::table('category_payoption')." WHERE uid='$_G[uid]' AND tid='$tid' AND optionid='$optionid'");
							if(empty($exist)) {
								$creditsid = $option['protect']['credits']['title'];
								$creditsname = $_G['setting']['extcredits'][$creditsid]['title'];
								$price = $option['protect']['credits']['price'];
								$_G['category_option'][$option['identifier']]['value'] = '<a href="###" onclick="showWindow(\'buyoption\', \'category.php?mod=misc&action=buyoption&optionid='.$optionid.'&tid='.$tid.'&handlekey=forumthread\');">
								'.lang('category/template', 'house_buy_view').$price.$creditsname.lang('category/template', 'house_buy_view').'</a>';
							} else {
								$_G['category_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'];
							}
						}
					}

					if(empty($_G['category_option'][$option['identifier']]['value'])) {
						$_G['category_option'][$option['identifier']]['value'] = $option['defaultvalue'];
						$_G['category_option'][$option['identifier']]['unit'] = '';
					}
				} else {
					$_G['category_option'][$option['identifier']]['value'] = lang('category/template', 'house_nopur_view');
				}


			} else {
				$_G['category_option'][$option['identifier']]['value'] = lang('category/template', 'house_view_expired');
			}
		}

		$typetemplate = '';
		if($templatearray) {
			foreach($sortoptionarray as $option) {
				$searchtitle[] = '/{('.$option['identifier'].')}/e';
				$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
				$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
			}

			$threadexpiration = $sortdataexpiration ? dgmdate($sortdataexpiration) : lang('category/template', 'house_perpetual');
			$typetemplate = preg_replace(array("/\{expiration\}/i", "/\{intermediary\}/i"), array($threadexpiration, $intermediary), stripslashes($templatearray));
			$typetemplate = preg_replace($searchtitle, "showcateoption('\\1', 'title')", $typetemplate);
			$typetemplate = preg_replace($searchvalue, "showcateoption('\\1', 'value')", $typetemplate);
			$typetemplate = preg_replace($searchunit, "showcateoption('\\1', 'unit')", $typetemplate);
		}
	}

	$threadsortshow['optionlist'] = $_G['category_option'];
	$threadsortshow['typetemplate'] = $typetemplate;
	$threadsortshow['expiration'] = dgmdate($sortdataexpiration, 'd');

	return $threadsortshow;
}

function showcateoption($var, $type) {
	global $_G;
	if($_G['category_option'][$var][$type]) {
		return $_G['category_option'][$var][$type];
	} else {
		return '';
	}
}

function threadsort_checkoption($sortid = 0, $unchangeable = 1) {
	global $_G;

	$_G['category_checkoption'] = array();
	foreach($_G['category_optionlist'] as $optionid => $option) {
		$_G['category_checkoption'][$option['identifier']]['optionid'] = $optionid;
		$_G['category_checkoption'][$option['identifier']]['type'] = $option['type'];
		$_G['category_checkoption'][$option['identifier']]['required'] = $option['required'] ? 1 : 0;
		$_G['category_checkoption'][$option['identifier']]['title'] = $option['title'];
		$_G['category_checkoption'][$option['identifier']]['unchangeable'] = $_G['gp_action'] == 'edit' && $unchangeable && $option['unchangeable'] ? 1 : 0;
		$checklist = array('maxnum', 'minnum', 'maxlength', 'numbercheck', 'numberrange');
		foreach($checklist as $op) {
			if($option[$op]) {
				$_G['category_checkoption'][$option['identifier']][$op] = $op != 'numberrange' ? intval($option[$op]) : $option[$op];
			}
		}
	}
}

function threadsort_optiondata($sortid, $sortoptionarray, $templatearray, $tid = 0, $housegroupid) {
	global $_G;
	$_G['category_optiondata'] = $_G['category_sorttemplate'] = $_G['category_option'] = $searchcontent = array();

	if($tid) {
		$query = DB::query("SELECT optionid, value FROM ".DB::table('category_sortoptionvar')." WHERE tid='$tid'");
		while($option = DB::fetch($query)) {
			$_G['category_optiondata'][$option['optionid']] = $option['value'];
		}
	}

	foreach($sortoptionarray as $optionid => $option) {
		if($tid) {
			$_G['category_optionlist'][$optionid]['unchangeable'] = $sortoptionarray[$optionid]['unchangeable'] ? 'readonly' : '';
			if($sortoptionarray[$optionid]['type'] == 'radio') {
				$_G['category_optionlist'][$optionid]['value'] = array($_G['category_optiondata'][$optionid] => 'checked="checked"');
			} elseif($sortoptionarray[$optionid]['type'] == 'select') {
				$_G['category_optionlist'][$optionid]['value'] = array($_G['category_optiondata'][$optionid] => 'selected="selected"');
			} elseif($sortoptionarray[$optionid]['type'] == 'checkbox') {
				foreach(explode("\t", $_G['category_optiondata'][$optionid]) as $value) {
					$_G['category_optionlist'][$optionid]['value'][$value] = array($value => 'checked="checked"');
				}
			} else {
				$_G['category_optionlist'][$optionid]['value'] = $_G['category_optiondata'][$optionid];
			}
			if(!isset($_G['category_optiondata'][$optionid])) {
				DB::query("INSERT INTO ".DB::table('category_sortoptionvar')." (sortid, tid, optionid)
				VALUES ('$sortid', '$tid', '$optionid')");
			}
		}

		if($templatearray['post']) {
			$_G['category_option'][$option['identifier']]['title'] = $option['title'];
			$_G['category_option'][$option['identifier']]['unit'] = $option['unit'];
			$_G['category_option'][$option['identifier']]['description'] = $option['description'];
			$_G['category_option'][$option['identifier']]['required'] = $option['required'] ? '*' : '';
			$_G['category_option'][$option['identifier']]['tips'] = '<span id="check'.$option['identifier'].'"></span>';

			$showoption = gettypetemplate($option, $_G['category_optionlist'][$optionid], $housegroupid);
			$_G['category_option'][$option['identifier']]['value'] = $showoption[$option['identifier']]['value'];

			$searchcontent['title'][] = '/{('.$option['identifier'].')}/e';
			$searchcontent['value'][] = '/\[('.$option['identifier'].')value\]/e';
			$searchcontent['unit'][] = '/\[('.$option['identifier'].')unit\]/e';
			$searchcontent['description'][] = '/\[('.$option['identifier'].')description\]/e';
			$searchcontent['required'][] = '/\[('.$option['identifier'].')required\]/e';
			$searchcontent['tips'][] = '/\[('.$option['identifier'].')tips\]/e';
		}
	}

	if($templatearray['post']) {
		$typetemplate = $templatearray['post'];
		foreach($searchcontent as $key => $content) {
			$typetemplate = preg_replace($searchcontent[$key], "showcateoption('\\1', '$key')", stripslashes($typetemplate));
		}

		$_G['category_sorttemplate'] = $typetemplate;
	}
}

function threadsort_validator($sortoption) {
	global $_G;
	$_G['category_optiondata'] = array();
	foreach($_G['category_checkoption'] as $var => $option) {
		$typetitle = $_G['category_checkoption'][$var]['title'];
		if($_G['category_checkoption'][$var]['required'] && !$sortoption[$var]) {
			showmessage('threadtype_required_invalid', '', array('typetitle' => $typetitle));
		} elseif($sortoption[$var] && ($_G['category_checkoption'][$var]['type'] == 'number' && !is_numeric($sortoption[$var]) || $_G['forum_checkoption'][$var]['type'] == 'email' && !isemail($sortoption[$var]))){
			showmessage('threadtype_format_invalid', '', array('typetitle' => $typetitle));
		} elseif($sortoption[$var] && $_G['category_checkoption'][$var]['maxlength'] && strlen($typeoption[$var]) > $_G['forum_checkoption'][$var]['maxlength']) {
			showmessage('threadtype_toolong_invalid', '', array('typetitle' => $typetitle));
		} elseif($sortoption[$var] && (($_G['category_checkoption'][$var]['maxnum'] && $sortoption[$var] > $_G['category_checkoption'][$var]['maxnum']) || ($_G['forum_checkoption'][$var]['minnum'] && $sortoption[$var] < $_G['category_checkoption'][$var]['minnum']))) {
			showmessage('threadtype_num_invalid', '', array('typetitle' => $typetitle));
		} elseif($sortoption[$var] && $_G['category_checkoption'][$var]['unchangeable']) {
			showmessage('threadtype_unchangeable_invalid', '', array('typetitle' => $typetitle));
		}

		if($_G['category_checkoption'][$var]['numbercheck']) {
			checkphonenum($sortoption[$var]);
		}
		if($_G['category_checkoption'][$var]['type'] == 'checkbox') {
			$sortoption[$var] = $sortoption[$var] ? implode("\t", $sortoption[$var]) : '';
		} elseif($_G['category_checkoption'][$var]['type'] == 'url') {
			$sortoption[$var] = $sortoption[$var] ? (substr(strtolower($sortoption[$var]), 0, 4) == 'www.' ? 'http://'.$sortoption[$var] : $sortoption[$var]) : '';
		}

		$sortoption[$var] = dhtmlspecialchars(censor(trim($sortoption[$var])));
		$_G['category_optiondata'][$_G['category_checkoption'][$var]['optionid']] = $sortoption[$var];
	}

	return $_G['category_optiondata'];
}

function threadsort_insertfile($tid, &$files, $sortid, $edit = 0, $modidentifier, $channel) {
	global $_G;
	$allowtype = 'jpg, jpeg, gif, bmp, png';
	$newfiles = $aid = array();
	if(empty($tid)) return;
	if($files['categoryimg']) {
		foreach($files['categoryimg']['name'] as $key => $val) {
			$newfiles[$key]['name'] = $val;
			$newfiles[$key]['type'] = $files['categoryimg']['type'][$key];
			$newfiles[$key]['tmp_name'] = $files['categoryimg']['tmp_name'][$key];
			$newfiles[$key]['error'] = $files['categoryimg']['error'][$key];
			$newfiles[$key]['size'] = $files['categoryimg']['size'][$key];
		}
	} else {
		return;
	}
	require_once libfile('class/upload');
	$upload = new discuz_upload();
	$uploadtype = 'category';
	if($channel['imageinfo']['watermarkstatus']) {
		require_once libfile('class/house_image');
		$image = new image($channel);
	}

	foreach($newfiles as $key => $file) {
		if(!$upload->init($file, $uploadtype)) {
			continue;
		}
		if(!$upload->save()) {
			if(count($newfiles) == 1) {
				showmessage($upload->errormessage());
			}
		}
		$newattach[$key] = $upload->attach['attachment'];
		if($channel['imageinfo']['watermarkstatus']) {
			$image->Watermark($upload->attach['target']);
		}
		DB::query("INSERT INTO ".DB::table('category_'.$modidentifier.'_pic')." (tid, url, dateline) VALUES ('$tid', '".$upload->attach['attachment']."', '".TIMESTAMP."')");
		$aid[$key] = DB::insert_id();
	}

	$attachnum = $edit ? intval(DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$tid'")) : intval(count($aid));

	if(substr($_G['gp_coverpic'], 0, 4) == 'old_') {
		$newaid = substr($_G['gp_coverpic'], 4);
	} else {
		$_G['gp_coverpic'] = intval($_G['gp_coverpic']);
		if($aid[$_G['gp_coverpic']]) {
			$newaid = $aid[$_G['gp_coverpic']];
		} else {
			$aid = array_slice($aid, 0, 1);
			$newaid = $aid[0];
		}
	}

	if(!empty($newaid)) {
		DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET attachid='$newaid', attachnum='$attachnum' WHERE tid='$tid'");
	}
}

function visitedshow($tids, $sortoptionarray, $sortid, $template, $modurl) {
	global $_G;

	$optionlist = $data = $datalist = $searchvalue = $searchunit = $stemplate = $_G['optionvisitlist'] = array();
	$valuefield = '';

	foreach($sortoptionarray as $optionid => $option) {
		if($option['visitedshow']) {
			$valuefield .= ','.$option['identifier'];
			$optionlist[$option['identifier']]['unit'] = $option['unit'];
			$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
			$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
		}
	}

	if($tids && is_array($tids)) {
		$query = DB::query("SELECT tid $valuefield FROM ".DB::table('category_sortvalue')."$sortid  WHERE tid IN (".dimplode($tids).")");
		while($thread = DB::fetch($query)) {
			foreach($optionlist as $identifier => $option) {
				$_G['optionvisitlist'][$thread['tid']][$identifier]['unit'] = $option['unit'];
				$_G['optionvisitlist'][$thread['tid']][$identifier]['value'] = $thread[$identifier];
				$data[$thread['tid']] = $thread['tid'];
			}
		}

		foreach($data as $tid => $option) {
			$stemplate[$tid] = preg_replace(array("/\[url\](.+?)\[\/url\]/i"),
							array("<a href=\"$modurl?mod=view&tid=$tid\">\\1</a>"
							), stripslashes($template));
			$stemplate[$tid] = preg_replace($searchvalue, "showvisitlistoption('\\1', 'value', '$tid')", $stemplate[$tid]);
			$stemplate[$tid] = preg_replace($searchunit, "showvisitlistoption('\\1', 'unit', '$tid')", $stemplate[$tid]);
		}

		if(!empty($data)) {
			foreach(array_reverse($tids) as $tid) {
				if($data[$tid]) {
					$datalist[$tid] = $stemplate[$tid];
				}
			}
		}
	}

	return $datalist;
}

function visitedsetcookie($tid) {
	$tid = intval($tid);
	if($tid) {
		$threadvisited = getcookie('threadvisited');
		if(!strexists(",$threadvisited,", ",$tid,")) {
			$threadvisited = $threadvisited ? explode(',', $threadvisited) : array();
			$threadvisited[] = $tid;
			if(count($threadvisited) > 5) {
				array_shift($threadvisited);
			}
			dsetcookie('threadvisited', implode(',', $threadvisited), 864000);
		}
	}
}

function category_uc_avatar($uid, $size = '', $returnsrc = FALSE) {
	global $_G;
	return avatar($uid, $size, $returnsrc, FALSE, $_G['setting']['avatarmethod'], $_G['setting']['ucenterurl']);
}

?>