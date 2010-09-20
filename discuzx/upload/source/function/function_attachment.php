<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_attachment.php 16751 2010-09-14 05:16:45Z monkey $
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

	$query = DB::query("SELECT a.*, af.description, l.relatedid AS payed
		FROM ".DB::table('forum_attachment')." a
		LEFT JOIN ".DB::table('forum_attachmentfield')." af ON a.aid=af.aid
		LEFT JOIN ".DB::table('common_credit_log')." l ON l.relatedid=a.aid AND l.uid='$_G[uid]' AND l.operation='BAC'
		WHERE a.pid IN ($attachpids)");

	$attachexists = FALSE;
	$skipattachcode = array();
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
		$attach['attachimg'] = $_G['setting']['attachimgpost'] && $attach['isimage'] && (!$attach['readperm'] || $_G['group']['readaccess'] >= $attach['readperm']) ? 1 : 0;
		if($attach['price']) {
			if($_G['setting']['maxchargespan'] && TIMESTAMP - $attach['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
				DB::query("UPDATE ".DB::table('forum_attachment')." SET price='0' WHERE aid='$attach[aid]'");
				$attach['price'] = 0;
			} else {
				if(!$_G['uid'] || (!$_G['forum']['ismoderator'] && $attach['uid'] != $_G['uid'] && !$attach['payed'])) {
					$attach['unpayed'] = 1;
				}
			}
		}

		$attach['payed'] = $attach['payed'] || $_G['forum_attachmentdown'] || $_G['uid'] == $attach['uid'] ? 1 : 0;
		$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
		$attach['dateline'] = dgmdate($attach['dateline'], 'u');
		$postlist[$attach['pid']]['attachments'][$attach['aid']] = $attach;
		if(!empty($attachtags[$attach['pid']]) && is_array($attachtags[$attach['pid']]) && in_array($attach['aid'], $attachtags[$attach['pid']])) {
			$findattach[$attach['pid']][] = "/\[attach\]$attach[aid]\[\/attach\]/i";
			$replaceattach[$attach['pid']][] = attachtag($attach['pid'], $attach['aid'], $postlist);
			$attached = 1;
		}

		if(!$attached) {
			if($attach['isimage']) {
				$postlist[$attach['pid']]['imagelist'] .= attachlist($attach);
			} else {
				if(!$_G['forum_skipaidlist'] || !in_array($attach['aid'], $_G['forum_skipaidlist'])) {
					$postlist[$attach['pid']]['attachlist'] .= attachlist($attach);
				}
			}
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
				$postlist[$pid]['message'] = preg_replace($findattach[$pid], $replaceattach[$pid], $postlist[$pid]['message'], 1);
				$postlist[$pid]['message'] = preg_replace($findattach[$pid], '', $postlist[$pid]['message']);
			}
		}
	} else {
		updatepost(array('attachment' => '0'), "pid IN ($attachpids)", true);
	}
}

function attachwidth($width) {
	global $_G;
	if($_G['setting']['imagemaxwidth'] && $width) {
		return 'width="'.($width > $_G['setting']['imagemaxwidth'] ? $_G['setting']['imagemaxwidth'].'" class="zoom" onclick="zoom(this, this.src)"' : $width.'"');
	} else {
		return 'thumbImg="1"';
	}
}

?>