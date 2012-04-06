<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_attachment.php 23304 2011-07-04 10:04:10Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function attachtype($type, $returnval = 'html') {

	static $attachicons = array(
			1 => 'unknown.gif',
			2 => 'binary.gif',
			3 => 'zip.gif',
			4 => 'rar.gif',
			5 => 'msoffice.gif',
			6 => 'text.gif',
			7 => 'html.gif',
			8 => 'real.gif',
			9 => 'av.gif',
			10 => 'flash.gif',
			11 => 'image.gif',
			12 => 'pdf.gif',
			13 => 'torrent.gif'
		);

	if(is_numeric($type)) {
		$typeid = $type;
	} else {
		if(preg_match("/bittorrent|^torrent\t/", $type)) {
			$typeid = 13;
		} elseif(preg_match("/pdf|^pdf\t/", $type)) {
			$typeid = 12;
		} elseif(preg_match("/image|^(jpg|gif|png|bmp)\t/", $type)) {
			$typeid = 11;
		} elseif(preg_match("/flash|^(swf|fla|flv|swi)\t/", $type)) {
			$typeid = 10;
		} elseif(preg_match("/audio|video|^(wav|mid|mp3|m3u|wma|asf|asx|vqf|mpg|mpeg|avi|wmv)\t/", $type)) {
			$typeid = 9;
		} elseif(preg_match("/real|^(ra|rm|rv)\t/", $type)) {
			$typeid = 8;
		} elseif(preg_match("/htm|^(php|js|pl|cgi|asp)\t/", $type)) {
			$typeid = 7;
		} elseif(preg_match("/text|^(txt|rtf|wri|chm)\t/", $type)) {
			$typeid = 6;
		} elseif(preg_match("/word|powerpoint|^(doc|ppt)\t/", $type)) {
			$typeid = 5;
		} elseif(preg_match("/^rar\t/", $type)) {
			$typeid = 4;
		} elseif(preg_match("/compressed|^(zip|arj|arc|cab|lzh|lha|tar|gz)\t/", $type)) {
			$typeid = 3;
		} elseif(preg_match("/octet-stream|^(exe|com|bat|dll)\t/", $type)) {
			$typeid = 2;
		} elseif($type) {
			$typeid = 1;
		} else {
			$typeid = 0;
		}
	}
	if($returnval == 'html') {
		return '<img src="'.STATICURL.'image/filetype/'.$attachicons[$typeid].'" border="0" class="vm" alt="" />';
	} elseif($returnval == 'id') {
		return $typeid;
	}
}

function parseattach($attachpids, $attachtags, &$postlist, $skipaids = array()) {
	global $_G;

	$query = DB::query("SELECT * FROM ".DB::table(getattachtablebytid($_G['tid']))." a WHERE a.pid IN ($attachpids)");
	$attachexists = FALSE;
	$skipattachcode = $aids = $payaids = $findattach = array();
	while($attach = DB::fetch($query)) {
		$attachexists = TRUE;
		if($skipaids && in_array($attach['aid'], $skipaids)) {
			$skipattachcode[$attach[pid]][] = "/\[attach\]$attach[aid]\[\/attach\]/i";
			continue;
		}
		$attached = 0;
		$extension = strtolower(fileext($attach['filename']));
		$attach['ext'] = $extension;
		$attach['imgalt'] = $attach['isimage'] ? strip_tags(str_replace('"', '\"', $attach['description'] ? $attach['description'] : $attach['filename'])) : '';
		$attach['attachicon'] = attachtype($extension."\t".$attach['filetype']);
		$attach['attachsize'] = sizecount($attach['filesize']);
		if($attach['isimage'] && !$_G['setting']['attachimgpost']) {
			$attach['isimage'] = 0;
		}
		$attach['attachimg'] = $attach['isimage'] && (!$attach['readperm'] || $_G['group']['readaccess'] >= $attach['readperm']) ? 1 : 0;
		if($attach['attachimg']) {
			$GLOBALS['aimgs'][$attach['pid']][] = $attach['aid'];
		}
		if($attach['price']) {
			if($_G['setting']['maxchargespan'] && TIMESTAMP - $attach['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
				DB::query("UPDATE ".DB::table(getattachtablebytid($_G['tid']))." SET price='0' WHERE aid='$attach[aid]'");
				$attach['price'] = 0;
			} elseif(!$_G['forum_attachmentdown'] && $_G['uid'] != $attach['uid']) {
				$payaids[$attach['aid']] = $attach['pid'];
			}
		}
		$attach['payed'] = $_G['forum_attachmentdown'] || $_G['uid'] == $attach['uid'] ? 1 : 0;
		$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
		$attach['dateline'] = dgmdate($attach['dateline'], 'u');
		$postlist[$attach['pid']]['attachments'][$attach['aid']] = $attach;
		if(!empty($attachtags[$attach['pid']]) && is_array($attachtags[$attach['pid']]) && in_array($attach['aid'], $attachtags[$attach['pid']])) {
			$findattach[$attach['pid']][$attach['aid']] = "/\[attach\]$attach[aid]\[\/attach\]/i";
			$attached = 1;
		}

		if(!$attached) {
			if($attach['isimage']) {
				$postlist[$attach['pid']]['imagelist'][] = $attach['aid'];
				$postlist[$attach['pid']]['imagelistcount']++;
				if($postlist[$attach['pid']]['first']) {
					$GLOBALS['firstimgs'][] = $attach['aid'];
				}
			} else {
				if(!$_G['forum_skipaidlist'] || !in_array($attach['aid'], $_G['forum_skipaidlist'])) {
					$postlist[$attach['pid']]['attachlist'][] = $attach['aid'];
				}
			}
		}
		$aids[] = $attach['aid'];
	}
	if($aids) {
		$query = DB::query("SELECT aid, pid, downloads FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($aids).")");
		while($attach = DB::fetch($query)) {
			$postlist[$attach['pid']]['attachments'][$attach['aid']]['downloads'] = $attach['downloads'];
		}
	}
	if($payaids) {
		$query = DB::query("SELECT relatedid FROM ".DB::table('common_credit_log')." WHERE relatedid IN (".dimplode(array_keys($payaids)).") AND uid='$_G[uid]' AND operation='BAC'");
		while($creditlog = DB::fetch($query)) {
			$postlist[$payaids[$creditlog['relatedid']]]['attachments'][$creditlog['relatedid']]['payed'] = 1;
		}
	}
	if(!empty($skipattachcode)) {
		foreach($skipattachcode as $pid => $findskipattach) {
			foreach($findskipattach as $findskip) {
				$postlist[$pid]['message'] = preg_replace($findskip, '', $postlist[$pid]['message']);
			}
		}
	}

	if($attachexists) {
		foreach($attachtags as $pid => $aids) {
			if($findattach[$pid]) {
				foreach($findattach[$pid] as $aid => $find) {
					$postlist[$pid]['message'] = preg_replace($find, attachinpost($postlist[$pid]['attachments'][$aid], $postlist[$pid]['first']), $postlist[$pid]['message'], 1);
					$postlist[$pid]['message'] = preg_replace($find, '', $postlist[$pid]['message']);
				}
			}
		}
	} else {
		updatepost(array('attachment' => '0'), "pid IN ($attachpids)", true);
	}
}

function attachwidth($width) {
	global $_G;
	if($_G['setting']['imagemaxwidth'] && $width) {
		return 'class="zoom" onclick="zoom(this, this.src)" width="'.($width > $_G['setting']['imagemaxwidth'] ? $_G['setting']['imagemaxwidth'] : $width).'"';
	} else {
		return 'thumbImg="1"';
	}
}

function packaids($attach) {
	global $_G;
	return aidencode($attach['aid'], 0, $_G['tid']);
}

function showattach($post, $type = 0) {
	$type = !$type ? 'attachlist' : 'imagelist';
	$return = '';
	if(!empty($post[$type]) && is_array($post[$type])) {
		foreach($post[$type] as $aid) {
			if(!empty($post['attachments'][$aid])) {
				$return .= $type($post['attachments'][$aid], $post['first']);
			}
		}
	}
	return $return;
}

?>