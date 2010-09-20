<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<style type="text/css">
.main .topic {
	position: relative;
}

#play img {
	border: 0px
}

#play_text {
	position: absolute;
	height: 20px;
	z-index: 1002;
	text-align: right;
	right: 10px;
	bottom: 5px;
}

#play_text span {
	font-weight: bold;
	font-size: 14px;
	display: inline-block;
	width: 16px;
	height: 16px;
	float: left;
	background-color: #FCF2CF;
	display: block;
	color: #D94B01;
	text-align: center;
	margin: 2px;
	cursor: pointer;
	font-family: "Courier New";
	border: 1px solid #F47500;
	filter: Alpha(Opacity = 80);
	opacity: 0.8;
}

#play_list a {
	display: block;
	width: 545px;
	height: 191px;
	position: absolute;
	overflow: hidden
}
</style>



<div id="container">
	<div class="sidebar">
		<div class="subnav">
			<h3>$lang['brand_nav']</h3>
			<!--{loop $_G['categorylist'] $cat}-->
			<!--{if $cat[upid] == 0}-->
			<h4 class="f_red"><a href="street.php?catid={$cat['catid']}">{$cat['name']}</a></h4>
			<!--{eval $subcatids = explode(', ',$cat['subcatid']);}-->
			<!--{if is_array($subcatids)}-->
			<ul style="height:34px;">
				<!--{loop $subcatids $c_cat}-->
				<!--{if $_G['categorylist'][$c_cat]['upid'] != 0 && $_G['categorylist'][$c_cat]['catid'] > 0}-->
				<li><a href="street.php?catid={$_G['categorylist'][$c_cat]['catid']}">$_G['categorylist'][$c_cat]['name']</a></li>
				<!--{/if}-->
				<!--{/loop}-->
			</ul>
			<!--{/if}-->
			<!--{/if}-->
			<!--{/loop}-->
			<div></div>
		</div>
		<div class="aboutbrand">
			<!--{eval $inids = $_G['brandads']['hotshop'];}-->
			<!--{template 'static/blockstyle/index_hotshop.html.php', 1}--><!--index_hotshop $lang['html_note_index_hotshop']-->
			<!--{block name="sql" parameter="sql/SELECT%20itemid%20FROM%20{$_SC['tablepre']}shopitems%20WHERE%20grade%3E2%20ORDER%20BY%20itemid%20DESC/limit/0,3/tpl/index_newshop/pagetype/index/usetype/shop"}--><!--index_newshop $lang['html_note_index_newshop']-->
			<ul class="tips">
				<li class="tit">{$lang['sitetel']}{$lang['colon']}</li>
				<li class='info'>{$_G['setting']['sitetel']}</li>
				<li class="tit">{$lang['siteqq']}{$lang['colon']}</li>
				<li class='info'>{$_G['setting']['siteqq']}</li>
			</ul>
		</div>
	</div>
	<div class="main">
		<div id="play" class="topic">
			<!--{if $_G['brandads']['ads_show_type']=="topic"}-->
			<div id="play_text"></div>
			<div id="play_list">
				<!--{loop $_G['brandads']['topic'] $topicinfo}-->
				<a href="{$topicinfo['url']}" target="_blank">
					<img src="{$topicinfo['image']}" title="" alt="" />
				</a>
				<!--{/loop}-->
			</div>
			<!--{else}-->
			$bannerhtml
			<!--{/if}-->
		</div>

		<div class="notice">
			<h3>$lang['hotspot']</h3>
			<ul>
				<!--{if is_array($_G['brandads']['notice'])}-->
				<!--{loop $_G['brandads']['notice'] $notice}-->
				<!--{if !empty($notice['title']) && !empty($notice['url'])}-->
				<li><a target="_blank" href="{$notice['url']}" style="{eval echo pktitlestyle($notice['style']);}">{$notice['title']}</a> </li>
				<!--{/if}-->
				<!--{/loop}-->
				<!--{/if}-->
			</ul>
		</div>

		<!--{block name="sql" parameter="sql/SELECT%20itemid%20FROM%20{$_SC['tablepre']}shopitems%20WHERE%20recommend%3D1%20AND%20grade%20%3E2%20ORDER%20BY%20displayorder%20ASC%20%2C%20itemid%20DESC/limit/0,18/tpl/index_recshop/pagetype/index/usetype/shop"}-->
		<!--{template 'static/blockstyle/index_hotgoods.html.php', 1}-->
		<!--{template 'static/blockstyle/index_consume.html.php', 1}-->
		<!--{template 'static/blockstyle/index_groupbuy.html.php', 1}-->
		<!--{if $_G['setting']['enablecard'] == 1}-->
		<!--{template 'static/blockstyle/index_discount.html.php', 1}-->
		<!--{/if}-->
		<div style="height:10px;background:#fff; clear:both;"></div>
	</div>
</div>
<script charset="utf-8" type="text/javascript" src="static/js/index.js"></script>
<!--{template 'templates/site/default/footer.html.php', 1}-->