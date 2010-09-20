<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: bbs_syncpost.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

if(!defined('IN_ADMIN') && !defined('IN_STORE')) {
	exit('Acess Denied');
}
/**
 *判断版块是否存在
 *@param $fid - 版块ID
 */
 
function checkbbsfid($fid) {
	global $_G, $_SC;
    $bbs_dbpre = $_SC['bbs_dbpre'];
    $db = new db_mysql(array(
        1 => array(
            'tablepre' => $_SC['bbs_dbpre'],
            'dbcharset' => $_SC['bbs_dbcharset'],
            'dbhost' => $_SC['bbs_dbhost'],
            'dbuser' => $_SC['bbs_dbuser'],
            'dbpw' => $_SC['bbs_dbpw'],
            'dbname' => $_SC['bbs_dbname'],
            'silent' => true,
        )
    ));
    $db->connect();
    if($db->result_first("SELECT count(*) FROM {$bbs_dbpre}forum_forum WHERE fid = '$fid'")) {
        $db->close();
		unset($db);
        return true;
    } else {
    	$db->close();
		unset($db);
        return false;
    }
}
/**
 *判断主题是否存在
 *param $tid - 主题id
 */
 
function checkbbstid($tid) {
	global $_G, $_SC;
    $bbs_dbpre = $_SC['bbs_dbpre'];
    $db = new db_mysql(array(
        1 => array(
            'tablepre' => $_SC['bbs_dbpre'],
            'dbcharset' => $_SC['bbs_dbcharset'],
            'dbhost' => $_SC['bbs_dbhost'],
            'dbuser' => $_SC['bbs_dbuser'],
            'dbpw' => $_SC['bbs_dbpw'],
            'dbname' => $_SC['bbs_dbname'],
            'silent' => true,
        )
    ));
    $db->connect();

    if($db->result_first("SELECT count(*) FROM {$bbs_dbpre}forum_thread WHERE tid = $tid")) {
        $db->close();
		unset($db);
        return true;
    } else {
    	$db->close();
		unset($db);
        return false;
    }
}

/**
 *同步更新相册
 */
function syncalbum($albumid) {
	global $_G, $_SC, $_SGLOBAL;
 	$bbs_dbpre = $_SC['bbs_dbpre'];
    $db = new db_mysql(array(
        1 => array(
            'tablepre' => $_SC['bbs_dbpre'],
            'dbcharset' => $_SC['bbs_dbcharset'],
            'dbhost' => $_SC['bbs_dbhost'],
            'dbuser' => $_SC['bbs_dbuser'],
            'dbpw' => $_SC['bbs_dbpw'],
            'dbname' => $_SC['bbs_dbname'],
            'silent' => true,
        )
    ));
    $db->connect();

    $album = DB::fetch_first("SELECT * FROM ".DB::table("albumitems")." WHERE itemid = '$albumid' AND frombbs = 0 AND grade = 3");
 	if(empty($album)) {
        $db->close();
        unset($db);
 	    return false;
 	}
 	//if(empty($_SGLOBAL['panelinfo'])) {
 	    getpanelinfo($album['shopid']);
 	//}
 	$fid = $_SGLOBAL['panelinfo']['syncfid'];
 	if(!checkbbsfid($fid)) {
        $db->close();
        unset($db);
        return false;
    }
 	$photolist = array();
 	$query = DB::query("SELECT * FROM ".DB::table('photoitems')." WHERE albumid = $albumid AND grade = 3");
 	while($result = DB::fetch($query)) {
 	    $photolist[] = $result;
 	}
 	
 	$data['item'] = $album;
 	$data['photolist'] = $photolist;
 	$data['message']['itemid'] = $album['itemid'];
 	//插入主题信息
    $author = $_SGLOBAL['panelinfo']['username'];
    $authorid = $_SGLOBAL['panelinfo']['uid'];
    $subject = "[".b_lang('album')."]".$data['item']['subject'];
 	$message = postformat('album', $data);
    $posttable_info = $db->result_first("SELECT svalue FROM {$bbs_dbpre}common_setting WHERE skey = 'posttable_info'");
    $posttableid = 0;
    if(!empty($posttable_info)) {
        $posttable_info = unserialize($posttable_info);
        if(is_array($posttable_info)) {
            foreach($posttable_info as $key=>$info) {
                if($info['type'] == 'primary') {
                    $posttableid = $key;
                }
            }
        }
    }
    if(!$posttableid) {
		$tablename = 'forum_post';
	} else {
		$tablename = "forum_post_$posttableid";
	}
	if(empty($album['bbstid'])) {
    
        $db->query("INSERT INTO {$bbs_dbpre}forum_thread (fid, posttableid, author, authorid, subject, dateline, lastpost, lastposter)
        VALUES ('$fid', '$posttableid', '$author', '$authorid', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author')");
        $tid = $db->insert_id();
        $db->query("UPDATE {$bbs_dbpre}common_member_field_home SET recentnote = '$subject' WHERE uid = '$authorid'");
    
        $db->query("INSERT INTO {$bbs_dbpre}forum_post_tableid (pid) values (null)");
        $pid = $db->insert_id();
        if($pid % 1024 == 0) {
            $db->query("DELETE FROM {$bbs_dbpre}forum_post_tableid WHERE pid<$pid");
        }
        $db->query("REPLACE INTO {$bbs_dbpre}common_syscache (cname, ctype, dateline, data) VALUES ('max_post_id', '0', '$_G[timestamp]', '$pid')");
        if(!$posttableid) {
            $tablename = 'forum_post';
        } else {
            $tablename = "forum_post_$posttableid";
        }
        $db->query("INSERT INTO {$bbs_dbpre}{$tablename} SET `fid`='$fid',`tid`='$tid',`first`='1',`author`='$author',`authorid`='$authorid',`subject`='$subject',`dateline`='$_G[timestamp]',`message`='$message ',`useip`='unknown',`invisible`='0',`anonymous`='0',`usesig`='1',`htmlon`='0',`bbcodeoff`='0',`smileyoff`='-1',`parseurloff`='',`attachment`='0',`tags`='',`pid`='$pid'");
        $db->query("UPDATE {$bbs_dbpre}forum_forum SET lastpost='{$tid} {$subject} {$_G[timestamp]} {$author}', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'");
        $db->query("UPDATE {$bbs_dbpre}common_stat SET `thread`=`thread`+1 WHERE daytime='".date("Ymd", $_G[timestamp])."'");
		
		updatetable('albumitems', array('bbstid'=>$tid), array('itemid'=>$album['itemid']));
    } else {
        $tid = $album['bbstid'];
        $db->query("UPDATE {$bbs_dbpre}forum_thread SET subject='".$subject."' WHERE tid='$tid'");
        $pid = $db->result_first("SELECT pid FROM {$bbs_dbpre}{$tablename} WHERE tid = '$tid' AND first = 1");
    	$db->query("UPDATE {$bbs_dbpre}{$tablename} SET message='$message' WHERE pid='$pid' AND tid = '$tid' AND first = 1");
    }
}
 

function bbs_bbcode($str) {
    //echo $str;
    $str = stripslashes($str);
	preg_match_all("/\<img[^>]*src\=\\\\\"(.*?)\\\\\"[^>]*>/i", $str, $match);
	foreach($match[0] as $key=>$matchs) {
		$str = str_replace($matchs,"[img]".$match[1][$key]."[/img]",$str);
	}
	preg_match_all("/\<img[^>]*src\=\"(.*?)\"[^>]*>/i", $str, $match);
	foreach($match[0] as $key=>$matchs) {
		$str = str_replace($matchs,"[img]".$match[1][$key]."[/img]",$str);
	}
    preg_match_all("/\<blockquote\>(.*?)\<\/blockquote\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[quote]".$match[1][$key]."[/quote]",$str);
    }
	preg_match_all("/\<strong[^>]*\>(.*?)\<\/strong\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[b]".$match[1][$key]."[/b]",$str);
    }
  	preg_match_all("/\<em[^>]*\>(.*?)\<\/em\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[i]".$match[1][$key]."[/i]",$str);
    }  
 	preg_match_all("/\<u[^>]*\>(.*?)\<\/u\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[u]".$match[1][$key]."[/u]",$str);
    }  
    preg_match_all("/\<del[^>]*\>(.*?)\<\/del\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[s]".$match[1][$key]."[/s]",$str);
    }
	preg_match_all("/\<a[^>]*href\=\\\\\"(.*?)\\\\\"[^>]*>(.*?)\<\/a\>/i", $str, $match);

	foreach($match[0] as $key=>$matchs) {
		$str = str_replace($matchs,"[url=".$match[1][$key]."]".$match[2][$key]."[/url]",$str);
	}	
	preg_match_all("/\<span style=\\\\\"background-color[^<]*\;\\\\\">([^<]*)\<\/span\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,$match[1][$key],$str);
    }
	preg_match_all("/\<span style=\\\\\"font-size\:([^<]*)\;color\:#([[:xdigit:]]{6})\;[^<]*\\\\\">([^<]*)\<\/span\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[color=#".$match[2][$key]."][size=".font_strtosize($match[1][$key])."]".$match[3][$key]."[/size][/color]",$str);
    }
    preg_match_all("/\<span style=\\\\\"font-size\:([^<]*)\;\\\\\">([^<]*)\<\/span\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[size=".font_strtosize($match[1][$key])."]".$match[2][$key]."[/size]",$str);
    }
     preg_match_all("/\<span style=\\\\\"color\:([a-zA-Z]*)\;\\\\\">([^<]*)\<\/span\>/i", $str, $match);
    
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[color=".$match[1][$key]."]".$match[2][$key]."[/color]",$str);
    }  
    preg_match_all("/\<span style=\\\\\"color\:#([[:xdigit:]]{6})\;\\\\\">([^<]*)\<\/span\>/i", $str, $match);
    
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[color=#".$match[1][$key]."]".$match[2][$key]."[/color]",$str);
    }
    preg_match_all("/\<font color=\\\\\"#([[:xdigit:]]{6})\\\\\">([^<]*)\<\/font\>/i", $str, $match);
    
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[color=#".$match[1][$key]."]".$match[2][$key]."[/color]",$str);
    }
   
    preg_match_all("/\<font color=\\\\\"#([[:xdigit:]]{6})\\\\\"\;size=\\\\\"(\d+)\\\\\">([^<]*)\<\/font\>/i", $str, $match);
    
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,"[color=#".$match[1][$key]."][size=".$match[2][$key]."]".$match[3][$key]."[/size][/color]",$str);
    }
    //<span style=\"font-size:large;color:#3366ff;\">

    preg_match_all("/\<ol\>(.*?)\<\/ol\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        preg_match_all("/\<li>(.*?)\<\/li\>/i", $matchs, $match_li);
        $listr = '';
        foreach($match_li[0] as $key_li=>$matchs_li) {
            $listr .= "[*]".$match_li[1][$key_li]."\r\n";
        }
        $str = str_replace($matchs,"[list=1]".$listr."[/list]",$str);
    }
    preg_match_all("/\<ul\>(.*?)\<\/ul\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        preg_match_all("/\<li>(.*?)\<\/li\>/i", $matchs, $match_li);
        $listr = '';
        foreach($match_li[0] as $key_li=>$matchs_li) {
            $listr .= "[*]".$match_li[1][$key_li]."\r\n";
        }
        $str = str_replace($matchs,"[list]".$listr."[/list]",$str);
    }
    preg_match_all("/\<p align\=\\\\\"(.*?)\\\\\"\>(.*?)\<\/p\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        if(in_array($match[1][$key], array('left', 'right', 'center'))) {
            $str = str_replace($matchs,"[align=".$match[1][$key]."]".$match[2][$key]."[/align]",$str);
        } elseif($match[1][$key] == 'justify') {
            $str = str_replace($matchs,"[p=30, 2, left]".$match[2][$key]."[/p]",$str);
            //[p=30, 2, left]auto[/p]
        }
    }
    preg_match_all("/\<h[^>]*\>(.*?)\<\/h[^>]*\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,$match[1][$key],$str);
    }
    preg_match_all("/\<p[^>]*\>(.*?)\<\/p\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,$match[1][$key]."\r\n",$str);
    }

    preg_match_all("/\<span[^>]*\>(.*?)\<\/span\>/i", $str, $match);
    foreach($match[0] as $key=>$matchs) {
        $str = str_replace($matchs,$match[1][$key],$str);
    }
	$str = str_replace("&nbsp;", " ", $str);
	$str = str_replace("<br />", "\r\n", $str);
    $str = strip_tags(stripslashes($str));
	return $str;

}
function bbs_getrelatedinfo($mname, $itemid, $shopid) {
    $relatedarr = getrelatedinfo($mname, $itemid, $shopid);
    $relatedstr = '';
    foreach($relatedarr as $key=>$relatedinfo) {
        $relatedstr .= "[size=3][".b_lang($relatedinfo['type'])."][url=".B_URL."/store.php?id=".$shopid."&action=".$relatedinfo['type']."&xid=".$relatedinfo['itemid']."]".$relatedinfo['simplesubject']."[/url][/size]\r\n";
    }
    return $relatedstr;
}
function bbs_getitemcatids($catid, $mname) {
    $categorylist = getmodelcategory($mname);
    $thiscatid = $catid;
    $catstr = '';
    $key = 0;
    switch($mname) {
        case 'good':
            $filename = 'goodsearch.php';
            break;
        case 'consume';
            $filename = 'consume.php';
            break;
        case 'notice':
            $filename = 'consume.php';
            break;
        case 'album':
            $filename = 'album.php';
            break;
        case 'groupbuy':
            $filename = 'groupbuy.php';
            break;
        default:
            $filename = 'goodsearch.php';
            break;
    }
    do{
        $catarr[$key] = '[url='.B_URL.'/'.$filename.'?catid='.$thiscatid.']'.$categorylist[$thiscatid]['name'].'[/url]';
        $thiscatid = $categorylist[$thiscatid]['upid'];
        $key++;
    } while ($thiscatid > 0);
    krsort($catarr);
    $catstr = implode(" > ", $catarr);
    return $catstr;
}
function bbs_getitemattr($catid, $itemid) {
	require_once( B_ROOT.'/batch.attribute.php');
	return getitemattributes($catid, $itemid);
}

function b_lang($langvar) {
    global $bbs_lang;
    include_once(B_ROOT.'./api/bbs_lang.php');
    $returnvalue = & $bbs_lang;
    $return = $langvar !== null ? (!empty($returnvalue[$langvar]) ? $returnvalue[$langvar] : $langvar) : $langvar;
    return $return;
}
function postformat($mname, $data) {
 	global $_G, $_SC, $_SGLOBAL;
 	$data['shopurl'] = B_URL."/store.php?id=".$_SGLOBAL['panelinfo']['itemid'];
 	$data['shopname'] = $_SGLOBAL['panelinfo']['subject'];
 	$data['sourceurl'] = B_URL."/store.php?id=".$_SGLOBAL['panelinfo']['itemid']."&action=".$mname."&xid=".$data['itemid'];
    $finalmsg = '';
    switch($mname) {
		case 'good':
		    $finalmsg .= "[b][size=3]".b_lang("good_minprice")."[/size]"."[/b][size=3][color=Red]".$data['minprice'].b_lang('rmb_yuan')."[/color][/size]        [size=3][b]".b_lang('good_priceo')."[/b]"."".$data['priceo'].b_lang('rmb_yuan')."[/size]\r\n\r\n";
            $finalmsg .= "[size=3][b]".b_lang("good_subjectimage")."[/b][/size]\r\n[img]".getattachurl($data['subjectimage'])."[/img]\r\n\r\n";
            $finalmsg .= "[size=3][b]".b_lang('good_message')."[/b][/size]\r\n[quote]".bbs_bbcode(bbcode2html($data['message']))."[/quote]\r\n";
            if($relatedmsg = bbs_getrelatedinfo($mname, $data['itemid'], $_SGLOBAL['panelinfo']['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang('item_related')."[/b][/size][/align]".$relatedmsg."\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang('ownedshop')."[/b][url=".$data['shopurl']."]".$data['shopname']."[/url][/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("good_cats")."[/b]".bbs_getitemcatids($data['catid'], $mname)."[/size][/align]\r\n";
            if($attrmsg = bbs_getitemattr($data['catid'], $data['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang("good_attributes")."[/b]".$attrmsg."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("good_validity")."[/b]".date("Y-m-d", $data['validity_start']).b_lang('validityto').date("Y-m-d", $data['validity_end'])."[/size][/align]\r\n";
            break;
        case 'notice':
            if(!empty($data['message']['jumpurl'])) {
                $finalmsg .= "[size=3][b]".b_lang('notice_jumpurl')."[/b][url=".$data['jumpurl']."]".$data['jumpurl']."[/url][/size]\r\n";
            } else {
                $finalmsg .= "[size=3][b]".b_lang("notice_subjectimage")."[/b][/size]\r\n[img]".getattachurl($data['subjectimage'])."[/img]\r\n\r\n";
                $finalmsg .= "[size=3][b]".b_lang('notice_message')."[/b][/size]\r\n[quote]".bbs_bbcode(bbcode2html($data['message']))."[/quote]\r\n";
            }
            $finalmsg .= "[align=left][size=3][b]".b_lang('ownedshop')."[/b][url=".$data['shopurl']."]".$data['shopname']."[/url][/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("notice_cats")."[/b]".bbs_getitemcatids($data['catid'], $mname)."[/size][/align]\r\n";
            if($attrmsg = bbs_getitemattr($data['catid'], $data['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang("good_attributes")."[/b]".$attrmsg."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("notice_validity")."[/b]".date("Y-m-d", $data['validity_start']).b_lang('validityto').date("Y-m-d", $data['validity_end'])."[/size][/align]\r\n";
            break;
        case 'consume':
            $finalmsg .= "[size=3][b]".b_lang("consume_nav")."[/b][/size]\r\n[img]".getattachurl($data['subjectimage'])."[/img]\r\n\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("consume_validity")."[/b]".date("Y-m-d", $data['validity_start']).b_lang('validityto').date("Y-m-d", $data['validity_end'])."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("consume_message")."[/b]".$data['message']."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("consume_exception")."[/b]".$data['exception']."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("consume_tel")."[/b]".$_SGLOBAL['panelinfo']['tel']."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("consume_address")."[/b]".$_SGLOBAL['panelinfo']['address']."[/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang('ownedshop')."[/b][url=".$data['shopurl']."]".$data['shopname']."[/url][/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("consume_cats")."[/b]".bbs_getitemcatids($data['catid'], $mname)."[/size][/align]\r\n";
            if($attrmsg = bbs_getitemattr($data['catid'], $data['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang("good_attributes")."[/b]".$attrmsg."[/size][/align]\r\n";
            break;
        case 'groupbuy':
		    $finalmsg .= "[b][size=3]".b_lang("groupbuy_priceo")."[/size]"."[/b][size=3][color=Red]".$data['groupbuypriceo'].b_lang('rmb_yuan')."[/color][/size]    [size=3][b]".b_lang('group_buyprice')."[/b]"."".$data['groupbuyprice'].b_lang('rmb_yuan')."[/size]    ";
            $finalmsg .= "[size=3][b]".b_lang("groupbuy_validity")."[/b]".date("Y-m-d", $data['validity_start']).b_lang('validityto').date("Y-m-d", $data['validity_end'])."    [url=".$data['sourceurl']."#groupbyjoin"."][color=Red]".b_lang('groupbyjoin')."[/color][/url][/size]\r\n";
            $finalmsg .= "[size=3][b]".b_lang("groupbuy_subjectimage")."[/b][/size]\r\n[img]".getattachurl($data['subjectimage'])."[/img]\r\n\r\n";
            $finalmsg .= "[size=3][b]".b_lang('groupbuy_message')."[/b][/size]\r\n[quote]".bbs_bbcode(bbcode2html($data['message']))."[/quote]\r\n";
            $finalmsg .= "[size=3][url=".$data['sourceurl']."#groupbyjoin"."][color=Red]".b_lang('groupbyjoin')."[/color][/url][/size]\r\n\r\n";
            if($relatedmsg = bbs_getrelatedinfo($mname, $data['itemid'], $_SGLOBAL['panelinfo']['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang('item_related')."[/b][/size][/align]".$relatedmsg."\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang('ownedshop')."[/b][url=".$data['shopurl']."]".$data['shopname']."[/url][/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("groupbuy_cats")."[/b]".bbs_getitemcatids($data['catid'], $mname)."[/size][/align]\r\n";
            if($attrmsg = bbs_getitemattr($data['catid'], $data['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang("good_attributes")."[/b]".$attrmsg."[/size][/align]\r\n";
            break;
        case 'album':
            $finalmsg .= "[size=3][b]".b_lang("album_message")."[/b][/size]".$data['item']['subject']."\r\n\r\n";
            $finalmsg .= "[size=3][b]".b_lang("album_photolist")."[/b][/size]\r\n\r\n";
            if(!empty($data['photolist'])) {
                foreach($data['photolist'] as $photo) {
                    $finalmsg .= "[size=3]".$photo['subject']."[/size]\r\n";
                    $finalmsg .= "[img]".getattachurl($photo['subjectimage'])."[/img]\r\n\r\n";
                }
            }
            $finalmsg .= "[align=left][size=3][b]".b_lang('ownedshop')."[/b][url=".$data['shopurl']."]".$data['shopname']."[/url][/size][/align]\r\n";
            $finalmsg .= "[align=left][size=3][b]".b_lang("album_cats")."[/b]".bbs_getitemcatids($data['item']['catid'], $mname)."[/size][/align]\r\n";
            if($attrmsg = bbs_getitemattr($data['catid'], $data['itemid']))
                $finalmsg .= "[align=left][size=3][b]".b_lang("good_attributes")."[/b]".$attrmsg."[/size][/align]\r\n";
          
            break;
        default:
            break;
    }
    $finalmsg .= "[size=3][b]".b_lang('sourceurl')."[/b][url=".$data['sourceurl']."]".$data['sourceurl']."[/url][/size]";

    return $finalmsg;
}
function font_strtosize($str) {
    $size = 2;
    $size_str = array(
        1 => 'xx-small',
        2 => 'x-small',
        3 => 'small',
        4 => 'medium',
        5 => 'large',
        6 => 'x-large',
        7 => 'xX-large',       
    );
    foreach($size_str as $key=>$value) {
        if($str == $value) {
            $size = $key;
        }
    }
    return $size;
}

function syncpost($itemid, $mname) {
    if(!in_array($mname, array('album', 'good', 'notice', 'consume', 'groupbuy'))) {
        if($mname == 'album') {
            syncalbum($itmeid);
            return false;
        }
    }
	global $_G, $_SC, $_SGLOBAL;
 	$bbs_dbpre = $_SC['bbs_dbpre'];
    $db = new db_mysql(array(
        1 => array(
            'tablepre' => $_SC['bbs_dbpre'],
            'dbcharset' => $_SC['bbs_dbcharset'],
            'dbhost' => $_SC['bbs_dbhost'],
            'dbuser' => $_SC['bbs_dbuser'],
            'dbpw' => $_SC['bbs_dbpw'],
            'dbname' => $_SC['bbs_dbname'],
            'silent' => true,
        )
    ));
    $db->connect();
    $item = DB::fetch_first("SELECT i.*, m.* FROM ".DB::table($mname."items")." i LEFT JOIN ".DB::table($mname."message")." m ON i.itemid = m.itemid WHERE i.itemid = '$itemid' AND i.grade = 3");
 	if(empty($item)) {
        $db->close();
        unset($db);
 	    return false;
 	}
 	getpanelinfo($item['shopid']);
 	$fid = $_SGLOBAL['panelinfo']['syncfid'];
 	if(!checkbbsfid($fid)) {
        $db->close();
        unset($db);
        return false;
    } 	
 	//插入主题信息
    $author = $_SGLOBAL['panelinfo']['username'];
    $authorid = $_SGLOBAL['panelinfo']['uid'];
    $subject = "[".b_lang($mname)."]".$item['subject'];
 	$message = postformat($mname, $item);
    $posttable_info = $db->result_first("SELECT svalue FROM {$bbs_dbpre}common_setting WHERE skey = 'posttable_info'");
    $posttableid = 0;
    if(!empty($posttable_info)) {
        $posttable_info = unserialize($posttable_info);
        if(is_array($posttable_info)) {
            foreach($posttable_info as $key=>$info) {
                if($info['type'] == 'primary') {
                    $posttableid = $key;
                }
            }
        }
    }
    if(!$posttableid) {
		$tablename = 'forum_post';
	} else {
		$tablename = "forum_post_$posttableid";
	}
	if(empty($item['bbstid'])) {
    
        $db->query("INSERT INTO {$bbs_dbpre}forum_thread (fid, posttableid, author, authorid, subject, dateline, lastpost, lastposter)
        VALUES ('$fid', '$posttableid', '$author', '$authorid', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author')");
        $tid = $db->insert_id();
        $db->query("UPDATE {$bbs_dbpre}common_member_field_home SET recentnote = '$subject' WHERE uid = '$authorid'");
    
        $db->query("INSERT INTO {$bbs_dbpre}forum_post_tableid (pid) values (null)");
        $pid = $db->insert_id();
        if($pid % 1024 == 0) {
            $db->query("DELETE FROM {$bbs_dbpre}forum_post_tableid WHERE pid<$pid");
        }
        $db->query("REPLACE INTO {$bbs_dbpre}common_syscache (cname, ctype, dateline, data) VALUES ('max_post_id', '0', '$_G[timestamp]', '$pid')");
        if(!$posttableid) {
            $tablename = 'forum_post';
        } else {
            $tablename = "forum_post_$posttableid";
        }
        $db->query("INSERT INTO {$bbs_dbpre}{$tablename} SET `fid`='$fid',`tid`='$tid',`first`='1',`author`='$author',`authorid`='$authorid',`subject`='$subject',`dateline`='$_G[timestamp]',`message`='$message ',`useip`='unknown',`invisible`='0',`anonymous`='0',`usesig`='1',`htmlon`='0',`bbcodeoff`='0',`smileyoff`='-1',`parseurloff`='',`attachment`='0',`tags`='',`pid`='$pid'");
        $db->query("UPDATE {$bbs_dbpre}forum_forum SET lastpost='{$tid} {$subject} {$_G[timestamp]} {$author}', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'");
        $db->query("UPDATE {$bbs_dbpre}common_stat SET `thread`=`thread`+1 WHERE daytime='".date("Ymd", $_G[timestamp])."'");
		
		updatetable($mname.'items', array('bbstid'=>$tid), array('itemid'=>$item['itemid']));
    } else {
        $tid = $item['bbstid'];
        $db->query("UPDATE {$bbs_dbpre}forum_thread SET subject='".$subject."' WHERE tid='$tid'");
        $pid = $db->result_first("SELECT pid FROM {$bbs_dbpre}{$tablename} WHERE tid = '$tid' AND first = 1");
    	$db->query("UPDATE {$bbs_dbpre}{$tablename} SET message='$message' WHERE pid='$pid' AND tid = '$tid' AND first = 1");
    }
}
?>