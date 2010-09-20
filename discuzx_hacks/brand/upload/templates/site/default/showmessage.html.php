<?exit?>
<!--{if empty($_G['inajax'])}-->
<!--{eval $seo_title = $lang['showmessage'];}-->
<!--{template 'templates/site/default/header.html.php', 1}-->

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: showmessage.html.php 4354 2010-09-07 01:35:17Z fanshengshuai $
 */-->

<div id="showmessage">
	<div class="mtitle">$lang['showmessage']</div>
	<div class="mcont">
		<div class="mp">
			<!--{/if}-->
			$message
			<!--{if $GLOBALS['ucsynlogin']}-->{$GLOBALS['ucsynlogin']}<!--{/if}-->
			<!--{if empty($_G['inajax'])}-->
			$error_details
		</div>
		<!--{if $url_forward}-->
		<a href="$url_forward">$lang['confirm']</a>
		<!--{else}-->
		<a href="javascript:history.back();">$lang['goback']</a>
		<!--{/if}-->
		<div class="mpad">&nbsp;</div>
	</div>
	<div class="mbot">&nbsp;</div>
</div>
<!--{template 'templates/site/default/footer.html.php', 1}-->
<!--{/if}-->