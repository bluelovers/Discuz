<?exit?>
<div class="consumer">
	<h3><a href="consume.php">$lang['hotgoods']</a></h3>
	<p class="introduce">$lang['hotgoodstips']</p>
	<span class="more"><a href="goodsearch.php">$lang['more']</a></span>
	<div class="woaixiaofei"><div class="fix"></div>
		<!--{if $_G['brandads']['hotgoodsarr']}-->
		<!--{loop $_G['brandads']['hotgoodsarr'] $tmpid}-->
		<!--{eval $__item = $_BCACHE->getiteminfo('good', $tmpid['itemid'] , $tmpid['shopid'])}-->
		<!--{if !empty($__item['itemid'])}-->
		<dl class="">
			<dt>
			<a target="_blank" href="store.php?id={$__item['shopid']}&action=good&xid={$__item['itemid']}"><img src="{$__item['thumb']}" alt="{$__item['subject']}" width="192" height="120"></a>
			</dt>
			<dd>
			<h4><a href="store.php?id={$__item['shopid']}&action=good&xid={$__item['itemid']}" title="{$__item['subject']}">{$__item['subject']}</a></h4>
			<p class="price">{$lang['price']}{$lang['colon']} {$lang['rmb']}{$__item['minprice']}<!--{if $__item['maxprice'] > 0}--> - {$__item['maxprice']}<!--{/if}--></p>
			</dd>
		</dl>
		<!--{/if}-->
		<!--{/loop}-->
		<!--{else}-->
		<div style="height:180px;"></div>
		<!--{/if}-->
		<div class="fix"></div><div class="woaixiaofei-xia"></div>
	</div>
</div>

<!--{eval unset($__item, $tmpads, $tmpid);}-->