<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.php 4477 2010-09-15 05:08:30Z fanshengshuai $
 */

require_once('./common.php');
@define('IN_BRAND', true);
@define('IN_ADMIN', true);
@define('IN_STORE', true);
require_once(B_ROOT.'./source/adminfunc/brandpost.func.php');
include_once (B_ROOT.'./language/swfupload.lang.php');
@header('Content-Type: text/xml; charset=utf-8');

$shopid = 0;

$albumcats = getmodelcategory('album', '|--'); //读入相册分类

//日志记录

function misclog($text) {
	@$fp = fopen(B_ROOT.'./data/log/misc.log.php', 'a');
	@flock($fp, 2);
	@fwrite($fp, $text);
	//@fwrite($fp, "<?exit? >".var_export($_GET, true)."\n".var_export($_POST, true)."\n".var_export($_FILES, true)."\n\n");
	@fclose($fp);
}


if($_GET['ac'] == 'swfupload') {
	if($_GET['op'] == 'config') {
		$swfhash = md5(swfhash().$_G['uid']);
		$attachextensions = '*.jpg,*.jpeg,*.gif,*.png';
		$depict = 'JPEG | PNG | GIF Image File ';
		$uploadurl = rawurlencode(substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'], 0, -9).'/misc.php?ac=swfupload&op=upload');
		$thisshopid = 0;

		if($_GET['ineditor']) {
			//编辑器中的数据返回
			if(pkperm('isadmin')) {
				$albumurl = rawurlencode('admin.php?action=ajax_editor&cont=imgattachlist&albumid=');
				$thisshopid = $_G['cookie']['shopid'];
			} else {
				$thisshopid = $_G['myshopid'];
				$albumurl = rawurlencode('panel.php?action=ajax_editor&cont=imgattachlist&shopid='.$_G['myshopid'].'&albumid=');
			}
		} else {
			//普通上传中的数据返回
			if(pkperm('isadmin')) {
				$thisshopid = $_G['cookie']['shopid'];
				$albumurl = rawurlencode('admin.php?action=list&m=photo&shopid=0&filtersubmit=GO&albumid=');
			} else {
				$thisshopid = $_G['myshopid'];
				$albumurl = rawurlencode('panel.php?action=list&m=photo&shopid='.$_G['myshopid'].'&filtersubmit=GO&albumid=');
			}
		}
		$feedurl = '';
		$maxupload = $_G['setting']['attach']['filesize']>0?$_G['setting']['attach']['filesize']:10240000;
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<parameter>
				<allowsExtend>
					<extend depict=\"$depict\">$attachextensions</extend>
				</allowsExtend>
			<language>$xmllang</language>
			<config>
				<userid>$_G[uid]</userid>
				<shopid>$thisshopid</shopid>
				<hash>$swfhash</hash>
				<maxupload>$maxupload</maxupload>
				<uploadurl>$uploadurl</uploadurl>
				<feedurl></feedurl>
				<albumurl>$albumurl</albumurl>
			</config>
			<albums>";
				if($_GET['ineditor']) {
						echo "	<album id=\"0\">$slang[album_default]</album>";
						//编辑器上传只列出默认相册，返回相册id -1
				} else {
					if(pkperm('isadmin')) {
						$wheresql = " WHERE itemid='$_GET[albumid]'";
					} else {
						$wheresql = " WHERE itemid='$_GET[albumid]' AND shopid='$_G[myshopid]'";
					}
					if($_GET['albumid']>0) {
						$subject = DB::result_first('SELECT subject FROM '.tname('albumitems').$wheresql);
						$subject = biconv($subject, $_G['charset'], 'UTF-8');
						if(!empty($subject)) {
							echo "<album id=\"$_GET[albumid]\">$subject</album>";
						}
					}
				}
			echo "
			</albums>
			<categories><category id=\"1\">cat</category></categories>
		</parameter>";


	} elseif ($_GET['op'] == 'upload' && $_POST['Upload'] == 'Submit Query') {
		$_POST['uid'] = intval($_POST['uid']);
		$_POST['albumid'] = intval($_POST['albumid']);
		$albumimg = '';
		$swfhash = md5(swfhash().$_POST['uid']);
		if(!$_FILES['Filedata']['error'] && $_POST['hash'] == $swfhash) {
			$_G['uid'] = intval($_POST['uid']);
			$query = DB::query('SELECT * FROM '.tname('members').' WHERE uid=\''.$_G['uid'].'\' LIMIT 1');
			$_G['member'] = DB::fetch($query);
			$_G['username'] = $_G['member']['username'];
			$_G['myshopid'] = $_G['member']['myshopid'];
			$shop_info = DB::fetch_first("select grade from ".tname('shopitems')." where itemid=".$_G['myshopid']);
			if(pkperm('isadmin') || ($shop_info['grade'] == 3)) {
				getpanelinfo();
				//现有相册
				if(pkperm('isadmin')) {
					$albumid = $_POST['albumid'];
					if(empty($albumid)) {
						//编辑器上传默认相册
						$shopid = intval($_POST['shopid']);
					} else {
						$sql = 'SELECT shopid, subjectimage FROM '.tname('albumitems')." WHERE itemid='$albumid' LIMIT 1";
						//非默认相册
						$query = DB::fetch_first($sql);
						$shopid = $query['shopid'];
						$albumimg = $query['subjectimage'];
					}
				} else {
					$shopid = $_G['myshopid'];
					if(empty($_POST['albumid'])) {
						$albumid = 0;
					} else {
						//检查是否为该商家创建的相册
						$query = DB::fetch_first('SELECT shopid, subjectimage, grade FROM '.tname('albumitems')." WHERE itemid='$_POST[albumid]' AND shopid='$_G[myshopid]' LIMIT 1");

						$shopid = $query['shopid'];
						$albumimg = $query['subjectimage'];
						$albumgrade = $query['grade'];
						if(empty($shopid)) {
							$albumid = 0; //不属于自己的相册，将传到默认相册中
						} else {
							$albumid = $_POST['albumid'];
							if($_SGLOBAL['panelinfo']['group']['verifyalbum']) {
								if($albumgrade > 1) {
									$query = DB::query('SELECT * FROM '.tname('albumitems')." WHERE itemid='$albumid' LIMIT 1");
									$update = DB::fetch($query);
									$update = serialize($update);
									DB::query("REPLACE INTO ".tname("itemupdates")." (`itemid`, `type`, `updatestatus`, `update`) VALUES ('$albumid', 'album', '1', '$update');");
									DB::query("UPDATE ".tname("albumitems")." SET updateverify = 1 WHERE itemid = '$albumid' ;");
								} elseif($albumgrade == 1) {
									DB::query("UPDATE ".tname("albumitems")." SET grade = 0 WHERE itemid = '$albumid' ;");
								}
							}
						}
					}
				}

				$attach = loadClass('attach')->attach_upload('Filedata');
				if(is_array($attach) && $shopid) {
					$attach['name'] = substr($attach['name'], 0, -4);
					if($_SGLOBAL['panelinfo']['group']['verifyalbum'] && !pkperm('isadmin')) {
						$grade = 0;
					} else {
						$grade = 3;
					}
					$photoid = DB::insert('photoitems', array('shopid'=>$shopid, 'albumid'=>$albumid, 'uid'=>$_G['uid'], 'username'=>$_G['username'], 'subject'=>$attach['title'], 'subjectimage'=>$attach['attachment'], 'dateline'=>$_G['timestamp'], 'lastpost'=>$_G['timestamp'], 'allowreply'=>'1', 'grade'=>$grade), 1);
				}
				if(empty($photoid) || $photoid<0) {
					//插入数据库失败则删除文件
					@unlink(A_DIR.'/'.$attach['attachment']);
				}
				$updatesql = array();
				if($photoid) {
					$updatesql[] = " `picnum`=`picnum`+1 ";
				}
				if(empty($albumimg) && $photoid) {
					//相册无封面图片时设置封面图片
					$updatesql[] = " `subjectimage`='$attach[attachment]' ";
				}
				if($updatesql) {
					DB::query('UPDATE '.tname('albumitems').' SET '.implode(', ', $updatesql)." WHERE itemid='$albumid'");
				    if(!empty($albumid)) {
				        require_once(B_ROOT.'./api/bbs_syncpost.php');
				        syncalbum($albumid);
				    }
				    
				}

			} else {
				$attach['name'] = 'UPLOAD Denied';
			}
		}
		if(!empty($photoid) && $photoid>0) {
			$_BCACHE->deltype('storelist', 'photo', $shopid, $albumid);
			$xmlstatus = 'success';
			$fileurl = getattachurl($attach['attachment']);
		} else {
			$xmlstatus = 'failure';
		}
		//返回XML
		$returnxml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<uploadResponse>
			<message>".(($xmlstatus=='success')?$slang['done']:$attach)."</message>
			<status>$xmlstatus</status>
			<albumid>$albumid</albumid>
			<picid>$photoid</picid>
			<proid></proid>
			<filepath>$fileurl</filepath>
		</uploadResponse>";
		echo $returnxml;
	}
}

function swfhash() {
	global $_G, $_SGLOBAL;

	if(empty($_SGLOBAL['swfhash'])) {
		$hashadd = (defined('IN_ADMIN') || defined('IN_STORE')) ? 'Only For BRAND Admin OR Panel' : '';
		$_SGLOBAL['swfhash'] = substr(md5(substr($_G['timestamp'], 0, -7).'|'.md5($_G['setting']['sitekey']).'|'.$hashadd), 8, 8);
	}
	return $_SGLOBAL['swfhash'];
}

?>