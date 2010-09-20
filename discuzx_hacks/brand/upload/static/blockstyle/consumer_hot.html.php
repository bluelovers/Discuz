<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: consumer_hot.html.php 3817 2010-07-20 08:45:12Z yexinhao $
 */-->

<!--{if $iarr}-->
<div class="consumer_hot box">
	<h3>$lang['newcoupon']</h3>
	<!--{loop $iarr $key $value}-->
	<dl>
		<dt><a target="_blank" href="store.php?id={$value['shopid']}&action=consume&xid={$value['itemid']}"><img alt="" src="{$value['thumb']}" border="0" width="192" height="120"></a></dt>
		<dd>
			<h4><a title="" href="store.php?id={$value['shopid']}&action=consume&xid={$value['itemid']}">{$value['subject']}</a></h4>
			<p>{$lang['deadline']}{$lang['colon']}<!--{eval echo date("Y-m-d", $value['validity_start']);}--> $lang['to'] <!--{eval echo date("Y-m-d", $value['validity_end']);}--></p>
		</dd>
	</dl>
	<!--{/loop}-->
</div>
<!--{/if}-->
