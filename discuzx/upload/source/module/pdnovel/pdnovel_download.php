<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!checkperm('pdnoveldown')) {
	showmessage($lang['download_no_per'], NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$novelid = $_G['gp_novelid'];
$novel = DB::fetch_first("SELECT name,author,lastupdate FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0 LIMIT 1");
if(!$novel){
	showmessage($lang['novel_error']);
}

$chapterpath = 'data/attachment/pdnovel/chapter/';
$txtpath = 'data/attachment/pdnovel/txt/';

$file = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_download')." WHERE novelid=$novelid ");
$size = $file['size'] ? $file['size'] : 0;
$name = $file['name'] ? $file['name'] : $novel['name'].'.txt';
$path = $file['path'] ? $txtpath.$file['path'] : '';

if(!$file){
	$query = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid");
	$contents = "《".$novel[name]."》作者:".$novel[author]."\r\n\r\n";
	while($chapter = DB::fetch($query)){
		$chapter['lastupdate'] = strftime('%Y-%m-%d %X',$chapter['lastupdate']);
		$content = file_get_contents($chapterpath.$chapter['chaptercontent']);
		$content = str_replace("document.write('","",$content);
		$content = str_replace("');","",$content);
		$content = str_replace("<br>","\r\n",$content);
		$content = $novel['name']." ".$chapter['chaptername']."\r\n".$lang['chapter_updatetime'].$chapter['lastupdate']." ".$lang['chapter_words'].$chapter['chapterwords']."\r\n\r\n".$content."\r\n\r\n";
		$contents .= $content;
	}
	$subnovelid = floor($novelid/1000);
	if (!file_exists($txtpath.$subnovelid)){
		@mkdir($txtpath.$subnovelid);
	}
	$txt = $subnovelid.'/'.$novelid.'-'.rand(100,999).'.txt';
	@file_put_contents($txtpath.$txt, $contents);
	$size = filesize($txtpath.$txt);
	$price = ceil($size/1048576);
	$setarr = array(
		'novelid' => $novelid,
		'dateline' => $novel['lastupdate'],
		'price' => $price,
		'name' => $name,
		'type' => 'txt',
		'size' => $size,
		'path' => $txt,
		'downloads' => 1,
		'uid' => 1
	);
	DB::insert('pdnovel_download', $setarr);
	
}elseif($file['dateline']!=$novel['lastupdate']){
	$query = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid AND lastupdate>$file[dateline]");
	$contents = "";
	while($chapter = DB::fetch($query)){
		$chapter['lastupdate'] = strftime('%Y-%m-%d %X',$chapter['lastupdate']);
		$content = @file_get_contents($chapter['chaptercontent']);
		$content = str_replace("document.write('","",$content);
		$content = str_replace("');","",$content);
		$content = str_replace("<br>","\r\n",$content);
		$content = $novel['name']." ".$chapter['chaptername']."\r\n".$lang['chapter_updatetime'].$chapter['lastupdate']." ".$lang['chapter_words'].$chapter['chapterwords']."\r\n\r\n".$content."\r\n\r\n";
		$contents .= $content;
	}
	@file_put_contents($path, $contents, FILE_APPEND);
	$size = filesize($path);
	$price = ceil($size/1048576);
	DB::query("UPDATE ".DB::table('pdnovel_download')." SET price=$price, size=$size, dateline=$novel[lastupdate] WHERE novelid=$novelid");
}

ob_end_clean();
$readmod = getglobal('config/download/readmod');
$readmod = $readmod > 0 && $readmod < 5 ? $readmod : 2;	
$name = '"'.(strtolower(CHARSET) == 'utf-8' && strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? urlencode($name) : $name).'"';
dheader('Date: '.gmdate('D, d M Y H:i:s', $novel['lastupdate']).' GMT');
dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $novel['lastupdate']).' GMT');
dheader('Content-Encoding: none');
dheader('Content-Disposition: attachment; filename='.$name);
dheader('Content-Type: application/octet-stream');
dheader('Content-Length: '.$size);
$range = 0;
if($readmod == 4 && !empty($_SERVER['HTTP_RANGE'])) {
	list($range) = explode('-',(str_replace('bytes=', '', $_SERVER['HTTP_RANGE'])));
}
if($readmod == 4) {
	dheader('Accept-Ranges: bytes');
	if(!empty($_SERVER['HTTP_RANGE'])) {
		$rangesize = ($size - $range) > 0 ? ($size - $range) : 0;
		dheader('Content-Length: '.$rangesize);
		dheader('HTTP/1.1 206 Partial Content');
		dheader('Content-Range: bytes='.$range.'-'.($size-1).'/'.($size));
	}
}
error_reporting(0);
getlocalfile($path, $readmod, $range);
	
function getlocalfile($filename, $readmod = 2, $range = 0) {
	if($readmod == 1 || $readmod == 3 || $readmod == 4) {
		if($fp = @fopen($filename, 'rb')) {
			@fseek($fp, $range);
			if(function_exists('fpassthru') && ($readmod == 3 || $readmod == 4)) {
				@fpassthru($fp);
			} else {
				echo @fread($fp, filesize($filename));
			}
		}
		@fclose($fp);
	} else {
		@readfile($filename);
	}
	@flush(); @ob_flush();
}
?>