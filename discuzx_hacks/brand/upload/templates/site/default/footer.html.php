<?exit?>
<div class="c"></div>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: footer.html.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */-->

<div id="bottomInfo">
	<div class="bottomInfo">
		{$lang['sitetel']}{$lang['colon']} $_G['setting']['sitetel'] |
		{$lang['siteqq']}{$lang['colon']} $_G['setting']['siteqq'] |
		<!--{if $_G['setting']['miibeian']}--><a href="http://www.miibeian.gov.cn" target="_blank">$_G['setting']['miibeian']</a><!--{/if}-->
	</div>
	<div class="bottomcopyright">
		<a href="$_G['setting']['wwwurl']">$_G['setting']['wwwname']</a> $lang['copyright'] Powered by <a href="http://www.comsenz.com" target="_blank">Comsenz Inc. </a><br /><!--{eval debuginfo();}-->
	</div>
</div>
<div style="display:none;">$_G['setting']['analytics']</div>
</body>
</html>