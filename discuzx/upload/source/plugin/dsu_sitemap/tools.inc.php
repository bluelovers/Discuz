<?php
if(!defined('IN_ADMINCP')) exit('Access Denied');

if($_G['gp_formhash']!=FORMHASH){
    echo '<div class="infobox"><h4 class="infotitle2">'.lang('plugin/dsu_sitemap', 'operation').'</h4>&nbsp;<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&identifier=dsu_sitemap&pmod=tools&formhash='.FORMHASH.'&mod=submitgoogle\'" value="'.lang('plugin/dsu_sitemap', 'submitgoogle').'">&nbsp;&nbsp;<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&identifier=dsu_sitemap&pmod=tools&formhash='.FORMHASH.'&mod=submitbaidu\'" value="'.lang('plugin/dsu_sitemap', 'submitbaidu').'">&nbsp;&nbsp;<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&identifier=dsu_sitemap&pmod=tools&formhash='.FORMHASH.'&mod=submitbing\'" value="'.lang('plugin/dsu_sitemap', 'submitbing').'">&nbsp;&nbsp;<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&identifier=dsu_sitemap&pmod=tools&formhash='.FORMHASH.'&mod=flush\'" value="'.lang('plugin/dsu_sitemap', 'flush').'">&nbsp;<br/><br/>&#x552E;&#x540E;&#x670D;&#x52A1;&#x652F;&#x6301;&#xFF1A;QQ 527544390&#xFF08;&#x5468;&#x672B;&#x5728;&#x7EBF;&#xFF09;<br/>&#x63A5;&#x53D7;&#x8BBE;&#x7F6E;&#x95EE;&#x9898;&#xFF0C;&#x4EE5;&#x53CA;&#x517C;&#x5BB9;&#x6027;&#x95EE;&#x9898;&#x5904;&#x7406;&#x3002;</div>';
			exit;
}elseif($_G['gp_mod']=='flush'){
    include_once DISCUZ_ROOT.'source/plugin/dsu_sitemap/hook.class.php';
 /*   @unlink(DISCUZ_ROOT.'sitemap_baidu.xml');
    @unlink(DISCUZ_ROOT.'sitemap.xml');*/
    loadcache('plugin');
    cpmsg('dsu_sitemap:flushsuccessful', 'action=plugins&operation=config&do=91&identifier=dsu_sitemap&pmod=tools', 'succeed', array('extra'=> plugin_dsu_sitemap::global_footerlink()));
}elseif($_G['gp_mod']=='submitgoogle'){
    echo str_replace('{_G/siteurl}', $_G['siteurl'], lang('plugin/dsu_sitemap', 'googleinfo'));
}elseif($_G['gp_mod']=='submitbaidu'){
    echo str_replace('{_G/siteurl}', $_G['siteurl'], lang('plugin/dsu_sitemap', 'baiduinfo'));
}elseif($_G['gp_mod']=='submitbing'){
    cpmsg('dsu_sitemap:submitsuccessful_bing', 'action=plugins&operation=config&do=91&identifier=dsu_sitemap&pmod=tools', 'succeed', array('extra'=>'<img src="http://api.moreover.com/ping?u='.rawurlencode($_G['siteurl'].'sitemap.xml').'"/>'));
}else{
    cpmsg('undefined_action', '', 'error');
}

function dsu_sitemap_langtools($code, $vars) {
    if($vars && is_array($vars)) {
		foreach($vars as $k => $v) {
			$searchs[] = '['.$k.']';
			$replaces[] = $v;
		}
        return str_replace($searchs, $replaces, $code);
	}else{
        return $code;
    }
}