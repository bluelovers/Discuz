<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: consumesearch.html.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */-->

<div class="layout" id="consumer">
	<div class="content">
		<h3>{$lang['totalfind']}{$resultcount}{$lang['findcoupon']}</h3>
		<div class="streetsearch msearch">
			<form action="consume.php" name="form_search_consume" method="get" id="form_search_consume">
				<span>$lang['searchcoupon']</span>
					<input maxlength="50" name="keyword" type="text" value="{$_GET['keyword']}">
					$lang['category'] <select name="catid" id="catId">
							<option value="all" label="$lang['all']" selected="selected">$lang['all']</option>
							<!--{loop $_SGLOBAL['consumecates'] $cat}-->
							<option value="{$cat['catid']}" label="{$cat['name']}" {if $cat['catid'] == $_REQUEST['streetId']} selected="selected"{/if}>{$cat['pre']}{$cat['name']}</option>
							<!--{/loop}-->
						</select>
					$lang['orderby'] <select name="orderBy" id="orderBy">
							<option value="dateline" label="$lang['orderbytime']"{$select['bytime']}>$lang['orderbytime']</option>
							<option value="viewnum" label="$lang['orderbyview']"{$select['byview']}>$lang['orderbyview']</option>
						</select>
					$lang['range'] <select name="range" id="range">
							<option value="all" label="$lang['all']">$lang['all']</option>
							<option value="available" label="$lang['inactive']"{$select['range']}>$lang['inactive']</option>
						</select>
					<input type="submit" class="btn" value=""/>
					<input type="hidden" name="refer" value="$refer" />
			</form>
		</div>
		<!--{loop $list $consume}-->
		<dl>
			<dt>
				<!--{if $consume['validity_end'] > $_G['timestamp'] && $consume['validity_start'] < $_G['timestamp']}-->
				<span class="ineffect">$lang['valid']</span>
				<!--{elseif $consume['validity_end'] < $_G['timestamp']}-->
				<span class="expire">$lang['invalid']</span>
				<!--{/if}-->
				<a href="store.php?id={$consume['shopid']}&action=consume&xid={$consume['itemid']}" target="_blank"><img border="0" src="{$consume['thumb']}" alt="" width="192" height="120"></a>
			</dt>
			<dd>
				<h4><a href="store.php?id={$consume['shopid']}&action=consume&xid={$consume['itemid']}" title="" target="_blank">{$consume['subject']}</a></h4>
				<p>{$lang['deadline']}{$lang['colon']} #date('Y-m-d', $consume['validity_start'])# $lang['to'] #date('Y-m-d', $consume['validity_end'])#</p>
				<p>{$lang['promulgator']}{$lang['colon']}<a href="store.php?id={$consume['shopid']}" target="_blank">{$consume['shopinfo']['subject']}</a><!--{if $consume['shopinfo']['roundingscore']}--><img src="static/image/{$consume['shopinfo']['roundingscore']}.gif"><!--{/if}--></p>
				<p>{$lang['viewnum']}{$lang['colon']} {$consume['viewnum']}{$lang['viewnumunit']}</p>
			</dd>
		</dl>
		<!--{/loop}-->
		$multipage
		<div class="c h10"></div>
	</div>
	<!--{eval include template('templates/site/default/sidebar.html.php', 1);}-->
</div>
<script>
	$(function(){
		$("#consumer dl").hover(function(){$(this).addClass("thison")},function(){$(this).removeClass("thison")})
	});
</script>
<!--{template 'templates/site/default/footer.html.php', 1}-->