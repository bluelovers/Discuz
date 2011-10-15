<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_portalcp.php 24656 2011-09-29 09:25:32Z zhangguosheng $
 */

function get_uploadcontent($attach, $type='portal', $dotype='') {

	$return = '';
	$dotype = $dotype ? 'checked' : '';
	$attach['attachid'] = $attach['aid'] ? $attach['aid'] : $attach['attachid'];
	if($attach['isimage']) {
		$pic = pic_get($attach['attachment'], $type, $attach['thumb'], $attach['remote'], 0);
		$small_pic = $attach['thumb'] ? getimgthumbname($pic) : '';
		$check = $attach['pic'] == $type.'/'.$attach['attachment'] ? 'checked' : $dotype;
		$aid = $check ? $attach['aid'] : '';

		$return .= '<table id="attach_list_'.$attach['attachid'].'" width="100%" class="xi2">';
		$return .= '<td width="50" class="bbs"><a href="'.$pic.'" target="_blank"><img src="'.($small_pic ? $small_pic : $pic).'" width="40" height="40"></a></td>';
		$return .= '<td align="right" class="bbs">';
		$return .= '<label for="setconver'.$attach['attachid'].'"><input type="radio" name="setconver" id="setconver'.$attach['attachid'].'" class="pr" value="1" onclick=setConver(\''.addslashes(serialize(array('pic'=>$type.'/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote']))).'\') '.$check.'>'.lang('portalcp', 'set_to_conver').'</label><br>';
		if($small_pic) $return .= '<a href="javascript:void(0);" onclick="insertImage(\''.$small_pic.'\', \''.$pic.'\');return false;">'.lang('portalcp', 'insert_small_image').'</a><br>';
		$return .= '<a href="javascript:void(0);" onclick="insertImage(\''.$pic.'\');return false;">'.lang('portalcp', 'insert_large_image').'</a><br>';
		if($type == 'portal') $return .= '<a href="javascript:void(0);" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&aid='.$aid.'&op=delete\');return false;">'.lang('portalcp', 'delete').'</a>';
		$return .= '</td>';
		$return .= '</table>';

	} else {
		$attach_url = $type == 'forum' ? 'forum.php?mod=attachment&aid='.aidencode($attach['attachid'], 1) : 'protal.php?mod=attachment&id='.$attach['attachid'];
		$return .= '<table id="attach_list_'.$attach['attachid'].'" width="100%" class="xi2">';
		$return .= '<td width="50" class="bbs"><a href="'.$attach_url.'" target="_blank">'.$attach['filename'].'</a></td>';
		$return .= '<td align="right" class="bbs">';
		$return .= '<a href="javascript:void(0);" onclick="insertFile(\''.$attach['filename'].'\', \''.$attach_url.'\');return false;">'.lang('portalcp', 'insert_file').'</a><br>';
		if($type == 'portal') $return .= '<a href="javascript:void(0);" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&op=delete\');return false;">'.lang('portalcp', 'delete').'</a>';
		$return .= '</td>';
		$return .= '</table>';
	}
	return $return;

}

function getallowcategory($uid){
	global $_G;
	$permission = array();
	if (empty($uid)) return $permission;
	if(getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3)) {
		$uid = max(0,intval($uid));
		$query = DB::query('SELECT * FROM '.DB::table('portal_category_permission')." WHERE uid='$uid'");
		while($value = DB::fetch($query)) {
			if ($value['allowpublish'] || $value['allowmanage']) {
				$catid = $value['catid'];
				$permission[$catid] = $value;
			}
		}
	}
	return $permission;
}

function getpermissioncategory($category, $permission = array()) {

	$cats = array();
	foreach ($permission as $k=>$v) {
		$cur = $category[$v];

		if ($cur['level'] != 0) {
			while ($cur['level']) {
				$cats[$cur['upid']]['permissionchildren'][$cur['catid']] = $cur['catid'];
				$cur = $category[$cur['upid']];
			}
		} elseif(empty($cats[$v])) {
			$cats[$v] = array();
		}
	}

	return $cats;
}

function getallowdiytemplate($uid){
	if (empty($uid)) return false;
	$permission = array();
	$uid = max(0,intval($uid));
	$query = DB::query("SELECT tp.* FROM ".DB::table('common_template_permission')." tp WHERE tp.uid='$uid'");
	while($value = DB::fetch($query)) {
			$permission[$value['targettplname']] = $value;
	}
	return $permission;
}

function save_diy_data($primaltplname, $targettplname, $data, $database = false, $optype = '') {
	global $_G;
	if (empty($data) || !is_array($data)) return false;
	checksecurity($data['spacecss']);
	$file = ($_G['cache']['style_default']['tpldir'] ? $_G['cache']['style_default']['tpldir'] : './template/default').'/'.$primaltplname.'.htm';
	if (!file_exists($file)) {
		$file = './template/default/'.$primaltplname.'.htm';
	}
	if(!file_exists($file)) return false;
	$content = file_get_contents(DISCUZ_ROOT.$file);
	$content = preg_replace("/\<\!\-\-\[name\].+?\[\/name\]\-\-\>\s+/is", '', $content);
	$content = preg_replace("/\<script src\=\"misc\.php\?mod\=diyhelp\&action\=get.+?\>\<\/script\>/", '', $content);
	foreach ($data['layoutdata'] as $key => $value) {
		$key = trimdxtpllang($key);
		$html = '';
		$html .= '<div id="'.$key.'" class="area">';
		$html .= getframehtml($value);
		$html .= '</div>';
		$content = preg_replace("/(\<\!\-\-\[diy\=$key\]\-\-\>).+?(\<\!\-\-\[\/diy\]\-\-\>)/is", "\\1".$html."\\2", $content);
	}
	$data['spacecss'] = str_replace('.content', '.dxb_bc', $data['spacecss']);
	$data['spacecss'] = trimdxtpllang($data['spacecss']);
	$content = preg_replace("/(\<style id\=\"diy_style\" type\=\"text\/css\"\>).*?(\<\/style\>)/is", "\\1".$data['spacecss']."\\2", $content);
	if (!empty($data['style'])) {
		$content = preg_replace("/(\<link id\=\"style_css\" rel\=\"stylesheet\" type\=\"text\/css\" href\=\").+?(\"\>)/is", "\\1".$data['style']."\\2", $content);
	}

	$flag = $optype == 'savecache' ? true : false;
	if($flag) {
		$targettplname = $targettplname.'_diy_preview';
	} else {
		@unlink('./data/diy/'.$targettplname.'_diy_preview.htm');
	}

	$tplfile =DISCUZ_ROOT.'./data/diy/'.$targettplname.'.htm';
	$tplpath = dirname($tplfile);
	if (!is_dir($tplpath)) {
		dmkdir($tplpath);
	} else {
		if (file_exists($tplfile) && !$flag) copy($tplfile, $tplfile.'.bak');
	}
	$r = file_put_contents($tplfile, $content);
	if ($r && $database && !$flag) {
		$tpldata = daddslashes(serialize($data));
		$diytplname = getdiytplname($targettplname);
		$diytplname = addslashes($diytplname);
		DB::query("REPLACE INTO ".DB::table('common_diy_data')." (targettplname, primaltplname, diycontent, `name`, uid, username, dateline) VALUES ('$targettplname', '$primaltplname', '$tpldata', '$diytplname', '$_G[uid]', '$_G[username]', '".TIMESTAMP."')");
	}
	return $r;
}

function getdiytplname($targettplname) {
	$diytplname = DB::result_first("SELECT name FROM ".DB::table('common_diy_data')." WHERE targettplname='$targettplname'");
	if(empty($diytplname)) {
		$sql = '';
		if (substr($targettplname, 0, 27) == 'portal/portal_topic_content') {
			$id = intval(str_replace('portal/portal_topic_content_', '', $targettplname));
			if(!empty($id)) {
				$sql = "SELECT title FROM ".DB::table('portal_topic')." WHERE topicid='$id'";
			}
		} elseif (substr($targettplname, 0, 11) == 'portal/list') {
			$id = intval(str_replace('portal/list_', '', $targettplname));
			if(!empty($id)) {
				$sql = "SELECT catname FROM ".DB::table('portal_category')." WHERE catid='$id'";
			}
		}
		if(!empty($sql)) {
			$diytplname = DB::result(DB::query($sql));
		}
	}
	return $diytplname;
}
function getframehtml($data = array()) {
	global $_G;
	$html = $style = '';
	foreach ((array)$data as $id => $content) {
		$id = trimdxtpllang($id);
		$flag = $name = '';
		list($flag, $name) = explode('`', $id);
		if ($flag == 'frame') {
			$fattr = $content['attr'];
			$fattr['name'] = trimdxtpllang($fattr['name']);
			$fattr['className'] = trimdxtpllang($fattr['className']);
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			if (checkhastitle($fattr['titles'])) {
				$style = gettitlestyle($fattr['titles']);
				$cn = trimdxtpllang(implode(' ',$fattr['titles']['className']));
				$html .= '<div class="'.$cn.'"'.$style.'>'.gettitlehtml($fattr['titles'], 'frame').'</div>';
			}
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				$colname = trimdxtpllang($colname);
				$cn = trimdxtpllang($coldata['attr']['className']);
				if ($colflag == 'column') {
					$html .= '<div id="'.$colname.'" class="'.$cn.'">';
					$html .= '<div id="'.$colname.'_temp" class="move-span temp"></div>';
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '</div>';
		} elseif ($flag == 'tab') {
			$fattr = $content['attr'];
			$fattr['name'] = trimdxtpllang($fattr['name']);
			$fattr['className'] = trimdxtpllang($fattr['className']);
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			$switchtype = 'click';
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				$colname = trimdxtpllang($colname);
				$cn = trimdxtpllang($coldata['attr']['className']);
				if ($colflag == 'column') {
					if (checkhastitle($fattr['titles'])) {
						$style = gettitlestyle($fattr['titles']);
						$title = gettitlehtml($fattr['titles'], 'tab');
					}
					$switchtype = is_array($fattr['titles']['switchType']) && !empty($fattr['titles']['switchType'][0]) ? $fattr['titles']['switchType'][0] : 'click';
					$switchtype = in_array(strtolower($switchtype), array('click', 'mouseover')) ? $switchtype : 'click';
					$html .= '<div id="'.$colname.'" class="'.$cn.'"'.$style.' switchtype="'.$switchtype.'">'.$title;
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '<div id="'.$fattr['name'].'_content" class="tb-c"></div>';
			$html .= '<script type="text/javascript">initTab("'.$fattr['name'].'","'.$switchtype.'");</script>';
			$html .= '</div>';
		} elseif ($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if (!empty($bid)) {
				$html .= "<!--{block/{$bid}}-->";
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}

	return $html;
}
function gettitlestyle($title) {
	$style = '';
	if (is_array($title['style']) && count($title['style'])) {
		foreach ($title['style'] as $k=>$v){
			$style .= trimdxtpllang($k).':'.trimdxtpllang($v).';';
		}
	}
	$style = $style ? ' style=\''.$style.'\'' : '';
	return $style;
}
function checkhastitle($title) {
	if (!is_array($title)) return false;
	foreach ($title as $k => $v) {
		if (strval($k) == 'className') continue;
		if (!empty($v['text'])) return true;
	}
	return false;
}

function gettitlehtml($title, $type) {
	global $_G;
	if (!is_array($title)) return '';
	$html = $one = $style = $color =  '';
	foreach ($title as $k => $v) {
		if (in_array(strval($k),array('className','style'))) continue;
		if (empty($v['src']) && empty($v['text'])) continue;
		$v['className'] = trimdxtpllang($v['className']);
		$v['font-size'] = intval($v['font-size']);
		$v['margin'] = intval($v['margin']);
		$v['float'] = trimdxtpllang($v['float']);
		$v['color'] = trimdxtpllang($v['color']);
		$v['src'] = trimdxtpllang($v['src']);
		$v['href'] = trimdxtpllang($v['href']);
		$v['text'] = htmlspecialchars(str_replace(array('{', '$'), array('{ ', '$ '), $v['text']));
		$one = "<span class=\"{$v['className']}\"";
		$style = $color = "";
		$style .= empty($v['font-size']) ? '' : "font-size:{$v['font-size']}px;";
		$style .= empty($v['float']) ? '' : "float:{$v['float']};";
		$margin_ = empty($v['float']) ? 'left' : $v['float'];
		$style .= empty($v['margin']) ? '' : "margin-{$margin_}:{$v['margin']}px;";
		$color = empty($v['color']) ? '' : "color:{$v['color']};";
		$img = !empty($v['src']) ? '<img src="'.$v['src'].'" class="vm" alt="'.$v['text'].'"/>' : '';
		if (empty($v['href'])) {
			$style = empty($style)&&empty($color) ? '' : ' style="'.$style.$color.'"';
			$one .= $style.">$img{$v['text']}";
		} else {
			$style = empty($style) ? '' : ' style="'.$style.'"';
			$colorstyle = empty($color) ? '' : ' style="'.$color.'"';
			$one .= $style.'><a href="'.$v['href'].'" target="_blank"'.$colorstyle.'>'.$img.$v['text'].'</a>';
		}
		$one .= '</span>';

		$siteurl = str_replace(array('/','.'),array('\/','\.'),$_G['siteurl']);
		$one = preg_replace('/\"'.$siteurl.'(.*?)\"/','"$1"',$one);

		$html = $k === 'first' ? $one.$html : $html.$one;
	}
	return $html;
}

function gettheme($type) {
	$themes = array();
	$themedirs = dreaddir(DISCUZ_ROOT."/static/$type");
	foreach ($themedirs as $key => $dirname) {
		$now_dir = DISCUZ_ROOT."/static/$type/$dirname";
		if(file_exists($now_dir.'/style.css') && file_exists($now_dir.'/preview.jpg')) {
			$themes[] = array(
				'dir' => $type.'/'.$dirname,
				'name' => getcssname($type.'/'.$dirname)
			);
		}
	}
	return $themes;
}

function getcssname($dirname) {
	$css = @file_get_contents(DISCUZ_ROOT.'./static/'.$dirname.'/style.css');
	if($css) {
		preg_match("/\[name\](.+?)\[\/name\]/i", trim($css), $mathes);
		if(!empty($mathes[1])) $name = dhtmlspecialchars($mathes[1]);
	} else {
		$name = 'No name';
	}
	return $name;
}

function checksecurity($str) {

	$filter = array(
		'/\/\*[\n\r]*(.+?)[\n\r]*\*\//is',
		'/[^a-z0-9]+/i',
		'/important/i',
	);
	$str = preg_replace($filter, '', $str);
	if(preg_match("/(expression|import|javascript)/i", $str)) {
		showmessage('css_contains_elements_of_insecurity');
	}
	return true;
}

function block_export($bids) {
	$return = array('block'=>array(), 'style'=>array());
	if(empty($bids)) {
		return;
	}
	$styleids = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($bids).')');
	while($value=DB::fetch($query)) {
		$value['param'] = unserialize($value['param']);
		if(!empty($value['blockstyle'])) $value['blockstyle'] = unserialize($value['blockstyle']);

		$return['block'][$value['bid']] = $value;
		if(!empty($value['styleid'])) $styleids[] = intval($value['styleid']);
	}
	if($styleids) {
		$styleids = array_unique($styleids);
		$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid IN (".dimplode($styleids).')');
		while($value=DB::fetch($query)) {
			$value['template'] = unserialize($value['template']);
			if(!empty($value['fields'])) $value['fields'] = unserialize($value['fields']);
			$return['style'][$value['styleid']] = $value;
		}
	}
	return $return ;
}

function block_import($data) {
	global $_G;
	if(!is_array($data['block'])) {
		return ;
	}
	$data = daddslashes($data);
	$stylemapping = array();
	if($data['style']) {
		$hashes = $styles = array();
		foreach($data['style'] as $value) {
			$hashes[] = $value['hash'];
			$styles[$value['hash']] = $value['styleid'];
		}
		if(!empty($hashes)) {
			$query = DB::query('SELECT styleid, hash FROM '.DB::table('common_block_style')." WHERE hash IN (".dimplode($hashes).')');
			while($value=DB::fetch($query)) {
				$id = $styles[$value['hash']];
				$stylemapping[$id] = intval($value['styleid']);
				unset($styles[$value['hash']]);
			}
		}
		foreach($styles as $id) {
			$style = $data['style'][$id];
			$style['styleid'] = '';
			if(is_array($style['template'])) {
				$style['template'] = dstripslashes($style['template']);
				$style['template'] = addslashes(serialize($style['template']));
			}
			if(is_array($style['fields'])) {
				$style['fields'] = dstripslashes($style['fields']);
				$style['fields'] = addslashes(serialize($style['fields']));
			}
			$newid = DB::insert('common_block_style', $style, true);
			$stylemapping[$id] = $newid;
		}
	}

	$blockmapping = array();
	foreach($data['block'] as $block) {
		$oid = $block['bid'];
		if(!empty($block['styleid'])) {
			$block['styleid'] = intval($stylemapping[$block['styleid']]);
		}
		$block['bid'] = '';
		$block['uid'] = $_G['uid'];
		$block['username'] = $_G['username'];
		$block['dateline'] = 0;
		$block['notinherited'] = 0;
		if(is_array($block['param'])) {
			$block['param'] = dstripslashes($block['param']);
			$block['param'] = addslashes(serialize($block['param']));
		}
		if(is_array($block['blockstyle'])) {
			$block['blockstyle'] = dstripslashes($block['blockstyle']);
			$block['blockstyle'] = addslashes(serialize($block['blockstyle']));
		}
		$newid = DB::insert('common_block', $block, true);
		$blockmapping[$oid] = $newid;
	}
	include_once libfile('function/cache');
	updatecache('blockclass');
	return $blockmapping;
}

function getobjbyname($name, $data) {
	if (!$name || !$data) return false;

	foreach ((array)$data as $id => $content) {
		list($type, $curname) = explode('`', $id);
		if ($curname == $name) {
			return array('type'=>$type,'content'=>$content);
		} elseif ($type == 'frame' || $type == 'tab' || $type == 'column') {
			$r = getobjbyname($name, $content);
			if ($r) return $r;
		}
	}
	return false;
}

function getframeblock($data) {
	global $_G;

	if (!isset($_G['curtplbid'])) $_G['curtplbid'] = array();
	if (!isset($_G['curtplframe'])) $_G['curtplframe'] = array();

	foreach ((array)$data as $id => $content) {
		list($flag, $name) = explode('`', $id);
		if ($flag == 'frame' || $flag == 'tab') {
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				if ($colflag == 'column') {
					getframeblock($coldata,$framename);
				}
			}
			$_G['curtplframe'][$name] = array('type'=>$flag,'name'=>$name);
		} elseif ($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if (!empty($bid)) {
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}
}

function getcssdata($css) {
	global $_G;
	if (empty($css)) return '';
	$reglist = array();
	foreach ((array)$_G['curtplframe'] as $value) {
		$reglist[] = '#'.$value['name'].'.*?\{.*?\}';
	}
	foreach ((array)$_G['curtplbid'] as $value) {
		$reglist[] = '#portal_block_'.$value.'.*?\{.*?\}';
	}
	$reg = implode('|',$reglist);
	preg_match_all('/'.$reg.'/',$css,$csslist);
	return implode('', $csslist[0]);
}

function import_diy($file) {
	global $_G;

	$css = '';
	$html = array();
	$arr = array();

	$content = file_get_contents($file);
	require_once libfile('class/xml');
	if (empty($content)) return $arr;
	$content = preg_replace("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>\s+/i", '', $content);
	$diycontent = xml2array($content);

	if ($diycontent) {

		foreach ($diycontent['layoutdata'] as $key => $value) {
			if (!empty($value)) getframeblock($value);
		}
		$newframe = array();
		foreach ($_G['curtplframe'] as $value) {
			$newframe[] = $value['type'].random(6);
		}

		$mapping = array();
		if (!empty($diycontent['blockdata'])) {
			$mapping = block_import($diycontent['blockdata']);
			unset($diycontent['blockdata']);
		}

		$oldbids = $newbids = array();
		if (!empty($mapping)) {
			foreach($mapping as $obid=>$nbid) {
				$oldbids[] = '#portal_block_'.$obid.' ';
				$newbids[] = '#portal_block_'.$nbid.' ';
				$oldbids[] = '[portal_block_'.$obid.']';
				$newbids[] = '[portal_block_'.$nbid.']';
				$oldbids[] = '~portal_block_'.$obid.'"';
				$newbids[] = '~portal_block_'.$nbid.'"';
			}
		}

		require_once libfile('class/xml');
		$xml = array2xml($diycontent['layoutdata'],true);
		$xml = str_replace($oldbids, $newbids, $xml);
		$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
		$diycontent['layoutdata'] = xml2array($xml);

		$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
		$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);
		foreach ($diycontent['layoutdata'] as $key => $value) {
			$html[$key] = getframehtml($value);
		}
	}
	if (!empty($html)) {
		$xml = array2xml($html, true);
		require_once libfile('function/block');
		block_get_batch(implode(',', $mapping));
		foreach ($mapping as $bid) {
			$blocktag[] = '<!--{block/'.$bid.'}-->';
			$blockcontent[] = block_fetch_content($bid);
		}
		$xml = str_replace($blocktag,$blockcontent,$xml);
		$html = xml2array($xml);
		$arr = array('html'=>$html,'css'=>$css,'mapping'=>$mapping);
	}
	return $arr;
}

function checkprimaltpl($template) {
	global $_G;
	if(!$template || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $template)) {
		return 'diy_template_filename_invalid';
	}
	$primaltplname = DISCUZ_ROOT.$_G['cache']['style_default']['tpldir'].'/'.$template.'.htm';
	if (!file_exists($primaltplname)) {
		$primaltplname = DISCUZ_ROOT.'./template/default/'.$template.'.htm';
	}
	$pathinfos = pathinfo($primaltplname);
	if(strtolower($pathinfos['extension']) != 'htm') {
		return 'diy_template_extension_invalid';
	}
	if (!is_file($primaltplname)) {
		return 'diy_template_noexist';
	}
	return true;
}

function article_tagnames() {
	global $_G;
	if(!isset($_G['article_tagnames'])) {
		$_G['article_tagnames'] = array();
		for($i=1; $i<=8; $i++) {
			if(isset($_G['setting']['article_tags']) && isset($_G['setting']['article_tags'][$i])) {
				$_G['article_tagnames'][$i] = $_G['setting']['article_tags'][$i];
			} else {
				$_G['article_tagnames'][$i] = lang('portalcp', 'article_tag').$i;
			}
		}
	}
	return $_G['article_tagnames'];
}

function article_parse_tags($tag) {
	$tag = intval($tag);
	$article_tags = array();
	for($i=1; $i<=8; $i++) {
		$k = pow(2, $i-1);
		$article_tags[$i] = ($tag & $k) ? 1 : 0;
	}
	return $article_tags;
}

function article_make_tag($tags) {
	$tags = (array)$tags;
	$tag = 0;
	for($i=1; $i<=8; $i++) {
		if(!empty($tags[$i])) {
			$tag += pow(2, $i-1);
		}
	}
	return $tag;
}

function category_showselect($type, $name='catid', $shownull=true, $current='') {
	global $_G;
	if(! in_array($type, array('portal', 'blog', 'album'))) {
		return '';
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];

	$select = "<select id=\"$name\" name=\"$name\" class=\"ps vm\">";
	if($shownull) {
		$select .= '<option value="">'.lang('portalcp', 'select_category').'</option>';
	}
	foreach ($category as $value) {
		if($value['level'] == 0) {
			$selected = ($current && $current==$value['catid']) ? 'selected="selected"' : '';
			$select .= "<option value=\"$value[catid]\"$selected>$value[catname]</option>";
			if(!$value['children']) {
				continue;
			}
			foreach ($value['children'] as $catid) {
				$selected = ($current && $current==$catid) ? 'selected="selected"' : '';
				$select .= "<option value=\"{$category[$catid][catid]}\"$selected>-- {$category[$catid][catname]}</option>";
				if($category[$catid]['children']) {
					foreach ($category[$catid]['children'] as $catid2) {
						$selected = ($current && $current==$catid2) ? 'selected="selected"' : '';
						$select .= "<option value=\"{$category[$catid2][catid]}\"$selected>---- {$category[$catid2][catname]}</option>";
					}
				}
			}
		}
	}
	$select .= "</select>";
	return $select;
}

function category_get_childids($type, $catid, $depth=3) {
	global $_G;
	if(! in_array($type, array('portal', 'blog', 'album'))) {
		return array();
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];
	$catids = array();
	if(isset($category[$catid]) && !empty($category[$catid]['children']) && $depth) {
		$catids = $category[$catid]['children'];
		foreach($category[$catid]['children'] as $id) {
			$catids = array_merge($catids, category_get_childids($type, $id, $depth-1));
		}
	}
	return $catids;
}

function category_get_num($type, $catid) {
	global $_G;
	if(! in_array($type, array('portal', 'blog', 'album'))) {
		return array();
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];

	$numkey = $type == 'portal' ? 'articles' : 'num';
	if(! isset($_G[$type.'category_nums'])) {
		$_G[$type.'category_nums'] = array();
		$tables = array('portal'=>'portal_category', 'blog'=>'home_blog_category', 'album'=>'home_album_category');
		$query = DB::query("SELECT catid, $numkey FROM ".DB::table($tables[$type]));
		while($value=DB::fetch($query)) {
			$_G[$type.'category_nums'][$value['catid']] = intval($value[$numkey]);
		}
	}

	$nums = $_G[$type.'category_nums'];
	$num = intval($nums[$catid]);
	if($category[$catid]['children']) {
		foreach($category[$catid]['children'] as $id) {
			$num += category_get_num($type, $id);
		}
	}
	return $num;
}


function updatetopic($topic = ''){
	global $_G;

	$topicid = empty($topic) ? '' : $topic['topicid'];
	include_once libfile('function/home');
	$_POST['title'] = getstr(trim($_POST['title']), 255, 1, 1);
	$_POST['name'] = getstr(trim($_POST['name']), 255, 1, 1);
	$_POST['domain'] = getstr(trim($_POST['domain']), 255, 1, 1);
	if(empty($_POST['title'])) {
		return 'topic_title_cannot_be_empty';
	}
	if(empty($_POST['name'])) {
		$_POST['name'] = $_POST['title'];
	}
	if(!$topicid || $_POST['name'] != $topic['name']) {
		$value = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE name = '$_POST[name]' LIMIT 1");
		if($value) {
			return 'topic_name_duplicated';
		}
	}
	if($topicid && !empty($topic['domain'])) {
		require_once libfile('function/delete');
		deletedomain($topicid, 'topic');
	}
	if(!empty($_POST['domain'])) {
		require_once libfile('function/domain');
		domaincheck($_POST['domain'], $_G['setting']['domain']['root']['topic'], 1);
	}

	$setarr = array(
		'title' => $_POST['title'],
		'name' => $_POST['name'],
		'domain' => $_POST['domain'],
		'summary' => getstr($_POST['summary'], '', 1, 1),
		'keyword' => getstr($_POST['keyword'], '', 1, 1),
		'useheader' => $_POST['useheader'] ? '1' : '0',
		'usefooter' => $_POST['usefooter'] ? '1' : '0',
		'allowcomment' => $_POST['allowcomment'] ? 1 : 0,
		'closed' => $_POST['closed'] ? 0 : 1,
	);

	if($_POST['deletecover'] && $topic['cover']) {
		if($topic['picflag'] != '0') pic_delete(str_replace('portal/', '', $topic['cover']), 'portal', 0, $topic['picflag'] == '2' ? '1' : '0');
		$setarr['cover'] = '';
	} else {
		if($_FILES['cover']['tmp_name']) {
			if($topic['cover'] && $topic['picflag'] != '0') pic_delete(str_replace('portal/', '', $topic['cover']), 'portal', 0, $topic['picflag'] == '2' ? '1' : '0');
			$pic = pic_upload($_FILES['cover'], 'portal');
			if($pic) {
				$setarr['cover'] = 'portal/'.$pic['pic'];
				$setarr['picflag'] = $pic['remote'] ? '2' : '1';
			}
		} else {
			if(!empty($_POST['cover']) && $_POST['cover'] != $topic['cover']) {
				if($topic['cover'] && $topic['picflag'] != '0') pic_delete(str_replace('portal/', '', $topic['cover']), 'portal', 0, $topic['picflag'] == '2' ? '1' : '0');
				$setarr['cover'] = $_POST['cover'];
				$setarr['picflag'] = '0';
			}
		}
	}


	$primaltplname = '';
	if(empty($topicid) || empty($topic['primaltplname']) || ($topic['primaltplname'] && $topic['primaltplname'] != 'portal/'.$_POST['primaltplname'])) {
		$primaltplname = 'portal/'.$_POST['primaltplname'];
		$checktpl = checkprimaltpl($primaltplname);
		if($checktpl !== true) {
			return $checktpl;
		}
		$setarr['primaltplname'] = $primaltplname;
	}

	if($topicid) {
		DB::update('portal_topic', $setarr, array('topicid'=>$topicid));
		DB::update('common_diy_data', array('name'=>$setarr['title']), array('targettplname'=>'portal/portal_topic_content_'.$topicid));
	} else {
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['dateline'] = $_G['timestamp'];
		$setarr['closed'] = '1';
		$topicid = addtopic($setarr);
		if(!$topicid) {
			return 'topic_created_failed';
		}

	}

	if(!empty($_POST['domain'])) {
		DB::insert('common_domain', array('domain' => $_POST['domain'], 'domainroot' => addslashes($_G['setting']['domain']['root']['topic']), 'id' => $topicid, 'idtype' => 'topic'));
	}

	if($topic['primaltplname'] != $primaltplname) {
		$targettplname = 'portal/portal_topic_content_'.$topicid;
		DB::update('common_diy_data',array('primaltplname'=>$primaltplname),array('targettplname'=>$targettplname));
		updatediytemplate($targettplname);
	}

	if($primaltplname && empty($topic['primaltplname'])) {
		$content = file_get_contents(DISCUZ_ROOT.'./template/default/'.$primaltplname.'.htm');
		$tplfile = DISCUZ_ROOT.'./data/diy/portal/portal_topic_content_'.$topicid.'.htm';
		$tplpath = dirname($tplfile);
		if (!is_dir($tplpath)) dmkdir($tplpath);
		file_put_contents($tplfile, $content);
	}

	include_once libfile('function/cache');
	updatecache(array('diytemplatename', 'setting'));

	return $topicid;
}

function addtopic($topic) {
	global $_G;
	$topicid = '';
	if($topic && is_array($topic)) {
		$topicid = DB::insert('portal_topic', $topic, true);
		if(!empty($topicid)) {
			$diydata = array(
				'targettplname' => 'portal/portal_topic_content_'.$topicid,
				'name' => $topic['title'],
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => TIMESTAMP,
			);
			DB::insert('common_diy_data', $diydata);
		}
	}
	return $topicid;
}

function getblockperm($bid) {
	global $_G;
	$perm = array('allowmanage'=>'0','allowrecommend'=>'0','needverify'=>'1');
	$bid = max(0, intval($bid));
	if(!$bid) return $perm;
	$allperm = array('allowmanage'=>'1','allowrecommend'=>'1','needverify'=>'0');
	if(checkperm('allowdiy')) {
		return $allperm;
	} elseif (!getstatus($_G['member']['allowadmincp'], 4) && !getstatus($_G['member']['allowadmincp'], 5) && !getstatus($_G['member']['allowadmincp'], 6) && !checkperm('allowmanagetopic') && !checkperm('allowaddtopic')) {
		return $perm;
	}
	require_once libfile('class/blockpermission');
	$blockpermsission = & block_permission::instance();
	$perm = $blockpermsission->get_perms_by_bid($bid, $_G['uid']);
	$perm = $perm ? $perm[0] : '';
	if(empty($perm)) {
		$block = DB::fetch_first('SELECT tb.*,b.blocktype,b.uid FROM '.DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid WHERE b.bid = '$bid'");
		if(empty($block['targettplname']) && empty($block['blocktype'])) {
			if(($_G['group']['allowmanagetopic'] || ($_G['group']['allowaddtopic'] && $block['uid'] == $_G['uid']))) {
				$perm = $allperm;
			}
		} elseif(substr($block['targettplname'], 0, 28) == 'portal/portal_topic_content_') {
			if(!empty($_G['group']['allowmanagetopic'])) {
				$perm = $allperm;
			} elseif($_G['group']['allowaddtopic']) {
				$id = str_replace('portal/portal_topic_content_', '', $block['targettplname']);
				$topic = DB::fetch_first('SELECT uid FROM '.DB::table('portal_topic')." WHERE topicid='".intval($id)."'");
				if($topic['uid'] == $_G['uid']) {
					$perm = $allperm;
				}
			}
		}
	}
	return $perm;
}

function check_articleperm($catid, $aid = 0, $article = array(), $isverify = false, $return = false) {
	global $_G;

	if(empty($catid) && empty($aid)) {
		if(!$return) {
			showmessage('article_category_empty');
		} else {
			return 'article_category_empty';
		}
	}

	if($_G['group']['allowmanagearticle'] || (empty($aid) && $_G['group']['allowpostarticle']) || $_G['gp_modarticlekey'] == modauthkey($aid)) {
		return true;
	}

	$permission = getallowcategory($_G['uid']);
	if(isset($permission[$catid])) {
		if($permission[$catid]['allowmanage'] || (empty($aid) && $permission[$catid]['allowpublish'])) {
			return true;
		}
	}
	if(!$isverify && $aid && !empty($article['uid']) && $article['uid'] == $_G['uid'] && ($article['status'] == 1 && $_G['group']['allowpostarticlemod'] || empty($_G['group']['allowpostarticlemod']))) {
		return true;
	}

	if(!$return) {
		showmessage('article_edit_nopermission');
	} else {
		return 'article_edit_nopermission';
	}
}

function getportalarticletplname($catid, $primaltplname = ''){
	global $_G;
	loadcache('portalcategory');
	$oldcatid = $catid;
	$portalcategory = $_G['cache']['portalcategory'];
	while(!empty($catid)) {
		if(!empty($portalcategory[$catid]['articleprimaltplname'])) {
			$primaltplname = $portalcategory[$catid]['articleprimaltplname'];
			break;
		} else {
			$catid = $portalcategory[$catid]['upid'];
		}
	}
	$catid = empty($catid) ? $oldcatid : $catid;
	return array($catid, $primaltplname);
}

function addportalarticlecomment($id, $message, $idtype = 'aid') {
	global $_G;

	$id = intval($id);
	if(empty($id)) {
		return 'comment_comment_noexist';
	}
	$message = getstr($message, $_G['group']['allowcommentarticle'], 1, 1, 1, 0);
	if(strlen($message) < 2) return 'content_is_too_short';

	$idtype = in_array($idtype, array('aid' ,'topicid')) ? $idtype : 'aid';
	$tablename = $idtype == 'aid' ? 'portal_article_title' : 'portal_topic';
	$data = DB::fetch_first("SELECT uid,allowcomment FROM ".DB::table($tablename)." WHERE $idtype='$id'");
	if(empty($data)) {
		return 'comment_comment_noexist';
	}
	if($data['allowcomment'] != 1) {
		return 'comment_comment_notallowed';
	}

	$message = censor($message);
	if(censormod($message)) {
		$comment_status = 1;
	} else {
		$comment_status = 0;
	}

	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'id' => $id,
		'idtype' => $idtype,
		'postip' => $_G['onlineip'],
		'dateline' => $_G['timestamp'],
		'status' => $comment_status,
		'message' => $message
	);

	$pcid = DB::insert('portal_comment', $setarr, true);

	if($comment_status == 1) {
		updatemoderate($idtype.'_cid', $pcid);
		$notifykey = $idtype == 'aid' ? 'verifyacommont' : 'verifytopiccommont';
		manage_addnotify($notifykey);
	}
	$tablename = $idtype == 'aid' ? 'portal_article_count' : 'portal_topic';
	DB::query("UPDATE ".DB::table($tablename)." SET commentnum=commentnum+1 WHERE $idtype='$id'");
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));

	if($data['uid'] != $_G['uid']) {
		updatecreditbyaction('portalcomment', 0, array(), $idtype.$id);
	}
	return 'do_success';
}

function trimdxtpllang($s){
	return str_replace(array('{', '$', '<', '>'), array('{ ', '$ ', '', ''), $s);
}
?>