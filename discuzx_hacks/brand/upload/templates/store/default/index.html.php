<?exit?>
<div id="seller" class="main layout store">
	<div class="content">
		<!--{if is_array($tips)}-->
		<div class="shop_notice">
			<span>$lang['windownotice']</span>
			<!--{if count($tips) > 1}-->
			<ul id="shop_notice">
				<!--{loop $tips $tip}-->
				<li>$tip</li>
				<!--{/loop}-->
			</ul>
			<p><em title="$lang['prev']" id="shop_notice_prev">$lang['prev']</em><em title="$lang['next']" id="shop_notice_next">$lang['next']</em></p>
			<!--{else}-->
			<ul id="shop_notice">
				<!--{loop $tips $tip}-->
				<li>$tip</li>
				<!--{/loop}-->
			</ul>
			<!--{/if}-->
		</div>
		<!--{/if}-->
		<div class="showpic">
			<img src="{$shop['windowsimg']}" width="710" height="220">
			<h4>$lang['brandart']</h4>
			<div>
				<p>{$shop['windowstext']}</p>
			</div>
		</div>
		<div class="sellerbox">
			<h3>{$lang['latestactivity']}</h3>
			<div style="display: none;" id="publishnew"></div>
			<ul class="menu" id="newmovementmenu">
				<li class="mouseover" id="coinauction"><a href="javascript:void(0);" title=""><span>{$lang['coupon']}</span></a></li>
				<li class="" id="coinauction"><a href="javascript:void(0);" title=""><span>{$lang['groupbuy']}</span></a></li>
			</ul>
			<div id="menulist">
            	<div class="movement">
				<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}consumeitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%20%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,4/cachename/consume_li/tpl/consumer_li/pagetype/storelist/usetype/consume/shopid/{$_GET['id']}"}--><!--shop new consume-->
                </div>
                <div class="movement">
				<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}groupbuyitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%20%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,4/cachename/groupbuy_li/tpl/groupbuy_li/pagetype/storelist/usetype/groupbuy/shopid/{$_GET['id']}"}--><!--shop new groupbuy-->
                </div>
			</div>
		</div>
		<div class="sellerbox newproduct">
			<h3>{$lang['hotgood']}</h3>
			<!-- new tab -->
			<ul id="hotgoods" class="menu">
				<li id="allGoodsList" class="mouseover"><a title="{$lang['all']}{$lang['goodlist']}" href="store.php?id={$shop['itemid']}&action=good"><span>$lang['all']</span></a></li>
			</ul><!-- new tab End -->
			<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}gooditems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%20%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,10/cachename/newgoods/tpl/data/pagetype/storelist/usetype/good/shopid/{$_GET['id']}"}--><!--shop new good-->
			<!--{if $_SBLOCK['newgoods']}-->
			<div id="productlist">
				<ul class="productlist" style="display: block;">
					<!--{loop $_SBLOCK['newgoods'] $ikey $value}-->
					<!--{eval $value['thumb'] = str_replace('static/image/nophoto.gif', 'static/image/noimg.gif', $value['thumb']);}-->
					<!--{eval $value['subjectimage'] = str_replace('static/image/nophoto.gif', 'static/image/noimg.gif', $value['subjectimage']);}-->
					<li>
					<a href="store.php?id={$shop['itemid']}&action=good&xid={$value['itemid']}" target="_blank"><img alt="" src="{$value['thumb']}"></a>
					<p><a href="store.php?id={$shop['itemid']}&action=good&xid={$value['itemid']}" target="_blank">{$value['subject']}</a></p>
					<!--{if $value['minprice']}-->
					<p>{$lang['price']}{$lang['colon']}<strong class="price">{eval echo round($value['minprice']);} {$lang['yuan']}</strong></p>
					<!--{/if}-->
					</li>
					<!--{/loop}-->
				</ul>
				<!-- new tab content -->
				<div class="more" id="moreGoodsLink"><a href="store.php?id={$shop['itemid']}&action=good">$lang['more']&gt;&gt;</a></div>
			</div>
			<!--{/if}-->
		</div>
		<div class="sellerbox bbs">
			<!--{eval include template('templates/store/default/comment.html.php', 1);}-->
		</div>
	</div>

	<div class="sidebar">
		<div class="jiontime">{$lang['jointime']}{$lang['colon']}<!--{eval echo date($lang['dateformat1'], $shop['dateline']);}--></div>
		<div class="box" id="shop_base_info">
			<!--{if $_G['uid'] && !$_G['myshopid'] && !ckfounder($_G['uid'])}-->
			<span style="color: font-size: 12px; margin-left: 190px; margin-top: 5px; cursor: pointer; position: absolute;" onclick="report('shop', '{$shop[itemid]}');">$lang['report']</span>
			<!--{/if}-->
			<h3>{$lang['shopinfo']}</h3>
			<ul id="shop_base_info_ul">
				<h4>{$shop['subject']}</h4>
				<!--{if $shop['roundingscore']}-->
				<li class="c_dgreen" id="star_area"><img  style="vertical-align:middle;" src="static/image/{$shop['roundingscore']}_big.png"> <font color="red" style="font-weight:blod;font-size:14px;vertical-align:middle;">{$shop['score']}</font></li>
				<!--{/if}-->
				<li class="c_dgreen" style="clear:both;"><span>$lang['address']</span><div>{$shop['address']}</div></li>
				<li class="c_dgreen" style="clear:both;"><span>$lang['telephone']</span><div>{$shop['tel']}</div></li>
				<li class="c_dgreen" style="clear:both;"><span>{$lang['shopinsubcat']}{$lang['colon']}</span><div>$thecat['name']</div></li>
				<!--{if $shop['isdiscount'] == 1}-->
				<li class="c_dgreen" style="clear:both;"><span>{$lang['shopcard']}{$lang['colon']}</span><img src="static/image/huiyuan-card.gif" /></li>
				<!--{if $shop['discount']}-->
				<li class="c_dgreen" style="clear:both;"><span>{$lang['disc']}{$lang['colon']}</span><div>{eval echo cutstr($shop['discount'], 35); }</div></li>
				<!--{/if}-->
				<!--{/if}-->
				<!--{if $shop['message']}-->
				<!--{eval $shortDesc = cutstr($shop['message'], 20, $havedot=1);}-->
				<li class="c_dgreen" style="clear:both;"><span>{$lang['shopdescription']}{$lang['colon']}</span>
				<div id="shopDesc" title="{$shop['message']}">{$shortDesc}<!--{if ($shop['message'] != $shortDesc)}-->&nbsp;&nbsp;&nbsp;&nbsp;<a onclick="showMoreDesc();" style="color:red; cursor:pointer; display:inline;">{$lang['moreDesc']}</a><!--{/if}--></div>
				</li>
				<!--{/if}-->
			</ul>
		</div>
		<!--{if $shop['score'] && $_SGLOBAL['commentmodel']['scorename']}-->
		<div class="score">
			<h3>
				{$lang['shopscore']}
			</h3>
			<ul>
				<!--{loop $_SGLOBAL['commentmodel']['scorename'] $key $scorename}-->
				<!--{if $shop['score'.$key]}-->
				<li><span>$scorename:</span>
				<dl>
					<dt style="width:{$shop['stripwidth'.$key]};">{$shop['score'.$key]} {$lang['fee']}</dt>
				</dl>
				</li>
				<!--{/if}-->
				<!--{/loop}-->
				<li style="color:#925A21;">{$lang['total']} {$shop['remarknum']} {$lang['scorenums']}</li>
			</ul>
		</div>
		<!--{/if}-->
		<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}noticeitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%20%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,5/cachename/sidenotice/tpl/data/pagetype/storelist/usetype/notice/shopid/{$_GET['id']}"}-->
		<div class="box">
			<h3><span><a href="store.php?id={$shop['itemid']}&action=notice">{$lang['view']}{$lang['all']}&gt;&gt;</a></span>{$lang['noticelist']}</h3>
			<ul>
				<!--{if $_SBLOCK['sidenotice']}-->
				<!--{loop $_SBLOCK['sidenotice'] $ikey $value}-->
				<!--{eval $value['styletitle'] = pktitlestyle($value['styletitle']);}-->
				<li><a{if $value['styletitle']}  style="{$value['styletitle']}"{/if} href="store.php?id=$shop['itemid']&amp;action=notice&amp;xid=$value['itemid']" target="_blank" title="$value[subjectall]"><!--{eval echo cutstr($value[subject],32);}--></a></li>
				<!--{/loop}-->
				<!--{else}-->
				<li style="color:#925A21;">$lang['nonotice']</li>
				<!--{/if}-->
			</ul>
		</div>
		<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}albumitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20ORDER%20BY%20itemid%20DESC/limit/0,6/cachename/album/tpl/album_li/pagetype/storelist/usetype/album/shopid/{$_GET['id']}"}--><!--photo-->
		<!--{if $shop['forum']}-->
		<a style="margin-bottom:10px;display:block;" href="{$shop['forum']}" target="_blank"><img height="60" width="230" alt="" src="static/image/pk_intercourse.jpg"></a>
		<!--{/if}-->
		<!--{if $_G['setting']['enablemap'] == 1}-->

		<div class="box">
			<h3>{$lang['shopmap']}</h3>
			<div style="width: 228px; height: 200px;" id="map_canvas"></div>
			<div><a class="map_bt" href="store.php?id={$shop['itemid']}&action=map" target="_blank">{$lang['originalmap']}</a></div>
			<br>
		</div>
		<!--{/if}-->
		<!--{if !empty($brandlinkslist)}-->
		<div class="box">
			<h3>{$lang['brandlinks']}</h3>
			<ul>
				<!--{loop $brandlinkslist $brandlink}-->
				<li><a href="{$brandlink['url']}" target="_blank" title="{$brandlink['name']}">{$brandlink['shortname']}</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
	</div>
</div>
<script charset="utf-8" type="text/javascript" src="static/js/store_index.js"></script>
<!--{if $_G['setting']['enablemap'] == 1}-->
<script src="http://ditu.google.cn/maps?file=api&amp;v=2&amp;key={$_G['setting']['mapapikey']}&hl=zh-CN" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

var map = new GMap2(document.getElementById("map_canvas"));
var center = new GLatLng {$shop['mapapimark']};
map.setCenter(center, 14);
var marker = new GMarker(center, {
	draggable: false
});
map.enableScrollWheelZoom();
map.addOverlay(marker);

</script>
<!--{/if}-->