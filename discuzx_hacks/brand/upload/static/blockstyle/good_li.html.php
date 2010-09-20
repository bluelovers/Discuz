<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: good_li.html.php 3814 2010-07-20 08:16:05Z yexinhao $
 */-->

<!--{if $iarr}-->
<div id="productlist">
	<ul class="productlist" style="display: block;">
		<!--{loop $iarr $ikey $value}-->
		<li>
			<a href="store.php?id={$_SGLOBAL['shopid']}&action=good&xid={$value['itemid']}" target="_blank"><img alt="" src="{$value['thumb']}"></a>
			<p><a href="store.php?id={$_SGLOBAL['shopid']}&action=good&xid={$value['itemid']}" target="_blank">{$value['subject']}</a></p>
			<p class="price">{$lang['price']}{$lang['colon']} {$lang['rmb']}{$value['minprice']}<!--{if $value['maxprice']>0}--> - {$value['maxprice']}<!--{/if}--></p>
		</li>
		<!--{/loop}-->
	</ul>

	<!-- 新增选项卡内容区 -->
	<div class="more" id="moreGoodsLink"><a href="store.php?id={$_SGLOBAL['shopid']}&action=good">更多&gt;&gt;</a></div>
</div>
<!--{/if}-->
