<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_blog.php 17174 2010-09-25 09:31:19Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function blog_post($POST, $olds=array()) {
	global $_G, $space;

	$isself = 1;
	if(!empty($olds['uid']) && $olds['uid'] != $_G['uid']) {
		$isself = 0;
		$__G = $_G;
		$_G['uid'] = $olds['uid'];
		$_G['username'] = addslashes($olds['username']);
	}

//	$POST['subject'] = getstr(trim($POST['subject']), 80, 1, 1);
	$POST['subject'] = getstr(trim($POST['subject']), $_G['setting']['maxpostsize_subject'], 1, 1);
	$POST['subject'] = censor($POST['subject']);
	if(strlen($POST['subject'])<1) $POST['subject'] = dgmdate($_G['timestamp'], 'Y-m-d');
	$POST['friend'] = intval($POST['friend']);

	$POST['target_ids'] = '';
	if($POST['friend'] == 2) {
		$uids = array();
		$names = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
		if($names) {
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN (".dimplode($names).")");
			while ($value = DB::fetch($query)) {
				$uids[] = $value['uid'];
			}
		}
		if(empty($uids)) {
			$POST['friend'] = 3;
		} else {
			$POST['target_ids'] = implode(',', $uids);
		}
	} elseif($POST['friend'] == 4) {
		$POST['password'] = trim($POST['password']);
		if($POST['password'] == '') $POST['friend'] = 0;
	}
	if($POST['friend'] !== 2) {
		$POST['target_ids'] = '';
	}
	if($POST['friend'] !== 4) {
		$POST['password'] == '';
	}

	$POST['tag'] = dhtmlspecialchars(trim($POST['tag']));
	$POST['tag'] = getstr($POST['tag'], 500, 1, 1);
	$POST['tag'] = censor($POST['tag']);

	// bluelovers
	$POST['message'] = scotext::lf($POST['message']);
	// bluelovers

	if($_G['mobile']) {
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 1);
		$POST['message'] = censor($POST['message']);
	} else {
		$POST['message'] = checkhtml($POST['message']);
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 0, 1);
		$POST['message'] = censor($POST['message']);
		$POST['message'] = preg_replace(array(
			"/\<div\>\<\/div\>/i",
			"/\<a\s+href\=\"([^\>]+?)\"\>/i"
		), array(
			'',
			'<a href="\\1" target="_blank">'
		), $POST['message']);
	}
	$message = $POST['message'];
	if(censormod($message) || censormod($POST['subject']) || $_G['group']['allowblogmod']) {
		$blog_status = 1;
	} else {
		$blog_status = 0;
	}

	if(empty($olds['classid']) || $POST['classid'] != $olds['classid']) {
		if(!empty($POST['classid']) && substr($POST['classid'], 0, 4) == 'new:') {
			$classname = dhtmlspecialchars(trim(substr($POST['classid'], 4)));
			$classname = getstr($classname, 0, 1, 1);
			$classname = censor($classname);
			if(empty($classname)) {
				$classid = 0;
			} else {
				$classid = DB::result(DB::query("SELECT classid FROM ".DB::table('home_class')." WHERE uid='$_G[uid]' AND classname='$classname'"));
				if(empty($classid)) {
					$setarr = array(
						'classname' => $classname,
						'uid' => $_G['uid'],
						'dateline' => $_G['timestamp']
					);
					$classid = DB::insert('home_class', $setarr, 1);
				}
			}
		} else {
			$classid = intval($POST['classid']);

		}
	} else {
		$classid = $olds['classid'];
	}
	if($classid && empty($classname)) {
		$classname = DB::result(DB::query("SELECT classname FROM ".DB::table('home_class')." WHERE classid='$classid' AND uid='$_G[uid]'"));
		if(empty($classname)) $classid = 0;
	}

	$blogarr = array(
		'subject' => $POST['subject'],
		'classid' => $classid,
		'friend' => $POST['friend'],
		'password' => $POST['password'],
		'noreply' => empty($POST['noreply'])?0:1,
		'catid' => intval($POST['catid']),
		'status' => $blog_status,
	);

	$titlepic = '';

	$uploads = array();
	if(!empty($POST['picids'])) {
		$picids = array_keys($POST['picids']);
		$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($picids).") AND uid='$_G[uid]'");
		while ($value = DB::fetch($query)) {
			if(empty($titlepic) && $value['thumb']) {
				$titlepic = $value['filepath'].'.thumb.jpg';
				$blogarr['picflag'] = $value['remote']?2:1;
			}
			$uploads[$POST['picids'][$value['picid']]] = $value;
		}
		if(empty($titlepic) && $value) {
			$titlepic = $value['filepath'];
			$blogarr['picflag'] = $value['remote']?2:1;
		}
	}

	if($uploads) {
		preg_match_all("/\[imgid\=(\d+)\]/i", $message, $mathes);
		if(!empty($mathes[1])) {
			$searchs = $replaces = array();
			foreach ($mathes[1] as $key => $value) {
				if(!empty($uploads[$value])) {
					$picurl = pic_get($uploads[$value]['filepath'], 'album', $uploads[$value]['thumb'], $uploads[$value]['remote'], 0);
					$searchs[] = "[imgid=$value]";
					$replaces[] = "<img src=\"$picurl\">";
					unset($uploads[$value]);
				}
			}
			if($searchs) {
				$message = str_replace($searchs, $replaces, $message);
			}
		}
		foreach ($uploads as $value) {
			$picurl = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$message .= "<div class=\"uchome-message-pic\"><img src=\"$picurl\"><p>$value[title]</p></div>";
		}
	}

	$ckmessage = preg_replace("/(\<div\>|\<\/div\>|\s|\&nbsp\;|\<br\>|\<p\>|\<\/p\>)+/is", '', $message);
	if(empty($ckmessage)) {
		return false;
	}

	$message = addslashes($message);

	if(empty($titlepic) && empty($olds)) {
		$titlepic = getmessagepic($message);
		$blogarr['picflag'] = 0;
	}

	if(checkperm('manageblog')) {
		$blogarr['hot'] = intval($POST['hot']);
	}

	if($olds['blogid']) {

		if($blogarr['catid'] != $olds['catid']) {
			if($olds['catid']) {
				DB::query("UPDATE ".DB::table('home_blog_category')." SET num=num-1 WHERE catid='$olds[catid]' AND num>0");
			}
			if($blogarr['catid']) {
				DB::query("UPDATE ".DB::table('home_blog_category')." SET num=num+1 WHERE catid='$blogarr[catid]'");
			}
		}

		$blogid = $olds['blogid'];
		DB::update('home_blog', $blogarr, array('blogid'=>$blogid));

		$fuids = array();

		$blogarr['uid'] = $olds['uid'];
		$blogarr['username'] = $olds['username'];
	} else {

		if($blogarr['catid']) {
			DB::query("UPDATE ".DB::table('home_blog_category')." SET num=num+1 WHERE catid='$blogarr[catid]'");
		}

		$blogarr['uid'] = $_G['uid'];
		$blogarr['username'] = $_G['username'];
		$blogarr['dateline'] = empty($POST['dateline'])?$_G['timestamp']:$POST['dateline'];
		$blogid = DB::insert('home_blog', $blogarr, 1);

		DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
		DB::update('common_member_field_home', array('recentnote'=>$POST['subject']), array('uid'=>$_G['uid']));
	}

	$blogarr['blogid'] = $blogid;

	$fieldarr = array(
		'message' => $message,
		'postip' => $_G['clientip'],
		'target_ids' => $POST['target_ids'],
		'tag' => $POST['tag']
	);

	if(!empty($titlepic)) {
		$fieldarr['pic'] = $titlepic;
	}

	if($olds) {
		DB::update('home_blogfield', $fieldarr, array('blogid'=>$blogid));
	} else {
		$fieldarr['blogid'] = $blogid;
		$fieldarr['uid'] = $blogarr['uid'];
		DB::insert('home_blogfield', $fieldarr);
	}

	if($isself && !$olds && $blog_status == 0) {
		updatecreditbyaction('publishblog', 0, array('blogs' => 1));

		include_once libfile('function/stat');
		updatestat('blog');
	}

	if($POST['makefeed'] && $blog_status == 0) {
		include_once libfile('function/feed');
		feed_publish($blogid, 'blogid', $olds?0:1);
	}

	if(!empty($__G)) $_G = $__G;

	return $blogarr;
}

function getmessagepic($message) {
	$pic = '';


	return addslashes($pic);
}

function checkhtml($html) {
	$html = dstripslashes($html);
	if(!checkperm('allowhtml')) {

		preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

		$searchs[] = '<';
		$replaces[] = '&lt;';
		$searchs[] = '>';
		$replaces[] = '&gt;';

		if($ms[1]) {
			$allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|object|param|embed';
			$ms[1] = array_unique($ms[1]);
			foreach ($ms[1] as $value) {
				$searchs[] = "&lt;".$value."&gt;";

				$value = str_replace('&', '_uch_tmp_str_', $value);
				$value = dhtmlspecialchars($value);
				$value = str_replace('_uch_tmp_str_', '&', $value);

				$value = str_replace(array('\\','/*'), array('.','/.'), $value);
				$value = preg_replace(array("/(javascript|script|eval|behaviour|expression|style|class)/i", "/(\s+|&quot;|')on/i"), array('.', ' .'), $value);
				if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
					$value = '';
				}
				$replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
			}
		}
		$html = str_replace($searchs, $replaces, $html);
	}
	$html = addslashes($html);

	return $html;
}

function blog_bbcode($message) {
	$message = preg_replace("/\[flash\=?(media|real)*\](.+?)\[\/flash\]/ie", "blog_flash('\\2', '\\1')", $message);
	return $message;
}
function blog_flash($swf_url, $type='') {
	$width = '520';
	$height = '390';
	if ($type == 'media') {
		$html = '<object classid="clsid:6bf52a52-394a-11d3-b153-00c04f79faa6" width="'.$width.'" height="'.$height.'">
			<param name="autostart" value="0">
			<param name="url" value="'.$swf_url.'">
			<embed autostart="false" src="'.$swf_url.'" type="video/x-ms-wmv" width="'.$width.'" height="'.$height.'" controls="imagewindow" console="cons"></embed>
			</object>';
	} elseif ($type == 'real') {
		$html = '<object classid="clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa" width="'.$width.'" height="'.$height.'">
			<param name="autostart" value="0">
			<param name="src" value="'.$swf_url.'">
			<param name="controls" value="Imagewindow,controlpanel">
			<param name="console" value="cons">
			<embed autostart="false" src="'.$swf_url.'" type="audio/x-pn-realaudio-plugin" width="'.$width.'" height="'.$height.'" controls="controlpanel" console="cons"></embed>
			</object>';
	} else {
		$html = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'.$width.'" height="'.$height.'">
			<param name="movie" value="'.$swf_url.'">
			<param name="allowscriptaccess" value="none">
			<param name="allowNetworking" value="none">
			<embed src="'.$swf_url.'" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'" allowfullscreen="true" allowscriptaccess="always"></embed>
			</object>';
	}
	return $html;
}

?>