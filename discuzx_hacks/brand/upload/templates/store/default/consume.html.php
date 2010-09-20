<?exit?>

<!--/**
*      [品牌空间] (C)2001-2010 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: consume.html.php 4406 2010-09-13 07:48:43Z fanshengshuai $
*/-->

<!--{if empty($_GET['xid'])}-->
<div id="consumerlist" class="main layout store_list">
	<div class="content">
		<h3>{$lang['couponlist']}</h3>
		<div class="all_consumer">
			<!--{loop $consumelist $consume}-->
			<dl class="">
				<dt>
				<!--{if $consume['validity_end'] > $_G['timestamp'] && $consume['validity_start'] < $_G['timestamp']}-->
				<span class="ineffect">$lang['valid']</span>
				<!--{elseif $consume['validity_end'] < $_G['timestamp']}-->
				<span class="expire">$lang['invalid']</span>
				<!--{/if}-->

				<a href="store.php?id={$shop['itemid']}&action=consume&xid={$consume['itemid']}" target="_blank"><img border="0" src="{$consume['thumb']}" alt="" width="192" height="120"></a></dt>
				<dd>
				<h4><a href="store.php?id={$shop['itemid']}&action=consume&xid={$consume['itemid']}" title="" target="_blank">{$consume['subject']}</a></h4>
				<p>{$lang['deadline']}{$lang['colon']} #date('Y-m-d', $consume['validity_start'])# $lang['to'] #date('Y-m-d', $consume['validity_end'])#</p>
				<p>{$lang['promulgator']}{$lang['colon']}<a href="store.php?id={$shop['itemid']}" target="_blank">{$shop['subject']}</a></p>
				<p>{$lang['viewnum']}{$lang['colon']} {$consume['viewnum']}{$lang['viewnumunit']}</p>
				</dd>
			</dl>
			<!--{/loop}-->
			<div class="c h10"></div>
		</div>
		$consumelist_multipage
	</div>

	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->

</div>
<!--{else}-->
<div id="consumer" class="main layout">
	<div class="content">
		<!--{if $_G['uid'] && !$_G['myshopid'] && !ckfounder($_G['uid'])}-->
		<span style="color: font-size: 12px; margin-left: 700px; margin-top: 5px; cursor: pointer; position: absolute;" onclick="report('consume', '{$consume['itemid']}');">$lang['report']</span>
		<!--{/if}-->
		<h3>{$consume['subject']}</h3>
		<dl class="print_img">
			<dt>
			<!--{if $consume['validity_end'] > $_G['timestamp'] && $consume['validity_start'] < $_G['timestamp']}-->
			<em class="ineffect">$lang['valid']</em>
			<!--{elseif $consume['validity_end'] < $_G['timestamp']}-->
			<em class="expire">$lang['invalid']</em>
			<!--{/if}-->
			<span><img alt="" width="555" height="345" src="{$consume['subjectimage']}" border="0"></span>
			</dt>
			<dd>
			<p>$lang['beforeyou']<strong>{$consume['viewnum']}</strong>$lang['cviews']
			<strong>{$consume['downnum']}</strong> $lang['cdowns']</p>
			<a class="print" target="_blank" title="$lang['print']" href="store.php?id={$shop['itemid']}&action=consume&xid={$consume['itemid']}&do=print">$lang['print']</a>
			</dd>
			<dd></dd>
		</dl>
		<ul class="cons_intro">
			<li><span>{$lang['consume_message']}{$lang['colon']}</span>{$consume['message']}</li>
			<li><span>{$lang['consume_exception']}</span>{$consume['exception']}</li>
			<li><span>{$lang['deadline']}{$lang['colon']}</span><strong>#date('Y-m-d', $consume['validity_start'])# {$lang['to']} #date('Y-m-d', $consume['validity_end'])#</strong></li>
			<li><span>{$lang['promulgator']}{$lang['colon']}</span><a target="_blank" href="store.php?id={$shop['itemid']}">{$shop['subject']}</a></li>
			<li><span>{$lang['dateline']}{$lang['colon']}</span>#date('Y-m-d', $consume['dateline'])#</li>
			<li><span>{$lang['shoptel']}{$lang['colon']}</span>{$shop['tel']}</li>
			<li><span>{$lang['shopaddr']}{$lang['colon']}</span>{$shop['address']}</li>
			<!-- <li><span>{$lang['consume_address']}{$lang['colon']}</span></li> -->
			<li class="cons_notice"><span>{$lang['disclaimer']}{$lang['colon']}</span>$lang['disclaimer_tip']</li>
		</ul>
		<div class="cons_copylink">
			<a onclick="javascript:setCopy('{$consumeurl}');" title="$lang['copylink']" href="javascript:;">$lang['copylink']</a>$lang['copylink_tip']
		</div>
		<div class="news_msg">
		<!--{eval include template('templates/store/default/comment.html.php', 1);}-->
		</div>
	</div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->

</div>
<!--{/if}-->
<script>
	<!--
	$(function(){
		$("#consumerlist dl").hover(function(){$(this).addClass("thison")},function(){$(this).removeClass("thison")})
	})
	//-->
</script>