<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: groupbuy.html.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */-->

<div id="allgroupbuyslist" class="layout">
	<div class="content">
		<div class="list">
			<div class="cont">
				<h3>{$lang['totalfind']}{$resultcount}{$lang['findgroupbuy']}</h3>
				<div class="streetsearch_new">
					<dl>
						<dt>{$lang['groupbuysearch']}{$lang['colon']}</dt>
						<dd>
						<form id="form_search_groupbuys" method="get" name="groupbuy.php" action="">
							<input type="text" name="keyword" id="keyword" maxlength=50 value="{$_GET['keyword']}" />
							<input type="hidden" name="catid" value="{$_GET['catid']}" />
							&nbsp;
							<input type="submit" class="btn" value=""/>
						</form>
						</dd>
					</dl>
				</div>
				<!--{if !empty($catsarr)}-->
				<div class="searchcat" id="cat_feilds">
					<!--{loop $catsarr $cat}-->
					<a href="groupbuy.php?keyword={$_GET['keyword']}&catid={$cat['catid']}">{$cat['name']} ({$catnums[$cat['catid']]})</a>
					<!--{/loop}-->
				</div>
				<!--{/if}-->
				<!--{if $attform}-->
				<div class="searchattr" id="attr_feilds">{$attform}</div>
				<!--{/if}-->
				<div id="groupbuyslist">
					{eval $step = 0;}
					<!--{loop $groupbuylist $groupbuy}-->
					<!--{eval
					$border = "";
					$step ++ ;
					if(($step % 2) == 1 )
					$border = "border-right:#ccc 1px dashed;";
					}-->
					<div class="list_item" style="border-bottom:#ccc 1px dashed; {$border}">
						<div class="groupbytitle">
							<h5><a target="_blank" href="store.php?id={$groupbuy['shopid']}&action=groupbuy&xid={$groupbuy['itemid']}"><!--{eval echo cutstr($groupbuy['subject'], 36, '.'); }--></a></h5>
							<span>{$lang['groupbuytime']}{$lang['colon']}</span>{$groupbuy['groupbuytime']}
						</div>

						<div class="groupbuy_item_content">
							<dl>
								<dt>
								<!--{if $groupbuy['validity_end'] < $_G['timestamp'] || $groupbuy['close'] == 1}-->
								<span class="expire"></span>
								<!--{else}-->
								<span class="ineffect"></span>
								<!--{/if}-->
								<span><a target="_blank" href="store.php?id={$groupbuy['shopid']}&action=groupbuy&xid={$groupbuy['itemid']}"><img width="100" height="80" src="{$groupbuy['thumb']}" alt=""></a></span>
								</dt>
								<dd>
								<ul>
									<li class="b red"><label>$lang['groupbuyprice_now']</label><span class="b">{$lang['rmb']}{$groupbuy['groupbuypriceo']}</span></li>
									<li><label>$lang['groupbuyprice']</label><span>{$lang['rmb']}{$groupbuy['groupbuyprice']}</span></li>
									<li><label>$lang['groupbuydiscount']</label><span class="b">{$groupbuy['groupbuydiscount']}{$lang['zhe']}</span></li>
									<li><label>$lang['groupbuysave']</label><span>{$groupbuy['groupbuysave']}</span> $lang['yuan']</li>
									<li><em class="red acb">{$groupbuy['buyingnum']}</em> $lang['groupbuyingnum']</li>
									<li><a id="iwantjoin" href="store.php?id={$groupbuy['shopid']}&action=groupbuy&xid={$groupbuy['itemid']}#groupbyjoin">$lang['iwantjoin']</a></li>
								</ul>
								</dd>
							</dl>
						</div>
					</div>
					<!--{/loop}-->
					<div class='c h10'></div>
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

		$("#groupbuyslist .everylist").hover(function() {
			$("#groupbuyslist .everylist").hover(function() {
				$(this).css({'background' : 'url(static/image/bg_groupbuyssearch_hover.png) repeat-x scroll 0 bottom'});

				} , function() {
				$(this).css({'background' : 'url(static/image/bg_groupbuyssearch.png) repeat-x scroll 0 bottom'});

			});
		});
	});
</script>
<!--{template 'templates/site/default/footer.html.php', 1}-->