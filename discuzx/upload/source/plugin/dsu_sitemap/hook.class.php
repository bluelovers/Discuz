<?php
class plugin_dsu_sitemap {
    function global_footerlink() {
        global $_G;
        if($_G['inajax']) {
			return '';
		}
        $return='';
        $setting_securityauth=$_G['cache']['plugin']['dsu_sitemap']['securityauth'];
        if($_G['timestamp']-filemtime(DISCUZ_ROOT.'sitemap_baidu.xml')>($_G['cache']['plugin']['dsu_sitemap']['baidu_update']*3600)){
            $randnum=mt_rand(0, 1000000);
            $auth=md5($randnum.'|'.$_G['timestamp'].md5($_G['timestamp'].$setting_securityauth.$_G['authkey']));

            $return.="<img src='{$_G['siteurl']}plugin.php?id=dsu_sitemap:updatebaidu&timestamp={$_G['timestamp']}&auth={$auth}&randnum={$randnum}' height='1' width='1' style='display:none;' />";
        }
        if($_G['timestamp']-filemtime(DISCUZ_ROOT.'sitemap.xml')>($_G['cache']['plugin']['dsu_sitemap']['normal_update']*3600)){
            $randnum=mt_rand(0, 1000000);
            $auth=md5($randnum.'|'.$_G['timestamp'].md5($_G['timestamp'].$setting_securityauth.$_G['authkey']));
            $return.="<img src='{$_G['siteurl']}plugin.php?id=dsu_sitemap:updatenormal&timestamp={$_G['timestamp']}&auth={$auth}&randnum={$randnum}' style='display:none;' height='1' width='1' />";
        }
        return $return;
    }
}