<?php
error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$ptid = !empty($_GET['tid']) ? intval($_GET['tid']) : (isset($_GET['ptid']) ? intval($_GET['ptid']) : 0);
$goto = isset($_GET['goto']) ? $_GET['goto'] : '';

/**
 * 修正網址參數會變成 0 的意外BUG
 * forum.php?mod=redirect&goto=lastpost&ptid=0&pid=0
 */
$url = 'forum.php?mod=redirect&goto='."{$goto}&ptid={$ptid}&pid={$pid}";

header("location: $url");
?>