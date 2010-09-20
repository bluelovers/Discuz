<?exit?>

<!--/**
*      [品牌空間] (C)2001-2010 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: album.html.php 4359 2010-09-07 07:58:57Z fanshengshuai $
*/-->

<!--{if empty($_GET['xid'])}-->
<div id="store_album_list" class="main layout store_list">
	<div class="store content">
		<h3>{$lang['albumscan']}</h3>
		<ul id="albumlist">
			<!--{loop $albumlist $value}-->
			<li>
			<a href="$value['url']"><img src="$value['thumb']" /></a>
			<div class="album_desc">$value['subject']</div>
			</li>
			<!--{/loop}-->
		</ul>
		$album_multipage
	</div>
<!--{else}-->
<div class="main layout">
	<div class="store_detail content">
		<!--{if $_G['uid'] && !$_G['myshopid'] && !ckfounder($_G['uid'])}-->
			<span style="color: font-size: 12px; margin-left: 700px; margin-top: 5px; cursor: pointer; position: absolute;" onclick="report('album', '{$_GET[xid]}');">$lang['report']</span>
		<!--{/if}-->
		<h3>{$lang['photoscan']}</h3>
		<div id="targetpic" class="targetpic">
			<div class="targetpic_prev"><a href="#" title="$lang['prev']">$lang['prev']</a></div>
			<div class="targetpic_main">
				<!--{if $photolist}-->
				<a href="javascript:void(0)"><img onload="if(this.width>320){this.height=this.height*(320/this.width);this.width=320;}" id="tarpic" onclick="window.open($('.goodlist .curr_pic').find('img').attr('bigimg'))" src="{$photolist[0]['thumb']}" alt=""></a>
				<p>{$photolist[0]['subject']}&nbsp;</p>
				<div><a href="javascript:void(0)"><img id="show_fullpic" onclick="window.open($('.goodlist .curr_pic').find('img').attr('bigimg'))" src="static/image/enlarge_pic.gif" alt="{$lang['originalphoto']}" width="135" height="33"></a></div>
				<!--{else}-->
				<a href="#"><img src="static/image/nophoto.gif" alt=""></a>
				<p>$lang['nopic']</p>
				<!--{/if}-->
			</div>
			<div class="targetpic_next"><a href="#" title="$lang['next']">$lang['next']</a></div>
		</div>

		<div id="album_show_thumb_panel">
			<div class="prev"><a href="javascript:void(0);" title="$lang['prevgroup']">$lang['prevgroup']</a></div>
			<div class="thumb_pic_list">
				<ul class="goodlist">
					<!--{if $photolist}-->
					<!--{loop $photolist $key $photo}-->
					<li class="<!--{if $key == 0}-->curr_pic<!--{/if}-->">
					<a href="javascript:void(0);"><img width="100" height="74" src="{$photo['thumb']}" bigimg="{$photo['subjectimage']}" midimg="{$photo['thumb']}" disp="{$photo['subject']}" alt=""></a>
					<p>{$photo['subject']}</p>
					</li>
					<!--{/loop}-->
					<!--{/if}-->
				</ul>
			</div>
			<div class="next"><a href="javascript:void(0);" title="$lang['nextgroup']">$lang['nextgroup']</a></div>
		</div>

	</div>
	<!--{if $photolist}--><script charset="utf8" type="text/javascript" src="static/js/album_photo_list.js"></script><!--{/if}-->
<!--{/if}-->
<!--{if !$shop_close}-->
<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->
<!--{else}-->
<div class="aboutbrand">
	<ul class="tips">
		<li class="tit">{$lang['sitetel']}{$lang['colon']}</li>
		<li class='info'>{$_G['setting']['sitetel']}</li>
		<li class="tit">{$lang['siteqq']}{$lang['colon']}</li>
		<li class='info'>{$_G['setting']['siteqq']}</li>
	</ul>
</div>
<!--{/if}-->
</div>
<div id="append_parent"></div>