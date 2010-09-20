<?exit?>

<!--/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: map.html.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */-->

<div class="main layout" id="map">
	<div class="content">
		<h3>$lang['shopmap']</h3>
		<div style="width: 978px; height: 600px;" class="main_map" id="mapObj">
		</div>
	</div>
</div>
<script src="http://ditu.google.cn/maps?file=api&amp;v=2&amp;key={$_G['setting']['mapapikey']}&hl=zh-CN" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
	var map = new GMap2(document.getElementById("mapObj"));
	var center = new GLatLng{$shop['mapapimark']};
	map.setCenter(center, 14);

	var marker = new GMarker(center, {draggable: false});
	GEvent.addListener(marker, "click", function() {
		marker.openInfoWindowHtml("<div style=\"text-align:left;\"><b>{$shop['subject']}</b><br />{$lang['telephone']}{$shop['tel']}<br />{$lang['address']}{$shop['address']}</div>");
  	});
	marker.openInfoWindowHtml("<div style=\"text-align:left;\"><b>{$shop['subject']}</b><br />{$lang['telephone']}{$shop['tel']}<br />{$lang['address']}{$shop['address']}</div>");
	map.addOverlay(marker);
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.enableGoogleBar()
	map.enableScrollWheelZoom();

</script>
