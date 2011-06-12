<?php

if(!defined('IN_DISCUZ')) {
exit('ACCESS DENIED');
}

if(!$_G['uid'] || !$_G['username']) {
showmessage('not_loggedin' , '' , '' , array('login' => 1));
}

define('IN_MARK6' , TRUE);

if($_G['gp_action'] == 'admin') {
require_once DISCUZ_ROOT.'./source/plugin/mark6/mark6cp.inc.php';
dexit();
}elseif($_G['gp_action'] == 'ranklist') {
require_once DISCUZ_ROOT.'./source/plugin/mark6/mark6rank.inc.php';
dexit();
}
@extract($_G['cache']['plugin']['mark6']);

$basejackpot = $_G['cache']['plugin']['mark6']['jackpot'];

$jackpot = DB::result_first("SELECT jackpot FROM ".DB::table('plugin_mark6jackpot')." ORDER BY id DESC LIMIT 1");

$username = $_G['username'];



$query = DB::query("SELECT id FROM ".DB::table('plugin_mark6cp')." ORDER BY id DESC");
$id = DB::result($query, 0);
$gameid = $id+1;
$query = DB::query("SELECT shownumber,id FROM ".DB::table('plugin_mark6cp')."  ORDER BY id LIMIT ".($gameid-6 < 0 ? 0 : $gameid-6).", 5");
$shownumberlist = array();
$count = 1;
while($shownumber = DB::fetch($query)) {
        $shownumberlist[] = $shownumber;
        $count += 1;
}

$query = DB::query("SELECT number,gameid,correct FROM ".DB::table('plugin_mark6')." WHERE username='$username' AND gameid='$id' ORDER BY correct DESC");
$mynumberlist = array();
$counts = 1;
while($mynumber = DB::fetch($query)) {
        $mynumberlist[] = $mynumber;
        $counts += 1;
}

$query = DB::query("SELECT username,number,id,correct,duzhu FROM ".DB::table('plugin_mark6')."  WHERE gameid='$id' ORDER BY correct DESC");
$memnumberlist = array();
$memcounts = 1;
while($memnumber = DB::fetch($query)) {
        $memnumberlist[] = $memnumber;
        $memcounts += 1;
}

if(!$jackpot){
$jackpotnew = $basejackpot;
}else{
$jackpotnew = $jackpot;
}

getuserprofile('extcredits2');
$usermoney = $_G['member']['extcredits2'];

if(submitcheck('comsubmit')){
$com = rand(8,12); //隨機抽取X個號碼，現時為8~12個; 
$duzhu = $comrandom;
        if ($usermoney < $duzhu) {
                        showmessage('mark6:mark6_nomoney', 'plugin.php?id=mark6' , array() , array('alert' => 'error'));
        }
        $query = DB::query("SELECT COUNT(*) FROM ".DB::table('plugin_mark6')." WHERE username='$_G[username]' AND gameid='$gameid' AND duzhu !='0'");
        $counter = DB::result($query, 0);
        if($counter > 0){
                showmessage('mark6:mark6_ae', "plugin.php?id=mark6&gameid=$gameid" , array() , array('alert' => 'error'));
        }
	while(sizeof(@array_unique($comnumber)) != $com) $comnumber[] = rand(1, $maxnum);
	$comnumber = array_unique($comnumber);

	sort($comnumber);
	foreach($comnumber as $bb){
		$data .= $sp."$bb";
		$sp = ',';
	}

//$jackpot = $_DCACHE['mark6jp'][0]['jackpot'];
if(!$jackpot){
$save = $duzhu * 0.5;
$new = $save + $basejackpot;
  DB::insert('plugin_mark6jackpot' , array('jackpot' => $new));
}else{
$save = $duzhu * 0.5;
$newjackpot = $jackpot + $save;
        DB::update('plugin_mark6jackpot' , array('jackpot' => $newjackpot) , array('id' => 1));
}
  DB::insert('plugin_mark6' , array('username' => $_G['username'] , 'uid' => $_G['uid'] , 'gameid' => $gameid , 'number' => $data , 'duzhu' => $duzhu));
  updatemembercount($_G['uid'] , array('extcredits2' => -$duzhu) , -1);

//updatecache('mark6jp');
        showmessage('mark6:mark6_success', "plugin.php?id=mark6" , array() , array('alert' => 'right'));
}

if (submitcheck('paysubmit')) {
        if(!$_G['gp_formhash'] == FORMHASH) {
        showmessage('undefined_action');
        }
                $query = DB::query("SELECT COUNT(*) FROM ".DB::table('plugin_mark6')." WHERE username='$_G[username]' AND gameid='$gameid' AND duzhu !='0'");
        $counter = DB::result($query, 0);
        if($counter > 0){
                showmessage('mark6:mark6_ae', "plugin.php?id=mark6&gameid=$gameid" , array() , array('alert' => 'error'));
        }
  DB::insert('plugin_mark6' ,  array('username' => $_G['username'] , 'uid' => $_G['uid'] , 'gameid' => intval($gameid) , 'number' => daddslashes($_G['gp_buy']) , 'duzhu' => intval($_G['gp_duzhu'])));
  updatemembercount($_G['uid'] , array('extcredits2' => -$duzhu) , -1);

//$jackpot = $_DCACHE['mark6jp'][0]['jackpot'];
if(!$jackpot){
$save = $duzhu * 0.5;
$new = $save + $basejackpot;
  DB::insert('plugin_mark6jackpot' , array('jackpot' => $new));
}else{
$save = $duzhu * 0.5;
$newjackpot = $jackpot + $save;
        DB::update('plugin_mark6jackpot' , array('jackpot' => $newjackpot) , array('id' => 1));
}

//updatecache('mark6jp');
        showmessage('mark6:mark6_success', "plugin.php?id=mark6" , array() , array('alert' => 'right'));
}

if (submitcheck('Submit')) {
        $usernum = array();
        for($i=0;$i<=11;$i++) {
        if($_G['gp_usernum'][$i]) {
        $usernum[$i] = intval($_G['gp_usernum'][$i]);
        }
        }
        if(count($usernum) == 0) {
        showmessage('mark6:mark6_nonum' , "plugin.php?id=mark6" , array() , array('alert' => 'error'));
        }
	foreach($usernum as $bb => $val){
                if($bb || $bb == 0){
                if(($val > $maxnum) || ($val < 1)){
              showmessage(lang('plugin/mark6' , 'mark6_restriction' , array('maxnum' => $maxnum)) , "plugin.php?id=mark6" , array() , array('alert' => 'error'));
	        }else{
		$buy .= $sp."$val";
		$sp = ',';
	        }
                $usernum[$bb] = intval($val);
	}
if(count(array_unique(explode(",", $buy))) != count(explode(",", $buy))) showmessage('mark6:mark6_norepeat', "plugin.php?id=mark6" , array() , array('alert' => 'error'));
}
$check = count(explode(",", $buy));
if($check < 8){
showmessage(lang('plugin/mark6' , 'mark6_notenough' , array('check' => $check , 'buy' => $buy)) , "plugin.php?id=mark6" , array() , array('alert' => 'error'));
}
if($check <9) $duzhu = $eight;
if($check == 9) $duzhu = $nine;
if($check == 10) $duzhu = $ten;
if($check == 11) $duzhu = $eleven;
if($check == 12) $duzhu = $twelve;

        if ($usermoney < $duzhu) {
                        showmessage('mark6:mark6_nomoney','plugin.php?id=mark6' , array() , array('alert' => 'error'));
        }

        if ($username && $duzhu) {
                $query = DB::query("SELECT COUNT(*) FROM ".DB::table('plugin_mark6')." WHERE username='$_G[username]' AND gameid='$gameid' AND duzhu !='0'");
                $counter = DB::result($query, 0);
                if($counter > 0){
                        showmessage('mark6:mark6_ae', "plugin.php?id=mark6" , array() , array('alert' => 'error'));
                }
                $moneyleft = $usermoney - $duzhu;
                include template('mark6:mark6_pay_submit');
        } 
} else {
loadcache('usergroups');
$pernum = 5;
$query = DB::query("SELECT username, number,duzhu FROM ".DB::table('plugin_mark6')." WHERE gameid='$gameid' ORDER BY duzhu LIMIT 0,5");
$allnum = DB::num_rows($query);
$userlist = array();
$num = 1;
$total = 0;
while($users = DB::fetch($query)) {
        $userlist[] = $users;
        $num += 1;
}
$multipage1 = multi($allnum , $pernum , $page , 'plugin.php?id=mark6&switch=userlist');

include template('mark6:mark6');


}

?>