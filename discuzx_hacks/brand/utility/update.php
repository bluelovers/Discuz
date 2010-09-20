<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 4491 2010-09-15 09:54:10Z xuhui $
 */

@define('IN_BRAND_UPDATE', true);

if(!@include('./common.php')) {
	exit('请将本文件移到程序根目录再运行!');
}

error_reporting(0);
@set_time_limit(300);

//不让计划任务执行
$_SGLOBAL['cronnextrun'] = $_G['timestamp']+3600;

//新SQL
$sqlfile = B_ROOT.'./data/install.sql';
if(!file_exists($sqlfile)) {
	show_msg('最新的SQL不存在,请先将最新的数据库结构文件 install.sql 已经上传到 ./data 目录下面后，再运行本升级程序');
}

$lockfile = './data/update.lock';
if(file_exists($lockfile)) {
	show_msg('请您先登录服务器ftp，手工删除 data/update.lock 文件，再次运行本文件进行品牌空间升级。');
}

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

//提交处理
if(submitcheck('delsubmit')) {
	//删除表
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			DB::query("DROP TABLE ".tname($tname));
		}
	}
	//删除字段
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					DB::query("ALTER TABLE ".tname($tname)." DROP PRIMARY KEY", 'SILENT');//屏蔽错误
				} elseif($col == 'UNIQUE') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".tname($tname)." DROP INDEX `$index`", 'SILENT');//屏蔽错误
					}
				} elseif($col == 'KEY') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".tname($tname)." DROP INDEX `$index`", 'SILENT');//屏蔽错误
					}
				} else {
					DB::query("ALTER TABLE ".tname($tname)." DROP `$col`");
				}
			}
		}
	}

	show_msg('删除表和字段操作完成了', 'update.php?step=delete');
}

if(empty($_GET['step'])) $_GET['step'] = 'start';

//处理开始
if($_GET['step'] == 'start') {

	show_msg('
	<div id="ready">
	本升级程序会参照最新的SQL文,对您的品牌空间数据库进行升级。<br><br>
	升级前请做好以下前期工作：<br><br>
	<!--<b>第一步：</b><br>
	关闭站点，避免升级时有用户写入数据导致数据出错。<br><br>-->
	<b>第一步：</b><br>
	备份当前的数据库，避免升级失败，造成数据丢失而无法恢复；<br><br>
	<b>第二步：</b><br>
	将程序包 ./upload/ 目录中，除 config.new.php 文件、./install/ 目录以外的其他所有文件，全部上传并覆盖当前程序。<b>特别注意的是，最新数据库结构 ./data/install.sql 文件不要忘记上传，否则会导致升级失败</b>；<br><br>
	<b>第三步：</b><br>
	确认已经将程序包中最新的 update.php 升级程序上传到服务器程序根目录中<br>
	<br><br>
	<a href="update.php?step=check">已经做好了以上工作，升级开始</a><br><br>
	特别提醒：为了数据安全，升级完毕后，不要忘记删除本升级文件。
	</div>
	');

} elseif ($_GET['step'] == 'check') {

	//UCenter_Client
	include_once B_ROOT.'./uc_client/client.php';
	if(!function_exists('uc_check_version')) {
		show_msg('请将品牌空间程序包中最新版本的 ./upload/uc_client 上传至程序根目录覆盖原有目录和文件后，再尝试升级。');
	}

	$uc_root = get_uc_root();
	$return = uc_check_version();
	if (empty($return)) {
		$upgrade_url = 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=sql';
	} else {
		if(strcmp($return['db'], '1.5.0') >= 0) {
			header("Location: update.php?step=sql");//UC升级完成
			exit();
		}
		$upgrade_url = 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=check';
	}

	$ucupdate = UC_API."/upgrade/upgrade2.php?action=db&forward=".urlencode($upgrade_url);

	show_msg('<b>您的 UCenter 程序还没有升级完成，请如下操作：</b><br>品牌空间支持了最新版本的UCenter，请先升级您的UCenter。<br><br>
		1. <a href="http://download.comsenz.com/UCenter/1.5.0/" target="_blank">点击这里下载对应编码的 UCenter 1.5.0 程序</a><br>
		2. 将解压缩得到的 ./upload 目录下的程序覆盖到已安装的UCenter目录 <b>'.($uc_root ? $uc_root : UC_API).'</b><br>
		&nbsp;&nbsp;&nbsp; (确保其升级程序 <b>./upgrade/upgrade2.php</b> 也已经上传到UCenter的 ./upgrade 目录)<br><br>
		确认完成以上UCenter程序升级操作完成后，您才可以：<br>
		<a href="'.$ucupdate.'" target="_blank">新窗口中访问 upgrade2.php 进行UCenter数据库升级</a><br>
		在打开的新窗口中，如果UCenter升级成功，程序会自动进行下一步的升级。<br>这时，您关闭本窗口即可。
		<br><br>
		如果您无法通过上述UCenter升级步骤，请调查问题后，务必将UCenter正常升级后，再继续本升级程序。<br>或者您可以：<br><a href="update.php?step=sql" style="color:#CCC;">跳过UCenter升级</a>，但这可能会带来一些未知兼容问题。');

} elseif ($_GET['step'] == 'sql') {

	$cachefile = B_ROOT.'./data/system/update_model.cache.php';
	@unlink($cachefile);

	//config.php检测
	$newconfigvalues = array(
		'var' => array('$_SC', '$_SC[\'dbhost\']', '$_SC[\'dbuser\']', '$_SC[\'dbpw\']', '$_SC[\'dbname\']', '$_SC[\'tablepre\']', '$_SC[\'pconnect\']', '$_SC[\'dbcharset\']', '$_SC[\'siteurl\']',
						'$_SC[\'bbs_dbhost\']', '$_SC[\'bbs_dbuser\']', '$_SC[\'bbs_dbpw\']', '$_SC[\'bbs_dbname\']', '$_SC[\'bbs_dbpre\']', '$_SC[\'bbs_url\']',
						'$_SC[\'founder\']', '$_SC[\'cookiepre\']', '$_SC[\'cookiedomain\']', '$_SC[\'cookiepath\']', '$_SC[\'charset\']',
						'$_SC[\'tplrefresh\']', '$_SC[\'cachegrade\']'),
		'define' => array('UC_CONNECT', 'UC_DBHOST', 'UC_DBUSER', 'UC_DBPW', 'UC_DBNAME', 'UC_DBCHARSET', 'UC_DBTABLEPRE', 'UC_DBCONNECT', 'UC_KEY', 'UC_API', 'UC_CHARSET', 'UC_IP', 'UC_APPID', 'UC_PPP')
	);
	$configcontent = sreadfile(B_ROOT.'/config.php');
	preg_match_all("/([$].*?)[\s\t\n]/i", $configcontent, $configvars);
	preg_match_all("/define\('(UC_.*?)'/i", $configcontent, $configdefines);
	$scarcity = array();
	foreach($newconfigvalues as $key => $val) {
		foreach($val as $value) {
			if(!in_array($value, ($key == 'var' ? $configvars[1] : $configdefines[1]))) $scarcity[] = $value;
		}
	}
	if(!empty($scarcity)) {
		show_msg('当前服务器上“config.php”文件不是最新，需要您对其进行更新。<br/><br/>
				文件中缺少的参数:<br/>'.implode('<br/>', $scarcity).'<br/><br/>
				请参考程序包 ./upload 目录中，config.new.php文件，将现行config.php文件更新后，再运行本升级程序');
	}

	//新的SQL
	$sql = sreadfile($sqlfile);
	preg_match_all("/CREATE\s+TABLE\s+`?brand\_(.+?)`?\s+\((.+?)\)\s+(TYPE|ENGINE)\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('最新的SQL不存在,请先将最新的数据库结构文件 install.sql 已经上传到 ./data 目录下面后，再运行本升级程序');
	}

	//升级表
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	if($i>=count($newtables)) {
		//处理完毕
		show_msg('数据库结构升级完毕，进入下一步操作', 'update.php?step=data');
	}

	//当前处理表
	$newtable = $newtables[$i];
	$newcols = getcolumn($newsqls[$i]);

	//获取当前SQL
	if(!$query = DB::query("SHOW CREATE TABLE ".tname($newtable), 'SILENT')) {
		//添加表
		preg_match("/(CREATE TABLE .+?)\s+[TYPE|ENGINE]+\=/is", $newsqls[$i], $maths);
		if(strpos($newtable, 'session')) {
			$type = mysql_get_server_info() > '4.1' ? " ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ): " TYPE=HEAP";
		} else {
			$type = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ): " TYPE=MYISAM";
		}
		$usql = $maths[1].$type;
		$usql = str_replace("CREATE TABLE brand_", 'CREATE TABLE '.$_SC['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE `brand_", 'CREATE TABLE `'.$_SC['tablepre'], $usql);
		if(!DB::query($usql, 'SILENT')) {
			show_msg('['.$i.'/'.count($newtables).']添加表 '.tname($newtable).' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>'.shtmlspecialchars($usql));
		} else {
			$msg = '['.$i.'/'.count($newtables).']添加表 '.tname($newtable).' 完成';
		}
	} else {
		$value = DB::fetch($query);
		$oldcols = getcolumn($value['Create Table']);

		//获取升级SQL文
		$updates = array();
		foreach ($newcols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) $updates[] = "DROP PRIMARY KEY";
					$updates[] = "ADD PRIMARY KEY $value";
				}
			} elseif($key == 'UNIQUE') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['UNIQUE'][$subkey])) {
						if($subvalue != $oldcols['UNIQUE'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD UNIQUE KEY `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD UNIQUE KEY `$subkey` $subvalue";
					}
				}
			} elseif ($key == 'KEY') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['KEY'][$subkey])) {
						if($subvalue != $oldcols['KEY'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(str_replace('mediumtext', 'text', $value) != str_replace('mediumtext', 'text', $oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$updates[] = "ADD `$key` $value";
				}
			}
		}

		//升级处理
		if(!empty($updates)) {
			$usql = "ALTER TABLE ".tname($newtable)." ".implode(', ', $updates);
			if(!DB::query($usql, 'SILENT')) {
				show_msg('['.$i.'/'.count($newtables).']升级表 '.tname($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.shtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
			} else {
				$msg = '['.$i.'/'.count($newtables).']升级表 '.tname($newtable).' 完成';
			}
		} else {
			$msg = '['.$i.'/'.count($newtables).']检查表 '.tname($newtable).' 完成，不需升级';
		}
	}

	//处理下一个
	$next = '?step=sql&i='.($_GET['i']+1);
	show_msg($msg, $next);

} elseif ($_GET['step'] == 'data') {

	if(empty($_GET['op'])) $_GET['op'] = 'setting';

	if($_GET['op'] == 'setting') {

		$nextop = 'setting2';

		DB::query("TRUNCATE TABLE ".tname('crons'));
		DB::query("REPLACE INTO ".tname('crons')." (`cronid`, `available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES
		(1, 1, 'system', '更新店铺状态', 'updateshopgrade.php', 0, 0, -1, -1, 0, '0'),
		(2, 0, 'system', '更新商品状态', 'updategoodgrade.php', 0, 0, -1, -1, 0, '15'),
		(3, 0, 'system', '更新消费卷状态', 'updateconsumegrade.php', 0, 0, -1, -1, 1, '15'),
		(4, 0, 'system', '更新公告状态', 'updatenoticegrade.php', 0, 0, -1, -1, 2, '15'),
		(5, 0, 'system', '更新团购状态', 'updategroupbuygrade.php', 0, 0, -1, -1, 3, '15');
		");
		DB::query("REPLACE INTO ".tname('settings')." (`variable`, `value`) VALUES
		('commorderby', '1'),
		('groupbuysearchperpage', '10'),
		('groupbuyperpage', '10'),
		('auditnewshops', '1'),
		('multipleshop', '0'),
		('defaultshopgroup', ''),
		('sitetheme', 'default');
		");
		DB::query("REPLACE INTO ".tname('reportreasons')." (`rrid`, `type`, `content`) VALUES
		(1, '', '信息有误'),
		(2, '', '不切实际');
		");

		DB::query("UPDATE ".tname('modelcolumns')." SET isimage='1' WHERE formtype='img';");

		show_msg("[数据升级] 计划任务 全部结束，进入下一步", 'update.php?step=data&op='.$nextop);

	} elseif($_GET['op'] == 'setting2') {

		$nextop = 'setting_grade';
		DB::query("REPLACE INTO ".tname('models')." (`mid`, `modelname`, `modelalias`, `allowpost`, `allowguest`, `allowgrade`, `allowcomment`, `allowrate`, `allowguestsearch`, `allowfeed`, `searchinterval`, `allowguestdownload`, `downloadinterval`, `allowfilter`, `listperpage`, `seokeywords`, `seodescription`, `thumbsize`, `tpl`, `fielddefault`) VALUES (6, 'groupbuy', '团购', 0, 0, 0, 0, 0, 0, 1, 0, 0, 30, 1, 10, '', '', '400,300', 'default', 'subject = 团购名称\r\nsubjectimage =  团购图片\r\ncatid =  团购品牌\r\nmessage =  团购详情')");

		DB::query("REPLACE INTO ".tname('modelcolumns')." (`mid`, `fieldname`, `fieldtitle`, `fieldcomment`, `fieldtype`, `fieldminlength`, `fieldlength`, `fielddefault`, `formtype`, `fielddata`, `displayorder`, `allowshow`, `allowpost`, `isfixed`, `isrequired`, `isimage`, `thumbsize`) VALUES
		(2, 'address', '店铺地址', '店铺地址', 'CHAR', 0, 80, '', 'text', '', 1, 1, 1, 1, 1, 0, ''),
		(2, 'tel', '店铺电话', '店铺电话', 'CHAR', 0, 30, '', 'text', '', 2, 1, 1, 1, 1, 0, ''),
		(2, 'isdiscount', '是否支持会员卡', '是否支持会员卡', 'TINYINT', 0, 1, '0', 'radio', '0\n1', 8, 1, 0, 1, 0, 0, ''),
		(2, 'discount', '会员卡折扣信息', '会员卡折扣信息', 'CHAR', 0, 100, '', 'text', '', 9, 1, 1, 1, 0, 0, ''),
		(2, 'banner', '品牌Banner图，固定大小980 x 150', '品牌Banner图，固定大小980 x 150', 'VARCHAR', 0, 150, '', 'img', '', 3, 1, 1, 0, 0, 0, ''),
		(2, 'windowsimg', '橱窗海报图片', '橱窗海报图片', 'VARCHAR', 0, 150, '', 'img', '', 4, 1, 1, 0, 0, 1, ''),
		(2, 'windowstext', '橱窗展示文字', '橱窗展示文字', 'VARCHAR', 0, 200, '', 'text', '', 5, 1, 1, 0, 0, 0, ''),
		(2, 'mapapimark', '地图API商家地点参数', '地图API商家地点参数', 'VARCHAR', 0, 60, '', 'text', '', 16, 1, 1, 0, 0, 0, ''),
		(2, 'tips', '橱窗上方公告文字', '橱窗上方公告文字', 'VARCHAR', 0, 255, '', 'textarea', '', 6, 1, 1, 0, 0, 0, ''),
		(2, 'applicant', '申请人姓名', '申请人姓名', 'VARCHAR', 2, 12, '', 'text', '', 10, 0, 1, 0, 1, 0, ''),
		(2, 'applicantmobi', '申请人手机', '申请人手机', 'VARCHAR', 7, 11, '', 'text', '', 12, 0, 1, 0, 1, 0, ''),
		(2, 'applicanttel', '申请人座机', '申请人座机', 'VARCHAR', 7, 18, '', 'text', '', 13, 0, 1, 0, 1, 0, ''),
		(2, 'applicantid', '申请人身份证', '申请人身份证', 'VARCHAR', 15, 18, '', 'text', '', 11, 0, 1, 0, 1, 0, ''),
		(2, 'applicantadd', '申请人住址', '申请人住址', 'VARCHAR', 4, 80, '', 'text', '', 14, 0, 1, 0, 1, 0, ''),
		(2, 'applicantpost', '申请人邮编', '申请人邮编', 'VARCHAR', 6, 6, '', 'text', '', 15, 0, 1, 0, 1, 0, ''),
		(2, 'forum', '互动专区', '互动专区', 'VARCHAR', 0, 150, '', 'text', '', 7, 1, 1, 0, 0, 0, ''),
		(2, 'styletitle', '店铺标题样式', '店铺标题样式', 'CHAR', 0, 10, '', 'text', '', 1, 0, 1, 1, 0, 0, ''),
		(2, 'region', '地区分类id', '地区分类id', 'SMALLINT', 0, 6, '', 'text', '', 0, 1, 1, 1, 0, 0, ''),
		(2, 'groupid', '用户组id', '用户组id', 'SMALLINT', 0, 6, '', 'text', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enablegood', '发布商品权限', '发布商品权限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enablenotice', '发布公告权限', '发布公告权限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enableconsume', '发布消费券权限', '发布消费券权限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enablealbum', '发布相册权限', '发布相册权限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 'validity_start', '有效期开始时间', '有效期开始时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(2, 'validity_end', '有效期结束时间', '有效期结束时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(3, 'styletitle', '标题样式', '标题样式', 'CHAR', 0, 10, '', 'text', '', 0, 0, 1, 1, 0, 0, ''),
		(3, 'jumpurl', '公告链接地址', '公告链接地址', 'VARCHAR', 0, 150, '', 'text', '', 0, 1, 1, 0, 0, 0, ''),
		(3, 'validity_start', '有效期开始时间', '有效期开始时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(3, 'validity_end', '有效期结束时间', '有效期结束时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'priceo', '商品原价', '商品原价', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'minprice', '网店特价', '网店特价', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'maxprice', '网店最高价', '网店最高价', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 0, 0, ''),
		(4, 'validity_start', '有效期开始时间', '有效期开始时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'validity_end', '有效期结束时间', '有效期结束时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(5, 'validity_start', '有效期开始时间', '有效期开始时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(5, 'validity_end', '有效期结束时间', '有效期结束时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(2, 's_enablegroupbuy', '发布团购权限', '发布团购权限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(6, 'groupbuyprice', '原价', '原价', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(6, 'groupbuypriceo', '团购价', '团购价', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(6, 'groupbuymaxnum', '最大购买数', '最大购买数', 'INT', 0, 10, '0', 'text', '', 0, 1, 1, 1, 0, 0, ''),
		(6, 'validity_start', '有效期开始时间', '有效期开始时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(6, 'validity_end', '有效期结束时间', '有效期结束时间', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'intro', '商品介绍', '商品简短描述', 'VARCHAR', 0, 200, '', 'textarea', '', 1, 1, 1, 1, 0, 0, '');
		");
		if(!DB::result_first('SELECT navid FROM '.tname('nav')." WHERE flag='groupbuy'")) {
			DB::query('INSERT INTO '.tname('nav')." (`type`, `shopid`, `available`, `displayorder`, `flag`, `name`, `url`, `target`, `highlight`) VALUES('sys', 0, 1, 7, 'groupbuy', '团购', 'groupbuy.php', 0, '0');");
		}

		show_msg("[数据升级] 自定义字段 全部结束，进入下一步", 'update.php?step=data&op='.$nextop);

	} elseif($_GET['op'] == 'setting_grade') {
		update_modelgrade();
    } elseif($_GET['op'] == 'update_relatedinfo') {
	    update_relatedinfo();
    } elseif($_GET['op'] == 'update_groupfield') {
	    update_groupfield();
    } elseif($_GET['op'] == 'setting_attribute') {
		itemattr_chk();
		$nextop = 'setting_attribute_row';
		DB::query("UPDATE ".tname('attribute')." SET `cat_id`=`type_id`", 'SILENT');
		show_msg("[数据升级] 属性分类id 全部结束，进入下一步", 'update.php?step=data&op='.$nextop);
	} elseif($_GET['op'] == 'setting_attribute_row') {
		itemattr_chk();
		update_attribute_row();
	} elseif($_GET['op'] == 'setting_attrvalue') {
		itemattr_chk();
		update_attrvalue();
	} elseif($_GET['op'] == 'setting_itemattr') {
		itemattr_chk();
		update_itemattr();
	} else {
		//结束
		$next = 'update.php?step=delete';
		show_msg("数据库数据升级完毕，进入下一步数据库结构清理操作", $next);
	}

} elseif ($_GET['step'] == 'delete') {

	//检查需要删除的字段
	//老表集合
	$oldtables = array();
	$query = DB::query("SHOW TABLES LIKE '$_SC[tablepre]%'");
	while ($value = DB::fetch($query)) {
		$values = array_values($value);
		if(!strexists($values[0], 'cache')) {
			$oldtables[] = $values[0];//分表、缓存
		}
	}

	//新表集合
	$sql = sreadfile($sqlfile);
	preg_match_all("/CREATE\s+TABLE\s+`?brand\_(.+?)`?\s+\((.+?)\)\s+(TYPE|ENGINE)\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];

	//需要删除的表
	$deltables = array();
	$delcolumns = array();

	//老的有，新的没有
	foreach ($oldtables as $tname) {
		$tname = substr($tname, strlen($_SC['tablepre']));
		if(in_array($tname, $newtables)) {
			//比较字段是否多余
			$query = DB::query("SHOW CREATE TABLE ".tname($tname));
			$cvalue = DB::fetch($query);
			$oldcolumns = getcolumn($cvalue['Create Table']);

			//新的
			$i = array_search($tname, $newtables);
			$newcolumns = getcolumn($newsqls[$i]);
			//老的有，新的没有的字段
			foreach ($oldcolumns as $colname => $colstruct) {
				if(!strexists($colname, 'field_')) {
					if($colname == 'PRIMARY') {
						//关键字
						if(empty($newcolumns[$colname])) {
							$delcolumns[$tname][] = 'PRIMARY';
						}
					} elseif($colname == 'UNIQUE') {
						//唯一索引
						foreach ($colstruct as $key_index => $key_value) {
							if(empty($newcolumns[$colname][$key_index])) {
								$delcolumns[$tname]['UNIQUE'][$key_index] = $key_value;
							}
						}
					} elseif($colname == 'KEY') {
						//索引
						foreach ($colstruct as $key_index => $key_value) {
							if(empty($newcolumns[$colname][$key_index])) {
								$delcolumns[$tname]['KEY'][$key_index] = $key_value;
							}
						}
					} else {
						//普通字段
						if(empty($newcolumns[$colname])) {
							if(in_array($tname, array('spacecomments')) && preg_match("/^click_/i", $colname)) {
								continue;
							}
							$delcolumns[$tname][] = $colname;
						}
					}
				}
			}
		} else {
			if(!preg_match("/(items|message|rates|comments|categories|folders)$/i", $tname)) $deltables[] = $tname;
		}
	}
	//显示
	show_header();
	echo '<form method="post" action="update.php?step=delete">';
	echo '<input type="hidden" name="formhash" value="'.formhash().'">';

	//删除表
	$deltablehtml = '';
	if($deltables) {
		$deltablehtml .= '<table>';
		foreach ($deltables as $tablename) {
			$deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td></tr>";
		}
		$deltablehtml .= '</table>';
		echo "<p>以下 数据表 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
	}
	//删除字段
	$delcolumnhtml = '';
	if($delcolumns) {
		$delcolumnhtml .= '<table>';
		foreach ($delcolumns as $tablename => $cols) {
			foreach ($cols as $colkey=>$col) {
				if ($colkey=='KEY' && is_array($col)) {
					foreach ($col as $index => $indexvalue) {
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][KEY][$index]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td><td>索引 $index $indexvalue</td></tr>";
					}
				} elseif($colkey=='UNIQUE' && is_array($col)) {
					foreach ($col as $index => $indexvalue) {
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][UNIQUE][$index]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td><td>唯一索引 $index $indexvalue</td></tr>";
					}
				} elseif($col == 'PRIMARY') {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][PRIMARY]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td><td>主键 PRIMARY</td></tr>";
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 字段 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"?step=cache\">请点击进入下一步</a></p>";
	} else {
		echo "<p><span style=\"color:#F00;\">删除请慎重！ 所有UCenter相关表和Discuz!相关的表请勿删除，shopmessage表ext_开头的字段为商家信息自定义字段， groupbuy表user_开头的字段为团购报名信息自定义字段</span>品牌空间数据表内的索引调整可以删除，其他情况请仔细辨别，如有疑问请到论坛求助</p><p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"?step=cache\">直接进入下一步</a></p>";
	}
	echo '<input type="hidden" name="formhash" value="'.formhash().'"></form>';

	show_footer();
	exit();

} elseif ($_GET['step'] == 'cache') {

	//更新缓存
	require_once(B_ROOT.'./source/function/cache.func.php');
	updatesettingcache();	//系统设置缓存
	updatecronscache();		//crons列表
	updatecroncache();		//计划任务
	updatecategorycache();	//分类
	updatecensorcache();	//缓存语言屏蔽
	model_cache();

	//写log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, '品牌空间');
		fclose($fp);
	}

	show_msg('升级完成，为了您的数据安全，避免重复升级，请登录FTP删除本文件!');
}


//正则匹配,获取字段/索引/关键字信息
function getcolumn($creatsql) {

	preg_match("/\((.+)\)/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);//特使字符替换
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);//去掉末尾逗号

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if(strtoupper($cname) == 'KEY') {
			$subvalue = trim(substr($value, 3));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols['KEY'][$subcname] = trim(substr($value, (5+strlen($subcname))));
		} elseif(strtoupper($cname) == 'UNIQUE') {
			$subvalue = trim(substr($value, 10));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols['UNIQUE'][$subcname] = trim(substr($value, (12+strlen($subcname))));
		} elseif(strtoupper($cname) == 'INDEX') {
			$subvalue = trim(substr($value, 5));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols['KEY'][$subcname] = trim(substr($value, (7+strlen($subcname))));
		} elseif(strtoupper($cname) == 'PRIMARY') {
			$newcols['PRIMARY'] = trim(substr($value, 11));
		} else {
			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

//整理sql文
function remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));//空格标准化
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )'), array('', ',', ',','(',')'), $value);//去掉无用符号
	$value = preg_replace('/(text NOT NULL) default \'\'/i',"\\1", $value);//去掉无用符号
	return $value;
}

//显示
function show_msg($message, $url_forward='') {
	global $_G, $_SGLOBAL;

	obclean();

	if($url_forward) {
		$_SGLOBAL['extrahead'] = '<meta http-equiv="refresh" content="1; url='.$url_forward.'">';
		$message = "<a href=\"$url_forward\">$message(跳转中...)</a>";
	} else {
		$_SGLOBAL['extrahead'] = '';
	}

	show_header();
	print<<<END
<table>
<tr><td>$message</td></tr>
</table>
END;
	show_footer();
	exit();
}


//页面头部
function show_header() {
	global $_G, $_SGLOBAL, $_SC;

	$nowarr = array($_GET['step'] => ' class="current"');

	if(empty($_SGLOBAL['extrahead'])) $_SGLOBAL['extrahead'] = '';

	print<<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$_G[charset]" />
$_SGLOBAL[extrahead]
<title> 品牌空间 数据库升级程序 </title>
<style type="text/css">
* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
#menu {width: 100%; margin: 10px auto; text-align: center; }
#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
.showtable td { padding: 3px; }
.showtable strong { color: #5086A5; }
.datatable { width: 100%; margin: 10px auto 25px; }
.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
.button { margin: 10px auto 20px; width: 100%; }
.button td { text-align: center; }
.button input, .button button { border: solid; border-color:#F90; border-width: 1px 1px 3px; padding: 5px 10px; color: #090; background: #FFFAF0; cursor: pointer; }
#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
</style>
</head>
<body>
<div class="bodydiv">
<h1>品牌空间 数据库升级工具</h1>
<div style="width:90%;margin:0 auto;">
<table id="menu">
<tr>
<td{$nowarr[start]}>升级开始</td>
<td{$nowarr[check]}>UC检测</td>
<td{$nowarr[sql]}>数据库结构添加/升级</td>
<td{$nowarr[data]}>数据库数据升级</td>
<td{$nowarr[delete]}>数据库结构删除</td>
<td{$nowarr[cache]}>升级完成</td>
</tr>
</table>
<br>
END;
}

//页面顶部
function show_footer() {
	print<<<END
</div>
<div id="footer">&copy; Comsenz Inc. 2009-2010 http://www.comsenz.com</div>
</div>
<br>
</body>
</html>
END;
}

function get_uc_root() {
	$uc_root = '';
	$uc = parse_url(UC_API);
	if($uc['host'] == $_SERVER['HTTP_HOST']) {
		$php_self_len = strlen($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
		$uc_root = substr(__FILE__, 0, -$php_self_len).$uc['path'];
	}
	return $uc_root;
}

function model_cache() {
	global $_G, $_SGLOBAL;

	$tpl = dir(B_ROOT.'./data/cache/model');
	$tpl->handle;
	while($entry = $tpl->read()) {
		if(preg_match("/.*\.cache\.php$/", $entry)) {
			@unlink(B_ROOT.'./data/cache/model/'.$entry);
		}
	}
	$tpl->close();
}

function update_modelgrade() {
	global $_G, $_SGLOBAL;
	$nextstep_end = 'update_relatedinfo';
	$models = array('album', 'consume', 'good', 'groupbuy', 'notice', 'photo');
	$m_step = empty($_GET['m_step'])?0:intval($_GET['m_step']);
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$mname = $models[$m_step];
	if(empty($mname)) {
		show_msg("[数据升级] 模型状态冗余更新 全部结束，进入下一步", 'update.php?step=data&op='.$nextstep_end);
	}
	$newi = $i+1;
	$page = 5000;
	$start = $i*$page;
	$end = $newi*$page;
	$maxid = DB::result_first("SELECT MAX(itemid) FROM ".tname($mname.'items'));
	if($start>$maxid) {
		$m_step = $m_step+1;
		$newi = 0;
		show_msg("[数据升级] 状态冗余 {$mname} 完成，进入下一步", 'update.php?step=data&op=setting_grade&m_step='.$m_step);
	} else {
		DB::query("UPDATE ".tname($mname.'items')." i INNER JOIN ".tname('shopitems')." si ON i.shopid=si.itemid SET i.grade_s = si.grade WHERE i.itemid>$start AND i.itemid<=$end");
		show_msg("[数据升级] 状态冗余 {$mname}: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_grade&m_step='.$m_step.'&i='.$newi);
	}
}

function update_attribute_row() {
	global $_G, $_SGLOBAL;
	$nextstep_end = 'setting_attrvalue';
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$newi = $i+1;
	$page = 100;
	$start = $i*$page;
	$end = $newi*$page;
	$maxid = DB::result_first("SELECT MAX(cat_id) FROM ".tname('attribute'));
	if($start>$maxid) {
		show_msg("[数据升级] 属性row表升级 完成，进入下一步", 'update.php?step=data&op='.$nextstep_end);
	} else {
		for($j=$start;$j<$end;$j++) {
			$query = DB::query("SELECT * FROM ".tname('attribute')." WHERE cat_id='$j' ORDER BY attr_id ASC");
			$k = 0;
			while($attributes = DB::fetch($query)) {
				$attr_id = intval($attributes['attr_id']);
				DB::query('UPDATE '.tname('attribute')." SET attr_row='$k' WHERE attr_id='$attr_id'");
				$k++;
			}
		}
		show_msg("[数据升级] 属性row表升级: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_attribute_row&i='.$newi);
	}
}

function update_attrvalue() {
	global $_G, $_SGLOBAL;
	$nextstep_end = 'setting_itemattr';
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$newi = $i+1;
	$page = 100;
	$start = $i*$page;
	$end = $newi*$page;
	$maxid = DB::result_first("SELECT MAX(attr_id) FROM ".tname('attribute'));
	if($start>=$maxid) {
		show_msg("[数据升级] 属性可选值表升级 完成，进入下一步", 'update.php?step=data&op='.$nextstep_end);
	} else {
		$query = DB::query("SELECT * FROM ".tname('attribute')." WHERE attr_id>'$start' AND attr_id<='$end' AND attr_type='0' ORDER BY attr_id ASC");
		while($attributes = DB::fetch($query)) {
			$attr_id = intval($attributes['attr_id']);
			$attr_value_arr = explode("\r\n", $attributes['attr_values']);
			foreach($attr_value_arr as $attr_text) {
				$attr_text = trim(strip_tags($attr_text));
				if(!empty($attr_text)) {
					$attrvalue_arr = array(
						'attr_id' => $attr_id,
						'attr_text' => $attr_text
					);
					inserttable('attrvalue', $attrvalue_arr);
				}
			}
		}
		show_msg("[数据升级] 属性可选值表升级: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_attrvalue&i='.$newi);
	}
}

function update_itemattr() {
	global $_G, $_SGLOBAL;
	$nextstep_end = 'settingend';
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$newi = $i+1;
	$page = 500;
	$start = $i*$page;
	$end = $newi*$page;
	$maxid = DB::result_first("SELECT MAX(itemid) FROM ".tname('itemattr'));
	if($start>$maxid) {
		show_msg("[数据升级] 内容属性关系表升级 完成，进入下一步", 'update.php?step=data&op='.$nextstep_end);
	} else {
		for($j=$start;$j<$end;$j++) {
			$query = DB::query("SELECT * FROM ".tname('itemattr')." WHERE itemid='$j'");
			$item_attribute_arr = array();
			while($items = DB::fetch($query)) {
				$attr_id = intval($items['attr_id']);
				$item_attribute_arr['itemid'] = $itemid = intval($items['itemid']);
				$attr_text =  trim(strip_tags($items['attr_value']));
				if($attr_id && $attr_text) {
					$query_item = DB::query('SELECT * FROM '.tname('attribute')." WHERE attr_id='$attr_id'");
					$row = DB::fetch($query_item);
					$item_attribute_arr['catid'] = $catid = $row['cat_id'];
					$attr_type = $row['attr_type'];
					$attr_rowid = $row['attr_row'];
					if($attr_type == 0) {
						$attr_valueid = DB::result_first('SELECT attr_valueid FROM '.tname('attrvalue')." WHERE attr_id='$attr_id' AND attr_text='$attr_text'");
					} else {
						$attr_valueid = 0;
						inserttable('attrvalue_text', array('attr_id'=>$attr_id, 'itemid'=>$itemid, 'attr_text'=>$attr_text));
					}
					$item_attribute_arr['attr_id_'.$attr_rowid] = $attr_valueid;
				}
			}
			if($item_attribute_arr) {
				inserttable('itemattribute', $item_attribute_arr, 0, true);
			}
		}
		show_msg("[数据升级] 内容属性关系表升级: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_itemattr&i='.$newi);
	}
}

function itemattr_chk() {
	global $_G, $_SGLOBAL, $_SC;
	$tables = array();
	$skip = 0;
	$nextop = 'settingend';
	$query = DB::query('SHOW TABLES');
	while($row = DB::fetch_row($query)) {
		$tables[] = $row[0];
	}
	if(in_array($_SC['tablepre'].'itemattribute', $tables)) {
		if(!in_array($_SC['tablepre'].'itemattr', $tables)) {
			$skip = 1;
		} else {
			$newitemid = DB::result_first('SELECT MAX(itemid) FROM '.tname('itemattribute'));
			$olditemid = @DB::result_first('SELECT MAX(itemid) FROM '.tname('itemattr'));
			if($newitemid == $olditemid) {
				$skip = 1;
			}
		}
	}
	if($skip)
	    show_msg("[数据升级] 跳过属性升级，进入下一步", 'update.php?step=data&op='.$nextop);
}

function update_relatedinfo() {
	global $_G, $_SGLOBAL, $_SC;

	$tables = array();
	$skip = 0;
	$nextop = 'update_groupfield';
	$query = DB::query('SHOW TABLES');
	while($row = DB::fetch_row($query)) {
		$tables[] = $row[0];
	}
	if(!in_array($_SC['tablepre'].'goodrelated', $tables)) {
		$skip = 1;
	} else {
		$relatedupdate = DB::result_first('SELECT value FROM '.tname('data').' WHERE variable =\'relatedupdate\'');
		if($relatedupdate) {
			$skip = 1;
		}
	}
	if($skip) {
		show_msg("[数据升级] 跳过商品关联数据升级，进入下一步", 'update.php?step=data&op='.$nextop);
	}

	$query = DB::query("SELECT * FROM ".tname('goodrelated'));
	while($item = DB::fetch($query)) {
		inserttable('relatedinfo', array('itemid'=>$item['goodid'], 'type'=>'good', 'relatedid'=>$item['relatedid'], 'relatedtype'=>$item['type'], 'shopid'=>$item['shopid']), 0, true);
	}
	inserttable('data', array('variable'=>'relatedupdate', 'value'=>1), 0, true);
	show_msg("[数据升级] 商品关联数据升级 完成，进入下一步", 'update.php?step=data&op='.$nextop);
}

function update_groupfield() {
    global $_G, $_SGLOBAL;
	$nextstep_end = 'setting_attribute';
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$maxid = DB::result_first("SELECT count(id) FROM ".tname('shopgroup'));
	if($i>=$maxid)
		show_msg("[数据升级] 店铺组关联分类数据升级 完成，进入下一步", 'update.php?step=data&op='.$nextstep_end);
	$catid = DB::result_first("SELECT id FROM ".tname('shopgroup')." LIMIT $i,1");

	updategroupfield($catid);
    $newi = $i+1;
	show_msg("[数据升级] 店铺组关联分类数据升级: {$newi} / {$maxid}，下一步", 'update.php?step=data&op=update_groupfield&i='.$newi);


}
function updategroupfield($groupid) {
    global $_G, $_SGLOBAL,$categorylist;
    $query = DB::query("SELECT album_field, good_field, notice_field, consume_field, groupbuy_field FROM ".tname("shopgroup")." WHERE id = '$groupid'");
    $result = DB::fetch($query);
    $types = array('album', 'good', 'consume', 'groupbuy', 'notice');
    foreach($types as $type) {
        $allarr = array();
        $categorylist = getmodelcategory($type);
        if(!empty($result[$type.'_field']) && $result[$type.'_field'] != 'all') {
            $allarr = $groupfields[$type] = explode(",", $result[$type.'_field']);
            foreach($groupfields[$type] as $catid) {
                $arr = categorygetparents($catid);
                $allarr = array_merge($allarr,$arr);
            }
            $allarr = array_unique($allarr);
            DB::query("UPDATE ".tname("shopgroup")." SET `".$type."_field` = '".implode(",", $allarr)."' WHERE id = '$groupid'");
        }
    }
    return $groupfields;
}

function categorygetparents($catid) {
    global $_G, $categorylist;
    $arr = array();
    if($categorylist[$catid]['upid']>0) {
        $arr = categorygetparents($categorylist[$catid]['upid']);
        array_push($arr, $categorylist[$catid]['upid']);
    }
    return $arr;
}
?>