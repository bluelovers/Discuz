<?php

if(!defined('IN_DISCUZ') || !defined('IN_MARK6')) {
exit('ACCESS DENIED');
}

if(!($_G['adminid'] == 1)) {
	showmessage('action_closed' , 'plugin.php?id=mark6');
}
loadcache('usergroups');
@extract($_G['cache']['plugin']['mark6']);
$basejackpot = $_G['cache']['plugin']['mark6']['jackpot'];
$jackpot = DB::result_first("SELECT jackpot FROM ".DB::table('plugin_mark6jackpot')." ORDER BY id DESC LIMIT 1");

$username = $_G['username'];

$query = DB::query("SELECT id FROM ".DB::table('plugin_mark6cp')." ORDER BY id DESC");
$gameid = DB::result($query, 0);
$id = $gameid+1;
$jid = $gameid+1;

$query = DB::query("SELECT number, username, correct, duzhu FROM ".DB::table('plugin_mark6')." WHERE gameid='$id'");
while($user = DB::fetch($query)) {
$userslist[] = $user;
}

if(submitcheck('submit2')){
        if(!$_G['gp_formhash'] == FORMHASH) {
        showmessage('undefined_action');
        }
	while(sizeof(@array_unique($shownumber)) != 6) $shownumber[] = rand(1,$maxnum);
	$shownumber = array_unique($shownumber);

	sort($shownumber);
	foreach($shownumber as $bb){
		$data .= $sp."$bb";
		$sp = ',';
	}
  DB::insert('plugin_mark6cp' , array('shownumber' => $data));

$query = DB::query("SELECT * FROM ".DB::table('plugin_mark6')." WHERE gameid='$id'");
$userlist = array();
$num = 1;
$total = 0;
while($users = DB::fetch($query)) {
$userlist[] = $users;
$total += $users[duzhu];
$test = explode(",", $users[number]);
        foreach($test as $dd){
                if(in_array($dd,$shownumber)){
                        $i++;
                }
        }
if($i<4) {
		$query2 = DB::query("SELECT * FROM ".DB::table('plugin_mark6list')." WHERE username='$users[username]'");
		$existentuser = DB::num_rows($query2);
                $m6l = DB::fetch($query2);
                $net = -($users['duzhu'] < 0 ? -$users['duzhu'] : $users['duzhu']);
		if ($existentuser > 0) {
                        DB::update('plugin_mark6list' , array('winonly' => $m6l['winonly']+$net) , array('username' => $users['username']));
		} else {
                        DB::insert('plugin_mark6list' , array('uid' => $users['uid'] , 'username' => $users['username'] , 'winonly' => $net));
		}
}

DB::update('plugin_mark6' , array('correct' => $i) , array('username' => $users['username'] , 'gameid' => $id , 'id' => $users['id']));
if($i==4) {$money = $third;
$query_m = DB::query("SELECT * FROM ".DB::table('plugin_mark6')." WHERE id='$users[id]'");
while($mark = DB::fetch($query_m)){
updatemembercount($mark['uid'] , array('extcredits2' => +$money) , -1);
}
		$query2 = DB::query("SELECT COUNT(*) FROM ".DB::table('plugin_mark6list')." WHERE username='$users[username]'");
		$existentuser = DB::result($query2, 0);
                $m6l = DB::fetch($query2);
                $net = $money-$users['duzhu'];
		if ($existentuser > 0) {
                        DB::update('plugin_mark6list' , array('win3' => $m6l['win3']+1 , 'totalwin' => $m6l['totalwin']+1 , 'totalmoney' => $m6l['totalmoney']+$money , 'winonly' => $m6l['winonly']+$net) , array('username' => $users['username']));
		} else {
                        DB::insert('plugin_mark6list' , array('uid' => $users['uid'] , 'username' => $users['username'] , 'win3' => 1 , 'totalwin' => 1 , 'totalmoney' => $money , 'winonly' => $net));
		}
}
else if($i==5) {
                $money = $second;
                sendpm($users['uid'] , lang('plugin/mark6' , 'mark6cp_pmtitle_secondprice') , lang('plugin/mark6' , 'mark6cp_pmcontent_secondprice' , array('money' => $money)) , 0);
                $query_m = DB::query("SELECT * FROM ".DB::table('plugin_mark6')." WHERE id='$users[id]'");
                while($mark = DB::fetch($query_m)){
                updatemembercount($mark['uid'] , array('extcredits2' => +$money) , -1);
                }
		$query2 = DB::query("SELECT COUNT(*) FROM ".DB::table('plugin_mark6list')." WHERE username='$users[username]'");
		$existentuser = DB::result($query2, 0);
$net = $money-$users['duzhu'];
		if ($existentuser > 0) {
			DB::query("UPDATE ".DB::table('plugin_mark6list')." SET win2=win2+1, totalwin=totalwin+1, totalmoney=totalmoney+'$money', winonly=winonly+'$net' WHERE username='$users[username]'");
		} else {
                        DB::insert('plugin_mark6list' , array('uid' => $users['uid'] , 'username' => $users['username'] , 'win2' => 1 , 'totalwin' => 1 , 'totalmoney' => $money , 'winonly' => $net));
		}
}
else if($i==6) {
$query_m = DB::query("SELECT * FROM ".DB::table('plugin_mark6')." WHERE id='$users[id]'");
while($mark = DB::fetch($query_m)){
updatemembercount($mark['uid'] , array('extcredits2' => +$jackpot) , -1);
}
DB::update('plugin_mark6' , array('correct' => $i) , array('username' => $users['username'] , 'gameid' => $id , 'id' => $users['id']));
unset($i);
		$query2 = DB::query("SELECT COUNT(*) FROM ".DB::table('plugin_mark6list')." WHERE username='$users[username]'");
		$existentuser = DB::result($query2, 0);
$net = $jackpot-$users['duzhu'];
		if ($existentuser > 0) {
			DB::query("UPDATE ".DB::table('plugin_mark6list')." SET win1=win1+1, totalwin=totalwin+1, totalmoney=totalmoney+'$jackpot', winonly=winonly+'$net' WHERE username='$users[username]'");
		} else {
                        DB::insert('plugin_mark6list' , array('uid' => $users['uid'] , 'username' => $users['username'] , 'win1' => 1 , 'totalwin' => 1 , 'totalmoney' => $jackpot , 'winonly' => $net));
		}
DB::update('plugin_mark6jackpot' , array('jackpot' => $basejackpot) , array('id' => 1));
//updatecache('mark6jp');

        sendpm($users['uid'] , lang('plugin/mark6' , 'mark6cp_pmtitle_firstprice') , lang('plugin/mark6' , 'mark6cp_pmcontent_firstprice' , array('jackpot' => $jackpot)) , 0);
}
unset($i);
}
	showmessage('mark6:mark6cp_success', "plugin.php?id=mark6" , array() , array('alert' => 'right'));
}

include template('mark6:mark6cp');

?>