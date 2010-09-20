<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index_hotshop.html.php 4367 2010-09-08 03:00:11Z fanshengshuai $
 */-->

<h3>$lang['hotshop']</h3>
<div class="hotbrand">
	<!--{eval $tmpnum=1}-->
	<!--{loop $_G['brandads']['hotshoparr'] $tmpid}-->
	<!--{eval $tmparr = $_BCACHE->getshopinfo($tmpid['itemid'])}-->
	<!--{if !empty($tmparr['itemid'])}-->
	<dl {if $tmpnum == 1} class="mouseover" {/if}>
		<dt><a href="store.php?id={$tmparr['itemid']}" target="_blank"><img width="53" height="34" alt="" src="{$tmparr['thumb']}"></a></dt>
		<dd class="n_{$tmpnum}">
			<sub>$tmpnum</sub>
			<h4><a href="store.php?id={$tmparr['itemid']}" target="_blank">{$tmparr['subject']}</a></h4>
			<p>$lang['category']:<a href="street.php?catid={$tmparr['catid']}">{$_G['categorylist'][$tmparr['catid']]['name']}</a></p>
		</dd>
	</dl>
	<!--{eval $tmpnum++;}-->
	<!--{/loop}-->
	<!--{/if}-->
</div>
<!--{eval unset($tmparr, $tmpads, $tmpid, $tmpnum);}-->