<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sidebar_shop.html.php 4367 2010-09-08 03:00:11Z fanshengshuai $
 */-->

<!--{if is_array($_G['brandads']['sidebarshoparr'])}-->
<div class="box hot">
	<h3>$lang['recommendshop']</h3>
	<ol>
	<!--{loop $_G['brandads']['sidebarshoparr'] $tmpid}-->
	<!--{eval $tmparr = $_BCACHE->getshopinfo($tmpid['itemid'])}-->
	<!--{if !empty($tmparr['itemid'])}-->
		<li><a href="store.php?id={$tmparr['itemid']}" target="_blank">{$tmparr['subject']}</a></li>
	<!--{/if}-->
	<!--{/loop}-->
	</ol>
</div>
<!--{/if}-->
<!--{eval unset($tmparr, $tmpads, $tmpid, $tmpnum);}-->
