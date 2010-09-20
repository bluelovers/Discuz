<?php

/*
	[品牌空间] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: db.inc.php 19482 2009-09-02 07:09:38Z monkey $
*/

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$tabletype = DB::version() > '4.1' ? 'Engine' : 'Type';

if(!ckfounder($_G['uid'])) {
	cpmsg('noaccess_isfounder', '', 'error');
}

$operation = trim($_GET['operation']);
$tablepre = $_G['config']['db'][1]['tablepre'];
$dbcharset = $_G['config']['db'][1]['dbcharset'];

$excepttables = array($tablepre.'cache_0', $tablepre.'cache_1', $tablepre.'cache_2', $tablepre.'cache_3', $tablepre.'cache_4', $tablepre.'cache_5', $tablepre.'cache_6', $tablepre.'cache_7', $tablepre.'cache_8', $tablepre.'cache_9', $tablepre.'cache_a', $tablepre.'cache_b', $tablepre.'cache_c', $tablepre.'cache_d', $tablepre.'cache_e', $tablepre.'cache_f');

$query = DB::query("SELECT variable, value FROM ".tname('settings')." WHERE variable IN ('backupdir', 'custombackup')");
while($var = DB::fetch($query)) {
	${$var['variable']} = $var['value'];
}
if(!$backupdir) {
	$backupdir = random(6);
	@mkdir('./data/backup_'.$backupdir, 0777);
	DB::query("REPLACE INTO ".tname('settings')." (variable, value) VALUES ('backupdir', '$backupdir')");
}
$backupdir = 'backup_'.$backupdir;
if(!is_dir('./data/'.$backupdir)) {
	mkdir('./data/'.$backupdir, 0777);
}

if($operation == 'export') {

	if(!submitcheck('exportsubmit')) {

		$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';

		$tables = '';
		$bdtables = array();
		if(!empty($custombackup)) {
			$tables = unserialize($custombackup);
			$tables = is_array($tables) ? $tables : '';
		}

		$brand_tables = fetchtablelist($tablepre);

		foreach($brand_tables as $table) {
			$bdtables[$table['Name']] = $table['Name'];
		}
		$bdtables = array_diff($bdtables, $excepttables);

		$defaultfilename = date('ymd').'_'.random(8);

		shownav('admintools', 'nav_db', 'nav_db_export');
		showsubmenu('nav_db', array(
			array('nav_db_export', 'db&operation=export', 1),
			array('nav_db_import', 'db&operation=import', 0)

		));
		showtips('db_export_tips');
		showformheader('db&operation=export&setup=1');
		showtableheader();
		showsetting('db_export_type', array('type', array(
			array('brand', $lang['db_export_brand'], array('showtables' => 'none')),
			array('custom', $lang['db_export_custom'], array('showtables' => ''))
		)), 'brand', 'mradio');

		showtagheader('tbody', 'showtables');
		showtablerow('', '', '<input class="checkbox" name="chkall" onclick="checkall(this.form, \'customtables\', \'chkall\', \'\', true)" checked="checked" type="checkbox" id="chkalltables" /><label for="chkalltables"> '.lang('db_export_custom_select_all').' - '.lang('db_export_brand_table')).'</label>';
		showtablerow('', 'colspan="2"', mcheckbox('customtables', $bdtables));
		showtagfooter('tbody');

		showtagheader('tbody', 'advanceoption');
		showsetting('db_export_method', '', '', '<ul class="nofloat"><li><input class="radio" type="radio" name="method" value="multivol" checked="checked" id="method_multivol" /><label for="method_multivol"> '.$lang['db_export_multivol'].'</label> <input type="text" class="txt" size="40" name="sizelimit" value="2048" /></li></ul>');
		showtitle('db_export_options');
		showsetting('db_export_options_extended_insert', 'extendins', 0, 'radio');
		/*
		showsetting('db_export_options_sql_compatible', array('sqlcompat', array(
			array('', $lang['default']),
			array('MYSQL40', 'MySQL 3.23/4.0.x'),
			array('MYSQL41', 'MySQL 4.1.x/5.x')
		)), '', 'mradio');
		*/
		showsetting('db_export_options_charset', array('sqlcharset', array(
			array('', lang('default')),
			$dbcharset ? array($dbcharset, strtoupper($dbcharset)) : array(),
			DB::version() > '4.1' && $dbcharset != 'utf8' ? array('utf8', 'UTF-8') : array()
		), true), 0, 'mradio');
		showsetting('db_export_usehex', 'usehex', 1, 'radio');
		if(function_exists('gzcompress')) {
			showsetting('db_export_usezip', array('usezip', array(
				array('1', $lang['db_export_zip_1']),
				array('2', $lang['db_export_zip_2']),
				array('0', $lang['db_export_zip_3'])
			)), 0, 'mradio');
		}
		showsetting('db_export_filename', '', '', '<input type="text" class="txt" name="filename" value="'.$defaultfilename.'" />.sql');
		showtagfooter('tbody');

		showsubmit('exportsubmit', 'submit', '', 'more_options');
		showtablefooter();
		showformfooter();

	} else {

		DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

		$filename = trim($_REQUEST['filename']);
		$type = trim($_REQUEST['type']);
		$method = trim($_REQUEST['method']);
		$sqlcharset = trim($_REQUEST['sqlcharset']);
		$sqlcompat = '';
		$usezip = intval($_REQUEST['usezip']);
		$sizelimit = intval($_REQUEST['sizelimit']);
		$usehex = intval($_REQUEST['usehex']);
		$extendins = intval($_REQUEST['extendins']);
		$volume = intval($_REQUEST['volume']);

		if(!$filename || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $filename)) {
			cpmsg('database_export_filename_invalid', '', 'error');
		}

		$time = gmdate("Y-n-j H:i", $_G['timestamp'] + $_G['setting']['timeoffset'] * 3600);

		if($type == 'brand') {
			$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
		} elseif($type == 'custom') {
			$tables = array();
			if(empty($_GET['setup'])) {
				if(!empty($custombackup)) {
					$tables = unserialize($custombackup);
				}
			} else {
				$customtables = $_POST['customtables'];
				$customtablesnew = empty($customtables)? '' : addslashes(serialize($customtables));
				DB::query("REPLACE INTO ".tname('settings')." (variable, value) VALUES ('custombackup', '$customtablesnew')");
				$tables = & $customtables;
			}
			if(!is_array($tables) || empty($tables)) {
				cpmsg('database_export_custom_invalid', '', 'error');
			}
		}

		$volume = intval($volume) + 1;
		$idstring = '# Identify: '.base64_encode("$_G[timestamp],$version,$type,$method,$volume")."\n";

		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', $_G['charset']);
		$setnames = ($sqlcharset && DB::version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if(DB::version() > '4.1') {
			if($sqlcharset) {
				DB::query("SET NAMES '".$sqlcharset."';\n\n");
			}
			if($sqlcompat == 'MYSQL40') {
				DB::query("SET SQL_MODE='MYSQL40'");
			} elseif($sqlcompat == 'MYSQL41') {
				DB::query("SET SQL_MODE=''");
			}
		}

		$backupfilename = './data/'.$backupdir.'/'.str_replace(array('/', '\\', '.'), '', $filename);

		if($usezip) {
			require_once B_ROOT.'source/adminfunc/zip.func.php';
		}

		if($method == 'multivol') {

			$sqldump = '';
			$tableid = intval($_REQUEST['tableid']);
			$startfrom = intval($_REQUEST['startfrom']);
			$dumptablestruct = $_REQUEST['dumptablestruct'];

			if(!$tableid && !$dumptablestruct) {
				foreach($tables as $table) {
					$sqldump .= sqldumptablestruct($table);
				}
				$dumptablestruct = true;
			}
			$complete = true;
			for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $sizelimit * 1000; $tableid++) {
				$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
				if($complete) {
					$startfrom = 0;
				}
			}

			$dumpfile = $backupfilename."-%s".'.sql';
			!$complete && $tableid--;
			if(trim($sqldump)) {
				$sqldump = "$idstring".
					"# <?exit();?>\n".
					"# Brand! Multi-Volume Data Dump Vol.$volume\n".
					"# Version: Brand! $version\n".
					"# Time: $time\n".
					"# Type: $type\n".
					"# Table Prefix: $tablepre\n".
					"#\n".
					"# Brand! Home: http://www.discuz.com\n".
					"# Please visit our website for newest infomation about Brand!\n".
					"# --------------------------------------------------------\n\n\n".
					"$setnames".
					$sqldump;
				$dumpfilename = sprintf($dumpfile, $volume);
				@$fp = fopen($dumpfilename, 'wb');
				@flock($fp, 2);
				if(@!fwrite($fp, $sqldump)) {
					@fclose($fp);
					cpmsg('database_export_file_invalid', '', 'error');
				} else {
					fclose($fp);
					if($usezip == 2) {
						$fp = fopen($dumpfilename, "r");
						$content = @fread($fp, filesize($dumpfilename));
						fclose($fp);
						$zip = new zipfile();
						$zip->addFile($content, basename($dumpfilename));
						$fp = fopen(sprintf($backupfilename."-%s".'.zip', $volume), 'w');
						if(@fwrite($fp, $zip->file()) !== false) {
							@unlink($dumpfilename);
						}
						fclose($fp);
					}
					unset($sqldump, $zip, $content);
					if(isset($lang['database_export_multivol_redirect'])) {
						eval("\$msg_title = \"".$lang['database_export_multivol_redirect']."\";");
					}
					cpmsg($msg_title, "$BASESCRIPT?action=db&operation=export&type=".rawurlencode($type)."&saveto=server&filename=".rawurlencode($filename)."&method=multivol&sizelimit=".rawurlencode($sizelimit)."&volume=".rawurlencode($volume)."&tableid=".rawurlencode($tableid)."&startfrom=".rawurlencode($startrow)."&extendins=".rawurlencode($extendins)."&sqlcharset=".rawurlencode($sqlcharset)."&sqlcompat=".rawurlencode($sqlcompat)."&exportsubmit=yes&usehex=$usehex&usezip=$usezip&dumptablestruct=$dumptablestruct&formhash=".formhash(), 'loading');
				}
			} else {
				$volume--;
				$filelist = '<ul>';
				//cpheader();

				if($usezip == 1) {
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$unlinks = array();
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($dumpfile, $i);
						$fp = fopen($filename, "r");
						$content = @fread($fp, filesize($filename));
						fclose($fp);
						$zip->addFile($content, basename($filename));
						$unlinks[] = $filename;
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					$fp = fopen($zipfilename, 'w');
					if(@fwrite($fp, $zip->file()) !== false) {
						foreach($unlinks as $link) {
							@unlink($link);
						}
					} else {
						cpmsg('database_export_multivol_succeed', '', 'succeed');
					}
					unset($sqldump, $zip, $content);
					fclose($fp);
					@touch('./data/'.$backupdir.'/index.htm');
					$filename = $zipfilename;
					if(isset($lang['database_export_zip_succeed'])) {
						eval("\$msg_title = \"".$lang['database_export_zip_succeed']."\";");
					}
					cpmsg($msg_title, '', 'succeed');
				} else {
					@touch('./data/'.$backupdir.'/index.htm');
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($usezip == 2 ? $backupfilename."-%s".'.zip' : $dumpfile, $i);
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					if(isset($lang['database_export_multivol_succeed'])) {
						eval("\$msg_title = \"".$lang['database_export_multivol_succeed']."\";");
					}
					cpmsg($msg_title, '', 'succeed');
				}
			}

		} else {

			$tablesstr = '';
			foreach($tables as $table) {
				$tablesstr .= '"'.$table.'" ';
			}

			$dbhost = $_G['config']['db'][1]['dbhost'];
			$dbuser = $_G['config']['db'][1]['dbuser'];
			$dbpw = $_G['config']['db'][1]['dbpw'];
			$dbname = $_G['config']['db'][1]['dbname'];

			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = DB::query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = DB::fetch($query, MYSQL_NUM);

			$dumpfile = addslashes(dirname(dirname(dirname(__FILE__)))).'/'.$backupfilename.'.sql';
			@unlink($dumpfile);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			@shell_exec($mysqlbin.'mysqldump --force --quick '.(DB::version() > '4.1' ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($extendins == 1 ? ' --extended-insert' : '').''.(DB::version() > '4.1' && $sqlcompat == 'MYSQL40' ? ' --compatible=mysql40' : '').' --host="'.$dbhost.($dbport ? (is_numeric($dbport) ? ' --port='.$dbport : ' --socket="'.$dbport.'"') : '').'" --user="'.$dbuser.'" --password="'.$dbpw.'" "'.$dbname.'" '.$tablesstr.' > '.$dumpfile);

			if(@file_exists($dumpfile)) {

				if($usezip) {
					require_once B_ROOT.'source/adminfunc/zip.func.php';
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$fp = fopen($dumpfile, "r");
					$content = @fread($fp, filesize($dumpfile));
					fclose($fp);
					$zip->addFile($idstring."# <?exit();?>\n ".$setnames."\n #".$content, basename($dumpfile));
					$fp = fopen($zipfilename, 'w');
					@fwrite($fp, $zip->file());
					fclose($fp);
					@unlink($dumpfile);
					@touch('./data/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.zip';
					unset($sqldump, $zip, $content);
					if(isset($lang['database_export_zip_succeed'])) {
						eval("\$msg_title = \"".$lang['database_export_zip_succeed']."\";");
					}
					cpmsg($msg_title, '', 'succeed');
				} else {
					if(@is_writeable($dumpfile)) {
						$fp = fopen($dumpfile, 'rb+');
						@fwrite($fp, $idstring."# <?exit();?>\n ".$setnames."\n #");
						fclose($fp);
					}
					@touch('./data/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.sql';
					if(isset($lang['database_export_succeed'])) {
						eval("\$msg_title = \"".$lang['database_export_succeed']."\";");
					}
					cpmsg($msg_title, '', 'succeed');
				}

			} else {

				cpmsg('database_shell_fail', '', 'error');

			}

		}
	}

} elseif($operation == 'importzip') {

	if(empty($_REQUEST['datafile_server'])) {
		cpmsg('database_import_file_illegal', '', 'error');
	} else {
		$datafile_server = B_ROOT.'./data/'.$backupdir.'/'.basename($_REQUEST['datafile_server']);
		if(!@file_exists($datafile_server)) {
			cpmsg('database_import_file_illegal', '', 'error');
		}
	}
	
	require_once B_ROOT.'source/adminfunc/zip.func.php';
	$unzip = new SimpleUnzip();
	$unzip->ReadFile($datafile_server);
	
	if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match("/\.sql$/i", $importfile = $unzip->GetName(0))) {
		cpmsg('database_import_file_illegal', '', 'error');
	}

	$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($unzip->GetData(0), 0, 256))));
	$confirm = !empty($confirm) ? 1 : 0;
	if(!$confirm && $identify[1] != $version) {
		cpmsg('database_import_confirm', $BASESCRIPT.'?action=db&operation=importzip&datafile_server=$datafile_server&importsubmit=yes&confirm=yes&formhash='.formhash(), 'form');
	}

	$sqlfilecount = 0;
	foreach($unzip->Entries as $entry) {
		if(preg_match("/\.sql$/i", $entry->Name)) {
			$fp = fopen('./data/'.$backupdir.'/'.$entry->Name, 'w');
			fwrite($fp, $entry->Data);
			fclose($fp);
			$sqlfilecount++;
		}
	}

	if(!$sqlfilecount) {
		cpmsg('database_import_file_illegal', '', 'error');
	}

	$info = basename($datafile_server).'<br />'.$lang['version'].': '.$identify[1].'<br />'.$lang['type'].': '.$lang['db_export_'.$identify[2]].'<br />'.$lang['db_method'].': '.($identify[3] == 'multivol' ? $lang['db_multivol'] : $lang['db_shell']).'<br />';

	if(isset($multivol)) {
		$multivol++;
		$datafile_server = preg_replace("/-(\d+)(\..+)$/", "-$multivol\\2", $datafile_server);
		if(file_exists($datafile_server)) {
			if(isset($lang['database_import_multivol_unzip_redirect'])) {
				eval("\$msg_title = \"".$lang['database_import_multivol_unzip_redirect']."\";");
			}
			cpmsg($msg_title, $BASESCRIPT.'?action=db&operation=importzip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes&formhash='.formhash(), 'loading');
		} else {
			cpmsg('database_import_multivol_confirm', $BASESCRIPT.'?action=db&operation=import&from=server&datafile_server='.$datafile_vol1.'&importsubmit=yes&delunzip=yes&formhash='.formhash(), 'form');
		}
	}

	if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $datafile_server)) {
		$datafile_vol1 = $datafile_server;
		$datafile_server = preg_replace("/-1(\..+)$/", "-2\\1", $datafile_server);
		if(file_exists($datafile_server)) {
			if(isset($lang['database_import_multivol_unzip'])) {
				eval("\$msg_title = \"".$lang['database_import_multivol_unzip']."\";");
			}
			cpmsg($msg_title, $BASESCRIPT.'?action=db&operation=importzip&multivol=1&datafile_vol1=./data/'.$backupdir.'/'.$importfile.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes&formhash='.formhash(), 'form');
		}
	}
	if(isset($lang['database_import_unzip'])) {
		eval("\$msg_title = \"".$lang['database_import_unzip']."\";");
	}
	cpmsg($msg_title, $BASESCRIPT.'?action=db&operation=import&from=server&datafile_server=./data/'.$backupdir.'/'.$importfile.'&importsubmit=yes&delunzip=yes&formhash='.formhash(), 'form');

} elseif($operation == 'import') {

	if(!submitcheck('importsubmit') && !submitcheck('deletesubmit')) {

		$exportlog = $exportsize = $exportziplog = array();
		if(is_dir(B_ROOT.'./data/'.$backupdir)) {
			$dir = dir(B_ROOT.'./data/'.$backupdir);
			while($entry = $dir->read()) {
				$entry = './data/'.$backupdir.'/'.$entry;
				if(is_file($entry)) {
					if(preg_match("/\.sql$/i", $entry)) {
						$filesize = filesize($entry);
						$fp = fopen($entry, 'rb');
						$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						fclose($fp);
						$key = preg_replace('/^(.+?)(\-\d+)\.sql$/i', '\\1', basename($entry));
						$exportlog[$key][$identify[4]] = array(
							'version' => $identify[1],
							'type' => $identify[2],
							'method' => $identify[3],
							'volume' => $identify[4],
							'filename' => $entry,
							'dateline' => filemtime($entry),
							'size' => $filesize
						);
						$exportsize[$key] += $filesize;
					} elseif(preg_match("/\.zip$/i", $entry)) {
						$filesize = filesize($entry);
						$exportziplog[] = array(
							'type' => 'zip',
							'filename' => $entry,
							'size' => filesize($entry),
							'dateline' => filemtime($entry)
						);
					}
				}
			}
			$dir->close();
		} else {
			cpmsg('database_export_dest_invalid', '', 'error');
		}

		shownav('admintools', 'nav_db', 'nav_db_import');
		showsubmenu('nav_db', array(
			array('nav_db_export', 'db&operation=export', 0),
			array('nav_db_import', 'db&operation=import', 1)

		));
		showtips('db_import_tips');
		showtableheader('db_import');
		showformheader('db&operation=import', 'enctype');
		showtablerow('', array('colspan="2" class="rowform"', 'colspan="7" class="rowform"'), array(
			'<input class="radio" type="radio" name="from" value="server" checked="checked" onclick="this.form.datafile_server.disabled=!this.checked;this.form.datafile.disabled=this.checked" />'.$lang[db_import_from_server],
			'<input type="text" class="txt" name="datafile_server" value="./data/'.$backupdir.'/" style="width:245px;" />'
		));
		showtablerow('', array('colspan="2" class="rowform"', 'colspan="8" class="rowform"'), array(
			'<input class="radio" type="radio" name="from" value="local" onclick="this.form.datafile_server.disabled=this.checked;this.form.datafile.disabled=!this.checked" />'.$lang[db_import_from_local],
			'<input type="file" name="datafile" size="29" disabled="disabled" class="uploadbtn marginbot" />'
		));
		showsubmit('importsubmit');
		showformfooter();

		showformheader('db&operation=import');
		showtitle('db_export_file');
		showsubtitle(array('', 'filename', 'time', 'type', 'size', 'db_method', 'db_volume', ''));

		foreach($exportlog as $key => $val) {
			$info = $val[1];
			$info['dateline'] = is_int($info['dateline']) ? gmdate("Y-n-j H:i", $info['dateline'] + $_G['setting']['timeoffset'] * 3600) : $lang['unknown'];
			$info['size'] = sizecount($exportsize[$key]);
			$info['volume'] = count($val);
			$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_shell']) : '';
			showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".$key."\">",
				"<a href=\"javascript:;\" onclick=\"document.getElementById('exportlog_$key').style.display = document.getElementById('exportlog_$key').style.display == '' ? 'none' : ''\">".$key."</a>",
				$info['dateline'],
				$lang['db_export_'.$info['type']],
				$info['size'],
				$info['method'],
				$info['volume'],
				$info['type'] == 'zip' ? "<a href=\"$BASESCRIPT?action=db&operation=importzip&datafile_server=$info[filename]&importsubmit=yes\" class=\"act\">$lang[db_import_unzip]</a>" : "<a class=\"act\" href=\"$BASESCRIPT?action=db&operation=import&from=server&datafile_server=$info[filename]&importsubmit=yes&formhash=".formhash()."\"".($info['version'] != $version ? " onclick=\"return confirm('$lang[db_import_confirm]');\"" : '')." class=\"act\">$lang[import]</a>"
			));
			echo '<tbody id="exportlog_'.$key.'" style="display:none">';
			foreach($val as $info) {
				$info['dateline'] = is_int($info['dateline']) ? gmdate("Y-n-j H:i", $info['dateline'] + $_G['setting']['timeoffset'] * 3600) : $lang['unknown'];
				$info['size'] = sizecount($info['size']);
				showtablerow('', '', array(
					'',
					"<a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a>",
					$info['dateline'],
					'',
					$info['size'],
					'',
					$info['volume'],
					''
				));
			}
			echo '</tbody>';
		}

		foreach($exportziplog as $info) {
			$info['dateline'] = is_int($info['dateline']) ? gmdate("Y-n-j H:i", $info['dateline'] + $_G['setting']['timeoffset'] * 3600) : $lang['unknown'];
			$info['size'] = sizecount($info['size']);
			$info['method'] = $info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_shell'];
			showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".basename($info['filename'])."\">",
				"<a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a>",
				$info['dateline'],
				$lang['db_export_'.$info['type']],
				$info['size'],
				$info['method'],
				'',
				"<a href=\"$BASESCRIPT?action=db&operation=importzip&datafile_server=$info[filename]&importsubmit=yes\" class=\"act\">$lang[db_import_unzip]</a>"
			));
		}

		showsubmit('deletesubmit', 'submit', 'del');
		showformfooter();

		showtablefooter();

	} elseif(submitcheck('importsubmit')) {

		$readerror = 0;
		$datafile = '';
		$datafile_server = trim($_REQUEST['datafile_server']);
		if($_REQUEST['from'] == 'server') {
			$datafile = B_ROOT.'./'.$datafile_server;
		} elseif($_REQUEST['from'] == 'local') {
			$datafile = $_FILES['datafile']['tmp_name'];
		}
		$datafile = urldecode($datafile);
		if(@$fp = fopen($datafile, 'rb')) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
			$dumpinfo = array('method' => $identify[3], 'volume' => intval($identify[4]));
			if($dumpinfo['method'] == 'multivol') {
				$sqldump .= fread($fp, filesize($datafile));
			}
			fclose($fp);
		} else {
			if($_REQUEST['autoimport']) {
				//updatecache
				cpmsg('database_import_multivol_succeed', '', 'succeed');
			} else {
				cpmsg('database_import_file_illegal', '', 'error');
			}
		}

		if($dumpinfo['method'] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);

			foreach($sqlquery as $sql) {

				$sql = syntablestruct(trim($sql), DB::version() > '4.1', $dbcharset);

				if($sql != '') {
					DB::query($sql, 'SILENT');
					/*if(($sqlerror = DB::error()) && DB::errno() != 1062) {
						$_SGLOBAL['db']->halt('MySQL Query Error', $sql);
					}*/
				}
			}

			if($_REQUEST['delunzip']) {
				@unlink($datafile_server);
			}

			if($_REQUEST['from'] == 'local') {
				cpmsg('database_import_file_succeed', $BASESCRIPT.'?action=db&operation=import&formhash='.formhash(), 'succeed');
			}

			$datafile_next = preg_replace("/-($dumpinfo[volume])(\..+)$/", "-".($dumpinfo['volume'] + 1)."\\2", $datafile_server);
			$datafile_next = urlencode($datafile_next);
			if($dumpinfo['volume'] == 1) {
				cpmsg('database_import_multivol_prompt',
					"$BASESCRIPT?action=db&operation=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes&formhash=".formhash().(!empty($delunzip) ? '&delunzip=yes' : ''),
					'form');
			} elseif($_REQUEST['autoimport']) {
				if(isset($lang['database_import_multivol_redirect'])) {
					eval("\$msg_title = \"".$lang['database_import_multivol_redirect']."\";");
				}
				cpmsg($msg_title, "$BASESCRIPT?action=db&operation=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes&formhash=".formhash().(!empty($delunzip) ? '&delunzip=yes' : ''), 'loading');
			} else {
				//updatecache
				cpmsg('database_import_succeed', '', 'succeed');
			}
		} elseif($dumpinfo['method'] == 'shell') {
			require './config.php';
			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = DB::query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = DB::fetch($query, MYSQL_NUM);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			shell_exec($mysqlbin.'mysql -h"'.$dbhost.($dbport ? (is_numeric($dbport) ? ' -P'.$dbport : ' -S"'.$dbport.'"') : '').
				'" -u"'.$dbuser.'" -p"'.$dbpw.'" "'.$dbname.'" < '.$datafile);

			//updatecache
			cpmsg('database_import_succeed', '', 'succeed');
		} else {
			cpmsg('database_import_format_illegal', '', 'error');
		}

	} elseif(submitcheck('deletesubmit')) {
		if(is_array($_POST['delete'])) {
			foreach($_POST['delete'] as $filename) {
				$file_path = './data/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename);
				if(is_file($file_path)) {
					@unlink($file_path);
				} else {
					$i = 1;
					while(1) {
						$file_path = './data/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename.'-'.$i.'.sql');
						if(is_file($file_path)) {
							@unlink($file_path);
							$i++;
						} else {
							break;
						}
					}
				}
			}
			cpmsg('database_file_delete_succeed', '', 'succeed');
		} else {
			cpmsg('database_file_delete_invalid', '', 'error');
		}
	}

}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function fetchtablelist($tablepre = '') {
	global $_G, $_SGLOBAL;

	$arr = explode('.', $tablepre);
	$dbname = $arr[1] ? $arr[0] : '';
	$tablepre = str_replace('_', '\_', $tablepre);
	$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
	$tables = $table = array();
	$query = DB::query("SHOW TABLE STATUS $sqladd");
	while($table = DB::fetch($query)) {
		$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
		$tables[] = $table;
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $val) {
		$return[] = $val[$key2];
	}
	return $return;
}


function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === false) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === false ? false : true;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function sqldumptablestruct($table) {
	global $_G, $db, $sqlcompat, $dumpcharset, $sqlcharset;

	$createtable = DB::query("SHOW CREATE TABLE $table", 'SILENT');

	if(!DB::error()) {
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
	} else {
		return '';
	}

	$create = DB::fetch_row($createtable);

	if(strpos($table, '.') !== FALSE) {
		$tablename = substr($table, strpos($table, '.') + 1);
		$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
	}
	$tabledump .= $create[1];

	if($sqlcompat == 'MYSQL41' && DB::version() < '4.1') {
		$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
	}
	if(DB::version() > '4.1' && $sqlcharset) {
		$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);
	}

	$tablestatus = DB::fetch_first("SHOW TABLE STATUS LIKE '$table'");
	$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
	if($sqlcompat == 'MYSQL40' && DB::version() >= '4.1' && DB::version() < '5.1') {
		if($tablestatus['Auto_increment'] <> '') {
			$temppos = strpos($tabledump, ',');
			$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
		}
		if($tablestatus['Engine'] == 'MEMORY') {
			$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
		}
	}
	return $tabledump;
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $_G, $_SGLOBAL, $sizelimit, $startrow, $extendins, $usehex, $complete, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = DB::query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(strexists($table, 'adminsession')) {
		return ;
	} elseif(!$query && DB::errno() == 1146) {
		return;
	} elseif(!$query) {
		$usehex = false;
	} else {
		while($fieldrow = DB::fetch($query)) {
			$tablefields[] = $fieldrow;
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if($extendins == '0') {
			while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = DB::num_fields($rows);

				$numrows = DB::num_rows($rows);
				while($row = DB::fetch_row($rows)) {
					$comma = $t = '';
					for($i = 0; $i < $numfields; $i++) {
						$t .= $comma.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($t) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($t);\n";
					} else {
						$complete = false;
						break 2;
					}
				}
			}
		} else {
			while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = DB::num_fields($rows);

				if($numrows = DB::num_rows($rows)) {
					$t1 = $comma1 = '';
					while($row = DB::fetch_row($rows)) {
						$t2 = $comma2 = '';
						for($i = 0; $i < $numfields; $i++) {
							$t2 .= $comma2.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text'))? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma2 = ',';
						}
						if(strlen($t1) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$t1 .= "$comma1 ($t2)";
							$comma1 = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $t1;\n";
							$complete = false;
							break 2;
						}
					}
					$tabledump .= "INSERT INTO $table VALUES $t1;\n";
				}
			}
		}

		$startrow = $startfrom;
		$tabledump .= "\n";
	}

	return $tabledump;
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function slowcheck($type1, $type2) {
	$t1 = explode(' ', $type1);$t1 = $t1[0];
	$t2 = explode(' ', $type2);$t2 = $t2[0];
	$arr = array($t1, $t2);
	sort($arr);
	if($arr == array('mediumtext', 'text')) {
		return true;
	} elseif(substr($arr[0], 0, 4) == 'char' && substr($arr[1], 0, 7) == 'varchar') {
		return true;
	}
	return false;
}

?>