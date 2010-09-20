<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sidebar_consume.html.php 4367 2010-09-08 03:00:11Z fanshengshuai $
 */-->

<!--{if is_array($_G['brandads']['sidebarconsumearr'])}-->
<div class="consumer_hot box">
	<h3>$lang['hotcoupon']</h3>
	<!--{loop $_G['brandads']['sidebarconsumearr'] $tmpid}-->
	<!--{eval $tmparr = $_BCACHE->getiteminfo('consume', $tmpid['itemid'], $tmpid['shopid'])}-->
	<!--{if !empty($tmparr['itemid'])}-->
	<dl>
		<dt><a target="_blank" href="store.php?id={$tmparr['shopid']}&action=consume&xid={$tmparr['itemid']}"><img alt="" src="{$tmparr['thumb']}" border="0" width="192" height="119"></a></dt>
		<dd>
			<h4><a title="" href="store.php?id={$tmparr['shopid']}&action=consume&xid={$tmparr['itemid']}">{$tmparr['subject']}</a></h4>
			<p>{$lang['deadline']}{$lang['colon']} #date("Y-m-d", $tmparr['validity_start'])# $lang['to'] #date("Y-m-d", $tmparr['validity_end'])#</p>
		</dd>
	</dl>
	<!--{/if}-->
	<!--{/loop}-->
</div>
<!--{/if}-->
<!--{eval unset($tmparr, $tmpads, $tmpid);}-->
