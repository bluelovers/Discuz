<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: groupbuy_hot.html.php 3776 2010-07-16 08:21:35Z yexinhao $
 */-->

<!--{if $iarr}-->
<div class="consumer_hot box">
	<h3>{$lang['groupbuy_new']}</h3>
	<!--{loop $iarr $key $value}-->
	<dl>
		<dt><a target="_blank" href="store.php?id={$value['shopid']}&action=groupbuy&xid={$value['itemid']}"><img alt="" src="{$value['thumb']}" border="0" width="192" height="120"></a></dt>
		<dd>
			<h4><a title="" href="store.php?id={$value['shopid']}&action=consume&xid={$value['itemid']}">{$value['subject']}</a></h4>
			<p>{$lang['price_old']} {$lang['rmb']}{$value['groupbuyprice']} &nbsp; &nbsp; {$lang['price_now']} {$lang['rmb']}{$value['groupbuypriceo']}</p>
		</dd>
	</dl>
	<!--{/loop}-->
</div>
<!--{/if}-->