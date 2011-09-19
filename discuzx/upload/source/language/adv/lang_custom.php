<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_custom.php
 *
 *      Translated by discuzindo.net
 */

$lang = array
(
	'custom_name'		=> 'Custom Adv',//'自定义广告',
	'custom_desc'		=> 'Description: Add custom adv code in templates or HTML file.<br /><br />
				<a href="javascript:;" onclick="prompt(\'Please copy (CTRL+C) the content below to templates\', \'<!--{ad/custom_'.$_G['gp_customid'].'}-->\')" />Internal js call/a>&nbsp;
				<a href="javascript:;" onclick="prompt(\'Please copy (CTRL+C) the content below to HTML files\', \'&lt;script type=\\\'text/javascript\\\' src=\\\''.$_G['siteurl'].'api.php?mod=ad&adid=custom_'.$_G['gp_customid'].'\\\'&gt;&lt;/script&gt;\')" />External js call</a>',
	'custom_id_notfound'	=> 'Custom Adv does not Exist',//'自定义广告不存在',
	'custom_codelink'	=> 'Internal Js Call',//'内部调用',
	'custom_text'		=> 'Custom Advertising',//'自定义广告',
);

