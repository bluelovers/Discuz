<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ftn_cross.inc.php 29021 2012-03-22 09:35:55Z songlixin $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['gp_ftn_formhash']) || empty($_G['uid']) || empty($_G['gp_filesize']) || empty($_G['gp_sha1']) || empty($_G['gp_filename'])){
    if(empty($_G['gp_allcount']) && empty($_G['gp_uploadedcount']) && empty($_G['gp_errorcount'])){
        exit;//echo $_G['gp_allcount'].'|'.$_G['gp_uploadedcount'].'|'.$_G['gp_errorcount'];
    } else {
        if($_G['gp_allcount'] == ($_G['gp_uploadedcount']+$_G['gp_errorcount'])){
            $allowUpdate = 1;
        } else {
            $allowUpdate = 0;
        }
        include template('xf_storage:cross');
    }
} elseif($_G['gp_ftn_formhash'] != ftn_formhash()){
	exit;//showmessage('操作超时或者数据来源错误','','error');
}



if($_G['gp_ftn_submit']) {

	$data = array();$index = array();
	$filesize = intval($_G['gp_filesize']);
	$filename = diconv(trim($_G['gp_filename']),'UTF-8');
	$filename = str_replace(array('\'','"','\/','\\','<','>'),array('','','','','',''),$filename);
	$sha = trim($_G['gp_sha1']);
	$index = array(
		'tid' => 0,
		'pid' => 0,
		'uid' => $_G['uid'],
		'tableid' => '127',
		'downloads' => 0
	);
	$aid = DB::insert('forum_attachment',$index,1);

	$data = array(
		'aid' => $aid,
		'uid' => $_G['uid'],
		'dateline' => $_G['timestamp'],
		'filename' => $filename,
		'filesize' => $filesize,
		'attachment' => '',
		'remote' => 0,
		'isimage' => 0,
		'width' => 0,
		'thumb' => 0,
		'sha1' => $sha
	);
	$aid = DB::insert('forum_attachment_unused',$data,1);
    if(empty($_G['gp_allcount']) && empty($_G['gp_uploadedcount']) && empty($_G['gp_errorcount'])){
        exit;//echo $_G['gp_allcount'].'|'.$_G['gp_uploadedcount'].'|'.$_G['gp_errorcount'];
    } else {
        if($_G['gp_allcount'] == ($_G['gp_uploadedcount']+$_G['gp_errorcount'])){
            $allowUpdate = 1;
        } else {
            $allowUpdate = 0;
        }
        include template('xf_storage:cross');
    }
}




?>