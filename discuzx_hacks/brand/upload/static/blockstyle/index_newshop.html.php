<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index_newshop.html.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */-->

<!--{if is_array($iarr)}-->
<h3>$lang['latestshop']</h3>
<!--{loop $iarr $ikey $value}-->
	<!--{eval $value = $_BCACHE->getshopinfo($value['itemid'])}-->
	<!--{if !empty($value['itemid'])}-->
	<dl>
		<dt><a href="store.php?id={$value['itemid']}" target="_blank"><img width="60" height="40" src="{$value['thumb']}" alt=""> </a></dt>
		<dd>
			<h5 class="f_red"><a href="store.php?id={$value['itemid']}" target="_blank">{$value['subject']}</a></h5>
			<p>$lang['category']:<a href="street.php?catid={$value['catid']}">{$_G['categorylist'][$value['catid']]['name']}</a></p>
		</dd>
	</dl>
	<!--{/if}-->
<!--{/loop}-->
<!--{/if}-->