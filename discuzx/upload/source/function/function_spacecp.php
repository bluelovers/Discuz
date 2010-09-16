<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_spacecp.php 16516 2010-09-08 01:52:09Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function album_creat_by_id($albumid, $catid = 0) {
	global $_G, $space;

	preg_match("/^new\:(.+)$/i", $albumid, $matchs);
	if(!empty($matchs[1])) {
		$albumname = dhtmlspecialchars(trim($matchs[1]));
		if(empty($albumname)) $albumname = dgmdate($_G['timestamp'],'Ymd');
		$albumarr = array('albumname' => $albumname);
		if($catid) {
			$albumarr['catid'] = $catid;
		}
		$albumid = album_creat($albumarr);
	} else {
		$albumid = intval($albumid);
		if($albumid) {
			$query = DB::query("SELECT albumname,friend FROM ".DB::table('home_album')." WHERE albumid='$albumid' AND uid='$_G[uid]'");
			if($value = DB::fetch($query)) {
				$albumname = addslashes($value['albumname']);
				$albumfriend = $value['friend'];
			} else {
				$albumname = dgmdate($_G['timestamp'],'Ymd');
				$albumarr = array('albumname' => $albumname);
				if($catid) {
					$albumarr['catid'] = $catid;
				}
				$albumid = album_creat($albumarr);
			}
		}
	}
	return $albumid;
}

function album_update_pic($albumid, $picid=0) {
	global $_G;

	$setarr = array();
	if($picid) {
		$wheresql = "AND picid='$picid'";
	} else {
		$wheresql = "ORDER BY picid DESC LIMIT 1";
		$piccount = getcount('home_pic', array('albumid'=>$albumid, 'status' => '0'));
		if(empty($piccount) && getcount('home_pic', array('albumid' => $albumid)) == 0) {
			DB::query("DELETE FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
			return false;
		} else {
			$setarr['picnum'] = $piccount;
		}
	}
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE albumid='$albumid' $wheresql");
	if(!$pic = DB::fetch($query)) {
		return false;
	}
	$from = $pic['remote'];
	$pic['remote'] = $pic['remote'] > 1 ? $pic['remote'] - 2 : 0;
	$basedir = !getglobal('setting/attachdir') ? (DISCUZ_ROOT.'./data/attachment/') : getglobal('setting/attachdir');
	$picdir = 'cover/'.substr(md5($albumid), 0, 2).'/';
	dmkdir($basedir.'./album/'.$picdir);
	if($pic['remote']) {
		$picsource = pic_get($pic['filepath'], $from > 1 ? 'forum' : 'album', $pic['thumb'], $pic['remote'], 0);
	} else {
		$picsource = $basedir.'./'.($from > 1 ? 'forum' : 'album').'/'.$pic['filepath'];
	}
	require_once libfile('class/image');
	$image = new image();
	if($image->Thumb($picsource, 'album/'.$picdir.$albumid.'.jpg', 120, 120, 2)) {
		$setarr['pic'] = $picdir.$albumid.'.jpg';
		$setarr['picflag'] = 1;
		if(getglobal('setting/ftp/on')) {
			if(ftpcmd('upload', 'album/'.$picdir.$albumid.'.jpg')) {
				$setarr['picflag'] = 2;
			}
		}
	} else {
		if($pic['status'] == 0) {
			$setarr['pic'] = $pic['thumb']?$pic['filepath'].'.thumb.jpg':$pic['filepath'];
		}
		if($from > 1) {
			$setarr['picflag'] = $pic['remote'] ? 4:3;
		} else {
			$setarr['picflag'] = $pic['remote'] ? 2:1;
		}
	}
	DB::update('home_album', $setarr, array('albumid'=>$albumid));
	return true;
}

function pic_save($FILE, $albumid, $title, $iswatermark = true, $catid = 0) {
	global $_G, $space;

	if($albumid<0) $albumid = 0;

	$allowpictype = array('jpg','jpeg','gif','png');

	require_once libfile('class/upload');
	$upload = new discuz_upload();
	$upload->init($FILE, 'album');

	if($upload->error()) {
		return lang('spacecp', 'lack_of_access_to_upload_file_size');
	}

	if(!$upload->attach['isimage']) {
		return lang('spacecp', 'only_allows_upload_file_types');
	}
	$oldgid = $_G['groupid'];
	if(empty($space)) {
		$_G['member'] = $space = getspace($_G['uid']);
		$_G['username'] = addslashes($space['username']);
		$_G['groupid'] = $space['groupid'];
	}
	$_G['member'] = $space;

	loadcache('usergroup_'.$space['groupid'], $oldgid != $_G['groupid'] ? true : false);
	$_G['group'] = $_G['cache']['usergroup_'.$space['groupid']];

	if(!checkperm('allowupload')) {
		return lang('spacecp', 'not_allow_upload');
	}

	if(!ckrealname('album', 1)) {
		return lang('message', 'no_privilege_realname');
	}

	if(!ckvideophoto('album', array(), 1)) {
		return lang('message', 'no_privilege_videophoto');
	}

	if(!cknewuser(1)) {
		return lang('message', 'no_privilege_newbiespan', array('newbiespan' => $_G['setting']['newbiespan']));
	}

	$maxspacesize = checkperm('maxspacesize');
	if($maxspacesize) {
		space_merge($space, 'count');
		space_merge($space, 'field_home');
		if($space['attachsize'] + $upload->attach['size'] > $maxspacesize + $space['addsize'] * 1024 * 1024) {
			return lang('spacecp', 'inadequate_capacity_space');
		}
	}

	$showtip = true;
	$albumfriend = 0;
	if($albumid) {
		$catid = intval($catid);
		$albumid = album_creat_by_id($albumid, $catid);
	} else {
		$albumid = 0;
		$showtip = false;
	}

	$upload->save();
	if($upload->error()) {
		return lang('spacecp', 'mobile_picture_temporary_failure');
	}

	$new_name = $upload->attach['target'];

	require_once libfile('class/image');
	$image = new image();
	$result = $image->Thumb($new_name, '', 140, 140, 1);
	$thumb = empty($result)?0:1;

	if($_G['setting']['maxthumbwidth'] && $_G['setting']['maxthumbheight']) {
		if($_G['setting']['maxthumbwidth'] < 300) $_G['setting']['maxthumbwidth'] = 300;
		if($_G['setting']['maxthumbheight'] < 300) $_G['setting']['maxthumbheight'] = 300;
		$image->Thumb($new_name, '', $_G['setting']['maxthumbwidth'], $_G['setting']['maxthumbheight'], 1, 1);
	}

	if ($iswatermark) {
		$image->Watermark($new_name, '', 'album');
	}
	$pic_remote = 0;
	$album_picflag = 1;

	if(getglobal('setting/ftp/on')) {
		$ftpresult_thumb = 0;
		$ftpresult = ftpcmd('upload', 'album/'.$upload->attach['attachment']);
		if($ftpresult) {
			if($thumb) {
				ftpcmd('upload', 'album/'.$upload->attach['attachment'].'.thumb.jpg');
			}
			$pic_remote = 1;
			$album_picflag = 2;
		} else {
			if(getglobal('setting/ftp/mirror')) {
				@unlink($upload->attach['target']);
				@unlink($upload->attach['target'].'.thumb.jpg');
				return lang('spacecp', 'ftp_upload_file_size');
			}
		}
	}

	$title = getstr($title, 200, 1, 1);
	$title = censor($title);
	if(censormod($title) || $_G['group']['allowuploadmod']) {
		$pic_status = 1;
	} else {
		$pic_status = 0;
	}

	$setarr = array(
		'albumid' => $albumid,
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'filename' => addslashes($upload->attach['name']),
		'postip' => $_G['clientip'],
		'title' => $title,
		'type' => addslashes($upload->attach['ext']),
		'size' => $upload->attach['size'],
		'filepath' => $upload->attach['attachment'],
		'thumb' => $thumb,
		'remote' => $pic_remote,
		'status' => $pic_status,
	);
	$setarr['picid'] = DB::insert('home_pic', $setarr, 1);

	DB::query("UPDATE ".DB::table('common_member_count')." SET attachsize=attachsize+{$upload->attach['size']} WHERE uid='$_G[uid]'");

	include_once libfile('function/stat');
	updatestat('pic');

	return $setarr;
}

function stream_save($strdata, $albumid = 0, $fileext = 'jpg', $name='', $title='', $delsize=0, $from = false) {
	global $_G, $space;

	if($albumid<0) $albumid = 0;

	$setarr = array();

	require_once libfile('class/upload');
	$upload = new discuz_upload();

	$filepath = $upload->get_target_dir('album').$upload->get_target_filename('album').'.'.$fileext;
	$newfilename = $_G['setting']['attachdir'].'./album/'.$filepath;

	if($handle = fopen($newfilename, 'wb')) {
		if(fwrite($handle, $strdata) !== FALSE) {
			fclose($handle);

			$size = filesize($newfilename);

			if(empty($space)) {
				$_G['member'] = $space = getspace($_G['uid']);
				$_G['username'] = addslashes($space['username']);
			}
			$_G['member'] = $space;
			loadcache('usergroup_'.$space['groupid']);
			$_G['group'] = $_G['cache']['usergroup_'.$space['groupid']];

			$maxspacesize = checkperm('maxspacesize');
			if($maxspacesize) {

				space_merge($space, 'count');
				space_merge($space, 'field_home');

				if($space['attachsize'] + $size - $delsize > $maxspacesize + $space['addsize'] * 1024 * 1024) {
					@unlink($newfilename);
					return -1;
				}
			}

			if(!$upload->get_image_info($newfilename)) {
				@unlink($newfilename);
				return -2;
			}

			require_once libfile('class/image');
			$image = new image();
			$result = $image->Thumb($newfilename, NULL, 140, 140, 1);
			$thumb = empty($result)?0:1;

			$image->Watermark($newfilename);

			$pic_remote = 0;
			$album_picflag = 1;

			if(getglobal('setting/ftp/on')) {
				$ftpresult_thumb = 0;
				$ftpresult = ftpcmd('upload', 'album/'.$filepath);
				if($ftpresult) {
					if($thumb) {
						ftpcmd('upload', 'album/'.$filepath.'.thumb.jpg');
					}
					$pic_remote = 1;
					$album_picflag = 2;
				} else {
					if(getglobal('setting/ftp/mirror')) {
						@unlink($newfilename);
						@unlink($newfilename.'.thumb.jpg');
						return -3;
					}
				}
			}

			$filename = addslashes(($name ? $name : substr(strrchr($filepath, '/'), 1)));
			$title = getstr($title, 200, 1, 1);
			$title = censor($title);
			if(censormod($title) || $_G['group']['allowuploadmod']) {
				$pic_status = 1;
			} else {
				$pic_status = 0;
			}

			if($albumid) {
				$albumid = album_creat_by_id($albumid);
			} else {
				$albumid = 0;
			}

			$setarr = array(
				'albumid' => $albumid,
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'filename' => $filename,
				'postip' => $_G['clientip'],
				'title' => $title,
				'type' => $fileext,
				'size' => $size,
				'filepath' => $filepath,
				'thumb' => $thumb,
				'remote' => $pic_remote,
				'status' => $pic_status,
			);
			$setarr['picid'] = DB::insert('home_pic', $setarr, 1);

			DB::query("UPDATE ".DB::table('common_member_count')." SET attachsize=attachsize+$size WHERE uid='$_G[uid]'");

			include_once libfile('function/stat');
			updatestat('pic');

			return $setarr;
		} else {
			fclose($handle);
		}
	}
	return -3;
}

function album_creat($arr) {
	global $_G;

	$albumid = DB::result(DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE albumname='$arr[albumname]' AND uid='$_G[uid]'"));
	if($albumid) {
		return $albumid;
	} else {
		$arr['uid'] = $_G['uid'];
		$arr['username'] = $_G['username'];
		$arr['dateline'] = $arr['updatetime'] = $_G['timestamp'];
		$albumid = DB::insert('home_album', $arr, 1);

		DB::query("UPDATE ".DB::table('common_member_count')." SET albums = albums + 1 WHERE uid = '$_G[uid]'");
		if(isset($arr['catid']) && $arr['catid']) {
			DB::query("UPDATE ".DB::table('home_album_category')." SET num=num+1 WHERE catid='$arr[catid]'");
		}

		return $albumid;
	}
}

function getfilepath($fileext, $mkdir=false) {
	global $_G;

	$filepath = "{$_G['uid']}_{$_G['timestamp']}".random(4).".$fileext";
	$name1 = gmdate('Ym');
	$name2 = gmdate('j');

	if($mkdir) {
		$newfilename = $_G['setting']['attachdir'].'./album/'.$name1;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $filepath;
			}
		}
		$newfilename .= '/'.$name2;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $name1.'/'.$filepath;
			}
		}
	}
	return $name1.'/'.$name2.'/'.$filepath;
}

function getalbumpic($uid, $id) {
	global $_G;

	$query = DB::query("SELECT filepath, thumb FROM ".DB::table('home_pic')." WHERE albumid='$id' AND uid='$uid' ORDER BY thumb DESC, dateline DESC LIMIT 0,1");
	if($pic = DB::fetch($query)) {
		return $pic['filepath'].($pic['thumb']?'.thumb.jpg':'');
	} else {
		return '';
	}
}

function getclassarr($uid) {
	global $_G;

	$classarr = array();
	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_class')." WHERE uid='$uid'");
	while ($value = DB::fetch($query)) {
		$classarr[$value['classid']] = $value;
	}
	return $classarr;
}

function getalbums($uid) {
	global $_G;

	$albums = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE uid='$uid' ORDER BY albumid DESC");
	while ($value = DB::fetch($query)) {
		$albums[$value['albumid']] = $value;
	}
	return $albums;
}

function hot_update($idtype, $id, $hotuser) {
	global $_G;

	$hotusers = empty($hotuser)?array():explode(',', $hotuser);
	if($hotusers && in_array($_G['uid'], $hotusers)) {
		return false;
	} else {
		$hotusers[] = $_G['uid'];
		$hotuser = implode(',', $hotusers);
	}
	$hotuser = daddslashes($hotuser);
	$newhot = count($hotusers)+1;
	if($newhot == $_G['setting']['feedhotmin']) {
		$tablename = gettablebyidtype($idtype);
		$query = DB::query("SELECT uid FROM ".DB::table($tablename)." WHERE $idtype='$id'");
		$item = DB::fetch($query);
		updatecreditbyaction('hotinfo', $item['uid']);
	}

	switch ($idtype) {
		case 'blogid':
			DB::query("UPDATE ".DB::table('home_blogfield')." SET hotuser='$hotuser' WHERE blogid='$id'");
			DB::query("UPDATE ".DB::table('home_blog')." SET hot=hot+1 WHERE blogid='$id'");
			break;
		case 'picid':
			DB::query("REPLACE INTO ".DB::table('home_picfield')." (picid, hotuser) VALUES ('$id', '$hotuser')");
			DB::query("UPDATE ".DB::table('home_pic')." SET hot=hot+1 WHERE picid='$id'");
			break;
		case 'sid':
			DB::query("UPDATE ".DB::table('home_share')." SET hot=hot+1, hotuser='$hotuser' WHERE sid='$id'");
			break;
		default:
			return false;
	}
	$query = DB::query("SELECT feedid, friend FROM ".DB::table('home_feed')." WHERE id='$id' AND idtype='$idtype'");
	if($feed = DB::fetch($query)) {
		if(empty($feed['friend'])) {
			DB::query("UPDATE ".DB::table('home_feed')." SET hot=hot+1 WHERE feedid='$feed[feedid]'");
		}
	} elseif($idtype == 'picid') {
		require_once libfile('function/feed');
		feed_publish($id, $idtype);
	}

	return true;
}

function gettablebyidtype($idtype) {
	$tablename = '';
	if($idtype == 'blogid') {
		$tablename = 'home_blog';
	} elseif($idtype == 'picid') {
		$tablename = 'home_pic';
	} elseif($idtype == 'sid') {
		$tablename = 'home_share';
	}
	return $tablename;
}

function privacy_update() {
	global $_G, $space;

	DB::update('common_member_field_home', array('privacy'=>addslashes(serialize($space['privacy']))), array('uid'=>$_G['uid']));
}

function ckrealname($type, $return=0) {
	global $_G;

	$result = true;
	if($_G['setting']['realname'] && empty($_G['setting']['name_allow'.$type])) {

		space_merge($_G['member'], 'profile');
		if(empty($_G['member']['realname'])) {
			if(empty($return)) showmessage('no_privilege_realname', '', array(), array('return' => true));
			$result = false;
		}
	}
	return $result;
}

function ckvideophoto($type, $tospace=array(), $return=0) {
	global $_G;

	if(empty($_G['setting']['videophoto']) || $_G['member']['videophotostatus']) {
		return true;
	}

	space_merge($tospace, 'field_home');

	$result = true;
	if(empty($tospace) || empty($tospace['privacy']['view']['video'.$type])) {
		if(!checkperm('videophotoignore') && empty($_G['setting']['video_allow'.$type])) {
			if($type != 'viewphoto' || $type == 'viewphoto' && !checkperm('allowviewvideophoto')) {
				$result = false;
			}
		}
	} elseif ($tospace['privacy']['view']['video'.$type] == 2) {
		$result = false;
	} elseif ($tospace['privacy']['view']['video'.$type] == 3) {
		$result = false;
	}
	if($return) {
		return $result;
	} elseif(!$result) {
		showmessage('no_privilege_videophoto', '', array(), array('return' => true));
	}
}

function getvideophoto($filename) {
	$dir1 = substr($filename, 0, 1);
	$dir2 = substr($filename, 1, 1);
	return 'data/avatar/'.$dir1.'/'.$dir2.'/'.$filename.".jpg";
}

function videophoto_upload($FILE, $uid) {
	if($FILE['size']) {
		$newfilename = md5(substr($_G['timestamp'], 0, 7).$uid);
		$dir1 = substr($newfilename, 0, 1);
		$dir2 = substr($newfilename, 1, 1);
		if(!is_dir(DISCUZ_ROOT.'./data/avatar/'.$dir1)) {
			if(!mkdir(DISCUZ_ROOT.'./data/avatar/'.$dir1)) return '';
		}
		if(!is_dir(DISCUZ_ROOT.'./data/avatar/'.$dir1.'/'.$dir2)) {
			if(!mkdir(DISCUZ_ROOT.'./data/avatar/'.$dir1.'/'.$dir2)) return '';
		}
		$new_name = DISCUZ_ROOT.'./'.getvideophoto($newfilename);
		$tmp_name = $FILE['tmp_name'];
		if(@copy($tmp_name, $new_name)) {
			@unlink($tmp_name);
		} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
		} elseif(@rename($tmp_name, $new_name)) {
		} else {
			return '';
		}
		return $newfilename;
	} else {
		return '';
	}
}

function isblacklist($touid) {
	global $_G;

	return getcount('home_blacklist', array('uid'=>$touid, 'buid'=>$_G['uid']));
}

function emailcheck_send($uid, $email) {
	global $_G;

	if($uid && $email) {
		$hash = authcode("$uid\t$email", 'ENCODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16)));
		$verifyurl = $_G['siteurl'].'home.php?mod=misc&amp;ac=emailcheck&amp;hash='.urlencode($hash);
		$mailsubject = lang('email', 'email_verify_subject');
		$mailmessage = lang('email', 'email_verify_message', array(
			'username' => $_G['member']['username'],
			'bbname' => $_G['setting']['bbname'],
			'siteurl' => $_G['siteurl'],
			'url' => $verifyurl
		));

		require_once libfile('function/mail');
		sendmail($email, $mailsubject, $mailmessage);
	}
}

function picurl_get($picurl, $maxlenth='200') {
	$picurl = dhtmlspecialchars(trim($picurl));
	if($picurl) {
		if(preg_match("/^http\:\/\/.{5,$maxlenth}\.(jpg|gif|png)$/i", $picurl)) return $picurl;
	}
	return '';
}

function avatar_file($uid, $size) {
	global $_G;

	$var = "home_avatarfile_{$uid}_{$size}";
	if(empty($_G[$var])) {
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$_G[$var] = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2)."_avatar_$size.jpg";
	}
	return $_G[$var];
}

function interval_check($type) {
	global $_G;

	$waittime = 0;
	if(checkperm('disablepostctrl')) {
		return $waittime;
	}
	if($_G['setting']['floodctrl']) {
		space_merge($_G['member'], 'status');
		getuserprofile('lastpost');
		$waittime = $_G['setting']['floodctrl'] - ($_G['timestamp'] - $_G['member']['lastpost']);
	}
	return $waittime;
}

?>