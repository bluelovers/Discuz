<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<!--/**
*      [品牌空间] (C)2001-2010 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*      $Id: street.html.php 4359 2010-09-07 07:58:57Z fanshengshuai $
*/-->

<div id="street" class="layout">
	<div class="content">
		<div class="streetsearch_new">
			<dl>
				<dt>{$lang['shopsearch']}{$lang['colon']}</dt>
				<dd>
				<form id="searchForm" method="get" action="street.php">
					<input id="keyword" name="keyword"  maxlength=50 value="$_GET['keyword']">
					&nbsp;
					<input class="btn" type="submit" name="shopsubmit" value="" />
					<input type="hidden" name="catid" value="{$catid}" />
					<input type="hidden" name="region" value="{$region}" />
				</form>
				</dd>
			</dl>
			<dl>
				<dt>{$lang['selectcategory']}{$lang['colon']}</dt>
				<dd class="catlist" style="overflow:hidden;">{$categorylist_select}</dd>
			</dl>
			<dl>
				<dt>{$lang['selectregion']}{$lang['colon']}</dt>
				<dd>{$regionlist_select}</dd>
			</dl>
		</div>
		<div class="list">
			<div class="cont">
				<h3>{$location['name']} {$location['region']} {$lang['shoplist']}{$lang['colon']}</h3>
				<div class="title">
					<div class="name">$lang['shopname']</div>
					<div class="foodlabels">$lang['insubcat']</div>
					<div class="rebate">$lang['rebate']</div>
				</div>
				<div id="searchList">
					<!--{loop $shoplist $value}-->
					<div class="everylist">
						<div class="name">
							<dl>
								<dt style="width:110px;"> <a target="_blank" href="store.php?id={$value['itemid']}"><img src="$value['thumb']" alt="" width="100" height="80" /></a> </dt>
								<dd style="width:300px;">
								<h5><a target="_blank" href="store.php?id={$value['itemid']}">$value['subject']</a>
									<!--{if $value['roundingscore']}-->
									<img src="static/image/{$value['roundingscore']}.gif">
									<!--{/if}-->
								</h5>
								<ul>
									<li><span>$lang['address']</span>$value['address']</li>
									<li><span>$lang['telephone']</span>$value['tel']</li>
								</ul>
								</dd>
							</dl>
						</div>
						<div class="foodlabels"> <a href="street.php?catid=$_G['categorylist'][$value['catid']]['catid']">$_G['categorylist'][$value['catid']]['name']</a> </div>
						<div class="rebate"><!--{if $value['isdiscount'] && $value['discount']}-->$value['discount']<!--{else}-->$lang['none']<!--{/if}--></div>
					</div>
					<!--{/loop}-->
				</div>
				$multipage
				<div class="clearboth"></div>
			</div>
		</div>
	</div>
	<!--{template 'templates/site/default/sidebar.html.php', 1}-->
</div>
<script>
	$(document).ready(function() {
		$("#searchList .everylist").hover(function() {
			$("#searchList .everylist").hover(function() {
				$(this).css({'background' : 'url(static/image/bg_goodssearch_hover.png) repeat-x scroll 0 bottom'});

				} , function() {
				$(this).css({'background' : 'url(static/image/bg_goodssearch.png) repeat-x scroll 0 bottom'});

			});
		});
	});
</script>
<!--{template 'templates/site/default/footer.html.php', 1}-->