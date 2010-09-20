<?exit?>

<!--/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sidebar.html.php 4397 2010-09-10 10:07:01Z fanshengshuai $
 */-->

<div class="sidebar">
	<div class="box">
		<h3>{$lang['shopinfo']}</h3>
		<ul class="hhhh">
			<h4>{$shop['subject']}</h4>
			<!--{if $shop['roundingscore']}-->
			<li id="star_area" class="c_dgreen"><img src="static/image/{$shop['roundingscore']}_big.png" style="vertical-align: middle;"> <font color="red" style="font-size: 14px; vertical-align: middle;">{$shop['score']}</font></li>
			<!--{/if}-->

			<li class="c_dgreen" style="clear:both;"><span>$lang['address']</span><div>{$shop['address']}</div></li>
			<li class="c_dgreen" style="clear:both;"><span>$lang['telephone']</span><div>{$shop['tel']}</div></li>
			<li class="c_dgreen" style="clear:both;"><span>{$lang['shopinsubcat']}{$lang['colon']}</span><div>$thecat['name']</div></li>
			<!--{if $shop['isdiscount'] == 1}-->
			<li class="c_dgreen" style="clear:both;"><span>{$lang['shopcard']}{$lang['colon']}</span><img src="static/image/huiyuan-card.gif" style="margin-left:-15px;" /></li>
			<!--{if $shop['discount']}-->
			<li class="c_dgreen" style="clear:both;"><span>{$lang['disc']}{$lang['colon']}</span><div>$shop['discount']</div></li>
			<!--{/if}-->
			<!--{/if}-->
			<li class="c_dgreen" style="clear:both;height:auto;"><span>{$lang['shopdescription']}{$lang['colon']}</span><div><!--{eval echo cutstr($shop['message'], 50, $havedot=1);}--></div></li>
		</ul>
		<div style="clear:both"></div>
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
	<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}noticeitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,5/cachename/sidenotice/tpl/data/pagetype/storelist/usetype/notice/shopid/{$_GET['id']}"}--><!--notice-->
	<!--{if $_SBLOCK['sidenotice']}-->
	<div class="box">
		<h3><span><a href="store.php?id={$shop['itemid']}&action=notice">{$lang['view']}{$lang['all']}&gt;&gt;</a></span>$lang['noticelist']</h3>
		<ul>
			<!--{loop $_SBLOCK['sidenotice'] $ikey $value}-->
			<!--{eval $value['styletitle'] = pktitlestyle($value['styletitle']);}-->
			<li><a{if $value['styletitle']}  style="{$value['styletitle']}"{/if} href="store.php?id=$shop['itemid']&action=notice&xid=$value['itemid']" target="_blank" title="$value[subjectall]"><!--{eval echo cutstr($value[subject],32);}--></a></li>
			<!--{/loop}-->
		</ul>
	</div>
	<!--{/if}-->


	<!--{if $action == "consume" && !empty($_GET['xid'])}-->
	<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}consumeitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,2/cachename/consume/tpl/consumer_hot/pagetype/storelist/usetype/consume/shopid/{$_GET['id']}"}--><!--new consume-->
	<!--{/if}-->

	<!--{if $action == "groupbuy" && !empty($_GET['xid'])}-->
	<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}groupbuyitems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%3D%203%20ORDER%20BY%20displayorder%20DESC%2Cdateline%20DESC/limit/0,2/cachename/groupbuy/tpl/groupbuy_hot/pagetype/storelist/usetype/groupbuy/shopid/{$_GET['id']}"}--><!--new groupbuy-->
	<!--{/if}-->


	<!--{if $action == "good" && !empty($_GET['xid'])}-->
	<!--{block name="sql" parameter="sql/SELECT%20%2A%20FROM%20{$_SC['tablepre']}gooditems%20WHERE%20shopid%20%3D%20{$shop['itemid']}%20AND%20grade%3D%203%20ORDER%20BY%20viewnum%20DESC%2Cdateline%20DESC/limit/0,2/cachename/sidegood/tpl/data/pagetype/storelist/usetype/good/shopid/{$_GET['id']}"}--><!--new good-->
	<!--{if $_SBLOCK['sidegood']}-->
	<div class="box hot">
		<h3>$lang['hotgood']</h3>
		<ul>
		<!--{loop $_SBLOCK['sidegood'] $good}-->
		<li><a href="store.php?id={$shop['itemid']}&action=good&xid={$good['itemid']}">{$good['subject']}</a></li>
		<!--{/loop}-->
		</ul>
	</div>
	<!--{/if}-->
	<!--{/if}-->
	<!--{if $_G['setting']['enablemap'] == 1}-->
	<script src="http://ditu.google.cn/maps?file=api&amp;v=2&amp;key={$_G['setting']['mapapikey']}&hl=zh-CN" type="text/javascript" charset="utf-8"></script>
	<div class="box">
		<h3>{$lang['shopmap']}</h3>
		<div style="width: 228px; height: 200px;" id="map_canvas"></div>
		<div><a class="map_bt" href="store.php?id={$shop['itemid']}&action=map" target="_blank">{$lang['originalmap']}</a></div>
		<br>
	</div>
	<script type="text/javascript" charset="utf-8">
		var map = new GMap2(document.getElementById("map_canvas"));
		var center = new GLatLng{$shop['mapapimark']};
		map.setCenter(center, 14);
		var marker = new GMarker(center, {draggable: false});
		map.enableScrollWheelZoom();
		map.addOverlay(marker);
	</script>
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