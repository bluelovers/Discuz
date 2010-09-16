<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_discuzcode.php 12218 2010-07-01 05:40:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include template('forum/discuzcode');

$_G['forum_discuzcode'] = array(
	'pcodecount' => -1,
	'codecount' => 0,
	'codehtml' => '',
	'smiliesreplaced' => 0,
	'seoarray' => array(
		0 => '',
		1 => $_SERVER['HTTP_HOST'],
		2 => $_G['setting']['bbname'],
		3 => $_G['setting']['seotitle'],
		4 => $_G['setting']['seokeywords'],
		5 => $_G['setting']['seodescription']
	)
);
$authorreplyexist = '';

if(!isset($_G['cache']['bbcodes']) || !is_array($_G['cache']['bbcodes']) || !is_array($_G['cache']['smilies'])) {
	loadcache(array('bbcodes', 'smilies', 'smileytypes'));
}

function attachtag($pid, $aid, &$postlist) {
	return attachinpost($postlist[$pid]['attachments'][$aid]);
}

function censormod($message) {
	require_once libfile('class/censor');
	$censor = discuz_censor::instance();
	$censor->check($message);
	return $censor->modmoderated();
}

function creditshide($creditsrequire, $message, $pid) {
	global $_G;

	if($_G['member']['credits'] >= $creditsrequire || $_G['forum']['ismoderator']) {
		return tpl_hide_credits($creditsrequire, str_replace('\\"', '"', $message));
	} else {
		return tpl_hide_credits_hidden($creditsrequire);
	}
}

function codedisp($code) {
	global $_G;
	$_G['forum_discuzcode']['pcodecount']++;
	$code = dhtmlspecialchars(str_replace('\\"', '"', preg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", "\\1", $code)));
	$code = str_replace("\n", "<li>", $code);
	$_G['forum_discuzcode']['codehtml'][$_G['forum_discuzcode']['pcodecount']] = tpl_codedisp($_G['forum_discuzcode'], $code);
	$_G['forum_discuzcode']['codecount']++;
	return "[\tDISCUZ_CODE_".$_G['forum_discuzcode']['pcodecount']."\t]";
}

function karmaimg($rate, $ratetimes) {
	$karmaimg = '';
	if($rate && $ratetimes) {
		$image = $rate > 0 ? 'agree.gif' : 'disagree.gif';
		for($i = 0; $i < ceil(abs($rate) / $ratetimes); $i++) {
			$karmaimg .= '<img src="'.IMGDIR.'/'.$image.'" border="0" alt="" />';
		}
	}
	return $karmaimg;
}

function discuzcode($message, $smileyoff, $bbcodeoff, $htmlon = 0, $allowsmilies = 1, $allowbbcode = 1, $allowimgcode = 1, $allowhtml = 0, $jammer = 0, $parsetype = '0', $authorid = '0', $allowmediacode = '0', $pid = 0) {
	global $_G;

	static $authorreplyexist;

	if($parsetype != 1 && !$bbcodeoff && $allowbbcode && (strpos($message, '[/code]') || strpos($message, '[/CODE]')) !== FALSE) {
		$message = preg_replace("/\s?\[code\](.+?)\[\/code\]\s?/ies", "codedisp('\\1')", $message);
	}

	$msglower = strtolower($message);

	$htmlon = $htmlon && $allowhtml ? 1 : 0;

	if(!$htmlon) {
		$message = dhtmlspecialchars($message);
	}

	if(!$smileyoff && $allowsmilies) {
		$message = parsesmiles($message);
	}

	if($_G['setting']['allowattachurl'] && strpos($msglower, 'attach://') !== FALSE) {
		$message = preg_replace("/attach:\/\/(\d+)\.?(\w*)/ie", "parseattachurl('\\1', '\\2')", $message);
	}

	if($allowbbcode) {
		if(strpos($msglower, 'ed2k://') !== FALSE) {
			$message = preg_replace("/ed2k:\/\/(.+?)\//e", "parseed2k('\\1')", $message);
		}
	}

	if(!$bbcodeoff && $allowbbcode) {
		if(strpos($msglower, '[/url]') !== FALSE) {
			$message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|synacast){1}:\/\/|www\.|mailto:)?([^\s\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5')", $message);
		}
		if(strpos($msglower, '[/email]') !== FALSE) {
			$message = preg_replace("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/ies", "parseemail('\\1', '\\4')", $message);
		}

		$nest = 0;
		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
			$message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies", "parsetable('\\1', '\\2', '\\3')", $message);
			if(++$nest > 4) break;
		}

		$message = str_replace(array(
			'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
			'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
			'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
			), array(
			'</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
			'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
			'<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
			), preg_replace(array(
			"/\[color=([#\w]+?)\]/i",
			"/\[color=(rgb\([\d\s,]+?\))\]/i",
			"/\[size=(\d{1,2}?)\]/i",
			"/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
			"/\[font=([^\[\<]+?)\]/i",
			"/\[align=(left|center|right)\]/i",
			"/\[p=(\d{1,2}), (\d{1,2}), (left|center|right)\]/i",
			"/\[float=(left|right)\]/i"

			), array(
			"<font color=\"\\1\">",
			"<font style=\"color:\\1\">",
			"<font size=\"\\1\">",
			"<font style=\"font-size: \\1\">",
			"<font face=\"\\1 \">",
			"<p align=\"\\1\">",
			"<p style=\"line-height: \\1px; text-indent: \\2em; text-align: \\3;\">",
			"<span style=\"float: \\1;\">"
			), $message));

		if($parsetype != 1) {
			if(strpos($msglower, '[/quote]') !== FALSE) {
				$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", tpl_quote(), $message);
			}
			if(strpos($msglower, '[/free]') !== FALSE) {
				$message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", tpl_free(), $message);
			}
		}
		if(strpos($msglower, '[/media]') !== FALSE) {
			$message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", $allowmediacode ? "parsemedia('\\1', '\\2')" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
		}
		if(strpos($msglower, '[/audio]') !== FALSE) {
			$message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", $allowmediacode ? "parseaudio('\\2', 400, '\\1')" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
		}
		if(strpos($msglower, '[/flash]') !== FALSE) {
			$message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", $allowmediacode ?
				"parseflash('\\2', '\\3', '\\4');" : "bbcodeurl('\\4', '<a href=\"{url}\" target=\"_blank\">{url}</a>')",
				$message);
		}
		if($parsetype != 1 && $allowbbcode == 2 && $_G['cache']['bbcodes']) {
			$message = preg_replace($_G['cache']['bbcodes']['searcharray'], $_G['cache']['bbcodes']['replacearray'], $message);
		}
		if($parsetype != 1 && strpos($msglower, '[/hide]') !== FALSE && $pid) {
			if(strpos($msglower, '[hide]') !== FALSE) {
				if($authorreplyexist === null) {
					$posttable = getposttablebytid($_G['tid']);
					$authorreplyexist = !$_G['forum']['ismoderator'] ? DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND ".($_G['uid'] ? "authorid='$_G[uid]'" : "authorid=0 AND useip='$_G[clientip]'")." LIMIT 1") : TRUE;
				}
				if($authorreplyexist) {
					$message = preg_replace("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", tpl_hide_reply(), $message);
				} else {
					$message = preg_replace("/\[hide\](.+?)\[\/hide\]/is", tpl_hide_reply_hidden(), $message);
					$message .= '<script type="text/javascript">replyreload += \',\' + '.$pid.';</script>';
				}
			}
			if(strpos($msglower, '[hide=') !== FALSE) {
				$message = preg_replace("/\[hide=(\d+)\]\s*(.+?)\s*\[\/hide\]/ies", "creditshide(\\1,'\\2', $pid)", $message);
			}
		}
	}

	if(!$bbcodeoff) {
		if($parsetype != 1 && strpos($msglower, '[swf]') !== FALSE) {
			$message = preg_replace("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies", "bbcodeurl('\\1', ' <img src=\"'.STATICURL.'image/filetype/flash.gif\" align=\"absmiddle\" alt=\"\" /> <a href=\"{url}\" target=\"_blank\">Flash: {url}</a> ')", $message);
		}
		if(strpos($msglower, '[/img]') !== FALSE) {
			$message = preg_replace(array(
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
				"/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
			), $allowimgcode ? array(
				"bbcodeurl('\\1', '<img src=\"{url}\" onload=\"thumbImg(this)\" alt=\"\" />')",
				"parseimg('\\1', '\\2', '\\3')"
			) : array(
				"bbcodeurl('\\1', '<a href=\"{url}\" target=\"_blank\">{url}</a>')",
				"bbcodeurl('\\3', '<a href=\"{url}\" target=\"_blank\">{url}</a>')"
			), $message);
		}
	}

	for($i = 0; $i <= $_G['forum_discuzcode']['pcodecount']; $i++) {
		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $_G['forum_discuzcode']['codehtml'][$i], $message);
	}

	if(!empty($_G['gp_highlight'])) {
		$highlightarray = explode('+', $_G['gp_highlight']);
		$sppos = strrpos($message, chr(0).chr(0).chr(0));
		if($sppos !== FALSE) {
			$specialextra = substr($message, $sppos + 3);
			$message = substr($message, 0, $sppos);
		}
		$message = preg_replace(array("/(^|>)([^<]+)(?=<|$)/sUe", "/<highlight>(.*)<\/highlight>/siU"), array("highlight('\\2', \$highlightarray, '\\1')", "<strong><font color=\"#FF0000\">\\1</font></strong>"), $message);
		if($sppos !== FALSE) {
			$message = $message.chr(0).chr(0).chr(0).$specialextra;
		}
	}

	unset($msglower);

	if($jammer) {
		$message = preg_replace("/\r\n|\n|\r/e", "jammer()", $message);
	}

	return $htmlon ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

function parseurl($url, $text) {
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}

function parseflash($w, $h, $url) {
	$w = !$w ? 550 : $w;
	$h = !$h ? 400 : $h;
	preg_match("/((https?){1}:\/\/|www\.)[^\[\"']+/i", $url, $matches);
	$url = $matches[0];
	return '<script type="text/javascript" reload="1">'.
		'document.write(AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$url.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\'));'.
		'</script>';
}

function parseed2k($url) {
	list(,$type, $name, $size,) = explode('|', $url);
	$url = 'ed2k://'.$url.'/';
	$name = str_replace("'", "\\'", $name);
	if($type == 'file') {
		return '<a href="'.$url.'" target="_blank"><script language="javascript">document.write(unescape(decodeURIComponent(\''.$name.'\')))</script> ('.sizecount($size).')</a>';
	} else {
		return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
	}
}

function parseattachurl($aid, $ext) {
	global $_G;
	$_G['forum_skipaidlist'][] = $aid;
	return $_G['siteurl'].'forum.php?mod=attachment&aid='.aidencode($aid).($ext ? '&request=yes&_f=.'.$ext : '');
}

function parseemail($email, $text) {
	if(!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
		$email = trim($matches[0]);
		return '<a href="mailto:'.$email.'">'.$email.'</a>';
	} else {
		return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
	}
}

function parsetable($width, $bgcolor, $message) {
	if(strpos($message, '[/tr]') === FALSE && strpos($message, '[/td]') === FALSE) {
		$rows = explode("\n", $message);
		$s = '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>');
		foreach($rows as $row) {
			$s .= '<tr><td>'.str_replace(array('\|', '|', '\n'), array('&#124;', '</td><td>', "\n"), $row).'</td></tr>';
		}
		$s .= '</table>';
		return $s;
	} else {
		if(!preg_match("/^\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td([=\d,%]+)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
			return str_replace('\\"', '"', preg_replace("/\[tr(?:=([\(\)\s%,#\w]+))?\]|\[td([=\d,%]+)?\]|\[\/td\]|\[\/tr\]/", '', $message));
		}
		if(substr($width, -1) == '%') {
			$width = substr($width, 0, -1) <= 98 ? intval($width).'%' : '98%';
		} else {
			$width = intval($width);
			$width = $width ? ($width <= 560 ? $width.'px' : '98%') : '';
		}
		return '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>').
			str_replace('\\"', '"', preg_replace(array(
					"/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,4}%?))?\]/ie",
					"/\[\/td\]\s*\[td(?:=(\d{1,4}%?))?\]/ie",
					"/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
					"/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
					"/\[\/td\]\s*\[\/tr\]\s*/i"
				), array(
					"parsetrtd('\\1', '0', '0', '\\2')",
					"parsetrtd('td', '0', '0', '\\1')",
					"parsetrtd('\\1', '\\2', '\\3', '\\4')",
					"parsetrtd('td', '\\1', '\\2', '\\3')",
					'</td></tr>'
				), $message)
			).'</table>';
	}
}

function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
	return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor ? ' style="background-color:'.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width ? ' width="'.$width.'"' : '').'>';
}

function parseaudio($url, $width = 400, $autostart = 0) {
	$autostart = $autostart !== '' ? 1 : 0;
	$ext = strtolower(substr(strrchr($url, '.'), 1, 5));
	switch($ext) {
		case 'mp3':
		case 'wma':
		case 'mid':
		case 'wav':
			return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="64"><param name="invokeURLs" value="0"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="application/x-mplayer2" width="'.$width.'" height="64"></embed></object>';
		case 'ra':
		case 'rm':
		case 'ram':
			$mediaid = 'media_'.random(3);
			return '<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="autostart" value="'.$autostart.'" /><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';
	}
}

function parsemedia($params, $url) {
	$params = explode(',', $params);
	$width = intval($params[1]) > 800 ? 800 : intval($params[1]);
	$height = intval($params[2]) > 600 ? 600 : intval($params[2]);
	$autostart = !empty($params[3]) ? 1 : 0;
	if($flv = parseflv($url, $width, $height)) {
		return $flv;
	}
	if(in_array(count($params), array(3, 4))) {
		$type = $params[0];
		$url = str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
		switch($type) {
			case 'mp3':
			case 'wma':
			case 'ra':
			case 'ram':
			case 'wav':
			case 'mid':
				return parseaudio($url, $width, $autostart);
			case 'rm':
			case 'rmvb':
			case 'rtsp':
				$mediaid = 'media_'.random(3);
				return '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.$autostart.'" /><param name="src" value="'.$url.'" /><param name="controls" value="imagewindow" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="imagewindow" console="'.$mediaid.'_" width="'.$width.'" height="'.$height.'"></embed></object><br /><object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="controlpanel" console="'.$mediaid.'_" width="'.$width.'" height="32"'.($autostart ? ' autostart="true"' : '').'></embed></object>';
			case 'flv':
				return '<script type="text/javascript" reload="1">document.write(AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.STATICURL.'image/common/flvplayer.swf\', \'flashvars\', \'file='.rawurlencode($url).'\', \'quality\', \'high\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\'));</script>';
			case 'swf':
				return '<script type="text/javascript" reload="1">document.write(AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$url.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\'));</script>';
			case 'asf':
			case 'asx':
			case 'wmv':
			case 'mms':
			case 'avi':
			case 'mpg':
			case 'mpeg':
				return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="'.$height.'"><param name="invokeURLs" value="0"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="application/x-mplayer2" width="'.$width.'" height="'.$height.'"></embed></object>';
			case 'mov':
				return '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.($autostart ? '' : 'false').'" /><param name="src" value="'.$url.'" /><embed src="'.$url.'" autostart="'.($autostart ? 'true' : 'false').'" type="video/quicktime" controller="true" width="'.$width.'" height="'.$height.'"></embed></object>';
			default:
				return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
	}
	return;
}

function bbcodeurl($url, $tags) {
	if(!preg_match("/<.+?>/s", $url)) {
		return str_replace(array('submit', 'member.php?mod=logging'), array('', ''), str_replace('{url}', addslashes($url), $tags));
	} else {
		return '&nbsp;'.$url;
	}
}

function jammer() {
	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(32, 59)).' '.chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<font style="font-size:10px;color:'.WRAPBG.'">'.$randomstr.'</font>'."\r\n" :
		"\r\n".'<span style="display:none">'.$randomstr.'</span>';
}

function highlight($text, $words, $prepend) {
	$text = str_replace('\"', '"', $text);
	foreach($words AS $key => $replaceword) {
		$text = str_replace($replaceword, '<highlight>'.$replaceword.'</highlight>', $text);
	}
	return "$prepend$text";
}

function parseflv($url, $width = 0, $height = 0) {
	$lowerurl = strtolower($url);
	$flv = '';
	$imgurl = '';
	if($lowerurl != str_replace(array('player.youku.com/player.php/sid/','tudou.com/v/','player.ku6.com/refer/'), '', $lowerurl)) {
		$flv = $url;
	} elseif(strpos($lowerurl, 'v.youku.com/v_show/') !== FALSE) {
		if(preg_match("/http:\/\/v.youku.com\/v_show\/id_([^\/]+)(.html|)/i", $url, $matches)) {
			$flv = 'http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf';
			if(!$width && !$height) {
				$api = 'http://v.youku.com/player/getPlayList/VideoIDS/'.$matches[1];
				$str = stripslashes(file_get_contents($api));
				if(!empty($str) && preg_match("/\"logo\":\"(.+?)\"/i", $str, $image)) {
					$url = substr($image[1], 0, strrpos($image[1], '/')+1);
					$filename = substr($image[1], strrpos($image[1], '/')+2);
					$imgurl = $url.'0'.$filename;
				}
			}
		}
	} elseif(strpos($lowerurl, 'tudou.com/programs/view/') !== FALSE) {
		if(preg_match("/http:\/\/(www.)?tudou.com\/programs\/view\/([^\/]+)/i", $url, $matches)) {
			$flv = 'http://www.tudou.com/v/'.$matches[2];
			if(!$width && !$height) {
				$str = file_get_contents($url);
				if(!empty($str) && preg_match("/<span class=\"s_pic\">(.+?)<\/span>/i", $str, $image)) {
					$imgurl = trim($image[1]);
				}
			}
		}
	} elseif(strpos($lowerurl, 'v.ku6.com/show/') !== FALSE) {
		if(preg_match("/http:\/\/v.ku6.com\/show\/([^\/]+).html/i", $url, $matches)) {
			$flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
			if(!$width && !$height) {
				$api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
				$str = file_get_contents($api);
				if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
					$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
				}
			}
		}
	} elseif(strpos($lowerurl, 'v.ku6.com/special/show_') !== FALSE) {
		if(preg_match("/http:\/\/v.ku6.com\/special\/show_\d+\/([^\/]+).html/i", $url, $matches)) {
			$flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
			if(!$width && !$height) {
				$api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
				$str = file_get_contents($api);
				if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
					$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
				}
			}
		}
	} elseif(strpos($lowerurl, 'www.youtube.com/watch?') !== FALSE) {
		if(preg_match("/http:\/\/www.youtube.com\/watch\?v=([^\/&]+)&?/i", $url, $matches)) {
			$flv = 'http://www.youtube.com/v/'.$matches[1].'&hl=zh_CN&fs=1';
			if(!$width && !$height) {
				$str = file_get_contents($url);
				if(!empty($str) && preg_match("/'VIDEO_HQ_THUMB':\s'(.+?)'/i", $str, $image)) {
					$url = substr($image[1], 0, strrpos($image[1], '/')+1);
					$filename = substr($image[1], strrpos($image[1], '/')+3);
					$imgurl = $url.$filename;
				}
			}
		}
	} elseif(strpos($lowerurl, 'tv.mofile.com/') !== FALSE) {
		if(preg_match("/http:\/\/tv.mofile.com\/([^\/]+)/i", $url, $matches)) {
			$flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
			if(!$width && !$height) {
				$str = file_get_contents($url);
				if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
					$imgurl = trim($image[1]);
				}
			}
		}
	} elseif(strpos($lowerurl, 'v.mofile.com/show/') !== FALSE) {
		if(preg_match("/http:\/\/v.mofile.com\/show\/([^\/]+).shtml/i", $url, $matches)) {
			$flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
			if(!$width && !$height) {
				$str = file_get_contents($url);
				if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
					$imgurl = trim($image[1]);
				}
			}
		}
	} elseif(strpos($lowerurl, 'you.video.sina.com.cn/b/') !== FALSE) {
		if(preg_match("/http:\/\/you.video.sina.com.cn\/b\/(\d+)-(\d+).html/i", $url, $matches)) {
			$flv = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid='.$matches[1];
			if(!$width && !$height) {
				$api = 'http://interface.video.sina.com.cn/interface/common/getVideoImage.php?vid='.$matches[1];
				$str = file_get_contents($api);
				if(!empty($str)) {
					$imgurl = str_replace('imgurl=', '', trim($str));
				}
			}
		}
	} elseif(strpos($lowerurl, 'http://v.blog.sohu.com/u/') !== FALSE) {
		if(preg_match("/http:\/\/v.blog.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
			$flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
			if(!$width && !$height) {
				$api = 'http://v.blog.sohu.com/videinfo.jhtml?m=view&id='.$matches[1].'&outType=3';
				$str = file_get_contents($api);
				if(!empty($str) && preg_match("/\"cutCoverURL\":\"(.+?)\"/i", $str, $image)) {
					$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
				}
			}
		}
	} elseif(strpos($lowerurl, 'http://www.ouou.com/fun_funview') !== FALSE) {
		$str = file_get_contents($url);
		if(!empty($str) && preg_match("/var\sflv\s=\s'(.+?)';/i", $str, $matches)) {
			$flv = IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($matches[1]);
			if(!$width && !$height && preg_match("/var\simga=\s'(.+?)';/i", $str, $image)) {
				$imgurl = trim($image[1]);
			}
		}
	} elseif(strpos($lowerurl, 'http://www.56.com') !== FALSE) {
		
		if(preg_match("/http:\/\/www.56.com\/\S+\/play_album-aid-(\d+)_vid-(.+?).html/i", $url, $matches)) {
			$flv = 'http://player.56.com/v_'.$matches[2].'.swf';
			$matches[1] = $matches[2];
		} elseif(preg_match("/http:\/\/www.56.com\/\S+\/([^\/]+).html/i", $url, $matches)) {
			$flv = 'http://player.56.com/'.$matches[1].'.swf';
		}
		if(!$width && !$height && !empty($matches[1])) {
			$api = 'http://vxml.56.com/json/'.str_replace('v_', '', $matches[1]).'/?src=out';
			$str = file_get_contents($api);
			if(!empty($str) && preg_match("/\"img\":\"(.+?)\"/i", $str, $image)) {
				$imgurl = trim($image[1]);
			}
		}
	}
	if($flv) {
		if(!$width && !$height) {
			return array('flv' => $flv, 'imgurl' => $imgurl);
		} else {
			return '<script type="text/javascript" reload="1">document.write(AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$flv.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\'));</script>';
		}
	} else {
		return FALSE;
	}
}

function parseimg($width, $height, $src) {
	$extra = '';
	if($width > IMAGEMAXWIDTH) {
		$height = intval(IMAGEMAXWIDTH * $height / $width);
		$width = IMAGEMAXWIDTH;
		$extra = ' onclick="zoom(this)" style="cursor:pointer"';
	}
	return bbcodeurl($src, '<img'.($width > 0 ? ' width="'.$width.'"' : '').($height > 0 ? ' height="'.$height.'"' : '').' src="'.$src.'"'.$extra.' border="0" alt="" />');
}

function parsesmiles(&$message) {
	global $_G;
	static $enablesmiles;
	if($enablesmiles === null) {
		$enablesmiles = false;
		if(!empty($_G['cache']['smilies']) && is_array($_G['cache']['smilies'])) {
			foreach($_G['cache']['smilies']['replacearray'] AS $key => $smiley) {
				$_G['cache']['smilies']['replacearray'][$key] = '<img src="'.STATICURL.'image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
			}
			$enablesmiles = true;
		}
	}
	$enablesmiles && $message = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], $message, $_G['setting']['maxsmilies']);
	return $message;
}

function bat_highlight($message, $words, $color = '#ff0000') {
	if(!empty($words)) {
		$highlightarray = explode('+', $words);
		$sppos = strrpos($message, chr(0).chr(0).chr(0));
		if($sppos !== FALSE) {
			$specialextra = substr($message, $sppos + 3);
			$message = substr($message, 0, $sppos);
		}
		$message = preg_replace(array("/(^|>)([^<]+)(?=<|$)/sUe", "/<highlight>(.*)<\/highlight>/siU"), array("highlight('\\2', \$highlightarray, '\\1')", "<strong><font color=\"$color\">\\1</font></strong>"), $message);
		if($sppos !== FALSE) {
			$message = $message.chr(0).chr(0).chr(0).$specialextra;
		}
	}
	return $message;
}

?>