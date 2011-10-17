<?php
//--------------------------------------------------------------------------------------------------------------------------------
//  附件流量,在 \source\function\function_post.php 的196行后用include插入数据
//243			DB::delete('forum_attachment_unused', "aid='$aid'");
//244			include DISCUZ_ROOT . "source/plugin/attdownbysize/function_post_attsizeforauthor.php";
//--------------------------------------------------------------------------------------------------------------------------------
	if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
	}
 
	include "config.php";
 
	if ($newattach && $AttachDownBysizeConfig['att_kg'])
	{
		$extcreditkb=  $AttachDownBysizeConfig['att_kb'];
		$att_exts	=  $AttachDownBysizeConfig['att_exts'];
		$att_extArr =  explode(',', $att_exts);

		$att_query  = DB::query("SELECT filename,filesize FROM ".DB::table('forum_attachment'). "_" . getattachtableid($tid) ." WHERE aid='$aid'");
		
		
		$att_one    = DB::fetch($att_query);
		$upmax		= $AttachDownBysizeConfig['att_upmax']; 
		$flsize     = intval( $att_one['filesize'] / 1024 ) * $AttachDownBysizeConfig['att_uptime'] * 0.01;
		$upsize     = $upmax>0 ? min($upmax, $flsize): $flsize;
		$flname     = $att_one['filename'];
		$flexts     = explode('.' , $flname); 
		$flext      = strtolower(end($flexts)); 
 
		if (!in_array($flext, $att_extArr))
		{
			DB::query("UPDATE ".DB::table('common_member_count')." SET $extcreditkb=$extcreditkb+'$upsize' WHERE uid='$uid'", 'UNBUFFERED');
		}
	}

?>


