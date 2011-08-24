<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!empty($_GET['debugaction'])) {
	if($_GET['debugaction'] == 'connect' && !empty($_GET['redirect_url'])) {

		chdir('../../');
		require './source/class/class_core.php';
		$discuz = & discuz_core::instance();
		$discuz->init();

		$uin = md5($_GET['qq']);

		$get = array(
			'con_expires_in' => '3600',
			'con_access_token' => '501|1290144222.3600|48d855c11ecb426c9838|LskyfOZ0moKUfEMINrYmuTaZmlP-xavvfeIM7Ylwi3c.',
			'con_uin' => $uin,
			'con_is_unbind' => '1',
			'con_x_nick' => '',
			'con_x_sex' => 'unknown',
			'con_x_birthday' => '1977-01-01',
			'con_x_email' => '1@22.net',
			'con_x_usernames' => base64_encode('User1,User2'),
		);
		ksort($get);
		$str = '';
		foreach($get as $k => $v) {
			if($v) {
				$str .= $k.'='.$v.'&';
			}
		}

		$get['con_sig'] = md5($str.$_G['setting']['connectsitekey']);
		header('location: '.$_GET['redirect_url'].'&'.http_build_query($get));
	}
	exit;
}

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function debugmessage($ajax = 0) {
	$m = function_exists('memory_get_usage') ? number_format(memory_get_usage()) : '';
	$mt = function_exists('memory_get_peak_usage') ? number_format(memory_get_peak_usage()) : '';
	if($m) {
		$m = 'Memory usage <s>'.$m.'</s> bytes'.($mt ? ', peak <s>'.$mt.'</s> bytes' : '').' / ';
	}
	global $_G;
	$debugfile = $_G['adminid'] == 1 ? '_debugadmin.php' : '_debug.php';
	$akey = md5(random(10));
	if(!defined('DISCUZ_DEBUG') || !DISCUZ_DEBUG || defined('IN_ARCHIVER') || defined('IN_MOBILE')) {
		return;
	}
	$phpinfok = 'I';
	$viewcachek = 'C';
	$mysqlplek = 'P';
	$includes = get_included_files();
	require_once DISCUZ_ROOT.'./source/discuz_version.php';

	$sqldebug = '';
	$sqlw = array();
	$db = & DB::object();
	$queries = count($db->sqldebug);
	foreach ($db->sqldebug as $string) {
		$sqldebug .= '<li><br />'.$string[1].'s &bull; '.nl2br(htmlspecialchars($string[0])).'<br /></li>';
		if(preg_match('/^SELECT /', $string[0])) {
			$query = DB::query("EXPLAIN ".$string[0]);
			$i = 0;
			$sqldebug .= '<table style="border-bottom:none">';
			while($row = DB::fetch($query)) {
				if(!$i) {
					$sqldebug .= '<tr style="border-bottom:1px dotted gray"><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', array_keys($row)).'&nbsp;</td></tr>';
					$i++;
				}
				if(strexists($row['Extra'], 'Using filesort')) {
					$sqlw['Using filesort']++;
					$row['Extra'] = str_replace('Using filesort', '<font color=red>Using filesort</font>', $row['Extra']);
				}
				if(strexists($row['Extra'], 'Using temporary')) {
					$sqlw['Using temporary']++;
					$row['Extra'] = str_replace('Using temporary', '<font color=red>Using temporary</font>', $row['Extra']);
				}
				$sqldebug .= '<tr><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', $row).'&nbsp;</td></tr>';
			}
			$sqldebug .= '</table>';
		}
		$sqldebug .= '<table><tr style="border-bottom:1px dotted gray"><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
		foreach($string[2] as $error) {
			$error['file'] = str_replace(DISCUZ_ROOT, '', $error['file']);
			$error['class'] = isset($error['class']) ? $error['class'] : '';
			$error['type'] = isset($error['type']) ? $error['type'] : '';
			$error['function'] = isset($error['function']) ? $error['function'] : '';
			$sqldebug .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
		}
		$sqldebug .= '</table>';
	}
	$ajaxhtml = 'data/'.$debugfile.'_ajax.php';
	if($ajax) {
		$idk = substr(md5($_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']), 0, 4);
		$sqldebug = '<b style="cursor:pointer" onclick="document.getElementById(\''.$idk.'\').style.display=document.getElementById(\''.$idk.'\').style.display == \'\' ? \'none\' : \'\'">Queries: </b> '.$queries.' ('.$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].')<ol id="'.$idk.'" style="display:none">'.$sqldebug.'</ol><br>';
		file_put_contents(DISCUZ_ROOT.'./'.$ajaxhtml, $sqldebug, FILE_APPEND);
		return;
	}
	file_put_contents(DISCUZ_ROOT.'./'.$ajaxhtml, '<?php if(empty($_GET[\'k\']) || $_GET[\'k\'] != \''.$akey.'\') { exit; } ?><style>body,table { font-size:12px; }table { width:90%;border:1px solid gray; }</style><a href="javascript:;" onclick="location.href=location.href">Refresh</a><br />');
	foreach($sqlw as $k => $v) {
		$sqlw[$k] = $k.': '.$v;
	}
	$sqlw = $sqlw ? '<s>('.implode(', ', $sqlw).')</s>' : '';

	$debug = '<?php if(empty($_GET[\'k\']) || $_GET[\'k\'] != \''.$akey.'\') { exit; } ?>';
	if($_G['adminid'] == 1 && !$ajax) {
		$debug .= '<?php
if(isset($_GET[\''.$phpinfok.'\'])) { phpinfo(); exit; }
elseif(isset($_GET[\''.$viewcachek.'\'])) {
	chdir(\'../\');
	require \'./source/class/class_core.php\';
	$discuz = & discuz_core::instance();
	$discuz->init();
	echo \'<style>body { font-size:12px; }</style>\';
	if(!isset($_GET[\'c\'])) {
		$query = DB::query("SELECT cname FROM ".DB::table("common_syscache"));
		while($names = DB::fetch($query)) {
			echo \'<a href="'.$debugfile.'?k='.$akey.'&'.$viewcachek.'&c=\'.$names[\'cname\'].\'" target="_blank" style="float:left;width:200px">\'.$names[\'cname\'].\'</a>\';
		}
	} else {
		loadcache($_GET[\'c\']);
		echo \'$_G[\\\'cache\\\'][\'.$_GET[\'c\'].\']<br>\';
		debug($_G[\'cache\'][$_GET[\'c\']]);
	}
	exit;
}
elseif(isset($_GET[\''.$mysqlplek.'\'])) {
	chdir(\'../\');
	require \'./source/class/class_core.php\';
	$discuz = & discuz_core::instance();
	$discuz->_init_db();
	if(!empty($_GET[\'Id\'])) {
		$query = DB::query("KILL ".floatval($_GET[\'Id\']), \'SILENT\');
	}
	$query = DB::query("SHOW FULL PROCESSLIST");
	echo \'<style>table { font-size:12px; }</style>\';
	echo \'<table style="border-bottom:none">\';
	while($row = DB::fetch($query)) {
		if(!$i) {
			echo \'<tr style="border-bottom:1px dotted gray"><td>&nbsp;</td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', array_keys($row)).\'&nbsp;</td></tr>\';
			$i++;
		}
		echo \'<tr><td><a href="'.$debugfile.'?k='.$akey.'&P&Id=\'.$row[\'Id\'].\'">[Kill]</a></td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', $row).\'&nbsp;</td></tr>\';
	}
	echo \'</table>\';
	exit;
}
		?>';
	}

	$debug .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head>';
	$debug .= "<base href=\"$_G[siteurl]\" />";

	$debug .= '<script type="text/javascript" src="http://code.jquery.com/jquery-latest.pack.js?oDr"></script>';
	$debug .= '<script type="text/javascript">jQuery.noConflict();</script>';

	/**
	 * JSPATH is not defined
	 *
	 * [在這個錯誤中斷] src = JSPATH + script + '.js?' + VERHASH;
	 * common.js（527 行）
	 **/
	$debug .= "<script type=\"text/javascript\">var STYLEID = '".STYLEID."', STATICURL = '".STATICURL."', IMGDIR = '".IMGDIR."', VERHASH = '".VERHASH."', charset = '".CHARSET."', discuz_uid = '$_G[uid]', cookiepre = '{$_G[config][cookie][cookiepre]}', cookiedomain = '{$_G[config][cookie][cookiedomain]}', cookiepath = '{$_G[config][cookie][cookiepath]}', showusercard = '{$_G[setting][showusercard]}', attackevasive = '{$_G[config][security][attackevasive]}', disallowfloat = '{$_G[setting][disallowfloat]}', creditnotice = '";
	if (0 && $_G['setting']['creditnotice']) {
		$debug .= $_G['setting']['creditnames'];
	}
	$debug .= "', defaultstyle = '{$_G[style][defaultextstyle]}', REPORTURL = '$_G[currenturl_encode]', SITEURL = '$_G[siteurl]', JSPATH = '{$_G[setting][jspath]}';</script>";

	if ($_G['setting']['jspath'] == 'data/cache/' && file_exists('data/cache/common.js'.VERHASH_GZIP_JS)) {
		$debug .= "<script src='data/cache/common.js".VERHASH_GZIP_JS."?".VERHASH."'></script>";
	} else {
		$debug .= "<script src='static/js/common.js?".VERHASH."'></script>";
	}

	$debug .= "<script>
	function switchTab(prefix, current, total, activeclass) {
		activeclass = !activeclass ? 'a' : activeclass;
		jQuery('[id^=\"' + prefix + '_\"]')
			.removeClass(activeclass)
			.filter('#' + prefix + '_' + current)
			.addClass(activeclass)
		;
		jQuery('[id^=\"' + prefix + '_c_\"]')
			.hide()
			.filter('[id^=\"' + prefix + '_c_' + current +'\"]')
			.show()
		;
	}

	jQuery(function(){
		jQuery('a[id^=\"__debug_\"], #__debug_c_6_nav a').click(function(e){
			e.preventDefault();
			var _this = jQuery(this);
			var _id = _this.attr('href').replace('#', '');
			_id = jQuery('#' + _id + ', [name=\"' + _id + '\"]').first();
			if (_id.size()) {
				jQuery(window).scrollTop(_id.offset().top);
			}
		});
	});
	</script>";

	if(!defined('IN_ADMINCP') && file_exists(DISCUZ_ROOT.'./static/image/common/temp-grid.png')) $debug .= <<<EOF
<script type="text/javascript">
var s = '<button style="position: fixed; width: 40px; right: 0; top: 30px; border: none; border:1px solid orange;background: yellow; color: red; cursor: pointer;" onclick="var pageHight = top.document.body.clientHeight;$(\'tempgrid\').style.height = pageHight + \'px\';$(\'tempgrid\').style.visibility = top.$(\'tempgrid\').style.visibility == \'hidden\'?\'\':\'hidden\';o.innerHTML = o.innerHTML == \'網格\'?\'關閉\':\'網格\';">網格</button>';
s += '<div id="tempgrid" style="position: absolute; top: 0px; left: 50%; margin-left: -500px; width: 1000px; height: 0; background: url(static/image/common/temp-grid.png); visibility :hidden;"></div>';
top.$('_debug_div').innerHTML = s;
</script>
EOF;

	$_GS = $_GA = '';
	if($_G['adminid'] == 1) {
		foreach($_G as $k => $v) {
			if(is_array($v)) {
				if($k != 'lang') {
					$_GA .= "<li><a name=\"S_$k\"></a><br />['$k'] => ".nl2br(str_replace(' ','&nbsp;', htmlspecialchars(print_r($v, true)))).'</li>';
				}
			} elseif(is_object($v)) {
				$_GA .= "<li><br />['$k'] => <i>object of ".get_class($v)."</i></li>";
			} else {
				$_GS .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
			}
		}
	}
	$modid = $_G['basescript'].(!defined('IN_ADMINCP') ? '::'.CURMODULE : '');
	$svn = '';
	if(file_exists(DISCUZ_ROOT.'./.svn/entries')) {
		$svn = @file(DISCUZ_ROOT.'./.svn/entries');
		$time = $svn[9];
		preg_match('/([\d\-]+)T([\d:]+)/', $time, $a);
		$svn = ' / SVN '.$svn[10].' by '.$svn[11].' on '.dgmdate(strtotime($a[1].' '.$a[2]) + $_G['setting']['timeoffset'] * 3600);
	}
	$max = $_G['adminid'] == 1 ? 6 : 5;
	$debug .= '
		<style>#__debugbarwrap__ { line-height:10px; text-align:left;font:12px Monaco,Consolas,"Lucida Console","Courier New",serif;}
		body { font-size:12px; }
		a, a:hover { color: black;text-decoration:none; }
		s { text-decoration:none;color: red; }
		img { vertical-align:middle; }
		#__debugbar__ { padding: 80px 1px 0 1px;  }
		#__debugbar__ table { width:90%;border:1px solid gray; }
		#__debugbar_s { border-bottom:1px dotted #EFEFEF;background:#FFF;width:100%;font-size:11px;position: fixed; top:0px; left:5px; }
		#__debugbar_s a { color:blue; }
		#__debugbar_s a.a { border-bottom: 1px dotted gray; }
		#__debug_c_1 ol { margin-left: 20px; padding: 0px; }
		#__debug_c_6_nav { background:#FFF; border:1px solid black; border-top:none; padding:5px; position: fixed; top:0px; right:0px }
		</style></head><body>'.
		'<div id="__debugbarwrap__">'.
		'<div id="__debugbar_s">Discuz! '.DISCUZ_VERSION.' '.DISCUZ_RELEASE.''.$svn.' / php:'.PHP_VERSION.' <span id="__debug_b"></span><br />'.
		$m.'Queries <s>'.$queries.$sqlw.($_G['debuginfo']['time'] ? ' in '.$_G['debuginfo']['time'].'s' : '').'</s> / Include files <s>'.(count($includes) - 1).'</s> / ModID <s>'.$modid.'</s>'.
		'<br /><a name="debugbar" href="javascript:;" onclick="parent.scrollTo(0,0)" style="float:right">[TOP]&nbsp;&nbsp;&nbsp;</a>'.
		'<img src="static/image/common/arw_r.gif" /><a id="__debug_1" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'800px\';switchTab(\'__debug\', 1, '.$max.')">Queries</a>'.
		'<img src="static/image/common/arw_r.gif" /><a id="__debug_4" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'800px\';switchTab(\'__debug\', 4, '.$max.');sqldebug_ajax.location.href = sqldebug_ajax.location.href;">Ajax Queries</a>'.
		'<img src="static/image/common/arw_r.gif" /><a id="__debug_2" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'300px\';switchTab(\'__debug\', 2, '.$max.')">Envionment</a>'.
		'<img src="static/image/common/arw_r.gif" /><a id="__debug_3" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'500px\';switchTab(\'__debug\', 3, '.$max.')">Include Files</a>'.
		'<img src="static/image/common/arw_r.gif" /><a id="__debug_5" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'300px\';switchTab(\'__debug\', 5, '.$max.')">$_COOKIE</a>'.
		($_G['adminid'] == 1 ? '<img src="../static/image/common/arw_r.gif" /><a id="__debug_6" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'1000px\';switchTab(\'__debug\', 6, 6)">$_G</a>' : '').
		($_G['adminid'] == 1 ?
			'<img src="../static/image/common/arw_r.gif" /><a href="data/'.$debugfile.'?k='.$akey.'&'.$phpinfok.'" target="_blank">phpinfo()</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="data/'.$debugfile.'?k='.$akey.'&'.$mysqlplek.'" target="_blank">MySQL Processlist</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="data/'.$debugfile.'?k='.$akey.'&'.$viewcachek.'" target="_blank">View Cache</a>'.
			'<img src="../static/image/common/arw_r.gif" /><a href="misc.php?mod=initsys" target="_debug_initframe" onclick="parent.$(\'_debug_initframe\').onload = function () {parent.location.href=parent.location.href;}">Update Cache</a>' : '').
		'&nbsp;&nbsp;|&nbsp;&nbsp;'.
		'<img src="../static/image/common/arw_r.gif" /><a href="install/update.php" target="_blank">update.php</a>'.
		($_G['setting']['connect']['allow'] ? '<img src="static/image/common/arw_r.gif" /><a onclick="var _q_ = prompt(\'QQ Number\');if(_q_) {window.open(this.href + \'&qq=\' + _q_);}return false;" href="source/function/function_debug.php?debugaction=connect&redirect_url='.rawurlencode($_G['connect']['redirect_url']).'" target="_top">QQ Login</a>' : '').
		'</div>'.
		'<div id="__debugbar__" style="clear:both">'.
		'<div id="__debug_c_1" style="display:none"><b>Queries: </b> '.$queries.'<ol>';
	$debug .= $sqldebug.'';
	$debug .= '</ol></div>'.
		'<div id="__debug_c_4" style="display:none"><iframe id="sqldebug_ajax" name="sqldebug_ajax" src="'.$ajaxhtml.'?k='.$akey.'" frameborder="0" width="100%" height="800"></iframe></div>'.
		'<div id="__debug_c_2" style="display:none"><b>SERVER: </b>'.PHP_OS.', '.$_SERVER['SERVER_SOFTWARE'].', '.php_sapi_name().'<br /><b>PHP: </b>'.PHP_VERSION.'<br /><b>MySQL: </b>'.DB::result_first("SELECT VERSION()").'<br /><b>IP: </b>'.$_G['clientip'].'<br /><b>UA: </b>'.$_SERVER['HTTP_USER_AGENT'].'<br /><b>BROWSER.x: </b><script>for(BROWSERi in BROWSER) {var __s=BROWSERi+\':\'+BROWSER[BROWSERi]+\' \';$(\'__debug_b\').innerHTML+=BROWSER[BROWSERi]!==0?__s:\'\';document.write(__s);}</script></div>'.
		'<div id="__debug_c_3" style="display:none"><ol>';

	$__func = create_function('$fn, $base', '
		$base = str_replace(array(\'\\\\\', \'//\'), \'/\', $base);
		$fn = str_replace(array(\'\\\\\', \'//\'), \'/\', $fn);

		if (stripos($fn, $base) === 0) return substr($fn, strlen($base));

		return $fn;
	');

	foreach (get_included_files() as $fn) {

		// bluelovers
		if (class_exists('scofile')) {
			$fn = scofile::remove_root($fn, DISCUZ_ROOT);
		} else {
			$fn = $__func($fn, DISCUZ_ROOT);
		}
		// bluelovers

		$debug .= '<li>'.$fn.'</li>';
	}
	$debug .= '<ol></div><div id="__debug_c_5" style="display:none"><ol>';
	foreach($_COOKIE as $k => $v) {
		if(strexists($k, $_G['config']['cookie']['cookiepre'])) {
			$k = '<font color=blue>'.$k.'</font>';
		}
		$debug .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
	}
	$debug .= '</ol></div><div id="__debug_c_6" style="display:none">'.
		'<div id="__debug_c_6_nav"><a href="#S_config">Nav:<br />
			<a href="#top">#top</a><br />
			<a href="#S_config">$_G[\'config\']</a><br />
			<a href="#S_setting">$_G[\'setting\']</a><br />
			<a href="#S_member">$_G[\'member\']</a><br />
			<a href="#S_group">$_G[\'group\']</a><br />
			<a href="#S_cookie">$_G[\'cookie\']</a><br />
			<a href="#S_style">$_G[\'style\']</a><br />
			<a href="#S_cache">$_G[\'cache\']</a><br />
			</div>'.
		'<ol><a name="top"></a>'.$_GS.$_GA.'</ol></div></body></html>';
	$fn = 'data/'.$debugfile;
	file_put_contents(DISCUZ_ROOT.'./'.$fn, $debug);
	echo '<iframe src="'.$fn.'?k='.$akey.'" name="_debug_iframe" id="_debug_iframe" style="border-top:1px solid gray;overflow-x:hidden;overflow-y:auto" width="100%" height="70" frameborder="0"></iframe><div id="_debug_div"></div><iframe name="_debug_initframe" id="_debug_initframe" style="display:none"></iframe>';
}

?>