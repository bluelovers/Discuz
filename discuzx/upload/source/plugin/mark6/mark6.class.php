<?php

if(!defined('IN_DISCUZ')) {
exit('ACCESS DENIED');
}

class plugin_mark6 {

  function plugin_mark6() {
  }
}

class plugin_mark6_forum extends plugin_mark6 {

  function post() {
  global $_G , $thread;

  switch($_G['gp_action']) {
  case 'newthread':
  if($_G['gp_topicsubmit']) {
  $prand = $_G['cache']['plugin']['mark6']['postrand'];
  $maxnum = $_G['cache']['plugin']['mark6']['maxnum'];
  $postrand = $prand + 1;
  $a = rand(0, 100);
  if($a < $postrand && $a > 0){
  $query_id = DB::query("SELECT id FROM ".DB::table('plugin_mark6cp')." ORDER BY id DESC");
  $id = DB::result($query_id, 0);
  $gameid = $id+1;
  $com = rand(8,12); // 隨機抽出X個號碼，現在是8~12個，可自行更改;
  while(sizeof(@array_unique($comnumber)) != $com) $comnumber[] = rand(1, $maxnum);
  $comnumber = array_unique($comnumber);
  sort($comnumber);
  foreach($comnumber as $bb){
   $data .= $sp."$bb";
   $sp = ',';
  }
  DB::insert('plugin_mark6' , array('username' => $_G['username'] , 'uid' => $_G['uid'] , 'gameid' => $gameid , 'number' => $data , 'duzhu' => 0));
   sendpm($_G['uid'] , lang('plugin/mark6' , 'mark6') , lang('plugin/mark6' , 'mark6class_yougetone') , 0);
   }
   }
   break;
   case 'reply':
   if($_G['gp_replysubmit']) {

   $rerand = $_G['cache']['plugin']['mark6']['replyrand'];
   $maxnum = $_G['cache']['plugin']['mark6']['maxnum'];
   $replyrand = $rerand + 1;
   $a = rand(0, 100);
   if($a < $replyrand && $a > 0){
   $query_id = DB::query("SELECT id FROM ".DB::table('plugin_mark6cp')." ORDER BY id DESC");
   $id = DB::result($query_id, 0);
   $gameid = $id+1;
   $com = rand(8,12); // 隨機抽出X個號碼，現在是8~12個，可自行更改;
   while(sizeof(@array_unique($comnumber)) != $com) $comnumber[] = rand(1, $maxnum);
   $comnumber = array_unique($comnumber);
   sort($comnumber);
   foreach($comnumber as $bb){
    $data .= $sp."$bb";
    $sp = ',';
   }
   DB::insert('plugin_mark6' , array('username' => $_G['username'] , 'uid' => $_G['uid'] , 'gameid' => $gameid , 'number' => $data , 'duzhu' => 0));
   sendpm($_G['uid'] , lang('plugin/mark6' , 'mark6') , lang('plugin/mark6' , 'mark6class_yougetone') , 0);
   }
   }
   break;
   }
   return array();
   }
}

?>