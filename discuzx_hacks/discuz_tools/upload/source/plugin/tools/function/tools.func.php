<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tools.func.php 2514 2011-05-31 09:36:35Z songlixin $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(file_exists(DISCUZ_ROOT.'/data/file.csv')){
	@unlink(DISCUZ_ROOT.'/data/file.csv');
}

function toolslang($var,$lang = 's'){
	
	if(file_exists(DISCUZ_ROOT.'./data/plugindata/tools.lang.'.CHARSET.'.php')){
		require DISCUZ_ROOT.'./data/plugindata/tools.lang.'.CHARSET.'.php';
		if($lang == 't'){
			return $templatelang['tools'][$var];
		} else {
			return $scriptlang['tools'][$var];
		}
	} else {
		return lang('plugin/tools',$var);	
	}
}

function getmaxmin($table,$key) {
	$max = DB::result_first("SELECT max($key) FROM ".DB::table($table));
	$min = DB::result_first("SELECT min($key) FROM ".DB::table($table)) ? DB::result_first("SELECT min($key) FROM ".DB::table($table)) : '0';
	return array('min' => $min,'max' => $max);
}

function save_config_file($filename, $config, $default) {
	$config = setdefault($config, $default);

	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$year = date('Y');
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)." //\r\n\r\n?>";
	file_put_contents($filename, $content);
}

function getvars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function setdefault($var, $default) {
	foreach ($default as $k => $v) {
		if(!isset($var[$k])) {
			$var[$k] = $default[$k];
		} elseif(is_array($v)) {
			$var[$k] = setdefault($var[$k], $default[$k]);
		}
	}
	return $var;
}

function buildarray($array, $level = 0, $pre = '$_config') {
	static $ks;
	if($level == 0) {
		$ks = array();
		$return = '';
	}

	foreach ($array as $key => $val) {

		if($level == 0) {
			$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 50, '-', STR_PAD_BOTH);
			$return .= "\r\n// $newline //\r\n";
		}

		$ks[$level] = $ks[$level - 1]."['$key']";
		if(is_array($val)) {
			$ks[$level] = $ks[$level - 1]."['$key']";
			$return .= buildarray($val, $level + 1, $pre);
		} else {
			$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			$return .= $pre.$ks[$level - 1]."['$key']"." = $val;\r\n";
		}
	}
	return $return;
}

function save_uc_config($config, $file) {

	$success = false;

	list($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset, $ucapi, $ucip) = explode('|', $config);

	$link = mysql_connect($ucdbhost, $ucdbuser, $ucdbpw, 1);
	$uc_connnect = $link && mysql_select_db($ucdbname, $link) ? 'mysql' : '';

	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$year = date('Y');
	$config = <<<EOT
<?php


define('UC_CONNECT', '$uc_connnect');

define('UC_DBHOST', '$ucdbhost');
define('UC_DBUSER', '$ucdbuser');
define('UC_DBPW', '$ucdbpw');
define('UC_DBNAME', '$ucdbname');
define('UC_DBCHARSET', '$ucdbcharset');
define('UC_DBTABLEPRE', '`$ucdbname`.$uctablepre');
define('UC_DBCONNECT', 0);

define('UC_CHARSET', '$uccharset');
define('UC_KEY', '$appauthkey');
define('UC_API', '$ucapi');
define('UC_APPID', '$appid');
define('UC_IP', '$ucip');
define('UC_PPP', 20);
?>
EOT;

	if($fp = fopen($file, 'w')) {
		fwrite($fp, $config);
		fclose($fp);
		$success = true;
	}
	return $success;
}


function loadcsstemplate() {
	global $_G;
	$scriptcss = '<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_common.css?{VERHASH}" />';
	$content = $this->csscurmodules = '';
	$content = @implode('', file(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_module.css'));
	$content = preg_replace("/\[(.+?)\](.*?)\[end\]/ies", "\$this->cssvtags('\\1','\\2')", $content);
	if($this->csscurmodules) {
		$this->csscurmodules = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $this->csscurmodules);;
		if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_'.$_G['basescript'].'_'.CURMODULE.'.css', 'w')) {
			fwrite($fp, $this->csscurmodules);
			fclose($fp);
		} else {
			exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
		}
		$scriptcss .='<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_'.$_G['basescript'].'_'.CURMODULE.'.css?{VERHASH}" />';
	}
	return $scriptcss;
}


function sex($gender){
	if($gender == 0){
		$re = toolslang('gender0');
	} elseif($gender == 1){
		$re = toolslang('gender1');
	} else {
		$re = toolslang('gender2');
	}
	return $re;
}

function tools_runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];
	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre, ' '.$tablepre, ' `'.$tablepre), $sql));

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
				DB::query(tools_createtable($query, $dbcharset));
			} else {
				DB::query($query);
			}
		}
	}
	return 1;
}

function tools_createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}


function toolsmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G;
	$_G['debug'] = 0;
	$param = array(
		'header'	=> false,
		'timeout'	=> null,
		'refreshtime'	=> null,
		'closetime'	=> null,
		'locationtime'	=> null,
		'alert'		=> null,
		'return'	=> false,
		'redirectmsg'	=> 0,
		'msgtype'	=> 1,
		'showmsg'	=> true,
		'showdialog'	=> false,
		'login'		=> false,
		'handle'	=> false,
	);

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template('tools:showmessage');
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @unserialize($_G['setting']['msgforward']);
	$handlekey = '';

	if(empty($_G['inajax']) && (!empty($_G['gp_quickforward']) || $_G['setting']['msgforward']['quick'] && $_G['setting']['msgforward']['messages'] && @in_array($message, $_G['setting']['msgforward']['messages']))) {
		$param['header'] = true;
	}
	if(!empty($_G['inajax'])) {
		$handlekey = $_G['gp_handlekey'] = !empty($_G['gp_handlekey']) ? htmlspecialchars($_G['gp_handlekey']) : '';
		$param['handle'] = true;
	}
	if(!empty($_G['inajax'])) {
		$param['msgtype'] = empty($_G['gp_ajaxmenu']) && (empty($_POST) || !empty($_G['gp_nopost'])) ? 2 : 3;
	}
	if($url_forward) {
		$param['timeout'] = true;
		if($param['handle'] && !empty($_G['inajax'])) {
			$param['showmsg'] = false;
		}
	}

	foreach($extraparam as $k => $v) {
		$param[$k] = $v;
	}
	if(array_key_exists('set', $extraparam)) {
		$setdata = array('1' => array('msgtype' => 3));
		if($setdata[$extraparam['set']]) {
			foreach($setdata[$extraparam['set']] as $k => $v) {
				$param[$k] = $v;
			}
		}
	}

	if($param['timeout'] !== null) {
		$refreshtime = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
		$refreshsecond = !empty($refreshtime) ? $refreshtime : 3;
		$refreshtime = $refreshsecond * 1000;
	} else {
		$refreshtime = $refreshsecond = 0;
	}

	if($param['login'] && $_G['uid'] || $url_forward) {
		$param['login'] = false;
	}

	$param['header'] = $url_forward && $param['header'] ? true : false;

	if($param['header']) {
		header("HTTP/1.1 301 Moved Permanently");
		dheader("location: ".str_replace('&amp;', '&', $url_forward));
	}

	$_G['hookscriptmessage'] = $message;
	$_G['hookscriptvalues'] = $values;
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}
	$show_jsmessage = str_replace("'", "\\\'", $show_message);
	if(!$param['showmsg']) {
		$show_message = '';
	}

	if($param['msgtype'] == 3) {
		$show_message = str_replace(lang('message', 'return_search'), lang('message', 'return_replace'), $show_message);
	}

	$allowreturn = !$param['timeout'] && stristr($show_message, lang('message', 'return')) || $param['return'] ? true : false;
	if($param['alert'] === null) {
		$alerttype = $url_forward ? (preg_match('/\_(succeed|success)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
	} else {
		$alerttype = 'alert_'.$param['alert'];
	}

	$extra = '';
	if($param['handle']) {
		$valuesjs = $comma = $subjs = '';
		foreach($values as $k => $v) {
			if (is_array($v)) {
				$subcomma = '';
				foreach ($v as $subk=>$subv) {
					$subjs .= $subcomma.'\''.$subk.'\':\''.$subv.'\'';
					$subcomma = ',';
				}
				$valuesjs .= $comma.'\''.$k.'\':{'.$subjs.'}';
			} else {
				$valuesjs .= $comma.'\''.$k.'\':\''.$v.'\'';
			}
			$comma = ',';
		}
		$valuesjs = '{'.$valuesjs.'}';
		if($url_forward) {
			$extra .= 'if($(\'return_'.$handlekey.'\')) $(\'return_'.$handlekey.'\').className=\'onright\';if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}
	if($handlekey) {
		if($param['showdialog']) {
			$st = $param['closetime'] !== null ? 'setTimeout("hideMenu(\'fwin_dialog\', \'dialog\')", '.($param['closetime'] * 1000).');' : '';
			$st .= $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \'notice\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward.'\'; }' : 'null').', 0);'.$st.'';
			$param['closetime'] = null;
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	}
	if(!$extra && $param['timeout']) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.'</script>' : '';

	include template('tools:showmessage');
	dexit();
}

function toolsgetsetting($var){
	return DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='$var'");
}

function toolssetsetting($var,$value){
	return DB::query("REPLACE INTO ".DB::table('common_setting')." (skey,svalue) VALUES ('$var','$value')");
}

function topattern_array($source_array) {
	$source_array = preg_replace("/\{(\d+)\}/",".{0,\\1}",$source_array);
	foreach($source_array as $key => $value) {
		$source_array[$key] = '/'.$value.'/i';
	}
	return $source_array;
}

function convtype($var,$lang) {
	$varlang = 'home_'.$var;
	return $lang[$varlang];	

}

function getdirentry($directory,$remove = '') {
	global $dirlist;
	//排除目录
	$removedir = explode('|',$remove);
	
	$fp = opendir($directory);
	while($entry = readdir($fp)) {
		if (in_array($entry,$removedir)) {
			continue;	
		}
		$fulldir = str_replace('.//','./',$directory.'/'.$entry);

		if($entry != '.' && $entry != '..' && is_dir($fulldir)) {
			//目录的话递归
			if(count(explode('/',$fulldir)) <= 3){
				//if(count(explode('/',$fulldir)) == 3 ){
				//	$dirlist[] = array($fulldir,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$fulldir);
				//} else {
					$dirlist[] = array($fulldir,$fulldir);
				//}
				//getdirentry($fulldir,$remove);
			} else {
				continue;	
			}		
		}
	}
}

function searchkeyword($keyword,$dir,$sub=0,$remove = array(),$hack = 0) {
	global $check;
	if($hack == 0) {
		$filetype = '/(\.php|\.php3|\.php4|\.php5|\.htm|\.html|\.js|\.css)$/i';	
	} else {
		$filetype = '/(\.php|\.php3|\.php4|\.php5)$/i';
	}
	$fp = opendir($dir);
	while($filename = readdir($fp)){
		if (in_array($filename,$remove)) {
			continue;
		}
		$path = $dir.$filename;
		if($filename!='.' && $filename!='..'){
			$path = realpath($path);
			if(is_dir($path)){
				$sub && searchkeyword($keyword,$path.'/',$sub,$remove,$hack);
			//搜索文件类型 大小限制 2M
			} elseif(preg_match($filetype,$filename) && filesize($path) < 2097152){
				$content = @file($path);

				//echo $path.$sub.'<br>';
				foreach($content as $key => $value){
					$value = strtolower($value);
					if($hack == 0){
						if(preg_match('/'.$keyword.'/i',$value,$match)){
							$check[$path][] = $key + 1;
						}
					} else {
						if(preg_match($keyword,$value,$match)){
							$check[$path][] = $key + 1;
						}
						
					}

				}
				
				$check[$path] && $check[$path] = implode(',',$check[$path]);
			}
		}
	}
	closedir($fp);
}

function findfile($dir,$ext = array(),$remove = array()) {
	global $filelist;
	$fp = opendir($dir);
	while($entry = readdir($fp)) {
		
		if (in_array($entry,$remove)) {
			continue;
		}
		$fulldir = str_replace('.//','./',$dir.'/'.$entry);
		if($entry != '.' && $entry != '..' && is_dir($fulldir)) {
			findfile($fulldir,$ext,$remove);
		} elseif(in_array(get_ex($entry),$ext)) {
			$filelist[] = $fulldir;
		}
	}
}

function get_ex($file_name){
        $retval = "";
        $pt = strrpos($file_name, ".");
        if($pt) {
        	$retval = substr($file_name, $pt+1, strlen($file_name) - $pt);
        }
        return $retval; 
}

function get_rule(){
	$query = "SELECT * FROM ".DB::table('tools_rule');
	$query = DB::query($query);
	$rule = array();
	while($data = DB::fetch($query)){
		$rule[$data['name']] = $data['rule'];
	}	
	return $rule;
}

function is_num($val){
	return is_numeric($val);
}

function getallthreadtable(){
	global $_G;
	loadcache('threadtableids');
	$allthreadtalbe[] = 'forum_thread';
	foreach($_G['cache']['threadtableids'] as $value){
		$allthreadtalbe[] = 'forum_thread_'.$value;
	}
	return $allthreadtalbe;
}


function do_datago($tableno,$do,$start,$limit){
	global $dbhost, $dbuser, $dbpw, $tablepre, $dbname, $todbcharset, $dbcharset,$operation,$toolslang;
	$whereis = '_pre';
	$allowcharset = array('latin1' => 'gbk','gbk' => 'utf8','utf8' => 'latin1');
	$tablename = 'Tables_in_'.strtolower($dbname).' ('.$tablepre.'%)';
	$mysql = mysql_connect($dbhost, $dbuser, $dbpw);
	mysql_select_db($fromdbname);
	mysql_query("SET sql_mode=''");
	$query = mysql_query('SHOW TABLES LIKE \''.$tablepre.'%\'');
	while($t = mysql_fetch_array($query,MYSQL_ASSOC)) {
		$tablearray[] = $t[$tablename];
	}

	$table = $tablearray["$tableno"];
	$query = mysql_query('SHOW TABLE STATUS LIKE '.'\''.$table.'\'');
	$tableinfo = array();
	
	while($t = mysql_fetch_array($query,MYSQL_ASSOC)) {
		$charset = explode('_',$t['Collation']);
		$t['Collation'] = $charset[0];
		$tableinfo = $t;
	}

	mysql_query("SET NAMES '$tableinfo[Collation]'");
	
	if($do == 'create') {
		$tablecreate=array();
		foreach ($tablearray as $key => $value){
			$query=mysql_query("SHOW CREATE TABLE $value");
			while($t = mysql_fetch_array($query,MYSQL_ASSOC)){
				$t['Create Table'] = str_replace($tablepre,$whereis.'_',$t['Create Table']);
				$t['Create Table'] = str_replace("$tableinfo[Collation]","$todbcharset",$t['Create Table']);
				$t['Create Table'] = str_replace($whereis.'_',$todbcharset.$whereis.'_',$t['Create Table']);
				$t['Table'] = str_replace($tablepre,$todbcharset.$whereis.'_',$t['Table']);
				$tablecreate[]=$t;
			}
		}
		mysql_query('SET NAMES \''.$todbcharset.'\'');
		if(mysql_get_server_info() > '5.0'){
			mysql_query("SET sql_mode=''");
		}
		foreach ($tablecreate as $key => $value){
			mysql_query("DROP TABLE IF EXISTS `$value[Table]`");
			mysql_query($value['Create Table']);
			$count++;	
		}
		cpmsg($toolslang['convert_count'],"action=exttools&operation=$operation&do=tools&cdo=data&convert_submit=yes",'succeed',array('count' => $count));

	} elseif($do == 'data') {
		if(strpos($tableinfo['Name'],$todbcharset) === 0) {
			$table = '';
		}
		$count = 0;
		$data = array();
		$newtable = str_replace($tablepre,$todbcharset.$whereis.'_',$table);
		if($table) {
			mysql_query("SET NAMES '$tableinfo[Collation]'");
			$query = mysql_query("SELECT * FROM $table LIMIT $start,$limit");
			
			while($t = mysql_fetch_array($query,MYSQL_ASSOC)) {
				$data[] = $t;	
			}			
			unset($t);			
			$todbcharset2 = $todbcharset;
			if($tableinfo['Collation'] == 'utf8' || $todbcharset=='utf8'){
				$todbcharset2 = $tableinfo['Collation'];
			}
			mysql_query('SET NAMES \''.$todbcharset2.'\'');
			if(mysql_get_server_info() > '5.0'){
				mysql_query("SET sql_mode=''");
			}
			if($start == 0){
				mysql_query("TRUNCATE TABLE $newtable");
			}

			foreach($data as $key => $value){
				$sql='';
				foreach($value as $tokey => $tovalue){
					$tovalue = addslashes($tovalue);
					$sql = $sql ? $sql.",'".$tovalue."'" : "'".$tovalue."'";
				}
				mysql_query("INSERT INTO $newtable VALUES($sql)") or mysql_errno();
				$count++;
			}
			if($count == $limit) {
				$start += $count;
				cpmsg($toolslang['convert_converting'],"action=exttools&operation=$operation&do=tools&cdo=data&tableno=$tableno&start=$start&convert_submit=yes",'loading',array('table' => $table,'start' => $start,'limit' => $limit));
			} else {
				$tableno ++;
				cpmsg($toolslang['convert_converting'],"action=exttools&operation=$operation&do=tools&cdo=data&tableno=$tableno&convert_submit=yes",'loading',array('table' => $table,'start' => $start,'limit' => $limit));
			}
		} else {
			//"tools.php?action=datago&do=serialize&fromdbname=$fromdbname&todbcharset=$todbcharset&submit=%D7%AA%BB%BB"
			cpmsg($toolslang['convert_serilise'],"action=exttools&operation=$operation&do=tools&cdo=serialize&convert_submit=yes",'succeed');
		}
		
	} elseif($do == 'serialize') {
		$arr = getlistarray('x','datago');
		$limit = '3000';
		
		foreach($arr as $field) {
			$stable = $todbcharset.$whereis.'_'.$field[0];
			$sfield = $field[1];
			$sid	= $field[2];
			$query = mysql_query("SELECT $sid,$sfield FROM $stable ORDER BY $sid DESC LIMIT $limit");
			while($values = mysql_fetch_array($query,MYSQL_ASSOC)) {
				$data = $values[$sfield];
				$id   = $values[$sid];
				$data = preg_replace_callback('/s:([0-9]+?):"([\s\S]*?)";/','_serialize',$data);
				$data = daddslashes($data);
				@mysql_query("update `$stable` set `$sfield`='$data' where `$sid`='$id'");
				//echo $toolstip;
			}
		}
		cpmsg($toolslang['convert_done'],NULL,'succeed',array('todbcharset' => $todbcharset));
	}
}

function getlistarray($whereis,$type) {
	if($whereis == 'x') {
		if($type == 'datago') {
			$list = array(
				array('common_advertisement','parameters','advid'),
				array('common_plugin','modules','pluginid'),
				array('common_setting','svalue','skey'),
				array('common_block','param','bid'),
				array('common_block_item','fields','itemid'),
				array('common_block_style','template','styleid'),
				array('common_diy_data','diycontent','targettplname'),
				array('common_member_field_forum','groups','uid'),
				array('common_member_stat_search','condition','optionid'),
				array('common_syscache','data','cname'),
				array('forum_grouplevel','creditspolicy','levelid'),
				array('forum_grouplevel','postpolicy','levelid'),
				array('forum_grouplevel','specialswitch','levelid'),
			);
		}
	}
	return $list;
}

function _serialize($str) {
	global $dbcharset,$todbcharset;
	$charset = $dbcharset == 'utf8' ? 'utf-8':$dbcharset;
	$tempdbcharset = $todbcharset == 'utf8' ? 'utf-8':$todbcharset;
	$charset = strtoupper($charset);
	$tempdbcharset = strtoupper($tempdbcharset);
	$temp = iconv($charset,$tempdbcharset,$str[2]);
	$l = strlen($temp);
	return 's:'.$l.':"'.$str[2].'";';
}

function get_avatar($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}


function fetchtablelist($tablepre = '') {
	global $db;
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

function sqldumptablestruct($table) {
	global $_G, $db;

	$createtable = DB::query("SHOW CREATE TABLE $table", 'SILENT');

	if(!DB::error()) {
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
	} else {
		return '';
	}

	$create = $db->fetch_row($createtable);

	if(strpos($table, '.') !== FALSE) {
		$tablename = substr($table, strpos($table, '.') + 1);
		$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
	}
	$tabledump .= $create[1];

	if($_G['gp_sqlcompat'] == 'MYSQL41' && $db->version() < '4.1') {
		$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
	}
	if($db->version() > '4.1' && $_G['gp_sqlcharset']) {
		$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$_G['gp_sqlcharset'], $tabledump);
	}

	$tablestatus = DB::fetch_first("SHOW TABLE STATUS LIKE '$table'");
	$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
	if($_G['gp_sqlcompat'] == 'MYSQL40' && $db->version() >= '4.1' && $db->version() < '5.1') {
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
	global $_G, $db, $startrow, $dumpcharset, $complete, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = DB::query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(strexists($table, 'adminsessions')) {
		return ;
	} elseif(!$query && DB::errno() == 1146) {
		return;
	} elseif(!$query) {
		$_G['gp_usehex'] = FALSE;
	} else {
		while($fieldrow = DB::fetch($query)) {
			$tablefields[] = $fieldrow;
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if($_G['gp_extendins'] == '0') {
			while($currsize + strlen($tabledump) + 500 < $_G['gp_sizelimit'] * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db->num_fields($rows);

				$numrows = DB::num_rows($rows);
				while($row = $db->fetch_row($rows)) {
					$comma = $t = '';
					for($i = 0; $i < $numfields; $i++) {
						$t .= $comma.($_G['gp_usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($t) + $currsize + strlen($tabledump) + 500 < $_G['gp_sizelimit'] * 1000) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($t);\n";
					} else {
						$complete = FALSE;
						break 2;
					}
				}
			}
		} else {
			while($currsize + strlen($tabledump) + 500 < $_G['gp_sizelimit'] * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db->num_fields($rows);

				if($numrows = DB::num_rows($rows)) {
					$t1 = $comma1 = '';
					while($row = $db->fetch_row($rows)) {
						$t2 = $comma2 = '';
						for($i = 0; $i < $numfields; $i++) {
							$t2 .= $comma2.($_G['gp_usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text'))? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma2 = ',';
						}
						if(strlen($t1) + $currsize + strlen($tabledump) + 500 < $_G['gp_sizelimit'] * 1000) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$t1 .= "$comma1 ($t2)";
							$comma1 = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $t1;\n";
							$complete = FALSE;
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

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

?>