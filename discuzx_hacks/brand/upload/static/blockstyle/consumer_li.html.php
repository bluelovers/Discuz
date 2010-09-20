<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: consumer_li.html.php 4375 2010-09-09 01:12:51Z yanghuan $
 */-->

<!--{if $iarr}-->
	<!--{loop $iarr $key $value}-->
	<dl style="background-position: 0pt 0pt;">
    	<dt>
			<!--{if $value['validity_end'] > $_G['timestamp'] && $value['validity_start'] < $_G['timestamp']}-->
			<span class="ineffect"></span>
			<!--{elseif $value['validity_end'] < $_G['timestamp']}-->
			<span class="expire"></span>
			<!--{/if}-->
			<a target="_blank" href="store.php?id={$value['shopid']}&action=consume&xid={$value['itemid']}"><img width="140" height="105" src="{$value['thumb']}" alt=""></a>
		</dt>
		<dd>
			<h4><a target="_blank" href="store.php?id={$value['shopid']}&action=consume&xid={$value['itemid']}" title="">{$value['subject']}</a></h4>
			<ul>
				<li>$lang['groupbuystarttime']{$lang['colon']} #date("Y-m-d", $value['validity_start'])#</li>
				<li>$lang['groupbuyendtime']{$lang['colon']} #date("Y-m-d", $value['validity_end'])#</li>
				<li>{$lang['viewnum']}{$lang['colon']} {$value['viewnum']} {$lang['viewnumunit']}</li>
				<li><a target="_blank" href="store.php?id={$value['shopid']}&action=consume&xid={$value['itemid']}">$lang['viewdetail']</a></li>
			</ul>
		</dd>
	</dl>
	<!--{/loop}-->
	<div id="moreConsumeLink" class="more"><a href="store.php?id={$_SGLOBAL['shopid']}&action=consume">$lang['viewall']</a></div>
<!--{/if}-->