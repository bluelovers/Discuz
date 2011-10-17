<?php
	if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
	}
	include "config.php";

	if ( $AttachDownBysizeConfig['att_kg'] )
	{
		$att_downloads = $AttachDownBysizeConfig['att_downTime'];
		$dz_downloads  = $att_downloads>0 ? DB::result_first("SELECT downloads FROM ".DB::table('forum_attachment')." where aid='$attach[aid]'") : -1;
		
		if($dz_downloads <= $att_downloads){
			$extcreditkb  =  $AttachDownBysizeConfig['att_kb']; 				//启用字段
			$authorfactor =  $AttachDownBysizeConfig['att_upnext'] * 0.01;  	//作者分成(初始值)
			$payfactor	  =  $AttachDownBysizeConfig[$_G['groupid']] * 0.01;	//支付流量(初始值)
			$paymax		  =  $AttachDownBysizeConfig['att_paymax']; 
			$attsize      =  intval($attach['filesize'] / 1024); 	//附件大小,使用K单位表示
			$paysize      =  $paymax>0 ? min($paymax, $attsize) : $attsize;
			$userhave     =  getuserprofile($extcreditkb);		//用户拥有流量
			$paysize 	  =  intval($paysize * $payfactor);		//支付流量
			$authorsize   =  intval($paysize * $authorfactor);	//作者收益
			 
			if ($userhave < $paysize)
			{
				showmessage("您没有足够的流量支付下载!请兑换成流量后下载<p><a href=home.php?mod=spacecp&ac=credit&op=exchange>开始兑换!</a></p>");
			}
			else
			{
				DB::query("UPDATE ".DB::table('common_member_count')." SET $extcreditkb=$extcreditkb-'$paysize' WHERE uid='$_G[uid]'", 'UNBUFFERED');//用户支出
				DB::query("UPDATE ".DB::table('common_member_count')." SET $extcreditkb=$extcreditkb+'$authorsize' WHERE uid='$attach[uid]'", 'UNBUFFERED');//奖励给附件作者
			}
		}
	}
?>