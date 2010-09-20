<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: album.html.php 4379 2010-09-09 03:00:50Z fanshengshuai $
 */-->

<div id="allalbumslist" class="layout">
	<div class="leftsidebar">
		<!--{block name="sql" parameter="sql/SELECT%20itemid%20FROM%20{$_SC['tablepre']}shopitems%20WHERE%20grade%20%3E2%20ORDER%20BY%20displayorder%20ASC/tpl/sidebar_allshop/limit/0,100/pagetype/sitelist/usetype/shop"}-->
	</div>
	<div class="content">
		<div class="list" id="albumlist">
			<div class="cont">
				<h3>{$lang['totalfind']}{$resultcount}{$lang['findalbum']}</h3>
				<div class="streetsearch_new">
					<dl>
						<dt>{$lang['albumsearch']}{$lang['colon']}</dt>
						<dd>
							<form id="form_search_albums" method="get" name="album.php" action="">
								<input type="text" name="keyword" id="keyword" maxlength=50 value="{$_GET['keyword']}" />
								<input type="hidden" name="catid" value="{$_GET['catid']}" />
								<input type="submit" class="btn" value="" />
							</form>
						</dd>
					</dl>
				</div>
				<!--{if !empty($catsarr)}-->
				<div class="searchcat" id="cat_feilds">
				<!--{loop $catsarr $cat}-->
					<a href="album.php?keyword={$_GET['keyword']}&catid={$cat['catid']}">{$cat['name']}({$catnums[$cat['catid']]})</a>
				<!--{/loop}-->
				</div>
				<!--{/if}-->
				<!--{if $attform}-->
				<div class="searchattr" id="attr_feilds">{$attform}</div>
				<!--{/if}-->
				<h3>{$lang['albumlist']}</h3>
				<div class="title">
					<div class="name1">$lang['albumimg']</div>
					<div class="foodlabels">$lang['albumintro']</div>
					<div class="rebate">$lang['rebate']</div>
				</div>
				<div id="albumslist">
				<!--{loop $albumlist $album}-->
					<div class="everylist">
						<div class="name">
							<dl>
								<dt>
									<span><a target="_blank" href="store.php?id={$album['shopid']}&action=album&xid={$album['itemid']}"><img width="100" height="80" src="{$album['thumb']}" alt=""></a></span>
								</dt>
								<dd style="width:320px;">
									<h5><a target="_blank" href="store.php?id={$album['shopid']}&action=album&xid={$album['itemid']}"  title="$album['subject']"><!--{eval echo cutstr($album['subject'], 20)}--></a></h5>
									<ul>
										<li><span>{$lang['dateline']}{$lang['colon']}</span>#date('Y-m-d', $album['dateline'])#</li>
										<li><span>{$lang['inshop']}{$lang['colon']}</span><a href="store.php?id=$album['shopid']" title="$album['shopinfo']['subject']" target="_blank"><!--{eval echo cutstr($album['shopinfo']['subject'], 20)}--></a></li>
										<!--{if $album['shopinfo']['roundingscore']}-->
											<li><span>$lang['shopscore'] </span>
												<img src="static/image/{$album['shopinfo']['roundingscore']}.gif">
											</li>
										<!--{/if}-->
										<li>{$album['description']}</li>
									</ul>
								</dd>
							</dl>
						</div>
						<div class="rebate" title="{$album['shopinfo']['discount']}"><!--{if $album['shopinfo']['isdiscount'] && $album['shopinfo']['discount']}-->{$album['shopinfo']['discount']}<!--{else}-->$lang['none']<!--{/if}--></div>
					</div>
				<!--{/loop}-->
	 			</div>
				$multipage
				<div class="clearboth"></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" charset="$_G['charset']">
$(document).ready(function() {

    $("#albumslist .everylist").hover(function() {
        $("#albumslist .everylist").hover(function() {
                $(this).css({'background' : 'url(static/image/bg_goodssearch_hover.png) repeat-x scroll 0 bottom'});

            } , function() {
                $(this).css({'background' : 'url(static/image/bg_goodssearch.png) repeat-x scroll 0 bottom'});

            });
    });
});
function showalbumlist(shopid) {
	$("#albumlist").load('album.php?action=getalbumlist&shopid=' + shopid);
}
</script>
<!--{template 'templates/site/default/footer.html.php', 1}-->