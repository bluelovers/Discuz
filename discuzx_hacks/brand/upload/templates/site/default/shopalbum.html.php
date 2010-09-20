<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: shopalbum.html.php 4419 2010-09-13 09:47:56Z fanshengshuai $
 */-->

<div class="cont">
	<div class="box">
		<h3 class="shop_album_title">$lang['shopinfo']</h3>
		<ul class="hhhh"  id="shop_album_info">
			<h4>$shopinfo['subject']</h4>
			<div class="xingji">
			<!--{if $shopinfo['roundingscore']}-->
				<img src="static/image/{$shopinfo['roundingscore']}_big.png" style="vertical-align: middle;">
				<font color="red" style="font-size: 14px; vertical-align: middle;">$shopinfo['score']</font>
			<!--{/if}-->
			</div>
			<li class="c_dgreen"><span>$lang['address']</span>$shopinfo['address']</li>
			<li class="c_dgreen"><span>$lang['telephone']</span>$shopinfo['tel']</li>
			<li class="c_dgreen"><span>{$lang['shopdescription']}{$lang['colon']}</span><!--{eval echo cutstr($shopinfo['message'], 50, $havedot=1);}--></li>
			<!--{if $shopinfo['isdiscount'] == 1}-->
			<li class="c_dgreen"><span>{$lang['shopcard']}{$lang['colon']}</span><img src="static/image/huiyuan-card.gif" style="margin-left:-15px;"></li>
			<li class="c_dgreen"><span style="float:left;">{$lang['disc']}{$lang['colon']}</span><div style="float:left; text-indent:0;">$shopinfo['discount']</div></li>
			<!--{/if}-->
		</ul>
		<div style="clear:both; height:20px;"></div>
	</div>
	<ul id="shop_album_list">
		<!--{loop $albumlist $value}-->
		<li>
		<a href="store.php?id={$value['shopid']}&action=album&xid={$value['itemid']}" target="_blank"><img src="{$value['thumb']}"/></a>
		<div class="album_desc">$value['subject']</div>
		</li>
		<!--{/loop}-->
	</ul>
</div>