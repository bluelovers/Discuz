<?php
if(!defined('IN_DISCUZ')) exit('Access Denied');
$setting_timepasslimit=30;

$setting_securityauth=$_G['cache']['plugin']['dsu_sitemap']['securityauth'];

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
if($_G['timestamp']-filemtime(DISCUZ_ROOT.'sitemap_baidu.xml')<=($_G['cache']['plugin']['dsu_sitemap']['baidu_update']*3600)){
    exit('Sitemap has been updated');
}

@set_time_limit(1000);
@ignore_user_abort(TRUE);

$processname='dsu_sitemap_baidu';

if($setting_lockopen){
    if(discuz_process::islocked($processname, 600)) {
		exit('Process has been held');
	}
}

$forums=getforums();
$topics=gettopics($forums);

make_baidu_sitemap($topics);

if($setting_lockopen){
    discuz_process::unlock($processname);
}

function make_baidu_sitemap($topics) {
    global $setting_updatetime, $_G;
    require_once DISCUZ_ROOT.'./source/discuz_version.php';
    $data=sprintf('<?xml version="1.0" encoding="%s" ?><document xmlns:bbs="http://www.baidu.com/search/bbs_sitemap.xsd"><webSite>%s</webSite><webMaster>%s</webMaster><updatePeri>%d</updatePeri><updatetime>%s</updatetime><version>Discuz! %s</version>', ($_G['charset']=='gbk'?'GB2312':($_G['charset']=='big5'?'BIG5':'UTF-8')), str_replace('http://', '', $_G['siteurl']), $_G['setting']['adminemail'], $setting_updatetime, date(DATE_COOKIE, $_G['timestamp']), DISCUZ_VERSION);
    foreach($topics as $topic){
        $data.=sprintf('<item><link>%s</link><title>%s</title><pubDate>%s</pubDate><bbs:lastDate>%s</bbs:lastDate><bbs:reply>%d</bbs:reply><bbs:hit>%d</bbs:hit><bbs:mainLen>%d</bbs:mainLen><bbs:boardid>%d</bbs:boardid><bbs:pick>%d</bbs:pick></item>', $_G['siteurl'].XmlSpecialchars(in_array('forum_viewthread', $_G['setting']['rewritestatus'])?str_replace(array('{fid}', '{tid}', '{page}', '{prevpage}'), array(empty($_G['setting']['forumkeys'][$topic['fid']]) ? $topic['fid'] : $_G['setting']['forumkeys'][$topic['fid']], $topic['tid'], 1, 1), $_G['setting']['rewriterule']['forum_viewthread']):'forum.php?mod=viewthread&tid='.$topic['tid']), XmlSpecialchars($topic['subject']), date(DATE_COOKIE, $topic['dateline']), date(DATE_COOKIE, $topic['lastpost']), $topic['replies'], $topic['views'], $topic['len'], $topic['fid'], $topic['digest']==0?0:1);
    }
    $data.='</document>';
    $fp=fopen(DISCUZ_ROOT.'sitemap_baidu.xml', 'w');
    fwrite($fp, $data);
    fclose($fp);
}

function XmlSpecialchars($code) {
    return str_replace(array('&', "'", '"', '>', '<'), array('&amp;', '&apos;', '&quot;', '&gt;', '&lt;'), $code);
}

function getforums() {
    global $setting_allowhiddengroupforum, $setting_allowgroupfrom, $setting_autopermextends;

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
    $forums=array_merge(array_keys($forums['sub']), array_keys($forums['forum']));
    return $forums;
}

function gettopics($forums) {
    global $_G, $setting_excludemode, $setting_excludetime;
    $sqlwhere=array();
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

    foreach($tableids as $tableid){
        get_topic_from_table($topic, $tableid, $sqlwhere);
    }
    return $topic;
}

function get_topic_from_table(&$topic, $tableid, $sqlwhere) {
    $query=DB::query('SELECT tid, fid, `subject`, dateline, lastpost, views, replies, heats, posttableid, digest, highlight, rate, attachment, recommend_add, recommend_sub, favtimes, sharetimes FROM '.DB::table($tableid > 0 ? "forum_thread_{$tableid}" : 'forum_thread').' WHERE '.join(' AND ', $sqlwhere));
    while($thread=DB::fetch($query)){
		$ret = DB::fetch_first("SELECT LENGTH(message) as len, status, invisible FROM ".DB::table($thread['posttableid'] > 0?"forum_post_{$thread['posttableid']}":'forum_post')." WHERE tid='{$thread['tid']}' AND first='1'");
        if($ret['invisible']==0 && (intval($ret['status']) & 1)==0){
            $thread['len']=$ret['len'];
            $topic[$thread['tid']]=$thread;
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