<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 10997 2010-05-19 06:55:03Z liguode $
 */

include_once('../source/class/class_core.php');
include_once('../source/function/function_core.php');
include_once('../source/discuz_version.php');
substr(DISCUZ_VERSION, 0, 4) != 'X1.5' && show_msg('此房产模块只适用于 Discuz!X1.5 系列，您的 Discuz! 版本是 '.DISCUZ_VERSION.'，请下载适用于 Discuz!'.DISCUZ_VERSION.' 的房产模块。');

$cachelist = array();
$discuz = & discuz_core::instance();

define('DBCHARSET', 'gbk');
define('ORIG_TABLEPRE', 'pre_');

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = false;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();
//配置
$config = array(
	'dbcharset' => $_G['config']['db']['1']['dbcharset'],
	'charset' => $_G['config']['output']['charset'],
	'tablepre' => $_G['config']['db']['1']['tablepre']
);
$theurl = 'index.php';

$lockfile = DISCUZ_ROOT.'./data/houseinstall.lock';
if(file_exists($lockfile)) {
	show_msg('请您先登录服务器ftp，手工删除 ./data/houseinstall.lock 文件，再次运行本文件进行升级。');
}

//新SQL, 优先使用开发过程中的 install_dev.sql
$devmode = file_exists(DISCUZ_ROOT.'./houseinstall/data/install_dev.sql');
$sqlfile = DISCUZ_ROOT.($devmode ? './houseinstall/data/install_dev.sql' : './houseinstall/data/install.sql');

if(!file_exists($sqlfile)) {
	show_msg('SQL文件 '.$sqlfile.' 不存在');
}

//提交处理
if($_POST['delsubmit']) {
	//删除表
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			DB::query("DROP TABLE `".DB::table($tname)."`", 'SILENT');
		}
	}
	//删除字段
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP PRIMARY KEY", 'SILENT');//屏蔽错误
				} elseif($col == 'KEY' || $col == 'UNIQUE') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".DB::table($tname)." DROP INDEX `$index`", 'SILENT');//屏蔽错误
					}
				} else {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP `$col`", 'SILENT');
				}
			}
		}
	}

	show_msg('删除表和字段操作完成了', $theurl.'?step=delete');
}

if(empty($_GET['step'])) $_GET['step'] = 'start';

//处理开始
if($_GET['step'] == 'start') {
	//开始
	show_msg('说明：<br>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
		请确保当前目录下 ./data/install.sql 文件为最新版本。<br><br>
		<a href="'.$theurl.'?step=sql">准备完毕，升级开始</a>');

} elseif ($_GET['step'] == 'sql') {

	//新的SQL
	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('SQL文件内容为空，请确认');
	}

	//升级表
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$count_i = count($newtables);
	if($i>=$count_i) {
		//处理完毕
		show_msg('数据库结构升级完毕，进入下一步数据升级操作', $theurl.'?step=data', 1);
	}
	//当前处理表
	$newtable = $newtables[$i];
	$newcols = getcolumn($newsqls[$i]);

	//获取当前SQL
	if(!$query = DB::query("SHOW CREATE TABLE ".DB::table($newtable), 'SILENT')) {
		//添加表
		preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*\=/is", $newsqls[$i], $maths);

		if(strpos($newtable, 'common_session')) {
			$type = mysql_get_server_info() > '4.1' ? " ENGINE=MEMORY".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=HEAP";
		} else {
			$type = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=MYISAM";
		}
		$usql = $maths[1].$type;

		$usql = str_replace("CREATE TABLE IF NOT EXISTS pre_", 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
		if(!DB::query($usql, 'SILENT')) {
			show_msg('添加表 '.DB::table($newtable).' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>'.dhtmlspecialchars($usql));
		} else {
			$msg = '添加表 '.DB::table($newtable).' 完成';
		}
	} else {
		$value = DB::fetch($query);
		$oldcols = getcolumn($value['Create Table']);

		//获取升级SQL文
		$updates = array();
		foreach ($newcols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) {
						$usql = "RENAME TABLE ".DB::table($newtable)." TO ".DB::table($newtable.'_bak');
						if(!DB::query($usql, 'SILENT')) {
							show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
						} else {
							$msg = '表改名 '.DB::table($newtable).' 完成！';
							show_msg($msg, $theurl.'?step=sql&i='.$_GET['i']);
						}
					}
					$updates[] = "ADD PRIMARY KEY $value";
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
			} elseif ($key == 'UNIQUE') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['UNIQUE'][$subkey])) {
						if($subvalue != $oldcols['UNIQUE'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					} else {
						$usql = "ALTER TABLE  ".DB::table($newtable)." DROP INDEX `$subkey`";
						DB::query($usql, 'SILENT');
						$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(strtolower($value) != strtolower($oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$updates[] = "ADD `$key` $value";
				}
			}
		}

		//升级处理
		if(!empty($updates)) {
			$usql = "ALTER TABLE ".DB::table($newtable)." ".implode(', ', $updates);
			if(!DB::query($usql, 'SILENT')) {
				show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
			} else {
				$msg = '升级表 '.DB::table($newtable).' 完成！';
			}
		} else {
			$msg = '检查表 '.DB::table($newtable).' 完成，不需升级，跳过';
		}
	}

	//处理下一个
	$next = $theurl.'?step=sql&i='.($_GET['i']+1);
	show_msg("[ $i / $count_i ] ".$msg, $next);

} elseif ($_GET['step'] == 'data') {// 升级数据

	$datasql = file_get_contents(DISCUZ_ROOT.'./houseinstall/data/install_data.sql');
	$datasql = str_replace("\r\n", "\n", $datasql);
	runquery($datasql);

	show_msg("数据处理完成", "$theurl?step=cache");

} elseif ($_GET['step'] == 'cache') {
	
	require_once libfile('function/category');

	$cachearray = array('categorysort', 'sortlist', 'channellist', 'arealist', 'usergroup');
	foreach($cachearray as $cachename) {
		categorycache($cachename, 'house');
	}

	//写log	
	if(!$devmode && @$fp = fopen($lockfile, 'w')) {
		fwrite($fp, ' ');
		fclose($fp);
	}

	//缓存更新
	show_msg('恭喜，数据库结构升级完成！为了数据安全，请删除 houseinstall 目录。');
}


//正则匹配,获取字段/索引/关键字信息
function getcolumn($creatsql) {

	$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
	preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);//特使字符替换
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);//去掉末尾逗号

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

			$name_length = strlen($cname);
			if($cname == 'UNIQUE') $name_length = $name_length + 4;

			$subvalue = trim(substr($value, $name_length));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

		}  elseif($cname == 'PRIMARY') {

			$newcols[$cname] = trim(substr($value, 11));

		}  else {

			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

//整理sql文
function remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));//空格标准化
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )'), array('', ',', ',','(',')'), $value);//去掉无用符号
	return $value;
}

//显示
function show_msg($message, $url_forward='') {

	if($url_forward) {
		$message = "<a href=\"$url_forward\">$message (跳转中...)</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1);</script>";
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

function runquery($sql) {
	global $lang, $config, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' '.ORIG_TABLEPRE, ' '.$config['tablepre'], $sql));
	$sql = str_replace("\r", "\n", str_replace(' `'.ORIG_TABLEPRE, ' `'.$config['tablepre'], $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(createtable($query), 'SILENT');
			} else {
				DB::query($query, 'SILENT');
			}

		}
	}

}

function createtable($sql) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=".DBCHARSET : " TYPE=$type");
}

//页面头部
function show_header() {
	global $config;

	$nowarr = array($_GET['step'] => ' class="current"');

	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> Discuz!X房产安装程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>Discuz!X房产安装程序</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>安装开始</td>
	<td{$nowarr[sql]}>数据库结构添加与更新</td>
	<td{$nowarr[data]}>数据更新</td>
	<td{$nowarr[cache]}>安装完成</td>
	</tr>
	</table>
	<br>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<div id="footer">&copy; Comsenz Inc. 2001-2010 http://www.comsenz.com</div>
	</div>
	<br>
	</body>
	</html>
END;
}


?>
