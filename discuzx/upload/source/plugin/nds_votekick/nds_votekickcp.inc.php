<?php
/*
	Plugin FOR Discuz! X1.5
	Copyright (c) 2009-2012 WWW.NWDS.CN | NDS.西域数码工作室
	$Id: nds_votekick.inc.php V1.6 20110401 SINGCEE $
 */

if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
	}
if (!$_G['uid']) {
	showmessage('nds_votekick:nologin', NULL);
}
$tid = intval($_G['gp_tid']);
 $voteforums = unserialize($_G['cache']['plugin']['nds_votekick']['voteforums']);
 $suspectedgroups = unserialize($_G['cache']['plugin']['nds_votekick']['suspectedgroups']);
 $lvotes = $_G['cache']['plugin']['nds_votekick']['lvotes'];
$vkscp = DB::fetch_first("SELECT votes,uids FROM ".DB::table('nds_votekick')." WHERE tid = '".$tid."'");
           $vkusers = '';
           $vkuids = '0';
           $votes = 0;
           if ($vkscp['votes'])  {
		      $votes = $vkscp['votes'];
		      $vkuids = array();
		      $vkuids = explode("\t",$vkscp['uids']);
		      foreach($vkuids as $vkuid) {
              $vkuser = getuserbyuid($vkuid);
              $vkusers.= "<a href=\"home.php?mod=space&uid=$vkuid\" target=\"_blank\">".$vkuser['username']."</a>&nbsp;&nbsp;";
              }
		   }

/*
 * if (in_array($_G['adminid'],array(1,2))){
      showmessage('nds_votekick:noadmin');
} **/
 $vtg = 0  ;
 $votegroups = unserialize($_G['cache']['plugin']['nds_votekick']['votegroups']);
 if (in_array($_G['groupid'],$votegroups)){
      	$vtg = 1;
 }else{
    $extgroupids = !empty($_G['member'][extgroupids]) ? explode("\t", $_G['member'][extgroupids]) : '';
    foreach($extgroupids as $v) {
     if (in_array($v,$votegroups) and !empty($extgroupids)){
        $vtg = 1;
     }
    }
 }
 if ($vtg != 1){
 	showmessage('nds_votekick:nopw');
 }

 $vkthread = DB::fetch_first("SELECT fid,authorid,subject,dateline FROM ".DB::table('forum_thread')." WHERE tid = '".$tid."'");
  if( $_G['cache']['plugin']['nds_votekick']['vtexpired'] > 0 && ($_G['timestamp'] - $vkthread['dateline']) > $_G['cache']['plugin']['nds_votekick']['vtexpired'] *24*60*60) {
  	 showmessage('nds_votekick:vtexpired');
  }
 if($vkthread['fid']) {
	if ( in_array($vkthread['fid'], $voteforums)) {
	 showmessage('nds_votekick:nofid');
	}
}else{
	showmessage('nds_votekick:notid');
}
 $vk2user = getuserbyuid($vkthread['authorid']);
   if ( !in_array($vk2user['groupid'],$suspectedgroups)) {
   	showmessage('nds_votekick:nopw2');
   }



 if ($votes  >=  $lvotes ){
      showmessage('nds_votekick:vtout');
 }
 $uids = explode("\t",$vkscp['uids']);
 if (in_array($_G['uid'],$uids)){
      showmessage('nds_votekick:norep');
   }

   //提交处理
if(submitcheck('votekicksubmit')) {
  if ($votes) {
    DB::query("UPDATE ".DB::table('nds_votekick')." SET votes = votes + 1,uids = CONCAT(uids,'\t','".$_G['uid']."')  WHERE tid='".$tid."'");
}else{

	DB::query("INSERT INTO ".DB::table('nds_votekick')." (tid, votes, uids) VALUES ('$tid','1' ,'$_G[uid]')");
}

$notevars = array(
  'url1' => "forum.php?mod=viewthread&tid=".$tid,
  'subject1'  => $vkthread['subject'] ,
  'vkuser' => "<a href=\"home.php?mod=space&uid=$_G[uid]\">".$_G['member']['username']."</a>",
  'reason' => dhtmlspecialchars(dstripslashes(cutstr(trim($_POST['description']),20)))
 );

$mistoadminid = $_G['cache']['plugin']['nds_votekick']['votekickadminid'] > 1  ? $_G['cache']['plugin']['nds_votekick']['votekickadminid']:$_G['config']['admincp']['founder'];
 if ($_G['cache']['plugin']['nds_votekick']['votetoauthor'] ) {
notification_add($vkthread['authorid'], 'votekick', lang('plugin/nds_votekick','notice1'),$notevars, '', 1);
}
 if ($_G['cache']['plugin']['nds_votekick']['votetoadmin']){
notification_add($mistoadminid, 'votekick', lang('plugin/nds_votekick','notice2'),$notevars, '', 1);
}
$treatmod = $_G['cache']['plugin']['nds_votekick']['treatmod'];
if ($votes+1  >=  $lvotes ){
 switch ($treatmod) {
 	 case 1:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -2  WHERE tid='".$tid."'");
        updatemoderate('tid', $tid);
        $opmode = lang('plugin/nds_votekick', 'vkop1');
       break;
     case 2:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -1  WHERE tid='".$tid."'");
               $opmode = lang('plugin/nds_votekick', 'vkop2');
       break;
     case 3:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -2  WHERE tid='".$tid."'");
        updatemoderate('tid', $tid);
       DB::query("UPDATE ".DB::table('common_member')." SET adminid = -1,groupid = 4  WHERE uid='".$vkthread['authorid']."'");
               $opmode = lang('plugin/nds_votekick', 'vkop3');
       break;
     case 4:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -1  WHERE tid='".$tid."'");
       DB::query("UPDATE ".DB::table('common_member')." SET adminid = -1,groupid = 4  WHERE uid='".$vkthread['authorid']."'");
               $opmode = lang('plugin/nds_votekick', 'vkop4');
       break;
     case 5:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -2  WHERE tid='".$tid."'");
        updatemoderate('tid', $tid);
       DB::query("UPDATE ".DB::table('common_member')." SET adminid = -1,groupid = 5  WHERE uid='".$vkthread['authorid']."'");
               $opmode = lang('plugin/nds_votekick', 'vkop5');
       break;
     case 6:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -1  WHERE tid='".$tid."'");
       DB::query("UPDATE ".DB::table('common_member')." SET adminid = -1,groupid = 5  WHERE uid='".$vkthread['authorid']."'");
               $opmode = lang('plugin/nds_votekick', 'vkop6');
       break;
     case 7:
       //DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -2  WHERE authorid ='".$vkthread['authorid']."'");
       //DB::query("UPDATE ".DB::table('common_member')." SET adminid = -1,groupid = 5  WHERE uid='".$vkthread['authorid']."'");
       //  $opmode = lang('plugin/nds_votekick', 'vkop7');
       break;
     case 8:
       DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = -1  WHERE authorid ='".$vkthread['authorid']."'");
       DB::query("UPDATE ".DB::table('common_member')." SET adminid = -1,groupid = 5  WHERE uid='".$vkthread['authorid']."'");
               $opmode = lang('plugin/nds_votekick', 'vkop8');
       break;
 }

 $notevars2 = array(
  'url1' => "forum.php?mod=viewthread&tid=".$tid,
  'subject1'  => $vkthread['subject'] ,
  'opmod' => $opmode,
  'vkuser' => "<a href=\"home.php?mod=space&uid=$_G[uid]\">".$_G['member']['username']."</a>",
  'vkcount' => $lvotes,
 );

 if ($_G['cache']['plugin']['nds_votekick']['mistoauthor'] ) {
 notification_add($vkthread['authorid'], 'votekick', lang('plugin/nds_votekick','notice3'),$notevars2, '', 1);
 }
  if ($_G['cache']['plugin']['nds_votekick']['mistoadmin']){
 notification_add($mistoadminid, 'votekick', lang('plugin/nds_votekick','notice4'),$notevars2, '', 1);
 }
}
   showmessage('nds_votekick:vkok', dreferer(), array(), array('showdialog' => true, 'closetime' => true));

	//showmessage('nds_votekick:vkok', '', array(), array('msgtype' => 3));
 }

include template('nds_votekick:votekickwindow');

?>