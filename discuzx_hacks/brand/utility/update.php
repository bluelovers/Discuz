<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 4491 2010-09-15 09:54:10Z xuhui $
 */

@define('IN_BRAND_UPDATE', true);

if(!@include('./common.php')) {
	exit('請將本文件移到程序根目錄再運行!');
}

error_reporting(0);
@set_time_limit(300);

//不讓計劃任務執行
$_SGLOBAL['cronnextrun'] = $_G['timestamp']+3600;

//新SQL
$sqlfile = B_ROOT.'./data/install.sql';
if(!file_exists($sqlfile)) {
	show_msg('最新的SQL不存在,請先將最新的數據庫結構文件 install.sql 已經上傳到 ./data 目錄下面後，再運行本升級程序');
}

$lockfile = './data/update.lock';
if(file_exists($lockfile)) {
	show_msg('請您先登錄服務器ftp，手工刪除 data/update.lock 文件，再次運行本文件進行品牌空間升級。');
}

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

//提交處理
if(submitcheck('delsubmit')) {
	//刪除表
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			DB::query("DROP TABLE ".tname($tname));
		}
	}
	//刪除字段
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					DB::query("ALTER TABLE ".tname($tname)." DROP PRIMARY KEY", 'SILENT');//屏蔽錯誤
				} elseif($col == 'UNIQUE') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".tname($tname)." DROP INDEX `$index`", 'SILENT');//屏蔽錯誤
					}
				} elseif($col == 'KEY') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".tname($tname)." DROP INDEX `$index`", 'SILENT');//屏蔽錯誤
					}
				} else {
					DB::query("ALTER TABLE ".tname($tname)." DROP `$col`");
				}
			}
		}
	}

	show_msg('刪除表和字段操作完成了', 'update.php?step=delete');
}

if(empty($_GET['step'])) $_GET['step'] = 'start';

//處理開始
if($_GET['step'] == 'start') {

	show_msg('
	<div id="ready">
	本升級程序會參照最新的SQL文,對您的品牌空間數據庫進行升級。<br><br>
	升級前請做好以下前期工作：<br><br>
	<!--<b>第一步：</b><br>
	關閉站點，避免升級時有用戶寫入數據導致數據出錯。<br><br>-->
	<b>第一步：</b><br>
	備份當前的數據庫，避免升級失敗，造成數據丟失而無法恢復；<br><br>
	<b>第二步：</b><br>
	將程序包 ./upload/ 目錄中，除 config.new.php 文件、./install/ 目錄以外的其他所有文件，全部上傳並覆蓋當前程序。<b>特別注意的是，最新數據庫結構 ./data/install.sql 文件不要忘記上傳，否則會導致升級失敗</b>；<br><br>
	<b>第三步：</b><br>
	確認已經將程序包中最新的 update.php 升級程序上傳到服務器程序根目錄中<br>
	<br><br>
	<a href="update.php?step=check">已經做好了以上工作，升級開始</a><br><br>
	特別提醒：為了數據安全，升級完畢後，不要忘記刪除本升級文件。
	</div>
	');

} elseif ($_GET['step'] == 'check') {

	//UCenter_Client
	include_once B_ROOT.'./uc_client/client.php';
	if(!function_exists('uc_check_version')) {
		show_msg('請將品牌空間程序包中最新版本的 ./upload/uc_client 上傳至程序根目錄覆蓋原有目錄和文件後，再嘗試升級。');
	}

	$uc_root = get_uc_root();
	$return = uc_check_version();
	if (empty($return)) {
		$upgrade_url = 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=sql';
	} else {
		if(strcmp($return['db'], '1.5.0') >= 0) {
			header("Location: update.php?step=sql");//UC升級完成
			exit();
		}
		$upgrade_url = 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=check';
	}

	$ucupdate = UC_API."/upgrade/upgrade2.php?action=db&forward=".urlencode($upgrade_url);

	show_msg('<b>您的 UCenter 程序還沒有升級完成，請如下操作：</b><br>品牌空間支持了最新版本的UCenter，請先升級您的UCenter。<br><br>
		1. <a href="http://download.comsenz.com/UCenter/1.5.0/" target="_blank">點擊這裡下載對應編碼的 UCenter 1.5.0 程序</a><br>
		2. 將解壓縮得到的 ./upload 目錄下的程序覆蓋到已安裝的UCenter目錄 <b>'.($uc_root ? $uc_root : UC_API).'</b><br>
		&nbsp;&nbsp;&nbsp; (確保其升級程序 <b>./upgrade/upgrade2.php</b> 也已經上傳到UCenter的 ./upgrade 目錄)<br><br>
		確認完成以上UCenter程序升級操作完成後，您才可以：<br>
		<a href="'.$ucupdate.'" target="_blank">新窗口中訪問 upgrade2.php 進行UCenter數據庫升級</a><br>
		在打開的新窗口中，如果UCenter升級成功，程序會自動進行下一步的升級。<br>這時，您關閉本窗口即可。
		<br><br>
		如果您無法通過上述UCenter升級步驟，請調查問題後，務必將UCenter正常升級後，再繼續本升級程序。<br>或者您可以：<br><a href="update.php?step=sql" style="color:#CCC;">跳過UCenter升級</a>，但這可能會帶來一些未知兼容問題。');

} elseif ($_GET['step'] == 'sql') {

	$cachefile = B_ROOT.'./data/system/update_model.cache.php';
	@unlink($cachefile);

	//config.php檢測
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
		show_msg('當前服務器上「config.php」文件不是最新，需要您對其進行更新。<br/><br/>
				文件中缺少的參數:<br/>'.implode('<br/>', $scarcity).'<br/><br/>
				請參考程序包 ./upload 目錄中，config.new.php文件，將現行config.php文件更新後，再運行本升級程序');
	}

	//新的SQL
	$sql = sreadfile($sqlfile);
	preg_match_all("/CREATE\s+TABLE\s+`?brand\_(.+?)`?\s+\((.+?)\)\s+(TYPE|ENGINE)\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('最新的SQL不存在,請先將最新的數據庫結構文件 install.sql 已經上傳到 ./data 目錄下面後，再運行本升級程序');
	}

	//升級表
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	if($i>=count($newtables)) {
		//處理完畢
		show_msg('數據庫結構升級完畢，進入下一步操作', 'update.php?step=data');
	}

	//當前處理表
	$newtable = $newtables[$i];
	$newcols = getcolumn($newsqls[$i]);

	//獲取當前SQL
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
			show_msg('['.$i.'/'.count($newtables).']添加表 '.tname($newtable).' 出錯,請手工執行以下SQL語句後,再重新運行本升級程序:<br><br>'.shtmlspecialchars($usql));
		} else {
			$msg = '['.$i.'/'.count($newtables).']添加表 '.tname($newtable).' 完成';
		}
	} else {
		$value = DB::fetch($query);
		$oldcols = getcolumn($value['Create Table']);

		//獲取升級SQL文
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

		//升級處理
		if(!empty($updates)) {
			$usql = "ALTER TABLE ".tname($newtable)." ".implode(', ', $updates);
			if(!DB::query($usql, 'SILENT')) {
				show_msg('['.$i.'/'.count($newtables).']升級表 '.tname($newtable).' 出錯,請手工執行以下升級語句後,再重新運行本升級程序:<br><br><b>升級SQL語句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.shtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
			} else {
				$msg = '['.$i.'/'.count($newtables).']升級表 '.tname($newtable).' 完成';
			}
		} else {
			$msg = '['.$i.'/'.count($newtables).']檢查表 '.tname($newtable).' 完成，不需升級';
		}
	}

	//處理下一個
	$next = '?step=sql&i='.($_GET['i']+1);
	show_msg($msg, $next);

} elseif ($_GET['step'] == 'data') {

	if(empty($_GET['op'])) $_GET['op'] = 'setting';

	if($_GET['op'] == 'setting') {

		$nextop = 'setting2';

		DB::query("TRUNCATE TABLE ".tname('crons'));
		DB::query("REPLACE INTO ".tname('crons')." (`cronid`, `available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES
		(1, 1, 'system', '更新店舖狀態', 'updateshopgrade.php', 0, 0, -1, -1, 0, '0'),
		(2, 0, 'system', '更新商品狀態', 'updategoodgrade.php', 0, 0, -1, -1, 0, '15'),
		(3, 0, 'system', '更新消費卷狀態', 'updateconsumegrade.php', 0, 0, -1, -1, 1, '15'),
		(4, 0, 'system', '更新公告狀態', 'updatenoticegrade.php', 0, 0, -1, -1, 2, '15'),
		(5, 0, 'system', '更新團購狀態', 'updategroupbuygrade.php', 0, 0, -1, -1, 3, '15');
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
		(1, '', '信息有誤'),
		(2, '', '不切實際');
		");

		DB::query("UPDATE ".tname('modelcolumns')." SET isimage='1' WHERE formtype='img';");

		show_msg("[數據升級] 計劃任務 全部結束，進入下一步", 'update.php?step=data&op='.$nextop);

	} elseif($_GET['op'] == 'setting2') {

		$nextop = 'setting_grade';
		DB::query("REPLACE INTO ".tname('models')." (`mid`, `modelname`, `modelalias`, `allowpost`, `allowguest`, `allowgrade`, `allowcomment`, `allowrate`, `allowguestsearch`, `allowfeed`, `searchinterval`, `allowguestdownload`, `downloadinterval`, `allowfilter`, `listperpage`, `seokeywords`, `seodescription`, `thumbsize`, `tpl`, `fielddefault`) VALUES (6, 'groupbuy', '團購', 0, 0, 0, 0, 0, 0, 1, 0, 0, 30, 1, 10, '', '', '400,300', 'default', 'subject = 團購名稱\r\nsubjectimage =  團購圖片\r\ncatid =  團購品牌\r\nmessage =  團購詳情')");

		DB::query("REPLACE INTO ".tname('modelcolumns')." (`mid`, `fieldname`, `fieldtitle`, `fieldcomment`, `fieldtype`, `fieldminlength`, `fieldlength`, `fielddefault`, `formtype`, `fielddata`, `displayorder`, `allowshow`, `allowpost`, `isfixed`, `isrequired`, `isimage`, `thumbsize`) VALUES
		(2, 'address', '店舖地址', '店舖地址', 'CHAR', 0, 80, '', 'text', '', 1, 1, 1, 1, 1, 0, ''),
		(2, 'tel', '店舖電話', '店舖電話', 'CHAR', 0, 30, '', 'text', '', 2, 1, 1, 1, 1, 0, ''),
		(2, 'isdiscount', '是否支持會員卡', '是否支持會員卡', 'TINYINT', 0, 1, '0', 'radio', '0\n1', 8, 1, 0, 1, 0, 0, ''),
		(2, 'discount', '會員卡折扣信息', '會員卡折扣信息', 'CHAR', 0, 100, '', 'text', '', 9, 1, 1, 1, 0, 0, ''),
		(2, 'banner', '品牌Banner圖，固定大小980 x 150', '品牌Banner圖，固定大小980 x 150', 'VARCHAR', 0, 150, '', 'img', '', 3, 1, 1, 0, 0, 0, ''),
		(2, 'windowsimg', '櫥窗海報圖片', '櫥窗海報圖片', 'VARCHAR', 0, 150, '', 'img', '', 4, 1, 1, 0, 0, 1, ''),
		(2, 'windowstext', '櫥窗展示文字', '櫥窗展示文字', 'VARCHAR', 0, 200, '', 'text', '', 5, 1, 1, 0, 0, 0, ''),
		(2, 'mapapimark', '地圖API商家地點參數', '地圖API商家地點參數', 'VARCHAR', 0, 60, '', 'text', '', 16, 1, 1, 0, 0, 0, ''),
		(2, 'tips', '櫥窗上方公告文字', '櫥窗上方公告文字', 'VARCHAR', 0, 255, '', 'textarea', '', 6, 1, 1, 0, 0, 0, ''),
		(2, 'applicant', '申請人姓名', '申請人姓名', 'VARCHAR', 2, 12, '', 'text', '', 10, 0, 1, 0, 1, 0, ''),
		(2, 'applicantmobi', '申請人手機', '申請人手機', 'VARCHAR', 7, 11, '', 'text', '', 12, 0, 1, 0, 1, 0, ''),
		(2, 'applicanttel', '申請人座機', '申請人座機', 'VARCHAR', 7, 18, '', 'text', '', 13, 0, 1, 0, 1, 0, ''),
		(2, 'applicantid', '申請人身份證', '申請人身份證', 'VARCHAR', 15, 18, '', 'text', '', 11, 0, 1, 0, 1, 0, ''),
		(2, 'applicantadd', '申請人住址', '申請人住址', 'VARCHAR', 4, 80, '', 'text', '', 14, 0, 1, 0, 1, 0, ''),
		(2, 'applicantpost', '申請人郵編', '申請人郵編', 'VARCHAR', 6, 6, '', 'text', '', 15, 0, 1, 0, 1, 0, ''),
		(2, 'forum', '互動專區', '互動專區', 'VARCHAR', 0, 150, '', 'text', '', 7, 1, 1, 0, 0, 0, ''),
		(2, 'styletitle', '店舖標題樣式', '店舖標題樣式', 'CHAR', 0, 10, '', 'text', '', 1, 0, 1, 1, 0, 0, ''),
		(2, 'region', '地區分類id', '地區分類id', 'SMALLINT', 0, 6, '', 'text', '', 0, 1, 1, 1, 0, 0, ''),
		(2, 'groupid', '用戶組id', '用戶組id', 'SMALLINT', 0, 6, '', 'text', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enablegood', '發佈商品權限', '發佈商品權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enablenotice', '發佈公告權限', '發佈公告權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enableconsume', '發佈消費券權限', '發佈消費券權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 's_enablealbum', '發佈相冊權限', '發佈相冊權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(2, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(2, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(3, 'styletitle', '標題樣式', '標題樣式', 'CHAR', 0, 10, '', 'text', '', 0, 0, 1, 1, 0, 0, ''),
		(3, 'jumpurl', '公告鏈接地址', '公告鏈接地址', 'VARCHAR', 0, 150, '', 'text', '', 0, 1, 1, 0, 0, 0, ''),
		(3, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(3, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'priceo', '商品原價', '商品原價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'minprice', '網店特價', '網店特價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'maxprice', '網店最高價', '網店最高價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 0, 0, ''),
		(4, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(5, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(5, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(2, 's_enablegroupbuy', '發佈團購權限', '發佈團購權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
		(6, 'groupbuyprice', '原價', '原價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(6, 'groupbuypriceo', '團購價', '團購價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
		(6, 'groupbuymaxnum', '最大購買數', '最大購買數', 'INT', 0, 10, '0', 'text', '', 0, 1, 1, 1, 0, 0, ''),
		(6, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(6, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
		(4, 'intro', '商品介紹', '商品簡短描述', 'VARCHAR', 0, 200, '', 'textarea', '', 1, 1, 1, 1, 0, 0, '');
		");
		if(!DB::result_first('SELECT navid FROM '.tname('nav')." WHERE flag='groupbuy'")) {
			DB::query('INSERT INTO '.tname('nav')." (`type`, `shopid`, `available`, `displayorder`, `flag`, `name`, `url`, `target`, `highlight`) VALUES('sys', 0, 1, 7, 'groupbuy', '團購', 'groupbuy.php', 0, '0');");
		}

		show_msg("[數據升級] 自定義字段 全部結束，進入下一步", 'update.php?step=data&op='.$nextop);

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
		show_msg("[數據升級] 屬性分類id 全部結束，進入下一步", 'update.php?step=data&op='.$nextop);
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
		//結束
		$next = 'update.php?step=delete';
		show_msg("數據庫數據升級完畢，進入下一步數據庫結構清理操作", $next);
	}

} elseif ($_GET['step'] == 'delete') {

	//檢查需要刪除的字段
	//老表集合
	$oldtables = array();
	$query = DB::query("SHOW TABLES LIKE '$_SC[tablepre]%'");
	while ($value = DB::fetch($query)) {
		$values = array_values($value);
		if(!strexists($values[0], 'cache')) {
			$oldtables[] = $values[0];//分表、緩存
		}
	}

	//新表集合
	$sql = sreadfile($sqlfile);
	preg_match_all("/CREATE\s+TABLE\s+`?brand\_(.+?)`?\s+\((.+?)\)\s+(TYPE|ENGINE)\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];

	//需要刪除的表
	$deltables = array();
	$delcolumns = array();

	//老的有，新的沒有
	foreach ($oldtables as $tname) {
		$tname = substr($tname, strlen($_SC['tablepre']));
		if(in_array($tname, $newtables)) {
			//比較字段是否多餘
			$query = DB::query("SHOW CREATE TABLE ".tname($tname));
			$cvalue = DB::fetch($query);
			$oldcolumns = getcolumn($cvalue['Create Table']);

			//新的
			$i = array_search($tname, $newtables);
			$newcolumns = getcolumn($newsqls[$i]);
			//老的有，新的沒有的字段
			foreach ($oldcolumns as $colname => $colstruct) {
				if(!strexists($colname, 'field_')) {
					if($colname == 'PRIMARY') {
						//關鍵字
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
	//顯示
	show_header();
	echo '<form method="post" action="update.php?step=delete">';
	echo '<input type="hidden" name="formhash" value="'.formhash().'">';

	//刪除表
	$deltablehtml = '';
	if($deltables) {
		$deltablehtml .= '<table>';
		foreach ($deltables as $tablename) {
			$deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td></tr>";
		}
		$deltablehtml .= '</table>';
		echo "<p>以下 數據表 與標準數據庫相比是多餘的:<br>您可以根據需要自行決定是否刪除</p>$deltablehtml";
	}
	//刪除字段
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
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][PRIMARY]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td><td>主鍵 PRIMARY</td></tr>";
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$_SC['tablepre']}$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 字段 與標準數據庫相比是多餘的:<br>您可以根據需要自行決定是否刪除</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>與標準數據庫相比，沒有需要刪除的數據表和字段</p><a href=\"?step=cache\">請點擊進入下一步</a></p>";
	} else {
		echo "<p><span style=\"color:#F00;\">刪除請慎重！ 所有UCenter相關表和Discuz!相關的表請勿刪除，shopmessage表ext_開頭的字段為商家信息自定義字段， groupbuy表user_開頭的字段為團購報名信息自定義字段</span>品牌空間數據表內的索引調整可以刪除，其他情況請仔細辨別，如有疑問請到論壇求助</p><p><input type=\"submit\" name=\"delsubmit\" value=\"提交刪除\"></p><p>您也可以忽略多餘的表和字段<br><a href=\"?step=cache\">直接進入下一步</a></p>";
	}
	echo '<input type="hidden" name="formhash" value="'.formhash().'"></form>';

	show_footer();
	exit();

} elseif ($_GET['step'] == 'cache') {

	//更新緩存
	require_once(B_ROOT.'./source/function/cache.func.php');
	updatesettingcache();	//系統設置緩存
	updatecronscache();		//crons列表
	updatecroncache();		//計劃任務
	updatecategorycache();	//分類
	updatecensorcache();	//緩存語言屏蔽
	model_cache();

	//寫log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, '品牌空間');
		fclose($fp);
	}

	show_msg('升級完成，為了您的數據安全，避免重複升級，請登錄FTP刪除本文件!');
}


//正則匹配,獲取字段/索引/關鍵字信息
function getcolumn($creatsql) {

	preg_match("/\((.+)\)/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);//特使字符替換
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);//去掉末尾逗號

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
	$value = trim(preg_replace("/\s+/", ' ', $value));//空格標準化
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )'), array('', ',', ',','(',')'), $value);//去掉無用符號
	$value = preg_replace('/(text NOT NULL) default \'\'/i',"\\1", $value);//去掉無用符號
	return $value;
}

//顯示
function show_msg($message, $url_forward='') {
	global $_G, $_SGLOBAL;

	obclean();

	if($url_forward) {
		$_SGLOBAL['extrahead'] = '<meta http-equiv="refresh" content="1; url='.$url_forward.'">';
		$message = "<a href=\"$url_forward\">$message(跳轉中...)</a>";
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


//頁面頭部
function show_header() {
	global $_G, $_SGLOBAL, $_SC;

	$nowarr = array($_GET['step'] => ' class="current"');

	if(empty($_SGLOBAL['extrahead'])) $_SGLOBAL['extrahead'] = '';

	print<<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
$_SGLOBAL[extrahead]
<title> 品牌空間 數據庫升級程序 </title>
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
<h1>品牌空間 數據庫升級工具</h1>
<div style="width:90%;margin:0 auto;">
<table id="menu">
<tr>
<td{$nowarr[start]}>升級開始</td>
<td{$nowarr[check]}>UC檢測</td>
<td{$nowarr[sql]}>數據庫結構添加/升級</td>
<td{$nowarr[data]}>數據庫數據升級</td>
<td{$nowarr[delete]}>數據庫結構刪除</td>
<td{$nowarr[cache]}>升級完成</td>
</tr>
</table>
<br>
END;
}

//頁面頂部
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
		show_msg("[數據升級] 模形狀態冗余更新 全部結束，進入下一步", 'update.php?step=data&op='.$nextstep_end);
	}
	$newi = $i+1;
	$page = 5000;
	$start = $i*$page;
	$end = $newi*$page;
	$maxid = DB::result_first("SELECT MAX(itemid) FROM ".tname($mname.'items'));
	if($start>$maxid) {
		$m_step = $m_step+1;
		$newi = 0;
		show_msg("[數據升級] 狀態冗余 {$mname} 完成，進入下一步", 'update.php?step=data&op=setting_grade&m_step='.$m_step);
	} else {
		DB::query("UPDATE ".tname($mname.'items')." i INNER JOIN ".tname('shopitems')." si ON i.shopid=si.itemid SET i.grade_s = si.grade WHERE i.itemid>$start AND i.itemid<=$end");
		show_msg("[數據升級] 狀態冗余 {$mname}: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_grade&m_step='.$m_step.'&i='.$newi);
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
		show_msg("[數據升級] 屬性row表升級 完成，進入下一步", 'update.php?step=data&op='.$nextstep_end);
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
		show_msg("[數據升級] 屬性row表升級: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_attribute_row&i='.$newi);
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
		show_msg("[數據升級] 屬性可選值表升級 完成，進入下一步", 'update.php?step=data&op='.$nextstep_end);
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
		show_msg("[數據升級] 屬性可選值表升級: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_attrvalue&i='.$newi);
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
		show_msg("[數據升級] 內容屬性關係表升級 完成，進入下一步", 'update.php?step=data&op='.$nextstep_end);
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
		show_msg("[數據升級] 內容屬性關係表升級: {$start}~{$end} / {$maxid}，下一步", 'update.php?step=data&op=setting_itemattr&i='.$newi);
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
	    show_msg("[數據升級] 跳過屬性升級，進入下一步", 'update.php?step=data&op='.$nextop);
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
		show_msg("[數據升級] 跳過商品關聯數據升級，進入下一步", 'update.php?step=data&op='.$nextop);
	}

	$query = DB::query("SELECT * FROM ".tname('goodrelated'));
	while($item = DB::fetch($query)) {
		inserttable('relatedinfo', array('itemid'=>$item['goodid'], 'type'=>'good', 'relatedid'=>$item['relatedid'], 'relatedtype'=>$item['type'], 'shopid'=>$item['shopid']), 0, true);
	}
	inserttable('data', array('variable'=>'relatedupdate', 'value'=>1), 0, true);
	show_msg("[數據升級] 商品關聯數據升級 完成，進入下一步", 'update.php?step=data&op='.$nextop);
}

function update_groupfield() {
    global $_G, $_SGLOBAL;
	$nextstep_end = 'setting_attribute';
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$maxid = DB::result_first("SELECT count(id) FROM ".tname('shopgroup'));
	if($i>=$maxid)
		show_msg("[數據升級] 店舖組關聯分類數據升級 完成，進入下一步", 'update.php?step=data&op='.$nextstep_end);
	$catid = DB::result_first("SELECT id FROM ".tname('shopgroup')." LIMIT $i,1");

	updategroupfield($catid);
    $newi = $i+1;
	show_msg("[數據升級] 店舖組關聯分類數據升級: {$newi} / {$maxid}，下一步", 'update.php?step=data&op=update_groupfield&i='.$newi);


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