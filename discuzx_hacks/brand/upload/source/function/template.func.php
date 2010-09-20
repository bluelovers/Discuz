<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: template.func.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

/**
 * 讀模板頁進行替換後寫入到cache頁裡
 *
 * @param string $tplfile ：模板文件名
 * @param string $objfile ：cache文件名
 * @return
 */
function parse_template($tplfile, $objfile, $template='') {
	global $_G;

	//read
	if(empty($template)) {
		if(!@$fp = fopen($tplfile, 'r')) {
			exit('Template file :<br>'.srealpath($tplfile).'<br>Not found or have no access!');
		}
		$template = fread($fp, filesize($tplfile));
		fclose($fp);
		$template = str_replace('<?exit?>', '', $template);
	}

	//parse
	$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
	$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

	$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
	$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
	$template = preg_replace("/\{lang\s+(.+?)\}/ies", "languagevar('\\1')", $template);
	$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

	$template = preg_replace("/(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/s", "\\1['\\2']", $template);
	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
	$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);

	$template = preg_replace("/[\n\r\t]*\{block\s+name=\"(.+?)\"\s+parameter=\"(.+?)\"\}[\n\r\t]*/ies", "blocktags('\\1', '\\2')", $template);
	$template = preg_replace("/[\n\r\t]*\#date\((.+?)\)\#[\n\r\t]*/ies", "striptagquotes('<?php sdate(\\1); ?>')", $template);
	$template = preg_replace("/[\n\r\t]*\#getad\((.+?)\)\#[\n\r\t]*/ies", "striptagquotes('<?php echo getad(\\1); ?>')", $template);
	$template = preg_replace("/[\n\r\t]*\#(uid|action)(.+?)\#[\n\r\t]*/ies", "striptagquotes('<?php echo geturl(\"\\1\\2\"); ?>')", $template);

	$template = ltrim($template);
	$template = "<?php if(!defined('IN_BRAND')) exit('Access Denied'); ?>$template";
	$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<?php include template('\\1'); ?>\n", $template);
	$template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", "\n<?php include template(\\1); ?>\n", $template);
	$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<?php \\1; ?>','')", $template);
	$template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('\n<?php echo \\1; ?>\n','')", $template);
	$template = preg_replace("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('\n<?php } elseif(\\1) { ?>\n','')", $template);
	$template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "\n<?php } else { ?>\n", $template);

	for($i = 0; $i < 5; $i++) {
		$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\n\\3\n<?php } } ?>\n')", $template);
		$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\n\\4\n<?php } } ?>\n')", $template);
		$template = preg_replace("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/ies", "stripvtags('\n<?php if(\\1) { ?>','\n\\2\n<?php } ?>\n')", $template);
	}
	$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

	//write
	$template = trim($template);
	if(!empty($template)) {
		$needwrite = false;
		if(@unlink($objfile)) {
			writefile($objfile.'.tmp', $template, 'text', 'w', 0);
			if(@rename($objfile.'.tmp', $objfile)) {
				$needwrite = false;
			} else {
				$needwrite = true;
			}
		} else {
			$needwrite = true;
		}
		//再次寫入
		if($needwrite) writefile($objfile, $template, 'text', 'w', 0);
	}
}

/**
 * 正則表達式匹配替換
 *
 * @param string $var ：
 * @return
 */
function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

/**
 * 正則表達式匹配替換
 *
 * @param string $expr ：
 * @return
 */
function striptagquotes($expr) {
	$expr = preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr);
	$expr = str_replace("\\\"", "\"", preg_replace("/\[\'([a-zA-Z0-9_\-\.\x7f-\xff]+)\'\]/s", "[\\1]", $expr));
	return $expr;
}

/**
 * ?
 *
 * @param string $var ：
 * @return
 */
function languagevar($var) {
	global $_G, $lang;
	if(isset($lang[$var])) {
		return $lang[$var];
	} else {
		return "!$var!";
	}
}

/**
 * 將模板中的塊替換成BLOCK函數
 *
 * @param string $cachekey ：
 * @param string $parameter ：
 * @return
 */
function blocktags($cachekey, $parameter) {
	return striptagquotes("<?php block(\"$cachekey\", \"$parameter\"); ?>");
}

/**
 * 正則表達式匹配替換
 *
 * @param string $expr ：
 * @param string $statement ：
 * @return
 */
function stripvtags($expr, $statement='') {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}

?>