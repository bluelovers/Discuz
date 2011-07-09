<?php

shownav('pdnovel', 'index');

if($do == 'show'){

	@include_once DISCUZ_ROOT.'./source/module/'.$action.'/pdnovel_version.php';
	@include_once DISCUZ_ROOT.'./data/attachment/pdnovel/collect/pdnovel_key.php';

	showsubmenu('pdnovel_welcome', array(array('system_detail', 'pdnovel&operation=index', 1), array('pdnovel_index_getkey', 'pdnovel&operation=index&do=getkey', 0)));
	
	showtableheader('pdnovel_sys_info', 'fixpadding');
	showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
		cplang('pdnovel_version'),
		'Pdnovel '.PDNOVEL_VERSION.' Release '.PDNOVEL_RELEASE.' <a href="http://www.dke8.com/thread-46498-1-1.html" target="_blank">'.cplang('check_newversion').'</a>'
	));
	showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
		cplang('pdnovel_key'),
		PDNOVEL_KEY
	));
	showtablefooter();
	
	showtableheader('pdnovel_dev', 'fixpadding');
	showtablerow('', array('class="vtop td24 lineheight"'), array(
		cplang('dev_copyright'),
		'<span class="bold"><a href="http://www.dke8.com" class="lightlink2" target="_blank">剖度网络(广州)科技有限公司 (Poudu Inc.)</a></span>'
	));
	showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
		cplang('dev_manager'),
		'<a href="http://www.dke8.com/thread-46498-1-1.html" class="lightlink2 smallfont" target="_blank">刘栢威 (BaiWei \'Benwil\' Liu)</a>'
	));
	showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
		cplang('dev_team'),
		'<a href="http://www.dke8.com/thread-46498-1-1.html" class="lightlink2 smallfont" target="_blank">BaiWei \'benwil\' Liu</a>',
	
	));
	showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
		cplang('dev_links'),
		'<a href="http://www.comsenz.com" class="lightlink2" target="_blank">公司网站</a>,
			<a href="http://idc.comsenz.com" class="lightlink2" target="_blank">虚拟主机</a>,
			<a href="http://www.comsenz.com/category-51" class="lightlink2" target="_blank">购买授权</a>,
			<a href="http://www.discuz.com/" class="lightlink2" target="_blank">&#x44;&#x69;&#x73;&#x63;&#x75;&#x7A;&#x21;&#x20;产品</a>,
			<a href="http://www.comsenz.com/downloads/styles/discuz" class="lightlink2" target="_blank">模板</a>,
			<a href="http://www.comsenz.com/downloads/plugins/discuz" class="lightlink2" target="_blank">插件</a>,
			<a href="http://faq.comsenz.com" class="lightlink2" target="_blank">文档</a>,
			<a href="http://www.discuz.net/" class="lightlink2" target="_blank">讨论区</a>'
	));
	showtablefooter();
	
}elseif($do == 'getkey'){
	$temp = @file_get_contents('http://www.dke8.com/manual/novle/pdgetkey.php?mod=pdnovel&domain='.$_SERVER['HTTP_HOST']);
	if(!empty($temp)){
		$result = @file_put_contents('data/attachment/pdnovel/collect/pdnovel_key.php', '<?php'."\r\ndefine('PDNOVEL_KEY', '{$temp}');\r\n".'?>');
		if($result>0){
			cpmsg('pdnovel_index_keysucceed', 'action=pdnovel&operation=index', 'succeed');
		}else{
			cpmsg('pdnovel_index_keynotwrite', 'action=pdnovel&operation=index', 'error');
		}
	}else{
		cpmsg('pdnovel_index_keyerror', 'action=pdnovel&operation=index', 'error');
	}	
}

?>