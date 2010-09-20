<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: js_good_li.html.php 3816 2010-07-20 08:34:33Z yexinhao $
 */-->

<!--站外js调用模块样式-->
<!--{if $iarr}-->
<div id="productlist">
	<ul class="productlist" style="display: block;">
		<!--{loop $iarr $ikey $value}-->
		<li>
			<a href="{B_URL}/store.php?id={$value['shopid']}&action=good&xid={$value['itemid']}" target="_blank"><img alt="" src="{$value['thumb']}"></a>
			<p><a href="{B_URL}/store.php?id={$value['shopid']}&action=good&xid={$value['itemid']}" target="_blank">{$value['subject']}</a></p>
			<p class="price">{$lang['price']}{$lang['colon']} {$lang['rmb']}{$value['minprice']}<!--{if $value['maxprice']>0}--> - {$value['maxprice']}<!--{/if}--></p>
		</li>
		<!--{/loop}-->
	</ul>

	<div class="more" id="moreGoodsLink"><a href="goodsearch.php">$lang['viewall']</a></div>
</div>
<!--{/if}-->
