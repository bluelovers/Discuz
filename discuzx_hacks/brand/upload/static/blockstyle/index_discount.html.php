<?exit?>

<div class="card">
	<h3><a href="card.php" target="_blank">$lang['discountshop']</a></h3>
	<span class="more"><a href="card.php" target="_blank">$lang['more']</a></span>
	<div class="woaixiaofei"><div class="fix"></div>
		<!--{if $_G['brandads']['discountarr']}-->
		<!--{loop $_G['brandads']['discountarr'] $tmpid}-->
		<!--{eval $tmparr = $_BCACHE->getshopinfo($tmpid['itemid'])}-->
		<!--{if !empty($tmparr['itemid'])}-->
		<dl>
			<dt><a href="store.php?id={$tmparr['itemid']}" target="_blank"><img width="100" height="80" src="{$tmparr['thumb']}" alt="{$tmparr['subject']}"></a> </dt>
			<dd>
			<h4><a href="store.php?id={$tmparr['itemid']}" target="_blank">{$tmparr['subject']}</a></h4>
			<p>{eval echo cutstr($tmparr['discount'], 18);}</p>
			<a href="store.php?id={$tmparr['itemid']}" target="_blank" class="btn">$lang['gogogo']</a>
			</dd>
		</dl>
		<!--{/if}-->
		<!--{/loop}-->
		<!--{else}-->
		<dl style="background:none repeat scroll 0 0 #FFFFFF;"></dl>
		<!--{/if}-->
		<div class="fix"></div><div class="woaixiaofei-xia"></div>
	</div>
</div>
<!--{eval unset($tmparr, $tmpads, $tmpid);}-->