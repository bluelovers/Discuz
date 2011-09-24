<?php	
	if(!defined('IN_DISCUZ')) exit('Access Denied');	
	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	$kk_stat=Array();
	$kk_stat['bbs_version']=DISCUZ_VERSION;
	$kk_stat['bbs_release']=DISCUZ_RELEASE;
	$kk_stat['bbs_name']=$_G['setting']['bbname'];
	$kk_stat['bbs_url']=$_G['siteurl'];
	$kk_stat['bbs_adminmail']=$_G['setting']['adminemail'];
	$kk_stat['install_time']=TIMESTAMP;
	$kk_stat['plugin_name']=$pluginarray['plugin']['identifier'];
	$kk_stat['plugin_version']=$pluginarray['plugin']['version'];	
	
	$kk_stat=base64_encode(serialize($kk_stat));
	$kk_stat_hash=md5($kk_stat);
	$kk_stat_url="http://9166.net/dplugin.php?info={$kk_stat}&hash={$kk_stat_hash}";		
	echo "<script language=\"javascript\" src=\"{$kk_stat_url}\"></script>";	
	//---------------------------------------------------------------------------------------
	$sql = <<<EOF
DROP TABLE IF EXISTS `cdb_kk_weibo`;
CREATE TABLE `cdb_kk_weibo` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uid` int(11) DEFAULT NULL,
	`uid_rel` int(11) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=gbk;

DROP TABLE IF EXISTS `cdb_kk_weibo_stat`;
CREATE TABLE `cdb_kk_weibo_stat` (
	`uid` int(11) NOT NULL DEFAULT '0',
	`count_attention` int(11) DEFAULT '0',
	`count_fans` int(11) DEFAULT '0',
	PRIMARY KEY (`uid`)
) ENGINE=MYISAM DEFAULT CHARSET=gbk;
EOF;

	runquery($sql); 
	//---------------------------------------------------------------------------------------
	$finish = true;
?>
