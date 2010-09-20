<?exit?>
<!--{template 'templates/site/default/header.html.php', 1}-->

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: card.html.php 4367 2010-09-08 03:00:11Z fanshengshuai $
 */-->

<div id="content">
	<div id="left_box">
		<div id="top_img">
		  <!--top img start-->
		  <div id="left_img"><img src="static/image/top_img3.jpg" alt="$_G['setting']['sitename'] card" height="220" width="419"></div>
		  <div id="right_img"><a href="{$_G['setting']['discounturl']}" target="_blank" onfocus="blur()" onclick="ad_c_m('card_woyaobanka')"><img src="static/image/apply1.gif" alt="apply" height="26" width="117"></a></div>
		</div>

		<div id="label">$lang['card_shop']</div>

		<div id="item">
			<div class="item_name">$lang['shopname']</div>
			<div class="item_pic">$lang['shoptag']</div>
			<div class="item_discount">$lang['discount']</div>

		</div>
		<!--{loop $list $shop}-->
		<div id="brandlist" class="on" onmouseout="this.className='on'" onmouseover="this.className='ex'">
			<div style="float:left; width:120px;">
				<a href="store.php?id={$shop['itemid']}" target="_blank"><img src="{$shop['thumb']}" alt="$shop['subject'] logo" height="80" width="100"></a>
			</div>
			<div class="list_name l22">
				<label class="blue f14"><a href="store.php?id={$shop['itemid']}" target="_blank">{$shop['subject']}</a></label><br>
				{$lang['address']}{$shop['address']}<br>{$lang['telephone']}{$shop['tel']} <br>
			</div>
			<div style="float:left; width:120px;">{$shop['catname']}</div>
			<div class="list_discount">{$shop['discount']}</div>
			<div class="list_remark"></div>

		</div>
		<!--{/loop}-->
		<div id="warepagebox">$multipage</div>
	</div>
	<div id="right_box" class="sidebar">
		<div class="kaka">
		  <p><span>{$_G['setting']['sitetel']}</span></p>
		</div>
		<!--{if is_array($_G['brandads']['notice'])}-->
		<div class="box hot">
			<h3>$lang['hotspot']</h3>
			<ol>
			<!--{loop $_G['brandads']['notice'] $notice}-->
			<!--{if !empty($notice['title']) && !empty($notice['url'])}-->
				<li><a href="{$notice['url']}" target="_blank">{$notice['title']}</a></li>
			<!--{/if}-->
			<!--{/loop}-->
			</ol>
		</div>
		<!--{/if}-->
		<!--{if !empty($_G['brandads']['sidebarshop'])}-->
		<!--{eval $inids = $_G['brandads']['sidebarshop']}-->
		<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}shopitems%20WHERE%20itemid%20IN%20%28{$inids}%29%20AND%20grade%20%3E2/limit/0,10/tpl/sidebar_shop/pagetype/sidebar/usetype/shop"}--><!--sidebar_shop-->
	    <!--{/if}-->
	</div>
</div>
<!--{template 'templates/site/default/footer.html.php', 1}-->