<?exit?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8'charset']}" />
		<title>{$seo_title}</title>
		<meta name="description" content="{$seo_description}" />
		<meta name="keywords" content="$seo_keywords" />
		<meta http-equiv="x-ua-compatible" content="ie=7" />
		<script charset="utf-8" type="text/javascript" src="static/js/jquery.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/common.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/viewgoodspic.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/jquery.cycle.lite.min.js"></script>

		<link type="text/css" rel="stylesheet" href="static/css/common.css" />
		<link type="text/css" rel="stylesheet" href="templates/site/{$_G['setting']['sitetheme']}/common.css" />
		<link type="text/css" rel="stylesheet" href="static/css/common_store.css" />
		<!--{if $btheme=='default'}-->
		<link rel="stylesheet" type="text/css" href="static/css/store.css" />
		<!--{else}-->
		<link rel="stylesheet" type="text/css" href="templates/store/{$btheme}/style.css" />
		<!--{/if}-->
		<!--{if $cur_store_style}-->
		<link type="text/css" rel="stylesheet" href="templates/store/{$btheme}/{$cur_store_style}.css">
		<!--{/if}-->
	</head>

	<body>
		<div id="append_parent"></div>
		<!--{template 'templates/site/default/header_common.html.php', 1}-->
		<div id="position">
			<a href="{$_G['setting']['wwwurl']}">{$_G['setting']['wwwname']}</a>
			<a href="index.php">$_G['setting']['sitename']</a>
			<!--{loop $guidearr $value}-->
			<a href="$value[url]">$value['name']</a>
			<!--{/loop}-->
			<!--{if !$shop_close}-->
			<span>{$shop['subject']}</span>
			<!--{/if}-->
			<a href="attend.php" class="attend">$lang['attend']</a>
		</div>
		<div class="bg_banner">
			<img src="{$shop['banner']}" width="980" height="150" />
		</div>
		<div id="header" class="layout">
			<!--{if !$shop_close}-->
			<!--{eval $shop['styletitle'] = pktitlestyle($shop['styletitle']);}-->
			<h2  style="{$shop['styletitle']}">{$shop['subject']}</h2>
			<h4>{$shop['subject']}</h4>
			<div class="siteurl">
				<a title="$lang['copylink']" href="javascript:;" onclick="javascript:setCopy('{B_URL}/{$shop[shopurl]}');"><img src="static/image/icon_siteinfo.gif" alt="$lang['copylink']"></a>
				<a title="$lang['addfav']" href="javascript:;" onclick="javascript:addBookmark('{$shop[subject]}', '{B_URL}/{$shop[shopurl]}');"><img src="static/image/icon_add.gif" alt="$lang['addfav']"></a>
				<a title="{$lang['shopaddr']}{$lang['colon']}" href="{$shop['shopurl']}">{B_URL}/{$shop['shopurl']}</a>
			</div>

			<ul class="menu">
				<!--{loop $shop['nav'] $v}-->
				<li{$mouseover[$v['flag']]}><a href="$v['url']" title="$v['name']" $v['target']><span $v['style']>$v['name']</span></a></li>
				<!--{/loop}-->
			</ul>
			<!--{/if}-->
		</div><!-- Header 結束 -->
		<script charset="utf-8" type="text/javascript" src="static/js/effect_common.js"></script>
		<script type="text/javascript">
			function addupcid(upcid) {
				$("#upcid").val(upcid);
				document.getElementById("reply").style.display = "";
				document.getElementById("replyname").innerHTML= document.getElementById('comauth_'+upcid).innerHTML;
				document.getElementById("commentmessage").focus();
			}
		</script>