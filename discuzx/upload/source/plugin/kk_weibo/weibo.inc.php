<?php
if(!defined('IN_DISCUZ')) exit('Access Denied');
if(empty($_G['uid'])) showmessage('本页面需要登录后查看');

$perpage = 24;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

$view=$_GET['view']; if(empty($view)) $view='attention';
$uid_rel=(int)$_GET['rel']; if($uid_rel<=0) $uid_rel=$_G['uid'];
if(!in_array($view,Array('attention','fans'))) showmessage('参数无效');

include libfile('function/home');
$space = getspace($uid_rel);
if(empty($space)) showmessage('space_does_not_exist');
$navtitle = $space['username'].'的关注';
$list=$uid_list=Array(); $uid_string=''; $count=0;
$table=DB::table('common_member'); $table_weibo=DB::table('kk_weibo');
if($view=='attention') {
	$theurl="/plugin.php?id=kk_weibo:weibo&view=attention&rel={$uid_rel}";
	$count=DB::fetch_first("select count(*) as t_count from {$table_weibo} where uid={$uid_rel}"); $count=$count['t_count'];
	if(!empty($count)) {
		$query=DB::query("select * from {$table_weibo} where uid={$uid_rel} limit {$start},{$perpage}");
		while($fetch=DB::fetch($query)) $uid_list[]=$fetch['uid_rel'];
	}
	$description="{$space['username']}关注的用户,共{$count}条";
} else if($view=='fans') {
	$theurl="/plugin.php?id=kk_weibo:weibo&view=fans&rel={$uid_rel}";
	$count=DB::fetch_first("select count(*) as t_count from {$table_weibo} where uid_rel={$uid_rel}"); $count=$count['t_count'];
	if(!empty($count)) {
		$query=DB::query("select * from {$table_weibo} where uid_rel={$uid_rel} limit {$start},{$perpage}");
		while($fetch=DB::fetch($query)) $uid_list[]=$fetch['uid'];
	}
	$description="关注{$space['username']}的用户,共{$count}条";
}
$uid_string=implode(',',$uid_list); if(!empty($uid_string)) {
	$table_group=DB::table('common_usergroup');
	$query=DB::query("select cm.*,g.grouptitle,kk.count_attention,kk.count_fans from {$table} as cm,{$table_weibo}_stat as kk,{$table_group} as g where cm.uid=kk.uid and cm.groupid=g.groupid and cm.uid in ({$uid_string})");	
	while($fetch=DB::fetch($query)) $list[$fetch['uid']]=$fetch;
	if($view=='fans'||$uid!=$uid_rel) {
		$table=DB::table('kk_weibo');
		$query=DB::query("select * from {$table} where uid={$_G['uid']} and uid_rel in ({$uid_string})");
		while($fetch=DB::fetch($query)) {
			$list[$fetch['uid_rel']]['already_attention']=true;
		}		
	}	
}
$multi = multi($count, $perpage, $page, $theurl);
$a_actives = array($view=>' class="a"');
include template('kk_weibo:weibo');
?>