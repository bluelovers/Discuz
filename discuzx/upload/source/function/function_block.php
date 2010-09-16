<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_block.php 16455 2010-09-07 03:51:33Z zhangguosheng $
 */

function block_script($blockclass, $script) {
	global $_G;
	$arr = explode('_', $blockclass);
	$dirname = $arr[0];
	$var = "blockscript_{$dirname}_{$script}";
	$script = 'block_'.$script;
	if(!isset($_G[$var])) {
		if(@include libfile($script, 'class/block/'.$dirname)) {
			$_G[$var] = new $script();
		} else {
			$_G[$var] = null;
		}
	}
	return $_G[$var];
}


function block_get_batch($parameter) {
	global $_G;

	$bids = array();
	$in_bids = $parameter?explode(',', $parameter):array();
	foreach ($in_bids as $bid) {
		$bid = intval($bid);
		if($bid) $bids[$bid] = $bid;
	}
	$styleids = array();
	if($bids) {
		$items = $prelist = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_block_item')." WHERE bid IN (".dimplode($bids).")");
		while ($item = DB::fetch($query)) {
			if($item['itemtype'] == '1' && $item['enddate'] && $item['enddate'] < TIMESTAMP) {
				continue;
			} elseif($item['itemtype'] == '1' && (!$item['startdate'] || $item['startdate'] <= TIMESTAMP)) {
				if (!empty($items[$item['bid']][$item['displayorder']])) {
					$prelist[$item['bid']] = array();
				}
				$prelist[$item['bid']][$item['displayorder']] = $item;
			}
			$items[$item['bid']][$item['displayorder']] = $item;
		}
		$query = DB::query("SELECT * FROM ".DB::table('common_block')." WHERE bid IN (".dimplode($bids).")");
		while ($block = DB::fetch($query)) {
			if(!empty($block['styleid']) && $block['styleid'] > 0) {
				$styleids[] = intval($block['styleid']);
			}
			if(!empty($items[$block['bid']])) {
				ksort($items[$block['bid']]);
				$newitem = array();
				if(!empty($prelist[$block['bid']])) {
					$countpre = 0;
					foreach($items[$block['bid']] as $position => $item) {
						if(empty($prelist[$block['bid']][$position])) {
							if(isset($items[$block['bid']][$position+$countpre])) {
								$newitem[$position+$countpre] = $item;
							}
						} else {
							if ($item['itemtype']=='1') {
								if ($prelist[$block['bid']][$position]['startdate'] >= $item['startdate']) {
									$newitem[$position] = $prelist[$block['bid']][$position];
								} else {
									$newitem[$position] = $item;
								}
							} else {
								$newitem[$position] = $prelist[$block['bid']][$position];
								$countpre++;
								if(isset($items[$block['bid']][$position+$countpre])) {
									$newitem[$position+$countpre] = $item;
								}
							}
						}
					}
					ksort($newitem);
				}
				$block['itemlist'] = empty($newitem) ? $items[$block['bid']] : $newitem;
			}
			$_G['block'][$block['bid']] = $block;
		}
	}
	if($styleids) {
		$styleids = array_unique($styleids);
		$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid IN (".dimplode($styleids).")");
		while($value=DB::fetch($query)) {
			$value['template'] = !empty($value['template']) ? (array)(unserialize($value['template'])) : array();
			$value['fields'] = !empty($value['fields']) ? (array)(unserialize($value['fields'])) : array();
			$_G['blockstyle_'.$value['styleid']] = $value;
		}
	}
}


function block_display_batch($bid) {
	echo block_fetch_content($bid);
}

function block_fetch_content($bid, $isjscall=false, $forceupdate=false) {
	global $_G;
	static $allowmem = null;

	if($allowmem === null) {
		$allowmem = getglobal('setting/memory/diyblockoutput/enable') && memory('check');
	}

	$str = '';
	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	if(!$block) {
		return;
	}

	if($forceupdate) {
		block_updatecache($bid, true);
		$block = $_G['block'][$bid];
	} elseif($block['cachetime'] > 0 && $_G['timestamp'] - $block['dateline'] > $block['cachetime']) {
		if($isjscall || $block['punctualupdate']) {
			block_updatecache($bid, true);
			$block = $_G['block'][$bid];
		} elseif(empty($_G['blockupdate']) || $block['dateline'] < $_G['blockupdate']['dateline']) {
			$_G['blockupdate'] = array('bid'=>$bid, 'dateline'=>$block['dateline']);
		}
	}

	if($allowmem && empty($block['hidedisplay']) && empty($block['nocache'])) {
		$str = memory('get', 'blockcache_'.$bid.'_'.($isjscall ? 'js' : 'htm'));
		if($str !== null) {
			return $str;
		}
	}

	if($isjscall) {
		if($block['summary']) $str .= $block['summary'];
		$str .= block_template($bid);
	} else {
		$classname = !empty($block['classname']) ? $block['classname'].' ' : '';
		$str .= "<div id=\"portal_block_$bid\" class=\"{$classname}block move-span\">";
		if($block['title']) $str .= $block['title'];
		$str .= '<div id="portal_block_'.$bid.'_content" class="content">';
		if($block['summary']) {
			$block['summary'] = stripslashes($block['summary']);
			$str .= "<div class=\"portal_block_summary\">$block[summary]</div>";
		}
		$str .= block_template($bid);
		$str .= '</div>';
		$str .= "</div>";
	}

	if($allowmem && empty($block['hidedisplay']) && empty($block['nocache'])) {
		memory('set', 'blockcache_'.$bid.'_'.($isjscall ? 'js' : 'htm'), $str, getglobal('setting/memory/diyblockoutput/ttl'));
	}

	return !empty($block['hidedisplay']) ? '' : $str;
}

function block_memory_clear($bid) {
	if(memory('check')) {
		memory('rm', 'blockcache_'.$bid.'');
		memory('rm', 'blockcache_'.$bid.'_htm');
		memory('rm', 'blockcache_'.$bid.'_js');
	}
}

function block_updatecache($bid, $forceupdate=false) {
	global $_G;

	if(!$forceupdate && discuz_process::islocked('block_update_cache', 5)) {
		return false;
	}
	block_memory_clear($bid);
	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	if(!$block) {
		return ;
	}
	$obj = block_script($block['blockclass'], $block['script']);
	if(is_object($obj)) {
		DB::update('common_block', array('dateline'=>TIMESTAMP), array('bid'=>$bid));
		$block['param'] = empty($block['param'])?array():unserialize($block['param']);
		$theclass = block_getclass($block['blockclass']);
		$thestyle = !empty($block['styleid']) ? block_getstyle($block['styleid']) : unserialize($block['blockstyle']);

		if(in_array($block['blockclass'], array('forum_thread', 'group_thread', 'space_blog', 'space_pic', 'portal_article'))) {
			$datalist = array();
			$mapping = array('forum_thread'=>'tid', 'group_thread'=>'tid', 'space_blog'=>'blogid', 'space_blog'=>'picid', 'portal_article'=>'aid');
			$idtype = $mapping[$block['blockclass']];
			$bannedids = !empty($block['param']['bannedids']) ? explode(',', $block['param']['bannedids']) : array();
			$bannedsql = $bannedids ? ' AND id NOT IN ('.dimplode($bannedids).')' : '';
			$shownum = intval($block['shownum']);
			$query = DB::query('SELECT * FROM '.DB::table('common_block_item_data')." WHERE bid='$bid' AND isverified='1' $bannedsql ORDER BY stickgrade DESC, verifiedtime DESC LIMIT $shownum");
			while(($value=DB::fetch($query))) {
				$datalist[] = $value;
				$bannedids[] = intval($value['id']);
			}
			$leftnum = $block['shownum'] - count($datalist);
			if($leftnum > 0) {
				$block['param']['items'] = $leftnum;
				$block['param']['bannedids'] = implode(',',$bannedids);
				$return = $obj->getdata($thestyle, $block['param']);
				$return['data'] = array_merge($datalist, $return['data']);
			} else {
				$return['data'] = $datalist;
			}
		} else {
			$return = $obj->getdata($thestyle, $block['param']);
		}

		if($return['data'] === null) {
			$_G['block'][$block['bid']]['summary'] = $return['html'];
			DB::update('common_block', array('summary'=>daddslashes($return['html'])), array('bid'=>$bid));
		} else {
			$_G['block'][$block['bid']]['itemlist'] = block_updateitem($bid, $return['data']);
		}
	} else {
		DB::update('common_block', array('dateline'=>TIMESTAMP+999999, 'cachetime'=>0), array('bid'=>$bid));
	}
	discuz_process::unlock('block_update_cache');
}

function block_template($bid) {
	global $_G;

	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];

	$theclass = block_getclass($block['blockclass'], false);
	$thestyle = !empty($block['styleid']) ? block_getstyle($block['styleid']) : unserialize($block['blockstyle']);
	if(empty($block) || empty($theclass) || empty($thestyle)) {
		return ;
	}
	$template = block_build_template($thestyle['template']);
	if(!empty($block['itemlist'])) {

		$fields = array('picwidth'=>array(), 'picheight'=>array(), 'target'=>array(), 'currentorder'=>array());
		if($block['hidedisplay']) {
			$fields = array_merge($fields, $block[''] ? $theclass['fields'] : $thestyle['fields']);
		} else {
			$thestyle['fields'] = !empty($thestyle['fields']) && is_array($thestyle['fields']) ? $thestyle['fields'] : block_parse_fields($template);
			foreach($thestyle['fields'] as $k) {
				if(isset($theclass['fields'][$k])) {
					$fields[$k] = $theclass['fields'][$k];
				}
			}
		}

		$order = 0;
		$dynamicparts = array();
		foreach($block['itemlist'] as $itemid=>$blockitem) {
			$order++;

			$rkey = $rpattern = $rvalue = $rtpl = array();
			if(isset($thestyle['template']['index']) && is_array($thestyle['template']['index']) && isset($thestyle['template']['index'][$order])) {
				$rkey[] = 'index_'.$order;
				$rpattern[] = '/\s*\[index='.$order.'\](.*?)\[\/index\]\s*/is';
				$rvalue[] = '';
				$rtpl[] = $thestyle['template']['index'][$order];
			}
			if(!empty($thestyle['template']['indexplus'])) {
				foreach($thestyle['template']['indexplus'] as $k=>$v) {
					if(isset($v[$order])) {
						$rkey[] = 'index'.$k.'='.$order;
						$rpattern[] = '/\[index'.$k.'='.$order.'\](.*?)\[\/index'.$k.'\]/is';
						$rvalue[] = '';
						$rtpl[] = $v[$order];
					}
				}
			}
			if(empty($rkey)) {
				$rkey[] = 'loop';
				$rpattern[] = '/\s*\[loop\](.*?)\[\/loop\]\s*/is';
				$rvalue[] = isset($dynamicparts['loop']) ? $dynamicparts['loop'][1] : '';
				if(is_array($thestyle['template']['order']) && isset($thestyle['template']['order'][$order])) {
					$rtpl[] = $thestyle['template']['order'][$order];
				} elseif(is_array($thestyle['template']['order']) && isset($thestyle['template']['order']['odd']) && ($order % 2 == 1)) {
					$rtpl[] = $thestyle['template']['order']['odd'];
				} elseif(is_array($thestyle['template']['order']) && isset($thestyle['template']['order']['even']) && ($order % 2 == 0)) {
					$rtpl[] = $thestyle['template']['order']['even'];
				} else {
					$rtpl[] = $thestyle['template']['loop'];
				}
				if(!empty($thestyle['template']['loopplus'])) {
					foreach($thestyle['template']['loopplus'] as $k=>$v) {
						$rkey[] = 'loop'.$k;
						$rpattern[] = '/\s*\[loop'.$k.'\](.*?)\[\/loop'.$k.'\]\s*/is';
						$rvalue[] = isset($dynamicparts['loop'.$k]) ? $dynamicparts['loop'.$k][1] : '';
						if(is_array($thestyle['template']['orderplus'][$k]) && isset($thestyle['template']['orderplus'][$k][$order])) {
							$rtpl[] = $thestyle['template']['orderplus'][$k][$order];
						} elseif(is_array($thestyle['template']['orderplus'][$k]) && isset($thestyle['template']['orderplus'][$k]['odd']) && ($order % 2 == 1)) {
							$rtpl[] = $thestyle['template']['order'.$k]['odd'];
						} elseif(is_array($thestyle['template']['orderplus'][$k]) && isset($thestyle['template']['orderplus'][$k]['even']) && ($order % 2 == 0)) {
							$rtpl[] = $thestyle['template']['orderplus'][$k]['even'];
						} else {
							$rtpl[] = $thestyle['template']['loopplus'][$k];
						}
					}
				}
			}
			$blockitem['showstyle'] = !empty($blockitem['showstyle']) ? unserialize($blockitem['showstyle']) : array();
			$blockitem['fields'] = !empty($blockitem['fields']) ? $blockitem['fields'] : array();
			$blockitem['fields'] = is_array($blockitem['fields']) ? $blockitem['fields'] : unserialize($blockitem['fields']);
			$blockitem['picwidth'] = !empty($block['picwidth']) ? intval($block['picwidth']) : 'auto';
			$blockitem['picheight'] = !empty($block['picheight']) ? intval($block['picheight']) : 'auto';
			$blockitem['target'] = !empty($block['target']) ? ' target="_'.$block['target'].'"' : '';
			$blockitem['currentorder'] = $order;
			$blockitem['parity'] = $order % 2;

			$searcharr = $replacearr = array();
			foreach($fields as $key=>$field) {
				$replacevalue = isset($blockitem[$key]) ? $blockitem[$key] : (isset($blockitem['fields'][$key]) ? $blockitem['fields'][$key] : '');
				$field['datatype'] = !empty($field['datatype']) ? $field['datatype'] : '';
				if($field['datatype'] == 'int') {// int
					$replacevalue = intval($replacevalue);
				} elseif($field['datatype'] == 'string') {
					$replacevalue = $replacevalue;
				} elseif($field['datatype'] == 'date') {
					$replacevalue = dgmdate($replacevalue, $block['dateuformat'] ? 'u' : $block['dateformat'], '9999', $block['dateuformat'] ? $block['dateformat'] : '');
				} elseif($field['datatype'] == 'title') {//title
					$replacevalue = stripslashes($replacevalue);
					$searcharr[] = '{title-title}';
					$replacearr[] = !empty($blockitem['fields']['fulltitle']) ? $blockitem['fields']['fulltitle'] : htmlspecialchars($replacevalue);
					$searcharr[] = '{alt-title}';
					$replacearr[] = !empty($blockitem['fields']['fulltitle']) ? $blockitem['fields']['fulltitle'] : htmlspecialchars($replacevalue);
					$style = block_showstyle($blockitem['showstyle'], 'title');
					if($style) {
						$replacevalue = '<font style="'.$style.'">'.$replacevalue.'</font>';
					}
				} elseif($field['datatype'] == 'summary') {//summary
					$replacevalue = stripslashes($replacevalue);
					$style = block_showstyle($blockitem['showstyle'], 'summary');
					if($style) {
						$replacevalue = '<font style="'.$style.'">'.$replacevalue.'</font>';
					}
				} elseif($field['datatype'] == 'pic') {
					if($blockitem['picflag'] == '1') {
						$replacevalue = $_G['setting']['attachurl'].$replacevalue;
					} elseif ($blockitem['picflag'] == '2') {
						$replacevalue = $_G['setting']['ftp']['attachurl'].$replacevalue;
					}
					if($block['picwidth'] && $block['picheight'] && $block['picwidth'] != 'auto' && $block['picheight'] != 'auto') {
						if($blockitem['makethumb'] == 1) {
							$replacevalue = $_G['setting']['attachurl'].block_thumbpath($block, $blockitem);
						} elseif(!$_G['block_makethumb'] && !$blockitem['makethumb']) {
							DB::update('common_block_item', array('makethumb'=>2), array('itemid'=>$blockitem['itemid']));
							require_once libfile('class/image');
							$image = new image();
							$thumbpath = block_thumbpath($block, $blockitem);
							if(file_exists($_G['setting']['attachdir'].$thumbpath) || ($return = $image->Thumb($replacevalue, $thumbpath, $block['picwidth'], $block['picheight'], 2))) {
								DB::update('common_block_item', array('makethumb'=>1), array('itemid'=>$blockitem['itemid']));
								$replacevalue = $_G['setting']['attachurl'].$thumbpath;
								$_G['block_makethumb'] = true;
							}
						}
					}
				}
				$searcharr[] = '{'.$key.'}';
				$replacearr[] = $replacevalue;

				$_G['block_'.$bid][$order-1][$key] = $replacevalue;
			}
			foreach($rtpl as $k=>$str_template) {
				if($str_template) {
					$str_template = preg_replace('/title=[\'"]{title}[\'"]/', 'title="{title-title}"', $str_template);
					$str_template = preg_replace('/alt=[\'"]{title}[\'"]/', 'alt="{alt-title}"', $str_template);
					$rvalue[$k] .= str_replace($searcharr, $replacearr, $str_template);
					$dynamicparts[$rkey[$k]] = array($rpattern[$k], $rvalue[$k]);
				}
			}
		}// foreach($block['itemlist'] as $itemid=>$blockitem) {

		foreach($dynamicparts as $value) {
			$template = preg_replace($value[0], $value[1], $template);
		}
	}
	$template = preg_replace('/\s*\[(order\d{0,1})=\w+\](.*?)\[\/\\1\]\s*/is', '', $template);
	$template = preg_replace('/\s*\[(index\d{0,1})=\w+\](.*?)\[\/\\1\]\s*/is', '', $template);
	$template = preg_replace('/\s*\[(loop\d{0,1})\](.*?)\[\/\\1\]\s*/is', '', $template);

	return $template;
}

function block_showstyle($showstyle, $key) {
	$style = '';
	if(!empty($showstyle["{$key}_b"])) {
		$style .= 'font-weight: 900;';
	}
	if(!empty($showstyle["{$key}_i"])) {
		$style .= 'font-style: italic;';
	}
	if(!empty($showstyle["{$key}_u"])) {
		$style .= 'text-decoration: underline;';
	}
	if(!empty($showstyle["{$key}_c"])) {
		$style .= 'color: '.$showstyle["{$key}_c"].';';
	}
	return $style;
}


function block_setting($blockclass, $script, $values = array()) {
	global $_G;

	$return = array();
	$obj = block_script($blockclass, $script);
	if(!is_object($obj)) return $return;
	return block_makeform($obj->getsetting(), $values);
}

function block_makeform($blocksetting, $values){
	global $_G;
	static $randomid = 0, $calendar_loaded = false;
	$return = array();
	foreach($blocksetting as $settingvar => $setting) {
		$varname = in_array($setting['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
			($setting['type'] == 'mselect' ? array('parameter['.$settingvar.'][]', $setting['value']) : array('parameter['.$settingvar.']', $setting['value']))
			: 'parameter['.$settingvar.']';
		$value = isset($values[$settingvar]) ? dstripslashes($values[$settingvar]) : $setting['default'];
		$type = $setting['type'];
		$s = $comment = '';
		if(preg_match('/^([\w]+?)_[\w]+$/i', $setting['title'], $match)) {
			$langscript = $match[1];
			$setname = lang('block/'.$langscript, $setting['title']);
			$comment = lang('block/'.$langscript, $setting['title'].'_comment', array(), '');
		} else {
			$langscript = '';
			$setname = $setting['title'];
		}
		$check = array();
		if($type == 'radio') {
			$value ? $check['true'] = "checked" : $check['false'] = "checked";
			$value ? $check['false'] = '' : $check['true'] = '';
			$s .= '<input type="radio" class="pr" name="'.$varname.'" id="randomid_'.(++$randomid).'" value="1" '.$check['true'].'>&nbsp;<label for="randomid_'.$randomid.'">'.lang('core', 'yes').'</label>&nbsp;&nbsp;'.
				'<input type="radio" class="pr" name="'.$varname.'" id="randomid_'.(++$randomid).'" value="0" '.$check['false'].'>&nbsp;<label for="randomid_'.$randomid.'">'.lang('core', 'no').'</label>';
		} elseif($type == 'text' || $type == 'password' || $type == 'number') {
			$s .= '<input name="'.$varname.'" value="'.dhtmlspecialchars($value).'" type="'.$type.'" class="px" />';
		} elseif($type == 'textarea') {
			$s .= '<textarea rows="4" name="'.$varname.'" cols="50" class="pt">'.dhtmlspecialchars($value).'</textarea>';
		} elseif($type == 'select') {
			$s .= '<select name="'.$varname[0].'" class="ps">';
			foreach($varname[1] as $option) {
				$selected = $option[0] == $value ? ' selected="selected"' : '';
				$s .= '<option value="'.$option[0].'"'.$selected.'>'.($langscript ? lang('block/'.$langscript, $option[1]) : $option[1]).'</option>';
			}
			$s .= '</select>';
		} elseif($type == 'mradio') {
			if(is_array($varname)) {
				$radiocheck = array($value => ' checked');
				$s .= '<ul'.(empty($varname[2]) ?  ' class="pr"' : '').'>';
				foreach($varname[1] as $varary) {
					if(is_array($varary) && !empty($varary)) {
						$s .= '<li'.($radiocheck[$varary[0]] ? ' class="checked"' : '').'><input class="pr" type="radio" name="'.$varname[0].'" id="randomid_'.(++$randomid).'" value="'.$varary[0].'"'.$radiocheck[$varary[0]].'>&nbsp;<label for="randomid_'.$randomid.'">'.($langscript ? lang('block/'.$langscript, $varary[1]) : $varary[1]).'</label></li>';
					}
				}
				$s .= '</ul>';
			}
		} elseif($type == 'mcheckbox') {
			$s .= '<ul class="nofloat">';
			foreach($varname[1] as $varary) {
				if(is_array($varary) && !empty($varary)) {
					$checked = is_array($value) && in_array($varary[0], $value) ? ' checked' : '';
					$s .= '<li'.($checked ? ' class="checked"' : '').'><input class="pc" type="checkbox" name="'.$varname[0].'[]" id="randomid_'.(++$randomid).'" value="'.$varary[0].'"'.$checked.'>&nbsp;<label for="randomid_'.$randomid.'">'.($langscript ? lang('block/'.$langscript, $varary[1]) : $varary[1]).'</label></li>';
				}
			}
			$s .= '</ul>';
		} elseif($type == 'mselect') {
			$s .= '<select name="'.$varname[0].'" multiple="multiple" size="10" class="ps">';
			foreach($varname[1] as $option) {
				$selected = is_array($value) && in_array($option[0], $value) ? ' selected="selected"' : '';
				$s .= '<option value="'.$option[0].'"'.$selected.'>'.($langscript ? lang('block/'.$langscript, $option[1]) : $option[1]).'</option>';
			}
			$s .= '</select>';
		} elseif($type == 'calendar') {
			if(! $calendar_loaded) {
				$s .= "<script type=\"text/javascript\" src=\"{$_G[setting][jspath]}calendar.js?".VERHASH."\"></script>";
				$calendar_loaded = true;
			}
			$s .= '<input name="'.$varname.'" value="'.dhtmlspecialchars($value).'" type="text" onclick="showcalendar(event, this, true)" class="px" />';
		} elseif($type == 'district') {
			include_once libfile('function/profile');
			$elems = $vals = array();
			$districthtml = '';
			foreach($setting['value'] as $fieldid) {
				$elems[] = 'parameter['.$fieldid.']';
				$vals[$fieldid] = $values[$fieldid];
				if(!empty($values[$fieldid])) {
					$districthtml .= $values[$fieldid].'<input type="hidden" name="parameter['.$fieldid.']" value="'.$values[$fieldid].'" /> ';
				}
			}
			$containerid = 'randomid_'.(++$randomid);
			if($districthtml) {
				$s .= $districthtml;
				$s .= '&nbsp;&nbsp;<a href="javascript:;" onclick="showdistrict(\''.$containerid.'\', ['.dimplode($elems).'], '.count($elems).'); return false;">'.lang('spacecp', 'profile_edit').'</a>';
				$s .= '<p id="'.$containerid.'"></p>';
			} else {
				$s .= "<div id=\"$containerid\">".showdistrict($vals, $elems, $containerid).'</div>';
			}
		} else {
			$s .= $type;
		}
		$return[] = array('title' => $setname, 'html' => $s, 'comment'=>$comment);
	}
	return $return;
}
function block_updateitem($bid, $items=array()) {
	global $_G;
	$block = $_G['block'][$bid];
	if(!$block) {
		$block = DB::fetch_first('SELECT * FROM '.DB::table('common_block')." WHERE bid='$bid'");
		if(!$block) {
			return false;
		}
		$_G['block'][$bid] = $block;
	}
	$block['shownum'] = max($block['shownum'], 1);
	$showlist = array_fill(1, $block['shownum'], array());
	$archivelist = array();
	$prelist = array();
	$manualkeys = array();
	$archivekeys = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_block_item')." WHERE bid='$bid'");
	while($value=DB::fetch($query)) {
		$key = $value['idtype'].'_'.$value['id'];
		$op_pre = $op_archive = $op_show = false;
		if($value['itemtype'] == '1') {
			if($value['startdate'] > TIMESTAMP) {
				$op_pre = true;
			} elseif((!$value['startdate'] || $value['startdate'] <= TIMESTAMP)
					&& (!$value['enddate'] || $value['enddate'] > TIMESTAMP)
					&& isset($showlist[$value['displayorder']])) {

				$op_show = true;
			} else {
				$op_archive = true;
			}
		} elseif($value['itemtype'] == '2') {

			foreach($items as $v) {
				if($key == $v['idtype'].'_'.$v['id']) {
					$op_show = true;
					break;
				}
			}
			if(! $op_show) {
				$op_archive = true;
			}
		} else {
			$op_archive = true;
		}

		if($op_pre) {
			$prelist[] = $value;
			$manualkeys[$key] = true;
		} elseif($op_show) {
			$showlist[$value['displayorder']] = $value;
			$manualkeys[$key] = true;
		} elseif($op_archive) {
			$archivelist[$value['itemid']] = 1;
			$archivekeys[$key] = $value['itemid'];
		}
	}
	$itemindex = 0;
	for($i=1; $i<=$block['shownum']; $i++) {
		if(empty($showlist[$i])) {
			$item = array_shift($items);
			$key = $item['idtype'].'_'.$item['id'];
			while($item && !empty($manualkeys[$key])) {
				$item = array_shift($items);
				$key = $item['idtype'].'_'.$item['id'];
			}
			if(!$item) {
				break;
			}
			$item['displayorder'] = $i;
			$item['makethumb'] = 0;
			if(is_array($item['fields'])) {
				$item['fields'] = serialize($item['fields']);
			}

			if($archivekeys[$key]) {
				$item['itemid'] = $archivekeys[$key];
				unset($archivelist[$archivekeys[$key]]);
			}
			$showlist[$i] = $item;
		}
		$showlist[$i]['displayorder'] = $i;
		$showlist[$i]['makethumb'] = 0;
		if($block['picwidth'] && $block['picheight'] && file_exists($_G['setting']['attachdir'].block_thumbpath($block, $showlist[$i]))) {
			$showlist[$i]['makethumb'] = 1;
		}
	}
	if($archivelist) {
		$delids = array_keys($archivelist);
		DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE bid='$bid' AND itemid IN (".dimplode($delids).")");
	}
	$inserts = $itemlist = array();
	$itemlist = array_merge($showlist, $prelist);
	foreach($itemlist as $value) {
		if($value) {
			$value = daddslashes($value);
			$inserts[] = "('$value[itemid]', '$bid', '$value[itemtype]', '$value[id]', '$value[idtype]', '$value[title]',
				 '$value[url]', '$value[pic]', '$value[summary]', '$value[showstyle]', '$value[related]',
				 '$value[fields]', '$value[displayorder]', '$value[startdate]', '$value[enddate]', '$value[picflag]', '$value[makethumb]')";
		}
	}
	if($inserts) {
		DB::query('REPLACE INTO '.DB::table('common_block_item')."(itemid, bid, itemtype, id, idtype, title, url, pic, summary, showstyle, related, `fields`, displayorder, startdate, enddate, picflag, makethumb) VALUES ".implode(',', $inserts));
	}

	$showlist = array_filter($showlist);
	return $showlist;
}

function block_thumbpath($block, $item) {
	global $_G;
	$hash = md5($item['pic'].'-'.$item['picflag'].':'.$block['picwidth'].'|'.$block['picheight']);
	return 'block/'.substr($hash, 0, 2).'/'.$hash.'.jpg';
}

function block_getclass($classname, $getstyle=false) {
	global $_G;
	if(!isset($_G['cache']['blockclass'])) {
		loadcache('blockclass');
	}
	$theclass = array();
	list($c1, $c2) = explode('_', $classname);
	if(is_array($_G['cache']['blockclass']) && isset($_G['cache']['blockclass'][$c1]['subs'][$classname])) {
		$theclass = $_G['cache']['blockclass'][$c1]['subs'][$classname];
		if($getstyle && !isset($theclass['style'])) {
			$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." WHERE blockclass='$classname'");
			while(($value=DB::fetch($query))) {
				$value['template'] = !empty($value['template']) ? (array)(unserialize($value['template'])) : array();
				$value['fields'] = !empty($value['fields']) ? (array)(unserialize($value['fields'])) : array();
				$key = 'blockstyle_'.$value['styleid'];
				$_G[$key] = $value;
				$theclass['style'][$value['styleid']] = $value;
			}
			$_G['cache']['blockclass'][$c1]['subs'][$classname] = $theclass;
		}
	}
	return $theclass;
}

function block_getdiyurl($tplname, $diymod = false) {
	$mod = $id = $script = $url = '';
	$flag = 0;
	if (empty ($tplname)) {
		$flag = 2;
	} else {
		list($script,$tpl) = explode('/',$tplname);
		if (!empty($tpl)) {
			$arr = array();
			preg_match_all('/(.*)\_(\d{1,9})/', $tpl,$arr);
			$mod = empty($arr[1][0]) ? $tpl : $arr[1][0];
			$id = max(intval($arr[2][0]),0);
			if($script == 'ranklist') {
				$script = 'misc';
				$mod = 'ranklist&type='.$mod;
			} else {
				switch ($mod) {
					case 'index' :
					case 'discuz' :
						$mod = 'index';
						break;
					case 'space_home' :
						$mod = 'space';
						break;
					case 'forumdisplay' :
						$flag = $id ? 0 : 1;
						$mod .= '&fid='.$id;
						break;
					case 'list' :
						$flag = $id ? 0 : 1;
						$mod .= '&catid='.$id;
						break;
					case 'portal_topic_content' :
						$flag = $id ? 0 : 1;
						$mod = 'topic&topic='.$id;
						break;
					case 'view' :
						$flag = $id ? 0 : 1;
						$mod .= '&aid='.$id;
						break;
					default :
						break;
				}
			}
		}
		$url = empty($mod) ? '' : $script.'.php?mod='.$mod.($diymod?'&diy=yes':'');
	}
	return array('url'=>$url,'flag'=>$flag);
}

function block_clear() {
	$uselessbids = $usingbids = $bids = array();
	$query = DB::query("SELECT bid FROM ".DB::table('common_block')." WHERE blocktype='0' ORDER BY bid DESC LIMIT 1000");
	while($value = DB::fetch($query)) {
		$bids[] = intval($value['bid']);
	}
	$query = DB::query("SELECT bid FROM ".DB::table('common_template_block')." WHERE bid IN (".dimplode($bids).")");
	while(($value = DB::fetch($query))) {
		$usingbids[] = intval($value['bid']);
	}
	$uselessbids = array_diff($bids, $usingbids);
	if (!empty($uselessbids)) {
		$delids = dimplode($uselessbids);
		DB::query("DELETE FROM ".DB::table('common_block')." WHERE bid IN ($delids)");
		DB::query("DELETE FROM ".DB::table('common_block_item')." WHERE bid IN ($delids)");
		DB::delete('common_block_permission', 'bid IN ('.$delids.')');
		DB::query("OPTIMIZE TABLE ".DB::table('common_block'), 'SILENT');
		DB::query("OPTIMIZE TABLE ".DB::table('common_block_item'), 'SILENT');
	}
}

function block_getstyle($styleid) {
	global $_G;
	$styleid = intval($styleid);
	$key = 'blockstyle_'.$styleid;
	if(!isset($_G[$key])) {
		$value = array();
		if($styleid) {
			$value = DB::fetch_first('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid='$styleid'");
			$value['template'] = !empty($value['template']) ? (array)(unserialize($value['template'])) : array();
			$value['fields'] = !empty($value['fields']) ? (array)(unserialize($value['fields'])) : array();
		}
		$_G[$key] = $value;
	}
	return $_G[$key];
}

function blockclass_cache() {
	global $_G;
	$data = $dirs = $styles = array();
	$dir = DISCUZ_ROOT.'/source/class/block/';
	$dh = opendir($dir);
	while(($filename=readdir($dh))) {
		if(is_dir($dir.$filename) && substr($filename,0,1) != '.') {
			$dirs[$filename] = $dir.$filename.'/';
		}
	}
	foreach($dirs as $name=>$dir) {
		$blockclass = array();
		if(file_exists($dir.'blockclass.php')) {
			include_once($dir.'blockclass.php');
		}
		if(empty($blockclass['name'])) {
			$blockclass['name'] = $name;
		} else {
			$blockclass['name'] = htmlspecialchars($blockclass['name']);
		}
		$blockclass['subs'] = array();

		$dh = opendir($dir);
		while(($filename=readdir($dh))) {
			$match = $info = array();
			$scriptname = $scriptclass = '';
			if(preg_match('/^(block_[\w]+)\.php$/i', $filename, $match)) {
				$scriptclass = $match[1];
				$scriptname = preg_replace('/^block_/i', '', $scriptclass);
				include_once $dir.$filename;
				if(class_exists($scriptclass)) {
					$obj = new $scriptclass();
					if(method_exists($obj, 'name') && method_exists($obj, 'blockclass') && method_exists($obj, 'fields')
							&& method_exists($obj, 'getsetting') && method_exists($obj, 'getdata')) {
						$info['name'] = $obj->name();
						$info['blockclass'] = $obj->blockclass();
						$info['fields'] = $obj->fields();
					}
				}
			}
			if($info['name'] && is_array($info['blockclass']) && $info['blockclass'][0] && $info['blockclass'][1]) {
				list($key, $title) = $info['blockclass'];
				$key = $name.'_'.$key;
				if(!isset($blockclass['subs'][$key])) {
					$blockclass['subs'][$key] = array(
						'name' => $title,
						'fields' => $info['fields'],
						'script' => array()
					);
				}
				$blockclass['subs'][$key]['script'][$scriptname] = $info['name'];
			}
		}

		if($blockclass['subs']) {
			$data[$name] = $blockclass;

			$blockstyle = array();
			if(file_exists($dir.'blockstyle.php')) {
				include_once($dir.'blockstyle.php');
			}
			if($blockstyle) {
				foreach($blockstyle as $value) {
					$arr = array(
						'blockclass'=>$name.'_'.$value['blockclass'],
						'name' => $value['name']
					);
					block_parse_template($value['template'], $arr);
					$styles[$arr['hash']] = $arr;
				}
			}
		}
	}

	if($styles) {
		$hashes = array_keys($styles);
		$query = DB::query('SELECT `hash` FROM '.DB::table('common_block_style')." WHERE `hash` IN (".dimplode($hashes).")");
		while(($value=DB::fetch($query))) {
			unset($styles[$value['hash']]);
		}
		if($styles) {
			$inserts = array();
			foreach($styles as $value) {
				$value = daddslashes($value);
				$inserts[] = "('$value[blockclass]', '$value[name]', '$value[template]', '$value[hash]', '$value[getpic]', '$value[getsummary]', '$value[settarget]', '$value[fields]')";
			}
			DB::query('INSERT INTO '.DB::table('common_block_style')."(`blockclass`, `name`, `template`, `hash`, `getpic`, `getsummary`, `settarget`, `fields`) VALUES ".implode(',',$inserts));
		}
	}
	save_syscache('blockclass', $data);
}

function block_parse_template($str_template, &$arr) {

	$arr['makethumb'] = strexists($str_template, '{picwidth}') ? 1 : 0;
	$arr['getpic'] = strexists($str_template, '{pic}') ? 1 : 0;
	$arr['getsummary'] = strexists($str_template, '{summary}') ? 1 : 0;
	$arr['settarget'] = strexists($str_template, '{target}') ? 1 : 0;
	$fields = block_parse_fields($str_template);
	$arr['fields'] = serialize($fields);

	$template = array();
	$template['raw'] = $str_template;
	$template['header'] = $template['footer'] = '';
	$template['loop'] = $template['loopplus'] = $template['order'] = $template['orderplus'] = $template['index'] = $template['indexplus'] = array();

	$match = array();
	if(preg_match('/\[loop\](.*?)\[\/loop]/is', $str_template, $match)) {
		$template['loop'] = trim($match[1]);
	}
	$match = array();
	if(preg_match_all('/\[(loop\d)\](.*?)\[\/\\1]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$value) {
			$content = trim($match[2][$key]);
			$k = intval(str_replace('loop', '', $value));
			$template['loopplus'][$k] = $content;
		}
	}
	$match = array();
	if(preg_match('/\[order=(\d+|odd|even)\](.*?)\[\/order]/is', $str_template, $match)) {
		$order = $match[1];
		$template['order'][$order] = trim($match[2]);
	}
	$match = array();
	if(preg_match_all('/\[(order\d)=(\d+|odd|even)\](.*?)\[\/\\1]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$value) {
			$content = trim($match[3][$key]);
			$order = $match[2][$key];
			$k = intval(str_replace('order', '', $value));
			$template['orderplus'][$k][$order] = $content;
		}
	}
	$match = array();
	if(preg_match_all('/\[index=(\d)\](.*?)\[\/index]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$order) {
			$template['index'][$order] = trim($match[2][$key]);
		}
	}
	$match = array();
	if(preg_match_all('/\[(index\d)=(\d)\](.*?)\[\/\\1]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$value) {
			$content = trim($match[3][$key]);
			$order = intval($match[2][$key]);
			$k = intval(str_replace('index', '', $value));
			$template['indexplus'][$k][$order] = $content;
		}
	}
	$arr['template'] = serialize($template);
	$arr['hash'] = substr(md5($arr['blockclass'].'|'.$arr['template']), 8, 8);
}

function block_parse_fields($template) {
	$fields = array();
	if(preg_match_all('/\{(\w+)\}/', $template, $matches)) {
		foreach($matches[1] as $fieldname) {
			$fields[] = $fieldname;
		}
		$fields = array_unique($fields);
		$fields = array_diff($fields, array('picwidth', 'picheight', 'target', ''));
		$fields = array_values($fields);
	}
	return $fields;
}

function block_build_template($template) {
	if(! is_array($template)) {
		return $template;
	}
	if(!empty($template['raw'])) {
		return $template['raw'];
	}
	$str_template = $template['header'];
	if($template['loop']) {
		$str_template .= "\n[loop]\n{$template['loop']}\n[/loop]";
	}
	if(!empty($template['order']) && is_array($template['order'])) {
		foreach($template['order'] as $key=>$value) {
			$str_template .= "\n[order={$key}]\n{$value}\n[/order]";
		}
	}
	$str_template .= $template['footer'];
	return $str_template;
}

function block_isrecommendable($block) {
	return !empty($block) && in_array($block['blockclass'], array('forum_thread', 'group_groupthread', 'portal_article', 'space_pic', 'space_blog')) ? true : false;
}

?>