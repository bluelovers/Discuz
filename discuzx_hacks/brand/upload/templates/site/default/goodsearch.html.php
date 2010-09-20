<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<div id="allgoodslist" class="layout">
	<div class="content">
		<div class="list">
			<div class="cont">
				<h3>{$lang['totalfind']}{$resultcount}{$lang['findgood']}</h3>
				<div class="streetsearch_new">
					<dl>
						<dt>{$lang['goodsearch']}</dt>
						<dd>
							<form id="form_search_goods" method="get" name="goodsearch.php" action="">
								<input type="text" name="keyword" id="keyword" maxlength=50 value="{$_GET['keyword']}" />
								<input type="hidden" name="catid" value="{$_GET['catid']}" />
								&nbsp;
								<input type="submit" class="btn" value="" />
							</form>
						</dd>
					</dl>
				</div>
				<!--{if !empty($catsarr)}-->
				<div class="searchcat" id="cat_feilds">
				<!--{loop $catsarr $cat}-->
					<a href="goodsearch.php?keyword={$_GET['keyword']}&catid={$cat['catid']}">{$cat['name']} ({$catnums[$cat['catid']]})</a>
				<!--{/loop}-->
				</div>
				<!--{/if}-->
				<!--{if $attform}-->
				<div class="searchattr" id="attr_feilds">{$attform}</div>
				<!--{/if}-->
				<h3>{$lang['goodlist']}</h3>
				<div class="title">
					<div class="name1">$lang['goodimg']</div>
					<div class="foodlabels">$lang['goodintro']</div>
				</div>
				<div id="goodslist">
				<!--{loop $goodlist $good}-->
					<div class="everylist">
						<div class="name">
							<dl>
								<dt>
									<span><a target="_blank" href="store.php?id={$good['shopid']}&action=good&xid={$good['itemid']}"><img width="100" height="80" src="{$good['thumb']}" alt=""></a></span>
								</dt>
								<dd>
									<h5><a target="_blank" href="store.php?id={$good['shopid']}&action=good&xid={$good['itemid']}">$good['subject']</a></h5>
									<ul>
										<!--{if $good['minprice']}--><li><span>{$lang['price']}{$lang['colon']}</span><em>{$good['minprice']}<!--{if $good['maxprice']>0}--> - {$good['maxprice']}<!--{/if}--></em>$lang['yuan']</li><!--{/if}-->
										<li><span>{$lang['dateline']}{$lang['colon']}</span>#date('Y-m-d', $good['dateline'])#</li>
										<li><span>{$lang['inshop']}{$lang['colon']}</span><a href="store.php?id=$good['shopid']" title="$good['shopinfo']['subject']" target="_blank"><!--{eval echo cutstr($good['shopinfo']['subject'], 20)}--></a></li>
										<!--{if $good['shopinfo']['roundingscore']}-->
											<li><span>$lang['shopscore']:</span>
												<img src="static/image/{$good['shopinfo']['roundingscore']}.gif">
											</li>
										<!--{/if}-->
									</ul>
									<div class="goods_intro">{$good['intro']}</div>
								</dd>
							</dl>
						</div>
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

    $("#goodslist .everylist").hover(function() {
        $("#goodslist .everylist").hover(function() {
                $(this).css({'background' : 'url(static/image/bg_goodssearch_hover.png) repeat-x scroll 0 bottom'});

            } , function() {
                $(this).css({'background' : 'url(static/image/bg_goodssearch.png) repeat-x scroll 0 bottom'});

            });
    });
});
</script>
<!--{template 'templates/site/default/footer.html.php', 1}-->