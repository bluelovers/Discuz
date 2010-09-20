<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attach.class.php 4443 2010-09-14 10:00:24Z fanshengshuai $
 */

class attach {

	function __construct() {
	}

	function attach() {
		$this->__construct();
	}

	function check_attach_size($attach_size) {
		global $_G;
		// 如果 $_G['setting']['attach']['filesize'] 为 0 ,就是不限制附件大小
		if($_G['setting']['attach']['filesize']>0 && ($attach_size > $_G['setting']['attach']['filesize'])){
			return false;
		}else{
			return true;
		}
	}

	function attach_upload($varname = 'Filedata', $multi = 0) {

		global $_G, $_FILES, $_POST, $_SGLOBAL, $_SC;
		$attachdir = A_DIR;

		$attacharray = $path_parts= array();
		$imageexists = 0;

		//static $imgext  = array('jpg', 'jpeg');

		$attach = $_FILES[$varname];

		if(empty($attach)) {
			return 0;
		}

		$attach_saved = false;

		$attach['uid'] = $_G['uid'];
		$filename = saddslashes($attach['name']);
		$attach['title'] = saddslashes(trim(strip_tags(rawurldecode($_POST['title']))));
		$path_parts = pathinfo($filename);
		$attach['ext'] = strtolower($path_parts['extension']);
		//if(!($attach['ext'] == 'jpg' && ($attach['type']=='image/jpeg' || $attach['type']=='application/octet-stream'))) {
		//	return false;
		//}

		// 文件大小检测
		if(!$this->check_attach_size($attach['size'])){
			@unlink($attach['tmp_name']);
			return -1;
		}

		$attach['isimage'] = 1;

		$attach['thumb'] = 0;

		$attach['name'] = htmlspecialchars($attach['name'], ENT_QUOTES);
		$attach['name'] = biconv($attach['name'], 'UTF-8', $_G['charset']);
		$attach['title'] = biconv($attach['title'], 'UTF-8', $_G['charset']);
		if(bstrlen($attach['name']) > 45) {
			$attach['name'] = 'abbr_'.md5($attach['name']).'.'.$attach['ext'];
		}

		if(!is_dir($attachdir.'/photo')) {
			@mkdir($attachdir.'/photo', 0777);
			@fclose(fopen($attachdir.'/photo/index.htm', 'w'));
		}

		$attach_subdir = 'photo/month_'.date('ym');
		$attach_dir = $attachdir.'/'.$attach_subdir;
		if(!is_dir($attach_dir)) {
			@mkdir($attach_dir, 0777);
			@fclose(fopen($attach_dir.'/index.htm', 'w'));
		}
		$attach['attachment'] = $attach_subdir.'/';
		$attach['attachment'] .= date('ymdHi').substr(md5($filename.microtime().random(6)), 8, 16).'.'.$attach['ext'];
		$target = $attachdir.'/'.$attach['attachment'];
		if(@copy($attach['tmp_name'], $target) || (function_exists('move_uploaded_file') && @move_uploaded_file($attach['tmp_name'], $target))) {
			@unlink($attach['tmp_name']);
			$attach_saved = true;
		}

		if(!$attach_saved && @is_readable($attach['tmp_name'])) {
			@$fp = fopen($attach['tmp_name'], 'rb');
			@flock($fp, 2);
			@$attachedfile = fread($fp, $attach['size']);
			@fclose($fp);

			@$fp = fopen($target, 'wb');
			@flock($fp, 2);
			if(@fwrite($fp, $attachedfile)) {
				@unlink($attach['tmp_name']);
				$attach_saved = true;
			}
			@fclose($fp);
		}

		if($attach_saved) {
			@chmod($target, 0644);
			$width = $height = $type = 0;
			$attach['thumb'] = $attach['attachment'];
			//$attach['thumb'] = loadClass('image')->makethumb($target, array(320, 240), substr($target, 0, -4).'.thumb.jpg');
		} else {
			return 8;
		}
		$attacharray = $attach;
		return !empty($attacharray) ? $attacharray : false;
	}

	function savelocalfile($filearr, $thumbarr=array(100, 100), $objfile='', $havethumb=1) {
		global $_G, $_SGLOBAL;

		$patharr = $deault = array('file'=>'', 'thumb'=>'', 'name'=>'', 'type'=>'', 'size'=>0);

		//debug 传入参数
		$filename = strip_tags($filearr['name']);
		$tmpname = str_replace('\\', '\\\\', $filearr['tmp_name']);

		//debug 文件后缀
		$ext = fileext($filename);

		$patharr['name'] = addslashes($filename);
		$patharr['type'] = $ext;
		$patharr['size'] = $filearr['size'];

		// 文件大小检测
		if(!$this->check_attach_size($patharr['size'])){
			@unlink($tmpname);
			cpmsg('attach_too_big','');
			return -1;
		}

		//debug 文件名
		if($objfile) {
			$newfilename = $objfile;
			$isimage = 0;
			$patharr['file'] = $patharr['thumb'] = $objfile;
		} else {
			if(in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
				$isimage = 1;
			} else {
				$isimage = 0;
				$ext = 'attach';
			}
			if(empty($_SGLOBAL['_num'])) $_SGLOBAL['_num'] = 0;
			$_SGLOBAL['_num'] = intval($_SGLOBAL['_num']);
			$_SGLOBAL['_num']++;
			$filemain = $_G['uid'].'_'.sgmdate($_G['timestamp'], 'YmdHis').$_SGLOBAL['_num'].random(4);

			//debug 得到存储目录
			$dirpath = $this->getattachdir();
			if(!empty($dirpath)) $dirpath .= '/';
			$patharr['file'] = $dirpath.$filemain.'.'.$ext;

			//debug 上传
			$newfilename = A_DIR.'/'.$patharr['file'];
		}
		if(@copy($tmpname, $newfilename)) {
		} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmpname, $newfilename))) {
		} elseif(@rename($tmpname, $newfilename)) {
		} else {
			return $deault;
		}
		@unlink($tmpname);

		//debug 缩略图水印
		if($isimage && empty($objfile)) {
			if($ext != 'gif') {
				//debug 缩略图
				if($havethumb == 1) {
					$patharr['thumb'] = loadClass('image')->makethumb($newfilename, $thumbarr);
				}
				//debug 加水印
				//if(!empty($patharr['thumb']) || $havethumb == 0) loadClass('image')->makewatermark($patharr['file']);
			}
			if(empty($patharr['thumb'])) $patharr['thumb'] = $patharr['file'];
		}
		return $patharr;
	}

	function saveremotefile($url, $thumbarr=array(100, 100), $mkthumb=1, $maxsize=0) {
		global $_G, $_SGLOBAL;

		$patharr = $blank = array('file'=>'', 'thumb'=>'', 'name'=>'', 'type'=>'', 'size'=>0);

		$ext = fileext($url);
		$patharr['type'] = $ext;

		if(in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
			$isimage = 1;
		} else {
			$isimage = 0;
			$ext = 'attach';
		}

		//debug 文件名
		if(empty($_SGLOBAL['_num'])) $_SGLOBAL['_num'] = 0;
		$_SGLOBAL['_num'] = intval($_SGLOBAL['_num']);
		$_SGLOBAL['_num']++;
		$filemain = $_G['uid'].'_'.sgmdate($_G['timestamp'], 'Ymd').'0000001'.substr(md5($url), 10, 4);
		$patharr['name'] = $filemain.'.'.$ext;

		if(!is_dir(A_DIR.'/photo')) {
			@mkdir(A_DIR.'/photo', 0777);
			@fclose(fopen(A_DIR.'/photo/index.htm', 'w'));
		}

		$dirpath = 'photo/month_'.date('ym');
		$attach_dir = A_DIR.'/'.$dirpath;
		if(!is_dir($attach_dir)) {
			@mkdir($attach_dir, 0777);
			@fclose(fopen($attach_dir.'/index.htm', 'w'));
		}

		//debug 得到存储目录
		if(!empty($dirpath)) $dirpath .= '/';
		$patharr['file'] = $dirpath.$filemain.'.'.$ext;

		//debug 上传
		$content = sreadfile($url, 'rb', 1, $maxsize);
		if(empty($content)) return $blank;
		writefile(A_DIR.'/'.$patharr['file'], $content, 'text', 'wb', 0);
		if(!file_exists(A_DIR.'/'.$patharr['file'])) return $blank;

		$patharr['size'] = filesize(A_DIR.'/'.$patharr['file']);

		//debug 缩略图水印
		if($isimage) {
			if($mkthumb && $ext != 'gif') {
				//debug 缩略图
				$patharr['thumb'] = loadClass('image')->makethumb($patharr['file'], $thumbarr);
				//debug 加水印
				//if(!empty($patharr['thumb'])) loadClass('image')->makewatermark($patharr['file']);
			}
			if(empty($patharr['thumb'])) $patharr['thumb'] = $patharr['file'];
		}

		return $patharr;
	}

	function getattachdir() {
		global $_G, $_SGLOBAL;
		switch ($_G['setting']['attachmentdirtype']) {
			case 'year':
				$dirpatharr[] = sgmdate($_G['timestamp'], 'Y');
				break;
			case 'month':
				$dirpatharr[] = sgmdate($_G['timestamp'], 'Y');
				$dirpatharr[] = sgmdate($_G['timestamp'], 'm');
				break;
			case 'day':
				$dirpatharr[] = sgmdate($_G['timestamp'], 'Y');
				$dirpatharr[] = sgmdate($_G['timestamp'], 'm');
				$dirpatharr[] = sgmdate($_G['timestamp'], 'd');
				break;
			case 'md5':
				$md5string = md5($_G['uid'].'-'.$_G['timestamp'].'-'.$_SGLOBAL['_num']);
				$dirpatharr[] =  substr($md5string, 0, 1);
				$dirpatharr[] =  substr($md5string, 1, 1);
				break;
			default:
				break;
		}

		$dirs = A_DIR;
		$subarr = array();
		foreach ($dirpatharr as $value) {
			$dirs .= '/'.$value;
			if($this->smkdir($dirs)) {
				$subarr[] = $value;
			} else {
				break;
			}
		}
		return implode('/', $subarr);
	}

	function smkdir($dirname, $ismkindex=1) {
		$mkdir = false;
		if(!is_dir($dirname)) {
			if(@mkdir($dirname, 0777)) {
				if($ismkindex) {
					@fclose(@fopen($dirname.'/index.htm', 'w'));
				}
				$mkdir = true;
			}
		} else {
			$mkdir = true;
		}
		return $mkdir;
	}
}
?>