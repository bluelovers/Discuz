<?php
    if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	   exit('Access Denied');
    }
    
    include 'source/plugin/v63ht/config.inc.php';
     if($_GET['pdo'] =='xgsz'){
        $config = "<?php \r\n 
        \$setting = array(
        'isopen'=>'".$_POST['isopen']."',
        'ispop'=>'".$_POST['ispop']."',
        'qz'=>'".$_POST['qz']."',
        'htid'=>'".$_POST['htid']."',
        'httitle'=>'".$_POST['httitle']."',
        'description'=>'".$_POST['description']."'
    );";
        $file_pointer = fopen("source/plugin/v63ht/config.inc.php","w+");        
        fwrite($file_pointer,$config);
        fclose($file_pointer);
        
     cpmsg('修改成功', 'action=plugins&operation=config&do='.$pluginid.'&identifier=v63ht&pmod=admin');
    }
    
    showformheader('plugins&operation=config&do='.$pluginid.'&identifier=v63ht&pmod=admin&pdo=xgsz');
    showtableheader();
    showtitle("每日话题设置(在使用过程中有任何疑问请登录www.v63app.com官网 @陈强)");
    
    showsetting('是否启用弹窗', 'ispop', $setting['ispop'], 'radio');
    
    showsetting('是否强制参与', 'qz', $setting['qz'], 'radio');
    
    showsetting('话题贴ID', 'htid',$setting['htid'], 'text','',0,'所有的互动都将会以回帖的形式回复该帖子');
    showsetting('话题名字', 'httitle',$setting['httitle'], 'text','',0,'');
    showsetting('话题介绍','description',$setting['description'],'textarea','',0,'支持html');
    showsubmit('submit');
    showtablefooter();
    showformfooter();
?>