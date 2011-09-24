<?php

// row=118, before    } elseif($_GET['view'] == 'me') {
// require_once DISCUZ_ROOT.'/source/plugin/kk_weibo/hook/space_home.php';
// /home.php?mod=space&do=home&view=all&kk_weibo=1
// 嵌入代码方式, 好兼容官方补丁
if (!defined('IN_DISCUZ')) exit('Access Denied');

if ($_GET['kk_weibo'] != '1') return;
//if(!isset($_G['cache']['plugin']['kk_weibo'])) return;
$_GET['view'] = 'kk_weibo';
$theurl .= '&kk_weibo=1';
$my_table = DB::table('kk_weibo');
$my_query = DB::query("select uid_rel from {$my_table} where uid={$_G['uid']}");
$my_list = Array();
while ($my_fetch = DB::fetch($my_query)) $my_list[] = $my_fetch['uid_rel'];
$my_string = implode(',', $my_list);
$wheresql['uid'] = empty($my_string) ? "uid=0" : "uid in ({$my_string})";

?>
