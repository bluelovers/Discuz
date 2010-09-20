<?exit?>

<div class="groupbuy">
	<h3><a href="groupbuy.php">$lang['hotgroupbuy']</a></h3>
	<p class="introduce">$lang['hotgroupbuytips']</p>
	<span class="more"><a href="groupbuy.php">$lang['more']</a></span>
	<div class="woaixiaofei">
		<!--{if $_G['brandads']['groupbuyarr']}-->
		<!--{loop $_G['brandads']['groupbuyarr'] $tmpid}-->
		<!--{eval $tmparr = $_BCACHE->getiteminfo('groupbuy', $tmpid['itemid'], $tmpid['shopid'])}-->
		<!--{if !empty($tmparr['itemid'])}-->
		<dl class="">
			<dt>
			<!--{if $tmparr['validity_end'] > $_G['timestamp'] && $tmparr['validity_start'] < $_G['timestamp']}-->
			<span class="ineffect">$lang['valid']</span>
			<!--{elseif $tmparr['validity_end'] < $_G['timestamp']}-->
			<span class="expire">$lang['invalid']</span>
			<!--{/if}-->
			<a target="_blank" href="store.php?id={$tmparr['shopid']}&action=groupbuy&xid={$tmparr['itemid']}"><img src="{$tmparr['thumb']}" alt="" width="192" height="120"></a>
			</dt>
			<dd>
			<h4><a href="store.php?id={$tmparr['shopid']}&action=groupbuy&xid={$tmparr['itemid']}" title="">{$tmparr['subject']}</a></h4>
			<p>{$lang['deadline']}{$lang['colon']} #date('Y-m-d', $tmparr['validity_start'])# $lang['to'] #date('Y-m-d', $tmparr['validity_end'])#</p>
			</dd>
		</dl>
		<!--{/if}-->
		<!--{/loop}-->
		<!--{else}-->
		<div style="height:180px;"></div>
		<!--{/if}-->
		<div class="fix"></div>
		<div class="woaixiaofei-xia"></div>
	</div>
</div>
	<!--{eval unset($tmparr, $tmpads, $tmpid);}-->