<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function debugmessage() {
	global $_G;
	if(!defined('DISCUZ_DEBUG') || !DISCUZ_DEBUG) {
		return;
	}

	$debug = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head>';
	$debug .= "<script src='../static/js/common.js'></script>";

	if(!defined('IN_ADMINCP') && file_exists(DISCUZ_ROOT.'./static/image/common/temp-grid.png')) $debug .= <<<EOF
<script type="text/javascript">
var s = '<button style="position: fixed; width: 40px; right: 0; top: 30px; border: none; border:1px solid orange;background: yellow; color: red; cursor: pointer;" onclick="var pageHight = top.document.body.clientHeight;$(\'tempgrid\').style.height = pageHight + \'px\';$(\'tempgrid\').style.visibility = top.$(\'tempgrid\').style.visibility == \'hidden\'?\'\':\'hidden\';o.innerHTML = o.innerHTML == \'厙跡\'?\'壽敕\':\'厙跡\';">厙跡</button>';
s += '<div id="tempgrid" style="position: absolute; top: 0px; left: 50%; margin-left: -500px; width: 1000px; height: 0; background: url(static/image/common/temp-grid.png); visibility :hidden;"></div>';
top.$('_debug_div').innerHTML = s;
</script>
EOF;

	$_GS = $_GA = '';
	if($_G['adminid'] == 1) {
		foreach($_G as $k => $v) {
			if(is_array($v) && $k != 'lang') {
				$_GA .= "<li><a name=\"S_$k\"></a><br />['$k'] => ".nl2br(str_replace(' ','&nbsp;', htmlspecialchars(print_r($v, true)))).'</li>';
			} elseif(is_object($v)) {
				$_GA .= "<li><br />['$k'] => <i>object of ".get_class($v)."</i></li>";
			} else {
				$_GS .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
			}
		}
	}
	$modid = $_G['basescript'].(!defined('IN_ADMINCP') ? '::'.CURMODULE : '');
	$db = & DB::object();
	$queries = count($db->sqldebug);
	$debug .= '
		<style>#__debugbarwrap__ { line-height:10px; text-align:left;font:12px Monaco,Consolas,"Lucida Console","Courier New",serif;}
		body { font-size:12px; }
		a, a:hover { color: black;text-decoration:none; }
		#__debugbar__ { padding: 10px; }
		#__debugbar__ table { width:90%;border:1px solid gray; }
		#__debugbar_s { position: fixed; top:0px; left:0px; }
		#__debugbar_s a.a { border-bottom: 1px dotted gray; }
		#__debug_c_1 ol { margin-left: 20px; padding: 0px; }
		#__debug_c_4_nav { background:#FFF; border:1px solid black; border-top:none; padding:5px; position: fixed; top:0px; right:0px }
		</style></head><body>'.
		'<div id="__debugbarwrap__">'.
		'<div id="__debugbar_s">DEBUG MESSAGE ::&nbsp;&nbsp;'.
		'<a id="__debug_1" href="javascript:;" onclick="parent.$(\'_debug_iframe\').height=\'500px\';switchTab(\'__debug\', 1, 4)">[<b>QUERY:</b> '.$queries.']</a>&nbsp;'.
		'<a id="__debug_2" href="javascript:;" onclick="parent.$(\'_debug_iframe\').height=\'500px\';switchTab(\'__debug\', 2, 4)">[<b>EVENT:</b> php:'.PHP_VERSION.' <span id="__debug_b"></span>]</a>&nbsp;'.
		'<a id="__debug_3" href="javascript:;" onclick="parent.$(\'_debug_iframe\').height=\'500px\';switchTab(\'__debug\', 3, 4)">[<b>INCLUDE:</b> '.$modid.']</a>&nbsp;'.
		'<a id="__debug_4" href="javascript:;" onclick="parent.$(\'_debug_iframe\').height=\'500px\';switchTab(\'__debug\', 4, 4)">[<b>$_G</b>]</a>&nbsp;'.
		($_G['adminid'] == 1 ? '&nbsp;&nbsp;<a href="../misc.php?mod=initsys" target="_debug_initframe">[<b>Update Cache</b>]</a>' : '').
		'</div>'.
		'<div id="__debugbar__" style="clear:both">'.
		'<div id="__debug_c_1" style="display:none">'.$queries.' queries'.'<ol>';
	foreach ($db->sqldebug as $string) {
		$debug .= '<li><br />'.$string[1].'s &bull; '.htmlspecialchars($string[0]).'<br /></li>';
		if(preg_match('/^SELECT /', $string[0])) {
			$query = DB::query("EXPLAIN ".$string[0]);
			$i = 0;
			$debug .= '<table style="border-bottom:none">';
			while($row = DB::fetch($query)) {
				if(!$i) {
					$debug .= '<tr style="border-bottom:1px dotted gray"><th>&nbsp;'.implode('&nbsp;</th><th>&nbsp;', array_keys($row)).'&nbsp;</th></tr>';
					$i++;
				}
				$debug .= '<tr><th>&nbsp;'.implode('&nbsp;</th><th>&nbsp;', $row).'&nbsp;</th></tr>';
			}
			$debug .= '</table>';
		}
		$debug .= '<table><tr style="border-bottom:1px dotted gray"><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
		foreach($string[2] as $error) {
			$error['file'] = str_replace(DISCUZ_ROOT, '', $error['file']);
			$error['class'] = isset($error['class']) ? $error['class'] : '';
			$error['type'] = isset($error['type']) ? $error['type'] : '';
			$error['function'] = isset($error['function']) ? $error['function'] : '';
			$debug .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
		}
		$debug .= '</table>';
	}
	$debug .= '</ol></div><div id="__debug_c_2" style="display:none">'.PHP_OS.' &bull; PHP '.PHP_VERSION.'<br />'.$_G['clientip'].' &bull; '.$_SERVER['HTTP_USER_AGENT'].'<br /><script>for(BROWSERi in BROWSER) {var __s=BROWSERi+\':\'+BROWSER[BROWSERi]+\' \';$(\'__debug_b\').innerHTML+=BROWSER[BROWSERi]!==0?__s:\'\';document.write(__s);}</script></div>'.
		'<div id="__debug_c_3" style="display:none">ModID: <b>'.$modid.'</b><ol>';
	foreach (get_included_files() as $fn) {
		$debug .= '<li>'.$fn.'</li>';
	}	
	$debug .= '</ol></div><div id="__debug_c_4" style="display:none">'.
		'<div id="__debug_c_4_nav"><a href="#S_config">Nav:<br />
			<a href="#top">#top</a></a><br />
			<a href="#S_config">$_G[\'config\']</a><br />
			<a href="#S_setting">$_G[\'setting\']</a><br />
			<a href="#S_member">$_G[\'member\']</a><br />
			<a href="#S_group">$_G[\'group\']</a><br />
			<a href="#S_cookie">$_G[\'cookie\']</a><br />
			<a href="#S_style">$_G[\'style\']</a><br />
			<a href="#S_cache">$_G[\'cache\']</a><br />
			</div>'.
		'<ol><a name="top"></a>'.$_GS.$_GA.'</ol></div></body></html><? @unlink(\'_debug.php\');?>';
	$fn = 'data/_debug.php';
	file_put_contents(DISCUZ_ROOT.'./'.$fn, $debug);
	echo '<iframe src="'.$fn.'" name="_debug_iframe" id="_debug_iframe" style="border-top:1px solid gray;overflow-x:hidden;overflow-y:auto" width="100%" height="40" frameborder="0"></iframe><div id="_debug_div"></div><iframe name="_debug_initframe" style="display:none" onload="if(this.contentWindow.document.body.innerHTML) location.href=location.href"></iframe></script>';
}

?>