<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sidebar_groupbuy.html.php 4367 2010-09-08 03:00:11Z fanshengshuai $
 */-->

<!--{if is_array($_G['brandads']['sidebargroupbuyarr'])}-->
<div class="consumer_hot box">
	<h3>$lang['hotgroupbuy']</h3>
	<!--{loop $_G['brandads']['sidebargroupbuyarr'] $tmpid}-->
	<!--{eval $tmparr = $_BCACHE->getiteminfo('groupbuy', $tmpid['itemid'], $tmpid['shopid'])}-->
	<!--{if !empty($tmparr['itemid'])}-->
	<dl>
		<dt><a target="_blank" href="store.php?id={$tmparr['shopid']}&action=groupbuy&xid={$tmparr['itemid']}"><img alt="" src="{$tmparr['thumb']}" border="0" width="192" height="119"></a></dt>
		<dd>
			<h4><a title="" href="store.php?id={$tmparr['shopid']}&action=groupbuy&xid={$tmparr['itemid']}">{$tmparr['subject']}</a></h4>
			<p>$lang['price_old'] {$lang['rmb']}{eval echo round($tmparr['groupbuyprice']);}  $lang['price_now'] {$lang['rmb']}{eval echo round($tmparr['groupbuypriceo']);}</p>
		</dd>
	</dl>
	<!--{/if}-->
	<!--{/loop}-->
</div>
<!--{/if}-->
<!--{eval unset($tmparr, $tmpads, $tmpid);}-->