<?php
if(!defined('IN_DISCUZ')) exit('Access Denied');
$setting_timepasslimit=30;

$setting_securityauth=$_G['cache']['plugin']['dsu_sitemap']['securityauth'];
$setting_iftopic=$_G['cache']['plugin']['dsu_sitemap']['allowtopic'];

$setting_lockopen=1;

$setting_allowhiddengroupforum=$_G['cache']['plugin']['dsu_sitemap']['allowhiddengroupforum'];
$setting_allowgroupfrom=$_G['cache']['plugin']['dsu_sitemap']['allowgroupfrom'];
$setting_autopermextends=$_G['cache']['plugin']['dsu_sitemap']['autopermextends'];

if($module=='updatebaidu'){
    $setting_excludemode='dateline';
    $setting_excludetime=$_G['cache']['plugin']['dsu_sitemap']['baidu_peri']*3600;
    $setting_updatetime=$_G['cache']['plugin']['dsu_sitemap']['baidu_peri'];
}
if($module=='updatenormal'){
    $setting_excludemode='dateline';
    $setting_excludetime=$_G['cache']['plugin']['dsu_sitemap']['normal_include']*86400;
    $setting_updatetime=$_G['cache']['plugin']['dsu_sitemap']['normal_update']*3600;
}

$timepass=$_G['timestamp']-$_G['gp_timestamp'];
if($timepass < 0 || $timepass > $setting_timepasslimit){
    exit('Access Denied');
}
if(!$_G['gp_randnum']==(string)intval($_G['gp_randnum'])){
    exit('Access Denied');
}
if($_G['gp_auth']!=md5($_G['gp_randnum'].'|'.$_G['gp_timestamp'].md5($_G['gp_timestamp'].$setting_securityauth.$_G['authkey']))){
    exit('Access Denied');
}
if($_G['timestamp']-filemtime(DISCUZ_ROOT.'sitemap.xml')<=($_G['cache']['plugin']['dsu_sitemap']['normal_update']*3600)){
    exit('Sitemap has been updated');
}

@set_time_limit(1000);
@ignore_user_abort(TRUE);

$processname='dsu_sitemap_normal';
if($setting_lockopen){
    if(discuz_process::islocked($processname, 600)) {
		exit('Process has been held');
	}
}

list($forums, $forumrank)=getforums();
$forumrank=updaterank($forumrank);
list($topics, $threadrank, $tidinarchiver)=gettopics($forums, $forumrank);

make_normal_sitemap($topics, $threadrank, $tidinarchiver);
if($setting_lockopen){
    discuz_process::unlock($processname);
}

function portal_gettopics() {
    global $_G, $setting_excludetime;

    $return=array();

    $query=DB::query('SELECT aid, dateline FROM '.DB::table('portal_article_title').' WHERE dateline>'.($_G['timestamp']-$setting_excludetime));

    while($topic=DB::fetch($query)){
        $return[]=$topic;
    }

    return $return;
}

function make_normal_sitemap($topics, $threadrank, $tidinarchiver) {
    global $_G, $setting_iftopic;
    require_once DISCUZ_ROOT.'./source/discuz_version.php';
    $data='<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    if($setting_iftopic){
        $acts=portal_gettopics();
        foreach($acts as $act){
            $data.=sprintf('<url><loc>%s</loc><lastmod>%s</lastmod>%s</url>', $_G['siteurl'].XmlSpecialchars((in_array('portal_article', $_G['setting']['rewritestatus'])?str_replace(array('{id}', '{page}'), array($act['aid'], 1), $_G['setting']['rewriterule']['portal_article']):'portal.php?mod=view&aid='.$act['aid'])), date('c', $act['dateline']), ($_G['timestamp']-$act['dateline']>86400?'<changefreq>monthly</changefreq>':''));
        }
    }
    $ranklist=array_keys($threadrank);
    foreach($ranklist as $topicid){
        $topic=$topics[$topicid];
        $data.=sprintf('<url><loc>%s</loc><lastmod>%s</lastmod>%s<priority>%s</priority></url>', $_G['siteurl'].XmlSpecialchars((in_array('forum_viewthread', $_G['setting']['rewritestatus'])?str_replace(array('{fid}', '{tid}', '{page}', '{prevpage}'), array(empty($_G['setting']['forumkeys'][$topic['fid']]) ? $topic['fid'] : $_G['setting']['forumkeys'][$topic['fid']], $topic['tid'], 1, 1), $_G['setting']['rewriterule']['forum_viewthread']):'forum.php?mod=viewthread&tid='.$topic['tid'])), date('c', $topic['lastpost']), (in_array($topicid, $tidinarchiver)?'<changefreq>never</changefreq>':($_G['timestamp']-$topic['lastpost']>86400?'<changefreq>hourly</changefreq>':'')), round(min(100, max(0, $threadrank[$topicid]))/100, 1));
    }
    $data.='</urlset>';
    $fp=fopen(DISCUZ_ROOT.'sitemap.xml', 'w');
    fwrite($fp, $data);
    fclose($fp);
}

function XmlSpecialchars($code) {
    return str_replace(array('&', "'", '"', '>', '<'), array('&amp;', '&apos;', '&quot;', '&gt;', '&lt;'), $code);
}

function getforums() {
    global $setting_allowhiddengroupforum, $setting_allowgroupfrom, $setting_autopermextends;

    $forumrank=array();

    $allowgstatus=array(1);
    if($setting_allowhiddengroupforum){
        $allowgstatus[]=0;
    }
    if($setting_allowgroupfrom){
        $allowgstatus[]=3;
    }

    $tmp=DB::query('SELECT fid, fup, `type`, threads, posts, `simple` FROM '.DB::table('forum_forum').' WHERE status IN('.join(',', $allowgstatus).')');
    $forums=array('group'=>array(), 'forum'=>array(), 'sub'=>array());
    while($aforum=DB::fetch($tmp)){
        $forums[$aforum['type']][$aforum['fid']]=$aforum;
    }
    $displayforum=array();

    foreach($forums['forum'] as $key=>$value){
        if(!isset($forums['group'][$value['fup']])){
            unset($forums['forum'][$key]);
        }else{
            if(!getforumperm($key)){
                if($setting_autopermextends){
                    $displayforum[]=$key;
                    unset($forums['forum'][$key]);
                }else{
                    unset($forums['forum'][$key]);
                }
            }else{
                if($value['simple']){
                    $displayforum[]=$key;
                    unset($forums['forum'][$key]);
                }else{
                    $displayforum[]=$key;
                }
            }
        }
    }
    foreach($forums['sub'] as $key=>$value){
        if(!in_array($value['fup'], $displayforum)){
            unset($forums['sub'][$key]);
        }else{
            if(!getforumperm($key)){
                unset($forums['sub'][$key]);
            }
        }
    }
    foreach($forums['sub'] as $key=>$value){
        $forumrank[$key]=getforumrank($value);
    }
    foreach($forums['forum'] as $key=>$value){
        $forumrank[$key]=getforumrank($value);
    }
    $forums=array_merge(array_keys($forums['sub']), array_keys($forums['forum']));
    return array($forums, $forumrank);
}

function getforumrank($line) {
    $policy=get_policy($line['fid']);
    return $policy['forum']*$line['posts']+$line['threads'];
}

function updaterank($ranks) {
    $count=count($ranks);
    $average=array_sum($ranks)/$count;
    $newranks=array();
    $tmp1=0;
    foreach($ranks as $v){
        $tmp1+=$v/$count*$v;
    }
    $sd=sqrt($tmp1-$average*$average);
    foreach($ranks as $k=>$v){
        $newranks[$k]=10*($v-$average)/$sd+50;
    }
    return $newranks;
}


function gettopics($forums, $forumrank) {
    global $_G, $setting_excludemode, $setting_excludetime;
    $sqlwhere=array();
    $tidinarchiver=array();
    $sqlwhere[]='fid IN ('.join(',', $forums).')';

    $tmp='dateline';
    if($setting_excludemode=='lastpost'){
        $tmp='lastpost';
    }
    if($setting_excludetime){
        $sqlwhere[]=$tmp.'>'.($_G['timestamp']-$setting_excludetime);
    }
    $sqlwhere[]='readperm<1';
    $sqlwhere[]='displayorder>=0';

    $topic=array();

    loadcache('threadtableids');
    if($GLOBALS['_G']['cache']['threadtableids']){
        $tableids=array_merge(array(0), $GLOBALS['_G']['cache']['threadtableids']);
    }else{
        $tableids=array(0);
    }

    $endthreadrank=array();
    $threadrank=array();
    foreach($tableids as $tableid){
        get_topic_from_table($topic, $tableid, $sqlwhere, $tidinarchiver);
    }
    foreach($topic as $thread){
        $threadrank[$thread['fid']][$thread['tid']]=getthreadrank($thread);
    }
    foreach($threadrank as $fid=>$threads){
        $thisforumrank=$forumrank[$fid];
        $threads=updaterank($threads);
        foreach($threads as $tid=>$rank){
            $policy=get_policy($topic[$tid]['fid']);
            $endthreadrank[$tid]=$score=($thisforumrank*0.2+$rank*0.8)*$policy['forumrank'];
            if($score < $policy['normal']){
                unset($topic[$tid]);
                unset($endthreadrank[$tid]);
            }
        }
    }
    return array($topic, $endthreadrank, $tidinarchiver);
}
function getthreadrank($line) {
    $policy=get_policy($line['fid']);
    return $policy['view']*$line['views']+$policy['reply']*$line['replies']+$policy['heat']*$line['heats']+$policy['recommend']*($line['recommend_add']+$line['recomment_sub'])+$policy['favtime']*$line['favtimes']+$policy['sharetime']*$line['sharetimes']+($line['digest']?$policy['digest']:0)+($line['rate']?$policy['rate']:0);
}


function get_topic_from_table(&$topic, $tableid, $sqlwhere, &$tidinarchiver) {
    $query=DB::query('SELECT tid, fid, `subject`, dateline, lastpost, views, replies, heats, posttableid, digest, highlight, rate, attachment, recommend_add, recommend_sub, favtimes, sharetimes FROM '.DB::table($tableid > 0 ? "forum_thread_{$tableid}" : 'forum_thread').' WHERE '.join(' AND ', $sqlwhere));
    while($thread=DB::fetch($query)){
		$ret = DB::fetch_first("SELECT LENGTH(message) as len, status, invisible FROM ".DB::table($thread['posttableid'] > 0?"forum_post_{$thread['posttableid']}":'forum_post')." WHERE tid='{$thread['tid']}' AND first='1'");
        if($ret['invisible']==0 && (intval($ret['status']) & 1)==0){
            $thread['len']=$ret['len'];
            $topic[$thread['tid']]=$thread;
            if($tableid>0){
                $tidinarchiver[]=$thread['tid'];
            }
        }
    }
}

function getforumperm($fid) {
	static $forumpermcache=array();
    if(!$forumpermcache){
        $query=DB::query('SELECT fid, password, formulaperm, viewperm, spviewperm FROM '.DB::table('forum_forumfield'));
        while($forum=DB::fetch($query)){
            $allow=1;
            while(1){
                $spviewperm=explode("\t", $forum['spviewperm']);
                if(in_array('7', $spviewperm)){
                    $allow=1;
                    break;
                }
                $formulaperm=unserialize($forum['formulaperm']);
                if($formulaperm[0] || $formulaperm['users'] || $forum['password'] || $formulaperm['medal']){
                    $allow=0;
                    break;
                }
                if($forum['viewperm'] && !preg_match("/(^|\t)(7)(\t|$)/", $forum['viewperm'])){
                    $allow=0;
                    break;
                }
                break;
            }
            $forumpermcache[$forum['fid']]=$allow;
        }
    }
    return $forumpermcache[$fid];
}
function get_policy($fid) {
    global $_G;
    static $policy=array (
      1 =>
      array (
        'forum' => '0.1',
        'forumrank' => '1.1',
        'view' => '1',
        'reply' => '50',
        'heat' => '0',
        'recommend' => '0',
        'favtime' => '80',
        'sharetime' => '40',
        'digest' => '0',
        'rate' => '0',
        'normal' => '0',
      ),
      2 =>
      array (
        'forum' => '0',
        'forumrank' => '0.8',
        'view' => '10',
        'reply' => '100',
        'heat' => '0',
        'recommend' => '0',
        'favtime' => '0',
        'sharetime' => '0',
        'digest' => '0',
        'rate' => '0',
        'normal' => '30',
      ),
      3 =>
      array (
        'forum' => '0.001',
        'forumrank' => '1.2',
        'view' => '1',
        'reply' => '2',
        'heat' => '0',
        'recommend' => '0',
        'favtime' => '10',
        'sharetime' => '10',
        'digest' => '200',
        'rate' => '120',
        'normal' => '40',
      ),
      4 =>
      array (
        'forum' => '0.1',
        'forumrank' => '0.8',
        'view' => '1',
        'reply' => '20',
        'heat' => '0',
        'recommend' => '20',
        'favtime' => '0',
        'sharetime' => '0',
        'digest' => '0',
        'rate' => '0',
        'normal' => '0',
      ),
      5 =>
      array (
        'forum' => '0.001',
        'forumrank' => '0.6',
        'view' => '0',
        'reply' => '1',
        'heat' => '0',
        'recommend' => '0',
        'favtime' => '0',
        'sharetime' => '0',
        'digest' => '0',
        'rate' => '0',
        'normal' => '0',
      ),
      6 =>
      array (
        'forum' => '0.001',
        'forumrank' => '1',
        'view' => '0',
        'reply' => '10',
        'heat' => '0',
        'recommend' => '5',
        'favtime' => '20',
        'sharetime' => '20',
        'digest' => '500',
        'rate' => '50',
        'baidu' => '0',
        'normal' => '0',
      ),
    );
    static $forumlist=null;
    if($forumlist===null){
        $forumlist=array();
        for($i=1;$i<=6;$i++){
            $data=unserialize($_G['cache']['plugin']['dsu_sitemap']['forum_policy_'.$i]);
            if(!in_array('', $data)){
                foreach($data as $id){
                    $forumlist[$id]=$i;
                }
            }
        }
    }
    if(isset($forumlist[$fid])){
        return $policy[$forumlist[$fid]];
    }else{
        return $policy[6];  //6 is default
    }
}