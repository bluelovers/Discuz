<?exit?>

<!--{if empty($_GET['xid'])}-->
<div id="goodslist" class="main layout store_list">
	<div class="content">
		<h3>{$lang['goodlist']}</h3>
		<div class="title">
			<div class="name1">$lang['goodimg']</div>
			<div class="foodlabels">$lang['goodintro']</div>
		</div>
		<div id="searchList">
			<!--{loop $goodlist $good}-->
			<div class="everylist">
				<div class="name">
					<dl>
						<dt>
						<span>
							<a target="_blank" href="store.php?id={$shop['itemid']}&action=good&xid={$good['itemid']}">
								<img width="100" height="80" src="{$good['thumb']}" alt="{$good['subject']}" />
							</a>
						</span>
						</dt>
						<dd>
						<h5><a target="_blank" href="store.php?id={$shop['itemid']}&action=good&xid={$good['itemid']}">$good['subject']</a></h5>
						<ul>
							<!--{if $good['minprice'] > 0 || $good['maxprice'] > 0}--><li><span>{$lang['price']}{$lang['colon']}</span><em><!--{if $good['minprice'] > 0}-->{$good['minprice']}<!--{/if}-->
								<!--{if $good['minprice'] > 0 && $good['maxprice'] > 0}--> - <!--{/if}-->
								<!--{if $good['maxprice']>0}-->{$good['maxprice']}<!--{/if}--></em> $lang['yuan']</li><!--{/if}-->
							<li><span>{$lang['dateline']}{$lang['colon']}</span>{$good['time']}</li>
						</ul>
						<div class="goods_intro">{$good['intro']}</div>
						</dd>
					</dl>
				</div>
				
			</div>
			<!--{/loop}-->
		</div>
		$goodlist_multipage
	</div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->
</div>
<!--{else}-->
<div id="goods" class="main layout">
	<div id="goodsDiv" class="content">
		<!--{if $_G['uid'] && !$_G['myshopid'] && !ckfounder($_G['uid'])}-->
		<span style="color: font-size: 12px; margin-left: 700px; margin-top: 5px; cursor: pointer; position: absolute;" onclick="report('good', '{$good['itemid']}');">$lang['report']</span>
		<!--{/if}-->
		<h3 id="goodsName" name="goodsName">{$good['subject']}</h3>
		<div class="goodspic1">
			<span>
				<img id="goodsPicture" onload="resize_image(this, 300, 220);" src="{$good['thumb']}" srcimg="{$good['subjectimage']}" alt="{$good['subject']}" title="{$good['subject']}" />
			</span>
		</div>
		<ul class="goodsinfo1">
			<li><span>{$lang['priceo']}{$lang['colon']}</span><strong id="goodsPrice" {if $good['minprice']} class="price2"{/if}>{$good['priceo']}&nbsp;$lang['yuan']</strong></li>
			<!--{if $good['minprice'] > 0 || $good['maxprice'] > 0}--><li><span>{$lang['price']}{$lang['colon']}</span><strong id="goodsMemberPrice" class="price1"><!--{if $good['minprice'] > 0}-->{$good['minprice']}<!--{/if}--><!--{if $good['minprice'] > 0 && $good['maxprice'] > 0}--> - <!--{/if}--><!--{if $good['maxprice'] > 0}-->{$good['maxprice']}<!--{/if}--> $lang['yuan']</strong></li><!--{/if}-->
			<li><span>{$lang['shoptel']}{$lang['colon']}</span>{$shop['tel']}<em></em></li>
			<li><span>{$lang['shopaddr']}{$lang['colon']}</span>{$shop['address']}
			<!--{if $_G['setting']['enablemap'] == 1}-->
			<a href="#goodsName" onclick="openmap();return false;"><img style="vertical-align: top; text-decoration: none;margin:0px 0 0 5px;" src="static/image/mapmarker.gif" /></a>
			<!--{/if}-->
			</li>
			<li><span>{$lang['viewnum']}{$lang['colon']}</span><font id="goodsViews">{$good['viewnum']}</font>$lang['viewnumunit']</li>
			<li><a href="javascript:void(0);" title="$lang['originalmap']"><img style="width: 135px; height: auto;" id="showBigPic" src="static/image/enlarge.gif" alt="$lang['originalmap']" onload="thumbImg(this)" onclick="zoom(this, $('#goodsDiv').find('#goodsPicture').attr('srcimg'), $('#goodsDiv').find('h3').html())"></a></li>
		</ul>
		<!--{if $_G['setting']['enablemap'] == 1}-->
		<script type="text/javascript">
			//<![CDATA[
		var gaJsHost = "http://ditu.";
		document.write(unescape("%3Cscript  charset='utf-8' src='" + gaJsHost + "google.cn/maps?file=api&v=2&key={$_G['setting']['mapapikey']}&hl=zh-CN' type='text/javascript'%3E%3C/script%3E"));
		//]]>
		</script>
		<script type="text/javascript" charset="utf-8">
			//<![CDATA[
		function openmap() {
			document.getElementById('maptop').style.display="block";
			var map = new GMap2(document.getElementById("map"));
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
			map.enableGoogleBar();
			map.enableScrollWheelZoom();
			var center = new GLatLng{$shop['mapapimark']};
			var markero = false;
			var zoomnum = 14;
			map.setCenter(center, zoomnum);

			var marker = new GMarker(center, {draggable: false});
			GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml("<div style=\"text-align:left;\"><b>{$shop['subject']}</b><br />{$lang['shoptel']}{$lang['colon']} {$shop['tel']}<br />{$lang['shopaddr']}{$lang['colon']} {$shop['address']}</div>");
					});
			marker.openInfoWindowHtml("<div style=\"text-align:left;\"><b>{$shop['subject']}</b><br />{$lang['shoptel']}{$lang['colon']} {$shop['tel']}<br />{$lang['shopaddr']}{$lang['colon']} {$shop['address']}</div>");

			map.addOverlay(marker);
			return false;
		}
function closemap() {
	document.getElementById('maptop').style.display="none";
}
</script>
<!--{/if}-->
<div style="clear:both; width:200px;height:5px; background:#fff;"></div>
<div class="intro2">
	<h4>$lang['details']</h4>
	<div id="goodsDescription">
		<div style="padding:5px;color:#000; border:none; border-bottom:#ccc 1px dashed;font-weight:normal;">{$good['message']}</div>
		<!--{if !empty($relatedarr)}-->
		<ul style="margin-top:10px;">
			<label>$lang['relatedinfo']</label>
			<!--{loop $relatedarr $related}-->
			<!--{eval $typename = $lang['header_'.$related['type']];}-->
			<li><p>[{$typename}]<a href="store.php?id={$shop['itemid']}&action={$related['type']}&xid={$related['itemid']}" target="_blank" title="{$related['subject']}">{$related['simplesubject']}</a></p></li>
			<!--{/loop}-->
		</ul>
		<!--{/if}-->
	</div>
</div>
<div style="clear:both;"></div>
<div class="news_msg">
	<!--{eval include template('templates/store/default/comment.html.php', 1);}-->
</div>
	</div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->
</div>
<div class="main">
	<div class="sidebar">
		<div class="box" id="maptop" style="width:600px;height:430px;position:absolute;display:none;top:400px;left:400px;background-color:#FFFFFF;">
			<h3 style="position:">{$lang['shopaddr']}{$lang['colon']}</h3>
			<a href="#" onclick="closemap();return false;" style="position:absolute;right:5px;top:5px;">$lang['close']</a>
			<div id="map" style="width:600px;height:400px;">

			</div>
		</div>
</div></div>
<div id="append_parent"></div>
<!--{/if}-->
<script>
	<!--
	$(function(){
		$("#consumerlist dl").hover(function(){$(this).addClass("thison")},function(){$(this).removeClass("thison")})
	});
	$(document).ready(function() {

		$("#searchList .everylist").hover(function() {
			$(this).css({'background' : 'url(static/image/bg_goodssearch_hover.png) repeat-x scroll 0 bottom'});

			} , function() {
			$(this).css({'background' : 'url(static/image/bg_goodssearch.png) repeat-x scroll 0 bottom'});

		});
	});
	//-->
</script>