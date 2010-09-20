<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: batch.javascript.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */

include_once('./common.php');

$mincachetime = 600;

if(empty($_GET['param'])) jsecho('JavaScript Error');
$param = $_GET['param'];

include_once('./data/system/config.cache.php');
$param = js_passport_decrypt($param, $_G['setting']['sitekey']);
$paramkey = md5($param);

$timestamp = time();

$paramarr = array();
$params = explode('/', $param);
for ($i=0; $i<count($params)-1; $i=$i+2) {
	$j = $i + 1;
	if(!isset($params[$j])) jsecho('JavaScript Error');
	$paramarr[$params[$i]] = $params[$j];
}
if(empty($paramarr['blocktype']) || empty($paramarr['tpl'])) jsecho('JavaScript Error');
if(!empty($paramarr['cachetime']) && intval($paramarr['cachetime']) > $mincachetime) {
	$paramarr['cachetime'] = intval($paramarr['cachetime']);
} else {
	$paramarr['cachetime'] = $mincachetime;
}

$blocktype = $paramarr['blocktype'];
unset($paramarr['blocktype']);

$param = $mod = '';
foreach($paramarr as $key => $value) {
	$param .= $mod.$key.'/'.$value;
	$mod = '/';
}

if(!is_dir($cachedir = './data/cache/js')) @mkdir($cachedir);
$cachefile = $cachedir.'/'.$paramkey.'.html';
if(file_exists($cachefile) && $timestamp - filemtime($cachefile) < $paramarr['cachetime']) {
	$jsmessage = '';
	if(@$fp = fopen($cachefile, 'r')) {
		$jsmessage = fread($fp, filesize($cachefile));
		fclose($fp);
	}
	jsecho($jsmessage);
} else {
	block($blocktype, $param);
	ob_out();
	obclean();
	$jsmessage = $_SGLOBAL['content'];
	if(!empty($jsmessage)) writefile($cachefile, $jsmessage, 'text', 'w', 0);
	jsecho($jsmessage);
}

function jsecho($message, $exit=1) {
	$jsmessage = '';
	$message = str_replace(">\r", '>', $message);
	$message = str_replace(">\n", '>', $message);
	preg_match("/\<script\>(.+?)\<\/script\>/is", $message, $mathes);
	if(!empty($mathes[1])) {
		$jsmessage = str_replace(array('<!--', '//-->'), '', $mathes[1]);
		$message = preg_replace("/\<script\>(.+?)\<\/script\>/is", '', $message);
	}
	$message = preg_replace("/(\r|\n)/s", '', $message);
	echo 'document.write(\''.addcslashes($message, '\'\\').'\');';
	echo $jsmessage;
	if($exit) exit;
}

function js_passport_decrypt($txt, $key) {
	$txt = js_passport_key(base64_decode(rawurldecode($txt)), $key);
	$tmp = '';
	for ($i = 0; $i < strlen($txt); $i++) {
		@$tmp .= $txt[$i] ^ $txt[++$i];
	}
	return $tmp;
}

function js_passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}

?>