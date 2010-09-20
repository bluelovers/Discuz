<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: bbs_pic.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

/**
 * 得到帖子中的图片
 * @param $tid - 帖子ID
 * @param $firstpostonly - 首帖
 * @param $page - 分页参数
 * @param $limit - 每页列出数目，初始为 9
 * @param $minsize - 附件体积最少为，单位KB
 * @param $getcount - 是否取总数（若取总数则不取具体信息）
 */
function list_thread_pic($tid=0, $firstpostonly=0, $page=0, $limit=9, $minsize=20, $getcount=0){
	global $_G, $_SC;

	$tid = intval($tid);
	$page = intval($page);
	$limit = intval($limit);
	$minsize = intval($minsize);
	$firstpostonly = intval($firstpostonly);

	$ret_attach = array();

	$thread = list_threads($tid); //取得帖子基本信息

	if($thread) {

		$bbs_dbpre = $_SC['bbs_dbpre'];
		$db = new db_mysql(array(
		    1 => array(
		        'tablepre' => $_SC['bbs_dbpre'],
		        'dbcharset' => $_SC['bbs_dbcharset'],
		        'dbhost' => $_SC['bbs_dbhost'],
		        'dbuser' => $_SC['bbs_dbuser'],
		        'dbpw' => $_SC['bbs_dbpw'],
		        'dbname' => $_SC['bbs_dbname'],
		    )
		));
		$db->connect();
		if($firstpostonly){
			$wheresql = " a.pid='$thread[pid]'";
		} else {
			$wheresql = " a.tid='$tid' AND a.uid='$thread[authorid]'";
		}
		$minsize = $minsize*1024;
		$wheresql .= " AND a.filesize>'$minsize'";
		$wheresql .= " AND a.isimage='1'";

		if($getcount) {

			$countsql = "SELECT count(a.aid) FROM {$bbs_dbpre}attachments a";
			$count = $db->result_first("$countsql WHERE $wheresql");
			return $count; //返回计数

		} else {

			// 得到附件目录
			$sql ="SELECT * FROM {$bbs_dbpre}settings WHERE variable IN ('attachurl', 'boardurl', 'ftp')";
			$query = $db->query($sql);
			while($rs = $db->fetch_array($query)){
				$ret_attach[$rs['variable']] = $rs['value'];
			}
			$ret_attach['ftp']=unserialize($ret_attach['ftp']);

			if(empty($ret_attach['boardurl'])){
				$ret_attach['boardurl'] = $_SC['bbs_url'].'/';
			}
			$selectsql = "SELECT a.aid, a.pid, a.price, a.dateline, a.readperm, a.downloads, a.filename, a.filetype, a.filesize, a.attachment, a.thumb, a.remote, aa.description FROM {$bbs_dbpre}attachments a LEFT JOIN {$bbs_dbpre}attachmentfields aa ON a.aid=aa.aid";
			$sql = "$selectsql WHERE $wheresql LIMIT ".($page*$limit).", $limit";
			$query = $db->query($sql);

			while($attach = $db->fetch_array($query)){
				if($attach['remote']){
					$attach['url']=$ret_attach['ftp']['attachurl'].'/'.$attach['attachment'];
				}else{
					$attach['url']=(strpos($ret_attach['attachurl'], 'http://')===0?$ret_attach['attachurl']:$ret_attach['boardurl'].$ret_attach['attachurl']).'/'.$attach['attachment'];
				}
				$ret_attach['attachments'][$attach['aid']] = $attach;
			}

		}

		$db->close();
		unset($db);
	}

	return $ret_attach;
}

/**
 * 得到帖子信息
 * @param $tid - 帖子ID
 * @param $returnpid - 返回首帖pid
 * @param $msgleng - 内容截断的长度
 */
function list_threads($tid=0, $msgleng=255){
	global $_G, $_SC;

	$tid = intval($tid);
	$msgleng = intval($msgleng);

	$ret_thread = array();

	if($tid>0) {
		$bbs_dbpre = $_SC['bbs_dbpre'];
		$db = new db_mysql(array(
		    1 => array(
		        'tablepre' => $_SC['bbs_dbpre'],
		        'dbcharset' => $_SC['bbs_dbcharset'],
		        'dbhost' => $_SC['bbs_dbhost'],
		        'dbuser' => $_SC['bbs_dbuser'],
		        'dbpw' => $_SC['bbs_dbpw'],
		        'dbname' => $_SC['bbs_dbname'],
		    )
		));
		$db->connect();
		//$db->charset = $_SC['bbs_dbcharset'];
		//$db->connect($_SC['bbs_dbhost'], $_SC['bbs_dbuser'], $_SC['bbs_dbpw'], $_SC['bbs_dbname'], 0, 1);

		$query = $db->query("SELECT pid, authorid, author, subject, message FROM {$bbs_dbpre}posts WHERE tid='$tid' AND first='1' LIMIT 1");
		$ret_thread = $db->fetch_array($query);
		if(!empty($ret_thread['message'])) {
			$ret_thread['message'] = messagecutstr($ret_thread['message'], $msgleng);
		}
		$db->close();
		unset($db);
	}

	return $ret_thread;
}

?>