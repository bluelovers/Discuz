<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$nobbname = false;
$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['forum']);
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][2]['navname'];
} else {
	$nobbname = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<base href="<?php echo $_G['siteurl']; ?>" />
<title><?php
	if(!empty($navtitle)) {
		echo $navtitle.' - ';
	}
	if(!$nobbname) {
		echo $_G['setting']['bbname'].' - ';
	}
?>Powered by Discuz! Archiver</title>
<?= $_G['setting']['seohead'] ?>
<meta name="keywords" content="<?php if(!empty($_G['setting']['seokeywords']['forum'])): echo $_G['setting']['seokeywords']['forum'].', ';endif; if(!empty($metakeywords)): $metakeywords; endif;?>" />
<meta name="description" content="<?php echo $_G['setting']['seodescription']; if(!empty($metadescription)): $metadescription; endif; echo $_G['setting']['bbname'];?> - Discuz! Board" />
<meta name="generator" content="Discuz! <?php echo $_G['setting']['version']; ?>" />
<meta name="author" content="Discuz! Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2010 Comsenz Inc." />
<style type="text/css">
	body {font-family: Verdana;FONT-SIZE: 12px;MARGIN: 0;color: #000000;background: #ffffff;}
	img {border:0;}
	li {margin-top: 8px;}
	.page {padding: 4px; border-top: 1px #EEEEEE solid}
	.author {background-color:#EEEEFF; padding: 6px; border-top: 1px #ddddee solid}
	#nav, #content, #footer {padding: 8px; border: 1px solid #EEEEEE; clear: both; width: 95%; margin: auto; margin-top: 10px;}
	#loginform {text-align: center;}
</style>
</head>
<body vlink="#333333" link="#333333">
<h2 style="text-align: center; margin-top: 20px"><?php echo $_G['setting']['bbname']; ?>'s Archiver </h2>