<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_custom.php 15100 2010-08-19 02:51:15Z zhaoxiongfei $
 */

$lang = array
(
	'custom_name' => '自定义广告',
	'custom_desc' => '通过在模版、HTML 文件中添加广告代码，可以在站点的任意页面添加广告。适用于懂得简单 HTML 知识的站长。<br /><br />
		<a href="javascript:;" onclick="prompt(\'请复制(CTRL+C)以下内容并添加到模板中，添加此广告位\', \'<!--{ad/custom_'.$_G['gp_customid'].'}-->\')" />内部调用</a>&nbsp;
		<a href="javascript:;" onclick="prompt(\'请复制(CTRL+C)以下内容并添加到 HTML 文件中，添加此广告位\', \'&lt;script type=\\\'text/javascript\\\' src=\\\''.$_G['siteurl'].'api.php?mod=ad&adid=custom_'.$_G['gp_customid'].'\\\'&gt;&lt;/script&gt;\')" />外部调用</a>',
	'custom_id_notfound' => '自定义广告不存在',
	'custom_codelink' => '内部调用',
	'custom_text' => '自定义广告',
);

?>