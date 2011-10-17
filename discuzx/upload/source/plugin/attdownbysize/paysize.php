<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
 
include "config.php";
if(!isset($attach[filesize]) || !isset($attach[filename]))
{
	$_G['gp_aid']=$_G['gp_aid']?$_G['gp_aid']:$_G['gp_amp;aid'];
	@list($aid) = explode('|', base64_decode($_G['gp_aid']));
	$attach = DB::fetch_first("SELECT filename,filesize FROM ". DB::table('forum_attachment'). "_" . getattachtableid($tid) ." WHERE aid='$aid'");
}

$filename = $attach[filename];
$filesize = sizecount($attach[filesize]);
$paysize  = $AttachDownBysizeConfig['att_paymax']>0 ? max($AttachDownBysizeConfig['att_paymax'], intval($attach['filesize'] / 1024)) : intval($attach['filesize'] / 1024);
$userhave = sizecount(getuserprofile( $AttachDownBysizeConfig[att_kb]) * 1024);		//用户拥有流量
?>