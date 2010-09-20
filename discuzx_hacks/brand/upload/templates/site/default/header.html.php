<?exit?>
<!--{eval if(!empty($location['title'])) $seo_title = $location['title'] . ' - ' . $seo_title;}-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset={$_G['charset']}" />
		<title>{$seo_title}</title>
		<meta name="description" content="{$seo_description}" />
		<meta name="keywords" content="{$seo_keywords}" />

		<script charset="utf-8" type="text/javascript" src="static/js/jquery.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/common.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/viewgoodspic.js"></script>

		<link type="text/css" rel="stylesheet" href="static/css/common.css" />

		<link type="text/css" rel="stylesheet" href="templates/site/{$_G['setting']['sitetheme']}/common.css" />
		<link type="text/css" rel="stylesheet" href="templates/site/{$_G['setting']['sitetheme']}/{CURSCRIPT}.css" />
		<!--{if !empty($active['index'])}-->
		<script charset="utf-8" type="text/javascript" src="static/js/scroll.js"></script>
		<!--{/if}-->

	</head>

	<body>
		<div id="append_parent"></div>
		<!--{template 'templates/site/default/header_common.html.php', 1}-->
		<div id="position">
			<a href="{$_G['setting']['wwwurl']}">{$_G['setting']['wwwname']}</a>
			<!--{if $location['name']}-->
			<a href="index.php">$_G['setting']['sitename']</a>
			<!--{if $_GET['tagid']>0}-->
			<a href="street.php?catid={$_GET['catid']}">{$location['name']}</a><span>{$location['tagname']}</span>
			<!--{else}-->
			<span>{$location['name']}</span>
			<!--{/if}-->
			<!--{else}-->
			<span>$_G['setting']['sitename']</span>
			<!--{/if}-->
			<a href="attend.php" class="attend">$lang['attend']</a>
		</div>